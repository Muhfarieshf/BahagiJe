<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSessionNotifications extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('session_notifications');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('participant_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('type', 'enum', [
            'values' => ['reupload_request', 'approved', 'rejected'],
            'null' => false,
        ]);
        $table->addColumn('is_read', 'boolean', [
            'default' => false,
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
