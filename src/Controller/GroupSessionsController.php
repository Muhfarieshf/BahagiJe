<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use App\Service\CalculationEngineService;
use App\Service\NetDebtSettlementService;

class GroupSessionsController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access only to the join and view flows (for guests)
        $this->Authentication->allowUnauthenticated(['join', 'view']);

        // Skip authorization checks (policy-based auth comes in Task 7)
        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
    }

    // -------------------------------------------------------------------------
    // create() — Host creates a new group session
    // -------------------------------------------------------------------------
    public function create()
    {
        $identity = $this->Authentication->getIdentity();
        $session  = $this->fetchTable('GroupSessions')->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $session = $this->fetchTable('GroupSessions')->patchEntity($session, [
                'name'            => $data['name'],
                'host_id'         => $identity->id,
                'preset_type'     => $data['preset_type'],
                'max_participants' => (int)$data['max_participants'],
            ]);

            if ($this->fetchTable('GroupSessions')->save($session)) {
                // Auto-join the host as a participant with role = 'host'
                $participantsTable = $this->fetchTable('Participants');
                $hostParticipant   = $participantsTable->newEmptyEntity();
                $hostParticipant   = $participantsTable->patchEntity($hostParticipant, [
                    'session_id' => $session->id,
                    'user_id'    => $identity->id,
                    'role'       => 'host',
                    'guest_name' => null,
                ]);
                $participantsTable->save($hostParticipant);

                // Save session charges if preset provides them
                $this->_saveSessionCharges($session->id, $data);

                $this->Flash->success('Session "' . $session->name . '" created successfully!');
                return $this->redirect(['action' => 'view', $session->uuid]);
            }

            $this->Flash->error('Could not create the session. Please check the form and try again.');
        }

        // Preset labels for the view
        $presets = [
            'dining'    => 'Food & Dining (SST%, Proportional Split)',
            'grocery'   => 'Grocery Run (Itemized receipt entry, 0% service charge)',
            'road_trip' => 'Road Trip (Flat Amounts, Equal Split)',
            'long_trip' => 'Long Trip (Collaborative Ledger, Net-Debt)',
            'custom'    => 'Custom (Manual Configuration)',
        ];

        // Allow pre-selecting a preset via ?preset= query param (from dashboard preset cards)
        $preSelectedPreset = $this->request->getQuery('preset');
        if ($preSelectedPreset && array_key_exists($preSelectedPreset, $presets)) {
            $session->preset_type = $preSelectedPreset;
        }

        $this->set(compact('session', 'presets', 'preSelectedPreset'));
    }

    // -------------------------------------------------------------------------
    // view() — Show session details and QR code (host and participants)
    // -------------------------------------------------------------------------
    public function view(string $uuid)
    {
        $identity      = $this->Authentication->getIdentity();
        $guestParticipantId = $this->request->getSession()->read('Guest.participant_id');
        $sessionsTable = $this->fetchTable('GroupSessions');

        $session = $sessionsTable->find()
            ->where(['uuid' => $uuid])
            ->contain(['Participants' => ['Users'], 'SessionCharges'])
            ->first();

        if (!$session) {
            $this->Flash->error('Session not found.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        // Check if the current user is a participant
        $isParticipant = false;
        $currentParticipant = null;
        foreach ($session->participants as $p) {
            if ($identity && $p->user_id === $identity->id) {
                $isParticipant      = true;
                $currentParticipant = $p;
                break;
            } elseif ($guestParticipantId && $p->id === $guestParticipantId) {
                $isParticipant      = true;
                $currentParticipant = $p;
                break;
            }
        }

        // Only participants (or the host) can view the session
        if (!$isParticipant) {
            $this->Flash->error('You are not a participant in this session.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        $isHost = false;
        if ($identity) {
            $isHost = ($session->host_id === $identity->id);
        }

        $expenses = $this->fetchTable('Expenses')->find()
            ->where(['Expenses.session_id' => $session->id])
            ->contain([
                'Participants' => ['Users'], 
                'ExpenseAllocations' => ['Participants' => ['Users']],
                'Receipts'
            ])
            ->all();

        $calcEngine = new CalculationEngineService();
        $participantTotals = $calcEngine->calculateParticipantTotals($session->id);

        $sessionGrandTotal = 0.0;
        foreach ($participantTotals as $totals) {
            $sessionGrandTotal += $totals['grand_total'];
        }

        $settlements = [];
        $paymentProofs = [];
        if ($session->status === 'closed') {
            $settlements = $this->fetchTable('SettlementTransactions')->find()
                ->where(['SettlementTransactions.session_id' => $session->id])
                ->contain([
                    'Debtors' => ['Users'], 
                    'Creditors' => ['Users' => ['UserPaymentMethods']],
                    'PaymentProofs'
                ])
                ->all();
                
            $paymentProofs = $this->fetchTable('PaymentProofs')->find()
                ->where(['session_id' => $session->id])
                ->toArray();
        }

        $waypoints = [];
        if ($session->preset_type === 'road_trip') {
            $rawWaypoints = $this->fetchTable('SessionWaypoints')->find()
                ->where(['session_id' => $session->id])
                ->orderBy(['sort_order' => 'ASC', 'id' => 'ASC'])
                ->all()
                ->toArray();
                
            usort($rawWaypoints, function($a, $b) {
                $getWeight = function($type) {
                    if ($type === 'start') return 1;
                    if ($type === 'destination') return 3;
                    return 2;
                };
                
                $wA = $getWeight($a->type);
                $wB = $getWeight($b->type);
                
                if ($wA !== $wB) {
                    return $wA <=> $wB;
                }
                
                if ($a->sort_order !== $b->sort_order) {
                    return $a->sort_order <=> $b->sort_order;
                }
                
                return $a->id <=> $b->id;
            });
            
            $waypoints = $rawWaypoints;
        }

        $this->set(compact(
            'session',
            'expenses',
            'isHost',
            'isParticipant',
            'currentParticipant',
            'participantTotals',
            'sessionGrandTotal',
            'settlements',
            'paymentProofs',
            'waypoints'
        ));
    }

    // -------------------------------------------------------------------------
    // join() — Join a session via QR code link (guests & registered users)
    // -------------------------------------------------------------------------
    public function join(string $uuid)
    {
        $sessionsTable = $this->fetchTable('GroupSessions');
        $session = $sessionsTable->find()
            ->where(['uuid' => $uuid])
            ->contain(['Participants'])
            ->first();

        if (!$session) {
            $this->Flash->error('Session not found. Please scan a valid QR code.');
            return $this->redirect(['controller' => 'Auth', 'action' => 'login']);
        }

        if ($session->status !== 'open') {
            $this->Flash->error('This session is no longer accepting new participants.');
            return $this->redirect(['controller' => 'Auth', 'action' => 'login']);
        }

        // Enforce max participants
        $currentCount = count($session->participants);
        if ($currentCount >= $session->max_participants) {
            $this->Flash->error('This session has reached the maximum number of participants.');
            return $this->redirect(['controller' => 'Auth', 'action' => 'login']);
        }

        $identity          = $this->Authentication->getIdentity();
        $participantsTable = $this->fetchTable('Participants');

        // Prevent duplicate joins for authenticated users
        if ($identity) {
            $alreadyJoined = false;
            foreach ($session->participants as $p) {
                if ($p->user_id === $identity->id) {
                    $alreadyJoined = true;
                    break;
                }
            }
            if ($alreadyJoined) {
                $this->Flash->success('You are already in this session.');
                return $this->redirect(['action' => 'view', $uuid]);
            }
        }

        if ($this->request->is('post')) {
            $data       = $this->request->getData();
            $participant = $participantsTable->newEmptyEntity();

            if ($identity) {
                // Registered user join
                $participant = $participantsTable->patchEntity($participant, [
                    'session_id' => $session->id,
                    'user_id'    => $identity->id,
                    'role'       => 'registered',
                    'guest_name' => null,
                ]);
            } else {
                // Guest join — name required
                if (empty(trim((string)($data['guest_name'] ?? '')))) {
                    $this->Flash->error('Please enter your name to join as a guest.');
                    $this->set(compact('session'));
                    return;
                }
                $participant = $participantsTable->patchEntity($participant, [
                    'session_id' => $session->id,
                    'user_id'    => null,
                    'role'       => 'guest',
                    'guest_name' => trim((string)$data['guest_name']),
                ]);
            }

            if ($participantsTable->save($participant)) {
                $this->Flash->success('You have joined the session!');
                if ($identity) {
                    return $this->redirect(['action' => 'view', $uuid]);
                }
                
                // For guests, store their participant ID in the session and redirect them to view
                $this->request->getSession()->write('Guest.participant_id', $participant->id);
                return $this->redirect(['action' => 'view', $uuid]);
            }

            $this->Flash->error('Could not join the session. Please try again.');
        }

        $this->set(compact('session', 'identity'));
    }

    // -------------------------------------------------------------------------
    // lockSession() — Prevents new expenses, prepares for preview
    // -------------------------------------------------------------------------
    public function lockSession(string $uuid)
    {
        $this->request->allowMethod(['post']);
        $identity = $this->Authentication->getIdentity();
        $sessionsTable = $this->fetchTable('GroupSessions');
        
        $session = $sessionsTable->find()->where(['uuid' => $uuid])->first();
        if (!$session || $session->host_id !== $identity->id) {
            $this->Flash->error('Session not found or permission denied.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        if ($session->status === 'open') {
            $session->status = 'locked';
            if ($sessionsTable->save($session)) {
                $this->Flash->success('Session is now locked for calculations. Participants cannot add expenses.');
            }
        }
        
        return $this->redirect(['action' => 'previewClose', $session->uuid]);
    }

    // -------------------------------------------------------------------------
    // unlockSession() — Reverts session back to open
    // -------------------------------------------------------------------------
    public function unlockSession(string $uuid)
    {
        $this->request->allowMethod(['post']);
        $identity = $this->Authentication->getIdentity();
        $sessionsTable = $this->fetchTable('GroupSessions');
        
        $session = $sessionsTable->find()->where(['uuid' => $uuid])->first();
        if (!$session || $session->host_id !== $identity->id) {
            $this->Flash->error('Session not found or permission denied.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        if ($session->status === 'locked') {
            $session->status = 'open';
            if ($sessionsTable->save($session)) {
                $this->Flash->success('Session unlocked. Expenses can be added again.');
            }
        }
        
        return $this->redirect(['action' => 'view', $session->uuid]);
    }

    // -------------------------------------------------------------------------
    // previewClose() — Host previews settlements before closing
    // -------------------------------------------------------------------------
    public function previewClose(string $uuid)
    {
        $identity      = $this->Authentication->getIdentity();
        $sessionsTable = $this->fetchTable('GroupSessions');
        $session = $sessionsTable->find()
            ->where(['uuid' => $uuid])
            ->contain(['Participants' => ['Users']])
            ->first();

        if (!$session) {
            $this->Flash->error('Session not found.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        if ($session->host_id !== $identity->id) {
            $this->Flash->error('Only the host can close the session.');
            return $this->redirect(['action' => 'view', $session->uuid]);
        }

        if ($session->status !== 'locked') {
            $this->Flash->error('Session must be locked before previewing closure.');
            return $this->redirect(['action' => 'view', $session->uuid]);
        }

        $settlementService = new NetDebtSettlementService();
        $transactions = $settlementService->calculateSettlements($session->id);

        $this->set(compact('session', 'transactions'));
    }

    // -------------------------------------------------------------------------
    // close() — Host closes the session (POST only)
    // -------------------------------------------------------------------------
    public function close(int $id)
    {
        $this->request->allowMethod(['post']);

        $identity      = $this->Authentication->getIdentity();
        $sessionsTable = $this->fetchTable('GroupSessions');
        $session       = $sessionsTable->get($id);

        // Only the host can close the session
        if ($session->host_id !== $identity->id) {
            $this->Flash->error('Only the session host can close this session.');
            return $this->redirect(['action' => 'view', $session->uuid]);
        }

        if ($session->status === 'closed') {
            $this->Flash->warning('This session is already closed.');
            return $this->redirect(['action' => 'view', $session->uuid]);
        }

        $session->status    = 'closed';
        $session->closed_at = date('Y-m-d H:i:s');

        $connection = $sessionsTable->getConnection();
        $connection->begin();

        try {
            if (!$sessionsTable->save($session)) {
                throw new \Exception('Failed to close session.');
            }

            // Generate and save settlement transactions
            $settlementService = new NetDebtSettlementService();
            $transactions = $settlementService->calculateSettlements($session->id);

            $transactionsTable = $this->fetchTable('SettlementTransactions');
            foreach ($transactions as $txn) {
                $entity = $transactionsTable->newEmptyEntity();
                $entity->session_id = $session->id;
                $entity->debtor_id = $txn['debtor_id'];
                $entity->creditor_id = $txn['creditor_id'];
                $entity->amount = $txn['amount'];
                $entity->status = 'pending';
                if (!$transactionsTable->save($entity)) {
                    throw new \Exception('Failed to save settlement transaction.');
                }
            }

            $connection->commit();
            $this->Flash->success('Session closed successfully and settlements generated.');
        } catch (\Exception $e) {
            $connection->rollback();
            $this->Flash->error('Could not close the session. ' . $e->getMessage());
        }

        return $this->redirect(['action' => 'view', $session->uuid]);
    }

    // -------------------------------------------------------------------------
    // _saveSessionCharges() — Private: save charges from the create form
    // -------------------------------------------------------------------------
    protected function _saveSessionCharges(int $sessionId, array $data): void
    {
        $chargesTable = $this->fetchTable('SessionCharges');

        // Charges are submitted as an array from the form
        $charges = $data['charges'] ?? [];
        foreach ($charges as $charge) {
            if (empty($charge['charge_name']) || empty($charge['charge_value'])) {
                continue;
            }
            $entity = $chargesTable->newEmptyEntity();
            $entity = $chargesTable->patchEntity($entity, [
                'session_id'   => $sessionId,
                'charge_name'  => $charge['charge_name'],
                'charge_type'  => $charge['charge_type'],
                'charge_value' => (float)$charge['charge_value'],
                'applies_to'   => $charge['applies_to'],
            ]);
            $chargesTable->save($entity);
        }
    }

    // -------------------------------------------------------------------------
    // delete() — Completely delete a session and all associated data
    // -------------------------------------------------------------------------
    public function delete(string $uuid)
    {
        $this->request->allowMethod(['post', 'delete']);
        $identity = $this->Authentication->getIdentity();

        $session = $this->fetchTable('GroupSessions')->find()
            ->where(['uuid' => $uuid])
            ->first();

        if (!$session) {
            $this->Flash->error('Session not found.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        // Security: Only host can delete
        if ($session->host_id !== $identity->id) {
            $this->Flash->error('Only the host can delete this session.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        // Prevent deletion of closed sessions that have pending/unsettled transactions
        if ($session->status === 'closed') {
            $unsettled = $this->fetchTable('SettlementTransactions')->find()
                ->where([
                    'session_id' => $session->id,
                    'status !=' => 'settled'
                ])
                ->count();
            
            if ($unsettled > 0) {
                $this->Flash->error('Cannot delete a closed session that still has pending or unpaid settlements.');
                return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
            }
        }

        if ($this->fetchTable('GroupSessions')->delete($session)) {
            $this->Flash->success('Session completely deleted.');
        } else {
            $this->Flash->error('Failed to delete the session.');
        }

        return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
    }

    // -------------------------------------------------------------------------
    // bulkDelete() — Completely delete selected sessions from the dashboard
    // -------------------------------------------------------------------------
    public function bulkDelete()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->Authentication->getIdentity();
        $uuids = $this->request->getData('session_uuids');

        if (empty($uuids) || !is_array($uuids)) {
            $this->Flash->error('No sessions selected for deletion.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        $sessionsTable = $this->fetchTable('GroupSessions');
        $sessions = $sessionsTable->find()
            ->where(['uuid IN' => $uuids])
            ->all();

        $deletedCount = 0;
        $skippedCount = 0;
        foreach ($sessions as $session) {
            // Security: Only host can delete
            if ($session->host_id === $identity->id) {
                // Prevent deletion of closed sessions with pending settlements
                $canDelete = true;
                if ($session->status === 'closed') {
                    $unsettled = $sessionsTable->SettlementTransactions->find()
                        ->where([
                            'session_id' => $session->id,
                            'status !=' => 'settled'
                        ])
                        ->count();
                    if ($unsettled > 0) {
                        $canDelete = false;
                        $skippedCount++;
                    }
                }

                if ($canDelete && $sessionsTable->delete($session)) {
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0 && $skippedCount === 0) {
            $this->Flash->success("Successfully deleted {$deletedCount} session(s).");
        } elseif ($deletedCount > 0 && $skippedCount > 0) {
            $this->Flash->success("Deleted {$deletedCount} session(s), but skipped {$skippedCount} unpaid closed session(s).");
        } elseif ($deletedCount === 0 && $skippedCount > 0) {
            $this->Flash->error("Could not delete {$skippedCount} session(s) because they still have pending or unpaid settlements.");
        } else {
            $this->Flash->error('Failed to delete selected sessions or permission denied.');
        }

        return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
    }

    // -------------------------------------------------------------------------
    // addWaypoint() — Adds a waypoint to a road trip session
    // -------------------------------------------------------------------------
    public function addWaypoint(string $uuid)
    {
        $this->request->allowMethod(['post']);
        $identity = $this->Authentication->getIdentity();

        $session = $this->fetchTable('GroupSessions')->find()
            ->where(['uuid' => $uuid])
            ->first();

        if (!$session || $session->status !== 'open') {
            $this->Flash->error('Invalid session or session is not open.');
            return $this->redirect($this->referer());
        }

        $isParticipant = false;
        if ($identity) {
            $isParticipant = $this->fetchTable('Participants')->exists([
                'session_id' => $session->id,
                'user_id' => $identity->id
            ]);
        }

        if (!$identity || ($session->host_id !== $identity->id && !$isParticipant)) {
            $this->Flash->error('Only session participants can add waypoints.');
            return $this->redirect(['action' => 'view', $session->uuid]);
        }

        $waypointsTable = $this->fetchTable('SessionWaypoints');
        
        $data = $this->request->getData();
        
        if (in_array($data['type'], ['start', 'destination'])) {
            $existing = $waypointsTable->find()
                ->where([
                    'session_id' => $session->id,
                    'type' => $data['type']
                ])
                ->first();
                
            if ($existing) {
                $this->Flash->error('A ' . ucfirst($data['type']) . ' point already exists. Please delete it first to change it.');
                return $this->redirect(['action' => 'view', $session->uuid]);
            }
        }

        // Determine sort order
        $maxOrder = $waypointsTable->find()
            ->where(['session_id' => $session->id])
            ->select(['max_order' => $waypointsTable->find()->func()->max('sort_order')])
            ->first();
        
        $nextOrder = ($maxOrder->max_order ?? -1) + 1;

        $waypoint = $waypointsTable->newEmptyEntity();
        $waypoint->session_id = $session->id;
        $waypoint->type = $data['type'];
        $waypoint->name = $data['name'];
        $waypoint->lat = !empty($data['lat']) ? $data['lat'] : null;
        $waypoint->lng = !empty($data['lng']) ? $data['lng'] : null;
        $waypoint->sort_order = $nextOrder;

        if ($waypointsTable->save($waypoint)) {
            $this->Flash->success('Waypoint added successfully.');
        } else {
            $this->Flash->error('Could not save waypoint.');
        }

        return $this->redirect(['action' => 'view', $session->uuid]);
    }

    // -------------------------------------------------------------------------
    // deleteWaypoint() — Deletes a waypoint
    // -------------------------------------------------------------------------
    public function deleteWaypoint(int $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $identity = $this->Authentication->getIdentity();
        
        $waypointsTable = $this->fetchTable('SessionWaypoints');
        $waypoint = $waypointsTable->find()
            ->where(['SessionWaypoints.id' => $id])
            ->contain(['GroupSessions'])
            ->first();

        if (!$waypoint) {
            $this->Flash->error('Waypoint not found.');
            return $this->redirect($this->referer());
        }

        $session = $waypoint->group_session;

        if ($session->host_id !== $identity->id || $session->status !== 'open') {
            $this->Flash->error('You cannot delete this waypoint.');
            return $this->redirect(['action' => 'view', $session->uuid]);
        }

        if ($waypointsTable->delete($waypoint)) {
            $this->Flash->success('Waypoint removed.');
        } else {
            $this->Flash->error('Failed to remove waypoint.');
        }

        return $this->redirect(['action' => 'view', $session->uuid]);
    }
}
