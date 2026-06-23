<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Service\CloudinaryService;

class ReceiptsController extends AppController
{
    private CloudinaryService $cloudinary;

    public function initialize(): void
    {
        parent::initialize();
        $this->cloudinary = new CloudinaryService();
        
        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
        $this->Authentication->allowUnauthenticated(['add', 'delete']);
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
            $this->Flash->error('Cannot add receipts to a closed or locked session.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
        }

        $data = $this->request->getData();
        $payerId = (int)($data['payer_id'] ?? 0);
        $receiptName = trim($data['name'] ?? '');
        
        if (!$payerId || empty($receiptName)) {
            $this->Flash->error('You must provide a receipt name and select who paid.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
        }

        $items = $data['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            $this->Flash->error('You must add at least one item to the receipt.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
        }

        $receiptsTable = $this->fetchTable('Receipts');
        $expensesTable = $this->fetchTable('Expenses');
        $allocationsTable = $this->fetchTable('ExpenseAllocations');

        $receipt = $receiptsTable->newEmptyEntity();
        $receipt->session_id = $session->id;
        $receipt->payer_id = $payerId;
        $receipt->name = $receiptName;

        // Handle Image Upload
        $imageFile = $this->request->getUploadedFile('image');
        if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
            try {
                $tmpPath = $imageFile->getStream()->getMetadata('uri');
                $folder  = 'equisplit/receipts/session_' . $session->id;
                $receipt->image_url = $this->cloudinary->uploadReceipt($tmpPath, $folder);
            } catch (\Exception $e) {
                $this->Flash->error('Image upload failed: ' . $e->getMessage());
                return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
            }
        }

        $connection = $receiptsTable->getConnection();
        $connection->begin();

        try {
            if (!$receiptsTable->save($receipt)) {
                throw new \Exception('Failed to save receipt details.');
            }

            foreach ($items as $item) {
                $consumers = $item['consumers'] ?? [];
                if (empty($consumers) || !is_array($consumers)) {
                    throw new \Exception("Item '{$item['description']}' is missing consumers.");
                }

                $quantity = (int)($item['quantity'] ?? 1);
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $totalAmount = $quantity * $unitPrice;

                if ($totalAmount <= 0) {
                    throw new \Exception("Item '{$item['description']}' must have a valid amount.");
                }

                $expense = $expensesTable->newEmptyEntity();
                $expense->session_id = $session->id;
                $expense->participant_id = $payerId;
                $expense->receipt_id = $receipt->id;
                $expense->waypoint_id = !empty($data['waypoint_id']) ? (int)$data['waypoint_id'] : null;
                $expense->description = $item['description'];
                $expense->total_amount = $totalAmount;
                $expense->quantity = $quantity;
                $expense->expense_type = 'group';
                $expense->split_type = $item['split_type'] ?? 'equal';

                if (!$expensesTable->save($expense)) {
                    throw new \Exception("Failed to save item '{$item['description']}'.");
                }

                // 1. Record the Payer
                $payerAllocation = $allocationsTable->newEmptyEntity();
                $payerAllocation->expense_id = $expense->id;
                $payerAllocation->participant_id = $payerId;
                $payerAllocation->is_payer = true;
                $payerAllocation->amount_owed = 0.0;
                if (!$allocationsTable->save($payerAllocation)) {
                    throw new \Exception("Failed to save payer allocation for '{$item['description']}'.");
                }

                // 2. Record the Consumers
                $splitType = $expense->split_type;
                
                if ($splitType === 'exact') {
                    $exacts = $item['split_exacts'] ?? [];
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
                            throw new \Exception("Failed to save exact consumer allocation for '{$item['description']}'.");
                        }
                    }
                    
                    if (round($sumExacts, 2) !== round($expense->total_amount, 2)) {
                        throw new \Exception("Exact amounts (RM {$sumExacts}) do not sum up to the total amount (RM {$expense->total_amount}) for item '{$item['description']}'.");
                    }
                    
                } elseif ($splitType === 'percentage') {
                    $percentages = $item['split_percentages'] ?? [];
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
                            $consumerAlloc->amount_owed = round($expense->total_amount - $accumulatedAmount, 2);
                        } else {
                            $amt = round(($expense->total_amount * $pct) / 100, 2);
                            $consumerAlloc->amount_owed = $amt;
                            $accumulatedAmount += $amt;
                        }
                        
                        if (!$allocationsTable->save($consumerAlloc)) {
                            throw new \Exception("Failed to save percentage consumer allocation for '{$item['description']}'.");
                        }
                    }
                    
                    if (round($sumPercentages, 2) !== 100.00) {
                        throw new \Exception("Percentages do not sum to exactly 100% for item '{$item['description']}'.");
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
                            $consumerAlloc->amount_owed = round($expense->total_amount - $accumulatedAmount, 2);
                        } else {
                            $consumerAlloc->amount_owed = $amountPerConsumer;
                            $accumulatedAmount += $amountPerConsumer;
                        }
                        
                        if (!$allocationsTable->save($consumerAlloc)) {
                            throw new \Exception("Failed to save equal consumer allocation for '{$item['description']}'.");
                        }
                    }
                }
            }

            $connection->commit();
            $this->Flash->success('Receipt added successfully.');

        } catch (\Exception $e) {
            $connection->rollback();
            $this->Flash->error($e->getMessage());
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $sessionUuid]);
    }

    public function delete(int $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $receiptsTable = $this->fetchTable('Receipts');
        $receipt = $receiptsTable->get($id, ['contain' => ['GroupSessions']]);

        if ($receipt->group_session->status !== 'open') {
            $this->Flash->error('Cannot delete receipts from a closed session.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $receipt->group_session->uuid]);
        }

        $identity = $this->Authentication->getIdentity();
        if ($identity && $receipt->group_session->host_id !== $identity->id) {
            $this->Flash->error('Only the host can delete receipts.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $receipt->group_session->uuid]);
        }

        if ($receiptsTable->delete($receipt)) {
            $this->Flash->success('Receipt and all its line items deleted successfully.');
        } else {
            $this->Flash->error('Failed to delete receipt.');
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $receipt->group_session->uuid]);
    }
}
