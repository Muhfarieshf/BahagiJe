<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Participant extends Entity
{
    protected array $_accessible = [
        'session_id' => true,
        'user_id'    => true,
        'guest_name' => true,
        'role'       => true,
        'joined_at'  => true,
    ];
}
