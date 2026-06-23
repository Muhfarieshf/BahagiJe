<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use App\Service\CloudinaryService;

class PaymentProofsController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Allow these actions since they might be used by guests (debtors can be guests)
        $this->Authentication->allowUnauthenticated(['upload', 'confirmSettlement', 'verify']);

        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
    }

    /**
     * Uploads a receipt for a specific settlement transaction.
     * Uses private authenticated Cloudinary URL.
     */
    public function upload($transactionId = null)
    {
        $this->request->allowMethod(['post']);
        $transactionsTable = $this->fetchTable('SettlementTransactions');
        
        $transactionId = (int)$transactionId;
        
        $query = $transactionsTable->find()->where(['SettlementTransactions.id' => $transactionId])->contain(['GroupSessions']);
        $sql = $query->sql();
        
        try {
            $transaction = $query->firstOrFail();
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error('Transaction not found. ID passed was: ' . h($transactionId));
            return $this->redirect($this->referer());
        }
        
        $session = $transaction->group_session;
        if ($session->status !== 'closed') {
            $this->Flash->error('You can only upload receipts for closed sessions.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
        }

        $receiptFile = $this->request->getData('receipt_file');
        
        if (!$receiptFile || $receiptFile->getError() !== UPLOAD_ERR_OK) {
            $this->Flash->error('Please select a valid image file to upload.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
        }

        try {
            $cloudinary = new CloudinaryService();
            $tmpPath = $receiptFile->getStream()->getMetadata('uri');
            
            // Upload to Cloudinary as private asset
            $folder = 'equisplit/payment_proofs/session_' . $session->id;
            [$secureUrl, $publicId] = $cloudinary->uploadPrivate($tmpPath, $folder);

            // Save the proof record
            $proofsTable = $this->fetchTable('PaymentProofs');
            $proof = $proofsTable->newEmptyEntity();
            $proof->session_id = $session->id;
            $proof->participant_id = $transaction->debtor_id;
            $proof->settlement_transaction_id = $transactionId;
            $proof->proof_url = $secureUrl;
            // For now, pending, but the creditor will confirm it.
            $proof->status = 'pending';
            
            if (!$proofsTable->save($proof)) {
                throw new \Exception('Failed to save payment proof record to the database.');
            }
            
            $this->Flash->success('Receipt uploaded successfully! Awaiting confirmation from the creditor.');

        } catch (\Exception $e) {
            $this->Flash->error($e->getMessage());
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
    }

    /**
     * Creditor confirms they received the payment.
     */
    public function confirmSettlement(int $transactionId)
    {
        $this->request->allowMethod(['post']);
        $identity = $this->Authentication->getIdentity();
        
        $transactionsTable = $this->fetchTable('SettlementTransactions');
        try {
            $transaction = $transactionsTable->get($transactionId, ['contain' => ['GroupSessions', 'Creditors']]);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error("Transaction not found. ID passed was: " . var_export($transactionId, true));
            return $this->redirect($this->referer());
        }
        
        $session = $transaction->group_session;
        
        // Ensure the current user is the creditor
        if ($identity) {
            if ($transaction->creditor->user_id !== $identity->id) {
                $this->Flash->error('Only the person receiving the money can confirm it.');
                return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
            }
        } else {
            // Guest check - verify via session participant cookie
            $guestParticipantId = $this->request->getSession()->read('Guest.participant_id');
            if (!$guestParticipantId || $guestParticipantId !== $transaction->creditor_id) {
                $this->Flash->error('Only the person receiving the money can confirm it.');
                return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
            }
        }

        // Also mark the associated proof as approved
        $proofsTable = $this->fetchTable('PaymentProofs');
        $proof = $proofsTable->find()->where(['settlement_transaction_id' => $transaction->id])->first();
        
        $transaction->status = 'settled';
        
        $connection = $transactionsTable->getConnection();
        $connection->begin();
        try {
            $transactionsTable->saveOrFail($transaction);
            if ($proof) {
                $proof->status = 'approved';
                $proofsTable->saveOrFail($proof);
            }
            $connection->commit();
            $this->Flash->success('Payment confirmed! The debt is now settled.');
        } catch (\Exception $e) {
            $connection->rollback();
            $this->Flash->error('Failed to confirm payment.');
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
    }

    /**
     * Verifies a payment proof (Host only)
     */
    public function verify($proofId = null, $action = null)
    {
        $this->request->allowMethod(['post']);
        $proofsTable = $this->fetchTable('PaymentProofs');
        $transactionsTable = $this->fetchTable('SettlementTransactions');
        
        try {
            $proof = $proofsTable->get($proofId, ['contain' => ['SettlementTransactions' => ['GroupSessions', 'Creditors']]]);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error('Payment proof not found.');
            return $this->redirect($this->referer());
        }

        $session = $proof->settlement_transaction->group_session;
        
        $identity = $this->Authentication->getIdentity();
        
        $isCreditor = false;
        if ($identity && $proof->settlement_transaction->creditor->user_id === $identity->id) {
            $isCreditor = true;
        } else {
            $guestParticipantId = $this->request->getSession()->read('Guest.participant_id');
            if ($guestParticipantId && $guestParticipantId === $proof->settlement_transaction->creditor_id) {
                $isCreditor = true;
            }
        }

        if (!$isCreditor) {
            $this->Flash->error('Only the person receiving the money can verify receipts.');
            return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
        }

        if ($action === 'approve') {
            $proof->status = 'approved';
            $transaction = $proof->settlement_transaction;
            $transaction->status = 'settled';
            
            $connection = $proofsTable->getConnection();
            $connection->begin();
            try {
                $proofsTable->saveOrFail($proof);
                $transactionsTable->saveOrFail($transaction);
                $connection->commit();
                $this->Flash->success('Receipt approved and debt marked as settled.');
            } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
                $connection->rollback();
                $errors = json_encode($e->getEntity()->getErrors());
                $this->Flash->error('Could not approve receipt. Validation errors: ' . $errors);
            } catch (\Exception $e) {
                $connection->rollback();
                $this->Flash->error('Could not approve receipt. ' . $e->getMessage());
            }
        } elseif ($action === 'reject') {
            $proof->status = 'rejected';
            if ($proofsTable->save($proof)) {
                $this->Flash->success('Receipt rejected.');
            } else {
                $this->Flash->error('Could not reject receipt.');
            }
        } else {
            $this->Flash->error('Invalid action.');
        }

        return $this->redirect(['controller' => 'GroupSessions', 'action' => 'view', $session->uuid]);
    }
}
