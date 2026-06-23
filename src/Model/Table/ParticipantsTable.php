<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class ParticipantsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('participants');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        // Associations
        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);

        $this->hasMany('Expenses', [
            'foreignKey' => 'participant_id',
        ]);

        $this->hasMany('ExpenseAllocations', [
            'foreignKey' => 'participant_id',
        ]);

        $this->hasMany('PaymentProofs', [
            'foreignKey' => 'participant_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->inList('role', ['host', 'registered', 'guest'], 'Invalid participant role.')
            ->allowEmptyString('guest_name')
            ->allowEmptyString('user_id');

        return $validator;
    }

    /**
     * Set joined_at programmatically before saving a new participant.
     */
    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew()) {
            $entity->joined_at = date('Y-m-d H:i:s');
        }
    }
}
