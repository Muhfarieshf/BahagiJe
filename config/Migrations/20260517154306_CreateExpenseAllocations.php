<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateExpenseAllocations extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('expense_allocations');
        
        $table->addColumn('expense_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('participant_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('amount_owed', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'null' => false,
        ]);
        $table->addColumn('is_payer', 'boolean', [
            'default' => false,
            'null' => false,
        ]);

        $table->addIndex(['expense_id']);
        $table->addIndex(['participant_id']);

        $table->addForeignKey('expense_id', 'expenses', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
        $table->addForeignKey('participant_id', 'participants', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);

        $table->create();
    }
}
