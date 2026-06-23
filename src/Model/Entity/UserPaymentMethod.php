<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class UserPaymentMethod extends Entity
{
    protected array $_accessible = [
        'user_id'       => true,
        'method_type'   => true,
        'label'         => true,
        'account_name'  => true,
        'account_value' => true,
        'bank_name'     => true,
        'qr_image_url'  => true,
        'qr_public_id'  => true,
        'created_at'    => true,
        'user'          => true,
    ];

    /**
     * Returns a human-readable label for the method type.
     */
    public function getMethodLabel(): string
    {
        return match($this->method_type) {
            'bank_transfer' => '🏦 Bank Transfer',
            'duitnow_qr'    => '📱 DuitNow QR',
            'duitnow_id'    => '📲 DuitNow ID',
            'tng'           => '💳 Touch \'n Go',
            'paypal'        => '🌐 PayPal',
            default         => $this->method_type,
        };
    }
}
