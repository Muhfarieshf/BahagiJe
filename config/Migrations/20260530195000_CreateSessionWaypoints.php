<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSessionWaypoints extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('session_waypoints');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('type', 'enum', [
            'values' => ['start', 'stop', 'toll', 'destination'],
            'null' => false,
        ]);
        $table->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('lat', 'decimal', [
            'precision' => 10,
            'scale' => 8,
            'null' => true,
        ]);
        $table->addColumn('lng', 'decimal', [
            'precision' => 11,
            'scale' => 8,
            'null' => true,
        ]);
        $table->addColumn('sort_order', 'integer', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['session_id']);
        $table->addForeignKey('session_id', 'group_sessions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);

        $table->create();
    }
}
