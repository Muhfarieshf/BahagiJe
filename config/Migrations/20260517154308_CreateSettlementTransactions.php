<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSettlementTransactions extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('settlement_transactions');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('debtor_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('creditor_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('amount', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'null' => false,
        ]);
        $table->addColumn('status', 'enum', [
            'values' => ['pending', 'settled', 'unresolved'],
            'default' => 'pending',
            'null' => false,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['session_id']);
        $table->addIndex(['debtor_id']);
        $table->addIndex(['creditor_id']);

        $table->addForeignKey('session_id', 'group_sessions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
        $table->addForeignKey('debtor_id', 'participants', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
        $table->addForeignKey('creditor_id', 'participants', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);

        $table->create();
    }
}
