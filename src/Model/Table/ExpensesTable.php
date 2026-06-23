<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class ExpensesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('expenses');
        $this->setDisplayField('description');
        $this->setPrimaryKey('id');

        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
            'joinType' => 'INNER',
        ]);
        
        $this->belongsTo('Receipts', [
            'foreignKey' => 'receipt_id',
            'joinType' => 'LEFT',
        ]);
        
        $this->belongsTo('Participants', [
            'foreignKey' => 'participant_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('ExpenseAllocations', [
            'foreignKey' => 'expense_id',
            'dependent' => true,
        ]);

        $this->belongsTo('SessionWaypoints', [
            'foreignKey' => 'waypoint_id',
            'joinType' => 'LEFT',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('description')
            ->numeric('total_amount')
            ->notEmptyString('total_amount')
            ->inList('expense_type', ['personal', 'group'])
            ->scalar('split_type')
            ->notEmptyString('split_type');

        $validator
            ->scalar('image_url')
            ->maxLength('image_url', 255)
            ->allowEmptyString('image_url');

        return $validator;
    }

    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew()) {
            $entity->created_at = date('Y-m-d H:i:s');
        }
    }
}
