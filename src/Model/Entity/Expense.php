<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Expense extends Entity
{
    protected array $_accessible = [
        'session_id' => true,
        'participant_id' => true,
        'description' => true,
        'total_amount' => true,
        'quantity' => true,
        'image_url' => true,
        'expense_type' => true,
        'split_type' => true,
        'created_at' => true,
        'group_session' => true,
        'participant' => true,
        'expense_allocations' => true,
    ];
}
