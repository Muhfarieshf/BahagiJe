<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class GroupSessionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('group_sessions');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        // Associations
        $this->belongsTo('Hosts', [
            'className'  => 'Users',
            'foreignKey' => 'host_id',
        ]);

        $this->hasMany('Participants', [
            'foreignKey' => 'session_id',
            'dependent'  => true,
        ]);

        $this->hasMany('SessionCharges', [
            'foreignKey' => 'session_id',
            'dependent'  => true,
        ]);

        $this->hasMany('Expenses', [
            'foreignKey' => 'session_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('Receipts', [
            'foreignKey' => 'session_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('PaymentProofs', [
            'foreignKey' => 'session_id',
            'dependent'  => true,
        ]);

        $this->hasMany('SettlementTransactions', [
            'foreignKey' => 'session_id',
            'dependent'  => true,
        ]);

        $this->hasMany('SessionNotifications', [
            'foreignKey' => 'session_id',
            'dependent'  => true,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', 'Session name is required.')
            ->maxLength('name', 150, 'Session name cannot exceed 150 characters.')
            ->inList('preset_type', ['dining', 'road_trip', 'long_trip', 'custom', 'grocery'], 'Invalid preset type.')
            ->integer('max_participants', 'Max participants must be a number.')
            ->greaterThanOrEqual('max_participants', 2, 'Session must allow at least 2 participants.')
            ->lessThanOrEqual('max_participants', 50, 'Session cannot exceed 50 participants.');

        return $validator;
    }

    /**
     * Auto-generate UUID and set created_at before saving a new session.
     */
    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew()) {
            // Generate a UUID v4
            $entity->uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            $entity->created_at = date('Y-m-d H:i:s');
            $entity->status = 'open';
        }
    }
}
