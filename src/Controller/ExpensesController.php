<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use App\Service\CloudinaryService;

class ExpensesController extends AppController
{
    private CloudinaryService $cloudinary;

    public function initialize(): void
    {
        parent::initialize();
        $this->cloudinary = new CloudinaryService();
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // In a real scenario with full Auth, we'd ensure user is in the session
        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
        
        // We might want to allowUnauthenticated for guests interacting, but for now
        // we'll skip auth checks on add if they are somehow doing it.
        // If guest access is required, we use allowUnauthenticated
        $this->Authentication->allowUnauthenticated(['add', 'delete', 'edit']);
    }

    public function delete(int $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $expensesTable = $this->fetchTable('Expenses');
        $expense = $expensesTable->get($id, ['contain' => ['GroupSessions']]);

        // Since we don't have strict policy enforcement yet, verify session status
        if ($expense->group_session->status !== 'open') {
            $this->Flash->error('Cannot delete expenses from a closed session.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
        }

        // Only the host should be able to delete it. We can trust the UI hiding the button, 
        // but let's do a basic check if identity matches the session host
        $identity = $this->Authentication->getIdentity();
        if ($identity && $expense->group_session->host_id !== $identity->id) {
            $this->Flash->error('Only the host can delete expenses.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
        }

        if ($expensesTable->delete($expense)) {
            $this->Flash->success('Expense deleted successfully.');
        } else {
            $this->Flash->error('Failed to delete expense.');
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
    }

    public function add(string $sessionUuid)
    {
        $this->request->allowMethod(['post']);
        
        $sessionsTable = $this->fetchTable('GroupSessions');
        $session = $sessionsTable->find()->where(['uuid' => $sessionUuid])->first();

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        if ($session->status !== 'open') {
            $this->Flash->error('Cannot add expenses to a closed or locked session.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
        }

        $data = $this->request->getData();
        $payerId = (int)($data['payer_id'] ?? 0);
        
        if (!$payerId) {
            $this->Flash->error('You must select who paid for this expense.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
        }

        // Validate consumers
        $consumers = $data['consumers'] ?? [];
        if (empty($consumers) || !is_array($consumers)) {
            $this->Flash->error('You must select at least one participant to split the cost.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
        }

        $expensesTable = $this->fetchTable('Expenses');
        $allocationsTable = $this->fetchTable('ExpenseAllocations');

        $expense = $expensesTable->newEmptyEntity();
        $expense->session_id = $session->id;
        $expense->participant_id = $payerId; // The one who physically recorded it or who paid
        $expense->description = $data['description'];
        $expense->total_amount = (float)$data['total_amount'];
        $expense->expense_type = $data['expense_type'] ?? 'group';
        $expense->split_type = $data['split_type'] ?? 'equal';
        $expense->waypoint_id = !empty($data['waypoint_id']) ? (int)$data['waypoint_id'] : null;

        // Handle Image Upload
        $imageFile = $this->request->getUploadedFile('image');
        if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
            try {
                $tmpPath = $imageFile->getStream()->getMetadata('uri');
                $folder  = 'equisplit/receipts/session_' . $session->id;
                $expense->image_url = $this->cloudinary->uploadReceipt($tmpPath, $folder);
            } catch (\Exception $e) {
                $this->Flash->error('Image upload failed: ' . $e->getMessage());
                return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
            }
        }

        $connection = $expensesTable->getConnection();
        $connection->begin();

        try {
            if (!$expensesTable->save($expense)) {
                throw new \Exception('Failed to save expense details.');
            }

            // 1. Record the Payer
            $payerAllocation = $allocationsTable->newEmptyEntity();
            $payerAllocation->expense_id = $expense->id;
            $payerAllocation->participant_id = $payerId;
            $payerAllocation->is_payer = true;
            $payerAllocation->amount_owed = 0.0;
            if (!$allocationsTable->save($payerAllocation)) {
                throw new \Exception('Failed to save payer allocation.');
            }

            // 2. Record the Consumers
            $splitType = $expense->split_type;
            
            if ($splitType === 'exact') {
                $exacts = $data['split_exacts'] ?? [];
                $sumExacts = 0.0;
                foreach ($consumers as $consumerId) {
                    $val = (float)($exacts[$consumerId] ?? 0);
                    $sumExacts += $val;
                    
                    $consumerAlloc = $allocationsTable->newEmptyEntity();
                    $consumerAlloc->expense_id = $expense->id;
                    $consumerAlloc->participant_id = (int)$consumerId;
                    $consumerAlloc->is_payer = false;
                    $consumerAlloc->amount_owed = round($val, 2);
                    
                    if (!$allocationsTable->save($consumerAlloc)) {
                        throw new \Exception('Failed to save exact consumer allocation.');
                    }
                }
                
                // Validate sum
                if (round($sumExacts, 2) !== round($expense->total_amount, 2)) {
                    throw new \Exception("Exact amounts (RM {$sumExacts}) do not sum up to the total amount (RM {$expense->total_amount}).");
                }
                
            } elseif ($splitType === 'percentage') {
                $percentages = $data['split_percentages'] ?? [];
                $sumPercentages = 0.0;
                $accumulatedAmount = 0.0;
                $numConsumers = count($consumers);
                $consumers = array_values($consumers);
                
                foreach ($consumers as $index => $consumerId) {
                    $pct = (float)($percentages[$consumerId] ?? 0);
                    $sumPercentages += $pct;
                    
                    $consumerAlloc = $allocationsTable->newEmptyEntity();
                    $consumerAlloc->expense_id = $expense->id;
                    $consumerAlloc->participant_id = (int)$consumerId;
                    $consumerAlloc->is_payer = false;
                    
                    if ($index === $numConsumers - 1) {
                        // Last consumer absorbs rounding remainder
                        $consumerAlloc->amount_owed = round($expense->total_amount - $accumulatedAmount, 2);
                    } else {
                        $amt = round(($expense->total_amount * $pct) / 100, 2);
                        $consumerAlloc->amount_owed = $amt;
                        $accumulatedAmount += $amt;
                    }
                    
                    if (!$allocationsTable->save($consumerAlloc)) {
                        throw new \Exception('Failed to save percentage consumer allocation.');
                    }
                }
                
                // Validate sum
                if (round($sumPercentages, 2) !== 100.00) {
                    throw new \Exception("Percentages do not sum to exactly 100%. Total was {$sumPercentages}%");
                }
                
            } else {
                // Default: Equal split
                $numConsumers = count($consumers);
                $amountPerConsumer = round($expense->total_amount / $numConsumers, 2);
                $accumulatedAmount = 0.0;
                $consumers = array_values($consumers);

                foreach ($consumers as $index => $consumerId) {
                    $consumerAlloc = $allocationsTable->newEmptyEntity();
                    $consumerAlloc->expense_id = $expense->id;
                    $consumerAlloc->participant_id = (int)$consumerId;
                    $consumerAlloc->is_payer = false;
                    
                    if ($index === $numConsumers - 1) {
                        // Last consumer absorbs the exact remainder
                        $consumerAlloc->amount_owed = round($expense->total_amount - $accumulatedAmount, 2);
                    } else {
                        $consumerAlloc->amount_owed = $amountPerConsumer;
                        $accumulatedAmount += $amountPerConsumer;
                    }
                    
                    if (!$allocationsTable->save($consumerAlloc)) {
                        throw new \Exception('Failed to save equal consumer allocation.');
                    }
                }
            }

            $connection->commit();
            $this->Flash->success('Expense added successfully!');

        } catch (\Exception $e) {
            $connection->rollback();
            $this->Flash->error($e->getMessage());
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
    }

    public function edit(int $id)
    {
        $this->request->allowMethod(['post', 'put']);
        
        $expensesTable = $this->fetchTable('Expenses');
        $expense = $expensesTable->get($id, ['contain' => ['GroupSessions']]);

        if ($expense->group_session->status !== 'open') {
            $this->Flash->error('Cannot edit expenses in a closed session.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
        }

        $identity = $this->Authentication->getIdentity();
        if ($identity && $expense->group_session->host_id !== $identity->id) {
            $this->Flash->error('Only the host can edit expenses.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
        }

        $data = $this->request->getData();
        $payerId = (int)($data['payer_id'] ?? 0);
        
        if (!$payerId) {
            $this->Flash->error('You must select who paid for this expense.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
        }

        $consumers = $data['consumers'] ?? [];
        if (empty($consumers) || !is_array($consumers)) {
            $this->Flash->error('You must select at least one participant to split the cost.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
        }

        $allocationsTable = $this->fetchTable('ExpenseAllocations');

        $expense->participant_id = $payerId;
        $expense->description = $data['description'];
        $expense->total_amount = (float)$data['total_amount'];
        $expense->split_type = $data['split_type'] ?? 'equal';
        if (isset($data['quantity']) && (int)$data['quantity'] > 0) {
            $expense->quantity = (int)$data['quantity'];
        }

        // Handle Image Upload
        $imageFile = $this->request->getUploadedFile('image');
        if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
            try {
                $tmpPath = $imageFile->getStream()->getMetadata('uri');
                $folder  = 'equisplit/receipts/session_' . $expense->session_id;
                $expense->image_url = $this->cloudinary->uploadReceipt($tmpPath, $folder);
            } catch (\Exception $e) {
                $this->Flash->error('Image upload failed: ' . $e->getMessage());
                return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
            }
        }

        $connection = $expensesTable->getConnection();
        $connection->begin();

        try {
            if (!$expensesTable->save($expense)) {
                throw new \Exception('Failed to update expense details.');
            }

            // Wipe all old allocations
            $allocationsTable->deleteAll(['expense_id' => $expense->id]);

            // 1. Record the Payer
            $payerAllocation = $allocationsTable->newEmptyEntity();
            $payerAllocation->expense_id = $expense->id;
            $payerAllocation->participant_id = $payerId;
            $payerAllocation->is_payer = true;
            $payerAllocation->amount_owed = 0.0;
            if (!$allocationsTable->save($payerAllocation)) {
                throw new \Exception('Failed to save payer allocation.');
            }

            // 2. Record the Consumers
            $splitType = $expense->split_type;
            
            if ($splitType === 'exact') {
                $exacts = $data['split_exacts'] ?? [];
                $sumExacts = 0.0;
                foreach ($consumers as $consumerId) {
                    $val = (float)($exacts[$consumerId] ?? 0);
                    $sumExacts += $val;
                    
                    $consumerAlloc = $allocationsTable->newEmptyEntity();
                    $consumerAlloc->expense_id = $expense->id;
                    $consumerAlloc->participant_id = (int)$consumerId;
                    $consumerAlloc->is_payer = false;
                    $consumerAlloc->amount_owed = round($val, 2);
                    
                    if (!$allocationsTable->save($consumerAlloc)) {
                        throw new \Exception('Failed to save exact consumer allocation.');
                    }
                }
                
                // Validate sum
                if (round($sumExacts, 2) !== round($expense->total_amount, 2)) {
                    throw new \Exception("Exact amounts (RM {$sumExacts}) do not sum up to the total amount (RM {$expense->total_amount}).");
                }
                
            } elseif ($splitType === 'percentage') {
                $percentages = $data['split_percentages'] ?? [];
                $sumPercentages = 0.0;
                $accumulatedAmount = 0.0;
                $numConsumers = count($consumers);
                $consumers = array_values($consumers);
                
                foreach ($consumers as $index => $consumerId) {
                    $pct = (float)($percentages[$consumerId] ?? 0);
                    $sumPercentages += $pct;
                    
                    $consumerAlloc = $allocationsTable->newEmptyEntity();
                    $consumerAlloc->expense_id = $expense->id;
                    $consumerAlloc->participant_id = (int)$consumerId;
                    $consumerAlloc->is_payer = false;
                    
                    if ($index === $numConsumers - 1) {
                        // Last consumer absorbs rounding remainder
                        $consumerAlloc->amount_owed = round($expense->total_amount - $accumulatedAmount, 2);
                    } else {
                        $amt = round(($expense->total_amount * $pct) / 100, 2);
                        $consumerAlloc->amount_owed = $amt;
                        $accumulatedAmount += $amt;
                    }
                    
                    if (!$allocationsTable->save($consumerAlloc)) {
                        throw new \Exception('Failed to save percentage consumer allocation.');
                    }
                }
                
                // Validate sum
                if (round($sumPercentages, 2) !== 100.00) {
                    throw new \Exception("Percentages do not sum to exactly 100%. Total was {$sumPercentages}%");
                }
                
            } else {
                // Default: Equal split
                $numConsumers = count($consumers);
                $amountPerConsumer = round($expense->total_amount / $numConsumers, 2);
                $accumulatedAmount = 0.0;
                $consumers = array_values($consumers);

                foreach ($consumers as $index => $consumerId) {
                    $consumerAlloc = $allocationsTable->newEmptyEntity();
                    $consumerAlloc->expense_id = $expense->id;
                    $consumerAlloc->participant_id = (int)$consumerId;
                    $consumerAlloc->is_payer = false;
                    
                    if ($index === $numConsumers - 1) {
                        // Last consumer absorbs the exact remainder
                        $consumerAlloc->amount_owed = round($expense->total_amount - $accumulatedAmount, 2);
                    } else {
                        $consumerAlloc->amount_owed = $amountPerConsumer;
                        $accumulatedAmount += $amountPerConsumer;
                    }
                    
                    if (!$allocationsTable->save($consumerAlloc)) {
                        throw new \Exception('Failed to save equal consumer allocation.');
                    }
                }
            }

            $connection->commit();
            $this->Flash->success('Expense updated successfully!');

        } catch (\Exception $e) {
            $connection->rollback();
            $this->Flash->error($e->getMessage());
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $expense->group_session->uuid]);
    }
}
