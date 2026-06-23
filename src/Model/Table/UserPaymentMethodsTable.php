<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

class UserPaymentMethodsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_payment_methods');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType'   => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $allowedTypes = ['bank_transfer', 'duitnow_qr', 'duitnow_id', 'tng', 'paypal'];

        $validator
            ->notEmptyString('method_type', 'Please select a payment method type.')
            ->inList('method_type', $allowedTypes, 'Invalid payment method type.');

        // account_value required for all non-QR types
        $validator->add('account_value', 'requiredForNonQR', [
            'rule' => function ($value, $context) {
                $type = $context['data']['method_type'] ?? '';
                if ($type !== 'duitnow_qr' && empty($value)) {
                    return false;
                }
                return true;
            },
            'message' => 'Account number / phone / email is required.',
        ]);

        return $validator;
    }

    public function beforeSave(EventInterface $event, $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && !$entity->has('created_at')) {
            $entity->created_at = date('Y-m-d H:i:s');
        }
    }

    /**
     * Count how many methods a user already has.
     */
    public function countForUser(int $userId): int
    {
        return $this->find()->where(['user_id' => $userId])->count();
    }
}
