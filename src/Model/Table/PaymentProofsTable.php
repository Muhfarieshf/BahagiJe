<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class PaymentProofsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('payment_proofs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
            'joinType' => 'INNER',
        ]);
        
        $this->belongsTo('Participants', [
            'foreignKey' => 'participant_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('SettlementTransactions', [
            'foreignKey' => 'settlement_transaction_id',
            'joinType'   => 'LEFT',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('proof_url')
            ->inList('status', ['pending', 'approved', 'rejected']);

        return $validator;
    }

    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && !$entity->has('submitted_at')) {
            $entity->submitted_at = date('Y-m-d H:i:s');
        }
    }
}
