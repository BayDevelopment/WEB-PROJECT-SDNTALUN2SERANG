<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJabatanToTbGuru extends Migration
{
    protected string $table = 'tb_guru';

    public function up()
    {
        $this->forge->addColumn($this->table, [
            'jabatan' => [
                'type'       => 'ENUM',
                'constraint' => ['Kepala Sekolah', 'Wakil Kepala', 'Guru', 'Wali Kelas', 'Operator', 'Staff'],
                'null'       => true,
                'after'      => 'status_active' // opsional: posisi kolom
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn($this->table, 'jabatan');
    }
}
