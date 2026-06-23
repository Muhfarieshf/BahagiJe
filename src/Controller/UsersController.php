<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Skip authorization for dashboard until policies are implemented
        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
    }
    public function profile()
    {
        $identity = $this->Authentication->getIdentity();
        $user = $this->fetchTable('Users')->get($identity->getIdentifier());

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            
            // Only allow updating name
            $user = $this->fetchTable('Users')->patchEntity($user, [
                'name' => $data['name'] ?? $user->name,
            ]);

            if ($this->fetchTable('Users')->save($user)) {
                // Update session identity so the navbar updates immediately
                $this->Authentication->setIdentity($user);
                $this->Flash->success('Profile updated successfully.');
                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('Unable to update your profile. Please check for errors.');
        }

        $this->set(compact('user'));
    }

    public function dashboard()
    {
        $user = $this->Authentication->getIdentity();

        // Sessions this user is hosting
        $hostedSessions = $this->fetchTable('GroupSessions')
            ->find()
            ->where(['host_id' => $user->id])
            ->contain([
                'Participants' => ['Users'],
                'Expenses',
                'SettlementTransactions' => [
                    'Debtors'   => ['Users'],
                    'Creditors' => ['Users'],
                    'PaymentProofs',
                ],
            ])
            ->orderBy(['created_at' => 'DESC'])
            ->all();

        // Sessions this user has joined (as participant, not host)
        $joinedSessions = $this->fetchTable('GroupSessions')
            ->find()
            ->matching('Participants', function ($q) use ($user) {
                return $q->where([
                    'Participants.user_id' => $user->id,
                    'Participants.role !=' => 'host',
                ]);
            })
            ->contain([
                'Participants' => ['Users'],
                'Expenses',
                'SettlementTransactions' => [
                    'Debtors'   => ['Users'],
                    'Creditors' => ['Users'],
                    'PaymentProofs',
                ],
            ])
            ->orderBy(['GroupSessions.created_at' => 'DESC'])
            ->all();

        // ── Debt Summary ─────────────────────────────────────────────────────
        // Find all participant records for this user across every session
        $participantsTable = $this->fetchTable('Participants');
        $myParticipants = $participantsTable->find()
            ->where(['user_id' => $user->id])
            ->select(['id'])
            ->all()
            ->map(fn($p) => $p->id)
            ->toArray();

        $myDebts  = [];
        $owedToMe = [];

        if (!empty($myParticipants)) {
            $settlementTable = $this->fetchTable('SettlementTransactions');

            // What I owe (I am the debtor)
            $myDebts = $settlementTable->find()
                ->where([
                    'SettlementTransactions.debtor_id IN'  => $myParticipants,
                    'SettlementTransactions.status IN'     => ['pending', 'claimed'],
                ])
                ->contain([
                    'Creditors' => ['Users'],
                    'GroupSessions',
                    'PaymentProofs',
                ])
                ->all()
                ->toArray();

            // What others owe me (I am the creditor)
            $owedToMe = $settlementTable->find()
                ->where([
                    'SettlementTransactions.creditor_id IN' => $myParticipants,
                    'SettlementTransactions.status IN'      => ['pending', 'claimed'],
                ])
                ->contain([
                    'Debtors' => ['Users'],
                    'GroupSessions',
                    'PaymentProofs',
                ])
                ->all()
                ->toArray();
        }

        // ── Stats ─────────────────────────────────────────────────────────────
        $allSessions   = array_merge($hostedSessions->toArray(), $joinedSessions->toArray());
        $totalSplit    = 0;
        $biggestExp    = 0;
        $totalExpCount = 0;

        foreach ($allSessions as $s) {
            foreach ($s->expenses ?? [] as $e) {
                $totalSplit    += $e->total_amount;
                $totalExpCount++;
                if ($e->total_amount > $biggestExp) {
                    $biggestExp = $e->total_amount;
                }
            }
        }

        $myStats = [
            'total_sessions'  => count($allSessions),
            'hosted'          => count($hostedSessions->toArray()),
            'total_split'     => $totalSplit,
            'biggest_expense' => $biggestExp,
            'total_expenses'  => $totalExpCount,
        ];

        $this->set(compact('user', 'hostedSessions', 'joinedSessions', 'myDebts', 'owedToMe', 'myStats'));
    }
}
