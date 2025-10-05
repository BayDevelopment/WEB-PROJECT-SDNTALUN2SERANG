<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruMatpel;
use App\Models\KelasModel;
use App\Models\ModelGuru;
use App\Models\ModelMatPel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class OperatorController extends BaseController
{
    protected $UserModel;
    protected $SiswaModel;
    protected $ModelGuru;
    protected $ModelMatpel;
    protected $TahunAjaran;
    protected $ModelGuruMatpel;
    protected $ModelKelas;
    public function __construct()
    {
        $this->UserModel = new UserModel();
        $this->SiswaModel = new SiswaModel();
        $this->ModelGuru = new ModelGuru();
        $this->ModelMatpel = new ModelMatPel();
        $this->TahunAjaran = new TahunAjaranModel();
        $this->ModelGuruMatpel = new GuruMatpel();
        $this->ModelKelas = new KelasModel();
        $this->ModelKelas = new KelasModel();
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
        // --- Ambil query string untuk filter UI ---
        $q      = trim((string) $this->request->getGet('q'));
        $gender = trim((string) $this->request->getGet('gender'));

        // --- Ambil data siswa + status aktif user lewat JOIN ---
        // Catatan: sesuaikan nama tabel user: 'tb_user' vs 'tb_users' sesuai skema kamu
        $rows = $this->SiswaModel
            ->select('tb_siswa.*, u.username AS user_name, u.is_active AS user_active')
            ->join('tb_users AS u', 'u.id_user = tb_siswa.user_id', 'left') // ganti ke tb_users jika memang pakai 's'
            ->findAll();

        // --- Filter manual sesuai input pencarian ---
        $filtered = array_values(array_filter($rows, function (array $row) use ($q, $gender) {
            $match = true;

            if ($q !== '') {
                $nama    = mb_strtolower((string)($row['full_name'] ?? ''), 'UTF-8');
                $nisn    = mb_strtolower((string)($row['nisn'] ?? ''), 'UTF-8');
                $keyword = mb_strtolower($q, 'UTF-8');
                $match   = (strpos($nama, $keyword) !== false) || (strpos($nisn, $keyword) !== false);
            }

            if ($match && $gender !== '') {
                $g = mb_strtolower((string)($row['gender'] ?? ''), 'UTF-8');
                $match = ($g === mb_strtolower($gender, 'UTF-8'));
            }

            return $match;
        }));

        // --- Hitung aktif/nonaktif dari hasil filter (bisa ganti ke $rows jika mau total keseluruhan) ---
        $SiswaAktif = 0;
        $SiswaNonAktif = 0;
        foreach ($filtered as $r) {
            $flag = (int)($r['user_active'] ?? 0);
            if ($flag === 1) $SiswaAktif++;
            else $SiswaNonAktif++;
        }

        // --- Kirim ke view ---
        $data = [
            'title'         => 'Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Data Siswa/i',
            'nav_link'      => 'Data Siswa',
            'd_siswa'       => $filtered,          // berisi tb_siswa.* + user_name + user_active
            'q'             => $q,
            'gender'        => $gender,
            'SiswaAktif'    => $SiswaAktif,        // jumlah siswa aktif (berdasarkan user.is_active)
            'SiswaNonAktif' => $SiswaNonAktif,     // jumlah siswa nonaktif
            'totalSiswa'    => count($filtered),   // total (setelah filter)
        ];

        return view('pages/operator/data_siswa', $data);
    }


    public function page_tambah_siswa()
    {
        // User Siswa yang AKTIF dan belum punya data di tb_siswa
        $belumIsi = $this->UserModel
            ->select('u.id_user, u.username, u.email, u.role, u.is_active')
            ->from('tb_users AS u')                               // ganti ke 'tb_users AS u' jika tabelmu pakai "s"
            ->join('tb_siswa AS s', 's.user_id = u.id_user', 'left')
            ->where('u.role', 'siswa')
            ->where('u.is_active', 1)
            ->where('s.id_siswa', null)                          // builder -> IS NULL
            ->orderBy('u.username', 'ASC')
            ->findAll();

        $data = [
            'title'         => 'Tambah siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Tambah Siswa/i',
            'nav_link'      => 'Tambah Siswa',
            'd_user'        => $belumIsi,                        // list user yang bisa dipilih
            'eligibleCount' => is_countable($belumIsi) ? count($belumIsi) : 0,
            'validation'    => \Config\Services::validation(),
        ];

        return view('pages/operator/tambah_siswa', $data);
    }

    // === ACTION: Insert data siswa ===
    public function aksi_insert_siswa()
    {
        $req = $this->request;

        // ---------- RULES ----------
        $rules = [
            'user_id'     => ['rules' => 'required|is_natural_no_zero', 'errors' => [
                'required' => 'User wajib dipilih.',
                'is_natural_no_zero' => 'User tidak valid.'
            ]],
            'nisn'        => ['rules' => 'required|min_length[8]|max_length[16]|is_unique[tb_siswa.nisn]', 'errors' => [
                'required' => 'NISN wajib diisi.',
                'min_length' => 'NISN minimal 8 digit.',
                'max_length' => 'NISN maksimal 16 digit.',
                'is_unique' => 'NISN sudah terdaftar.'
            ]],
            'full_name'   => ['rules' => 'required|min_length[3]', 'errors' => [
                'required' => 'Nama lengkap wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.'
            ]],
            'gender'      => ['rules' => 'required|in_list[L,P]', 'errors' => [
                'required' => 'Jenis kelamin wajib dipilih.',
                'in_list' => 'Pilih L atau P.'
            ]],
            'birth_place' => ['rules' => 'required', 'errors' => ['required' => 'Tempat lahir wajib diisi.']],
            'birth_date'  => ['rules' => 'required|valid_date[Y-m-d]', 'errors' => [
                'required' => 'Tanggal lahir wajib diisi.',
                'valid_date' => 'Format tanggal harus YYYY-MM-DD.'
            ]],
            'address'     => ['rules' => 'permit_empty', 'errors' => []],
            'parent_name' => ['rules' => 'required|min_length[3]', 'errors' => [
                'required' => 'Nama orang tua wajib diisi.',
                'min_length' => 'Minimal 3 karakter.'
            ]],
            'phone'       => ['rules' => 'required|numeric|min_length[8]|max_length[20]', 'errors' => [
                'required' => 'Nomor HP wajib diisi.',
                'numeric' => 'Nomor HP harus angka.',
                'min_length' => 'Nomor HP minimal 8 digit.',
                'max_length' => 'Nomor HP maksimal 20 digit.'
            ]],
            'photo'       => [
                'rules' => 'permit_empty|is_image[photo]|max_size[photo,2048]|ext_in[photo,jpg,jpeg,png]|mime_in[photo,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'is_image' => 'File harus berupa gambar.',
                    'max_size' => 'Maksimal 2MB.',
                    'ext_in' => 'Ekstensi wajib: jpg/jpeg/png.',
                    'mime_in' => 'MIME harus image/jpg, image/jpeg, atau image/png.'
                ]
            ],
        ];


        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors()); // <-- WAJIB agar error per-field muncul
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
            'nav_link'   => 'Edit Siswa',
            'siswa'      => $siswa,                           // ← ada 'user_id' dari SiswaModel
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_siswa', $data);
    }
    // === ACTION: Update Siswa (by NISN di URL) ===
    public function aksi_update_siswa(string $nisnParam)
    {
        $req = $this->request;

        // --- Ambil data existing ---
        $existing = $this->SiswaModel->where('nisn', $nisnParam)->first();
        if (! $existing) {
            session()->setFlashdata('sweet_error', 'Data siswa tidak ditemukan.');
            return redirect()->to(base_url('operator/data-siswa'));
        }

        $idSiswa = (int) ($existing['id_siswa'] ?? 0);
        $userId  = (int) ($existing['user_id'] ?? 0);

        // --- RULES + pesan Indonesia ---
        $rules = [
            'nisn' => [
                'rules'  => "required|min_length[8]|max_length[16]|is_unique[tb_siswa.nisn,id_siswa,{$idSiswa}]",
                'errors' => [
                    'required'   => 'NISN wajib diisi.',
                    'min_length' => 'NISN minimal 8 digit.',
                    'max_length' => 'NISN maksimal 16 digit.',
                    'is_unique'  => 'NISN sudah terdaftar.'
                ]
            ],
            'full_name' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Nama lengkap wajib diisi.',
                    'min_length' => 'Nama lengkap minimal 3 karakter.'
                ]
            ],
            'gender' => [
                'rules'  => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin wajib dipilih.',
                    'in_list'  => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).'
                ]
            ],
            'birth_place' => [
                'rules'  => 'required',
                'errors' => ['required' => 'Tempat lahir wajib diisi.']
            ],
            'birth_date' => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => 'Tanggal lahir wajib diisi.',
                    'valid_date' => 'Format tanggal harus YYYY-MM-DD.'
                ]
            ],
            'address' => [
                'rules'  => 'permit_empty',
                'errors' => []
            ],
            'parent_name' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Nama orang tua/wali wajib diisi.',
                    'min_length' => 'Nama orang tua/wali minimal 3 karakter.'
                ]
            ],
            'phone' => [
                'rules'  => 'required|numeric|min_length[8]|max_length[20]',
                'errors' => [
                    'required'   => 'Nomor HP wajib diisi.',
                    'numeric'    => 'Nomor HP harus angka.',
                    'min_length' => 'Nomor HP minimal 8 digit.',
                    'max_length' => 'Nomor HP maksimal 20 digit.'
                ]
            ],
            'photo' => [
                'rules'  => 'permit_empty|is_image[photo]|max_size[photo,2048]|ext_in[photo,jpg,jpeg,png]|mime_in[photo,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'is_image' => 'File harus berupa gambar.',
                    'max_size' => 'Ukuran foto maksimal 2MB.',
                    'ext_in'   => 'Ekstensi wajib: jpg, jpeg, atau png.',
                    'mime_in'  => 'MIME harus image/jpg, image/jpeg, atau image/png.'
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            // bawa error per-field agar tampil di view
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- Ambil input (selain user_id) ---
        $nisnNew    = trim((string) $req->getPost('nisn'));
        $fullName   = trim((string) $req->getPost('full_name'));
        $gender     = (string) $req->getPost('gender');
        $birthPlace = trim((string) $req->getPost('birth_place'));
        $birthDate  = (string) $req->getPost('birth_date'); // format Y-m-d
        $address    = trim((string) $req->getPost('address'));
        $parentName = trim((string) $req->getPost('parent_name'));
        $phone      = trim((string) $req->getPost('phone'));
        $photoOld   = trim((string) $req->getPost('photo_old'));

        // --- Validasi user existing (role siswa & aktif) ---
        $user = $this->UserModel
            ->select('id_user, role, is_active')
            ->where('id_user', $userId)
            ->first();

        if (! $user || $user['role'] !== 'siswa' || (int) $user['is_active'] !== 1) {
            session()->setFlashdata('sweet_error', 'User tidak valid / tidak aktif / bukan role siswa.');
            return redirect()->back()->withInput();
        }

        // --- Handle Foto ---
        $uploadDir   = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'uploads';
        $defaultName = 'user.png';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $photoFile = $req->getFile('photo');
        $photoName = $photoOld ?: $defaultName; // default: pertahankan foto lama (atau default)

        if ($photoFile && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $ext       = strtolower($photoFile->getExtension() ?: 'jpg');
            $photoName = 'siswa_' . $userId . '_' . time() . '.' . $ext;

            try {
                $photoFile->move($uploadDir, $photoName);

                // Hapus foto lama jika bukan default & berbeda
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

        // --- Data update (user_id TIDAK diubah) ---
        $dataUpdate = [
            'user_id'     => $userId,   // tetap pakai existing
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
        return redirect()->to(base_url('operator/data-siswa'));
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
            'validation' => \Config\Services::validation()
        ];

        return view('pages/operator/profile', $data);
    }

    // EDIT PROFILE
    public function aksi_update_profile()
    {

        $session = session();
        $uid     = (int) $session->get('id_user');

        $user = $this->UserModel->find($uid);
        if (!$user) {
            return redirect()->back()->with('sweet_error', 'User tidak ditemukan.');
        }

        // validasi (NOTE: sesuaikan "user_id" jika PK kamu berbeda)
        $rules = [
            'username' => [
                'label'  => 'Username',
                'rules'  => "required|min_length[4]|max_length[24]|alpha_numeric_punct|is_unique[tb_users.username,id_user,{$uid}]",
                'errors' => [
                    'required'            => '{field} wajib diisi.',
                    'min_length'          => '{field} minimal {param} karakter.',
                    'max_length'          => '{field} maksimal {param} karakter.',
                    'alpha_numeric_punct' => '{field} hanya boleh huruf, angka, dan tanda baca standar (., _, -).',
                    'is_unique'           => '{field} sudah digunakan.',
                ],
            ],

            'email' => [
                'label'  => 'Email',
                'rules'  => "required|valid_email|max_length[128]|is_unique[tb_users.email,id_user,{$uid}]",
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'valid_email' => 'Format {field} tidak valid.',
                    'max_length'  => '{field} maksimal {param} karakter.',
                    'is_unique'   => '{field} sudah terdaftar.',
                ],
            ],

            'role' => [
                'label'  => 'Role',
                'rules'  => 'required|in_list[operator,guru,siswa]',
                'errors' => [
                    'required' => '{field} wajib dipilih.',
                    'in_list'  => '{field} tidak valid. Pilih: operator, guru, atau siswa.',
                ],
            ],

            'is_active' => [
                'label'  => 'Status',
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => '{field} wajib dipilih.',
                    'in_list'  => '{field} tidak valid. Pilih Aktif (1) atau Nonaktif (0).',
                ],
            ],
        ];


        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('sweet_error', 'Periksa kembali input Anda.')
                ->with('active_tab', 'account'); // opsional: buka tab akun otomatis
        }


        // payload dasar
        $payload = [
            'id_user'   => $uid, // PK untuk Model::save()
            'username'  => trim((string) $this->request->getPost('username')),
            'email'     => strtolower(trim((string) $this->request->getPost('email'))),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // hanya operator (atau admin) yang boleh ubah role & status
        $roleLogin = (string) $session->get('role');
        if (in_array($roleLogin, ['operator'], true)) {
            $payload['role']      = (string) $this->request->getPost('role');
            $payload['is_active'] = (int) $this->request->getPost('is_active');
        } else {
            // user biasa: pertahankan nilai lama
            $payload['role']      = $user['role'];
            $payload['is_active'] = (int) $user['is_active'];
        }

        try {
            $this->UserModel->save($payload);
        } catch (\Throwable $e) {
            log_message('error', 'Profile update failed: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('sweet_error', 'Gagal menyimpan perubahan.');
        }

        return redirect()->to(base_url('operator/profile'))
            ->with('sweet_success', 'Profil berhasil diperbarui.');
    }

    public function aksi_update_password()
    {

        $session = session();
        $uid     = (int) $session->get('id_user');     // sesuaikan key session kamu
        $user    = $this->UserModel->find($uid);

        if (! $user) {
            return redirect()->back()->with('sweet_error', 'User tidak ditemukan.');
        }

        // RULES + ERRORS
        $rules = [
            'password' => [
                'label'  => 'Password Saat Ini',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                ],
            ],
            'new_password' => [
                'label'  => 'Password Baru',
                'rules'  => 'required|min_length[8]|differs[password]',
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'differs'    => '{field} harus berbeda dari Password Saat Ini.',
                ],
            ],
            'new_password_confirm' => [
                'label'  => 'Konfirmasi Password Baru',
                'rules'  => 'required|matches[new_password]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'matches'  => '{field} tidak sama dengan Password Baru.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('sweet_error', 'Periksa kembali input Anda.');
        }

        $curr = (string) $this->request->getPost('password');
        $new  = (string) $this->request->getPost('new_password');

        // ====== VERIFIKASI PASSWORD SAAT INI ======
        // Sesuaikan kolom hash di DB: 'password' / 'password_hash'
        $hashFromDb = (string) ($user['password'] ?? $user['password_hash'] ?? '');

        if ($hashFromDb === '' || ! password_verify($curr, $hashFromDb)) {
            // PENTING: pakai key field yang sama persis dengan di view: 'password'
            $validation = \Config\Services::validation();
            $validation->setError('password', 'Password saat ini salah.');
            return redirect()->back()
                ->withInput()
                ->with('validation', $validation)         // <— kirim sebagai 'validation'
                ->with('active_tab', 'security');         // (opsional) aktifkan tab keamanan
        }

        // ====== SIMPAN PASSWORD BARU (HASH) ======
        $newHash = password_hash($new, PASSWORD_ARGON2ID);

        try {
            $this->UserModel->update($uid, [
                // ganti kolom sesuai skema kamu
                'password'   => $newHash,            // atau 'password_hash' => $newHash
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Update password gagal: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('sweet_error', 'Gagal memperbarui password.');
        }

        // (opsional) re-generate session ID
        session()->regenerate();

        return redirect()->to(base_url('operator/profile'))
            ->with('sweet_success', 'Password berhasil diperbarui.');
    }

    // DATA GURU
    public function data_guru()
    {
        // --- Ambil parameter filter dari GET ---
        $req    = $this->request;
        $q      = trim((string)($req->getGet('q') ?? ''));          // cari nama atau NIP
        $gender = strtoupper((string)($req->getGet('gender') ?? '')); // 'L' / 'P' / ''
        if ($gender !== 'L' && $gender !== 'P') {
            $gender = '';
        }

        // 1) Tahun Ajaran aktif (opsional)
        $taAktif = $this->TahunAjaran
            ->select('id_tahun_ajaran')
            ->where('is_active', 1)
            ->orderBy('start_date', 'DESC')
            ->first();

        $idTaAktif = $taAktif['id_tahun_ajaran'] ?? null;

        // 2) Total penugasan global (seluruh baris tb_guru_mapel)
        $totalPenugasan = $this->ModelGuruMatpel->countAll();

        // 3) Daftar guru + jumlah penugasan per guru (filter TA aktif di ON agar tetap LEFT JOIN)
        $joinCond = 'gm.id_guru = tb_guru.id_guru';
        if ($idTaAktif) {
            $joinCond .= ' AND gm.id_tahun_ajaran = ' . (int)$idTaAktif;
        }

        $builder = $this->ModelGuru
            ->select("
            tb_guru.id_guru,
            tb_guru.nip,
            tb_guru.nama_lengkap,
            tb_guru.jenis_kelamin,
            tb_guru.foto,
            COALESCE(COUNT(gm.id_guru_mapel), 0) AS jum_penugasan,
            CASE WHEN COUNT(gm.id_guru_mapel) > 0 THEN 1 ELSE 0 END AS sudah
        ", false)
            ->join('tb_guru_mapel gm', $joinCond, 'left')
            ->groupBy('tb_guru.id_guru, tb_guru.nip, tb_guru.nama_lengkap, tb_guru.jenis_kelamin, tb_guru.foto')
            ->orderBy('tb_guru.nama_lengkap', 'ASC');

        // --- Terapkan filter pencarian (nama_lengkap / nip) ---
        if ($q !== '') {
            $builder->groupStart()
                ->like('tb_guru.nama_lengkap', $q)
                ->orLike('tb_guru.nip', $q)
                ->groupEnd();
        }

        // --- Terapkan filter gender ---
        if ($gender !== '') {
            $builder->where('tb_guru.jenis_kelamin', $gender);
        }

        $d_guru = $builder->findAll();

        // 4) Kirim ke view (ikutkan q & gender agar form tetap terisi)
        return view('pages/operator/data_guru', [
            'title'           => 'Data Guru | SDN Talun 2 Kota Serang',
            'sub_judul'       => 'Data Guru',
            'nav_link'        => 'Data Guru',
            'd_guru'          => $d_guru,
            'idTaAktif'       => $idTaAktif,
            'total_penugasan' => $totalPenugasan,
            'q'               => $q,
            'gender'          => $gender,
        ]);
    }



    public function page_tambah_guru()
    {
        $belumIsi = $this->UserModel
            ->select('tb_users.*')
            ->join('tb_guru g', 'g.user_id = tb_users.id_user', 'left') // <-- pakai user_id
            ->where('tb_users.role', 'guru')
            ->where('tb_users.is_active', 1)
            ->where('g.id_guru', null)   // builder => "IS NULL"
            ->orderBy('tb_users.username', 'ASC')
            ->findAll();

        return view('pages/operator/tambah_guru', [
            'title'       => 'Tambah Guru | SDN Talun 2 Kota Serang',
            'sub_judul'   => 'Tambah Guru/i',
            'nav_link'    => 'Tambah Guru',
            'd_user'      => $belumIsi,
            'validation'  => \Config\Services::validation(),
        ]);
    }

    public function aksi_tambah_guru()
    {
        $req = $this->request;

        // ---------- RULES & Pesan Indonesia ----------
        $rules = [
            'user_id' => [
                'rules'  => 'required|is_natural_no_zero',
                'errors' => [
                    'required'            => 'User wajib dipilih.',
                    'is_natural_no_zero'  => 'User tidak valid.'
                ]
            ],
            'nip' => [
                'rules'  => 'required|min_length[8]|max_length[30]|is_unique[tb_guru.nip]',
                'errors' => [
                    'required'   => 'NIP wajib diisi.',
                    'min_length' => 'NIP minimal 8 karakter.',
                    'max_length' => 'NIP maksimal 30 karakter.',
                    'is_unique'  => 'NIP sudah terdaftar.'
                ]
            ],
            'nama_lengkap' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Nama lengkap wajib diisi.',
                    'min_length' => 'Nama lengkap minimal 3 karakter.'
                ]
            ],
            'jenis_kelamin' => [
                'rules'  => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin wajib dipilih.',
                    'in_list'  => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).'
                ]
            ],
            'tgl_lahir' => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => 'Tanggal lahir wajib diisi.',
                    'valid_date' => 'Format tanggal harus YYYY-MM-DD.'
                ]
            ],
            'no_telp' => [
                'rules'  => 'required|numeric|min_length[8]|max_length[20]',
                'errors' => [
                    'required'   => 'No. Telepon wajib diisi.',
                    'numeric'    => 'No. Telepon harus berupa angka.',
                    'min_length' => 'No. Telepon minimal 8 digit.',
                    'max_length' => 'No. Telepon maksimal 20 digit.'
                ]
            ],
            'alamat' => [
                'rules'  => 'permit_empty',
                'errors' => []
            ],
            'foto' => [
                'rules'  => 'permit_empty|is_image[foto]|max_size[foto,2048]|ext_in[foto,jpg,jpeg,png]|mime_in[foto,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'is_image' => 'File foto harus berupa gambar.',
                    'max_size' => 'Ukuran foto maksimal 2MB.',
                    'ext_in'   => 'Ekstensi foto harus jpg, jpeg, atau png.',
                    'mime_in'  => 'MIME foto harus image/jpg, image/jpeg, atau image/png.'
                ]
            ],
            'status_active' => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status aktif wajib diisi.',
                    'in_list'  => 'Status aktif hanya boleh 0 (nonaktif) atau 1 (aktif).'
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ---------- AMBIL INPUT ----------
        $userId       = (int) $req->getPost('user_id');
        $nip          = trim((string) $req->getPost('nip'));
        $namaLengkap  = trim((string) $req->getPost('nama_lengkap'));
        $jk           = (string) $req->getPost('jenis_kelamin'); // L/P
        $tglLahir     = (string) $req->getPost('tgl_lahir');     // Y-m-d
        $noTelp       = trim((string) $req->getPost('no_telp'));
        $alamat       = trim((string) $req->getPost('alamat'));
        $statusActive = (int) $req->getPost('status_active');

        // ---------- CEK USER VALID (role guru & aktif) ----------
        $user = $this->UserModel
            ->select('id_user, role, is_active')
            ->where('id_user', $userId)
            ->first();

        if (! $user || $user['role'] !== 'guru' || (int) $user['is_active'] !== 1) {
            session()->setFlashdata('sweet_error', 'User tidak valid / tidak aktif / bukan role guru.');
            return redirect()->back()->withInput();
        }

        // ---------- CEK user_id SUDAH DIPAKAI DI tb_guru? ----------
        $existByUser = $this->ModelGuru->where('user_id', $userId)->first();
        if ($existByUser) {
            session()->setFlashdata('sweet_error', 'User ini sudah memiliki data guru.');
            return redirect()->back()->withInput();
        }

        // ---------- CEK nip SUDAH ADA? (tambahan selain rules) ----------
        $existByNip = $this->ModelGuru->where('nip', $nip)->first();
        if ($existByNip) {
            session()->setFlashdata('sweet_error', 'NIP sudah terdaftar.');
            return redirect()->back()->withInput();
        }

        // ---------- HANDLE FOTO ----------
        $uploadDir   = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'uploads';
        $defaultSrc  = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'user.png';
        $defaultName = 'user.png';

        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $fotoFile = $req->getFile('foto');
        $fotoName = null;

        if ($fotoFile && $fotoFile->isValid() && ! $fotoFile->hasMoved()) {
            $ext      = strtolower($fotoFile->getExtension() ?: 'jpg');
            $fotoName = 'guru_' . $userId . '_' . time() . '.' . $ext;

            try {
                $fotoFile->move($uploadDir, $fotoName);
            } catch (\Throwable $e) {
                session()->setFlashdata('sweet_error', 'Gagal menyimpan foto: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        } else {
            // pakai default; pastikan tersedia di /uploads
            $targetDefault = $uploadDir . DIRECTORY_SEPARATOR . $defaultName;
            if (! is_file($targetDefault)) {
                @copy($defaultSrc, $targetDefault);
            }
            $fotoName = $defaultName;
        }

        // ---------- INSERT ----------
        $dataInsert = [
            'user_id'       => $userId,
            'nip'           => $nip,
            'nama_lengkap'  => $namaLengkap,
            'jenis_kelamin' => $jk,
            'tgl_lahir'     => $tglLahir,
            'no_telp'       => $noTelp,
            'alamat'        => $alamat,
            'foto'          => $fotoName,
            'status_active' => $statusActive,
        ];

        try {
            $this->ModelGuru->insert($dataInsert);
        } catch (\Throwable $e) {
            // rollback foto jika bukan default
            if ($fotoName && $fotoName !== $defaultName) {
                @unlink($uploadDir . DIRECTORY_SEPARATOR . $fotoName);
            }
            session()->setFlashdata('sweet_error', 'Gagal menyimpan data guru: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Data guru berhasil ditambahkan.');
        return redirect()->to(base_url('operator/data-guru'));
    }
    public function page_edit_guru(string $nip)
    {
        // Ambil siswa dari SiswaModel saja (user_id ikut dari sini)
        $guru = $this->ModelGuru
            ->select('tb_guru.*, tb_users.username AS user_name') // opsional join untuk tampilan nama user
            ->join('tb_users', 'tb_users.id_user = tb_guru.user_id', 'left')
            ->where('tb_guru.nip', $nip)
            ->first();

        if (! $guru) {
            session()->setFlashdata('sweet_error', 'Data guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        $data = [
            'title'      => 'Edit Guru | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit Guru',
            'nav_link'   => 'Edit Guru',
            'd_guru'      => $guru,                           // ← ada 'user_id' dari SiswaModel
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_guru', $data);
    }
    public function aksi_update_guru(string $nipParam)
    {
        $nip = urldecode($nipParam);

        // 1) Ambil record existing
        $existing = $this->ModelGuru->where('nip', $nip)->first();
        if (! $existing) {
            session()->setFlashdata('sweet_error', 'Data guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        $idGuru = (int)($existing['id_guru'] ?? 0);
        $userId = (int)($existing['user_id'] ?? 0); // LOCK user_id dari DB (abaikan input view)

        // 2) VALIDASI
        $rules = [
            'nip' => [
                'rules'  => "required|min_length[8]|max_length[30]|is_unique[tb_guru.nip,id_guru,{$idGuru}]",
                'errors' => [
                    'required'   => 'NIP wajib diisi.',
                    'min_length' => 'NIP minimal 8 digit.',
                    'max_length' => 'NIP maksimal 30 digit.',
                    'is_unique'  => 'NIP sudah terdaftar.',
                ],
            ],
            'nama_lengkap' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Nama lengkap wajib diisi.',
                    'min_length' => 'Nama lengkap minimal 3 karakter.',
                ],
            ],
            'jenis_kelamin' => [
                'rules'  => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin wajib dipilih.',
                    'in_list'  => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
                ],
            ],
            'tgl_lahir' => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => 'Tanggal lahir wajib diisi.',
                    'valid_date' => 'Format tanggal lahir tidak valid (gunakan YYYY-MM-DD).',
                ],
            ],
            'no_telp' => [
                'rules'  => 'required|numeric|min_length[8]|max_length[20]',
                'errors' => [
                    'required'   => 'No. HP/WhatsApp wajib diisi.',
                    'numeric'    => 'No. HP/WhatsApp harus berupa angka.',
                    'min_length' => 'No. HP/WhatsApp minimal 8 digit.',
                    'max_length' => 'No. HP/WhatsApp maksimal 20 digit.',
                ],
            ],
            'alamat' => [
                'rules'  => 'permit_empty',
                'errors' => [],
            ],
            'foto' => [
                'rules'  => 'permit_empty|is_image[foto]|max_size[foto,2048]|ext_in[foto,jpg,jpeg,png]|mime_in[foto,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'is_image' => 'File yang diunggah harus berupa gambar.',
                    'max_size' => 'Ukuran foto maksimal 2 MB.',
                    'ext_in'   => 'Ekstensi foto harus jpg, jpeg, atau png.',
                    'mime_in'  => 'Tipe file foto tidak didukung (harus image/jpg, image/jpeg, atau image/png).',
                ],
            ],
            'status_active' => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status aktif wajib diisi.',
                    'in_list'  => 'Status aktif hanya boleh 0 (Nonaktif) atau 1 (Aktif).',
                ],
            ],
        ];


        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 3) Ambil input (TANPA user_id)
        $nipNew       = trim((string)$this->request->getPost('nip'));
        $namaLengkap  = trim((string)$this->request->getPost('nama_lengkap'));
        $jk           = (string)$this->request->getPost('jenis_kelamin');
        $tglLahir     = (string)$this->request->getPost('tgl_lahir');
        $noTelp       = trim((string)$this->request->getPost('no_telp'));
        $alamat       = trim((string)$this->request->getPost('alamat'));
        $statusActive = (int)$this->request->getPost('status_active');

        // (opsional) pastikan user masih role guru
        $user = $this->UserModel->select('id_user, role')->where('id_user', $userId)->first();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            session()->setFlashdata('sweet_error', 'User tidak valid / bukan role guru.');
            return redirect()->back()->withInput();
        }

        // 4) Handle foto
        $uploadDir   = FCPATH . 'assets/img/uploads';
        $defaultName = 'user.png';
        if (! is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        $fotoFile = $this->request->getFile('foto');
        $fotoLama = trim((string)($existing['foto'] ?? ''));
        $fotoBaru = $fotoLama ?: $defaultName;

        if ($fotoFile && $fotoFile->isValid() && ! $fotoFile->hasMoved()) {
            $ext = strtolower($fotoFile->getExtension() ?: 'jpg');
            $fotoBaru = 'guru_' . $userId . '_' . time() . '.' . $ext;
            try {
                $fotoFile->move($uploadDir, $fotoBaru);
                if ($fotoLama && $fotoLama !== $defaultName && $fotoLama !== $fotoBaru) {
                    $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $fotoLama;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
            } catch (\Throwable $e) {
                session()->setFlashdata('sweet_error', 'Gagal menyimpan foto: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        // 5) Update (user_id DIPAKSA dari DB)
        $dataUpdate = [
            'user_id'       => $userId,      // <- kunci di server
            'nip'           => $nipNew,
            'nama_lengkap'  => $namaLengkap,
            'jenis_kelamin' => $jk,
            'tgl_lahir'     => $tglLahir,
            'no_telp'       => $noTelp,
            'alamat'        => $alamat,
            'foto'          => $fotoBaru,
            'status_active' => $statusActive,
        ];

        try {
            $this->ModelGuru->update($idGuru, $dataUpdate);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal mengubah data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Data guru berhasil diperbarui.');
        return redirect()->to(base_url('operator/data-guru'));
    }
    public function page_detail_guru(string $nipRaw)
    {
        $nip = urldecode(trim($nipRaw));

        $guru = $this->ModelGuru
            ->select('g.*, u.username AS user_name')
            ->from('tb_guru AS g')
            ->join('tb_users AS u', 'u.id_user = g.user_id', 'left') // sesuaikan jika tabelmu bernama tb_users
            ->where('g.nip', $nip)
            ->first();

        if (! $guru) {
            session()->setFlashdata('sweet_error', 'Data guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        return view('pages/operator/detail_guru', [
            'title'     => 'Detail Guru | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Detail Guru',
            'nav_link'  => 'Data Guru',
            'guru'      => $guru,
        ]);
    }
    public function delete_data_guru($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            session()->setFlashdata('sweet_error', 'Parameter ID tidak valid.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        $guruModel = new \App\Models\ModelGuru();
        $gmModel   = new \App\Models\GuruMatpel(); // tb_guru_mapel

        // Cek data guru
        $guru = $guruModel->find($id);
        if (!$guru) {
            session()->setFlashdata('sweet_error', 'Data guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        // Cek ketergantungan: masih punya penugasan?
        $masihAdaPenugasan = $gmModel->where('id_guru', $id)->countAllResults(true) > 0;
        if ($masihAdaPenugasan) {
            session()->setFlashdata('sweet_error', 'Tidak bisa dihapus: guru masih memiliki penugasan. Hapus penugasan terlebih dahulu.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        // (Opsional) Hapus foto lokal jika ada & bukan default/URL
        try {
            $foto = trim((string)($guru['foto'] ?? ''));
            if ($foto !== '' && !preg_match('~^https?://~i', $foto)) {
                $uploadsRel = 'assets/img/uploads/';     // sesuaikan path upload
                $defaultRel = 'assets/img/user.png';     // sesuaikan default
                $fullPath   = FCPATH . $uploadsRel . $foto;

                if (is_file($fullPath) && basename($fullPath) !== basename($defaultRel)) {
                    @unlink($fullPath);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kegagalan hapus file; lanjut ke delete DB
        }

        // Hapus data guru
        try {
            $guruModel->delete($id); // hard delete (tidak pakai SoftDeletes)
            session()->setFlashdata('sweet_success', 'Data guru berhasil dihapus.');
        } catch (\Throwable $e) {
            // Tangani FK error (MySQL 1451) atau error lain
            $msg = 'Gagal menghapus data guru.';
            if (strpos($e->getMessage(), '1451') !== false) {
                $msg = 'Gagal menghapus: data guru masih terikat dengan data lain.';
            }
            session()->setFlashdata('sweet_error', $msg);
        }

        return redirect()->to(base_url('operator/data-guru'));
    }





    // DATA USER
    public function data_user()
    {
        $allUsers = $this->UserModel->findAll();

        $q       = trim((string) $this->request->getGet('q'));
        $role    = strtolower(trim((string) $this->request->getGet('role'))); // <— konsisten dgn form
        $page    = max(1, (int) $this->request->getGet('page'));
        $perPage = (int) $this->request->getGet('per_page') ?: 10;

        // terima hanya role yang valid
        $allowedRoles = ['operator', 'guru', 'siswa'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = '';
        }

        $filtered = array_filter($allUsers, function ($row) use ($q, $role) {
            $ok = true;

            if ($q !== '') {
                $u = strtolower((string)($row['username'] ?? ''));
                $e = strtolower((string)($row['email'] ?? ''));
                $k = strtolower($q);
                $ok = (strpos($u, $k) !== false) || (strpos($e, $k) !== false);
            }

            if ($ok && $role !== '') {
                $r = strtolower((string)($row['role'] ?? ''));
                $ok = ($r === $role);
            }

            return $ok;
        });

        usort($filtered, fn($a, $b) => strcasecmp((string)($a['username'] ?? ''), (string)($b['username'] ?? '')));

        $total   = count($filtered);
        $pages   = (int) ceil(max(1, $total) / max(1, $perPage));
        $offset  = ($page - 1) * $perPage;
        $pageData = array_slice($filtered, $offset, $perPage);

        return view('pages/operator/data_user', [
            'title'     => 'Data User | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Pengguna',
            'nav_link'  => 'Data User',
            'd_user'    => $pageData,
            'q'         => $q,
            'role'      => $role,
            'page'      => $page,
            'per_page'  => $perPage,
            'total'     => $total,
            'pages'     => $pages,
        ]);
    }
    public function page_tambah_user()
    {
        $allUsers = $this->UserModel->findAll();

        $data = [
            'title' => 'Tambah user | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Tambah User/i',
            'nav_link' => 'Tambah User',
            'd_user' => $allUsers,
            'validation' => \Config\Services::validation(),
        ];
        return view('pages/operator/tambah_user', $data);
    }
    public function aksi_insert_user()
    {
        $req = $this->request;

        // ---------- RULES ----------
        $rules = [
            'username' => [
                'rules'  => 'required|min_length[3]|max_length[50]|is_unique[tb_users.username]',
                'errors' => [
                    'required'   => 'Username wajib diisi.',
                    'min_length' => 'Username minimal 3 karakter.',
                    'max_length' => 'Username maksimal 50 karakter.',
                    'is_unique'  => 'Username sudah terdaftar, silakan gunakan yang lain.'
                ]
            ],
            'password' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 6 karakter.'
                ]
            ],
            'email' => [
                'rules'  => 'required|valid_email|is_unique[tb_users.email]',
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'is_unique'   => 'Email sudah terdaftar.'
                ]
            ],
            'role' => [
                'rules'  => 'required|in_list[operator,siswa,guru,admin]',
                'errors' => [
                    'required' => 'Role wajib dipilih.',
                    'in_list'  => 'Role harus salah satu dari: operator, siswa, guru, atau admin.'
                ]
            ],
            'is_active' => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status aktif wajib diisi.',
                    'in_list'  => 'Status aktif hanya boleh 0 (nonaktif) atau 1 (aktif).'
                ]
            ],
        ];


        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors()); // <— WAJIB
        }

        // ---------- AMBIL INPUT ----------
        $username  = trim((string) $req->getPost('username'));
        $password  = (string) $req->getPost('password'); // akan di-hash
        $email     = trim((string) $req->getPost('email'));
        $role      = (string) $req->getPost('role');
        $isActive  = (int) $req->getPost('is_active');

        // ---------- HASH PASSWORD ----------
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        // ---------- INSERT ----------
        $dataInsert = [
            'username'   => $username,
            'password'   => $passwordHash,
            'email'      => $email,
            'role'       => $role,
            'is_active'  => $isActive,
        ];

        try {
            $this->UserModel->insert($dataInsert);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menyimpan data user: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'User baru berhasil ditambahkan.');
        return redirect()->to(base_url('operator/data-user'));
    }
    public function page_edit_user(string $idUserRaw)
    {
        // id_user numerik; jangan pakai nip lagi
        $idUser = (int) $idUserRaw;

        // Ambil user dari UserModel (sesuaikan nama tabel/model-mu)
        $user = $this->UserModel
            ->select('*')              // atau sebutkan kolom yang kamu butuhkan
            ->where('id_user', $idUser)
            ->first();

        if (! $user) {
            session()->setFlashdata('sweet_error', 'Data user tidak ditemukan.');
            return redirect()->to(base_url('operator/data-user'));
        }

        $data = [
            'title'      => 'Edit User | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit User',
            'nav_link'   => 'Edit User',
            'd_user'     => $user,
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_user', $data);
    }
    public function aksi_update_user(string $idUserRaw)
    {
        $idUser = (int) $idUserRaw;

        // --- Cek data user existing ---
        $existing = $this->UserModel->where('id_user', $idUser)->first();
        if (! $existing) {
            session()->setFlashdata('sweet_error', 'Data user tidak ditemukan.');
            return redirect()->to(base_url('operator/data-user'));
        }

        // --- Rules + pesan error (ID) ---
        $rules = [
            'username' => [
                'rules'  => "required|min_length[3]|max_length[50]|is_unique[tb_users.username,id_user,{$idUser}]",
                'errors' => [
                    'required'   => 'Username wajib diisi.',
                    'min_length' => 'Username minimal 3 karakter.',
                    'max_length' => 'Username maksimal 50 karakter.',
                    'is_unique'  => 'Username sudah digunakan.',
                ],
            ],
            'password' => [
                'rules'  => 'permit_empty|min_length[6]',
                'errors' => [
                    'min_length' => 'Password minimal 6 karakter.',
                ],
            ],
            'email' => [
                'rules'  => "required|valid_email|is_unique[tb_users.email,id_user,{$idUser}]",
                'errors' => [
                    'required'   => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'is_unique'  => 'Email sudah digunakan.',
                ],
            ],
            'role' => [
                'rules'  => 'required|in_list[operator,siswa,guru,admin]',
                'errors' => [
                    'required' => 'Role wajib dipilih.',
                    'in_list'  => 'Role harus salah satu dari: operator, siswa, guru, admin.',
                ],
            ],
            'is_active' => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status aktif wajib diisi.',
                    'in_list'  => 'Status aktif hanya boleh 0 (Nonaktif) atau 1 (Aktif).',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- Ambil input ---
        $username = trim((string) $this->request->getPost('username'));
        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $role     = (string) $this->request->getPost('role');
        $isActive = (int) $this->request->getPost('is_active');
        $password = (string) $this->request->getPost('password'); // opsional

        // --- Siapkan data update ---
        $dataUpdate = [
            'username'  => $username,
            'email'     => $email,
            'role'      => $role,
            'is_active' => $isActive,
        ];

        // Update password hanya jika diisi
        if ($password !== '') {
            $dataUpdate['password'] = password_hash($password, PASSWORD_ARGON2ID);
        }

        // --- Eksekusi update ---
        try {
            $this->UserModel->update($idUser, $dataUpdate);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal mengubah data user: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Data user berhasil diperbarui.');
        return redirect()->to(base_url('operator/data-user'));
    }
    public function page_detail_user(string $idUserRaw)
    {
        $idUser = (int) urldecode(trim($idUserRaw)); // id numerik

        // Ambil data user dari UserModel
        $user = $this->UserModel
            ->select('id_user, username, email, role, is_active, created_at, updated_at')
            ->where('id_user', $idUser)
            ->first();

        if (! $user) {
            session()->setFlashdata('sweet_error', 'Data user tidak ditemukan.');
            return redirect()->to(base_url('operator/data-user'));
        }

        return view('pages/operator/detail_user', [
            'title'     => 'Detail User | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Detail User',
            'nav_link'  => 'Data User',
            'user'      => $user,
        ]);
    }

    // DATA MATPEL
    public function data_matpel()
    {
        $req = $this->request;
        $q   = trim((string)($req->getGet('q') ?? ''));

        $builder = $this->ModelMatpel
            ->select('id_mapel, kode, nama, is_active') // sesuaikan kolom yang ada
            ->orderBy('nama', 'ASC');

        if ($q !== '') {
            // Pecah jadi beberapa kata; setiap kata harus ketemu di kode ATAU nama
            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            $builder->groupStart();
            foreach ($terms as $t) {
                $builder->groupStart()
                    ->like('kode', $t)
                    ->orLike('nama', $t)
                    ->groupEnd();
            }
            $builder->groupEnd();
        }

        $d_mapel = $builder->findAll();

        return view('pages/operator/data_matpel', [
            'title'     => 'Data Mata Pelajaran | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Mata Pelajaran',
            'nav_link'  => 'Data Mata Pelajaran',
            'd_mapel'   => $d_mapel,
            'q'         => $q, // agar value input tetap terisi
        ]);
    }


    // ====== PAGE: Tambah MatPel ======
    public function page_tambah_matpel()
    {
        // Ambil semua (kalau perlu untuk list/cek) — opsional
        $all = $this->ModelMatpel->orderBy('id_mapel', 'ASC')->findAll();

        // Hitung saran kode berikutnya dari data AKTIF
        $kodeSaran = $this->suggestNextKode(); // e.g. "MP007" atau "003" tergantung data

        $data = [
            'title'      => 'Tambah MatPel | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Tambah Mata Pelajaran',
            'nav_link'   => 'Data Matpel',
            'd_mapel'    => $all,
            'kode_saran' => $kodeSaran,  // Kirim ke view, bisa jadikan default value
        ];
        return view('pages/operator/tambah_mapel', $data);
    }

    // ====== AKSI: Insert MatPel ======
    public function aksi_insert_matpel()
    {
        $rules = [
            'nama' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Nama mata pelajaran wajib diisi.',
                    'min_length' => 'Nama mata pelajaran minimal 3 karakter.',
                ]
            ],
            'is_active'  => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status aktif wajib dipilih.',
                    'in_list'  => 'Status aktif hanya 0 atau 1.',
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nama  = trim((string)$this->request->getPost('nama'));
        $aktif = (int)$this->request->getPost('is_active');

        // KODE DIBUAT DI SERVER (prefix MP + urut)
        $kodeFinal = $this->suggestNextKode('MP', 3);

        // Safety: pastikan belum pernah dipakai (aktif/nonaktif)
        $col = 'kode'; // atau 'kode_mapel'
        $dup = $this->ModelMatpel->where($col, $kodeFinal)->first();
        if ($dup) {
            // Sangat jarang terjadi, tapi aman kalau race condition
            session()->setFlashdata('sweet_error', 'Terjadi bentrok penomoran. Silakan submit ulang.');
            return redirect()->back()->withInput();
        }

        try {
            $this->ModelMatpel->insert([
                $col         => $kodeFinal,
                'nama' => $nama,
                'is_active'  => $aktif,
            ]);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menyimpan data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', "Mata pelajaran <b>{$kodeFinal}</b> berhasil ditambahkan.");
        return redirect()->to(base_url('operator/matpel'));
    }

    private function suggestNextKode(string $prefixDefault = 'MP', int $padDefault = 3): string
    {
        $col = 'kode'; // ganti ke 'kode_mapel' kalau kolommu bernama itu

        $rows = $this->ModelMatpel
            ->select($col)
            ->where('is_active', 1)
            ->like($col, $prefixDefault, 'after')   // hanya kode berawalan "MP"
            ->findAll();

        $maxNum = 0;
        $pad    = $padDefault;

        foreach ($rows as $r) {
            $kode = $this->normalizeKode((string)($r[$col] ?? '')); // trim + strtoupper
            if (strpos($kode, $prefixDefault) !== 0) continue;

            [$pre, $num, $len] = $this->splitKode($kode); // pecah ke [prefix, angka, panjangAngka]
            if ($pre !== $prefixDefault) continue;

            if ($num > $maxNum) {
                $maxNum = $num;
                $pad    = max($pad, $len); // jaga padding jika ada yang lebih panjang
            }
        }

        $nextNum = $maxNum + 1;
        $numStr  = str_pad((string)$nextNum, $pad, '0', STR_PAD_LEFT);
        return $prefixDefault . $numStr; // contoh: "MP001"
    }
    private function normalizeKode(string $kode): string
    {
        return strtoupper(trim($kode));
    }
    private function splitKode(string $kode): array
    {
        if (preg_match('/^(.*?)(\d+)$/', $kode, $m)) {
            $pre = $m[1] ?? '';
            $numStr = $m[2] ?? '';
            $num = (int)$numStr;
            $len = strlen($numStr);
            return [$pre, $num, $len];
        }
        return [$kode, 0, 0]; // tidak ada angka di belakang
    }

    // GET: /operator/edit-matpel/{id}
    public function page_edit_matpel(string $idRaw)
    {
        $id = (int) trim($idRaw);

        // Ambil satu data mapel by id_mapel
        $mapel = $this->ModelMatpel->where('id_mapel', $id)->first();
        if (! $mapel) {
            session()->setFlashdata('sweet_error', 'Mata pelajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/matpel'));
        }

        $data = [
            'title'      => 'Edit MatPel | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit Mata Pelajaran',
            'nav_link'   => 'Data Matpel',
            'mapel'      => $mapel,
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_mapel', $data);
    }


    // PUT: /operator/edit-matpel/{id}
    public function aksi_update_matpel(string $idRaw)
    {
        $id = (int) trim($idRaw);

        $existing = $this->ModelMatpel->where('id_mapel', $id)->first();
        if (! $existing) {
            session()->setFlashdata('sweet_error', 'Mata pelajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/matpel'));
        }

        // Aturan validasi (kolom formulir: kode, nama, is_active)
        // Catatan: ganti 'kode' & 'nama' di bawah jika kolom DB Anda bernama 'kode_mapel' / 'nama_mapel'.
        $rules = [
            'nama' => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama mata pelajaran wajib diisi.',
                    'min_length' => 'Nama minimal 3 karakter.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ]
            ],
            'is_active' => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status aktif wajib dipilih.',
                    'in_list'  => 'Status aktif tidak valid.',
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Ambil input
        $nama     = trim((string) $this->request->getPost('nama'));
        $isActive = (int) $this->request->getPost('is_active');

        // (Opsional) Normalisasi/format kode, mis. "MP001" (biarkan apa adanya jika memang boleh diedit manual)
        // $kode = strtoupper($kode);

        // Update
        $dataUpdate = [
            'nama'      => $nama,
            'is_active' => $isActive,
        ];

        try {
            $this->ModelMatpel->update($id, $dataUpdate);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menyimpan perubahan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Mata pelajaran berhasil diperbarui.');
        return redirect()->to(base_url('operator/matpel'));
    }
    // Controller: Operator.php (contoh)
    public function page_detail_matpel(string $idRaw)
    {
        // Sanitasi & validasi id
        $id = (int) trim($idRaw);
        if (!$id) {
            session()->setFlashdata('sweet_error', 'Parameter tidak valid.');
            return redirect()->to(base_url('operator/matpel'));
        }

        // Ambil data mapel by id_mapel
        $mapel = $this->ModelMatpel
            ->where('id_mapel', $id)
            ->first();

        if (! $mapel) {
            session()->setFlashdata('sweet_error', 'Mata Pelajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/matpel'));
        }

        // Kirim ke view
        return view('pages/operator/detail_mapel', [
            'title'     => 'Detail MatPel | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Detail Mata Pelajaran',
            'nav_link'  => 'Data Matpel',
            'mapel'     => $mapel, // view sudah menormalkan nama kolom (kode/kode_mapel, nama/nama_mapel)
        ]);
    }
    public function aksi_delete_matpel(string $idRaw)
    {

        // Sanitasi ID
        $id = (int) trim($idRaw);
        if (!$id) {
            session()->setFlashdata('sweet_error', 'Parameter tidak valid.');
            return redirect()->to(base_url('operator/matpel'));
        }

        // Ambil data mapel (untuk validasi & pesan)
        $row = $this->ModelMatpel
            ->select('id_mapel, kode, nama, is_active')
            ->where('id_mapel', $id)
            ->first();

        if (! $row) {
            session()->setFlashdata('sweet_error', 'Mata pelajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/matpel'));
        }

        // Normalisasi kolom kode/nama (tergantung skema)
        $kode = (string) ($row['kode'] ?? '');
        $nama = (string) ($row['nama'] ?? '');

        // Cek relasi: sudah dipakai di tb_guru_mapel?
        try {
            $db = \Config\Database::connect();
            $linked = $db->table('tb_guru_mapel')
                ->where('id_mapel', $id)
                ->countAllResults();

            if ($linked > 0) {
                session()->setFlashdata(
                    'sweet_error',
                    'Tidak dapat menghapus karena mata pelajaran ini sudah dipakai pada relasi/penugasan guru.'
                );
                return redirect()->to(base_url('operator/matpel'));
            }
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal memeriksa relasi: ' . $e->getMessage());
            return redirect()->to(base_url('operator/matpel'));
        }

        // Hapus
        try {
            $this->ModelMatpel->delete($id);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menghapus: ' . $e->getMessage());
            return redirect()->to(base_url('operator/matpel'));
        }

        session()->setFlashdata(
            'sweet_success',
            'Mata pelajaran' . ($nama ? " <b>{$nama}</b>" : '') . ($kode ? " ({$kode})" : '') . ' berhasil dihapus.'
        );
        return redirect()->to(base_url('operator/matpel'));
    }

    // TAHUN AJARAN 
    public function data_tahun_ajaran()
    {
        $req = $this->request;
        $q   = trim((string)($req->getGet('q') ?? ''));

        $builder = $this->TahunAjaran
            ->select('id_tahun_ajaran, tahun, semester, is_active'/* , 'start_date', 'end_date', 'keterangan' */) // sesuaikan kolom yang ada
            ->orderBy('is_active', 'DESC')
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'DESC');

        if ($q !== '') {
            // tiap kata harus match di tahun/semester/(keterangan jika ada)
            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($terms as $t) {
                $builder->groupStart()
                    ->like('tahun', $t)
                    ->orLike('semester', $t)
                    // ->orLike('keterangan', $t) // buka jika kolom ada
                    ->groupEnd();
            }
        }

        $rows = $builder->findAll();

        return view('pages/operator/data_tahun_ajaran', [
            'title'          => 'Tahun Ajaran | SDN Talun 2 Kota Serang',
            'sub_judul'      => 'Tahun Ajaran',
            'nav_link'       => 'Tahun Ajaran',
            'd_TahunAjaran'  => $rows,
            'q'              => $q, // agar nilai input tetap terisi
        ]);
    }

    public function tambah_tahun_ajaran()
    {
        $data = [
            'title'      => 'Tambah Tahun Ajaran | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Tambah Tahun Ajaran',
            'nav_link'  => 'Tambah Tahun Ajaran',
            'validation'    => \Config\Services::validation(),

        ];
        return view('pages/operator/tambah_tahun_ajaran', $data);
    }
    public function aksi_tahun_ajaran()
    {

        $data = [
            'tahun'      => trim((string) $this->request->getPost('tahun')),
            'semester'   => trim((string) $this->request->getPost('semester')),
            'start_date' => (string) $this->request->getPost('start_date'),
            'end_date'   => (string) $this->request->getPost('end_date'),
            // hidden input 0 + switch 1 -> gunakan hadirnya checkbox
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Validasi dasar
        $rules = [
            'tahun'      => [
                'label'  => 'Tahun',
                'rules'  => 'required|regex_match[/^\d{4}\/\d{4}$/]',
                'errors' => [
                    'required'    => 'Tahun wajib diisi.',
                    'regex_match' => 'Format tahun harus YYYY/YYYY (contoh: 2024/2025).',
                ],
            ],
            'semester'   => [
                'label'  => 'Semester',
                'rules'  => 'required|in_list[ganjil,genap]',
                'errors' => [
                    'required' => 'Semester wajib dipilih.',
                    'in_list'  => 'Semester tidak valid.',
                ],
            ],
            'start_date' => [
                'label'  => 'Mulai',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'  => 'Tanggal mulai wajib diisi.',
                    'valid_date' => 'Tanggal mulai tidak valid.',
                ],
            ],
            'end_date'   => [
                'label'  => 'Selesai',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'  => 'Tanggal selesai wajib diisi.',
                    'valid_date' => 'Tanggal selesai tidak valid.',
                ],
            ],
            'is_active'  => [
                'label'  => 'Status',
                'rules'  => 'permit_empty|in_list[0,1]',
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validasi lanjutan (logika bisnis)
        $errors = [];

        // 1) Tahun ajaran harus berurutan: 2024/2025 -> 2025 = 2024+1
        [$y1, $y2] = array_map('intval', explode('/', $data['tahun']));
        if ($y2 !== $y1 + 1) {
            $errors['tahun'] = 'Tahun ajaran harus berurutan, misal 2024/2025 (tahun kedua = tahun pertama + 1).';
        }

        // 2) start_date <= end_date
        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors['end_date'] = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
        }

        $model = new TahunAjaranModel();

        // 3) Unique kombinasi tahun+semester
        $exist = $model->where([
            'tahun'    => $data['tahun'],
            'semester' => $data['semester'],
        ])->first();

        if ($exist) {
            $errors['tahun'] = 'Kombinasi Tahun dan Semester sudah ada.';
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Jika set aktif, nonaktifkan yang lain
        if ((int) $data['is_active'] === 1) {
            // Hindari update masal tanpa kondisi tabel
            $model->where('is_active', 1)->set(['is_active' => 0])->update();
        }

        // Simpan
        try {
            $model->insert($data);
        } catch (\Throwable $e) {
            // Jika DB punya UNIQUE KEY (tahun, semester), antisipasi error DB
            return redirect()->back()->withInput()->with('errors', [
                'tahun' => 'Gagal menyimpan. Pastikan kombinasi Tahun & Semester belum terdaftar.',
            ]);
        }

        return redirect()->to(site_url('operator/tahun-ajaran'))
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }
    public function page_edit_tahun_ajaran(string $idRaw)
    {
        $id = (int) trim($idRaw);

        // Ambil satu data mapel by id_mapel
        $TahunAjaran = $this->TahunAjaran->where('id_tahun_ajaran', $id)->first();
        if (! $TahunAjaran) {
            session()->setFlashdata('sweet_error', 'Tahun Ajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        $data = [
            'title'      => 'Edit Tahun Ajaran | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit Tahun Ajaran',
            'nav_link'   => 'Data Tahun Ajaran',
            'd_TahunAjaran'      => $TahunAjaran,
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_tahun_ajaran', $data);
    }
    // Di controller: app/Controllers/Operator/TahunAjaranController.php
    // use App\Models\TahunAjaranModel;  // opsional, jika tidak pakai FQCN di bawah

    public function aksi_edit_tahun_ajaran($id = null)
    {
        $req = $this->request;
        $id  = (int) $id;

        if ($id <= 0) {
            session()->setFlashdata('sweet_error', 'ID tidak valid.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        $model = new \App\Models\TahunAjaranModel();

        // Pastikan data ada
        $row = $model->where('id_tahun_ajaran', $id)->first();
        if (! $row) {
            session()->setFlashdata('sweet_error', 'Data tahun ajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        // ---------- RULES ----------
        $rules = [
            'tahun' => [
                'rules'  => 'required|regex_match[/^\d{4}\/\d{4}$/]',
                'errors' => [
                    'required'    => 'Tahun wajib diisi.',
                    'regex_match' => 'Format tahun harus YYYY/YYYY (contoh: 2024/2025).',
                ]
            ],
            'semester' => [
                'rules'  => 'required|in_list[ganjil,genap]',
                'errors' => [
                    'required' => 'Semester wajib dipilih.',
                    'in_list'  => 'Semester tidak valid.',
                ]
            ],
            'start_date' => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'  => 'Tanggal mulai wajib diisi.',
                    'valid_date' => 'Tanggal mulai tidak valid.',
                ]
            ],
            'end_date' => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'  => 'Tanggal selesai wajib diisi.',
                    'valid_date' => 'Tanggal selesai tidak valid.',
                ]
            ],
            'is_active' => [
                'rules'  => 'permit_empty|in_list[0,1]',
                'errors' => [
                    'in_list' => 'Status aktif hanya boleh 0 (nonaktif) atau 1 (aktif).',
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ---------- AMBIL INPUT ----------
        $tahun     = trim((string) $req->getPost('tahun'));
        $semester  = strtolower(trim((string) $req->getPost('semester'))); // ganjil/genap
        $startDate = (string) $req->getPost('start_date');
        $endDate   = (string) $req->getPost('end_date');
        $isActive  = (int) $req->getPost('is_active'); // 0/1 (hidden 0 + switch 1)

        $dataUpdate = [
            'tahun'      => $tahun,
            'semester'   => $semester,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'is_active'  => $isActive,
        ];

        // ---------- VALIDASI LANJUTAN (LOGIKA BISNIS) ----------
        $errors = [];

        // 1) Tahun ajaran berurutan: 2024/2025 -> 2025 = 2024 + 1
        if (preg_match('/^\d{4}\/\d{4}$/', $tahun)) {
            [$y1, $y2] = array_map('intval', explode('/', $tahun));
            if ($y2 !== $y1 + 1) {
                $errors['tahun'] = 'Tahun ajaran harus berurutan, misal 2024/2025 (tahun kedua = tahun pertama + 1).';
            }
        }

        // 2) start_date <= end_date
        if (strtotime($startDate) > strtotime($endDate)) {
            $errors['end_date'] = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
        }

        // 3) Unik kombinasi tahun + semester (kecuali baris saat ini)
        $dup = $model->where('tahun', $tahun)
            ->where('semester', $semester)
            ->where('id_tahun_ajaran !=', $id)
            ->first();

        if ($dup) {
            // bisa ditempelkan di 'tahun' atau 'semester'
            $errors['tahun'] = 'Kombinasi Tahun dan Semester sudah terdaftar.';
        }

        if (! empty($errors)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Jika set aktif, nonaktifkan yang lain
        if ((int) $isActive === 1) {
            $model->where('id_tahun_ajaran !=', $id)->set(['is_active' => 0])->update();
        }

        // ---------- UPDATE ----------
        try {
            $model->update($id, $dataUpdate);
        } catch (\Throwable $e) {
            // Antisipasi error DB (mis. unique index)
            session()->setFlashdata('sweet_error', 'Gagal memperbarui data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Tahun ajaran berhasil diperbarui.');
        return redirect()->to(base_url('operator/tahun-ajaran'));
    }

    public function page_edit_detail_tahun_ajaran($id = null)
    {
        $id = (int) $id;
        if (!$id) {
            session()->setFlashdata('sweet_error', 'ID tidak valid.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        $model = new TahunAjaranModel();
        $row = $model->where('id_tahun_ajaran', $id)->first();

        if (! $row) {
            session()->setFlashdata('sweet_error', 'Data tahun ajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        $data = [
            'title'      => 'Detail Tahun Ajaran | SDN Talun 2 Kota Serang',
            'sub_judul'      => 'Detail Tahun Ajaran',
            'nav_link'      => 'Detail Tahun Ajaran',
            'd_TahunAjaran'  => $row,
        ];

        return view('pages/operator/detail_tahun_ajaran', $data);
    }

    public function delete_tahun_ajaran($id = null)
    {
        $id = (int) $id;
        if (!$id) {
            session()->setFlashdata('sweet_error', 'ID tidak valid.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        $model = new TahunAjaranModel();
        $row = $model->where('id_tahun_ajaran', $id)->first();

        if (! $row) {
            session()->setFlashdata('sweet_error', 'Data tahun ajaran tidak ditemukan.');
            return redirect()->to(base_url('operator/tahun-ajaran'));
        }

        // Opsional: blok jika masih aktif
        if ((int)($row['is_active'] ?? 0) === 1) {
            session()->setFlashdata('sweet_error', 'Tidak bisa menghapus data yang sedang Aktif. Nonaktifkan dulu.');
            return redirect()->back();
        }

        try {
            $model->delete($id); // hard/soft tergantung konfigurasi model
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menghapus. Data mungkin terkait modul lain.');
            return redirect()->back();
        }

        session()->setFlashdata('sweet_success', 'Tahun ajaran berhasil dihapus.');
        return redirect()->to(base_url('operator/tahun-ajaran'));
    }

    // GURU MAPEL
    public function page_tambah_guru_mapel($nipGuru = null)
    {
        // Inisialisasi model
        $guruModel   = new \App\Models\ModelGuru();        // id_guru, nama_lengkap, nip
        $mapelModel  = new \App\Models\ModelMatPel();      // id_mapel, nama, kode, is_active
        $taModel     = new \App\Models\TahunAjaranModel(); // id_tahun_ajaran, tahun, semester, is_active
        $kelasModel  = new \App\Models\KelasModel();       // id_kelas, nama_kelas, tingkat, jurusan
        $gmModel     = new \App\Models\GuruMatpel();       // tb_guru_mapel

        // Dropdown dasar
        $guruList  = $guruModel->select('id_guru, nama_lengkap, nip')
            ->orderBy('nama_lengkap', 'ASC')->findAll();
        $tahunList = $taModel->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('is_active', 'DESC')->orderBy('tahun', 'DESC')->findAll();
        $kelasList = $kelasModel->select('id_kelas, nama_kelas, tingkat, jurusan')
            ->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll();

        // Cari TA aktif (opsional)
        $idTaAktif = null;
        foreach ($tahunList as $t) {
            if ((int)($t['is_active'] ?? 0) === 1) {
                $idTaAktif = (int)$t['id_tahun_ajaran'];
                break;
            }
        }

        // Preselect & GUARD jika NIP diberikan
        $d_row  = [];
        $idGuru = null;

        if (!empty($nipGuru)) {
            $guru = $guruModel->where('nip', (string)$nipGuru)->first();
            if (!$guru) {
                session()->setFlashdata('sweet_error', 'Guru dengan NIP ' . esc($nipGuru) . ' tidak ditemukan.');
                return redirect()->to(base_url('operator/data-guru'));
            }

            $idGuru = (int)$guru['id_guru'];

            // GUARD: tolak jika sudah ada penugasan pada TA aktif (atau global bila diinginkan)
            $sudahAda = false;
            if ($idTaAktif) {
                $sudahAda = $gmModel->where([
                    'id_guru'         => $idGuru,
                    'id_tahun_ajaran' => $idTaAktif,
                ])->countAllResults(true) > 0;
            } else {
                // Guard global (jika tidak pakai TA aktif)
                // $sudahAda = $gmModel->where('id_guru', $idGuru)->countAllResults(true) > 0;
            }

            if ($sudahAda) {
                session()->setFlashdata(
                    'sweet_error',
                    'Guru ini sudah memiliki penugasan' . ($idTaAktif ? ' pada Tahun Ajaran aktif.' : '.')
                );
                return redirect()->to(base_url('operator/data-guru'));
            }

            // Preselect form
            $d_row['id_guru'] = (string)$idGuru;
            if ($idTaAktif) {
                $d_row['id_tahun_ajaran'] = (string)$idTaAktif;
            }
        }

        // === FILTER MAPEL: hanya mapel AKTIF dan belum dimiliki guru ini (opsional: pada TA aktif) ===
        if ($idGuru) {
            // Taruh filter penugasan di ON agar tetap LEFT JOIN
            $on = 'gm.id_mapel = tb_mapel.id_mapel AND gm.id_guru = ' . (int)$idGuru;
            if ($idTaAktif) {
                $on .= ' AND gm.id_tahun_ajaran = ' . (int)$idTaAktif;
            }
            // Jika pakai SoftDeletes di tb_guru_mapel:
            // $on .= ' AND gm.deleted_at IS NULL';

            $mapelList = $mapelModel
                ->select('tb_mapel.id_mapel, tb_mapel.nama, tb_mapel.kode')
                ->join('tb_guru_mapel gm', $on, 'left')
                ->where('gm.id_mapel', null)                // belum dimiliki guru ini (di TA aktif bila diset)
                ->where('tb_mapel.is_active', 1)        // HANYA MAPEL AKTIF  ← ubah ke is_active jika beda
                // ->where('tb_mapel.deleted_at', null)      // jika tb_mapel pakai SoftDeletes
                ->orderBy('tb_mapel.nama', 'ASC')
                ->findAll();
        } else {
            // Tanpa idGuru → tampilkan hanya mapel aktif (tanpa filter penugasan)
            $mapelList = $mapelModel
                ->select('id_mapel, nama, kode')
                ->where('is_active', 1)                 // HANYA MAPEL AKTIF  ← ubah ke is_active jika beda
                // ->where('deleted_at', null)               // jika tb_mapel pakai SoftDeletes
                ->orderBy('nama', 'ASC')
                ->findAll();
        }

        // Kirim ke view
        return view('pages/operator/guru_mapel', [
            'title'      => 'Tambah Guru Mapel | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Tambah Guru Mapel',
            'nav_link'   => 'Tambah Guru Mapel',
            'guruList'   => $guruList,
            'mapelList'  => $mapelList,   // ← hanya mapel aktif & belum dimiliki guru
            'tahunList'  => $tahunList,
            'kelasList'  => $kelasList,
            'd_row'      => $d_row,
            'idTaAktif'  => $idTaAktif,
        ]);
    }

    public function aksi_tambah_guru_mapel()
    {
        $req = $this->request;

        // ---------- VALIDASI DASAR ----------
        $rules = [
            'id_guru'         => 'required|is_natural_no_zero',
            'id_mapel'        => 'required|is_natural_no_zero',
            'id_tahun_ajaran' => 'required|is_natural_no_zero',
            'id_kelas'        => 'required|is_natural_no_zero',
            'jam_per_minggu'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[40]',
            'keterangan'      => 'permit_empty|max_length[255]',
        ];
        $labels = [
            'id_guru'         => ['required' => 'ID Guru wajib diisi.', 'is_natural_no_zero' => 'ID Guru tidak valid.'],
            'id_mapel'        => ['required' => 'Mapel wajib dipilih.', 'is_natural_no_zero' => 'Mapel tidak valid.'],
            'id_tahun_ajaran' => ['required' => 'Tahun ajaran wajib dipilih.', 'is_natural_no_zero' => 'Tahun ajaran tidak valid.'],
            'id_kelas'        => ['required' => 'Kelas wajib dipilih.', 'is_natural_no_zero' => 'Kelas tidak valid.'],
            'jam_per_minggu'  => [
                'required' => 'Jam/minggu wajib diisi.',
                'integer' => 'Jam/minggu harus bilangan bulat.',
                'greater_than_equal_to' => 'Jam/minggu minimal 0.',
                'less_than_equal_to'   => 'Jam/minggu maksimal 40.'
            ],
            'keterangan'      => ['max_length' => 'Keterangan maksimal 255 karakter.'],
        ];

        if (!$this->validate($rules, $labels)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ---------- AMBIL INPUT ----------
        $idGuru  = (int) $req->getPost('id_guru');
        $idMapel = (int) $req->getPost('id_mapel');
        $idTA    = (int) $req->getPost('id_tahun_ajaran');
        $idKelas = (int) $req->getPost('id_kelas');
        $jam     = (int) $req->getPost('jam_per_minggu');
        $ket     = trim((string) $req->getPost('keterangan'));

        // ---------- CEK FK ----------
        $guruModel   = new \App\Models\ModelGuru();
        $mapelModel  = new \App\Models\ModelMatPel();
        $taModel     = new \App\Models\TahunAjaranModel();
        $kelasModel  = new \App\Models\KelasModel();

        if (!$guruModel->find($idGuru)) {
            session()->setFlashdata('sweet_error', 'ID Guru tidak sesuai / tidak ditemukan.');
            return redirect()->back()->withInput()->with('errors', ['id_guru' => 'ID Guru tidak ditemukan.']);
        }
        if (!$mapelModel->find($idMapel)) {
            session()->setFlashdata('sweet_error', 'Mata pelajaran tidak ditemukan.');
            return redirect()->back()->withInput()->with('errors', ['id_mapel' => 'Mapel tidak ditemukan.']);
        }
        if (!$taModel->where('id_tahun_ajaran', $idTA)->first()) {
            session()->setFlashdata('sweet_error', 'Tahun ajaran tidak ditemukan.');
            return redirect()->back()->withInput()->with('errors', ['id_tahun_ajaran' => 'Tahun ajaran tidak ditemukan.']);
        }
        if (!$kelasModel->find($idKelas)) {
            session()->setFlashdata('sweet_error', 'Kelas tidak ditemukan.');
            return redirect()->back()->withInput()->with('errors', ['id_kelas' => 'Kelas tidak ditemukan.']);
        }

        // ---------- CEK KONFLIK mapel+kelas+TA (harus unik, hanya 1 guru) ----------
        $gmModel = new \App\Models\GuruMatpel(); // table: tb_guru_mapel
        $konflik = $gmModel->where([
            'id_mapel'        => $idMapel,
            'id_kelas'        => $idKelas,
            'id_tahun_ajaran' => $idTA,
        ])->first();

        if ($konflik) {
            // (opsional) detailkan pesan
            $m  = $mapelModel->find($idMapel);
            $k  = $kelasModel->find($idKelas);
            $nm = $m['nama']        ?? 'Mapel';
            $kl = $k['nama_kelas']  ?? 'Kelas';
            session()->setFlashdata('sweet_error', "{$nm} pada {$kl} untuk Tahun Ajaran ini sudah diajar oleh guru lain.");
            return redirect()->back()->withInput()->with('errors', [
                'id_mapel'        => 'Sudah diampu guru lain.',
                'id_kelas'        => 'Sudah diampu guru lain.',
                'id_tahun_ajaran' => 'Sudah diampu guru lain.',
            ]);
        }

        // ---------- CEK DUPLIKASI baris identik (guru yang sama) ----------
        $dup = $gmModel->where([
            'id_guru'         => $idGuru,
            'id_mapel'        => $idMapel,
            'id_tahun_ajaran' => $idTA,
            'id_kelas'        => $idKelas,
        ])->first();

        if ($dup) {
            session()->setFlashdata('sweet_error', 'Penugasan sudah ada untuk guru & kelas tersebut.');
            return redirect()->back()->withInput()->with('errors', [
                'id_guru'         => 'Duplikat penugasan.',
                'id_mapel'        => 'Duplikat penugasan.',
                'id_tahun_ajaran' => 'Duplikat penugasan.',
                'id_kelas'        => 'Duplikat penugasan.',
            ]);
        }

        // ---------- INSERT ----------
        try {
            $gmModel->insert([
                'id_guru'         => $idGuru,
                'id_mapel'        => $idMapel,
                'id_tahun_ajaran' => $idTA,
                'id_kelas'        => $idKelas,
                'jam_per_minggu'  => $jam,
                'keterangan'      => $ket ?: null,
            ]);
        } catch (\Throwable $e) {
            // Jika sudah pasang UNIQUE INDEX (lihat catatan di bawah), bisa kena error 1062 di sini
            session()->setFlashdata('sweet_error', 'Gagal menyimpan data (mungkin duplikat atau kendala server).');
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('sweet_success', 'Data penugasan guru–mapel berhasil ditambahkan.');
        return redirect()->to(base_url('operator/data-guru'));
    }

    public function page_edit_guru_mapel($idGuruMapel = null)
    {
        $idGuruMapel = (int) ($idGuruMapel ?? 0);
        if ($idGuruMapel <= 0) {
            session()->setFlashdata('sweet_error', 'ID penugasan tidak valid.');
            return redirect()->to(base_url('operator/guru-mapel'));
        }

        $row = $this->ModelGuruMatpel->find($idGuruMapel);
        if (! $row) {
            session()->setFlashdata('sweet_error', 'Data penugasan tidak ditemukan.');
            return redirect()->to(base_url('operator/guru-mapel'));
        }

        $idGuru = (int)($row['id_guru'] ?? 0);
        if ($idGuru <= 0) {
            session()->setFlashdata('sweet_error', 'ID Guru pada penugasan tidak valid.');
            return redirect()->to(base_url('operator/guru-mapel'));
        }

        $guru = $this->ModelGuru->select('id_guru, nama_lengkap, nip')->find($idGuru);
        if (! $guru) {
            session()->setFlashdata('sweet_error', 'Guru dengan ID ' . $idGuru . ' tidak ditemukan.');
            return redirect()->to(base_url('operator/guru-mapel'));
        }

        $mapelList = $this->ModelMatpel
            ->select('id_mapel, nama, kode')->orderBy('nama', 'ASC')->findAll();
        $tahunList = $this->TahunAjaran
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('is_active', 'DESC')->orderBy('tahun', 'DESC')->findAll();
        $kelasList = $this->ModelKelas
            ->select('id_kelas, nama_kelas, tingkat, jurusan')
            ->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll();

        $d_row = [
            // === PK wajib ikut dikirim ke view ===
            'id_guru_mapel'   => old('id_guru_mapel', (string)$idGuruMapel),

            'id_guru'         => old('id_guru',         (string)$idGuru),
            'id_mapel'        => old('id_mapel',        (string)$row['id_mapel']),
            'id_tahun_ajaran' => old('id_tahun_ajaran', (string)$row['id_tahun_ajaran']),
            'id_kelas'        => old('id_kelas',        (string)$row['id_kelas']),
            'jam_per_minggu'  => old('jam_per_minggu',  (string)($row['jam_per_minggu'] ?? '0')),
            'keterangan'      => old('keterangan',      (string)($row['keterangan'] ?? '')),

            'nama_lengkap'    => (string)$guru['nama_lengkap'],
            'nip'             => (string)$guru['nip'],
        ];

        return view('pages/operator/edit_guru_mapel', [
            'title'        => 'Edit Guru Mapel | SDN Talun 2 Kota Serang',
            'sub_judul'    => 'Edit Guru Mapel',
            'nav_link'     => 'Edit Guru Mapel',
            'mapelList'    => $mapelList,
            'tahunList'    => $tahunList,
            'kelasList'    => $kelasList,
            'd_row'        => $d_row,
            'idGuru'       => $idGuru,
            'idGuruMapel'  => $idGuruMapel, // tetap dikirim untuk action URL
        ]);
    }
    public function aksi_update_guru_mapel($idGuruMapel = null)
    {

        $idGuruMapel = (int) ($idGuruMapel ?? 0);
        if ($idGuruMapel <= 0) {
            return redirect()->to(base_url('operator/penugasan-guru'))
                ->with('sweet_error', 'ID penugasan tidak valid.');
        }

        $row = $this->ModelGuruMatpel->find($idGuruMapel);
        if (! $row) {
            return redirect()->to(base_url('operator/penugasan-guru'))
                ->with('sweet_error', 'Data penugasan tidak ditemukan.');
        }

        $idGuru = (int) ($row['id_guru'] ?? 0);
        if ($idGuru <= 0) {
            return redirect()->to(base_url('operator/penugasan-guru'))
                ->with('sweet_error', 'Relasi guru pada penugasan tidak valid.');
        }

        // validasi input
        $rules = [
            'id_mapel' => [
                'label'  => 'Mata Pelajaran',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_mapel.id_mapel]',
                'errors' => [
                    'required'            => '{field} wajib dipilih.',
                    'is_natural_no_zero'  => '{field} tidak valid.',
                    'is_not_unique'       => '{field} tidak terdaftar di database.',
                ],
            ],
            'id_tahun_ajaran' => [
                'label'  => 'Tahun Ajaran',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_tahun_ajaran.id_tahun_ajaran]',
                'errors' => [
                    'required'            => '{field} wajib dipilih.',
                    'is_natural_no_zero'  => '{field} tidak valid.',
                    'is_not_unique'       => '{field} tidak terdaftar di database.',
                ],
            ],
            'id_kelas' => [
                'label'  => 'Kelas',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_kelas.id_kelas]',
                'errors' => [
                    'required'            => '{field} wajib dipilih.',
                    'is_natural_no_zero'  => '{field} tidak valid.',
                    'is_not_unique'       => '{field} tidak terdaftar di database.',
                ],
            ],
            'jam_per_minggu' => [
                'label'  => 'Jam per Minggu',
                'rules'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[40]',
                'errors' => [
                    'required'                 => '{field} wajib diisi.',
                    'integer'                  => '{field} harus berupa bilangan bulat.',
                    'greater_than_equal_to'    => '{field} minimal {param}.',
                    'less_than_equal_to'       => '{field} maksimal {param}.',
                ],
            ],
            'keterangan' => [
                'label'  => 'Keterangan',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => '{field} maksimal {param} karakter.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('sweet_error', 'Periksa kembali isian Anda.');
        }

        $newData = [
            'id_mapel'        => (int) $this->request->getPost('id_mapel'),
            'id_tahun_ajaran' => (int) $this->request->getPost('id_tahun_ajaran'),
            'id_kelas'        => (int) $this->request->getPost('id_kelas'),
            'jam_per_minggu'  => (int) $this->request->getPost('jam_per_minggu'),
            'keterangan'      => ($k = trim((string) $this->request->getPost('keterangan'))) !== '' ? $k : null,
        ];

        // cek duplikat kombinasi (id_guru tetap)
        $dupe = $this->ModelGuruMatpel
            ->where([
                'id_guru'         => $idGuru,
                'id_mapel'        => $newData['id_mapel'],
                'id_tahun_ajaran' => $newData['id_tahun_ajaran'],
                'id_kelas'        => $newData['id_kelas'],
            ])
            ->where('id_guru_mapel !=', $idGuruMapel)
            ->first();

        if ($dupe) {
            return redirect()->back()->withInput()
                ->with('errors', ['id_mapel' => 'Kombinasi guru–mapel–kelas–tahun sudah ada.'])
                ->with('sweet_error', 'Penugasan duplikat.');
        }

        try {
            $this->ModelGuruMatpel->update($idGuruMapel, $newData);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('sweet_error', 'Gagal memperbarui: ' . $e->getMessage());
        }

        return redirect()->to(base_url('operator/penugasan-guru'))
            ->with('sweet_success', 'Penugasan berhasil diperbarui.');
    }

    public function aksi_delete_guru_mapel($idGuruMapel = null)
    {
        // Validasi ID
        $id = (int) ($idGuruMapel ?? 0);
        if ($id <= 0) {
            return redirect()->to(base_url('operator/penugasan-guru'))
                ->with('sweet_error', 'ID penugasan tidak valid.');
        }

        // Pastikan data ada
        $row = $this->ModelGuruMatpel->find($id);
        if (! $row) {
            return redirect()->to(base_url('operator/penugasan-guru'))
                ->with('sweet_error', 'Data penugasan tidak ditemukan.');
        }

        // Hapus (soft delete kalau model di-set demikian)
        try {
            $this->ModelGuruMatpel->delete($id);
        } catch (\Throwable $e) {
            // Tangkap error constraint (FK): MySQL 1451, Postgres 23503
            $msg = $e->getMessage();
            if (preg_match('/1451|23503/', $msg)) {
                return redirect()->to(base_url('operator/penugasan-guru'))
                    ->with('sweet_error', 'Data tidak dapat dihapus karena masih digunakan pada data lain.');
            }
            return redirect()->to(base_url('operator/penugasan-guru'))
                ->with('sweet_error', 'Gagal menghapus: ' . $e->getMessage());
        }

        return redirect()->to(base_url('operator/penugasan-guru'))
            ->with('sweet_success', 'Penugasan berhasil dihapus.');
    }

    public function data_penugasan()
    {
        $q     = trim((string) $this->request->getGet('q'));
        $tahun = trim((string) $this->request->getGet('tahun'));

        $builder = $this->ModelGuruMatpel
            ->select("
            tb_guru_mapel.id_guru_mapel,
            tb_guru_mapel.id_guru_mapel AS id,
            tb_guru_mapel.id_guru,
            tb_guru_mapel.id_mapel,
            tb_guru_mapel.id_tahun_ajaran,
            tb_guru_mapel.id_kelas,
            tb_guru_mapel.jam_per_minggu,
            tb_guru_mapel.keterangan,
            g.nama_lengkap,
            m.nama,                               -- konsisten dgn view yg pakai \$p['nama']
            k.nama_kelas AS kelas,
            t.tahun
        ")
            ->join('tb_guru g',         'g.id_guru = tb_guru_mapel.id_guru', 'left')
            ->join('tb_mapel m',        'm.id_mapel = tb_guru_mapel.id_mapel', 'left')
            ->join('tb_kelas k',        'k.id_kelas = tb_guru_mapel.id_kelas', 'left')
            ->join('tb_tahun_ajaran t', 't.id_tahun_ajaran = tb_guru_mapel.id_tahun_ajaran', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('g.nama_lengkap', $q)
                ->orLike('m.nama', $q)
                ->orLike('k.nama_kelas', $q)
                ->orLike('t.tahun', $q)
                ->groupEnd();
        }

        if ($tahun !== '') {
            $builder->where('t.tahun', $tahun);
        }

        $builder->orderBy('t.tahun', 'DESC')
            ->orderBy('g.nama_lengkap', 'ASC');

        $d_penugasan = $builder->findAll();

        // Dropdown Tahun Ajaran (distinct tahun -> id terakhir)
        $list_tahun_ajaran = $this->TahunAjaran
            ->select('tahun, MAX(id_tahun_ajaran) AS id_tahun_ajaran', false)
            ->groupBy('tahun')
            ->orderBy('tahun', 'DESC')
            ->findAll();

        return view('pages/operator/data-penugasan', [
            'title'             => 'Data Penugasan | SDN Talun 2 Kota Serang',
            'sub_judul'         => 'Data Penugasan Guru',
            'nav_link'          => 'Data Penugasan',
            'd_penugasan'       => $d_penugasan,
            'q'                 => $q,
            'tahun'             => $tahun,
            'list_tahun_ajaran' => $list_tahun_ajaran,
        ]);
    }





    // KELAS
    public function data_kelas()
    {
        $req = $this->request;
        $q   = trim((string)($req->getGet('q') ?? ''));

        $builder = $this->ModelKelas
            ->select('id_kelas, nama_kelas, tingkat, jurusan') // sesuaikan kolom yang ada
            ->orderBy('tingkat', 'ASC')
            ->orderBy('nama_kelas', 'ASC');

        if ($q !== '') {
            // setiap kata harus match di salah satu kolom (AND antar-kata, OR antar-kolom)
            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($terms as $t) {
                $builder->groupStart()
                    ->like('id_kelas', $t)
                    ->orLike('nama_kelas', $t)
                    ->orLike('tingkat', $t)
                    ->orLike('jurusan', $t)
                    ->groupEnd();
            }
        }

        $data_kelas = $builder->findAll();

        return view('pages/operator/data_kelas', [
            'title'     => 'Data Kelas | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Kelas',
            'nav_link'  => 'Data Kelas',
            'd_kelas'   => $data_kelas,
            'q'         => $q,  // biar nilai input tetap terisi
        ]);
    }

    public function page_tambah_kelas()
    {
        $data = [
            'title'      => 'Tambah Kelas | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Tambah Kelas',
            'nav_link' => 'Tambah Kelas'
        ];
        return view('pages/operator/tambah_kelas', $data);
    }
    // Controller: Operator/KelasController.php (atau OperatorController.php)

    public function aksi_insert_kelas()
    {
        // Ambil input & normalisasi
        $nama_kelas = trim((string) $this->request->getPost('nama_kelas'));
        $jurusan    = trim((string) $this->request->getPost('jurusan'));
        $tingkat    = trim((string) $this->request->getPost('tingkat'));

        // Validasi form
        $validation = \Config\Services::validation();
        $rules = [
            'nama_kelas' => [
                'label'  => 'Nama Kelas',
                'rules'  => 'required|min_length[1]|max_length[50]',
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'min_length' => '{field} terlalu pendek.',
                    'max_length' => '{field} terlalu panjang.'
                ]
            ],
            'tingkat' => [
                'label'  => 'Tingkat',
                'rules'  => 'required|in_list[1,2,3,4,5,6]',
                'errors' => [
                    'required' => '{field} wajib dipilih.',
                    'in_list'  => '{field} tidak valid.'
                ]
            ],
            'jurusan' => [
                'label'  => 'Jurusan',
                'rules'  => 'permit_empty|max_length[50]',
                'errors' => [
                    'max_length' => '{field} terlalu panjang.'
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            // kirim balik error + old input
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors())
                ->with('sweet_error', 'Periksa kembali isian Anda.');
        }

        // Cek duplikat (kebijakan: Nama Kelas harus unik)
        $duplikat = $this->ModelKelas
            ->where('LOWER(nama_kelas)', strtolower($nama_kelas))
            ->first();

        if ($duplikat) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['nama_kelas' => 'Nama kelas sudah terdaftar.'])
                ->with('sweet_error', 'Nama kelas sudah ada.');
        }

        // Siapkan data simpan
        $dataInsert = [
            'nama_kelas' => $nama_kelas,
            'jurusan'    => ($jurusan === '') ? null : $jurusan,
            'tingkat'    => (int) $tingkat,
        ];

        try {
            $this->ModelKelas->insert($dataInsert);
        } catch (\Throwable $e) {
            // Tangani error DB
            return redirect()->back()
                ->withInput()
                ->with('sweet_error', 'Gagal menyimpan data: ' . $e->getMessage());
        }

        return redirect()->to(site_url('operator/kelas'))
            ->with('sweet_success', 'Kelas berhasil ditambahkan.');
    }
    public function page_edit_kelas($id = null)
    {
        $data_byID = $this->ModelKelas->find($id);
        if (!$data_byID) {
            session()->setFlashdata('sweet_error', 'ID tidak valid.');
            return redirect()->to(base_url('operator/kelas'));
        }
        $data = [
            'title'      => 'Edit Kelas | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Edit Kelas',
            'nav_link' => 'Edit Kelas',
            'data_kelas' => $data_byID
        ];
        return view('pages/operator/edit_kelas', $data);
    }
    public function aksi_update_kelas($id = null)
    {

        $row = $this->ModelKelas->find($id);
        if (!$row) {
            return redirect()->to(site_url('operator/kelas'))->with('sweet_error', 'ID tidak valid.');
        }

        // Ambil input
        $nama_kelas = trim((string) $this->request->getPost('nama_kelas'));
        $jurusan    = trim((string) $this->request->getPost('jurusan'));
        $tingkat    = trim((string) $this->request->getPost('tingkat'));

        // Validasi
        $rules = [
            'nama_kelas' => 'required|min_length[1]|max_length[50]',
            'tingkat'    => 'required|in_list[1,2,3,4,5,6]',
            'jurusan'    => 'permit_empty|max_length[50]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('sweet_error', 'Periksa kembali isian Anda.');
        }

        // Unik: nama_kelas (opsional: plus tingkat). Hilangkan diri sendiri dari cek.
        $cek = $this->ModelKelas
            ->where('LOWER(nama_kelas)', strtolower($nama_kelas))
            // ->where('tingkat', (int) $tingkat) // aktifkan jika uniknya per (nama_kelas + tingkat)
            ->where('id_kelas !=', $id)
            ->first();

        if ($cek) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['nama_kelas' => 'Nama kelas sudah digunakan.'])
                ->with('sweet_error', 'Nama kelas sudah ada.');
        }

        $dataUpdate = [
            'nama_kelas' => $nama_kelas,
            'jurusan'    => ($jurusan === '') ? null : $jurusan,
            'tingkat'    => (int) $tingkat,
        ];

        try {
            $this->ModelKelas->update($id, $dataUpdate);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('sweet_error', 'Gagal memperbarui data: ' . $e->getMessage());
        }

        return redirect()->to(site_url('operator/kelas'))
            ->with('sweet_success', 'Kelas berhasil diperbarui.');
    }
    public function page_detail_kelas($id = null)
    {
        $data_byID = $this->ModelKelas->find($id);
        if (!$data_byID) {
            session()->setFlashdata('sweet_error', 'ID tidak valid.');
            return redirect()->to(base_url('operator/kelas'));
        }
        $data = [
            'title'      => 'Detail Kelas | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Detail Kelas',
            'nav_link' => 'Detail Kelas',
            'data_kelas' => $data_byID
        ];
        return view('pages/operator/detail_kelas', $data);
    }

    // Lapiran Data Siswa
    public function page_laporan_d_siswa()
    {
        // --- Ambil query string untuk filter UI ---
        $q      = trim((string) $this->request->getGet('q'));
        $gender = trim((string) $this->request->getGet('gender'));

        // --- Ambil data siswa + status aktif user lewat JOIN ---
        // Catatan: sesuaikan nama tabel user: 'tb_user' vs 'tb_users' sesuai skema kamu
        $rows = $this->SiswaModel
            ->select('tb_siswa.*, u.username AS user_name, u.is_active AS user_active')
            ->join('tb_users AS u', 'u.id_user = tb_siswa.user_id', 'left') // ganti ke tb_users jika memang pakai 's'
            ->findAll();

        // --- Filter manual sesuai input pencarian ---
        $filtered = array_values(array_filter($rows, function (array $row) use ($q, $gender) {
            $match = true;

            if ($q !== '') {
                $nama    = mb_strtolower((string)($row['full_name'] ?? ''), 'UTF-8');
                $nisn    = mb_strtolower((string)($row['nisn'] ?? ''), 'UTF-8');
                $keyword = mb_strtolower($q, 'UTF-8');
                $match   = (strpos($nama, $keyword) !== false) || (strpos($nisn, $keyword) !== false);
            }

            if ($match && $gender !== '') {
                $g = mb_strtolower((string)($row['gender'] ?? ''), 'UTF-8');
                $match = ($g === mb_strtolower($gender, 'UTF-8'));
            }

            return $match;
        }));

        // --- Hitung aktif/nonaktif dari hasil filter (bisa ganti ke $rows jika mau total keseluruhan) ---
        $SiswaAktif = 0;
        $SiswaNonAktif = 0;
        foreach ($filtered as $r) {
            $flag = (int)($r['user_active'] ?? 0);
            if ($flag === 1) $SiswaAktif++;
            else $SiswaNonAktif++;
        }

        // --- Kirim ke view ---
        $data = [
            'title'         => 'Laporan Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Laporan Data Siswa/i',
            'nav_link'      => 'Laporan Data Siswa',
            'd_siswa'       => $filtered,          // berisi tb_siswa.* + user_name + user_active
            'q'             => $q,
            'gender'        => $gender,
            'SiswaAktif'    => $SiswaAktif,        // jumlah siswa aktif (berdasarkan user.is_active)
            'SiswaNonAktif' => $SiswaNonAktif,     // jumlah siswa nonaktif
            'totalSiswa'    => count($filtered),   // total (setelah filter)
        ];

        return view('pages/operator/laporan_data_siswa', $data);
    }
}
