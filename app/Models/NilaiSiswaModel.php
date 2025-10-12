<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiSiswaModel extends Model
{
    protected $table            = 'tb_nilai_siswa';
    protected $primaryKey       = 'id_nilai';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['siswa_id', 'tahun_ajaran_id',    'mapel_id',    'kategori_id',    'skor',    'tanggal',    'keterangan'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }
}
