<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KategoriNilai extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kategori' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode'        => ['type' => 'VARCHAR', 'constraint' => 20],   // UTS, UAS, HAR, TGS, ...
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'bobot'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'], // 0..100 atau 0..1 (bebas)
            'is_wajib'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kategori', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('tb_kategori_nilai', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_kategori_nilai', true);
    }
}
