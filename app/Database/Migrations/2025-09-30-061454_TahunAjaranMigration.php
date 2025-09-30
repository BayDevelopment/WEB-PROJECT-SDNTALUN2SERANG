<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TahunAjaranMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tahun'           => ['type' => 'VARCHAR', 'constraint' => 9], // contoh: 2024/2025
            'semester'        => ['type' => 'ENUM', 'constraint' => ['ganjil', 'genap'], 'default' => 'ganjil'],
            'start_date'      => ['type' => 'DATE', 'null' => true],
            'end_date'        => ['type' => 'DATE', 'null' => true],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_tahun_ajaran', true);
        $this->forge->addUniqueKey(['tahun', 'semester']); // 1x per semester
        $this->forge->createTable('tb_tahun_ajaran', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_tahun_ajaran', true);
    }
}
