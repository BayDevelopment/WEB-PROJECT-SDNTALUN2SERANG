<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KelasMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kelas'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_kelas'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'tingkat'     => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true], // 1..12 (opsional)
            'jurusan'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kelas', true);
        $this->forge->createTable('tb_kelas', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
    }

    public function down()
    {
        $this->forge->dropTable('tb_kelas', true);
    }
}
