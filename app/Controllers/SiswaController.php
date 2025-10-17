<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruTahunanModel;
use App\Models\ModelGuru;
use App\Models\ModelMatPel;
use App\Models\NilaiSiswaModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class SiswaController extends BaseController
{
    protected $UserModel;
    protected $SiswaModel;
    protected $NilaiSiswaTahunan;
    protected $MapelModel;
    protected $GuruModel;
    protected $TahunAjaran;
    protected $GuruTahunanModel;
    public function __construct()
    {
        $this->UserModel = new UserModel();
        $this->SiswaModel = new SiswaModel();
        $this->NilaiSiswaTahunan = new NilaiSiswaModel();
        $this->MapelModel = new ModelMatPel();
        $this->GuruModel = new ModelGuru();
        $this->TahunAjaran = new TahunAjaranModel();
        $this->GuruTahunanModel = new GuruTahunanModel();
    }
    public function index()
    {
        $data = [
            'title'    => 'Siswa | Welcome to SDN Talun 2 Kota Serang',
            'nav_link' => 'Dashboard',
        ];

        $db = \Config\Database::connect();

        // ===== KPI: jumlah guru aktif (tanpa model guru, cukup dari tb_users)
        $data['guruCount'] = (int) (clone $this->UserModel)
            ->where('role', 'guru')
            ->where('is_active', 1)
            ->countAllResults();

        // ===== Identifikasi siswa yang login (dari session id_user -> tb_siswa.id_siswa)
        $userId = (int) (session()->get('id_user') ?? 0);
        $siswaId = null;

        if ($userId > 0) {
            $rowSiswa = (clone $this->SiswaModel)
                ->select('id_siswa')
                ->where('user_id', $userId)
                ->first();
            $siswaId = isset($rowSiswa['id_siswa']) ? (int)$rowSiswa['id_siswa'] : null;
        }

        // ===== Siapkan data chart
        $mapelLabels = [];
        $mapelScores = [];
        $trendLabels = [];
        $trendScores = [];

        if (!empty($siswaId)) {
            // ---- Chart 1: Nilai per Mapel (rata-rata skor per mapel untuk siswa ini)
            $rowsMapel = $db->table('tb_nilai_siswa ns')
                ->select('m.nama AS mapel, AVG(ns.skor) AS avg_skor', false)
                ->join('tb_mapel m', 'm.id_mapel = ns.mapel_id', 'left')
                ->where('ns.siswa_id', $siswaId)
                ->groupBy('ns.mapel_id, m.nama')
                ->orderBy('m.nama', 'ASC')
                ->get()->getResultArray();

            foreach ($rowsMapel as $r) {
                $mapelLabels[] = (string)($r['mapel'] ?? 'Mapel');
                $mapelScores[] = round((float)($r['avg_skor'] ?? 0), 2);
            }

            // ---- Chart 2: Tren Nilai (rata-rata per tanggal, 12 titik terakhir)
            $rowsTrend = $db->table('tb_nilai_siswa')
                ->select('tanggal, AVG(skor) AS avg_skor', false)
                ->where('siswa_id', $siswaId)
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'ASC')
                ->limit(12)
                ->get()->getResultArray();

            foreach ($rowsTrend as $r) {
                $trendLabels[] = (string)($r['tanggal'] ?? '');
                $trendScores[] = round((float)($r['avg_skor'] ?? 0), 2);
            }
        }

        $data['mapelLabels'] = $mapelLabels;   // ex: ["IPA","IPS","Matematika"]
        $data['mapelScores'] = $mapelScores;   // ex: [88.5, 92, 79]
        $data['trendLabels'] = $trendLabels;   // ex: ["2025-01-10","2025-02-08",...]
        $data['trendScores'] = $trendScores;   // ex: [78, 82, 90, ...]

        return view('pages/siswa/dashboard_siswa', $data);
    }
    public function data_diri()
    {
        // Pastikan sudah login
        $userId = (int) (session()->get('id_user') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Silakan login terlebih dahulu.');
        }

        // Ambil profil siswa + user + kelas + jumlah laporan_tahun
        $me = (clone $this->SiswaModel)
            ->select("
            tb_siswa.*,
            u.username   AS user_name,
            u.email      AS user_email,
            u.is_active  AS user_active,
            k.nama_kelas AS kelas_nama,
            k.tingkat    AS kelas_tingkat,
            (
                SELECT COUNT(1)
                FROM tb_siswa_tahun st
                WHERE st.siswa_id = tb_siswa.id_siswa
            ) AS laporan_count
        ", false)
            ->join('tb_users u',  'u.id_user  = tb_siswa.user_id',  'left')
            ->join('tb_kelas k',  'k.id_kelas = tb_siswa.kelas_id', 'left')
            ->where('tb_siswa.user_id', $userId)
            ->first();

        if (!$me) {
            return redirect()->to(base_url('siswa/dashboard'))
                ->with('sweet_error', 'Data siswa belum terhubung ke akun ini. Silakan lengkapi profil.');
        }

        // Rekap nilai untuk chart & KPI
        $db = \Config\Database::connect();

        // Nilai per mapel (avg)
        $rowsMapel = $db->table('tb_nilai_siswa ns')
            ->select('m.nama AS mapel, AVG(ns.skor) AS avg_skor', false)
            ->join('tb_mapel m', 'm.id_mapel = ns.mapel_id', 'left')
            ->where('ns.siswa_id', (int)$me['id_siswa'])
            ->groupBy('ns.mapel_id, m.nama')
            ->orderBy('m.nama', 'ASC')
            ->get()->getResultArray();

        $mapelLabels = [];
        $mapelScores = [];
        foreach ($rowsMapel as $r) {
            $mapelLabels[] = (string)($r['mapel'] ?? 'Mapel');
            $mapelScores[] = round((float)($r['avg_skor'] ?? 0), 2);
        }

        // KPI total & rata-rata nilai
        $rekap = $db->table('tb_nilai_siswa')
            ->select('COUNT(*) AS total_nilai, AVG(skor) AS avg_nilai', false)
            ->where('siswa_id', (int)$me['id_siswa'])
            ->get()->getRowArray();

        // ===== SUSUN SEKALI JADI DALAM $data =====
        $data = [
            'title'        => 'Siswa | Data Diri',
            'nav_link'     => 'Data Diri',
            'me'           => $me,
            'mapelLabels'  => $mapelLabels,                            // contoh: ["IPA","IPS","Matematika"]
            'mapelScores'  => $mapelScores,                            // contoh: [88.5, 92, 79]
            'totalNilai'   => (int)($rekap['total_nilai'] ?? 0),
            'avgNilai'     => $rekap['avg_nilai'] !== null ? round((float)$rekap['avg_nilai'], 2) : null,
        ];

        return view('pages/siswa/data-diri', $data);
    }
    public function data_guru()
    {
        $req     = $this->request;
        $q       = trim((string)($req->getGet('q') ?? ''));
        $gender  = strtoupper(trim((string)($req->getGet('gender') ?? ''))); // 'L' / 'P' / ''
        $idTA    = (int)($req->getGet('tahunajaran') ?? 0);                  // opsional

        // Pakai model yang sudah di-construct di controller
        $guruModel = $this->GuruModel;

        // Fallback: kalau belum dijadikan properti di controller, bikin instans lokal
        $taModel = property_exists($this, 'TahunAjaran')
            ? $this->TahunAjaran
            : new \App\Models\TahunAjaranModel();

        $gtModel = property_exists($this, 'GuruTahunanModel')
            ? $this->GuruTahunanModel
            : new \App\Models\GuruTahunanModel();

        // 1) Query guru + filter q / gender (kalau ada)
        $gq = (clone $guruModel)
            ->select('id_guru, nip, nama_lengkap, jenis_kelamin, status_active')
            ->orderBy('nama_lengkap', 'ASC');

        if ($q !== '') {
            $gq->groupStart()
                ->like('nama_lengkap', $q)
                ->orLike('nip', $q)
                ->groupEnd();
        }

        if ($gender !== '') {
            if (in_array($gender, ['L', 'P'], true)) {
                $gq->where('jenis_kelamin', $gender);
            } else {
                $gq->like('jenis_kelamin', $gender);
            }
        }

        $rows = $gq->findAll();

        // 2) Tentukan Tahun Ajaran target
        $taTargetId = 0;
        if ($idTA > 0) {
            $taTargetId = (int)($taModel->select('id_tahun_ajaran')
                ->where('id_tahun_ajaran', $idTA)
                ->get()->getRow('id_tahun_ajaran'));
        } else {
            // cari TA aktif; kalau skemamu pakai 'aktif' atau 1, handle keduanya
            $rowTa = $taModel->select('id_tahun_ajaran')
                ->groupStart()
                ->where('is_active', 1)
                ->orWhere('is_active', 'aktif')
                ->groupEnd()
                ->orderBy('tahun', 'DESC')
                ->orderBy('semester', 'DESC')
                ->first();
            $taTargetId = (int)($rowTa['id_tahun_ajaran'] ?? 0);
        }

        // 3) Ambil guru_id yang sudah punya entri di tb_guru_tahun untuk TA target
        $exists = [];
        if ($taTargetId > 0) {
            $existsRows = $gtModel->select('guru_id')
                ->where('tahun_ajaran_id', $taTargetId)
                ->groupBy('guru_id')
                ->findAll();
            foreach ($existsRows as $er) {
                $exists[(int)$er['guru_id']] = true;
            }
        }

        // 4) Sisipkan flag 'has_laporan_ta' ke setiap guru
        $d_guru = array_map(function (array $g) use ($exists) {
            $gid = (int)$g['id_guru'];
            $g['has_laporan_ta'] = !empty($exists[$gid]); // true jika sudah ada entri pada TA target
            return $g;
        }, $rows);

        // 5) Susun data untuk view
        $data = [
            'title'        => 'Data Guru | SDN Talun 2 Kota Serang',
            'sub_judul'    => 'Data Guru',
            'nav_link'     => 'Data Guru',
            'd_guru'       => $d_guru,
            'q'            => $q,
            'gender'       => $gender,
            'tahunajaran'  => $taTargetId,
            'totalGuru'    => count($d_guru),
        ];

        return view('pages/siswa/data-guru', $data);
    }
    public function nilai_siswa()
    {
        // ===== Meta halaman
        $data = [
            'title'    => 'Siswa | Laporan Nilai Saya',
            'nav_link' => 'Data Nilai Siswa',
        ];

        // ===== Pastikan login & role siswa
        $userId = (int) (session()->get('id_user') ?? 0);
        $role   = (string) (session()->get('role') ?? '');
        if ($userId <= 0 || $role !== 'siswa') {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Silakan login sebagai siswa untuk melihat laporan nilai.');
        }

        // ===== Dapatkan data siswa (me) dari user_id
        $me = (clone $this->SiswaModel)
            ->select("
            tb_siswa.*,
            u.username   AS user_name,
            u.email      AS user_email,
            u.is_active  AS user_active,
            k.nama_kelas AS kelas_nama,
            k.tingkat    AS kelas_tingkat,
            (
                SELECT COUNT(1)
                FROM tb_siswa_tahun st
                WHERE st.siswa_id = tb_siswa.id_siswa
            ) AS laporan_count
        ")
            ->join('tb_users u',  'u.id_user  = tb_siswa.user_id',  'left')
            ->join('tb_kelas k',  'k.id_kelas = tb_siswa.kelas_id', 'left')
            ->where('tb_siswa.user_id', $userId)
            ->first();

        if (! $me) {
            return redirect()->to(base_url('siswa/data-diri'))
                ->with('sweet_error', 'Profil siswa belum terhubung. Lengkapi data diri terlebih dahulu.');
        }

        $data['me'] = $me;
        $siswaId    = (int) $me['id_siswa'];

        // ===== Filter GET (opsional) — tetap untuk siswa ini saja
        $req     = $this->request;
        $idTA    = trim((string) ($req->getGet('tahunajaran') ?? '')); // id_tahun_ajaran
        $kodeKat = trim((string) ($req->getGet('kategori') ?? ''));   // UTS/UAS (kode)
        $idMapel = trim((string) ($req->getGet('mapel') ?? ''));      // id_mapel

        // ===== Ambil daftar nilai milik siswa ini
        // gunakan query builder mentah agar hasil PASTI array (getResultArray)
        $db = \Config\Database::connect();

        $QB = $db->table('tb_nilai_siswa ns')
            ->select("
            ns.id_nilai, ns.siswa_id, ns.tahun_ajaran_id, ns.mapel_id, ns.kategori_id,
            ns.skor, ns.tanggal, ns.keterangan,

            ta.tahun AS tahun_ajaran, ta.semester,
            m.nama   AS mapel_nama,
            k.kode   AS kategori_kode, k.nama AS kategori_nama
        ", false)
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = ns.tahun_ajaran_id', 'left')
            ->join('tb_mapel m',         'm.id_mapel         = ns.mapel_id',       'left')
            ->join('tb_kategori_nilai k', 'k.id_kategori      = ns.kategori_id',    'left')
            ->where('ns.siswa_id', $siswaId);

        if ($idTA !== '' && ctype_digit($idTA)) {
            $QB->where('ns.tahun_ajaran_id', (int) $idTA);
        }
        if ($kodeKat !== '') {
            $QB->where('k.kode', strtoupper($kodeKat));
        }
        if ($idMapel !== '' && ctype_digit($idMapel)) {
            $QB->where('ns.mapel_id', (int) $idMapel);
        }

        $rows = $QB->orderBy('ta.tahun', 'DESC')
            ->orderBy('ta.semester', 'DESC')
            ->orderBy('ns.tanggal', 'DESC')
            ->get()->getResultArray(); // <- PASTI array

        // ===== KPI ringkas
        $totalNilai = count($rows);
        $avgNilai   = null;
        if ($totalNilai > 0) {
            $sum = 0;
            foreach ($rows as $r) {
                $sum += (float)($r['skor'] ?? 0);
            }
            $avgNilai = round($sum / $totalNilai, 2);
        }
        $data['totalNilai'] = $totalNilai;
        $data['avgNilai']   = $avgNilai;

        // ===== Data untuk chart: rata-rata skor per mapel (tetap milik siswa ini + hormati filter TA/kategori)
        $QB2 = $db->table('tb_nilai_siswa ns')
            ->select('m.nama AS mapel, AVG(ns.skor) AS avg_skor', false)
            ->join('tb_mapel m', 'm.id_mapel = ns.mapel_id', 'left')
            ->where('ns.siswa_id', $siswaId);

        if ($idTA !== '' && ctype_digit($idTA)) {
            $QB2->where('ns.tahun_ajaran_id', (int) $idTA);
        }
        if ($kodeKat !== '') {
            $QB2->join('tb_kategori_nilai k', 'k.id_kategori = ns.kategori_id', 'left')
                ->where('k.kode', strtoupper($kodeKat));
        }

        $rowsMapel = $QB2->groupBy('ns.mapel_id, m.nama')->orderBy('m.nama', 'ASC')->get()->getResultArray();

        $mapelLabels = [];
        $mapelScores = [];
        foreach ($rowsMapel as $r) {
            $mapelLabels[] = (string)($r['mapel'] ?? 'Mapel');
            $mapelScores[] = round((float)($r['avg_skor'] ?? 0), 2);
        }
        $data['mapelLabels'] = $mapelLabels;
        $data['mapelScores'] = $mapelScores;

        // ===== (Opsional) data dropdown untuk filter di view siswa
        $data['listTA'] = $db->table('tb_tahun_ajaran')
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->get()->getResultArray();

        $data['listMapel'] = $db->table('tb_mapel m')
            ->select('m.id_mapel, m.nama')
            ->join('tb_nilai_siswa ns', 'ns.mapel_id = m.id_mapel', 'inner')
            ->where('ns.siswa_id', $siswaId)
            ->groupBy('m.id_mapel, m.nama')
            ->orderBy('m.nama', 'ASC')
            ->get()->getResultArray();

        $data['listKategori'] = $db->table('tb_kategori_nilai')
            ->select('id_kategori, kode, nama')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        // ===== kirim ulang filter ke view
        $data['tahunajaran'] = $idTA;
        $data['kategori']    = $kodeKat;
        $data['mapel']       = $idMapel;

        // ===== Data utama untuk tabel
        $data['d_nilai'] = $rows;

        // ===== Render view
        return view('pages/siswa/nilai-siswa', $data);
    }


    public function profile()
    {

        // ==== Pastikan user login ====
        $userId = (int) (session()->get('id_user') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Silakan login terlebih dahulu.');
        }

        // ==== Ambil data akun (UserModel) ====
        $user = (clone $this->UserModel)
            ->select('id_user, username, email, is_active, updated_at')
            ->where('id_user', $userId)
            ->first();

        if (! $user) {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Akun tidak ditemukan.');
        }

        // ==== Ambil data siswa (SiswaModel) ====
        $siswa = (clone $this->SiswaModel)
            ->select("
            tb_siswa.*,
            k.nama_kelas  AS kelas_nama,
            k.tingkat     AS kelas_tingkat
        ")
            ->join('tb_kelas k', 'k.id_kelas = tb_siswa.kelas_id', 'left')
            ->where('tb_siswa.user_id', $userId)
            ->first();

        // Fallback minimal agar view tidak error
        if (! $siswa) {
            $siswa = [
                'full_name'     => $user['username'] ?? '',
                'nisn'          => '',
                'gender'        => '',
                'phone'         => '',
                'birth_place'   => '',
                'birth_date'    => '',
                'address'       => '',
                'photo'         => '',
                'updated_at'    => null,
                'kelas_nama'    => null,
                'kelas_tingkat' => null,
            ];
        }

        // ==== VALIDASI untuk view ====
        $validation = \Config\Services::validation();

        // Ambil validasi dari session jika ada (hasil dari redirect()->withInput())
        $validation->withRequest($this->request)->run(); // Ini WAJIB agar old input dan error kebaca

        if (session()->has('_ci_validation_errors')) {
            $errors = session('_ci_validation_errors');
            foreach ($errors as $field => $message) {
                $validation->setError($field, $message);
            }
        }


        // Validasi email (jika data DB error)
        $validation->setRules(['email' => 'permit_empty|valid_email']);
        if (! $validation->run(['email' => $user['email'] ?? ''])) {
            $user['email'] = '';
        }

        // ==== Normalisasi helper utk view ====
        // Avatar: SiswaModel->photo → UserModel->avatar_url → default
        $fotoSiswa   = trim((string)($siswa['photo'] ?? ''));
        $avatarUser  = trim((string)($user['avatar_url'] ?? ''));
        if ($fotoSiswa !== '') {
            $avatar = base_url('assets/img/uploads/' . $fotoSiswa);
        } elseif ($avatarUser !== '') {
            $avatar = $avatarUser;
        } else {
            $avatar = base_url('assets/img/avatar-default.png');
        }

        // Gender label
        $gRaw         = strtoupper((string)($siswa['gender'] ?? ''));
        $genderLabel  = $gRaw === 'L' ? 'Laki-laki' : ($gRaw === 'P' ? 'Perempuan' : '—');

        // Status aktif
        $isActive = ((int)($user['is_active'] ?? 0) === 1);

        // ==== Kirim ke view ====
        $data = [
            'title'       => 'Siswa | Profil Saya',
            'nav_link'    => 'Profile',
            'user'        => $user,
            'siswa'       => $siswa,
            'avatar'      => $avatar,
            'genderLabel' => $genderLabel,
            'isActive'    => $isActive,
            'v'           => $validation, // instance validator
        ];

        return view('pages/siswa/profile', $data);
    }

    public function updateUsername()
    {
        // Wajib login
        $userId = (int) (session()->get('id_user') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Silakan login terlebih dahulu.');
        }

        $user = $this->UserModel->find($userId);
        if (! $user) {
            return redirect()->back()->with('sweet_error', 'Akun tidak ditemukan.');
        }

        $username = trim((string) $this->request->getPost('username'));
        $validator = \Config\Services::validation();

        // === 1) Manual check: spasi dilarang
        if (preg_match('/\s/', $username)) {
            $validator->setError('username', 'Username tidak boleh mengandung spasi.');
            return redirect()->back()->withInput()->with('validation', $validator);
        }

        // === 2) Manual check: reserved name "tb_users" (case-insensitive)
        if (strcasecmp($username, 'tb_users') === 0) {
            $validator->setError('username', 'Username tidak boleh menggunakan nama "tb_users".');
            return redirect()->back()->withInput()->with('validation', $validator);
        }

        // === 3) Rules CI4 (unik + pola + panjang)
        $rules = [
            'username' => [
                'label'  => 'Username',
                // Samakan dengan hint: 4–24
                'rules'  => "required|regex_match[/^[A-Za-z0-9._:@#-]+$/]|min_length[4]|max_length[24]|is_unique[tb_users.username,id_user,{$userId}]",
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'regex_match' => '{field} hanya boleh berisi huruf, angka, dan karakter (.,:_-@#), tanpa spasi.',
                    'min_length'  => '{field} minimal harus {param} karakter.',
                    'max_length'  => '{field} maksimal {param} karakter.',
                    'is_unique'   => '{field} sudah digunakan oleh pengguna lain.',
                ]
            ],
        ];

        if (! $this->validate($rules)) {
            // Kirimkan objek validator ke view agar $v->hasError(...) berfungsi
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        try {
            $this->UserModel->update($userId, [
                'username'   => $username,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            session()->regenerate(); // good practice

            return redirect()->to(base_url('siswa/profile'))
                ->with('sweet_success', 'Username berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('sweet_error', 'Gagal memperbarui username. Coba lagi.');
        }
    }


    public function updatePassword()
    {
        // Wajib login
        $userId = (int) (session()->get('id_user') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Silakan login terlebih dahulu.');
        }

        $user = $this->UserModel->find($userId);
        if (! $user) {
            return redirect()->back()->with('sweet_error', 'Akun tidak ditemukan.');
        }

        // Ambil input password
        $oldPassword = (string) $this->request->getPost('old_password');
        $newPassword = (string) $this->request->getPost('password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        // Validasi
        $rules = [
            'old_password' => [
                'label' => 'Password Lama',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                ]
            ],
            'password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[8]|max_length[255]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'min_length'  => '{field} minimal harus {param} karakter.',
                    'max_length'  => '{field} maksimal {param} karakter.',
                ]
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'matches'  => '{field} harus sama dengan Password Baru.',
                ]
            ],
        ];


        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('sweet_error', 'Periksa kembali input Anda.');
        }

        // Cek kecocokan password lama
        if (! password_verify($oldPassword, $user['password'])) {
            return redirect()->back()->withInput()->with('sweet_error', 'Password lama salah.');
        }

        try {
            $this->UserModel->update($userId, [
                'password'   => password_hash($newPassword, PASSWORD_ARGON2ID),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Regenerasi session ID
            session()->regenerate();

            return redirect()->to(base_url('siswa/profile'))
                ->with('sweet_success', 'Password berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('sweet_error', 'Gagal memperbarui password. Coba lagi.');
        }
    }
}
