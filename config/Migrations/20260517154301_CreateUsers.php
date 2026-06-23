<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');
        
        $table->addColumn('name', 'string', [
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('email', 'string', [
            'limit' => 150,
            'null' => false,
        ]);
        $table->addColumn('google_id', 'string', [
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('avatar_url', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['email'], ['unique' => true, 'name' => 'UNIQUE_EMAIL']);
        $table->addIndex(['google_id'], ['unique' => true, 'name' => 'UNIQUE_GOOGLE_ID']);

        $table->create();
    }
}
