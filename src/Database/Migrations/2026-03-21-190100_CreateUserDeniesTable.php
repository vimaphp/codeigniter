<?php

namespace Vima\CodeIgniter\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserDeniesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'permission_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'namespace' => [
                'type'          => 'VARCHAR',
                'constraint'    => 255,
                'null'          => true,
            ],
            'reason' => [
                'type'          => 'VARCHAR',
                'constraint'    => 255,
                'null'          => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'permission_id', 'namespace']);
        $this->forge->createTable('user_denies', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_denies', true);
    }
}
