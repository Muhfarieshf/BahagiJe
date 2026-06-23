<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class PaymentProof extends Entity
{
    protected array $_accessible = [
        'session_id' => true,
        'participant_id' => true,
        'proof_url' => true,
        'status' => true,
        'submitted_at' => true,
        'reviewed_at' => true,
        'rejection_reason' => true,
        'group_session' => true,
        'participant' => true,
    ];
}
