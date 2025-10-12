<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GuruTahunanMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_guru_tahun' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'guru_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tahun_ajaran_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                // silakan sesuaikan set status sesuai kebutuhan
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
            ],
            'tanggal_masuk' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_keluar' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_guru_tahun', true);
        $this->forge->addKey('guru_id');
        $this->forge->addKey('tahun_ajaran_id');
        // Satu baris per guru per tahun ajaran
        $this->forge->addUniqueKey(['guru_id', 'tahun_ajaran_id']);

        // FK sesuaikan nama tabel/PK master guru & tahun ajaran
        $this->forge->addForeignKey('guru_id', 'tb_guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tahun_ajaran_id', 'tb_tahun_ajaran', 'id_tahun_ajaran', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('tb_guru_tahun', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_guru_tahun', true);
    }
}
