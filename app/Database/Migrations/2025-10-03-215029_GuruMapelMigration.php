<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GuruMapelMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_guru_mapel'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_guru'         => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'id_mapel'        => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'id_tahun_ajaran' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'id_kelas'        => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'jam_per_minggu'  => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'keterangan'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_guru_mapel', true);
        $this->forge->addKey('id_guru');
        $this->forge->addKey('id_mapel');
        $this->forge->addKey('id_tahun_ajaran');
        $this->forge->addKey('id_kelas');

        $this->forge->addUniqueKey(
            ['id_guru', 'id_mapel', 'id_tahun_ajaran', 'id_kelas'],
            'uniq_guru_mapel_ta_kelas'
        );

        // Pastikan nama tabel referensi BENAR dan kolom PK-nya INT UNSIGNED
        $this->forge->addForeignKey('id_guru',         'tb_guru',          'id_guru',         'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_mapel',        'tb_mapel',         'id_mapel',        'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tb_tahun_ajaran',  'id_tahun_ajaran', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_kelas',        'tb_kelas',         'id_kelas',        'CASCADE', 'RESTRICT');

        $this->forge->createTable('tb_guru_mapel', true, [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_guru_mapel', true);
    }
}
