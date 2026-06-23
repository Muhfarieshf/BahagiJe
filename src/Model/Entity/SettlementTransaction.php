<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class SettlementTransaction extends Entity
{
    protected array $_accessible = [
        'session_id' => true,
        'debtor_id' => true,
        'creditor_id' => true,
        'amount' => true,
        'status' => true,
        'created_at' => true,
        'group_session' => true,
        'debtor' => true,
        'creditor' => true,
    ];
}
