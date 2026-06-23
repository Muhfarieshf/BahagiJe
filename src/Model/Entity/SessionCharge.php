<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class SessionCharge extends Entity
{
    protected array $_accessible = [
        'session_id' => true,
        'charge_name' => true,
        'charge_type' => true,
        'charge_value' => true,
        'applies_to' => true,
        'group_session' => true,
    ];
}
