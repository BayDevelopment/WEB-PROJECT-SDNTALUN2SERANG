<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelGuru;
use App\Models\SiswaModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class OperatorController extends BaseController
{
    protected $UserModel;
    protected $SiswaModel;
    protected $ModelGuru;
    public function __construct()
    {
        $this->UserModel = new UserModel();
        $this->SiswaModel = new SiswaModel();
        $this->ModelGuru = new ModelGuru();
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
        // ambil semua siswa (boleh dari model, atau bisa array statis)
        $allSiswa = $this->SiswaModel->findAll();

        // ambil query string
        $q      = trim((string) $this->request->getGet('q'));
        $gender = trim((string) $this->request->getGet('gender'));

        // filter manual (tanpa query builder DB)
        $filtered = array_filter($allSiswa, function ($row) use ($q, $gender) {
            $match = true;

            if ($q !== '') {
                $nama = strtolower($row['full_name'] ?? '');
                $nisn = strtolower($row['nisn'] ?? '');
                $keyword = strtolower($q);
                $match = (strpos($nama, $keyword) !== false || strpos($nisn, $keyword) !== false);
            }

            if ($match && $gender !== '') {
                // samakan format gender di array dan filter
                $g = strtolower($row['gender'] ?? '');
                $match = ($g === strtolower($gender));
            }

            return $match;
        });

        $data = [
            'title'     => 'Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Siswa/i',
            'nav_link'  => 'Data Siswa',
            'd_siswa'   => $filtered,
            'q'         => $q,
            'gender'    => $gender,
        ];

        return view('pages/operator/data_siswa', $data);
    }

    public function page_tambah_siswa()
    {
        $belumIsi = $this->UserModel
            ->select('tb_users.*')
            ->join('tb_siswa s', 's.id_siswa = tb_users.id_user', 'left')
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
        $allGuru = $this->ModelGuru->findAll();
        $data = [
            'title'     => 'Data guru | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Guru',
            'nav_link'  => 'Data Guru',
            'd_guru' => $allGuru
        ];
        return view('pages/operator/data_guru', $data);
    }
    public function page_tambah_guru()
    {
        $belumIsi = $this->UserModel
            ->select('tb_users.*')
            ->join('tb_siswa s', 's.id_siswa = tb_users.id_user', 'left')
            ->where('tb_users.role', 'guru')
            ->where('tb_users.is_active', 1)
            ->where('s.id_siswa', null)   // builder akan jadi "IS NULL"
            ->orderBy('tb_users.username', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Tambah guru | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Tambah Guru/i',
            'nav_link' => 'Tambah Guru',
            'd_user' => $belumIsi,
            'validation' => \Config\Services::validation(),
        ];
        return view('pages/operator/tambah_guru', $data);
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
}
