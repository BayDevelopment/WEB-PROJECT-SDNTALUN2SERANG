<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GuruMapelMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_guru_mapel'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_guru'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_mapel'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jam_per_minggu'  => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true],
            'keterangan'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        // PK
        $this->forge->addKey('id_guru_mapel', true);

        // INDEX untuk lookup cepat (single column saja)
        $this->forge->addKey('id_guru');
        $this->forge->addKey('id_mapel');
        $this->forge->addKey('id_tahun_ajaran');

        // UNIQUE untuk cegah duplikasi kombinasi
        $this->forge->addUniqueKey(['id_guru', 'id_mapel', 'id_tahun_ajaran'], 'uk_gm_guru_mapel_ta');

        // FKs (MySQL akan otomatis bikin index jika belum ada)
        $this->forge->addForeignKey('id_guru', 'tb_guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mapel', 'tb_mapel', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tb_tahun_ajaran', 'id_tahun_ajaran', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('tb_guru_mapel', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_guru_mapel', true);
    }
}
