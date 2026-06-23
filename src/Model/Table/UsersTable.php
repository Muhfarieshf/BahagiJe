<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\Query\SelectQuery;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('UserPaymentMethods', [
            'foreignKey' => 'user_id',
            'dependent'  => true,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', 'Name is required.')
            ->notEmptyString('email', 'Email is required.')
            ->email('email', false, 'Please enter a valid email address.')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table', 'message' => 'This email is already in use.'])
            ->notEmptyString('google_id', 'Google ID is required.')
            ->add('google_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table', 'message' => 'This Google account is already linked.']);

        return $validator;
    }

    public function findByGoogleId(SelectQuery $query, string $googleId): SelectQuery
    {
        return $query->where(['google_id' => $googleId]);
    }
}
