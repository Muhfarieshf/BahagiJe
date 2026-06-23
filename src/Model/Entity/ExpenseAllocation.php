<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ExpenseAllocation extends Entity
{
    protected array $_accessible = [
        'expense_id' => true,
        'participant_id' => true,
        'amount_owed' => true,
        'is_payer' => true,
        'expense' => true,
        'participant' => true,
    ];
}
