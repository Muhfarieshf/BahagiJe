<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Receipt extends Entity
{
    protected array $_accessible = [
        'session_id' => true,
        'payer_id' => true,
        'name' => true,
        'image_url' => true,
        'created_at' => true,
        'group_session' => true,
        'payer' => true,
        'expenses' => true,
    ];
}
