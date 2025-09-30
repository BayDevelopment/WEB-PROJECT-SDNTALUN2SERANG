<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelGuru extends Model
{
    protected $table            = 'tb_guru';
    protected $primaryKey       = 'id_guru';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id',    'nip',    'nama_lengkap',    'jenis_kelamin',    'tgl_lahir',    'no_telp',    'alamat',    'foto',    'status_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';
}
