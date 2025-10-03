<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SiswaTahunMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_siswa_tahun' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tahun_ajaran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['aktif', 'keluar', 'lulus'], 'default' => 'aktif'],
            'tanggal_masuk'  => ['type' => 'DATE', 'null' => true],
            'tanggal_keluar' => ['type' => 'DATE', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa_tahun', true);
        $this->forge->addUniqueKey(['siswa_id', 'tahun_ajaran_id']); // satu baris per TA
        $this->forge->addForeignKey('siswa_id', 'tb_siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tahun_ajaran_id', 'tb_tahun_ajaran', 'id_tahun_ajaran', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('tb_siswa_tahun', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_siswa_tahun', true);
    }
}
