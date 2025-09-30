<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GuruMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_guru'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nip'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'nuptk'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'full_name'  => ['type' => 'VARCHAR', 'constraint' => 128],
            'gender'     => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'default' => 'L'],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_guru', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey(['full_name']);
        $this->forge->createTable('tb_guru', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_guru', true);
    }
}
