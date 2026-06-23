<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateParticipants extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('participants');
        
        $table->addColumn('session_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('guest_name', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
        ]);
        $table->addColumn('role', 'enum', [
            'values' => ['host', 'registered', 'guest'],
            'null' => false,
        ]);
        // joined_at will be set programmatically in ParticipantsTable beforeSave()
        $table->addColumn('joined_at', 'datetime', [
            'default' => null,
            'null' => false,
        ]);

        $table->addIndex(['session_id']);
        $table->addIndex(['user_id']);

        $table->addForeignKey('session_id', 'group_sessions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
        $table->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE']);

        $table->create();
    }
}
