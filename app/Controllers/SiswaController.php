<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class SiswaController extends BaseController
{
    protected $UserModel;
    protected $SiswaModel;
    public function __construct()
    {
        $this->UserModel = new UserModel();
        $this->SiswaModel = new SiswaModel();
    }
    public function index()
    {
        //
        $data = [
            'title' => 'Siswa | Welcome to SDN Talun 2 Kota Serang',
            'nav_link' => 'Dashboard'
        ];
        return view('pages/siswa/dashboard_siswa', $data);
    }
    public function profile()
    {
        //
        $data = [
            'title' => 'Siswa | Welcome to SDN Talun 2 Kota Serang',
            'nav_link' => 'Profile'
        ];
        return view('pages/siswa/profile', $data);
    }
}
