<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class OperatorController extends BaseController
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
            'title' => 'Operator | SDN Talun 2 Kota Serang',
            'nav_link' => 'Dashboard'
        ];
        return view('pages/operator/dashboard_operator', $data);
    }
    public function Data_siswa()
    {
        $dataSiswa = $this->SiswaModel->findAll();
        $data = [
            'title' => 'Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Siswa/i',
            'nav_link' => 'Data Siswa',
            'd_siswa' => $dataSiswa
        ];
        return view('pages/operator/data_siswa', $data);
    }
    public function page_tambah_siswa()
    {
        $belumIsi = $this->UserModel
            ->select('tb_users.*')
            ->join('tb_siswa s', 's.user_id = tb_users.id_user', 'left')
            ->where('tb_users.role', 'siswa')
            ->where('tb_users.is_active', 1)
            ->where('s.id_siswa', null)   // builder akan jadi "IS NULL"
            ->orderBy('tb_users.username', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Tambah siswa | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Tambah Siswa/i',
            'nav_link' => 'Tambah Siswa',
            'd_user' => $belumIsi,
            'validation' => \Config\Services::validation(),
        ];
        return view('pages/operator/tambah_siswa', $data);
    }
    // === ACTION: Insert data siswa ===
    public function aksi_insert_siswa()
    {
        $req = $this->request;

        // ---------- RULES ----------
        $rules = [
            'user_id'     => 'required|is_natural_no_zero',
            'nisn'        => 'required|min_length[8]|max_length[16]|is_unique[tb_siswa.nisn]',
            'full_name'   => 'required|min_length[3]',
            'gender'      => 'required|in_list[L,P]',
            'birth_place' => 'required',
            'birth_date'  => 'required|valid_date',
            'address'     => 'permit_empty|string',
            'parent_name' => 'required|min_length[3]',
            'phone'       => 'required|numeric|min_length[8]|max_length[20]',
            'photo'       => 'permit_empty'
                . '|is_image[photo]'
                . '|max_size[photo,2048]'
                . '|ext_in[photo,jpg,jpeg,png]'
                . '|mime_in[photo,image/jpg,image/jpeg,image/png]',
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput();
        }

        // ---------- AMBIL INPUT ----------
        $userId     = (int) $req->getPost('user_id');
        $nisn       = trim((string) $req->getPost('nisn'));
        $fullName   = trim((string) $req->getPost('full_name'));
        $gender     = (string) $req->getPost('gender');              // L / P
        $birthPlace = trim((string) $req->getPost('birth_place'));
        $birthDate  = (string) $req->getPost('birth_date');
        $address    = trim((string) $req->getPost('address'));       // View kamu pakai name="alamat"? -> ganti ke 'address' di form
        $parentName = trim((string) $req->getPost('parent_name'));
        $phone      = trim((string) $req->getPost('phone'));

        // ---------- CEK USER VALID (role siswa & aktif) ----------
        $user = $this->UserModel
            ->select('id_user, role, is_active')
            ->where('id_user', $userId)
            ->first();

        if (! $user || $user['role'] !== 'siswa' || (int)$user['is_active'] !== 1) {
            session()->setFlashdata('sweet_error', 'User tidak valid / tidak aktif / bukan role siswa.');
            return redirect()->back()->withInput();
        }

        // ---------- CEK user_id SUDAH DIPAKAI DI tb_siswa? (unik) ----------
        $sudahAda = $this->SiswaModel->where('user_id', $userId)->first();
        if ($sudahAda) {
            // "user_id jika tidak sama" → interpretasi umum: kalau SUDAH ADA (duplikat), tolak
            session()->setFlashdata('sweet_error', 'User ini sudah memiliki data siswa.');
            return redirect()->back()->withInput();
        }

        // ---------- CEK nisn SUDAH ADA? (tambahan manual selain rules) ----------
        $nisnAda = $this->SiswaModel->where('nisn', $nisn)->first();
        if ($nisnAda) {
            // "nisn jika tidak sama" → interpretasi: kalau SUDAH ADA (duplikat), tolak
            session()->setFlashdata('sweet_error', 'NISN sudah terdaftar.');
            return redirect()->back()->withInput();
        }

        // ---------- HANDLE FOTO ----------
        $uploadDir   = FCPATH . 'assets/img/uploads';
        $defaultSrc  = FCPATH . 'assets/img/user.png';        // sumber default
        $defaultName = 'user.png';                            // nama file default di /uploads

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photoFile = $req->getFile('photo');
        $photoName = null;

        if ($photoFile && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            // Ada upload: simpan dengan nama aman
            $ext       = strtolower($photoFile->getExtension() ?: 'jpg');
            $photoName = 'siswa_' . $userId . '_' . time() . '.' . $ext;
            try {
                $photoFile->move($uploadDir, $photoName);
            } catch (\Throwable $e) {
                session()->setFlashdata('sweet_error', 'Gagal menyimpan foto: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        } else {
            // Tidak ada upload → pakai default. Pastikan tersedia di /uploads
            $targetDefault = $uploadDir . DIRECTORY_SEPARATOR . $defaultName;
            if (! file_exists($targetDefault)) {
                // copy dari assets/img/user.png → assets/img/uploads/user.png
                @copy($defaultSrc, $targetDefault);
            }
            $photoName = $defaultName; // simpan nama file default
        }

        // ---------- INSERT ----------
        $dataInsert = [
            'user_id'     => $userId,
            'nisn'        => $nisn,
            'full_name'   => $fullName,
            'gender'      => $gender,
            'birth_place' => $birthPlace,
            'birth_date'  => $birthDate,
            'address'     => $address,     // jika kolommu bernama 'alamat', ganti key ini -> 'alamat'
            'parent_name' => $parentName,
            'phone'       => $phone,
            'photo'       => $photoName,
        ];

        try {
            $this->SiswaModel->insert($dataInsert);
        } catch (\Throwable $e) {
            // rollback foto upload jika bukan default
            if ($photoName && $photoName !== $defaultName) {
                @unlink($uploadDir . DIRECTORY_SEPARATOR . $photoName);
            }
            session()->setFlashdata('sweet_error', 'Gagal menyimpan data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Data siswa berhasil ditambahkan.');
        return redirect()->to(base_url('operator/data-siswa'));
    }

    public function page_edit_siswa(string $nisn)
    {
        // Ambil siswa dari SiswaModel saja (user_id ikut dari sini)
        $siswa = $this->SiswaModel
            ->select('tb_siswa.*, tb_users.username AS user_name') // opsional join untuk tampilan nama user
            ->join('tb_users', 'tb_users.id_user = tb_siswa.user_id', 'left')
            ->where('tb_siswa.nisn', $nisn)
            ->first();

        if (! $siswa) {
            session()->setFlashdata('sweet_error', 'Data siswa tidak ditemukan.');
            return redirect()->to(base_url('operator/data-siswa'));
        }

        $data = [
            'title'      => 'Edit siswa | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit Siswa/i',
            'nav_link'   => 'Data Siswa',
            'siswa'      => $siswa,                           // ← ada 'user_id' dari SiswaModel
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_siswa', $data);
    }
    // === ACTION: Update Siswa (by NISN di URL) ===
    public function aksi_update_siswa(string $nisnParam)
    {
        $req = $this->request;

        // Ambil record existing dari SiswaModel
        $existing = $this->SiswaModel
            ->where('nisn', $nisnParam)
            ->first();

        if (! $existing) {
            session()->setFlashdata('sweet_error', 'Data siswa tidak ditemukan.');
            return redirect()->to(base_url('operator/data-siswa'));
        }

        $idSiswa = (int) ($existing['id_siswa'] ?? 0);
        // SELALU pakai user_id dari record existing (abaikan input form)
        $userId  = (int) ($existing['user_id'] ?? 0);

        // RULES:
        // - user_id TIDAK diedit (tetap required di sisi logika kita, tapi tidak diambil dari POST)
        // - nisn unik kecuali untuk id_siswa ini sendiri
        $rules = [
            // 'user_id' tidak kita validasi dari POST—pakai existing. (Opsional: validasi manual di bawah)
            'nisn'        => "required|min_length[8]|max_length[16]|is_unique[tb_siswa.nisn,id_siswa,{$idSiswa}]",
            'full_name'   => 'required|min_length[3]',
            'gender'      => 'required|in_list[L,P]',
            'birth_place' => 'required',
            'birth_date'  => 'required|valid_date',
            'address'     => 'permit_empty|string',
            'parent_name' => 'required|min_length[3]',
            'phone'       => 'required|numeric|min_length[8]|max_length[20]',
            'photo'       => 'permit_empty'
                . '|is_image[photo]'
                . '|max_size[photo,2048]'
                . '|ext_in[photo,jpg,jpeg,png]'
                . '|mime_in[photo,image/jpg,image/jpeg,image/png]',
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput();
        }

        // Ambil input lain
        $nisnNew    = trim((string) $req->getPost('nisn'));
        $fullName   = trim((string) $req->getPost('full_name'));
        $gender     = (string) $req->getPost('gender');
        $birthPlace = trim((string) $req->getPost('birth_place'));
        $birthDate  = (string) $req->getPost('birth_date');
        $address    = trim((string) $req->getPost('address'));
        $parentName = trim((string) $req->getPost('parent_name'));
        $phone      = trim((string) $req->getPost('phone'));
        $photoOld   = trim((string) $req->getPost('photo_old'));

        // Validasi user (diambil dari existing) — role siswa & aktif
        $user = $this->UserModel
            ->select('id_user, role, is_active')
            ->where('id_user', $userId)
            ->first();

        if (! $user || $user['role'] !== 'siswa' || (int)$user['is_active'] !== 1) {
            session()->setFlashdata('sweet_error', 'User tidak valid / tidak aktif / bukan role siswa.');
            return redirect()->back()->withInput();
        }

        // Handle Foto
        $uploadDir   = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'uploads';
        $defaultName = 'user.png';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $photoFile = $req->getFile('photo');
        $photoName = $photoOld; // default: pertahankan foto lama

        if ($photoFile && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $ext       = strtolower($photoFile->getExtension() ?: 'jpg');
            $photoName = 'siswa_' . $userId . '_' . time() . '.' . $ext;

            try {
                $photoFile->move($uploadDir, $photoName);

                // hapus foto lama kalau bukan default & berbeda
                if (! empty($photoOld) && $photoOld !== $defaultName && $photoOld !== $photoName) {
                    $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $photoOld;
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            } catch (\Throwable $e) {
                session()->setFlashdata('sweet_error', 'Gagal menyimpan foto: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        // Data update (user_id TIDAK diubah)
        $dataUpdate = [
            'user_id'     => $userId,     // ← pakai existing
            'nisn'        => $nisnNew,
            'full_name'   => $fullName,
            'gender'      => $gender,
            'birth_place' => $birthPlace,
            'birth_date'  => $birthDate,
            'address'     => $address,
            'parent_name' => $parentName,
            'phone'       => $phone,
            'photo'       => $photoName,
        ];

        try {
            $this->SiswaModel->update($idSiswa, $dataUpdate);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal mengubah data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Data siswa berhasil diperbarui.');
        return redirect()->to(base_url('operator/data-siswa/'));
    }

    public function page_detail_siswa(string $nisn)
    {
        $nisn = trim($nisn);

        // Ambil siswa by NISN (bukan find)
        $detailSiswa = $this->SiswaModel
            ->select('tb_siswa.*, tb_users.username AS user_name, tb_users.username AS user_username')
            ->join('tb_users', 'tb_users.id_user = tb_siswa.user_id', 'left')
            ->where('tb_siswa.nisn', $nisn)
            ->first();

        if (! $detailSiswa) {
            session()->setFlashdata('sweet_error', 'Data siswa tidak ditemukan.');
            return redirect()->to(base_url('operator/data-siswa'));
        }

        $data = [
            'title'     => 'Detail siswa | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Detail Siswa/i',
            'nav_link'  => 'Data Siswa',
            'siswa'     => $detailSiswa,
        ];

        return view('pages/operator/detail_siswa', $data);
    }
    // OperatorController.php

    public function aksi_delete_siswa(string $nisn)
    {
        // // Disarankan: hanya izinkan POST/DELETE (hidden form + _method=DELETE)
        // if (! $this->request->is('post') && ! $this->request->is('delete')) {
        //     session()->setFlashdata('sweet_error', 'Metode tidak diizinkan.');
        //     return redirect()->to(base_url('operator/data-siswa'));
        // }

        // Ambil record berdasarkan NISN (bukan find, kecuali PK-mu memang NISN)
        $row = $this->SiswaModel
            ->select('id_siswa, nisn, photo')
            ->where('nisn', trim($nisn))
            ->first();

        if (! $row) {
            session()->setFlashdata('sweet_error', 'Data siswa tidak ditemukan.');
            return redirect()->to(base_url('operator/data-siswa'));
        }

        $uploadDir   = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'uploads';
        $defaultName = 'user.png';
        $photoName   = (string)($row['photo'] ?? '');

        try {
            // Hapus di DB
            $this->SiswaModel->delete((int)$row['id_siswa']);

            // Hapus file foto kalau bukan default
            if ($photoName !== '' && $photoName !== $defaultName) {
                $path = $uploadDir . DIRECTORY_SEPARATOR . $photoName;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menghapus: ' . $e->getMessage());
            return redirect()->to(base_url('operator/data-siswa'));
        }

        session()->setFlashdata('sweet_success', 'Data siswa berhasil dihapus.');
        return redirect()->to(base_url('operator/data-siswa'));
    }

    // OperatorController.php
    public function page_profile()
    {
        $s = session();

        // pastikan login & role operator
        $userId = (int) ($s->get('id_user') ?? 0);
        $role   = (string) ($s->get('role') ?? '');
        if ($userId === 0) {
            $s->setFlashdata('sweet_error', 'Sesi berakhir. Silakan login kembali.');
            return redirect()->to(base_url('auth/logout'));
        }
        if ($role !== 'operator') {
            $s->setFlashdata('sweet_error', 'Akses ditolak. Halaman ini khusus operator.');
            return redirect()->to(base_url('auth/login'));
        }

        // ambil data operator langsung dari UserModel (termasuk photo)
        $operator = $this->UserModel
            ->select('id_user, username, role, email, is_active, created_at, updated_at') // pastikan kolom photo ada
            ->find($userId);

        if (! $operator) {
            $s->setFlashdata('sweet_error', 'User tidak ditemukan.');
            return redirect()->to(base_url('auth/logoutt'));
        }

        // url foto (fallback default)
        $photoName = trim((string)($operator['photo'] ?? ''));
        $photoUrl  = $photoName !== ''
            ? base_url('assets/img/uploads/' . $photoName)
            : base_url('assets/img/user.png');

        $data = [
            'title'     => 'Profil Operator | SDN Talun 2 Kota Serang',
            'nav_link'  => 'Profile',
            'sub_judul' => 'Profil Operator',
            'user'      => $operator,   // {id_user, username, role, email, is_active, created_at, updated_at, photo}
            'photoUrl'  => $photoUrl,
        ];

        return view('pages/operator/profile', $data);
    }
}
