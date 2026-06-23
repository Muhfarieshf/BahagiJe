<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ReceiptsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('receipts');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new'
                ]
            ]
        ]);

        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
            'joinType' => 'INNER',
        ]);
        
        $this->belongsTo('Participants', [
            'foreignKey' => 'payer_id',
            'joinType' => 'INNER',
            'propertyName' => 'payer'
        ]);

        $this->hasMany('Expenses', [
            'foreignKey' => 'receipt_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('image_url')
            ->maxLength('image_url', 255)
            ->allowEmptyString('image_url');

        return $validator;
    }
}
