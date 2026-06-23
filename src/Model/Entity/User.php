<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'email' => true,
        'google_id' => true,
        'avatar_url' => true,
        'created_at' => true,
    ];

    // Hide sensitive fields from JSON serialization
    protected array $_hidden = [
        'google_id',
    ];
}
