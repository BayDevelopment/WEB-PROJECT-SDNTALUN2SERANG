<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MapelMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_mapel'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode'       => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 128],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_mapel', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey(['nama']);
        $this->forge->createTable('tb_mapel', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_mapel', true);
    }
}
