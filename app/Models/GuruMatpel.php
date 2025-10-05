<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruMatpel extends Model
{
    protected $table            = 'tb_guru_mapel';
    protected $primaryKey       = 'id_guru_mapel';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_guru',    'id_mapel',    'id_tahun_ajaran', 'id_kelas',    'jam_per_minggu',    'keterangan'];


    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';
}
