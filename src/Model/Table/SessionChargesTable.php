<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SessionChargesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('session_charges');
        $this->setDisplayField('charge_name');
        $this->setPrimaryKey('id');

        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('charge_name')
            ->inList('charge_type', ['percentage', 'flat'])
            ->numeric('charge_value')
            ->notEmptyString('charge_value')
            ->inList('applies_to', ['proportional', 'equal']);

        return $validator;
    }
}
