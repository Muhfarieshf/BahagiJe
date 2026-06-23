<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateGroupSessions extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('group_sessions');
        
        $table->addColumn('uuid', 'string', [
            'limit' => 36,
            'null' => false,
        ]);
        $table->addColumn('name', 'string', [
            'limit' => 150,
            'null' => false,
        ]);
        $table->addColumn('host_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('preset_type', 'enum', [
            'values' => ['dining', 'road_trip', 'long_trip', 'custom'],
            'null' => false,
        ]);
        $table->addColumn('status', 'enum', [
            'values' => ['open', 'locked', 'closed'],
            'default' => 'open',
            'null' => false,
        ]);
        $table->addColumn('max_participants', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('reference_doc_url', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('reference_doc_type', 'enum', [
            'values' => ['receipt', 'bill', 'invoice', 'document'],
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('closed_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);

        $table->addIndex(['uuid'], ['unique' => true, 'name' => 'UNIQUE_UUID']);
        $table->addIndex(['host_id']);

        $table->addForeignKey('host_id', 'users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE']);

        $table->create();
    }
}
