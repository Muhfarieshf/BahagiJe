<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddWaypointIdToExpenses extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('expenses');
        
        $table->addColumn('waypoint_id', 'integer', [
            'default' => null,
            'null' => true,
            'after' => 'receipt_id'
        ]);

        $table->addIndex(['waypoint_id']);
        $table->addForeignKey('waypoint_id', 'session_waypoints', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE']);

        $table->update();
    }
}
