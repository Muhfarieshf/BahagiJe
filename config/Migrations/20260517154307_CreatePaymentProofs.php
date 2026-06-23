<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePaymentProofs extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('payment_proofs');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('participant_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('proof_url', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('status', 'enum', [
            'values' => ['pending', 'approved', 'rejected'],
            'default' => 'pending',
            'null' => false,
        ]);
        // submitted_at will be set programmatically in PaymentProofsTable beforeSave()
        $table->addColumn('submitted_at', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('reviewed_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('rejection_reason', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);

        $table->addIndex(['session_id']);
        $table->addIndex(['participant_id']);

        $table->addForeignKey('session_id', 'group_sessions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
        $table->addForeignKey('participant_id', 'participants', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);

        $table->create();
    }
}
