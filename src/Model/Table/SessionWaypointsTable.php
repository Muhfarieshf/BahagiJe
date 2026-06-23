<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class SessionWaypointsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('session_waypoints');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('GroupSessions', [
            'foreignKey' => 'session_id',
            'joinType' => 'INNER',
        ]);
        
        $this->hasMany('Expenses', [
            'foreignKey' => 'waypoint_id',
            'dependent' => false,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('type')
            ->inList('type', ['start', 'stop', 'toll', 'destination'])
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->decimal('lat')
            ->allowEmptyString('lat');

        $validator
            ->decimal('lng')
            ->allowEmptyString('lng');

        $validator
            ->integer('sort_order')
            ->notEmptyString('sort_order');

        return $validator;
    }

    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && !$entity->has('created_at')) {
            $entity->created_at = date('Y-m-d H:i:s');
        }
    }
}
