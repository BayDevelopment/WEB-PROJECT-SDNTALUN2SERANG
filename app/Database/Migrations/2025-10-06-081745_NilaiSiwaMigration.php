<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NilaiSiwaMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_nilai'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tahun_ajaran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'mapel_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kategori_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // UTS / UAS
            'skor'            => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => false], // 0..100
            'tanggal'         => ['type' => 'DATE', 'null' => true],
            'keterangan'      => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_nilai', true);

        // Satu entri per (siswa, TA, mapel, kategori)
        $this->forge->addUniqueKey(['siswa_id', 'tahun_ajaran_id', 'mapel_id', 'kategori_id']);

        // FK
        $this->forge->addForeignKey('siswa_id', 'tb_siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tahun_ajaran_id', 'tb_tahun_ajaran', 'id_tahun_ajaran', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('mapel_id', 'tb_mapel', 'id_mapel', 'RESTRICT', 'CASCADE');          // pastikan ada tb_mapel
        $this->forge->addForeignKey('kategori_id', 'tb_kategori_nilai', 'id_kategori', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('tb_nilai_siswa', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_nilai_siswa', true);
    }
}
