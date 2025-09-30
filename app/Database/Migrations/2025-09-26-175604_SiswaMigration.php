<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SiswaMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_siswa'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true], // FK -> tb_users.id_user
            'nisn'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'full_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'gender'      => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'default' => 'L'],
            'birth_place' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'birth_date'  => ['type' => 'DATE', 'null' => true],
            'address'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'parent_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'phone'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'photo'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_siswa', true);
        $this->forge->addKey('user_id', false, true); // UNIQUE (opsional)
        $this->forge->addKey('nisn', false, true);    // UNIQUE (opsional)

        // Beri nama constraint agar tidak bentrok
        $this->forge->addForeignKey(
            'user_id',
            'tb_users',
            'id_user',
            'SET NULL',
            'CASCADE',
            'fk_tb_siswa_user_id'
        );

        $this->forge->createTable('tb_siswa', true, [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_siswa', true);
    }
}
