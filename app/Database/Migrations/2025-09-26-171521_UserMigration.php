<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'username'  => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'password'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'      => ['type' => 'ENUM', 'constraint' => ['operator', 'guru', 'siswa'], 'default' => 'siswa'],
            'email'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('tb_users', true, [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_users', true);
    }
}
