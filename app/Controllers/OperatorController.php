<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruMatpel;
use App\Models\GuruTahunanModel;
use App\Models\KategoriNilai;
use App\Models\KelasModel;
use App\Models\ModelGuru;
use App\Models\ModelMatPel;
use App\Models\NilaiSiswaModel;
use App\Models\SiswaModel;
use App\Models\SiswaTahunanModel;
use App\Models\TahunAjaranModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
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
    protected $SiswaTahunanModel;
    protected $GuruTahunanModel;
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
        $this->SiswaTahunanModel = new SiswaTahunanModel();
        $this->GuruTahunanModel = new GuruTahunanModel();
    }
    public function index()
    {
        $data = [
            'title'    => 'Operator | SDN Talun 2 Kota Serang',
            'nav_link' => 'Dashboard',
        ];

        $db = \Config\Database::connect();

        // ===== Tahun Ajaran aktif / fallback terbaru
        $taAktif = $this->TahunAjaran
            ->where('is_active', 1)
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->first();
        if (! $taAktif) {
            $taAktif = $this->TahunAjaran
                ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
                ->first();
        }
        $taId = (int)($taAktif['id_tahun_ajaran'] ?? 0);
        $data['ta_aktif'] = $taAktif;

        // ===== Guru Aktif (distinct)
        $aktifSet = ['1', 'aktif', 'active', 'ya', 'true'];
        if ($taId > 0) {
            $guruCount = (int) (clone $this->GuruTahunanModel)
                ->select('COUNT(DISTINCT guru_id) AS jml', false)
                ->where('tahun_ajaran_id', $taId)
                ->groupStart()->whereIn('status', $aktifSet)->orWhere('status', 1)->groupEnd()
                ->get()->getRow('jml');
        } else {
            $guruCount = (int) (clone $this->ModelGuru)
                ->select('COUNT(DISTINCT id_guru) AS jml', false)
                ->groupStart()->whereIn('status', $aktifSet)->orWhere('status', 1)->groupEnd()
                ->get()->getRow('jml');
        }
        $data['guruCount'] = $guruCount;

        // ===== Siswa aktif per kelas
        $rowsPerKelas = $db->table('tb_siswa s')
            ->select('s.kelas_id, k.tingkat, k.nama_kelas, COUNT(*) AS jml')
            ->join('tb_users u', 'u.id_user = s.user_id', 'inner')
            ->join('tb_kelas k', 'k.id_kelas = s.kelas_id', 'left')
            ->where('u.role', 'siswa')
            ->where('u.is_active', 1)
            ->where('s.kelas_id IS NOT NULL', null, false)
            ->groupBy('s.kelas_id, k.tingkat, k.nama_kelas')
            ->get()->getResultArray();

        $byClass    = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        $siswaTotal = 0;

        $kelasTerpadatId      = null;
        $kelasTerpadatJumlah  = 0;
        $kelasTerpadatNama    = null;
        $kelasTerpadatTingkat = null;

        foreach ($rowsPerKelas as $r) {
            $j = (int)$r['jml'];
            $siswaTotal += $j;

            $tingkat = isset($r['tingkat']) && $r['tingkat'] !== null
                ? (int)$r['tingkat']
                : (preg_match('/\d+/', (string)($r['nama_kelas'] ?? ''), $m) ? (int)$m[0] : null);

            if ($tingkat !== null && $tingkat >= 1 && $tingkat <= 6) {
                $byClass[$tingkat] += $j;
            }

            if ($j > $kelasTerpadatJumlah) {
                $kelasTerpadatJumlah  = $j;
                $kelasTerpadatId      = (int)$r['kelas_id'];
                $kelasTerpadatTingkat = $tingkat;
                $kelasTerpadatNama    = $r['nama_kelas'] ?? null;
            }
        }

        $data['byClass']             = $byClass;
        $data['siswaTotal']          = (int)$siswaTotal;
        $data['kelasTerpadat']       = ($kelasTerpadatTingkat !== null) ? (string)$kelasTerpadatTingkat : $kelasTerpadatNama;
        $data['kelasTerpadatJumlah'] = (int)$kelasTerpadatJumlah;
        $data['kelasTerpadatNama']   = $kelasTerpadatNama;

        // ===== Distribusi Mapel (contoh sederhana)
        $rowsMapel = $db->table('tb_mapel')
            ->select('nama, COUNT(*) AS jml', false)
            ->groupBy('nama')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        $mapelLabels = [];
        $mapelCounts = [];
        foreach ($rowsMapel as $rm) {
            $label = trim((string)($rm['nama'] ?? ''));
            $count = (int)($rm['jml'] ?? 0);
            if ($label !== '') {
                $mapelLabels[] = $label;
                $mapelCounts[] = $count;
            }
        }
        $data['mapelLabels'] = $mapelLabels;
        $data['mapelCounts'] = $mapelCounts;

        // ===== Nilai Tertinggi (TA aktif)
        $topNilai = 0;
        $topNama  = '—';
        $topKelas = null;

        if ($taId > 0) {
            try {
                $rowTop = $db->table('tb_nilai_siswa ns')
                    ->select('ns.skor, ns.siswa_id, s.full_name as siswa_nama, ns.tanggal')
                    ->join('tb_siswa s', 's.id_siswa = ns.siswa_id', 'left')
                    ->where('ns.tahun_ajaran_id', $taId)
                    ->where('ns.skor IS NOT NULL', null, false)
                    ->orderBy('ns.skor', 'DESC')
                    ->orderBy('ns.tanggal', 'DESC')
                    ->limit(1)
                    ->get()->getRowArray();

                if ($rowTop) {
                    $topNilai = (float)($rowTop['skor'] ?? 0);
                    $topNama  = (string)($rowTop['siswa_nama'] ?? '—');

                    $kidTop = $db->table('tb_siswa')->select('kelas_id')->where('id_siswa', (int)$rowTop['siswa_id'])->get()->getRow('kelas_id');
                    if ($kidTop) {
                        $metaTop = $db->table('tb_kelas')->select('tingkat, nama_kelas')->where('id_kelas', (int)$kidTop)->get()->getRowArray();
                        if ($metaTop) {
                            $topKelas = !empty($metaTop['tingkat']) ? (string)$metaTop['tingkat'] : (string)($metaTop['nama_kelas'] ?? null);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // biarkan default
            }
        }

        $data['topNilai'] = $topNilai;
        $data['topNama']  = $topNama;
        $data['topKelas'] = $topKelas;

        return view('pages/operator/dashboard_operator', $data);
    }


    public function Data_siswa()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $gender = trim((string) $this->request->getGet('gender'));

        $rows = $this->SiswaModel
            ->select("
            tb_siswa.*,
            u.username AS user_name,
            u.is_active AS user_active,
            (
                SELECT COUNT(1)
                FROM tb_siswa_tahun st
                WHERE st.siswa_id = tb_siswa.id_siswa
            ) AS laporan_count
        ")
            ->join('tb_users AS u', 'u.id_user = tb_siswa.user_id', 'left')
            ->findAll();

        // Filter manual sesuai UI
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

        // Hitung aktif/nonaktif dari hasil filter
        $SiswaAktif = 0;
        $SiswaNonAktif = 0;
        foreach ($filtered as $r) {
            $flag = (int)($r['user_active'] ?? 0);
            if ($flag === 1) $SiswaAktif++;
            else $SiswaNonAktif++;
        }

        $data = [
            'title'         => 'Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Data Siswa/i',
            'nav_link'      => 'Data Siswa',
            'd_siswa'       => $filtered,       // now includes laporan_count
            'q'             => $q,
            'gender'        => $gender,
            'SiswaAktif'    => $SiswaAktif,
            'SiswaNonAktif' => $SiswaNonAktif,
            'totalSiswa'    => count($filtered),
        ];

        return view('pages/operator/data_siswa', $data);
    }



    public function page_tambah_siswa()
    {
        // Akun "siswa" yang belum punya entri di tb_siswa
        $belumIsi = $this->UserModel
            ->select('u.id_user, u.username, u.email, u.role, u.is_active')
            ->from('tb_users u', true) // overwrite FROM
            ->join('tb_siswa s', 's.user_id = u.id_user', 'left')
            ->where('u.role', 'siswa')
            ->where('u.is_active', 1)
            ->where('s.id_siswa', null)
            ->groupBy('u.id_user, u.username, u.email, u.role, u.is_active')
            ->orderBy('u.username', 'ASC')
            ->findAll();

        // ===== Ambil daftar kelas dari KelasModel =====
        // Sesuaikan kolom nama kelas sesuai skema kamu:
        //   - jika kolomnya "nama_kelas", pakai select di bawah
        //   - jika kolomnya "nama", ganti jadi: ->select('id_kelas, nama AS nama_kelas')
        $optKelas = $this->ModelKelas
            ->select('id_kelas, nama_kelas')   // <-- ganti ke 'id_kelas, nama AS nama_kelas' bila perlu
            ->orderBy('nama_kelas', 'ASC')
            ->findAll(200);

        return view('pages/operator/tambah_siswa', [
            'title'         => 'Tambah siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Tambah Siswa/i',
            'nav_link'      => 'Tambah Siswa',
            'd_user'        => $belumIsi,
            'eligibleCount' => count($belumIsi),
            'optKelas'      => $optKelas,       // <-- kirim ke view
            'validation'    => \Config\Services::validation(),
        ]);
    }



    // === ACTION: Insert data siswa ===
    public function aksi_insert_siswa()
    {
        $req = $this->request;

        // ---------- RULES ----------
        $rules = [
            'user_id'     => ['rules' => 'required|is_natural_no_zero', 'errors' => [
                'required'           => 'User wajib dipilih.',
                'is_natural_no_zero' => 'User tidak valid.'
            ]],
            // KELAS WAJIB
            'kelas_id'    => ['rules' => 'required|is_natural_no_zero|is_not_unique[tb_kelas.id_kelas]', 'errors' => [
                'required'           => 'Kelas wajib dipilih.',
                'is_natural_no_zero' => 'Kelas tidak valid.',
                'is_not_unique'      => 'Kelas tidak ditemukan.'
            ]],
            'nisn'        => ['rules' => 'required|min_length[8]|max_length[16]|is_unique[tb_siswa.nisn]', 'errors' => [
                'required'   => 'NISN wajib diisi.',
                'min_length' => 'NISN minimal 8 digit.',
                'max_length' => 'NISN maksimal 16 digit.',
                'is_unique'  => 'NISN sudah terdaftar.'
            ]],
            'full_name'   => ['rules' => 'required|min_length[3]', 'errors' => [
                'required'   => 'Nama lengkap wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.'
            ]],
            'gender'      => ['rules' => 'required|in_list[L,P]', 'errors' => [
                'required' => 'Jenis kelamin wajib dipilih.',
                'in_list'  => 'Pilih L atau P.'
            ]],
            'birth_place' => ['rules' => 'required', 'errors' => ['required' => 'Tempat lahir wajib diisi.']],
            'birth_date'  => ['rules' => 'required|valid_date[Y-m-d]', 'errors' => [
                'required'   => 'Tanggal lahir wajib diisi.',
                'valid_date' => 'Format tanggal harus YYYY-MM-DD.'
            ]],
            'address'     => ['rules' => 'permit_empty', 'errors' => []],
            'parent_name' => ['rules' => 'required|min_length[3]', 'errors' => [
                'required'   => 'Nama orang tua wajib diisi.',
                'min_length' => 'Minimal 3 karakter.'
            ]],
            'phone'       => ['rules' => 'required|numeric|min_length[8]|max_length[20]', 'errors' => [
                'required'   => 'Nomor HP wajib diisi.',
                'numeric'    => 'Nomor HP harus angka.',
                'min_length' => 'Nomor HP minimal 8 digit.',
                'max_length' => 'Nomor HP maksimal 20 digit.'
            ]],
            'photo'       => [
                'rules' => 'permit_empty|is_image[photo]|max_size[photo,2048]|ext_in[photo,jpg,jpeg,png]|mime_in[photo,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'is_image' => 'File harus berupa gambar.',
                    'max_size' => 'Maksimal 2MB.',
                    'ext_in'   => 'Ekstensi wajib: jpg/jpeg/png.',
                    'mime_in'  => 'MIME harus image/jpg, image/jpeg, atau image/png.'
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ---------- AMBIL INPUT ----------
        $userId     = (int) $req->getPost('user_id');
        $kelasId    = (int) $req->getPost('kelas_id');          // <-- ambil kelas_id
        $nisn       = trim((string) $req->getPost('nisn'));
        $fullName   = trim((string) $req->getPost('full_name'));
        $gender     = (string) $req->getPost('gender');          // L / P
        $birthPlace = trim((string) $req->getPost('birth_place'));
        $birthDate  = (string) $req->getPost('birth_date');
        $address    = trim((string) $req->getPost('address'));
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

        // ---------- CEK user_id SUDAH DIPAKAI DI tb_siswa? ----------
        $sudahAda = $this->SiswaModel->where('user_id', $userId)->first();
        if ($sudahAda) {
            session()->setFlashdata('sweet_error', 'User ini sudah memiliki data siswa.');
            return redirect()->back()->withInput();
        }

        // ---------- CEK nisn SUDAH ADA? (tambahan guard) ----------
        $nisnAda = $this->SiswaModel->where('nisn', $nisn)->first();
        if ($nisnAda) {
            session()->setFlashdata('sweet_error', 'NISN sudah terdaftar.');
            return redirect()->back()->withInput();
        }

        // (Opsional) Guard tambahan untuk kelas_id (di luar rules)
        // if (! $this->ModelKelas->where('id_kelas', $kelasId)->first()) {
        //     session()->setFlashdata('sweet_error', 'Kelas tidak ditemukan.');
        //     return redirect()->back()->withInput();
        // }

        // ---------- HANDLE FOTO ----------
        $uploadDir   = FCPATH . 'assets/img/uploads';
        $defaultSrc  = FCPATH . 'assets/img/user.png';
        $defaultName = 'user.png';

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photoFile = $req->getFile('photo');
        $photoName = null;

        if ($photoFile && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $ext       = strtolower($photoFile->getExtension() ?: 'jpg');
            $photoName = 'siswa_' . $userId . '_' . time() . '.' . $ext;
            try {
                $photoFile->move($uploadDir, $photoName);
            } catch (\Throwable $e) {
                session()->setFlashdata('sweet_error', 'Gagal menyimpan foto: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        } else {
            $targetDefault = $uploadDir . DIRECTORY_SEPARATOR . $defaultName;
            if (! file_exists($targetDefault)) {
                @copy($defaultSrc, $targetDefault);
            }
            $photoName = $defaultName;
        }

        // ---------- INSERT ----------
        $dataInsert = [
            'user_id'     => $userId,
            'kelas_id'    => $kelasId,     // <-- simpan kelas_id
            'nisn'        => $nisn,
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
            $this->SiswaModel->insert($dataInsert);
        } catch (\Throwable $e) {
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
        $nisn = trim($nisn);

        // Ambil siswa + user + kelas; alias-kan kelas_id → id_kelas biar konsisten di view & aksi_update
        $siswa = $this->SiswaModel
            ->select([
                'tb_siswa.*',
                'tb_siswa.kelas_id AS id_kelas',        // <-- alias penting
                'tb_users.username AS user_name',
                'tb_kelas.nama_kelas AS kelas_name'
            ])
            ->join('tb_users', 'tb_users.id_user = tb_siswa.user_id', 'left')
            ->join('tb_kelas', 'tb_kelas.id_kelas = tb_siswa.kelas_id', 'left')
            ->where('tb_siswa.nisn', $nisn)
            ->first();

        if (! $siswa) {
            return redirect()->to(base_url('operator/data-siswa'))
                ->with('sweet_error', 'Data siswa tidak ditemukan.');
        }

        // Semua kelas untuk dropdown
        $kelasList = $this->ModelKelas   // pastikan nama model sesuai: ModelKelas atau KelasModel
            ->select('id_kelas, nama_kelas')
            ->orderBy('id_kelas', 'ASC') // atau 'nama_kelas' kalau penamaan bukan angka
            ->findAll();

        // (Opsional defensif) jika kelas siswa tidak ada di list (data lama), tambahkan sementara
        if (!empty($siswa['id_kelas']) && $siswa['id_kelas'] > 0) {
            $inList = array_search((int)$siswa['id_kelas'], array_column($kelasList, 'id_kelas'));
            if ($inList === false && !empty($siswa['kelas_name'])) {
                $kelasList[] = [
                    'id_kelas'   => (int)$siswa['id_kelas'],
                    'nama_kelas' => (string)$siswa['kelas_name'],
                ];
                // urutkan lagi
                usort($kelasList, fn($a, $b) => $a['id_kelas'] <=> $b['id_kelas']);
            }
        }

        $data = [
            'title'      => 'Edit siswa | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit Siswa/i',
            'nav_link'   => 'Edit Siswa',
            'siswa'      => $siswa,       // sudah punya 'id_kelas' (alias) & 'kelas_name'
            'kelasList'  => $kelasList,   // semua kelas untuk dropdown
            'validation' => \Config\Services::validation(),
        ];

        return view('pages/operator/edit_siswa', $data);
    }


    public function aksi_update_siswa(string $nisnParam)
    {
        $req = $this->request;

        $existing = $this->SiswaModel->where('nisn', $nisnParam)->first();
        if (! $existing) {
            session()->setFlashdata('sweet_error', 'Data siswa tidak ditemukan.');
            return redirect()->to(base_url('operator/data-siswa'));
        }

        $idSiswa = (int) ($existing['id_siswa'] ?? 0);
        $userId  = (int) ($existing['user_id'] ?? 0);

        $rules = [
            'nisn'        => "required|min_length[8]|max_length[16]|is_unique[tb_siswa.nisn,id_siswa,{$idSiswa}]",
            'full_name'   => 'required|min_length[3]',
            'gender'      => 'required|in_list[L,P]',
            'birth_place' => 'required',
            'birth_date'  => 'required|valid_date[Y-m-d]',
            'address'     => 'permit_empty',
            'parent_name' => 'required|min_length[3]',
            'phone'       => 'required|numeric|min_length[8]|max_length[20]',
            'photo'       => 'permit_empty|is_image[photo]|max_size[photo,2048]|ext_in[photo,jpg,jpeg,png]|mime_in[photo,image/jpg,image/jpeg,image/png]',
            'id_kelas'    => 'required|is_natural_no_zero|is_not_unique[tb_kelas.id_kelas]', // validasi input
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nisnNew    = trim((string) $req->getPost('nisn'));
        $fullName   = trim((string) $req->getPost('full_name'));
        $gender     = (string) $req->getPost('gender');
        $birthPlace = trim((string) $req->getPost('birth_place'));
        $birthDate  = (string) $req->getPost('birth_date');
        $address    = trim((string) $req->getPost('address'));
        $parentName = trim((string) $req->getPost('parent_name'));
        $phone      = trim((string) $req->getPost('phone'));
        $photoOld   = trim((string) $req->getPost('photo_old'));
        $kelasId    = (int) $req->getPost('id_kelas');  // dari form

        // Validasi user existing...
        $user = $this->UserModel->select('id_user, role, is_active')->where('id_user', $userId)->first();
        if (! $user || $user['role'] !== 'siswa' || (int) $user['is_active'] !== 1) {
            session()->setFlashdata('sweet_error', 'User tidak valid / tidak aktif / bukan role siswa.');
            return redirect()->back()->withInput();
        }

        // Handle foto...
        $uploadDir   = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'uploads';
        $defaultName = 'user.png';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $photoFile = $req->getFile('photo');
        $photoName = $photoOld ?: $defaultName;

        if ($photoFile && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $ext       = strtolower($photoFile->getExtension() ?: 'jpg');
            $photoName = 'siswa_' . $userId . '_' . time() . '.' . $ext;
            try {
                $photoFile->move($uploadDir, $photoName);
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

        // Simpan ke kolom 'kelas_id'
        $dataUpdate = [
            'user_id'     => $userId,
            'nisn'        => $nisnNew,
            'full_name'   => $fullName,
            'gender'      => $gender,
            'birth_place' => $birthPlace,
            'birth_date'  => $birthDate,
            'address'     => $address,
            'parent_name' => $parentName,
            'phone'       => $phone,
            'photo'       => $photoName,
            'kelas_id'    => $kelasId, // <-- konsisten dengan relasi di page_edit_siswa
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

        // Ambil siswa by NISN
        $detailSiswa = $this->SiswaModel
            ->select("
            tb_siswa.*,
            tb_users.username AS user_name,
            tb_users.username AS user_username,
            tb_siswa.kelas_id       AS kelas_id_final,
            k.nama_kelas            AS nama_kelas
        ")
            ->join('tb_users', 'tb_users.id_user = tb_siswa.user_id', 'left')
            // JOIN langsung pakai kelas_id (karena memang kolom itu yang ada)
            ->join('tb_kelas k', 'k.id_kelas = tb_siswa.kelas_id', 'left')
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

        // --- Ambil input mentah (untuk validasi) ---
        $usernameIn = (string) $this->request->getPost('username');
        $emailIn    = (string) $this->request->getPost('email');

        // --- RULES: username tanpa spasi, hanya A-Z a-z 0-9 . _ (sejalan dgn contohmu) ---
        $rules = [
            'username' => [
                'label'  => 'Username',
                // no space → regex: hanya huruf/angka/titik/underscore
                'rules'  => "required|min_length[4]|max_length[24]|regex_match[/^[A-Za-z0-9._]+$/]"
                    . "|is_unique[tb_users.username,id_user,{$uid}]",
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'min_length'  => '{field} minimal {param} karakter.',
                    'max_length'  => '{field} maksimal {param} karakter.',
                    'regex_match' => '{field} hanya boleh huruf, angka, titik, atau underscore (tanpa spasi).',
                    'is_unique'   => '{field} sudah digunakan.',
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
                ->with('active_tab', 'account');
        }

        // --- Payload dasar (tanpa mengubah role/status untuk non-operator) ---
        $payload = [
            'id_user'    => $uid, // PK untuk Model::save()
            // catatan: tidak auto-replace spasi→underscore agar user sadar aturan; sudah ditangkap regex di atas
            'username'   => trim($usernameIn),
            'email'      => strtolower(trim($emailIn)),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // hanya operator yang boleh ubah role & status
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

            // opsional: sinkronkan session bila field yang tampil di header berubah
            $session->set('username', $payload['username']);
            $session->set('email', $payload['email']);
        } catch (\Throwable $e) {
            log_message('error', 'Profile update failed: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('sweet_error', 'Gagal menyimpan perubahan.');
        }

        // arahkan sesuai kebutuhanmu
        return redirect()->to(base_url('operator/profile'))
            ->with('sweet_success', 'Profil berhasil diperbarui.')
            ->with('active_tab', 'account');
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
        $req    = $this->request;
        $q      = trim((string) ($req->getGet('q') ?? ''));
        $gender = strtoupper(trim((string) ($req->getGet('gender') ?? ''))); // L / P / ''
        $idTA   = (int) ($req->getGet('tahunajaran') ?? 0);

        $guruModel = new \App\Models\ModelGuru();        // table: tb_guru
        $gtModel   = new \App\Models\GuruTahunanModel(); // table: tb_guru_tahun (asumsi)
        $taModel   = new \App\Models\TahunAjaranModel(); // table: tb_tahun_ajaran
        $gmModel   = new \App\Models\GuruMatpel();       // table: tb_guru_mapel / tbl_guru_mapel

        // 1) Tentukan TA target (parameter ?tahunajaran=... atau fallback yang is_active=1)
        $taTargetId = 0;
        if ($idTA > 0) {
            $ta = $taModel->select('id_tahun_ajaran')->where('id_tahun_ajaran', $idTA)->first();
            $taTargetId = $ta ? (int) $ta['id_tahun_ajaran'] : 0;
        } else {
            $ta = $taModel->select('id_tahun_ajaran')->where('is_active', 1)->first();
            $taTargetId = $ta ? (int) $ta['id_tahun_ajaran'] : 0;
        }

        // 2) Ambil list guru + filter q/gender + sertakan jabatan
        $gq = $guruModel
            ->select('id_guru, nip, nama_lengkap, jenis_kelamin, jabatan, status_active')
            ->orderBy('nama_lengkap', 'ASC');

        if ($gender === 'L' || $gender === 'P') {
            $gq->where('jenis_kelamin', $gender);
        }

        if ($q !== '') {
            // cari di NIP / Nama (gunakan LIKE)
            $gq->groupStart()
                ->like('nip', $q)
                ->orLike('nama_lengkap', $q)
                ->groupEnd();
        }

        $rows = $gq->findAll();

        // 3) Flag "sudah ada entri guru_tahun" untuk TA target (opsional kalau ada tabelnya)
        $exists = [];
        if ($taTargetId > 0) {
            $existsRows = $gtModel
                ->select('guru_id')
                ->where('tahun_ajaran_id', $taTargetId)
                ->groupBy('guru_id')
                ->findAll();
            foreach ($existsRows as $er) {
                $exists[(int) $er['guru_id']] = true;
            }
        }

        // 4) Flag "sudah punya mapel" — PER TA (pakai kolom yang benar!)
        //   Catatan: di thread sebelumnya kolomnya adalah `id_tahun_ajaran`, bukan `tahun_ajaran_id`.
        $hasMapel = [];
        $mapelB = $gmModel->builder()
            ->select('id_guru')
            ->groupBy('id_guru');

        // deteksi nama kolom TA pada tabel penugasan
        $taCol = 'id_tahun_ajaran';
        // kalau skema kamu lain (mis. tahun_ajaran_id), silakan override manual:
        // $taCol = 'tahun_ajaran_id';

        if ($taTargetId > 0) {
            $mapelB->where($taCol, $taTargetId);
        }

        $mapelRows = $mapelB->get()->getResultArray();
        foreach ($mapelRows as $mr) {
            $hasMapel[(int) $mr['id_guru']] = true;
        }

        // 5) Sisipkan flag ke setiap guru (dipakai view)
        $d_guru = array_map(function (array $g) use ($exists, $hasMapel) {
            $gid = (int) $g['id_guru'];
            $g['has_laporan_ta'] = !empty($exists[$gid]);   // sudah ada entri di TA target
            $g['has_mapel']      = !empty($hasMapel[$gid]); // sudah punya mapel di TA target
            return $g;
        }, $rows);

        return view('pages/operator/data_guru', [
            'title'        => 'Data Guru | SDN Talun 2 Kota Serang',
            'sub_judul'    => 'Data Guru',
            'nav_link'     => 'Data Guru',
            'd_guru'       => $d_guru,
            'q'            => $q,
            'gender'       => $gender,
            'tahunajaran'  => $taTargetId,
            'totalGuru'    => count($d_guru),
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

            // + jabatan (opsional sesuai ENUM di migration Anda)
            'jabatan' => [
                'rules'  => 'permit_empty|in_list[Kepala Sekolah,Wakil Kepala,Guru,Wali Kelas,Operator,Staff]',
                'errors' => [
                    'in_list' => 'Jabatan tidak valid.'
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

        // + ambil jabatan dari POST (opsional)
        $jabatanPost  = trim((string) $req->getPost('jabatan'));
        // whitelist kecil (opsional, karena sudah ada validasi in_list)
        $whitelistJabatan = ['Kepala Sekolah', 'Wakil Kepala', 'Guru', 'Wali Kelas', 'Operator', 'Staff'];
        $jabatan = in_array($jabatanPost, $whitelistJabatan, true) ? $jabatanPost : null;

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

        // ---------- CEK nip SUDAH ADA? ----------
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
            'jabatan'       => $jabatan, // + jabatan
        ];

        try {
            $this->ModelGuru->insert($dataInsert);
        } catch (\Throwable $e) {
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
        $guru = $this->ModelGuru
            ->select('tb_guru.*, tb_users.username AS user_name, tb_users.id_user AS fresh_user_id')
            ->join('tb_users', 'tb_users.id_user = tb_guru.user_id', 'left')
            ->where('tb_guru.nip', $nip)
            ->first();

        if (!$guru) {
            session()->setFlashdata('sweet_error', 'Data guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        // Ambil hanya 1 user “terbaru” MILIK guru ini (berdasarkan relasi user_id yang sama)
        // Artinya dropdown akan menampilkan 1 opsi: user yang saat ini ter-relasi + paling baru updated.
        $optUsers = $this->UserModel
            ->select('id_user, username, email, updated_at')
            ->where('id_user', (int)($guru['user_id'] ?? 0))
            ->orderBy('updated_at', 'DESC')
            ->limit(1)
            ->findAll();

        return view('pages/operator/edit_guru', [
            'title'      => 'Edit Guru | SDN Talun 2 Kota Serang',
            'sub_judul'  => 'Edit Guru',
            'nav_link'   => 'Edit Guru',
            'd_guru'     => $guru,
            'optUsers'   => $optUsers, // ← dropdown source
            'validation' => \Config\Services::validation(),
        ]);
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

            // ==== TAMBAHAN: JABATAN (opsional, sesuai ENUM) ====
            'jabatan' => [
                'rules'  => 'permit_empty|in_list[Kepala Sekolah,Wakil Kepala,Guru,Wali Kelas,Operator,Staff]',
                'errors' => [
                    'in_list' => 'Jabatan tidak valid.',
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

        // Ambil & bersihkan jabatan dari POST (opsional)
        $jabatanPost  = trim((string)$this->request->getPost('jabatan'));
        $whitelistJabatan = ['Kepala Sekolah', 'Wakil Kepala', 'Guru', 'Wali Kelas', 'Operator', 'Staff'];
        $jabatan      = $jabatanPost !== '' && in_array($jabatanPost, $whitelistJabatan, true) ? $jabatanPost : null;

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
            'jabatan'       => $jabatan,     // <<< TAMBAHKAN INI
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

    // Controller: page_detail_guru (versi pakai ModelMatpel saja)
    public function page_detail_guru(string $nipRaw)
    {
        $nip = urldecode(trim($nipRaw));

        // --- Profil guru
        $guru = $this->ModelGuru
            ->select('g.*, g.jabatan AS jabatan, u.username AS user_name')
            ->from('tb_guru AS g')
            ->join('tb_users AS u', 'u.id_user = g.user_id', 'left')
            ->where('g.nip', $nip)
            ->first();

        if (!$guru) {
            session()->setFlashdata('sweet_error', 'Data guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }
        $idGuru = (int)$guru['id_guru'];

        // --- Tabel penugasan (listing)
        $builder = $this->ModelGuruMatpel
            ->select('
            tb_guru_mapel.id_guru,
            tb_guru_mapel.id_mapel,
            tb_guru_mapel.id_tahun_ajaran,
            tb_guru_mapel.id_kelas,
            tb_guru_mapel.jam_per_minggu,
            tb_guru_mapel.keterangan,
            m.nama AS mapel,
            k.nama_kelas AS kelas,
            ta.start_date, ta.end_date, ta.semester
        ')
            ->join('tb_mapel m', 'm.id_mapel = tb_guru_mapel.id_mapel', 'inner')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = tb_guru_mapel.id_tahun_ajaran', 'inner')
            ->join('tb_kelas k', 'k.id_kelas = tb_guru_mapel.id_kelas', 'inner')
            ->where('tb_guru_mapel.id_guru', $idGuru);

        // (Opsional) jika pakai soft deletes pada tb_guru_mapel
        if (property_exists($this->ModelGuruMatpel, 'useSoftDeletes') && $this->ModelGuruMatpel->useSoftDeletes) {
            $builder->where('tb_guru_mapel.deleted_at', null);
        }

        $raw = $builder
            ->orderBy('ta.start_date', 'DESC')
            ->orderBy('m.nama', 'ASC')
            ->orderBy('k.nama_kelas', 'ASC')
            ->findAll();

        // --- GROUP: gabungkan kelas per (mapel, tahun_ajaran)
        $grouped = []; // key: "$idMapel|$idTa"
        foreach ($raw as $r) {
            $idMap = (int)($r['id_mapel'] ?? 0);
            $idTa  = (int)($r['id_tahun_ajaran'] ?? 0);
            $key   = $idMap . '|' . $idTa;

            if (!isset($grouped[$key])) {
                $y1 = !empty($r['start_date']) ? (new \DateTime($r['start_date']))->format('Y') : '';
                $y2 = !empty($r['end_date'])   ? (new \DateTime($r['end_date']))->format('Y')   : '';
                $sem = trim((string)($r['semester'] ?? ''));
                $tahunAjar = $y1 && $y2 ? ($y1 . '/' . $y2 . ($sem !== '' ? ' - ' . $sem : '')) : ($sem ?: '');

                $grouped[$key] = [
                    'id_mapel'        => $idMap,
                    'mapel'           => (string)($r['mapel'] ?? ''),
                    'id_tahun_ajaran' => $idTa,
                    'tahun_ajaran'    => $tahunAjar,
                    // asumsikan jam/ket seragam untuk semua kelas dalam kombinasi
                    'jam_per_minggu'  => (int)($r['jam_per_minggu'] ?? 0),
                    'keterangan'      => (string)($r['keterangan'] ?? ''),
                    'kelas_list'      => [],
                    'kelas_ids'       => [],
                ];
            }

            $grouped[$key]['kelas_list'][] = (string)($r['kelas'] ?? '');
            $grouped[$key]['kelas_ids'][]  = (int)($r['id_kelas'] ?? 0);
        }

        // sort & unique kelas di tiap grup (jaga-jaga)
        foreach ($grouped as &$g) {
            $pairs = array_map(fn($id, $nama) => ['id' => $id, 'nama' => $nama], $g['kelas_ids'], $g['kelas_list']);
            usort($pairs, fn($a, $b) => strcmp($a['nama'], $b['nama']));
            $g['kelas_ids']  = array_values(array_unique(array_column($pairs, 'id')));
            $g['kelas_list'] = array_values(array_unique(array_column($pairs, 'nama')));
        }
        unset($g);

        $penugasanGrouped = array_values($grouped);

        // --- Rows per baris (legacy, jika masih dipakai tabel lama)
        $penugasanRows = array_map(function (array $r) {
            $y1 = !empty($r['start_date']) ? (new \DateTime($r['start_date']))->format('Y') : '';
            $y2 = !empty($r['end_date'])   ? (new \DateTime($r['end_date']))->format('Y')   : '';
            $sem = trim((string)($r['semester'] ?? ''));
            $tahunAjar = $y1 && $y2 ? ($y1 . '/' . $y2 . ($sem !== '' ? ' - ' . $sem : '')) : ($sem ?: '');
            return [
                'id_guru'         => (int)($r['id_guru'] ?? 0),
                'id_mapel'        => (int)($r['id_mapel'] ?? 0),
                'id_kelas'        => (int)($r['id_kelas'] ?? 0),
                'id_tahun_ajaran' => (int)($r['id_tahun_ajaran'] ?? 0),
                'mapel'           => (string)($r['mapel'] ?? ''),
                'kelas'           => (string)($r['kelas'] ?? ''),
                'tahun_ajaran'    => $tahunAjar,
                'jam_per_minggu'  => (int)($r['jam_per_minggu'] ?? 0),
                'keterangan'      => (string)($r['keterangan'] ?? ''),
            ];
        }, $raw);

        // --- Opsi dropdown untuk form edit
        $optMapel = $this->ModelMatpel->select('id_mapel, nama')->orderBy('nama', 'ASC')->findAll();
        $optKelas = $this->ModelKelas->select('id_kelas, nama_kelas')->orderBy('nama_kelas', 'ASC')->findAll();
        $optTahunAjaran = $this->TahunAjaran
            ->select('id_tahun_ajaran, start_date, end_date, semester, tahun')
            ->orderBy('start_date', 'DESC')->findAll();

        // --- Tentukan baris yang sedang diedit
        $qMapel = (int)($this->request->getGet('mapel') ?? 0);
        $qKelas = (int)($this->request->getGet('kelas') ?? 0);
        $qTa    = (int)($this->request->getGet('ta') ?? 0);

        $gmEdit = null;
        if ($qMapel && $qKelas && $qTa) {
            $gmEdit = $this->ModelGuruMatpel
                ->where([
                    'id_guru'         => $idGuru,
                    'id_mapel'        => $qMapel,
                    'id_kelas'        => $qKelas,
                    'id_tahun_ajaran' => $qTa,
                ])->first();
        }

        if (!$gmEdit && !empty($penugasanRows)) {
            $first  = $penugasanRows[0];
            $gmEdit = [
                'id_guru'         => $idGuru,
                'id_mapel'        => $first['id_mapel'],
                'id_kelas'        => $first['id_kelas'],
                'id_tahun_ajaran' => $first['id_tahun_ajaran'],
                'jam_per_minggu'  => $first['jam_per_minggu'],
                'keterangan'      => $first['keterangan'],
            ];
        }

        // --- Kumpulkan semua kelas (multi-kelas) utk kombinasi yang aktif di form
        $gmEdit['id_kelas_list'] = [];
        if (!empty($gmEdit['id_mapel']) && !empty($gmEdit['id_tahun_ajaran'])) {
            $kelasList = $this->ModelGuruMatpel
                ->select('id_kelas')
                ->where([
                    'id_guru'         => $idGuru,
                    'id_mapel'        => (int)$gmEdit['id_mapel'],
                    'id_tahun_ajaran' => (int)$gmEdit['id_tahun_ajaran'],
                ])
                // ->where('deleted_at', null) // jika soft delete
                ->orderBy('id_kelas', 'ASC')
                ->findColumn('id_kelas');

            $kelasList = array_values(array_unique(array_map('intval', $kelasList ?? [])));
            if (empty($kelasList) && !empty($gmEdit['id_kelas'])) {
                $kelasList = [(int)$gmEdit['id_kelas']];
            }
            $gmEdit['id_kelas_list'] = $kelasList;
        }

        return view('pages/operator/detail_guru', [
            'title'             => 'Detail Guru | SDN Talun 2 Kota Serang',
            'sub_judul'         => 'Detail Guru',
            'nav_link'          => 'Data Guru',
            'guru'              => $guru,
            'penugasanRows'     => $penugasanRows,     // per baris (lama)
            'penugasanGrouped'  => $penugasanGrouped,  // ← kirim ke view: satu mapel/TA berisi banyak kelas
            'optMapel'          => $optMapel,
            'optKelas'          => $optKelas,
            'optTahunAjaran'    => $optTahunAjaran,
            'gmEdit'            => $gmEdit,            // menyertakan 'id_kelas_list' untuk form multi-kelas
        ]);
    }



    public function aksi_detail_update_guru_mapel(string $nipParam)
    {
        $nip = urldecode(trim($nipParam));

        // 1) Lock guru (+ ambil jabatan)
        $guru = $this->ModelGuru
            ->select('id_guru, jabatan')
            ->where('nip', $nip)
            ->first();

        if (!$guru) {
            return redirect()->to(base_url('operator/data-guru'))
                ->with('sweet_error', 'Guru tidak ditemukan.');
        }

        $idGuru   = (int)$guru['id_guru'];
        $jabatan  = mb_strtolower(trim((string)($guru['jabatan'] ?? '')), 'UTF-8');
        $isWali   = ($jabatan === 'wali kelas');
        $isGuru   = ($jabatan === 'guru'); // silakan sesuaikan jika ada variasi lain

        // 2) Validasi (Indonesia)
        $rules = [
            'id_mapel'            => 'required',               // array/atau single → kita normalkan
            'id_mapel.*'          => 'is_natural_no_zero',
            'id_tahun_ajaran'     => 'required|is_natural_no_zero',
            'id_kelas'            => 'permit_empty',           // opsional
            'id_kelas.*'          => 'is_natural_no_zero',
            'jam_per_minggu'      => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[40]',
            'keterangan'          => 'permit_empty|max_length[190]',
        ];
        $messages = [
            'id_mapel' => [
                'required' => 'Silakan pilih minimal satu mata pelajaran.',
            ],
            'id_mapel.*' => [
                'is_natural_no_zero' => 'Mata pelajaran tidak valid.',
            ],
            'id_tahun_ajaran' => [
                'required' => 'Tahun ajaran wajib dipilih.',
                'is_natural_no_zero' => 'Tahun ajaran tidak valid.',
            ],
            'id_kelas.*' => [
                'is_natural_no_zero' => 'Data kelas tidak valid.',
            ],
            'jam_per_minggu' => [
                'required' => 'Jam per minggu wajib diisi.',
                'integer'  => 'Jam per minggu harus berupa bilangan bulat.',
                'greater_than_equal_to' => 'Jam per minggu minimal 0.',
                'less_than_equal_to'    => 'Jam per minggu maksimal 40.',
            ],
            'keterangan' => [
                'max_length' => 'Keterangan maksimal 190 karakter.',
            ],
        ];
        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('sweet_error', 'Validasi gagal. Periksa kembali isian Anda.');
        }

        // 3) Ambil input
        $idTa   = (int)$this->request->getPost('id_tahun_ajaran');
        $jam    = (int)$this->request->getPost('jam_per_minggu');
        $ketRaw = trim((string)$this->request->getPost('keterangan'));
        $ket    = ($ketRaw !== '') ? $ketRaw : null;

        // Normalisasi mapel jadi array unik-int
        $mapelPost = $this->request->getPost('id_mapel');
        if (!is_array($mapelPost)) $mapelPost = [$mapelPost];
        $mapelIds = array_values(array_unique(array_filter(array_map(static function ($v) {
            $n = (int)$v;
            return $n > 0 ? $n : null;
        }, $mapelPost))));

        if (empty($mapelIds)) {
            return redirect()->back()->withInput()
                ->with('sweet_error', 'Silakan pilih minimal satu mata pelajaran.');
        }

        // Validasi mapel ada di master
        $validMapelIds = $this->ModelMatpel->whereIn('id_mapel', $mapelIds)->findColumn('id_mapel');
        $validMapelIds = array_map('intval', $validMapelIds ?? []);
        if (count($validMapelIds) !== count($mapelIds)) {
            return redirect()->back()->withInput()
                ->with('sweet_error', 'Terdapat mata pelajaran yang tidak valid.');
        }

        // Kelas (opsional): jika dikirim, sinkronkan; jika tidak, jangan ubah kelas
        $kelasPost = $this->request->getPost('id_kelas');
        $kelasArr  = null;
        if (!is_null($kelasPost)) {
            if (!is_array($kelasPost)) $kelasPost = [$kelasPost];
            $kelasArr = array_values(array_unique(array_filter(array_map(static function ($v) {
                $n = (int)$v;
                return $n > 0 ? $n : null;
            }, $kelasPost))));

            if (!empty($kelasArr)) {
                $validKelas = $this->ModelKelas->whereIn('id_kelas', $kelasArr)->findColumn('id_kelas');
                $validKelas = array_map('intval', $validKelas ?? []);
                if (count($validKelas) !== count($kelasArr)) {
                    return redirect()->back()->withInput()
                        ->with('sweet_error', 'Terdapat kelas yang tidak valid.');
                }
            }
        }

        // ====== ATURAN JABATAN ======
        // Wali Kelas: 1 kelas saja (wajib tepat satu), mapel boleh > 1
        if ($isWali) {
            // Jika user mengirim kelas → wajib tepat satu
            if (is_array($kelasArr)) {
                if (count($kelasArr) !== 1) {
                    return redirect()->back()->withInput()
                        ->with('sweet_error', 'Wali Kelas hanya boleh memilih satu kelas.');
                }
            } else {
                // Kelas tidak dikirim → cek existing di TA ini. Jika kosong → wajib pilih satu kelas.
                $distinctKelas = $this->ModelGuruMatpel->select('id_kelas')
                    ->where([
                        'id_guru'         => $idGuru,
                        'id_tahun_ajaran' => $idTa,
                    ])->where('id_kelas IS NOT NULL', null, false)
                    ->groupBy('id_kelas')
                    ->findColumn('id_kelas');

                $distinctKelas = array_map('intval', $distinctKelas ?? []);
                if (empty($distinctKelas)) {
                    return redirect()->back()->withInput()
                        ->with('sweet_error', 'Wali Kelas wajib memilih tepat satu kelas.');
                }
                if (count($distinctKelas) > 1) {
                    return redirect()->back()->withInput()
                        ->with('sweet_error', 'Data wali kelas ini memiliki lebih dari satu kelas pada tahun ajaran ini. Mohon rapikan data terlebih dahulu.');
                }
                // tetap biarkan $kelasArr = null (mode tidak mengubah kelas), hanya update jam/ket.
            }
        }

        // Guru (biasa): 1 mapel saja, kelas boleh banyak
        if ($isGuru) {
            if (count($mapelIds) !== 1) {
                return redirect()->back()->withInput()
                    ->with('sweet_error', 'Guru hanya diperbolehkan memiliki satu mata pelajaran.');
            }
        }

        $gmModel = $this->ModelGuruMatpel;

        // 4) Transaksi
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Ambil semua penugasan EXISTING untuk guru + TA ini
            $existing = $gmModel->select('id_guru_mapel, id_mapel, id_kelas')
                ->where([
                    'id_guru'         => $idGuru,
                    'id_tahun_ajaran' => $idTa,
                ])->findAll();

            // Index existing: per mapel → list kelas
            $byMapel = [];
            foreach ($existing as $r) {
                $mid = (int)$r['id_mapel'];
                $kid = (int)$r['id_kelas'];
                $byMapel[$mid][$kid] = (int)$r['id_guru_mapel'];
            }

            // --- 4A) Hapus semua MAPEL yang tidak lagi dipilih (untuk TA ini)
            $existingMapels = array_keys($byMapel);
            $mapelsToDelete = array_diff($existingMapels, $mapelIds);
            if (!empty($mapelsToDelete)) {
                $gmModel->where([
                    'id_guru'         => $idGuru,
                    'id_tahun_ajaran' => $idTa,
                ])->whereIn('id_mapel', $mapelsToDelete)->delete();
            }

            // --- 4B) Untuk setiap MAPEL yang dipilih:
            foreach ($mapelIds as $mid) {
                $existingKelasForMapel = array_keys($byMapel[$mid] ?? []);

                if (is_array($kelasArr)) {
                    // MODE SINKRON KELAS (kelas dikirim)
                    // (Jika Wali Kelas → kelasArr validasinya sudah dipastikan 1 item)
                    $desired = $kelasArr;

                    // Insert yang belum ada
                    $toInsert = array_diff($desired, $existingKelasForMapel);
                    foreach ($toInsert as $kid) {
                        $gmModel->insert([
                            'id_guru'         => $idGuru,
                            'id_mapel'        => $mid,
                            'id_tahun_ajaran' => $idTa,
                            'id_kelas'        => (int)$kid,
                            'jam_per_minggu'  => $jam,
                            'keterangan'      => $ket,
                        ]);
                    }

                    // Update jam/ket untuk yang dipertahankan
                    $toKeep = array_intersect($existingKelasForMapel, $desired);
                    if (!empty($toKeep)) {
                        $ids = [];
                        foreach ($toKeep as $kid) {
                            $ids[] = (int)($byMapel[$mid][$kid] ?? 0);
                        }
                        foreach ($ids as $pk) {
                            if ($pk > 0) {
                                $gmModel->update($pk, [
                                    'jam_per_minggu' => $jam,
                                    'keterangan'     => $ket,
                                ]);
                            }
                        }
                    }

                    // Hapus yang tidak dipilih lagi
                    $toDelete = array_diff($existingKelasForMapel, $desired);
                    if (!empty($toDelete)) {
                        $gmModel->where([
                            'id_guru'         => $idGuru,
                            'id_mapel'        => $mid,
                            'id_tahun_ajaran' => $idTa,
                        ])->whereIn('id_kelas', $toDelete)->delete();
                    }
                } else {
                    // MODE TANPA PERUBAHAN KELAS (kelas TIDAK dikirim)
                    // → hanya update jam/ket untuk semua baris mapel ini
                    if (!empty($existingKelasForMapel)) {
                        $ids = [];
                        foreach ($existingKelasForMapel as $kid) {
                            $ids[] = (int)($byMapel[$mid][$kid] ?? 0);
                        }
                        foreach ($ids as $pk) {
                            if ($pk > 0) {
                                $gmModel->update($pk, [
                                    'jam_per_minggu' => $jam,
                                    'keterangan'     => $ket,
                                ]);
                            }
                        }
                    }
                    // tidak ada insert/delete kelas pada cabang ini
                }
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()
                ->with('sweet_error', 'Gagal menyimpan penugasan: ' . $e->getMessage());
        }

        return redirect()->to(base_url('operator/data-guru'))
            ->with('sweet_success', 'Penugasan berhasil diperbarui.');
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
    // routes:
    // $routes->delete('operator/detail-guru/(:segment)/delete-all', 'Operator::aksi_hapus_semua_guru_mapel/$1');

    public function aksi_hapus_semua_guru_mapel(string $nipParam)
    {
        $nip = urldecode(trim($nipParam));

        // 1) Lock id_guru by NIP
        $guru = $this->ModelGuru->select('id_guru, nip')->where('nip', $nip)->first();
        if (! $guru) {
            session()->setFlashdata('sweet_error', 'Guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }
        $idGuru = (int)$guru['id_guru'];

        // 2) Hapus semua penugasan mapel milik id_guru (semua mapel/kelas/TA)
        $db = \Config\Database::connect();
        $db->transStart();

        // pastikan model menunjuk ke tabel penugasan (tb_guru_mapel / tbl_guru_mapel sesuai skema Anda)
        $this->ModelGuruMatpel->where('id_guru', $idGuru)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('sweet_error', 'Gagal menghapus semua penugasan mapel guru.');
            return redirect()->back();
        }

        session()->setFlashdata('sweet_success', 'Semua penugasan mapel guru berhasil dihapus.');
        return redirect()->to(base_url('operator/data-guru'));
    }






    // DATA USER
    public function data_user()
    {
        $allUsers = $this->UserModel->findAll();

        $q    = trim((string) $this->request->getGet('q'));
        $role = strtolower(trim((string) $this->request->getGet('role'))); // dari form

        // terima hanya role yang valid
        $allowedRoles = ['operator', 'guru', 'siswa'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = '';
        }

        // filter
        $filtered = array_filter($allUsers, function ($row) use ($q, $role) {
            $ok = true;

            if ($q !== '') {
                $u = strtolower(trim((string)($row['username'] ?? '')));
                $e = strtolower(trim((string)($row['email'] ?? '')));
                $k = strtolower($q);
                $ok = (strpos($u, $k) !== false) || (strpos($e, $k) !== false);
            }

            if ($ok && $role !== '') {
                $r = strtolower(trim((string)($row['role'] ?? '')));
                $ok = ($r === $role);
            }

            return $ok;
        });

        // urutkan by username (case-insensitive)
        usort($filtered, fn($a, $b) => strcasecmp((string)($a['username'] ?? ''), (string)($b['username'] ?? '')));

        // tanpa pagination: kirim semua
        $pageData = array_values($filtered); // reindex

        return view('pages/operator/data_user', [
            'title'     => 'Data User | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Data Pengguna',
            'nav_link'  => 'Data User',
            'd_user'    => $pageData,   // semua data tampil
            'q'         => $q,
            'role'      => $role,
            // metadata opsional (tidak dipakai di view)
            'total'     => count($pageData),
            'pages'     => 1,
            'page'      => 1,
            'per_page'  => 'all',
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
                // Tanpa spasi, hanya [A-Za-z0-9._], 4–24 karakter, unik
                'rules'  => 'required|min_length[4]|max_length[24]|regex_match[/^[A-Za-z0-9._]+$/]|is_unique[tb_users.username]',
                'errors' => [
                    'required'    => 'Username wajib diisi.',
                    'min_length'  => 'Username minimal 4 karakter.',
                    'max_length'  => 'Username maksimal 24 karakter.',
                    'regex_match' => 'Username hanya boleh huruf, angka, titik (.) dan underscore (_), tanpa spasi.',
                    'is_unique'   => 'Username sudah terdaftar, silakan gunakan yang lain.'
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

        // ---------- (Guard tambahan) ----------
        // Jika ingin ekstra aman (mis. mencegah bypass), cek lagi di server:
        if (!preg_match('/^[A-Za-z0-9._]+$/', $username)) {
            session()->setFlashdata('sweet_error', 'Username hanya boleh huruf, angka, titik (.) dan underscore (_), tanpa spasi.');
            return redirect()->back()->withInput();
        }

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
    public function aksi_delete_data_user(string $idUserRaw)
    {
        $idUser = (int) $idUserRaw;

        // --- Cek data user existing ---
        $existing = $this->UserModel->where('id_user', $idUser)->first();
        if (!$existing) {
            session()->setFlashdata('sweet_error', 'Data user tidak ditemukan.');
            return redirect()->to(base_url('operator/data-user'));
        }

        try {
            $this->UserModel->delete($idUser);
        } catch (\Throwable $e) {
            session()->setFlashdata('sweet_error', 'Gagal menghapus data user: ' . $e->getMessage());
            return redirect()->back();
        }

        session()->setFlashdata('sweet_success', 'Data user berhasil dihapus.');
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
    // di class controller (mis. Operator)
    private array $blockedJabatan = ['kepala sekolah', 'wakil kepala', 'operator', 'staff'];

    private function isJabatanBlocked(?string $jabatan): bool
    {
        $j = mb_strtolower(trim((string)$jabatan), 'UTF-8');
        return in_array($j, $this->blockedJabatan, true);
    }

    public function page_tambah_guru_mapel(?string $nipGuru = null)
    {
        // --- Model
        $guruModel  = new \App\Models\ModelGuru();        // tb_guru
        $mapelModel = new \App\Models\ModelMatpel();      // tb_mapel
        $taModel    = new \App\Models\TahunAjaranModel(); // tb_tahun_ajaran
        $kelasModel = new \App\Models\KelasModel();       // tb_kelas
        $gmModel    = new \App\Models\GuruMatpel();       // tb_guru_mapel

        // --- Dropdown dasar
        $guruList = $guruModel->select('id_guru, nama_lengkap, nip')
            ->orderBy('nama_lengkap', 'ASC')->findAll();

        // HANYA tahun ajaran AKTIF
        $tahunList = $taModel->select('id_tahun_ajaran, tahun, semester, is_active')
            ->where('is_active', 1)
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'ASC')
            ->findAll();

        // Kelas (tetap semua; nanti bisa dipersempit kalau wali kelas)
        $kelasList = $kelasModel->select('id_kelas, nama_kelas, tingkat, jurusan')
            ->orderBy('tingkat', 'ASC')
            ->orderBy('nama_kelas', 'ASC')
            ->findAll();

        // --- Ambil ID TA aktif (jika ada)
        $idTaAktif = null;
        if (!empty($tahunList)) {
            // karena sudah difilter aktif, cukup ambil yang pertama
            $idTaAktif = (int)($tahunList[0]['id_tahun_ajaran'] ?? 0) ?: null;
        }

        // --- Preselect & guard bila NIP diberikan
        $d_row     = [];
        $idGuru    = null;
        $isWali    = false;
        $lockedKls = null;

        if (!empty($nipGuru)) {
            $guru = $guruModel->select('id_guru, jabatan, nama_lengkap, nip')
                ->where('nip', (string)$nipGuru)->first();

            if (!$guru) {
                session()->setFlashdata('sweet_error', 'Guru dengan NIP ' . esc($nipGuru) . ' tidak ditemukan.');
                return redirect()->to(base_url('operator/data-guru'));
            }

            $idGuru = (int)$guru['id_guru'];

            // Blokir jabatan tertentu (opsional)
            $blokir = in_array(
                mb_strtolower(trim((string)($guru['jabatan'] ?? '')), 'UTF-8'),
                ['kepala sekolah', 'wakil kepala', 'operator', 'staff', 'staf'],
                true
            );
            if ($blokir) {
                session()->setFlashdata('sweet_error', 'Jabatan ini tidak diperbolehkan memiliki penugasan mapel.');
                return redirect()->to(base_url('operator/data-guru'));
            }

            // Aturan wali kelas (boleh 1 kelas saja di TA aktif)
            $isWali = (mb_strtolower(trim((string)($guru['jabatan'] ?? '')), 'UTF-8') === 'wali kelas');

            if ($isWali && $idTaAktif) {
                // cek apakah wali sudah punya kelas di TA aktif
                $kelasAssigned = $gmModel->select('id_kelas')
                    ->where('id_guru', $idGuru)
                    ->where('id_tahun_ajaran', $idTaAktif)
                    ->where('id_kelas IS NOT NULL', null, false)
                    ->groupBy('id_kelas')
                    ->get()->getResultArray();

                if (!empty($kelasAssigned)) {
                    // kunci ke kelas yang sudah ada
                    $lockedKls = (int)$kelasAssigned[0]['id_kelas'];

                    // batasi dropdown kelas
                    $kelasList = $kelasModel->select('id_kelas, nama_kelas, tingkat, jurusan')
                        ->where('id_kelas', $lockedKls)->findAll();

                    $d_row['id_kelas']   = (string)$lockedKls;
                    $d_row['lock_kelas'] = true;
                } else {
                    $d_row['lock_kelas'] = false; // boleh pilih satu (validasi saat simpan)
                }
            }

            // Preselect form
            $d_row['id_guru'] = (string)$idGuru;
            if ($idTaAktif) {
                $d_row['id_tahun_ajaran'] = (string)$idTaAktif;
            }
        }

        // --- Filter mapel: hanya aktif & (kalau id_guru ada) belum dimiliki di TA aktif
        if ($idGuru && $idTaAktif) {
            $on = 'gm.id_mapel = tb_mapel.id_mapel AND gm.id_guru = ' . (int)$idGuru .
                ' AND gm.id_tahun_ajaran = ' . (int)$idTaAktif;
            $mapelList = $mapelModel
                ->select('tb_mapel.id_mapel, tb_mapel.nama, tb_mapel.kode')
                ->join('tb_guru_mapel gm', $on, 'left')
                ->where('gm.id_mapel', null)
                ->where('tb_mapel.is_active', 1)
                ->orderBy('tb_mapel.nama', 'ASC')
                ->findAll();
        } else {
            // tanpa id_guru atau tanpa TA aktif → hanya tampilkan mapel aktif
            $mapelList = $mapelModel
                ->select('id_mapel, nama, kode')
                ->where('is_active', 1)
                ->orderBy('nama', 'ASC')
                ->findAll();
        }

        // --- Kirim ke view
        return view('pages/operator/guru_mapel', [
            'title'       => 'Tambah Guru Mapel | SDN Talun 2 Kota Serang',
            'sub_judul'   => 'Tambah Guru Mapel',
            'nav_link'    => 'Tambah Guru Mapel',
            'guruList'    => $guruList,
            'mapelList'   => $mapelList,
            'tahunList'   => $tahunList, // hanya TA aktif
            'kelasList'   => $kelasList,
            'd_row'       => $d_row,
            'idTaAktif'   => $idTaAktif,
            'isWaliKelas' => $isWali,
        ]);
    }



    public function aksi_tambah_guru_mapel()
    {
        $req = $this->request;

        // --- Validasi dasar (field tunggal) + pesan Indonesia
        $rules = [
            'id_guru'         => 'required|is_natural_no_zero',
            'id_tahun_ajaran' => 'required|is_natural_no_zero',
            'jam_per_minggu'  => 'required|is_natural', // boleh 0
            // id_mapel & id_kelas divalidasi manual karena berbentuk array
        ];
        $messages = [
            'id_guru' => [
                'required'            => 'Guru wajib dipilih.',
                'is_natural_no_zero'  => 'Guru tidak valid.',
            ],
            'id_tahun_ajaran' => [
                'required'            => 'Tahun ajaran wajib dipilih.',
                'is_natural_no_zero'  => 'Tahun ajaran tidak valid.',
            ],
            'jam_per_minggu' => [
                'required'   => 'Jam per minggu wajib diisi.',
                'is_natural' => 'Jam per minggu harus berupa angka 0 atau lebih.',
            ],
        ];
        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- Ambil data POST
        $idGuru  = (int) $req->getPost('id_guru');
        $idTa    = (int) $req->getPost('id_tahun_ajaran');
        $jam     = (int) $req->getPost('jam_per_minggu');
        $ket     = (string) $req->getPost('keterangan');

        // Ambil array mapel & kelas (multi)
        $mapelArr = $req->getPost('id_mapel'); // di form: name="id_mapel[]"
        $kelasArr = $req->getPost('id_kelas'); // di form: name="id_kelas[]" (atau single -> string)

        // Pastikan array
        if (!is_array($mapelArr)) $mapelArr = ($mapelArr !== null && $mapelArr !== '') ? [$mapelArr] : [];
        if (!is_array($kelasArr)) $kelasArr = ($kelasArr !== null && $kelasArr !== '') ? [$kelasArr] : [];

        // Normalisasi -> integer > 0 + dedup
        $normIntArray = static function (array $arr): array {
            $arr = array_map(static function ($v) {
                return (int)$v;
            }, $arr);
            $arr = array_filter($arr, static fn($v) => $v > 0);
            $arr = array_values(array_unique($arr));
            return $arr;
        };

        $mapelIds = $normIntArray($mapelArr);
        $kelasIds = $normIntArray($kelasArr);

        // Validasi manual (bahasa Indonesia)
        $manualErrors = [];
        if (empty($mapelIds)) $manualErrors['id_mapel'] = 'Minimal pilih satu mata pelajaran.';
        if (empty($kelasIds)) $manualErrors['id_kelas'] = 'Minimal pilih satu kelas.';

        if (!empty($manualErrors)) {
            return redirect()->back()->withInput()->with('errors', $manualErrors);
        }

        // --- Cek jabatan guru (logika Wali Kelas)
        $guruModel = new \App\Models\ModelGuru();
        $guru = $guruModel->select('id_guru, jabatan, nip, nama_lengkap')
            ->where('id_guru', $idGuru)
            ->first();

        if (!$guru) {
            session()->setFlashdata('sweet_error', 'Guru tidak ditemukan.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        $jabatan = mb_strtolower(trim((string)($guru['jabatan'] ?? '')), 'UTF-8');

        // Blokir jabatan tertentu (opsional)
        $blokir = ['kepala sekolah', 'wakil kepala', 'operator', 'staff', 'staf'];
        if (in_array($jabatan, $blokir, true)) {
            session()->setFlashdata('sweet_error', 'Jabatan ini tidak diperbolehkan memiliki penugasan mapel.');
            return redirect()->to(base_url('operator/data-guru'));
        }

        // *** Aturan Wali Kelas: hanya boleh 1 kelas, tapi mapel boleh banyak
        if ($jabatan === 'wali kelas' && count($kelasIds) > 1) {
            return redirect()->back()->withInput()->with('errors', [
                'id_kelas' => 'Wali Kelas hanya diperbolehkan memilih satu kelas.',
            ]);
        }

        // --- Model penugasan
        $gmModel = new \App\Models\GuruMatpel();

        // --- Transaksi Insert (loop: setiap MAPEL x setiap KELAS)
        $db = \Config\Database::connect();
        $db->transException(true)->transStart();

        $inserted = 0;
        $skipped  = 0;

        foreach ($mapelIds as $idMapel) {
            foreach ($kelasIds as $idKelas) {
                // Cek duplikat kombinasi
                $exists = $gmModel->where([
                    'id_guru'         => $idGuru,
                    'id_mapel'        => $idMapel,
                    'id_tahun_ajaran' => $idTa,
                    'id_kelas'        => $idKelas,
                ])->countAllResults(true) > 0;

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $gmModel->insert([
                    'id_guru'         => $idGuru,
                    'id_mapel'        => $idMapel,
                    'id_tahun_ajaran' => $idTa,
                    'id_kelas'        => $idKelas,
                    'jam_per_minggu'  => $jam,
                    'keterangan'      => $ket,
                ]);
                $inserted++;
            }
        }

        $db->transComplete();

        // --- Feedback
        if ($inserted > 0) {
            $msg = "Berhasil menambahkan {$inserted} penugasan.";
            if ($skipped > 0) $msg .= " ({$skipped} data duplikat dilewati)";
            session()->setFlashdata('sweet_success', $msg);
        } else {
            session()->setFlashdata('sweet_warning', "Tidak ada penugasan baru. {$skipped} data duplikat dilewati.");
        }

        return redirect()->to(base_url('operator/data-guru'));
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
    public function aksi_delete_kelas($id = null)
    {
        // Pastikan kelas ada
        $row = $this->ModelKelas->find($id);
        if (!$row) {
            return redirect()->to(site_url('operator/kelas'))
                ->with('sweet_error', 'Data kelas tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Cek relasi (ganti nama kolom kalau beda)
        $jmlSiswa     = (int) $db->table('tb_siswa')->where('kelas_id', $id)->countAllResults();
        $jmlGuruMapel = (int) $db->table('tb_guru_mapel')->where('id_kelas', $id)->countAllResults();

        if ($jmlSiswa > 0 || $jmlGuruMapel > 0) {
            $parts = [];
            if ($jmlSiswa > 0)     $parts[] = "- Siswa terdaftar: {$jmlSiswa}";
            if ($jmlGuruMapel > 0) $parts[] = "- Guru/Mapel terkait: {$jmlGuruMapel}";

            $msg = "Kelas tidak dapat dihapus karena masih digunakan:\n"
                . implode("\n", $parts)
                . "\n\nSilakan pindahkan siswa/guru mapel dari kelas ini terlebih dahulu.";

            return redirect()->to(site_url('operator/kelas'))
                ->with('sweet_error', nl2br($msg));
        }

        // Aman dihapus
        try {
            $this->ModelKelas->delete($id);
            return redirect()->to(site_url('operator/kelas'))
                ->with('sweet_success', 'Kelas berhasil dihapus.');
        } catch (\Throwable $e) {
            // fallback jika masih ada FK lain yang luput
            return redirect()->to(site_url('operator/kelas'))
                ->with('sweet_error', 'Gagal menghapus: kelas masih direferensikan atau terjadi kesalahan. Detail: ' . $e->getMessage());
        }
    }



    // Lapiran Data Siswa
    public function page_laporan_d_siswa()
    {
        $req        = $this->request;
        $q          = trim((string)($req->getGet('q') ?? ''));               // cari nama/NISN
        $idTA       = trim((string)($req->getGet('tahunajaran') ?? ''));     // id_tahun_ajaran (opsional)
        $kategori   = strtoupper((string)($req->getGet('kategori') ?? ''));  // UTS/UAS (opsional)
        $gender     = strtoupper(trim((string)($req->getGet('gender') ?? ''))); // 'L' / 'P' / ''

        // List TA untuk dropdown
        $listTA = $this->TahunAjaran
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'ASC')
            ->findAll();

        // Query utama
        $builder = $this->SiswaTahunanModel
            ->select("
            id_siswa_tahun, siswa_id, tahun_ajaran_id, status, tanggal_masuk, tanggal_keluar,
            s.id_siswa, s.nisn, s.full_name, s.gender,
            u.username AS user_name, u.is_active AS user_active,
            ta.tahun AS tahun_ajaran, ta.semester AS semester, ta.is_active AS ta_active
        ", false)
            ->join('tb_siswa s', 's.id_siswa = siswa_id', 'left')
            ->join('tb_users u', 'u.id_user = s.user_id', 'left')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = tahun_ajaran_id', 'left');

        // Filter q (nama / NISN)
        if ($q !== '') {
            $builder->groupStart()
                ->like('s.full_name', $q)
                ->orLike('s.nisn', $q)
                ->groupEnd();
        }

        // Filter Tahun Ajaran
        if ($idTA !== '' && ctype_digit($idTA)) {
            $builder->where('tahun_ajaran_id', (int)$idTA);
        }

        // Filter Gender (hanya jika L/P)
        if ($gender === 'L' || $gender === 'P') {
            $builder->where('s.gender', $gender);
        }

        // (opsional) filter kategori nilai...

        $rows = $builder->orderBy('s.full_name', 'ASC')->findAll();

        // Ringkasan
        $SiswaAktif = $SiswaNonAktif = $EnrolAktif = $EnrolNonAktif = 0;
        foreach ($rows as $r) {
            ((int)($r['user_active'] ?? 0) === 1) ? $SiswaAktif++ : $SiswaNonAktif++;
            $st = strtolower((string)($r['status'] ?? ''));
            in_array($st, ['1', 'aktif', 'active', 'ya', 'true'], true) ? $EnrolAktif++ : $EnrolNonAktif++;
        }

        return view('pages/operator/laporan_data_siswa', [
            'title'         => 'Laporan Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Laporan Data Siswa/i',
            'nav_link'      => 'Laporan Data Siswa',
            'd_siswa'       => $rows,
            'q'             => $q,
            'tahunajaran'   => $idTA,
            'kategori'      => $kategori,
            'gender'        => $gender,     // ← KIRIM KE VIEW (buat selected option)
            'listTA'        => $listTA,
            'SiswaAktif'    => $SiswaAktif,
            'SiswaNonAktif' => $SiswaNonAktif,
            'EnrolAktif'    => $EnrolAktif,
            'EnrolNonAktif' => $EnrolNonAktif,
            'totalSiswa'    => count($rows),
        ]);
    }


    public function page_tambah_laporan_siswa(?string $param = null)
    {
        $siswaModel = new \App\Models\SiswaModel();        // kolom: id_siswa, nisn, full_name
        $taModel    = new \App\Models\TahunAjaranModel();  // kolom: id_tahun_ajaran, tahun, semester, is_active

        $siswaTerpilih = null;
        $isParamNisn   = false;

        $param = isset($param) ? trim($param) : null;

        if (!empty($param)) {
            // 1) Coba sebagai id_siswa (numerik)
            if (ctype_digit($param)) {
                $siswaTerpilih = $siswaModel
                    ->select('id_siswa, nisn, full_name')
                    ->where('id_siswa', (int) $param)
                    ->limit(1)
                    ->first();
            }

            // 2) Jika belum ketemu, coba sebagai NISN
            if (!$siswaTerpilih) {
                $siswaTerpilih = $siswaModel
                    ->select('id_siswa, nisn, full_name')
                    ->where('nisn', $param)
                    ->limit(1)
                    ->first();

                if ($siswaTerpilih) {
                    $isParamNisn = true; // tandai param memang NISN
                }
            }

            // 3) Tetap tak ketemu → 404
            if (!$siswaTerpilih) {
                throw PageNotFoundException::forPageNotFound('Siswa tidak ditemukan.');
            }
        }

        // Dropdown siswa:
        // - Jika datang via NISN → kunci hanya 1 opsi
        // - Selain itu → tampilkan semua siswa
        if ($isParamNisn && $siswaTerpilih) {
            $d_siswa = [$siswaTerpilih];
        } else {
            $d_siswa = $siswaModel
                ->select('id_siswa, nisn, full_name')
                ->orderBy('full_name', 'ASC')
                ->findAll();
        }

        // Tahun ajaran: HANYA yang aktif
        $d_tahun = $taModel
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->where('is_active', 1)
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'ASC')
            ->findAll();

        // Fallback opsional: jika tidak ada yang aktif, tampilkan semua supaya form tetap bisa dipakai
        if (empty($d_tahun)) {
            $d_tahun = $taModel
                ->select('id_tahun_ajaran, tahun, semester, is_active')
                ->orderBy('tahun', 'DESC')
                ->orderBy('semester', 'ASC')
                ->findAll();
        }

        $data = [
            'title'         => 'Tambah Data Siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Tambah Data Siswa/i',
            'nav_link'      => 'Tambah Data Siswa',
            'd_siswa'       => $d_siswa,
            'd_tahun'       => $d_tahun,
            'siswaTerpilih' => $siswaTerpilih,
            'preselectId'   => (int) ($siswaTerpilih['id_siswa'] ?? 0),
            // guard NISN untuk POST (verifikasi pasangan siswa_id × nisn_lock)
            'nisn_lock'     => $isParamNisn && $siswaTerpilih ? (string) $siswaTerpilih['nisn'] : null,
            // opsional: memudahkan view mengunci dropdown
            'lock_dropdown' => $isParamNisn && $siswaTerpilih,
        ];

        return view('pages/operator/tambah_siswa_laporan', $data);
    }
    public function aksi_laporan_data_siswa()
    {

        $req        = $this->request;
        $siswaModel = new \App\Models\SiswaModel();          // id_siswa (PK), nisn, nama_lengkap
        $taModel    = new \App\Models\TahunAjaranModel();     // id_tahun_ajaran, tahun, semester, is_active
        $lapModel   = new \App\Models\SiswaTahunanModel();    // id_laporan, siswa_id, tahun_ajaran_id, status, tanggal_masuk, tanggal_keluar, created_at

        // ========= 1) RULES & MESSAGES =========
        $rules = [
            'siswa_id'         => 'required|is_natural_no_zero',
            'tahun_ajaran_id'  => 'required|is_natural_no_zero',
            'status'           => 'required|in_list[aktif,keluar,lulus]',
            'tanggal_masuk'    => 'required|valid_date[Y-m-d]',
            'tanggal_keluar'   => 'permit_empty|valid_date[Y-m-d]',
            // nisn_lock opsional (ada jika form dibuka via NISN)
        ];

        $messages = [
            'siswa_id' => [
                'required'           => 'Siswa wajib dipilih.',
                'is_natural_no_zero' => 'Siswa tidak valid.',
            ],
            'tahun_ajaran_id' => [
                'required'           => 'Tahun ajaran wajib dipilih.',
                'is_natural_no_zero' => 'Tahun ajaran tidak valid.',
            ],
            'status' => [
                'required' => 'Status wajib dipilih.',
                'in_list'  => 'Status harus salah satu dari: aktif, keluar, atau lulus.',
            ],
            'tanggal_masuk' => [
                'required'   => 'Tanggal masuk wajib diisi.',
                'valid_date' => 'Tanggal masuk harus berformat YYYY-MM-DD.',
            ],
            'tanggal_keluar' => [
                'valid_date' => 'Tanggal keluar harus berformat YYYY-MM-DD.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ========= 2) AMBIL INPUT =========
        $siswaId    = (int) $req->getPost('siswa_id');
        $taId       = (int) $req->getPost('tahun_ajaran_id');
        $status     = (string) $req->getPost('status');
        $tglMasuk   = (string) $req->getPost('tanggal_masuk');
        $tglKeluar  = trim((string) $req->getPost('tanggal_keluar'));
        $nisnLock   = trim((string) $req->getPost('nisn_lock')); // opsional

        // ========= 3) GUARD nisn_lock (jika ada) =========
        if ($nisnLock !== '') {
            $pairOk = $siswaModel->select('id_siswa')
                ->where('id_siswa', $siswaId)
                ->where('nisn', $nisnLock)
                ->limit(1)
                ->first();

            if (! $pairOk) {
                return redirect()->back()->withInput()->with('errors', [
                    'siswa_id' => 'Tidak diizinkan: siswa tidak sesuai dengan NISN di URL.',
                ]);
            }
        }

        // ========= 4) CEK ENTITAS ADA =========
        $siswaRow = $siswaModel->select('id_siswa')->where('id_siswa', $siswaId)->first();
        if (! $siswaRow) {
            return redirect()->back()->withInput()->with('errors', [
                'siswa_id' => 'Siswa tidak ditemukan.',
            ]);
        }

        $taRow = $taModel->select('id_tahun_ajaran, is_active')->where('id_tahun_ajaran', $taId)->first();
        if (! $taRow) {
            return redirect()->back()->withInput()->with('errors', [
                'tahun_ajaran_id' => 'Tahun ajaran tidak ditemukan.',
            ]);
        }

        // ========= 5) LOGIKA STATUS & TANGGAL =========
        $logicErrors = [];

        // a) Status AKTIF → tanggal_keluar harus kosong
        if ($status === 'aktif' && $tglKeluar !== '') {
            $logicErrors['tanggal_keluar'] = 'Untuk status AKTIF, tanggal keluar harus dikosongkan.';
        }

        // b) Status KELUAR/LULUS → tanggal_keluar wajib
        if (in_array($status, ['keluar', 'lulus'], true) && $tglKeluar === '') {
            $logicErrors['tanggal_keluar'] = 'Untuk status KELUAR/LULUS, tanggal keluar wajib diisi.';
        }

        // c) Urutan tanggal: keluar ≥ masuk
        if ($tglKeluar !== '' && $tglMasuk !== '' && $tglKeluar < $tglMasuk) {
            $logicErrors['tanggal_keluar'] = 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.';
        }

        if (! empty($logicErrors)) {
            return redirect()->back()->withInput()->with('errors', $logicErrors);
        }

        // ========= 6) ANTI-DUPLIKASI (siswa_id × tahun_ajaran_id) =========
        $dupe = $lapModel->select('id_siswa_tahun')
            ->where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $taId)
            ->limit(1)
            ->first();

        if ($dupe) {
            return redirect()->back()->withInput()->with('errors', [
                'tahun_ajaran_id' => 'Laporan untuk siswa & tahun ajaran tersebut sudah ada.',
            ]);
        }

        // ========= 7) SIMPAN =========
        try {
            $lapModel->insert([
                'siswa_id'        => $siswaId,
                'tahun_ajaran_id' => $taId,
                'status'          => $status,
                'tanggal_masuk'   => $tglMasuk,
                'tanggal_keluar'  => ($tglKeluar !== '' ? $tglKeluar : null),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('errors', [
                'general' => 'Gagal menyimpan data. Silakan coba lagi. ' . ($e->getCode() ? '(Kode: ' . $e->getCode() . ')' : ''),
            ]);
        }

        // ========= 8) SUKSES =========
        return redirect()->to(site_url('operator/data-siswa'))
            ->with('sweet_success', 'Laporan siswa berhasil ditambahkan.');
    }
    public function aksi_delete_laporan_siswa($param = null)
    {
        $req        = $this->request;
        $siswaModel = new \App\Models\SiswaModel();         // tb_siswa
        $lapModel   = new \App\Models\SiswaTahunanModel();  // tb_siswa_tahun (PK: id_siswa_tahun)

        if ($param === null || $param === '') {
            return redirect()->back()->with('sweet_error', 'Parameter tidak valid.');
        }

        // ========== CASE 1: PARAM NUMERIC → anggap ID laporan (id_siswa_tahun) ==========
        if (ctype_digit((string)$param)) {
            $lap = $lapModel->select('id_siswa_tahun, siswa_id, tahun_ajaran_id')
                ->where('id_siswa_tahun', (int)$param)
                ->first();
            if (!$lap) {
                return redirect()->back()->with('sweet_error', 'Data laporan tidak ditemukan.');
            }

            try {
                $lapModel->delete((int)$lap['id_siswa_tahun']);
            } catch (\Throwable $e) {
                return redirect()->back()->with('sweet_error', 'Gagal menghapus laporan. ' . $e->getMessage());
            }

            return redirect()->back()->with('sweet_success', 'Laporan siswa berhasil dihapus.');
        }

        // ========== CASE 2: PARAM NON-NUMERIC → anggap NISN ==========
        $nisn  = (string) $param;
        $siswa = $siswaModel->select('id_siswa, nisn, nama_lengkap')
            ->where('nisn', $nisn)
            ->first();
        if (!$siswa) {
            return redirect()->back()->with('sweet_error', 'Siswa berdasarkan NISN tidak ditemukan.');
        }

        // Jika ada parameter tahun ajaran (?ta=ID), hapus laporan spesifik tsb
        $taId = (int) $req->getGet('ta');
        if ($taId > 0) {
            $row = $lapModel->select('id_siswa_tahun')
                ->where('siswa_id', (int)$siswa['id_siswa'])
                ->where('tahun_ajaran_id', $taId)
                ->first();
            if (!$row) {
                return redirect()->back()->with('sweet_error', 'Laporan untuk tahun ajaran tersebut tidak ditemukan.');
            }

            try {
                $lapModel->delete((int)$row['id_siswa_tahun']);
            } catch (\Throwable $e) {
                return redirect()->back()->with('sweet_error', 'Gagal menghapus laporan. ' . $e->getMessage());
            }

            return redirect()->back()->with('sweet_success', 'Laporan siswa berhasil dihapus.');
        }

        // Tanpa ?ta → cek jumlah laporan siswa ini
        $rows = $lapModel->select('id_siswa_tahun')
            ->where('siswa_id', (int)$siswa['id_siswa'])
            ->findAll();

        if (count($rows) === 0) {
            return redirect()->back()->with('sweet_warning', 'Tidak ada laporan untuk siswa ini.');
        }
        if (count($rows) > 1) {
            // Hindari mass-delete tidak sengaja
            return redirect()->back()->with(
                'sweet_error',
                'Terdapat lebih dari satu laporan untuk siswa ini. Sertakan parameter ?ta={tahun_ajaran_id} atau gunakan ID laporan numerik.'
            );
        }

        // Tepat satu laporan → hapus
        try {
            $lapModel->delete((int)$rows[0]['id_siswa_tahun']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('sweet_error', 'Gagal menghapus laporan. ' . $e->getMessage());
        }

        return redirect()->back()->with('sweet_success', 'Laporan siswa berhasil dihapus.');
    }

    public function page_laporan_guru()
    {
        $req    = $this->request;
        $q      = trim((string)($req->getGet('q') ?? ''));
        $idTA   = trim((string)($req->getGet('tahunajaran') ?? ''));
        $gender = trim((string)($req->getGet('gender') ?? ''));

        // Dropdown Tahun Ajaran
        $listTA = $this->TahunAjaran
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'DESC')
            ->findAll();

        // Normalisasi tanggal dari gt (DATE/DATETIME -> NULL jika zero-date/empty)
        $exprMasuk = "
        NULLIF(
            NULLIF(NULLIF(gt.tanggal_masuk, ''), '0000-00-00'),
            '0000-00-00 00:00:00'
        )
    ";
        $exprKeluar = "
        NULLIF(
            NULLIF(NULLIF(gt.tanggal_keluar, ''), '0000-00-00'),
            '0000-00-00 00:00:00'
        )
    ";

        // === MODE A: Ada filter Tahun Ajaran -> tampil per-TA (tidak dedupe)
        if ($idTA !== '' && ctype_digit($idTA)) {
            $builder = $this->GuruTahunanModel
                ->select("
                gt.id_guru_tahun,
                gt.guru_id,
                gt.tahun_ajaran_id,
                gt.status,

                DATE_FORMAT({$exprMasuk},  '%Y-%m-%d') AS tanggal_masuk_all,
                DATE_FORMAT({$exprKeluar}, '%Y-%m-%d') AS tanggal_keluar_all,

                gt.tanggal_masuk   AS t_masuk_gt,
                gt.tanggal_keluar  AS t_keluar_gt,

                g.id_guru,
                g.nip,
                g.nama_lengkap,
                g.jenis_kelamin,
                g.status_active AS guru_active,

                u.username AS user_name,
                u.is_active AS user_active,

                ta.tahun    AS tahun_ajaran,
                ta.semester AS semester,
                ta.is_active AS ta_active
            ", false)
                ->from('tb_guru_tahun gt')
                ->join('tb_guru g', 'g.id_guru = gt.guru_id', 'left')
                ->join('tb_users u', 'u.id_user = g.user_id', 'left')
                ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = gt.tahun_ajaran_id', 'left')
                ->where('gt.tahun_ajaran_id', (int)$idTA);

            if ($q !== '') {
                $builder->groupStart()
                    ->like('g.nama_lengkap', $q)
                    ->orLike('g.nip', $q)
                    ->groupEnd();
            }
            if (in_array($gender, ['L', 'P'], true)) {
                $builder->where('g.jenis_kelamin', $gender);
            }

            $rows = $builder
                ->orderBy('g.nama_lengkap', 'ASC')
                ->orderBy("{$exprMasuk} IS NULL", 'ASC', false)
                ->orderBy($exprMasuk, 'ASC', false)
                ->findAll();

            // === MODE B: Tanpa filter Tahun Ajaran -> dedupe: ambil TA paling baru per guru
        } else {
            $db = \Config\Database::connect();

            // Subquery: beri ranking per guru (TA paling baru = rn=1)
            $sub = $db->table('tb_guru_tahun gt')
                ->select("
                gt.id_guru_tahun,
                gt.guru_id,
                gt.tahun_ajaran_id,
                gt.status,
                gt.tanggal_masuk,
                gt.tanggal_keluar,
                ta.tahun,
                ta.semester,
                ROW_NUMBER() OVER (
                    PARTITION BY gt.guru_id
                    ORDER BY ta.tahun DESC, ta.semester DESC
                ) AS rn
            ", false)
                ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = gt.tahun_ajaran_id', 'left');

            $subSql = $sub->getCompiledSelect(false); // keep aliases as-is

            // Bungkus subquery sebagai e (enrol_terbaru)
            $builder = $db->table("({$subSql}) e")
                ->select("
                e.id_guru_tahun,
                e.guru_id,
                e.tahun_ajaran_id,
                e.status,

                DATE_FORMAT(
                    NULLIF(NULLIF(NULLIF(e.tanggal_masuk, ''), '0000-00-00'), '0000-00-00 00:00:00'),
                    '%Y-%m-%d'
                ) AS tanggal_masuk_all,
                DATE_FORMAT(
                    NULLIF(NULLIF(NULLIF(e.tanggal_keluar, ''), '0000-00-00'), '0000-00-00 00:00:00'),
                    '%Y-%m-%d'
                ) AS tanggal_keluar_all,

                e.tanggal_masuk  AS t_masuk_gt,
                e.tanggal_keluar AS t_keluar_gt,

                g.id_guru,
                g.nip,
                g.nama_lengkap,
                g.jenis_kelamin,
                g.status_active AS guru_active,

                u.username AS user_name,
                u.is_active AS user_active,

                ta.tahun    AS tahun_ajaran,
                ta.semester AS semester,
                ta.is_active AS ta_active
            ", false)
                ->join('tb_guru g', 'g.id_guru = e.guru_id', 'left')
                ->join('tb_users u', 'u.id_user = g.user_id', 'left')
                ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = e.tahun_ajaran_id', 'left')
                ->where('e.rn', 1); // <-- ambil satu baris terbaru per guru

            if ($q !== '') {
                $builder->groupStart()
                    ->like('g.nama_lengkap', $q)
                    ->orLike('g.nip', $q)
                    ->groupEnd();
            }
            if (in_array($gender, ['L', 'P'], true)) {
                $builder->where('g.jenis_kelamin', $gender);
            }

            $rows = $builder
                ->orderBy('g.nama_lengkap', 'ASC')
                ->orderBy("tanggal_masuk_all IS NULL", 'ASC', false)
                ->orderBy('tanggal_masuk_all', 'ASC', false)
                ->get()->getResultArray();
        }

        // Ringkasan
        $GuruAktif = $GuruNonAktif = $EnrolAktif = $EnrolNonAktif = 0;
        foreach ($rows as $r) {
            if ((int)($r['guru_active'] ?? 0) === 1) $GuruAktif++;
            else $GuruNonAktif++;
            $st = strtolower((string)($r['status'] ?? ''));
            if (in_array($st, ['1', 'aktif', 'active', 'ya', 'true'], true)) $EnrolAktif++;
            else $EnrolNonAktif++;
        }

        return view('pages/operator/laporan_data_guru', [
            'title'          => 'Laporan Data Guru | SDN Talun 2 Kota Serang',
            'sub_judul'      => 'Laporan Data Guru',
            'nav_link'       => 'Laporan Data Guru',
            'd_guru'         => $rows,
            'q'              => $q,
            'tahunajaran'    => $idTA,
            'gender'         => $gender,
            'listTA'         => $listTA,
            'GuruAktif'      => $GuruAktif,
            'GuruNonAktif'   => $GuruNonAktif,
            'EnrolAktif'     => $EnrolAktif,
            'EnrolNonAktif'  => $EnrolNonAktif,
            'totalGuru'      => count($rows),
        ]);
    }



    public function page_tambah_laporan_guru(?string $param = null)
    {
        $guruModel = new \App\Models\ModelGuru();          // tb_guru
        $taModel   = new \App\Models\TahunAjaranModel();   // tb_tahun_ajaran

        $guruTerpilih = null;
        $isParamNip   = false;

        $param = isset($param) ? trim($param) : null;

        if ($param !== null && $param !== '') {
            // 1) Coba sebagai id_guru
            if (ctype_digit($param)) {
                $guruTerpilih = $guruModel
                    ->select('id_guru, nip, nama_lengkap')
                    ->where('id_guru', (int)$param)
                    ->first();
            }

            // 2) Jika belum ketemu, coba sebagai NIP
            if (!$guruTerpilih) {
                $guruTerpilih = $guruModel
                    ->select('id_guru, nip, nama_lengkap')
                    ->where('nip', $param)
                    ->first();

                if ($guruTerpilih) {
                    $isParamNip = true;
                }
            }

            // 3) Tetap tak ketemu → 404
            if (!$guruTerpilih) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Guru tidak ditemukan.');
            }
        }

        // Dropdown guru
        if ($isParamNip && $guruTerpilih) {
            $d_guru = [$guruTerpilih];
        } else {
            $d_guru = $guruModel
                ->select('id_guru, nip, nama_lengkap')
                ->orderBy('nama_lengkap', 'ASC')
                ->findAll();
        }

        // ======= Tahun ajaran: hanya yang AKTIF =======
        // Ambil 1 TA aktif terbaru; kalau tidak ada, fallback ke TA terbaru (non-aktif) agar form tetap hidup.
        $taAktif = $taModel
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->where('is_active', 1)
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'DESC')
            ->first();

        if (!$taAktif) {
            // fallback: pakai TA terbaru (non-aktif)
            $taAktif = $taModel
                ->select('id_tahun_ajaran, tahun, semester, is_active')
                ->orderBy('tahun', 'DESC')
                ->orderBy('semester', 'DESC')
                ->first();
        }

        // Kirim ke view sebagai array berisi satu item saja (TA aktif)
        $d_tahun = $taAktif ? [$taAktif] : [];

        $data = [
            'title'         => 'Tambah Data Guru | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Laporan Data Guru',
            'nav_link'      => 'Laporan Data Guru',
            'd_guru'        => $d_guru,
            'd_tahun'       => $d_tahun, // hanya 1 (aktif) atau fallback terbaru
            'guruTerpilih'  => $guruTerpilih,
            'preselectId'   => (int)($guruTerpilih['id_guru'] ?? 0),

            // guard NIP untuk POST
            'nip_lock'      => $isParamNip && $guruTerpilih ? (string)$guruTerpilih['nip'] : null,

            // kunci dropdown guru jika datang via NIP
            'lock_dropdown' => $isParamNip && $guruTerpilih,

            // memudahkan view kalau perlu id TA aktif
            'idTaAktif'     => (int)($taAktif['id_tahun_ajaran'] ?? 0),
        ];

        return view('pages/operator/tambah-guru-laporan', $data);
    }

    public function aksi_laporan_data_guru()
    {
        $req       = $this->request;
        $guruModel = new \App\Models\ModelGuru();          // id_guru (PK), nip, nama_lengkap, ...
        $taModel   = new \App\Models\TahunAjaranModel();   // id_tahun_ajaran, tahun, semester, is_active
        $gtModel   = new \App\Models\GuruTahunanModel();   // id_guru_tahun, guru_id, tahun_ajaran_id, status, tanggal_masuk, tanggal_keluar

        // ========= 1) RULES & MESSAGES =========
        $rules = [
            'guru_id'         => 'required|is_natural_no_zero',
            'tahun_ajaran_id' => 'required|is_natural_no_zero',
            'status'          => 'required|in_list[aktif,nonaktif]',
            'tanggal_masuk'   => 'required|valid_date[Y-m-d]',
            'tanggal_keluar'  => 'permit_empty|valid_date[Y-m-d]',
            // nip_lock optional (sent when page opened via NIP)
        ];
        $messages = [
            'guru_id' => [
                'required'           => 'Guru wajib dipilih.',
                'is_natural_no_zero' => 'Guru tidak valid.',
            ],
            'tahun_ajaran_id' => [
                'required'           => 'Tahun ajaran wajib dipilih.',
                'is_natural_no_zero' => 'Tahun ajaran tidak valid.',
            ],
            'status' => [
                'required' => 'Status wajib dipilih.',
                'in_list'  => 'Status harus salah satu dari: aktif atau nonaktif.',
            ],
            'tanggal_masuk' => [
                'required'   => 'Tanggal masuk wajib diisi.',
                'valid_date' => 'Tanggal masuk harus berformat YYYY-MM-DD.',
            ],
            'tanggal_keluar' => [
                'valid_date' => 'Tanggal keluar harus berformat YYYY-MM-DD.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ========= 2) INPUT =========
        $guruId    = (int) $req->getPost('guru_id');
        $taId      = (int) $req->getPost('tahun_ajaran_id');
        $status    = (string) $req->getPost('status');            // aktif | nonaktif
        $tglMasuk  = (string) $req->getPost('tanggal_masuk');     // YYYY-MM-DD
        $tglKeluar = trim((string) $req->getPost('tanggal_keluar'));
        $nipLock   = trim((string) $req->getPost('nip_lock'));    // optional

        // ========= 3) GUARD nip_lock (jika ada) =========
        if ($nipLock !== '') {
            $pairOk = $guruModel->select('id_guru')
                ->where('id_guru', $guruId)
                ->where('nip', $nipLock)
                ->limit(1)
                ->first();

            if (! $pairOk) {
                return redirect()->back()->withInput()->with('sweet_errors', [
                    'guru_id' => 'Tidak diizinkan: guru tidak sesuai dengan NIP di URL.',
                ]);
            }
        }

        // ========= 4) CEK ENTITAS ADA =========
        $guruRow = $guruModel->select('id_guru')->where('id_guru', $guruId)->first();
        if (! $guruRow) {
            return redirect()->back()->withInput()->with('sweet_errors', [
                'guru_id' => 'Guru tidak ditemukan.',
            ]);
        }
        $taRow = $taModel->select('id_tahun_ajaran, is_active')->where('id_tahun_ajaran', $taId)->first();
        if (! $taRow) {
            return redirect()->back()->withInput()->with('sweet_errors', [
                'tahun_ajaran_id' => 'Tahun ajaran tidak ditemukan.',
            ]);
        }

        // ========= 5) LOGIKA STATUS & TANGGAL =========
        $logicErrors = [];

        // a) Status AKTIF → tanggal_keluar harus kosong
        if ($status === 'aktif' && $tglKeluar !== '') {
            $logicErrors['tanggal_keluar'] = 'Untuk status AKTIF, tanggal keluar harus dikosongkan.';
        }
        // b) Status NONAKTIF → tanggal_keluar wajib
        if ($status === 'nonaktif' && $tglKeluar === '') {
            $logicErrors['tanggal_keluar'] = 'Untuk status NONAKTIF, tanggal keluar wajib diisi.';
        }
        // c) Urutan tanggal: keluar ≥ masuk
        if ($tglKeluar !== '' && $tglMasuk !== '' && $tglKeluar < $tglMasuk) {
            $logicErrors['tanggal_keluar'] = 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.';
        }

        if (! empty($logicErrors)) {
            return redirect()->back()->withInput()->with('errors', $logicErrors);
        }

        // ========= 6) ANTI-DUPLIKASI (guru_id × tahun_ajaran_id) =========
        $dupe = $gtModel->select('id_guru_tahun')
            ->where('guru_id', $guruId)
            ->where('tahun_ajaran_id', $taId)
            ->limit(1)
            ->first();

        if ($dupe) {
            return redirect()->back()->withInput()->with('sweet_errors', [
                'tahun_ajaran_id' => 'Laporan untuk guru & tahun ajaran tersebut sudah ada.',
            ]);
        }

        // ========= 7) SIMPAN =========
        try {
            $gtModel->insert([
                'guru_id'         => $guruId,
                'tahun_ajaran_id' => $taId,
                'status'          => $status,                               // aktif | nonaktif
                'tanggal_masuk'   => $tglMasuk,
                'tanggal_keluar'  => ($tglKeluar !== '' ? $tglKeluar : null),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('sweet_errors', [
                'general' => 'Gagal menyimpan data. Silakan coba lagi. ' . ($e->getCode() ? '(Kode: ' . $e->getCode() . ')' : ''),
            ]);
        }

        // ========= 8) SUKSES =========
        return redirect()->to(site_url('operator/data-guru'))
            ->with('sweet_success', 'Laporan guru berhasil ditambahkan.');
    }
    public function aksi_delete_laporan_guru($param = null)
    {
        $req       = $this->request;
        $guruModel = new \App\Models\ModelGuru();        // tb_guru
        $gtModel   = new \App\Models\GuruTahunanModel(); // tb_guru_tahun (PK: id_guru_tahun)

        if ($param === null || $param === '') {
            return redirect()->back()->with('sweet_error', 'Parameter tidak valid.');
        }

        // ========== CASE 1: PARAM NUMERIC → anggap ID laporan (id_guru_tahun) ==========
        if (ctype_digit((string)$param)) {
            $lap = $gtModel->select('id_guru_tahun, guru_id, tahun_ajaran_id')
                ->where('id_guru_tahun', (int)$param)
                ->first();

            if (! $lap) {
                return redirect()->back()->with('sweet_error', 'Data laporan tidak ditemukan.');
            }

            try {
                $gtModel->delete((int)$lap['id_guru_tahun']);
            } catch (\Throwable $e) {
                return redirect()->back()->with('sweet_error', 'Gagal menghapus laporan. ' . $e->getMessage());
            }

            return redirect()->back()->with('sweet_success', 'Laporan guru berhasil dihapus.');
        }

        // ========== CASE 2: PARAM NON-NUMERIC → anggap NIP ==========
        $nip  = (string) $param;
        $guru = $guruModel->select('id_guru, nip, nama_lengkap')
            ->where('nip', $nip)
            ->first();

        if (! $guru) {
            return redirect()->back()->with('sweet_error', 'Guru berdasarkan NIP tidak ditemukan.');
        }

        // Jika ada parameter tahun ajaran (?ta=ID), hapus laporan spesifik tsb
        $taId = (int) $req->getGet('ta');
        if ($taId > 0) {
            $row = $gtModel->select('id_guru_tahun')
                ->where('guru_id', (int)$guru['id_guru'])
                ->where('tahun_ajaran_id', $taId)
                ->first();

            if (! $row) {
                return redirect()->back()->with('sweet_error', 'Laporan untuk tahun ajaran tersebut tidak ditemukan.');
            }

            try {
                $gtModel->delete((int)$row['id_guru_tahun']);
            } catch (\Throwable $e) {
                return redirect()->back()->with('sweet_error', 'Gagal menghapus laporan. ' . $e->getMessage());
            }

            return redirect()->back()->with('sweet_success', 'Laporan guru berhasil dihapus.');
        }

        // Tanpa ?ta → cek jumlah laporan guru ini
        $rows = $gtModel->select('id_guru_tahun')
            ->where('guru_id', (int)$guru['id_guru'])
            ->findAll();

        if (count($rows) === 0) {
            return redirect()->back()->with('sweet_warning', 'Tidak ada laporan untuk guru ini.');
        }

        if (count($rows) > 1) {
            // Hindari mass-delete tidak sengaja
            return redirect()->back()->with(
                'sweet_error',
                'Terdapat lebih dari satu laporan untuk guru ini. Sertakan parameter ?ta={tahun_ajaran_id} atau gunakan ID laporan numerik.'
            );
        }

        // Tepat satu laporan → hapus
        try {
            $gtModel->delete((int)$rows[0]['id_guru_tahun']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('sweet_error', 'Gagal menghapus laporan. ' . $e->getMessage());
        }

        return redirect()->back()->with('sweet_success', 'Laporan guru berhasil dihapus.');
    }


    // laporan nilai siswa
    public function page_laporan_nilai_siswa()
    {
        $req      = $this->request;
        $q        = trim((string)($req->getGet('q') ?? ''));             // cari nama/NISN
        $idTA     = trim((string)($req->getGet('tahunajaran') ?? ''));   // id_tahun_ajaran (opsional)
        $kodeKat  = trim((string)($req->getGet('kategori') ?? ''));      // opsional: UTS/UAS
        $idMapel  = trim((string)($req->getGet('mapel') ?? ''));         // opsional: id_mapel

        $nilaiModel = new \App\Models\NilaiSiswaModel();  // tb_nilai_siswa

        $builder = $nilaiModel->builder()->from('tb_nilai_siswa ns')
            ->select("
            ns.id_nilai,
            ns.siswa_id,
            ns.tahun_ajaran_id,
            ns.mapel_id,
            ns.kategori_id,
            ns.skor,
            DATE_FORMAT(ns.tanggal, '%Y-%m-%d') AS tanggal_nilai,
            ns.keterangan AS keterangan_nilai,

            s.id_siswa         AS s_id,
            s.nisn             AS siswa_nisn,
            s.full_name        AS siswa_nama,
            s.gender           AS siswa_gender,

            ta.id_tahun_ajaran AS ta_id,
            ta.tahun           AS tahun_ajaran,
            ta.semester        AS semester,

            m.id_mapel         AS m_id,
            m.nama             AS mapel_nama,

            k.id_kategori      AS k_id,
            k.kode             AS kategori_kode,
            k.nama             AS kategori_nama
        ", false)
            ->join('tb_siswa s', 's.id_siswa = ns.siswa_id', 'left')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = ns.tahun_ajaran_id', 'left')
            ->join('tb_mapel m', 'm.id_mapel = ns.mapel_id', 'left')
            ->join('tb_kategori_nilai k', 'k.id_kategori = ns.kategori_id', 'left');

        // Filter q (nama / NISN)
        if ($q !== '') {
            $builder->groupStart()
                ->like('s.full_name', $q)
                ->orLike('s.nisn', $q)
                ->groupEnd();
        }

        // Filter Tahun Ajaran (id)
        if ($idTA !== '' && ctype_digit($idTA)) {
            $builder->where('ns.tahun_ajaran_id', (int)$idTA);
        }

        // (Opsional) Filter Kategori: UTS/UAS via kode
        if ($kodeKat !== '') {
            $builder->where('k.kode', strtoupper($kodeKat));
        }

        // (Opsional) Filter Mapel: id_mapel
        if ($idMapel !== '' && ctype_digit($idMapel)) {
            $builder->where('ns.mapel_id', (int)$idMapel);
        }

        // Hilangkan duplikasi akibat join — kunci ke PK nilai
        $builder->groupBy('ns.id_nilai');

        // Urutkan: Nama siswa → Mapel → Kategori (UTS/UAS) → tanggal
        $rows = $builder
            ->orderBy('s.full_name', 'ASC')
            ->orderBy('m.nama', 'ASC')
            ->orderBy('k.kode', 'ASC')
            ->orderBy('ns.tanggal', 'ASC')
            ->get()
            ->getResultArray();

        // Ringkasan sederhana
        $totalNilai = count($rows);
        $distinctSiswa = [];
        foreach ($rows as $r) {
            $distinctSiswa[(int)($r['siswa_id'] ?? 0)] = true;
        }
        $totalSiswa = count($distinctSiswa);

        // ===== Ambil opsi dropdown dari DB =====
        $db = \Config\Database::connect();

        // Tahun Ajaran
        $listTA = $db->table('tb_tahun_ajaran')
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'ASC')
            ->get()
            ->getResultArray();

        // Kategori (UTS/UAS) - case-insensitive
        $listKat = $db->table('tb_kategori_nilai')
            ->select('id_kategori, kode, nama')
            ->where('UPPER(kode) IN ("UTS","UAS")', null, false) // raw agar aman untuk semua case
            ->orderBy('kode', 'ASC')
            ->get()
            ->getResultArray();

        // Mapel
        $listMapel = $db->table('tb_mapel')
            ->select('id_mapel, nama, kode')
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();

        return view('pages/operator/laporan_nilai_siswa', [
            'title'         => 'Laporan Nilai Siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Laporan Nilai Siswa',
            'nav_link'      => 'Laporan Nilai',
            'd_nilai'       => $rows,
            'q'             => $q,
            'tahunajaran'   => $idTA,
            'kategori'      => $kodeKat,
            'mapel'         => $idMapel,
            'totalNilai'    => $totalNilai,
            'totalSiswa'    => $totalSiswa,

            // opsi dropdown
            'listTA'        => $listTA,
            'listKat'       => $listKat,
            'listMapel'     => $listMapel,
        ]);
    }


    public function page_tambah_nilai_siswa()
    {
        $req     = $this->request;
        $q       = trim((string)($req->getGet('q') ?? ''));            // cari nama/NISN
        $idTA    = trim((string)($req->getGet('tahunajaran') ?? ''));  // id_tahun_ajaran (opsional)
        $kodeKat = trim((string)($req->getGet('kategori') ?? ''));     // opsional: UTS/UAS
        $idMapel = trim((string)($req->getGet('mapel') ?? ''));        // opsional: id_mapel

        $nilaiModel = new \App\Models\NilaiSiswaModel();

        // ====== LIST DATA (tabel utama + join alias) ======
        $builder = $nilaiModel->builder()->from('tb_nilai_siswa ns')
            ->select("
            ns.id_nilai, ns.siswa_id, ns.tahun_ajaran_id, ns.mapel_id, ns.kategori_id, ns.skor, ns.tanggal, ns.keterangan,

            s.nisn      AS siswa_nisn,
            s.full_name AS siswa_nama,
            s.gender    AS siswa_gender,

            ta.tahun    AS ta_tahun,
            ta.semester AS ta_semester,

            m.nama      AS mapel_nama,

            k.kode      AS kategori_kode,
            k.nama      AS kategori_nama
        ")
            ->join('tb_siswa s', 's.id_siswa = ns.siswa_id', 'left')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = ns.tahun_ajaran_id', 'left')
            ->join('tb_mapel m', 'm.id_mapel = ns.mapel_id', 'left')
            ->join('tb_kategori_nilai k', 'k.id_kategori = ns.kategori_id', 'left');

        // Selalu batasi TA aktif (ingat: kolom di DB adalah is_active)
        $builder->where('ta.is_active', 1);

        if ($q !== '') {
            $builder->groupStart()
                ->like('s.full_name', $q)
                ->orLike('s.nisn', $q)
                ->groupEnd();
        }

        if ($idTA !== '' && ctype_digit($idTA)) {
            // Tetap batasi ke TA aktif + id yang dipilih
            $builder->where('ns.tahun_ajaran_id', (int)$idTA);
        }

        if ($kodeKat !== '') {
            $builder->where('k.kode', strtoupper($kodeKat));
        }
        if ($idMapel !== '' && ctype_digit($idMapel)) {
            $builder->where('ns.mapel_id', (int)$idMapel);
        }

        $rows = $builder
            ->orderBy('s.full_name', 'ASC')
            ->orderBy('m.nama', 'ASC')
            ->orderBy('k.kode', 'ASC')
            ->orderBy('ns.tanggal', 'ASC')
            ->get()->getResultArray();

        // Ringkasan
        $totalNilai    = count($rows);
        $distinctSiswa = [];
        foreach ($rows as $r) {
            $distinctSiswa[(int)($r['siswa_id'] ?? 0)] = true;
        }
        $totalSiswa = count($distinctSiswa);

        // ====== DROPDOWN ======
        // Siswa
        $siswaModel = new \App\Models\SiswaModel();
        $siswaQ = $siswaModel->select('id_siswa, nisn, full_name')->orderBy('full_name', 'ASC');
        if ($q !== '') {
            $siswaQ->groupStart()->like('full_name', $q)->orLike('nisn', $q)->groupEnd();
        }
        $optSiswa = $siswaQ->findAll(500);

        // Tahun Ajaran (hanya aktif)
        $taModel = new \App\Models\TahunAjaranModel();
        $optTA   = $taModel->select('id_tahun_ajaran, tahun, semester')
            ->where('is_active', 1) // <— kolom yang benar
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'ASC')
            ->findAll(200);

        // Mapel
        $mapelModel = new \App\Models\ModelMatPel();
        $optMapel   = $mapelModel->select('id_mapel, nama')
            ->orderBy('nama', 'ASC')->findAll(300);

        // Kategori Nilai
        $katModel    = new \App\Models\KategoriNilai();
        $optKategori = $katModel->select('id_kategori, kode, nama')
            ->orderBy('nama', 'ASC')->findAll(100);

        // allowedFields
        $allowedFields = method_exists($nilaiModel, 'getAllowedFields')
            ? $nilaiModel->getAllowedFields()
            : ['siswa_id', 'tahun_ajaran_id', 'mapel_id', 'kategori_id', 'skor', 'tanggal', 'keterangan'];

        return view('pages/operator/tambah_laporan_nilai', [
            'title'         => 'Tambah/Laporan Nilai Siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Data Nilai Siswa',
            'nav_link'      => 'Nilai Siswa',

            // data tabel
            'd_nilai'       => $rows,

            // filter GET
            'q'             => $q,
            'tahunajaran'   => $idTA,
            'kategori'      => $kodeKat,
            'mapel'         => $idMapel,

            // ringkasan
            'totalNilai'    => $totalNilai,
            'totalSiswa'    => $totalSiswa,

            // dropdown sources
            'optSiswa'      => $optSiswa,
            'optTA'         => $optTA,     // hanya TA aktif (ganjil = 1)
            'optMapel'      => $optMapel,
            'optKategori'   => $optKategori,

            // guard form
            'allowedFields' => $allowedFields,
        ]);
    }


    public function aksi_tambah_nilai_siswa()
    {
        $m = new \App\Models\NilaiSiswaModel();

        // Normalisasi & ambil nilai input
        $data = [
            'siswa_id'        => (string) $this->request->getPost('siswa_id'),
            'tahun_ajaran_id' => (string) $this->request->getPost('tahun_ajaran_id'),
            'mapel_id'        => (string) $this->request->getPost('mapel_id'),
            'kategori_id'     => (string) $this->request->getPost('kategori_id'),
            'skor'            => (string) $this->request->getPost('skor'),
            'tanggal'         => (string) $this->request->getPost('tanggal'),
            'keterangan'      => (string) $this->request->getPost('keterangan'),
        ];

        // Validasi (Bahasa Indonesia)
        $rules = [
            'siswa_id' => [
                'label'  => 'Siswa',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_siswa.id_siswa]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'tahun_ajaran_id' => [
                'label'  => 'Tahun Ajaran',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_tahun_ajaran.id_tahun_ajaran]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'mapel_id' => [
                'label'  => 'Mata Pelajaran',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_mapel.id_mapel]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'kategori_id' => [
                'label'  => 'Kategori Penilaian',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_kategori_nilai.id_kategori]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'skor' => [
                'label'  => 'Skor',
                'rules'  => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
                'errors' => [
                    'required'              => '{field} wajib diisi.',
                    'decimal'               => '{field} harus berupa angka/desimal.',
                    'greater_than_equal_to' => '{field} minimal {param}.',
                    'less_than_equal_to'    => '{field} maksimal {param}.',
                ],
            ],
            'tanggal' => [
                'label'  => 'Tanggal',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'valid_date' => '{field} tidak valid (format: YYYY-MM-DD).',
                ],
            ],
            'keterangan' => [
                'label'  => 'Keterangan',
                'rules'  => 'permit_empty|max_length[200]',
                'errors' => [
                    'max_length' => '{field} maksimal {param} karakter.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Pastikan hanya field yang diizinkan yang tersimpan
        $payload = array_intersect_key($data, array_flip($m->getAllowedFields()));

        // ===== CEK DUPLIKASI: siswa_id + tahun_ajaran_id + mapel_id + kategori_id =====
        $exists = (clone $m)
            ->where('siswa_id',        $payload['siswa_id'])
            ->where('tahun_ajaran_id', $payload['tahun_ajaran_id'])
            ->where('mapel_id',        $payload['mapel_id'])
            ->where('kategori_id',     $payload['kategori_id'])
            ->countAllResults();

        if ($exists > 0) {
            // Ambil label untuk pesan yang informatif
            $db   = \Config\Database::connect();
            $nama = $db->table('tb_siswa')->select('full_name')->where('id_siswa', $payload['siswa_id'])->get()->getRow('full_name');
            $map  = $db->table('tb_mapel')->select('nama')->where('id_mapel', $payload['mapel_id'])->get()->getRow('nama');
            $kat  = $db->table('tb_kategori_nilai')->select('kode')->where('id_kategori', $payload['kategori_id'])->get()->getRow('kode');

            return redirect()->back()->withInput()->with(
                'sweet_error',
                'Duplikat nilai: ' . ($nama ?: 'Siswa') . ' - ' . ($map ?: 'Mapel') . ' - ' . ($kat ?: 'Kategori') . ' sudah ada.'
            );
        }

        try {
            $m->insert($payload);
            return redirect()
                ->to(base_url('operator/laporan/nilai-siswa'))
                ->with('sweet_success', 'Nilai siswa berhasil ditambahkan.');
        } catch (\Throwable $e) {
            // Tangkap kemungkinan duplicate key jika ada unique index di DB
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                return redirect()->back()->withInput()->with(
                    'sweet_error',
                    'Duplikat nilai: kombinasi siswa/TA/mapel/kategori sudah ada.'
                );
            }
            return redirect()->back()->withInput()->with('errors', ['db' => $e->getMessage()]);
        }
    }
    public function page_edit_nilai_siswa($idNilai = null)
    {
        $req = $this->request;

        // ===== Filter GET (untuk konteks kembali) =====
        $q       = trim((string)($req->getGet('q') ?? ''));            // cari nama/NISN
        $idTA    = trim((string)($req->getGet('tahunajaran') ?? ''));  // id_tahun_ajaran (opsional)
        $kodeKat = trim((string)($req->getGet('kategori') ?? ''));     // opsional: UTS/UAS
        $idMapel = trim((string)($req->getGet('mapel') ?? ''));        // opsional: id_mapel

        // ===== Validasi parameter utama =====
        if ($idNilai === null || !ctype_digit((string)$idNilai)) {
            $redirectUrl = base_url('operator/laporan/nilai-siswa') . '?' . http_build_query([
                'q'           => $q,
                'tahunajaran' => $idTA,
                'kategori'    => $kodeKat,
                'mapel'       => $idMapel,
            ]);
            return redirect()->to($redirectUrl)->with('sweet_error', 'Parameter nilai tidak valid.');
        }

        $nilaiModel = new \App\Models\NilaiSiswaModel();

        // ===== Ambil 1 data nilai (join alias untuk tampilan) =====
        $row = $nilaiModel->builder()->from('tb_nilai_siswa ns')
            ->select("
            ns.id_nilai, ns.siswa_id, ns.tahun_ajaran_id, ns.mapel_id, ns.kategori_id, ns.skor, ns.tanggal, ns.keterangan,

            s.nisn      AS siswa_nisn,
            s.full_name AS siswa_nama,
            s.gender    AS siswa_gender,

            ta.tahun    AS ta_tahun,
            ta.semester AS ta_semester,

            m.nama      AS mapel_nama,

            k.kode      AS kategori_kode,
            k.nama      AS kategori_nama
        ")
            ->join('tb_siswa s', 's.id_siswa = ns.siswa_id', 'left')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = ns.tahun_ajaran_id', 'left')
            ->join('tb_mapel m', 'm.id_mapel = ns.mapel_id', 'left')
            ->join('tb_kategori_nilai k', 'k.id_kategori = ns.kategori_id', 'left')
            ->where('ns.id_nilai', (int)$idNilai)
            ->get()->getRowArray();

        if (! $row) {
            $redirectUrl = base_url('operator/laporan/nilai-siswa') . '?' . http_build_query([
                'q'           => $q,
                'tahunajaran' => $idTA,
                'kategori'    => $kodeKat,
                'mapel'       => $idMapel,
            ]);
            return redirect()->to($redirectUrl)->with('sweet_error', 'Data nilai tidak ditemukan.');
        }

        // ====== DROPDOWN (sama seperti page_tambah...) ======
        // Siswa
        $siswaModel = new \App\Models\SiswaModel();
        $siswaQ = $siswaModel->select('id_siswa, nisn, full_name')->orderBy('full_name', 'ASC');
        if ($q !== '') {
            $siswaQ->groupStart()->like('full_name', $q)->orLike('nisn', $q)->groupEnd();
        }
        $optSiswa = $siswaQ->findAll(500);

        // Tahun Ajaran — tampilkan dan tandai yang aktif
        $taModel = new \App\Models\TahunAjaranModel();
        $optTA   = $taModel->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('is_active', 'DESC')   // aktif di atas
            ->orderBy('tahun', 'DESC')
            ->orderBy('semester', 'ASC')
            ->findAll(200);
        // === Jika ingin hanya TA aktif, ganti blok di atas dengan:
        // $optTA = $taModel->select('id_tahun_ajaran, tahun, semester, is_active')
        //     ->where('is_active', 1)
        //     ->orderBy('tahun', 'DESC')->orderBy('semester', 'ASC')
        //     ->findAll(200);

        // Mapel
        $mapelModel = new \App\Models\ModelMatPel();
        $optMapel   = $mapelModel->select('id_mapel, nama')
            ->orderBy('nama', 'ASC')->findAll(300);

        // Kategori Nilai
        $katModel    = new \App\Models\KategoriNilai();
        $optKategori = $katModel->select('id_kategori, kode, nama')
            ->orderBy('nama', 'ASC')->findAll(100);

        // allowedFields (guard)
        $allowedFields = method_exists($nilaiModel, 'getAllowedFields')
            ? $nilaiModel->getAllowedFields()
            : ['siswa_id', 'tahun_ajaran_id', 'mapel_id', 'kategori_id', 'skor', 'tanggal', 'keterangan'];

        // ===== URL kembali (bawa filter) agar UX enak =====
        $backUrl = base_url('operator/laporan/nilai-siswa') . '?' . http_build_query([
            'q'           => $q,
            'tahunajaran' => $idTA,
            'kategori'    => $kodeKat,
            'mapel'       => $idMapel,
        ]);

        return view('pages/operator/edit_laporan_nilai', [
            'title'         => 'Edit Nilai Siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Edit Nilai',
            'nav_link'      => 'Nilai Siswa',

            // data utama
            'row'           => $row,
            'id_nilai'      => (int)$idNilai,

            // filter GET (konteks)
            'q'             => $q,
            'tahunajaran'   => $idTA,
            'kategori'      => $kodeKat,
            'mapel'         => $idMapel,
            'backUrl'       => $backUrl,

            // dropdown sources
            'optSiswa'      => $optSiswa,
            'optTA'         => $optTA,       // sudah ada is_active
            'optMapel'      => $optMapel,
            'optKategori'   => $optKategori,

            // guard form
            'allowedFields' => $allowedFields,
        ]);
    }

    public function aksi_edit_nilai_siswa($idNilai = null)
    {
        // ID dari route wajib numerik
        if ($idNilai === null || !ctype_digit((string)$idNilai)) {
            return redirect()->to(base_url('operator/laporan/nilai-siswa'))
                ->with('sweet_error', 'Parameter nilai tidak valid.');
        }
        $idNilai = (int) $idNilai;

        $m   = new \App\Models\NilaiSiswaModel();
        $req = $this->request;

        // Pastikan data ada
        $current = $m->find($idNilai);
        if (! $current) {
            return redirect()->to(base_url('operator/laporan/nilai-siswa'))
                ->with('sweet_error', 'Data nilai tidak ditemukan.');
        }

        // ===== (OPSIONAL) Deteksi manipulasi hidden id_nilai =====
        $postedId = trim((string) ($req->getPost('id_nilai') ?? ''));
        if ($postedId !== '' && ctype_digit($postedId) && (int)$postedId !== $idNilai) {
            return redirect()->back()->withInput()->with(
                'sweet_error',
                'Manipulasi parameter terdeteksi: ID tidak sesuai.'
            );
        }

        // ===== Ambil filter GET untuk redirect balik =====
        $q         = trim((string)($req->getGet('q') ?? ''));
        $idTAGet   = trim((string)($req->getGet('tahunajaran') ?? ''));
        $kodeKat   = trim((string)($req->getGet('kategori') ?? ''));
        $idMapelGt = trim((string)($req->getGet('mapel') ?? ''));
        $redirectUrl = base_url('operator/laporan/nilai-siswa') . '?' . http_build_query([
            'q'           => $q,
            'tahunajaran' => $idTAGet,
            'kategori'    => $kodeKat,
            'mapel'       => $idMapelGt,
        ]);

        // ===== Input (rapikan tipe) =====
        $data = [
            'siswa_id'        => trim((string) $req->getPost('siswa_id')),
            'tahun_ajaran_id' => trim((string) $req->getPost('tahun_ajaran_id')),
            'mapel_id'        => trim((string) $req->getPost('mapel_id')),
            'kategori_id'     => trim((string) $req->getPost('kategori_id')),
            'skor'            => trim((string) $req->getPost('skor')),
            'tanggal'         => trim((string) $req->getPost('tanggal')),
            'keterangan'      => trim((string) $req->getPost('keterangan')),
        ];

        // ===== Ambil daftar Tahun Ajaran AKTIF untuk validasi =====
        $db = \Config\Database::connect();
        $activeTA = $db->table('tb_tahun_ajaran')
            ->select('id_tahun_ajaran')
            ->where('is_active', 1)
            ->get()->getResultArray();
        $activeTAIds = array_map('strval', array_column($activeTA, 'id_tahun_ajaran'));
        $taInListRule = $activeTAIds ? 'in_list[' . implode(',', $activeTAIds) . ']' : ''; // jika tidak ada TA aktif, rule ini kosong

        // ===== Validasi =====
        $rules = [
            'siswa_id' => [
                'label'  => 'Siswa',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_siswa.id_siswa]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'tahun_ajaran_id' => [
                'label'  => 'Tahun Ajaran',
                // wajib ada di tabel + (jika ada) wajib termasuk TA aktif
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_tahun_ajaran.id_tahun_ajaran]' . ($taInListRule ? ('|' . $taInListRule) : ''),
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                    'in_list'            => '{field} harus Tahun Ajaran yang aktif.',
                ],
            ],
            'mapel_id' => [
                'label'  => 'Mata Pelajaran',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_mapel.id_mapel]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'kategori_id' => [
                'label'  => 'Kategori Penilaian',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[tb_kategori_nilai.id_kategori]',
                'errors' => [
                    'required'           => '{field} wajib dipilih.',
                    'is_natural_no_zero' => '{field} tidak valid.',
                    'is_not_unique'      => '{field} tidak ditemukan.',
                ],
            ],
            'skor' => [
                'label'  => 'Skor',
                'rules'  => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
                'errors' => [
                    'required'              => '{field} wajib diisi.',
                    'decimal'               => '{field} harus berupa angka/desimal.',
                    'greater_than_equal_to' => '{field} minimal {param}.',
                    'less_than_equal_to'    => '{field} maksimal {param}.',
                ],
            ],
            'tanggal' => [
                'label'  => 'Tanggal',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'valid_date' => '{field} tidak valid (format: YYYY-MM-DD).',
                ],
            ],
            'keterangan' => [
                'label'  => 'Keterangan',
                'rules'  => 'permit_empty|max_length[200]',
                'errors' => [
                    'max_length' => '{field} maksimal {param} karakter.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Whitelist fields + pastikan tidak ada id_nilai
        $payload = array_intersect_key($data, array_flip($m->getAllowedFields()));
        unset($payload['id_nilai']);

        // (opsional) normalisasi tipe numerik
        $payload['siswa_id']        = (int)$payload['siswa_id'];
        $payload['tahun_ajaran_id'] = (int)$payload['tahun_ajaran_id'];
        $payload['mapel_id']        = (int)$payload['mapel_id'];
        $payload['kategori_id']     = (int)$payload['kategori_id'];

        // ===== Cegah duplikasi kombinasi kunci (kecuali record ini) =====
        $exists = (clone $m)
            ->where('siswa_id',        $payload['siswa_id'])
            ->where('tahun_ajaran_id', $payload['tahun_ajaran_id'])
            ->where('mapel_id',        $payload['mapel_id'])
            ->where('kategori_id',     $payload['kategori_id'])
            ->where('id_nilai !=',     $idNilai)
            ->countAllResults();

        if ($exists > 0) {
            $nama = $db->table('tb_siswa')->select('full_name')->where('id_siswa', $payload['siswa_id'])->get()->getRow('full_name');
            $map  = $db->table('tb_mapel')->select('nama')->where('id_mapel', $payload['mapel_id'])->get()->getRow('nama');
            $kat  = $db->table('tb_kategori_nilai')->select('kode')->where('id_kategori', $payload['kategori_id'])->get()->getRow('kode');

            return redirect()->back()->withInput()->with(
                'sweet_error',
                'Duplikat nilai: ' . ($nama ?: 'Siswa') . ' - ' . ($map ?: 'Mapel') . ' - ' . ($kat ?: 'Kategori') . ' sudah ada.'
            );
        }

        try {
            $m->update($idNilai, $payload); // gunakan ID dari route, bukan dari POST
            return redirect()->to($redirectUrl)->with('sweet_success', 'Nilai siswa berhasil diperbarui.');
        } catch (\Throwable $e) {
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                return redirect()->back()->withInput()->with(
                    'sweet_error',
                    'Duplikat nilai: kombinasi siswa/TA/mapel/kategori sudah ada.'
                );
            }
            return redirect()->back()->withInput()->with('errors', ['db' => $e->getMessage()]);
        }
    }




    public function aksi_delete_nilai_siswa($idOrNisn = null)
    {
        $req = $this->request;

        // ---- Ambil filter untuk dibawa saat redirect ----
        $q           = trim((string)($req->getGet('q') ?? ''));
        $idTA        = trim((string)($req->getGet('tahunajaran') ?? '')); // id_tahun_ajaran (opsional)
        $kodeKat     = trim((string)($req->getGet('kategori') ?? ''));   // kode kategori: UTS/UAS (opsional)
        $idMapel     = trim((string)($req->getGet('mapel') ?? ''));      // id_mapel (opsional)

        $redirectUrl = base_url('operator/laporan/nilai-siswa') . '?' . http_build_query([
            'q'           => $q,
            'tahunajaran' => $idTA,
            'kategori'    => $kodeKat,
            'mapel'       => $idMapel,
        ]);

        if ($idOrNisn === null || $idOrNisn === '') {
            return redirect()->to($redirectUrl)->with('sweet_error', 'Parameter penghapusan tidak valid.');
        }

        $db = \Config\Database::connect();
        $mNilai = new \App\Models\NilaiSiswaModel();

        try {
            // ------------------------------
            // MODE 1: id_nilai (numerik)
            // ------------------------------
            if (ctype_digit((string)$idOrNisn)) {
                $idNilai = (int)$idOrNisn;

                // Pastikan ada datanya
                $exist = $mNilai->where('id_nilai', $idNilai)->first();
                if (! $exist) {
                    return redirect()->to($redirectUrl)->with('sweet_error', 'Data nilai tidak ditemukan atau sudah dihapus.');
                }

                $mNilai->where('id_nilai', $idNilai)->delete();
                $affected = $db->affectedRows();

                if ($affected > 0) {
                    return redirect()->to($redirectUrl)->with('sweet_success', '1 data nilai berhasil dihapus.');
                }
                return redirect()->to($redirectUrl)->with('sweet_error', 'Gagal menghapus data nilai.');
            }

            // ---------------------------------
            // MODE 2: NISN (string) + filter
            // ---------------------------------
            $nisn = trim((string)$idOrNisn);
            if ($nisn === '') {
                return redirect()->to($redirectUrl)->with('sweet_error', 'NISN tidak valid.');
            }

            // Ambil id_siswa dari NISN
            $siswaIds = $db->table('tb_siswa')->select('id_siswa')->where('nisn', $nisn)->get()->getResultArray();
            if (empty($siswaIds)) {
                return redirect()->to($redirectUrl)->with('sweet_error', 'Siswa dengan NISN tersebut tidak ditemukan.');
            }
            $siswaIds = array_map(fn($r) => (int)$r['id_siswa'], $siswaIds);

            // Opsional: map kode kategori (UTS/UAS) -> id_kategori
            $kategoriId = null;
            if ($kodeKat !== '') {
                $katRow = $db->table('tb_kategori_nilai')->select('id_kategori')->where('kode', strtoupper($kodeKat))->get()->getRowArray();
                if (! $katRow) {
                    return redirect()->to($redirectUrl)->with('sweet_error', 'Kategori penilaian tidak dikenali.');
                }
                $kategoriId = (int)$katRow['id_kategori'];
            }

            // Build where delete
            $builder = $db->table('tb_nilai_siswa');

            // Wajib: siswa_id ∈ (hasil dari NISN)
            $builder->whereIn('siswa_id', $siswaIds);

            // Opsional: filter tahun ajaran
            if ($idTA !== '' && ctype_digit($idTA)) {
                $builder->where('tahun_ajaran_id', (int)$idTA);
            }

            // Opsional: filter kategori (pakai id_kategori hasil mapping)
            if ($kategoriId !== null) {
                $builder->where('kategori_id', $kategoriId);
            }

            // Opsional: filter mapel
            if ($idMapel !== '' && ctype_digit($idMapel)) {
                $builder->where('mapel_id', (int)$idMapel);
            }

            // Eksekusi hapus
            $builder->delete();
            $affected = $db->affectedRows();

            if ($affected > 0) {
                return redirect()->to($redirectUrl)->with('sweet_success', $affected . ' data nilai berhasil dihapus.');
            }
            return redirect()->to($redirectUrl)->with('sweet_error', 'Tidak ada data yang cocok untuk dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to($redirectUrl)->with('sweet_error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    // kategori
    public function page_tambah_kategori()
    {
        $req   = $this->request;

        // ==== GET filters ====
        $q     = trim((string)($req->getGet('q') ?? ''));              // cari kode/nama
        $wajib = trim((string)($req->getGet('wajib') ?? ''));          // '' | '0' | '1'
        $bmin  = trim((string)($req->getGet('bmin') ?? ''));           // bobot min
        $bmax  = trim((string)($req->getGet('bmax') ?? ''));           // bobot max
        $sort  = strtolower((string)($req->getGet('sort') ?? 'kode')); // kode|nama|bobot|created_at
        $dir   = strtolower((string)($req->getGet('dir')  ?? 'asc'));  // asc|desc

        // Normalisasi sort & dir
        $allowedSorts = ['kode', 'nama', 'bobot', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'kode';
        $dir = $dir === 'desc' ? 'DESC' : 'ASC';

        $katModel = new \App\Models\KategoriNilai();

        // Penting: JANGAN tambah ->from() lagi agar tidak double FROM (duplikasi baris)
        $builder = $katModel->builder()
            ->select('id_kategori, kode, nama, bobot, is_wajib, created_at, updated_at');

        // Filter q: kode/nama
        if ($q !== '') {
            $builder->groupStart()
                ->like('kode', $q)
                ->orLike('nama', $q)
                ->groupEnd();
        }

        // Filter wajib
        if ($wajib === '0' || $wajib === '1') {
            $builder->where('is_wajib', (int)$wajib);
        }

        // Filter bobot min/max
        if ($bmin !== '' && is_numeric($bmin)) $builder->where('bobot >=', (float)$bmin);
        if ($bmax !== '' && is_numeric($bmax)) $builder->where('bobot <=', (float)$bmax);

        // Urutkan
        $builder->orderBy($sort, $dir);
        if ($sort !== 'kode') $builder->orderBy('kode', 'ASC'); // stabil

        $rows = $builder->get()->getResultArray();

        // Ringkasan
        $totalKategori = count($rows);
        $totalWajib = 0;
        foreach ($rows as $r) {
            if ((int)($r['is_wajib'] ?? 0) === 1) $totalWajib++;
        }

        // allowedFields → guard form
        $allowedFields = method_exists($katModel, 'getAllowedFields')
            ? $katModel->getAllowedFields()
            : ['kode', 'nama', 'bobot', 'is_wajib'];

        return view('pages/operator/data-kategori', [
            'title'         => 'Kategori Penilaian | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Kategori Penilaian',
            'nav_link'      => 'Kategori Nilai',

            // data
            'd_kategori'    => $rows,

            // sticky filters
            'q'             => $q,
            'wajib'         => $wajib,
            'bmin'          => $bmin,
            'bmax'          => $bmax,
            'sort'          => $sort,
            'dir'           => strtolower($dir),

            // ringkasan
            'totalKategori' => $totalKategori,
            'totalWajib'    => $totalWajib,

            // guard form
            'allowedFields' => $allowedFields,
        ]);
    }


    public function aksi_tambah_kategori()
    {
        $katModel = new KategoriNilai();

        // normalisasi is_wajib (hidden 0 + checkbox 1)
        $isWajib = $this->request->getPost('is_wajib') === '1' ? 1 : 0;

        $data = [
            'kode'     => trim((string)$this->request->getPost('kode')),
            'nama'     => trim((string)$this->request->getPost('nama')),
            'bobot'    => (string)$this->request->getPost('bobot'), // validasi di rules
            'is_wajib' => $isWajib,
        ];

        $rules = [
            'kode' => [
                'label'  => 'Kode',
                'rules'  => 'required|alpha_numeric_punct|min_length[1]|max_length[20]|is_unique[tb_kategori_nilai.kode]',
                'errors' => [
                    'required'             => '{field} wajib diisi.',
                    'alpha_numeric_punct'  => '{field} hanya boleh berisi huruf, angka, spasi, dan tanda baca umum.',
                    'min_length'           => '{field} minimal {param} karakter.',
                    'max_length'           => '{field} maksimal {param} karakter.',
                    'is_unique'            => '{field} sudah digunakan. Gunakan kode lain.',
                ],
            ],
            'nama' => [
                'label'  => 'Nama',
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'max_length' => '{field} maksimal {param} karakter.',
                ],
            ],
            'bobot' => [
                'label'  => 'Bobot',
                'rules'  => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
                'errors' => [
                    'required'                 => '{field} wajib diisi.',
                    'decimal'                  => '{field} harus berupa angka desimal yang valid.',
                    'greater_than_equal_to'    => '{field} minimal {param}.',
                    'less_than_equal_to'       => '{field} maksimal {param}.',
                ],
            ],
            'is_wajib' => [
                'label'  => 'Status Wajib',
                'rules'  => 'permit_empty|in_list[0,1]',
                'errors' => [
                    'in_list' => '{field} hanya boleh bernilai 0 (Opsional) atau 1 (Wajib).',
                ],
            ],
        ];


        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $katModel->insert($data);
            return redirect()
                ->to(base_url('operator/kategori/tambah'))
                ->with('sweet_success', 'Kategori berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('errors', ['db' => $e->getMessage()]);
        }
    }

    /**
     * DELETE
     * GET: operator/kategori/delete/{id}
     */

    public function aksi_delete($id = null)
    {
        $katModel = new KategoriNilai();
        $id = (int)$id;

        if (!$katModel->find($id)) {
            return redirect()->to(base_url('operator/kategori/tambah'))->with('errors', ['notfound' => 'Data tidak ditemukan.']);
        }

        try {
            $katModel->delete($id);
            return redirect()
                ->to(base_url('operator/kategori/tambah'))
                ->with('sweet_success', 'Kategori berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()
                ->to(base_url('operator/kategori/tambah'))
                ->with('sweet_errors', ['db' => $e->getMessage()]);
        }
    }
}
