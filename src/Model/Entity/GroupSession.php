<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class GroupSession extends Entity
{
    protected array $_accessible = [
        'uuid'               => false, // Never mass-assignable — set in beforeSave
        'name'               => true,
        'host_id'            => true,
        'preset_type'        => true,
        'status'             => true,
        'max_participants'   => true,
        'reference_doc_url'  => true,
        'reference_doc_type' => true,
        'created_at'         => true,
        'closed_at'          => true,
    ];
}
