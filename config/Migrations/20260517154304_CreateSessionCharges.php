<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSessionCharges extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('session_charges');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('charge_name', 'string', [
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('charge_type', 'enum', [
            'values' => ['percentage', 'flat'],
            'null' => false,
        ]);
        $table->addColumn('charge_value', 'decimal', [
            'precision' => 8,
            'scale' => 2,
            'null' => false,
        ]);
        $table->addColumn('applies_to', 'enum', [
            'values' => ['proportional', 'equal'],
            'null' => false,
        ]);

        $table->addIndex(['session_id']);

        $table->addForeignKey('session_id', 'group_sessions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);

        $table->create();
    }
}
