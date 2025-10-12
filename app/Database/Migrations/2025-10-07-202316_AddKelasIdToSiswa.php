<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AddKelasIdToSiswa extends Migration
{
    // JIKA BLM ADA ID_KELAS / KELAS_ID GUNAKAN DIBAWH INI
    // public function up()
    // {
    //     // 1) Tambah kolom
    //     $this->forge->addColumn('tb_siswa', [
    //         'kelas_id' => [
    //             'type'       => 'INT',
    //             'unsigned'   => true,
    //             'null'       => false,
    //             'after'      => 'user_id', // taruh di dekat user_id (opsional)
    //             'comment'    => 'FK -> tb_kelas.id_kelas',
    //         ],
    //     ]);

    //     // 2) (Opsional) tambah index untuk performa
    //     $this->db->query('ALTER TABLE `tb_siswa` ADD INDEX `idx_tb_siswa_kelas_id` (`kelas_id`);');

    //     // 3) Tambah foreign key (CI4 belum punya addForeignKey() untuk addColumn, jadi pakai SQL)
    //     $this->db->query("
    //         ALTER TABLE `tb_siswa`
    //           ADD CONSTRAINT `fk_tb_siswa_kelas_id`
    //           FOREIGN KEY (`kelas_id`)
    //           REFERENCES `tb_kelas`(`id_kelas`)
    //           ON UPDATE CASCADE
    //           ON DELETE SET NULL
    //     ");
    // }

    // public function down()
    // {
    //     // Hapus FK + index + kolom
    //     // Perhatikan nama constraint/index harus sama dengan yang dibuat di up()
    //     $this->db->query("ALTER TABLE `tb_siswa` DROP FOREIGN KEY `fk_tb_siswa_kelas_id`;");
    //     $this->db->query("ALTER TABLE `tb_siswa` DROP INDEX `idx_tb_siswa_kelas_id`;");
    //     $this->forge->dropColumn('tb_siswa', 'kelas_id');
    // }

    // JIKA SUDAH ADA ID_KELAS / KELAS_ID GUNAKAN DIBAWAH INI
    protected string $table  = 'tb_siswa';
    protected string $fkName = 'fk_tb_siswa_kelas_id';
    protected string $idx    = 'idx_tb_siswa_kelas_id';

    public function up()
    {
        $db = Database::connect();

        // 1) Tambah kolom hanya jika belum ada
        if (! $db->fieldExists('kelas_id', $this->table)) {
            $this->forge->addColumn($this->table, [
                'kelas_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,        // WAJIB nullable karena ON DELETE SET NULL
                    'comment'    => 'FK -> tb_kelas.id_kelas',
                    'after'      => 'user_id',
                ],
            ]);
        } else {
            // Jika kolom sudah ada tapi NOT NULL, ubah jadi NULL agar cocok dengan FK SET NULL
            $colInfo = $db->query("
                SELECT IS_NULLABLE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'kelas_id'
            ", [$this->table])->getFirstRow('array');

            if ($colInfo && strtoupper($colInfo['IS_NULLABLE'] ?? '') !== 'YES') {
                $this->db->query("ALTER TABLE `{$this->table}` MODIFY `kelas_id` INT(11) UNSIGNED NULL COMMENT 'FK -> tb_kelas.id_kelas'");
            }
        }

        // 2) Tambah INDEX kalau belum ada
        $idxExists = $db->query("
            SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$this->table, $this->idx])->getFirstRow();

        if (! $idxExists) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD INDEX `{$this->idx}` (`kelas_id`)");
        }

        // 3) Tambah FK kalau belum ada
        $fkExists = $db->query("
            SELECT 1
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = 'kelas_id'
              AND REFERENCED_TABLE_NAME = 'tb_kelas'
              AND REFERENCED_COLUMN_NAME = 'id_kelas'
            LIMIT 1
        ", [$this->table])->getFirstRow();

        if (! $fkExists) {
            $this->db->query("
                ALTER TABLE `{$this->table}`
                ADD CONSTRAINT `{$this->fkName}`
                FOREIGN KEY (`kelas_id`)
                REFERENCES `tb_kelas`(`id_kelas`)
                ON UPDATE CASCADE
                ON DELETE SET NULL
            ");
        }
    }

    public function down()
    {
        // Hapus FK jika ada
        try {
            $this->db->query("ALTER TABLE `{$this->table}` DROP FOREIGN KEY `{$this->fkName}`");
        } catch (\Throwable $e) {
        }

        // Hapus index jika ada
        try {
            $this->db->query("ALTER TABLE `{$this->table}` DROP INDEX `{$this->idx}`");
        } catch (\Throwable $e) {
        }

        // Hapus kolom jika ada
        $db = Database::connect();
        if ($db->fieldExists('kelas_id', $this->table)) {
            $this->forge->dropColumn($this->table, 'kelas_id');
        }
    }
}
