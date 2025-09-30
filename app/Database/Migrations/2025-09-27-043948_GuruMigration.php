<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GuruMigration extends Migration
{
    protected string $table     = 'tb_guru';
    protected string $userTable = 'tb_users'; // sesuaikan jika beda

    public function up()
    {
        // Fields
        $this->forge->addField([
            'id_guru' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,   // boleh kosong
            ],
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'], // L = Laki-laki, P = Perempuan
                'null'       => true,
            ],
            'tgl_lahir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'no_telp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // 1 aktif, 0 nonaktif
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);

        // Keys & indexes
        $this->forge->addKey('id_guru', true);   // PK
        $this->forge->addKey('user_id');         // index biasa
        $this->forge->addUniqueKey('nip');       // unique NIP (opsional)

        // Foreign key: tb_guru.user_id -> tb_users.id_user
        $this->forge->addForeignKey('user_id', $this->userTable, 'id_user', 'CASCADE', 'CASCADE');

        // Create table
        $this->forge->createTable($this->table, true, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Tabel master guru',
        ]);
    }

    public function down()
    {
        // Hapus FK (nama default: {table}_{col}_foreign)
        try {
            $this->forge->dropForeignKey($this->table, $this->table . '_user_id_foreign');
        } catch (\Throwable $e) {
            // abaikan jika sudah ter-drop
        }

        $this->forge->dropTable($this->table, true);
    }
}
