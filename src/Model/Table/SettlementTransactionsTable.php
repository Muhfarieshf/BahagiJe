<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class SettlementTransactionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('settlement_transactions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
            'joinType' => 'INNER',
        ]);
        
        $this->belongsTo('Debtors', [
            'className' => 'Participants',
            'foreignKey' => 'debtor_id',
            'joinType' => 'INNER',
        ]);
        
        $this->belongsTo('Creditors', [
            'className' => 'Participants',
            'foreignKey' => 'creditor_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('PaymentProofs', [
            'foreignKey' => 'settlement_transaction_id',
            'dependent'  => false,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->numeric('amount')
            ->notEmptyString('amount')
            ->inList('status', ['pending', 'settled', 'unresolved', 'claimed']);

        return $validator;
    }

    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && !$entity->has('created_at')) {
            $entity->created_at = date('Y-m-d H:i:s');
        }
    }
}
