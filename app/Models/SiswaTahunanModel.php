<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaTahunanModel extends Model
{
    protected $table            = 'tb_siswa_tahun';
    protected $primaryKey       = 'id_siswa_tahun';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['siswa_id', 'tahun_ajaran_id',    'status',    'tanggal_masuk',    'tanggal_keluar'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';
}
