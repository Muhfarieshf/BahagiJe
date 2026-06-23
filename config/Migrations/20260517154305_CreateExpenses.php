<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateExpenses extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('expenses');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('participant_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('description', 'string', [
            'limit' => 200,
            'null' => false,
        ]);
        $table->addColumn('total_amount', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'null' => false,
        ]);
        $table->addColumn('expense_type', 'enum', [
            'values' => ['personal', 'group'],
            'null' => false,
        ]);
        $table->addColumn('split_type', 'enum', [
            'values' => ['equal', 'proportional'],
            'null' => false,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['session_id']);
        $table->addIndex(['participant_id']);

        $table->addForeignKey('session_id', 'group_sessions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
        $table->addForeignKey('participant_id', 'participants', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);

        $table->create();
    }
}
