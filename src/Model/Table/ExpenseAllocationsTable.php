<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ExpenseAllocationsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('expense_allocations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Expenses', [
            'foreignKey' => 'expense_id',
            'joinType' => 'INNER',
        ]);
        
        $this->belongsTo('Participants', [
            'foreignKey' => 'participant_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->numeric('amount_owed')
            ->notEmptyString('amount_owed')
            ->boolean('is_payer')
            ->notEmptyString('is_payer');

        return $validator;
    }
}
