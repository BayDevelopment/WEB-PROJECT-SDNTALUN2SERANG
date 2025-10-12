<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruMatpel;
use App\Models\GuruTahunanModel;
use App\Models\KelasModel;
use App\Models\ModelGuru;
use App\Models\ModelMatPel;
use App\Models\SiswaModel;
use App\Models\SiswaTahunanModel;
use App\Models\TahunAjaranModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class GuruController extends BaseController
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
            'title'    => 'Guru | SDN Talun 2 Kota Serang',
            'nav_link' => 'Dashboard',
        ];

        $db = \Config\Database::connect();

        // ===== 0) Ambil user & guru login (WAJIB role=Guru) =====
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }

        $user = $db->table('tb_users')
            ->select('id_user, role, is_active')
            ->where('id_user', $userId)
            ->get()->getRowArray();

        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('dashboard'))
                ->with('sweet_error', 'Akses dashboard guru ditolak.');
        }

        $guru = $db->table('tb_guru')
            ->select('id_guru, user_id')
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (! $guru) {
            return redirect()->to(base_url('dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ===== 1) Tahun Ajaran aktif/terbaru =====
        $taAktif = $this->TahunAjaran
            ->groupStart()->where('is_active', 'aktif')->orWhere('is_active', 1)->groupEnd()
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->first();

        if (! $taAktif) {
            $taAktif = $this->TahunAjaran
                ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
                ->first();
        }

        $taId = (int) ($taAktif['id_tahun_ajaran'] ?? 0);
        $data['ta_aktif'] = $taAktif;

        // ===== helper: ambil nama kolom status aktif yang tersedia =====
        $pickStatusCol = function (string $table, array $candidates = ['status_active', 'is_active', 'status']) use ($db) {
            try {
                $fields = $db->getFieldNames($table); // CI4
                foreach ($candidates as $c) {
                    if (in_array($c, $fields, true)) return $c;
                }
            } catch (\Throwable $e) {
            }
            return null;
        };

        // ===== 2) Kelas yang diajar guru login =====
        // 2a) Kelas sebagai Wali → deteksi nama kolom wali secara dinamis
        $kelasWali = [];
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = [
                'wali_guru_id',
                'wali_id',
                'id_guru_wali',
                'guru_wali_id',
                'id_wali_guru',
                // jika skema menyimpan langsung id_guru di tb_kelas:
                'id_guru',
                'guru_id',
                // fallback nama lain yang mungkin ada:
                'wali_kelas_id',
                'id_wali_kelas',
                'user_id_wali',
                'user_wali_id'
            ];
            $waliCol = null;
            foreach ($waliCandidates as $cand) {
                if (in_array($cand, $kelasFields, true)) {
                    $waliCol = $cand;
                    break;
                }
            }
            if ($waliCol !== null) {
                $kelasWali = $db->table('tb_kelas')
                    ->select('id_kelas')
                    ->where($waliCol, $guruId)
                    ->get()->getResultArray();
            }
        } catch (\Throwable $e) {
            $kelasWali = [];
        }

        // 2b) Kelas dari penugasan guru-mapel
        $kelasMapel = $db->table('tb_guru_mapel gm')
            ->select('gm.id_kelas')
            ->where('gm.id_guru', $guruId)
            ->where('gm.id_kelas IS NOT NULL', null, false)
            ->groupBy('gm.id_kelas')
            ->get()->getResultArray();

        // Gabungkan & unikkan
        $kelasIds = [];
        foreach (array_merge($kelasWali, $kelasMapel) as $row) {
            $kid = (int) ($row['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        $kelasIds = array_keys($kelasIds); // [int, int, ...]

        // ===== 3) KPI: Guru Aktif (GLOBAL) =====
        $aktifSet = ['1', 'aktif', 'active', 'ya', 'true'];

        // Coba hitung berdasarkan tb_guru_tahunan jika ada kolomnya
        $guruCount = 0;
        try {
            $statusColTahunan = $pickStatusCol('tb_guru_tahunan', ['is_active', 'status_active', 'status']);
            if ($taId > 0 && $statusColTahunan) {
                $builder = $db->table('tb_guru_tahunan')
                    ->where('tahun_ajaran_id', $taId)
                    ->groupStart()
                    ->whereIn($statusColTahunan, $aktifSet)
                    ->orWhere($statusColTahunan, 1)
                    ->groupEnd();
                $guruCount = (int) $builder->countAllResults();
            }
        } catch (\Throwable $e) {
            // lanjut ke fallback tb_guru
        }

        if ($guruCount === 0) {
            $statusColGuru = $pickStatusCol('tb_guru', ['status_active', 'is_active', 'status']);
            $builder = $db->table('tb_guru');
            if ($statusColGuru) {
                $builder->groupStart()
                    ->whereIn($statusColGuru, $aktifSet)
                    ->orWhere($statusColGuru, 1)
                    ->groupEnd();
            }
            $guruCount = (int) $builder->countAllResults();
        }
        $data['guruCount'] = $guruCount;

        // ===== 4) Siswa aktif & distribusi per kelas (HANYA kelas yang diajar) =====
        $byClass    = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        $siswaTotal = 0;

        $kelasTerpadatJumlah  = 0;
        $kelasTerpadatNama    = null;
        $kelasTerpadatTingkat = null;

        if (!empty($kelasIds)) {
            $rowsPerKelas = $db->table('tb_siswa s')
                ->select('s.kelas_id, k.tingkat, k.nama_kelas, COUNT(*) AS jml')
                ->join('tb_users u', 'u.id_user = s.user_id', 'inner')
                ->join('tb_kelas k', 'k.id_kelas = s.kelas_id', 'left')
                ->where('u.role', 'siswa')
                ->where('u.is_active', 1)
                ->whereIn('s.kelas_id', $kelasIds)
                ->groupBy('s.kelas_id, k.tingkat, k.nama_kelas')
                ->get()->getResultArray();

            foreach ($rowsPerKelas as $r) {
                $j = (int) ($r['jml'] ?? 0);
                $siswaTotal += $j;

                $tingkat = isset($r['tingkat']) && $r['tingkat'] !== null
                    ? (int) $r['tingkat']
                    : (preg_match('/\d+/', (string)($r['nama_kelas'] ?? ''), $m) ? (int) $m[0] : null);

                if ($tingkat !== null && $tingkat >= 1 && $tingkat <= 6) {
                    $byClass[$tingkat] += $j;
                }

                if ($j > $kelasTerpadatJumlah) {
                    $kelasTerpadatJumlah  = $j;
                    $kelasTerpadatTingkat = $tingkat;
                    $kelasTerpadatNama    = $r['nama_kelas'] ?? null;
                }
            }
        }

        $data['byClass']             = $byClass;
        $data['siswaTotal']          = (int) $siswaTotal;
        $data['kelasTerpadat']       = ($kelasTerpadatTingkat !== null) ? (string) $kelasTerpadatTingkat : $kelasTerpadatNama;
        $data['kelasTerpadatJumlah'] = (int) $kelasTerpadatJumlah;
        $data['kelasTerpadatNama']   = $kelasTerpadatNama;

        // ===== 5) Distribusi Mapel (hanya mapel yang diajar guru login) =====
        $mapelLabels = [];
        $mapelCounts = [];
        if (!empty($kelasIds)) {
            $rowsMapel = $db->table('tb_guru_mapel gm')
                ->select('m.nama, COUNT(*) AS jml', false)
                ->join('tb_mapel m', 'm.id_mapel = gm.id_mapel', 'left')
                ->where('gm.id_guru', $guruId)
                ->groupBy('m.nama')
                ->orderBy('m.nama', 'ASC')
                ->get()->getResultArray();

            foreach ($rowsMapel as $rm) {
                $label = trim((string)($rm['nama'] ?? ''));
                $count = (int) ($rm['jml'] ?? 0);
                if ($label !== '') {
                    $mapelLabels[] = $label;
                    $mapelCounts[] = $count;
                }
            }
        }
        $data['mapelLabels'] = $mapelLabels;
        $data['mapelCounts'] = $mapelCounts;

        // ===== 6) Nilai Tertinggi (dibatasi ke kelas yang diajar + TA aktif) =====
        $topNilai = 0;
        $topNama = '—';
        $topKelas = null;

        if ($taId > 0 && !empty($kelasIds)) {
            try {
                $rowTop = $db->table('tb_nilai_siswa ns')
                    ->select('ns.skor, ns.siswa_id, s.full_name as siswa_nama, ns.tanggal, s.kelas_id')
                    ->join('tb_siswa s', 's.id_siswa = ns.siswa_id', 'left')
                    ->where('ns.tahun_ajaran_id', $taId)
                    ->whereIn('s.kelas_id', $kelasIds)
                    ->orderBy('ns.skor', 'DESC')
                    ->orderBy('ns.tanggal', 'DESC')
                    ->limit(1)
                    ->get()->getRowArray();

                if ($rowTop) {
                    $topNilai = (float) ($rowTop['skor'] ?? 0);
                    $topNama  = (string) ($rowTop['siswa_nama'] ?? '—');

                    $kidTop = (int) ($rowTop['kelas_id'] ?? 0);
                    if ($kidTop > 0) {
                        $metaTop = $db->table('tb_kelas')
                            ->select('tingkat, nama_kelas')
                            ->where('id_kelas', $kidTop)
                            ->get()->getRowArray();

                        if ($metaTop) {
                            $topKelas = !empty($metaTop['tingkat'])
                                ? (string) $metaTop['tingkat']
                                : (string) ($metaTop['nama_kelas'] ?? null);
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

        return view('pages/guru/dashboard_guru', $data);
    }

    // DATA SISWA
    public function Data_siswa()
    {
        $db = \Config\Database::connect();

        // ========== A) Identitas GURU login ==========
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }

        // pastikan role guru
        $user = $db->table('tb_users')->select('id_user, role')->where('id_user', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('dashboard'))
                ->with('sweet_error', 'Akses data siswa khusus untuk akun Guru.');
        }

        // map ke tb_guru (tb_guru PUNYA user_id)
        $guru = $db->table('tb_guru')->select('id_guru')->where('user_id', $userId)->get()->getRowArray();
        if (! $guru) {
            return redirect()->to(base_url('dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ========== B) Kelas yang diajar guru (wali + guru_mapel) ==========
        // Deteksi kolom wali di tb_kelas secara dinamis (kalau ada)
        $kelasWali   = [];
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = [
                'wali_guru_id',
                'wali_id',
                'id_guru_wali',
                'guru_wali_id',
                'id_wali_guru',
                'id_guru',
                'guru_id', // jika skema menyimpan id_guru langsung
                'wali_kelas_id',
                'id_wali_kelas',
                'user_id_wali',
                'user_wali_id'
            ];
            $waliCol = null;
            foreach ($waliCandidates as $cand) {
                if (in_array($cand, $kelasFields, true)) {
                    $waliCol = $cand;
                    break;
                }
            }
            if ($waliCol !== null) {
                $kelasWali = $db->table('tb_kelas')->select('id_kelas')
                    ->where($waliCol, $guruId)
                    ->get()->getResultArray();
            }
        } catch (\Throwable $e) {
            $kelasWali = [];
        }

        // Kelas dari penugasan guru-mapel
        $kelasMapel = $db->table('tb_guru_mapel gm')
            ->select('gm.id_kelas')
            ->where('gm.id_guru', $guruId)
            ->where('gm.id_kelas IS NOT NULL', null, false)
            ->groupBy('gm.id_kelas')
            ->get()->getResultArray();

        // Unikkan daftar kelas
        $kelasIds = [];
        foreach (array_merge($kelasWali, $kelasMapel) as $row) {
            $kid = (int)($row['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        $kelasIds = array_values(array_keys($kelasIds));  // [int, int, ...]

        // Jika guru belum terikat kelas apa pun → hasil kosong
        if (empty($kelasIds)) {
            $data = [
                'title'         => 'Data siswa | SDN Talun 2 Kota Serang',
                'sub_judul'     => 'Data Siswa/i',
                'nav_link'      => 'Data Siswa',
                'd_siswa'       => [],
                'q'             => '',
                'gender'        => '',
                'SiswaAktif'    => 0,
                'SiswaNonAktif' => 0,
                'totalSiswa'    => 0,
            ];
            return view('pages/guru/data_siswa', $data);
        }

        // ========== C) Ambil parameter filter ==========
        $q      = trim((string) $this->request->getGet('q'));
        $gender = trim((string) $this->request->getGet('gender'));

        // ========== D) Query siswa HANYA di kelas yang diajar ==========
        $builder = $db->table('tb_siswa')
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
            ->whereIn('tb_siswa.kelas_id', $kelasIds);

        // filter q (nama / NISN)
        if ($q !== '') {
            $builder->groupStart()
                ->like('tb_siswa.full_name', $q, 'both', null, true) // case-insensitive (depends on collation)
                ->orLike('tb_siswa.nisn', $q, 'both', null, true)
                ->groupEnd();
        }

        // filter gender
        if ($gender !== '') {
            $builder->where('tb_siswa.gender', $gender);
        }

        // optional: hanya akun siswa aktif (kalau mau)
        // $builder->where('u.role', 'siswa')->where('u.is_active', 1);

        $rows = $builder
            ->orderBy('tb_siswa.full_name', 'ASC')
            ->get()->getResultArray();

        // ========== E) Hitung aktif/nonaktif ==========
        $SiswaAktif = 0;
        $SiswaNonAktif = 0;
        foreach ($rows as $r) {
            $flag = (int)($r['user_active'] ?? 0);
            if ($flag === 1) $SiswaAktif++;
            else $SiswaNonAktif++;
        }

        // ========== F) Kirim ke view ==========
        $data = [
            'title'         => 'Data siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Data Siswa/i',
            'nav_link'      => 'Data Siswa',
            'd_siswa'       => $rows,   // sudah termasuk laporan_count
            'q'             => $q,
            'gender'        => $gender,
            'SiswaAktif'    => $SiswaAktif,
            'SiswaNonAktif' => $SiswaNonAktif,
            'totalSiswa'    => count($rows),
        ];

        return view('pages/guru/data_siswa', $data);
    }

    public function page_detail_siswa(string $nisn)
    {
        $db   = \Config\Database::connect();
        $nisn = trim($nisn);

        // ===== 0) Validasi sesi & role guru =====
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }

        $user = $db->table('tb_users')->select('id_user, role')->where('id_user', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Akses ditolak. Khusus akun Guru.');
        }

        $guru = $db->table('tb_guru')->select('id_guru')->where('user_id', $userId)->get()->getRowArray();
        if (! $guru) {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ===== 1) Susun daftar kelas yang diajar guru =====
        // 1a) Kelas sebagai wali (deteksi kolom wali secara dinamis)
        $kelasWali = [];
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = [
                'wali_guru_id',
                'wali_id',
                'id_guru_wali',
                'guru_wali_id',
                'id_wali_guru',
                'id_guru',
                'guru_id', // jika skema menyimpan id_guru langsung di tb_kelas
                'wali_kelas_id',
                'id_wali_kelas',
                'user_id_wali',
                'user_wali_id'
            ];
            $waliCol = null;
            foreach ($waliCandidates as $cand) {
                if (in_array($cand, $kelasFields, true)) {
                    $waliCol = $cand;
                    break;
                }
            }
            if ($waliCol !== null) {
                $kelasWali = $db->table('tb_kelas')->select('id_kelas')->where($waliCol, $guruId)->get()->getResultArray();
            }
        } catch (\Throwable $e) {
            $kelasWali = [];
        }

        // 1b) Kelas dari penugasan guru-mapel
        $kelasMapel = $db->table('tb_guru_mapel gm')
            ->select('gm.id_kelas')
            ->where('gm.id_guru', $guruId)
            ->where('gm.id_kelas IS NOT NULL', null, false)
            ->groupBy('gm.id_kelas')
            ->get()->getResultArray();

        // 1c) Gabungkan & unikkan kelas
        $kelasIds = [];
        foreach (array_merge($kelasWali, $kelasMapel) as $row) {
            $kid = (int) ($row['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        $kelasIds = array_keys($kelasIds); // [int, int, ...]

        // Jika guru belum terikat kelas apapun → tolak akses
        if (empty($kelasIds)) {
            return redirect()->to(base_url('guru/data-siswa'))
                ->with('sweet_error', 'Anda belum terikat pada kelas manapun.');
        }

        // ===== 2) Ambil siswa by NISN, tapi BATASI ke kelas yang diajar =====
        $detailSiswa = $db->table('tb_siswa')
            ->select("
            tb_siswa.*,
            tb_users.username AS user_name,
            tb_users.username AS user_username,
            tb_siswa.kelas_id AS kelas_id_final,
            k.nama_kelas      AS nama_kelas
        ")
            ->join('tb_users', 'tb_users.id_user = tb_siswa.user_id', 'left')
            ->join('tb_kelas k', 'k.id_kelas = tb_siswa.kelas_id', 'left')
            ->where('tb_siswa.nisn', $nisn)
            ->whereIn('tb_siswa.kelas_id', $kelasIds)   // <<< pembatasan akses utama
            ->get()->getRowArray();

        if (! $detailSiswa) {
            // Bisa karena NISN tidak ada, atau ada tapi bukan kelas yang diajar
            return redirect()->to(base_url('guru/data-siswa'))
                ->with('sweet_error', 'Siswa tidak ditemukan atau bukan kelas yang Anda ajar.');
        }

        // ===== 3) Kirim ke view guru =====
        $data = [
            'title'     => 'Detail siswa | SDN Talun 2 Kota Serang',
            'sub_judul' => 'Detail Siswa/i',
            'nav_link'  => 'Data Siswa',
            'siswa'     => $detailSiswa,
        ];

        return view('pages/guru/detail-siswa', $data);
    }

    public function page_laporan_d_siswa()
    {
        $db  = \Config\Database::connect();
        $req = $this->request;

        $q    = trim((string)($req->getGet('q') ?? ''));            // cari nama/NISN
        $idTA = trim((string)($req->getGet('tahunajaran') ?? ''));  // id_tahun_ajaran

        // ===== 0) Validasi sesi & role guru =====
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }

        $user = $db->table('tb_users')->select('id_user, role')->where('id_user', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Akses ditolak. Khusus akun Guru.');
        }

        $guru = $db->table('tb_guru')->select('id_guru')->where('user_id', $userId)->get()->getRowArray();
        if (! $guru) {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ===== 1) Ambil daftar Tahun Ajaran untuk dropdown =====
        $taRows = $db->table('tb_tahun_ajaran')
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->get()->getResultArray();

        $listTA = [];
        $activeTAId = null;
        foreach ($taRows as $ta) {
            $label = trim((string)($ta['tahun'] ?? '')) . ' • Semester ' . trim((string)($ta['semester'] ?? ''));
            $isActive = (string)($ta['is_active'] ?? '');
            // tandai aktif
            if (in_array(strtolower($isActive), ['1', 'aktif', 'active', 'ya', 'true'], true)) {
                $label .= ' (Aktif)';
                if ($activeTAId === null) $activeTAId = (int)$ta['id_tahun_ajaran'];
            }
            $listTA[] = [
                'id_tahun_ajaran' => (int)$ta['id_tahun_ajaran'],
                'label'           => $label,
            ];
        }

        // Jika user belum memilih TA, default ke TA aktif (jika ada)
        if ($idTA === '' && $activeTAId !== null) {
            $idTA = (string)$activeTAId;
        }

        // ===== 2) Susun daftar kelas yang diajar (wali + guru_mapel) =====
        $kelasWali = [];
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = [
                'wali_guru_id',
                'wali_id',
                'id_guru_wali',
                'guru_wali_id',
                'id_wali_guru',
                'id_guru',
                'guru_id',
                'wali_kelas_id',
                'id_wali_kelas',
                'user_id_wali',
                'user_wali_id'
            ];
            $waliCol = null;
            foreach ($waliCandidates as $cand) {
                if (in_array($cand, $kelasFields, true)) {
                    $waliCol = $cand;
                    break;
                }
            }
            if ($waliCol !== null) {
                $kelasWali = $db->table('tb_kelas')->select('id_kelas')->where($waliCol, $guruId)->get()->getResultArray();
            }
        } catch (\Throwable $e) {
            $kelasWali = [];
        }

        $kelasMapel = $db->table('tb_guru_mapel gm')
            ->select('gm.id_kelas')
            ->where('gm.id_guru', $guruId)
            ->where('gm.id_kelas IS NOT NULL', null, false)
            ->groupBy('gm.id_kelas')
            ->get()->getResultArray();

        $kelasIds = [];
        foreach (array_merge($kelasWali, $kelasMapel) as $row) {
            $kid = (int)($row['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        $kelasIds = array_keys($kelasIds);

        if (empty($kelasIds)) {
            return view('pages/guru/laporan_data_siswa', [
                'title'         => 'Laporan Data Siswa | SDN Talun 2 Kota Serang',
                'sub_judul'     => 'Laporan Data Siswa/i',
                'nav_link'      => 'Laporan Data Siswa',
                'd_siswa'       => [],
                'q'             => $q,
                'tahunajaran'   => $idTA,
                'listTA'        => $listTA,
                'SiswaAktif'    => 0,
                'SiswaNonAktif' => 0,
                'EnrolAktif'    => 0,
                'EnrolNonAktif' => 0,
                'totalSiswa'    => 0,
            ]);
        }

        // ===== 3) Query data (dibatasi kelas guru) =====
        $builder = $this->SiswaTahunanModel
            ->select("
            id_siswa_tahun, siswa_id, tahun_ajaran_id, status, tanggal_masuk, tanggal_keluar,
            s.id_siswa, s.nisn, s.full_name, s.gender, s.kelas_id,
            u.username AS user_name, u.is_active AS user_active,
            ta.tahun AS tahun_ajaran, ta.semester AS semester, ta.is_active AS ta_active
        ", false)
            ->join('tb_siswa s', 's.id_siswa = siswa_id', 'left')
            ->join('tb_users u', 'u.id_user = s.user_id', 'left')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = tahun_ajaran_id', 'left')
            ->whereIn('s.kelas_id', $kelasIds);

        // Pencarian q
        if ($q !== '') {
            $builder->groupStart()
                ->like('s.full_name', $q, 'both', null, true)
                ->orLike('s.nisn', $q, 'both', null, true)
                ->groupEnd();
        }

        // Filter TA (dropdown)
        if ($idTA !== '' && ctype_digit($idTA)) {
            $builder->where('tahun_ajaran_id', (int)$idTA);
        }

        $rows = $builder->orderBy('s.full_name', 'ASC')->findAll();

        // ===== 4) Ringkasan =====
        $SiswaAktif = 0;
        $SiswaNonAktif = 0;
        $EnrolAktif = 0;
        $EnrolNonAktif = 0;
        foreach ($rows as $r) {
            $ua = (int)($r['user_active'] ?? 0);
            if ($ua === 1) $SiswaAktif++;
            else $SiswaNonAktif++;

            $st = strtolower((string)($r['status'] ?? ''));
            $isEnrolActive = in_array($st, ['1', 'aktif', 'active', 'ya', 'true'], true);
            if ($isEnrolActive) $EnrolAktif++;
            else $EnrolNonAktif++;
        }

        // ===== 5) Render =====
        return view('pages/guru/laporan_data_siswa', [
            'title'         => 'Laporan Data Siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Laporan Data Siswa/i',
            'nav_link'      => 'Laporan Data Siswa',
            'd_siswa'       => $rows,
            'q'             => $q,
            'tahunajaran'   => $idTA,
            'listTA'        => $listTA,           // << kirim ke view
            'SiswaAktif'    => $SiswaAktif,
            'SiswaNonAktif' => $SiswaNonAktif,
            'EnrolAktif'    => $EnrolAktif,
            'EnrolNonAktif' => $EnrolNonAktif,
            'totalSiswa'    => count($rows),
        ]);
    }


    public function page_laporan_nilai_siswa()
    {
        $db  = \Config\Database::connect();
        $req = $this->request;

        $q        = trim((string)($req->getGet('q') ?? ''));
        $idTA     = trim((string)($req->getGet('tahunajaran') ?? ''));
        $kodeKat  = trim((string)($req->getGet('kategori') ?? ''));
        $idMapel  = trim((string)($req->getGet('mapel') ?? ''));

        // ===== 0) Validasi sesi & role guru =====
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }

        $user = $db->table('tb_users')->select('id_user, role')->where('id_user', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Akses ditolak. Khusus akun Guru.');
        }

        $guru = $db->table('tb_guru')->select('id_guru')->where('user_id', $userId)->get()->getRowArray();
        if (! $guru) {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ===== 1) Dropdown Tahun Ajaran =====
        $taRows = $db->table('tb_tahun_ajaran')
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->get()->getResultArray();

        $listTA = [];
        $activeTAId = null;
        foreach ($taRows as $ta) {
            $label = trim((string)($ta['tahun'] ?? '')) . ' • Semester ' . trim((string)($ta['semester'] ?? ''));
            $isActive = strtolower((string)($ta['is_active'] ?? ''));
            if (in_array($isActive, ['1', 'aktif', 'active', 'ya', 'true'], true)) {
                $label .= ' (Aktif)';
                if ($activeTAId === null) $activeTAId = (int)$ta['id_tahun_ajaran'];
            }
            $listTA[] = ['id_tahun_ajaran' => (int)$ta['id_tahun_ajaran'], 'label' => $label];
        }
        if ($idTA === '' && $activeTAId !== null) $idTA = (string)$activeTAId;

        // ===== 2) Kelas & Mapel yang diajar guru =====
        $kelasWali = [];
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = ['wali_guru_id', 'wali_id', 'id_guru_wali', 'guru_wali_id', 'id_wali_guru', 'id_guru', 'guru_id', 'wali_kelas_id', 'id_wali_kelas', 'user_id_wali', 'user_wali_id'];
            $waliCol = null;
            foreach ($waliCandidates as $cand) if (in_array($cand, $kelasFields, true)) {
                $waliCol = $cand;
                break;
            }
            if ($waliCol !== null) {
                $kelasWali = $db->table('tb_kelas')->select('id_kelas')->where($waliCol, $guruId)->get()->getResultArray();
            }
        } catch (\Throwable $e) {
            $kelasWali = [];
        }

        $gmRows = $db->table('tb_guru_mapel gm')
            ->select('gm.id_kelas, gm.id_mapel')
            ->where('gm.id_guru', $guruId)
            ->where('gm.id_kelas IS NOT NULL', null, false)
            ->where('gm.id_mapel IS NOT NULL', null, false)
            ->get()->getResultArray();

        $kelasIds = [];
        foreach ($kelasWali as $r) {
            $kid = (int)($r['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        foreach ($gmRows as $r) {
            $kid = (int)($r['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        $kelasIds = array_keys($kelasIds);

        $mapelIds = [];
        foreach ($gmRows as $r) {
            $mid = (int)($r['id_mapel'] ?? 0);
            if ($mid > 0) $mapelIds[$mid] = true;
        }
        $mapelIds = array_keys($mapelIds);

        $listMapel = [];
        if (!empty($mapelIds)) {
            $listMapel = $db->table('tb_mapel')
                ->select('id_mapel, nama')
                ->whereIn('id_mapel', $mapelIds)
                ->orderBy('nama', 'ASC')
                ->get()->getResultArray();
        }

        if (empty($kelasIds) && empty($mapelIds)) {
            return view('pages/guru/laporan_nilai_siswa', [
                'title'       => 'Laporan Nilai Siswa | SDN Talun 2 Kota Serang',
                'sub_judul'   => 'Laporan Nilai Siswa',
                'nav_link'    => 'Laporan Nilai',
                'd_nilai'     => [],
                'q'           => $q,
                'tahunajaran' => $idTA,
                'kategori'    => $kodeKat,
                'mapel'       => $idMapel,
                'listTA'      => $listTA,
                'listMapel'   => $listMapel,
                'totalNilai'  => 0,
                'totalSiswa'  => 0,
            ]);
        }

        // ===== 3) Query nilai: batasi ke mapel & kelas yang diajar guru =====
        $nilaiModel = new \App\Models\NilaiSiswaModel();  // table: tb_nilai_siswa
        $tNS = $nilaiModel->table ?? 'tb_nilai_siswa';    // nama tabel nilai

        $builder = $nilaiModel
            ->select("
            {$tNS}.id_nilai      AS id_nilai,
            {$tNS}.siswa_id      AS siswa_id,
            {$tNS}.tahun_ajaran_id,
            {$tNS}.mapel_id      AS mapel_id,
            {$tNS}.kategori_id   AS kategori_id,
            {$tNS}.skor          AS skor,
            {$tNS}.tanggal       AS tanggal_nilai,
            {$tNS}.keterangan    AS nilai_keterangan,

            s.id_siswa, s.nisn, s.full_name, s.gender, s.kelas_id,

            ta.id_tahun_ajaran, ta.tahun AS tahun_ajaran, ta.semester,

            m.id_mapel, m.nama,

            k.id_kategori, k.kode AS kategori_kode, k.nama AS kategori_nama
        ", false)
            ->join('tb_siswa s', 's.id_siswa = ' . $tNS . '.siswa_id', 'left')
            ->join('tb_tahun_ajaran ta', 'ta.id_tahun_ajaran = ' . $tNS . '.tahun_ajaran_id', 'left')
            ->join('tb_mapel m', 'm.id_mapel = ' . $tNS . '.mapel_id', 'left')
            ->join('tb_kategori_nilai k', 'k.id_kategori = ' . $tNS . '.kategori_id', 'left')
            ->join(
                'tb_guru_mapel gm',
                'gm.id_guru = ' . $db->escape($guruId) .
                    ' AND gm.id_mapel = ' . $tNS . '.mapel_id' .
                    ' AND gm.id_kelas = s.kelas_id',
                'inner'
            );

        if (!empty($kelasIds))  $builder->whereIn('s.kelas_id', $kelasIds);
        if (!empty($mapelIds))  $builder->whereIn($tNS . '.mapel_id', $mapelIds);

        if ($q !== '') {
            $builder->groupStart()
                ->like('s.full_name', $q, 'both', null, true)
                ->orLike('s.nisn', $q, 'both', null, true)
                ->groupEnd();
        }

        if ($idTA !== '' && ctype_digit($idTA)) {
            $builder->where($tNS . '.tahun_ajaran_id', (int)$idTA);
        }

        if ($kodeKat !== '') {
            $builder->where('k.kode', strtoupper($kodeKat));
        }

        if ($idMapel !== '' && ctype_digit($idMapel)) {
            $idMapelInt = (int)$idMapel;
            if (in_array($idMapelInt, $mapelIds, true)) {
                $builder->where($tNS . '.mapel_id', $idMapelInt);
            } else {
                $builder->where('1 = 0', null, false);
            }
        }

        // Urutkan dengan kolom yang terkwalifikasi
        $rows = $builder
            ->orderBy('s.full_name', 'ASC')
            ->orderBy('m.nama', 'ASC')
            ->orderBy('k.kode', 'ASC')
            ->orderBy($tNS . '.tanggal', 'ASC')
            ->findAll();

        // Ringkasan
        $totalNilai = count($rows);
        $distinctSiswa = [];
        foreach ($rows as $r) $distinctSiswa[(int)($r['siswa_id'] ?? 0)] = true;
        $totalSiswa = count($distinctSiswa);

        return view('pages/guru/laporan-nilai-siswa', [
            'title'         => 'Laporan Nilai Siswa | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Laporan Nilai Siswa',
            'nav_link'      => 'Laporan Nilai',
            'd_nilai'       => $rows,
            'q'             => $q,
            'tahunajaran'   => $idTA,
            'kategori'      => $kodeKat,
            'mapel'         => $idMapel,
            'listTA'        => $listTA,
            'listMapel'     => $listMapel,
            'totalNilai'    => $totalNilai,
            'totalSiswa'    => $totalSiswa,
        ]);
    }

    public function page_tambah_nilai()
    {
        $db   = \Config\Database::connect();
        $req  = $this->request;
        $isPost = $req->getMethod() === 'post';

        // ===== 0) Validasi sesi & role guru =====
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }
        $user = $db->table('tb_users')->select('id_user, role')->where('id_user', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Akses ditolak. Khusus akun Guru.');
        }
        $guru = $db->table('tb_guru')->select('id_guru')->where('user_id', $userId)->get()->getRowArray();
        if (! $guru) {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ===== mode perilaku: hide (default) | prune_kategori | warn =====
        $mode = strtolower((string)($req->getGet('mode') ?? 'hide'));
        if (!in_array($mode, ['hide', 'prune_kategori', 'warn'], true)) {
            $mode = 'hide';
        }

        // ===== 1) Himpun kelas & mapel yang diajar guru =====
        // 1a) kelas sebagai wali (deteksi kolom wali dinamis)
        $kelasWali = [];
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = [
                'wali_guru_id',
                'wali_id',
                'id_guru_wali',
                'guru_wali_id',
                'id_wali_guru',
                'id_guru',
                'guru_id',
                'wali_kelas_id',
                'id_wali_kelas',
                'user_id_wali',
                'user_wali_id'
            ];
            $waliCol = null;
            foreach ($waliCandidates as $cand) {
                if (in_array($cand, $kelasFields, true)) {
                    $waliCol = $cand;
                    break;
                }
            }
            if ($waliCol !== null) {
                $kelasWali = $db->table('tb_kelas')->select('id_kelas')->where($waliCol, $guruId)->get()->getResultArray();
            }
        } catch (\Throwable $e) {
            $kelasWali = [];
        }

        // 1b) kelas & mapel dari tb_guru_mapel
        $gmRows = $db->table('tb_guru_mapel gm')
            ->select('gm.id_kelas, gm.id_mapel')
            ->where('gm.id_guru', $guruId)
            ->where('gm.id_kelas IS NOT NULL', null, false)
            ->where('gm.id_mapel IS NOT NULL', null, false)
            ->get()->getResultArray();

        // 1c) gabungkan kelas unik + mapel unik
        $kelasIds = [];
        foreach ($kelasWali as $r) {
            $kid = (int)($r['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        foreach ($gmRows   as $r) {
            $kid = (int)($r['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
        }
        $kelasIds = array_keys($kelasIds);

        $mapelIds = [];
        foreach ($gmRows as $r) {
            $mid = (int)($r['id_mapel'] ?? 0);
            if ($mid > 0) $mapelIds[$mid] = true;
        }
        $mapelIds = array_keys($mapelIds);

        if (empty($kelasIds) && empty($mapelIds)) {
            return redirect()->to(base_url('guru/laporan/nilai-siswa'))
                ->with('sweet_error', 'Anda belum terikat pada kelas atau mapel mana pun.');
        }

        // ===== 2) Dropdown data =====
        // 2a) Tahun ajaran
        $optTA = $db->table('tb_tahun_ajaran')
            ->select('id_tahun_ajaran, tahun, semester, is_active')
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->get()->getResultArray();

        $tahunajaran = (string)($req->getGet('tahunajaran') ?? '');
        $activeTAId = null;
        foreach ($optTA as $ta) {
            $ia = strtolower((string)($ta['is_active'] ?? ''));
            if (in_array($ia, ['1', 'aktif', 'active', 'ya', 'true'], true)) {
                $activeTAId = (int)$ta['id_tahun_ajaran'];
                break;
            }
        }
        if ($tahunajaran === '' && $activeTAId !== null) $tahunajaran = (string)$activeTAId;
        $taInt = ctype_digit($tahunajaran) ? (int)$tahunajaran : 0;

        // 2b) Mapel (hanya yang diajar guru)
        $optMapel = [];
        if (!empty($mapelIds)) {
            $optMapel = $db->table('tb_mapel')
                ->select('id_mapel, nama')
                ->whereIn('id_mapel', $mapelIds)
                ->orderBy('nama', 'ASC')
                ->get()->getResultArray();
        }
        $mapel = (string)($req->getGet('mapel') ?? '');
        $mapelInt = ctype_digit($mapel) ? (int)$mapel : 0;

        // 2c) Kategori nilai
        $optKategori = $db->table('tb_kategori_nilai')
            ->select('id_kategori, nama, kode')
            ->orderBy('nama', 'ASC')->get()->getResultArray();
        $kategori = (string)($req->getGet('kategori') ?? '');
        $kategoriIdFilter = 0;
        if ($kategori !== '') {
            $rowKat = $db->table('tb_kategori_nilai')->select('id_kategori,kode')
                ->where('UPPER(kode)', strtoupper($kategori))->get()->getRowArray();
            if ($rowKat) $kategoriIdFilter = (int)$rowKat['id_kategori'];
        }

        // 2d) Siswa (hanya dari kelas yang diajar guru)
        $optSiswa = [];
        if (!empty($kelasIds)) {
            $optSiswa = $db->table('tb_siswa s')
                ->select('s.id_siswa, s.full_name, s.nisn, s.kelas_id, k.nama_kelas')
                ->join('tb_users u', 'u.id_user = s.user_id', 'left')
                ->join('tb_kelas k', 'k.id_kelas = s.kelas_id', 'left')
                ->where('u.role', 'siswa')
                ->where('u.is_active', 1)
                ->whereIn('s.kelas_id', $kelasIds)
                ->orderBy('s.full_name', 'ASC')
                ->get()->getResultArray();
        }

        // ===== 3) Anti-duplikasi pra-tampil =====
        // Kumpulkan siapa saja yang SUDAH punya nilai untuk kombinasi TA×Mapel×Kategori
        // a) dari tb_nilai_siswa (by siswa_id)
        $sudahById = [];
        if ($mapelInt > 0) { // minimal mapel dipilih
            $q = $db->table('tb_nilai_siswa')->select('siswa_id, kategori_id, tahun_ajaran_id, mapel_id');
            $q->where('mapel_id', $mapelInt);
            if ($taInt > 0)            $q->where('tahun_ajaran_id', $taInt);
            if ($kategoriIdFilter > 0) $q->where('kategori_id', $kategoriIdFilter);
            $rowsSudah = $q->get()->getResultArray();
            foreach ($rowsSudah as $r) {
                $sudahById[(int)$r['siswa_id']] = true;
            }
        }

        // b) dari tb_nilai_tahun (fallback by nisn)
        $sudahByNisn = [];
        if ($mapelInt > 0) {
            // asumsi kolom tb_nilai_tahun: nisn, tahun_ajaran_id, mapel_id, kategori_id
            $q2 = $db->table('tb_nilai_tahun')->select('nisn, kategori_id, tahun_ajaran_id, mapel_id');
            $q2->where('mapel_id', $mapelInt);
            if ($taInt > 0)            $q2->where('tahun_ajaran_id', $taInt);
            if ($kategoriIdFilter > 0) $q2->where('kategori_id', $kategoriIdFilter);
            foreach ($q2->get()->getResultArray() as $r) {
                $nisn = (string)($r['nisn'] ?? '');
                if ($nisn !== '') $sudahByNisn[$nisn] = true;
            }
        }

        // ==== Mode: hide → sembunyikan siswa jika TA×Mapel×Kategori sudah ada
        if ($mode === 'hide' && $mapelInt > 0 && $taInt > 0 && $kategoriIdFilter > 0) {
            $optSiswa = array_values(array_filter($optSiswa, function ($s) use ($sudahById, $sudahByNisn) {
                $sid  = (int)($s['id_siswa'] ?? 0);
                $nisn = (string)($s['nisn'] ?? '');
                if (isset($sudahById[$sid])) return false;
                if ($nisn !== '' && isset($sudahByNisn[$nisn])) return false;
                return true;
            }));
        }

        // ==== Mode: prune_kategori → hilangkan kategori yang sudah dipakai oleh siswa tertentu
        // Agar efektif, butuh ?siswa=<id> pada query (mis. dari dependent dropdown via JS)
        $siswaSelected = (int)($req->getGet('siswa') ?? 0);
        if ($mode === 'prune_kategori' && $mapelInt > 0 && $taInt > 0 && $siswaSelected > 0) {
            // ambil kategori yang sudah ada untuk siswaSelected pada TA+Mapel tsb
            $katUsed = [];
            $r1 = $db->table('tb_nilai_siswa')->select('kategori_id')
                ->where('siswa_id', $siswaSelected)
                ->where('tahun_ajaran_id', $taInt)
                ->where('mapel_id', $mapelInt)
                ->get()->getResultArray();
            foreach ($r1 as $r) {
                $katUsed[(int)$r['kategori_id']] = true;
            }

            // fallback tb_nilai_tahun by nisn
            $nisnSel = $db->table('tb_siswa')->select('nisn')->where('id_siswa', $siswaSelected)->get()->getRow('nisn');
            if ($nisnSel) {
                $r2 = $db->table('tb_nilai_tahun')->select('kategori_id')
                    ->where('nisn', (string)$nisnSel)
                    ->where('tahun_ajaran_id', $taInt)
                    ->where('mapel_id', $mapelInt)
                    ->get()->getResultArray();
                foreach ($r2 as $r) {
                    $katUsed[(int)$r['kategori_id']] = true;
                }
            }

            if (!empty($katUsed)) {
                $optKategori = array_values(array_filter($optKategori, function ($k) use ($katUsed) {
                    return !isset($katUsed[(int)($k['id_kategori'] ?? 0)]);
                }));
            }
        }

        // ===== 4) POST: Simpan =====
        if ($isPost) {
            $nilaiModel = new \App\Models\NilaiSiswaModel(); // tb_nilai_siswa

            $payload = [
                'siswa_id'        => (int)$req->getPost('siswa_id'),
                'tahun_ajaran_id' => (int)$req->getPost('tahun_ajaran_id'),
                'mapel_id'        => (int)$req->getPost('mapel_id'),
                'kategori_id'     => (int)$req->getPost('kategori_id'),
                'skor'            => (float)$req->getPost('skor'),
                'tanggal'         => trim((string)$req->getPost('tanggal')),
                'keterangan'      => trim((string)$req->getPost('keterangan')),
            ];

            // Validasi
            $rules = [
                'siswa_id'        => 'required|is_natural_no_zero',
                'tahun_ajaran_id' => 'required|is_natural_no_zero',
                'mapel_id'        => 'required|is_natural_no_zero',
                'kategori_id'     => 'required|is_natural_no_zero',
                'skor'            => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
                'tanggal'         => 'required|valid_date[Y-m-d]',
                'keterangan'      => 'permit_empty|max_length[255]',
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Otorisasi: siswa di kelas guru
            $siswa = $db->table('tb_siswa s')->select('s.id_siswa, s.kelas_id, s.nisn')
                ->where('s.id_siswa', $payload['siswa_id'])->get()->getRowArray();
            if (! $siswa || !in_array((int)$siswa['kelas_id'], $kelasIds, true)) {
                return redirect()->back()->withInput()->with('sweet_error', 'Siswa tidak berada pada kelas yang Anda ampu.');
            }

            // Otorisasi: guru mengajar mapel tsb di kelas siswa
            $gmOk = $db->table('tb_guru_mapel')
                ->where('id_guru', $guruId)
                ->where('id_mapel', $payload['mapel_id'])
                ->where('id_kelas', (int)$siswa['kelas_id'])
                ->countAllResults();
            if ($gmOk < 1) {
                return redirect()->back()->withInput()->with('sweet_error', 'Anda tidak mengajar mapel tersebut di kelas siswa ini.');
            }

            // Validasi referensi
            $taOk  = $db->table('tb_tahun_ajaran')->where('id_tahun_ajaran', $payload['tahun_ajaran_id'])->countAllResults();
            $katOk = $db->table('tb_kategori_nilai')->where('id_kategori', $payload['kategori_id'])->countAllResults();
            if ($taOk < 1 || $katOk < 1) {
                return redirect()->back()->withInput()->with('sweet_error', 'Tahun ajaran / kategori tidak valid.');
            }

            // Cegah duplikasi untuk TA×Mapel×Kategori (by siswa_id) + fallback NISN di tb_nilai_tahun
            $dup1 = $db->table('tb_nilai_siswa')
                ->where('siswa_id', $payload['siswa_id'])
                ->where('tahun_ajaran_id', $payload['tahun_ajaran_id'])
                ->where('mapel_id', $payload['mapel_id'])
                ->where('kategori_id', $payload['kategori_id'])
                ->countAllResults();

            $dup2 = 0;
            $nisn = (string)($siswa['nisn'] ?? '');
            if ($nisn !== '') {
                $dup2 = $db->table('tb_nilai_tahun')
                    ->where('nisn', $nisn)
                    ->where('tahun_ajaran_id', $payload['tahun_ajaran_id'])
                    ->where('mapel_id', $payload['mapel_id'])
                    ->where('kategori_id', $payload['kategori_id'])
                    ->countAllResults();
            }

            if ($dup1 > 0 || $dup2 > 0) {
                // mode=warn → tampilkan alert yang jelas
                $msg = 'Nilai untuk kombinasi TA×Mapel×Kategori siswa ini sudah ada.';
                return redirect()->back()->withInput()->with('sweet_error', $msg);
            }

            // Simpan
            try {
                $nilaiModel->insert($payload);
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('sweet_error', 'Gagal menyimpan nilai. Coba lagi.');
            }

            // Redirect ke daftar, bawa filter terakhir
            $qs = http_build_query([
                'q'           => '',
                'tahunajaran' => (string)$payload['tahun_ajaran_id'],
                'kategori'    => (string)($kategori ?? ''),
                'mapel'       => (string)$payload['mapel_id'],
            ]);
            return redirect()->to(base_url('guru/laporan/nilai-siswa') . ($qs ? ('?' . $qs) : ''))
                ->with('sweet_success', 'Nilai berhasil disimpan.');
        }

        // ===== 5) Render GET form =====
        return view('pages/guru/tambah_laporan_nilai_siswa', [
            'title'         => 'Tambah Nilai | SDN Talun 2 Kota Serang',
            'sub_judul'     => 'Tambah Nilai Siswa',
            'nav_link'      => 'Laporan Nilai',
            'optSiswa'      => $optSiswa,
            'optTA'         => $optTA,
            'optMapel'      => $optMapel,
            'optKategori'   => $optKategori,
            'tahunajaran'   => $tahunajaran,
            'kategori'      => $kategori,
            'mapel'         => $mapel,
            'mode'          => $mode,
            'allowedFields' => (new \App\Models\NilaiSiswaModel())->allowedFields ?? [],
        ]);
    }


    public function aksi_tambah_nilai()
    {
        $db   = \Config\Database::connect();
        $req  = $this->request;

        // ===== 0) Validasi sesi & role guru =====
        $userId = (int) (session('id_user') ?? session('user_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('login'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login ulang.');
        }
        $user = $db->table('tb_users')->select('id_user, role')->where('id_user', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'guru') {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Akses ditolak. Khusus akun Guru.');
        }
        $guru = $db->table('tb_guru')->select('id_guru')->where('user_id', $userId)->get()->getRowArray();
        if (! $guru) {
            return redirect()->to(base_url('guru/dashboard'))
                ->with('sweet_error', 'Profil guru tidak ditemukan.');
        }
        $guruId = (int) $guru['id_guru'];

        // ===== 1) Payload + Validasi dasar =====
        $payload = [
            'siswa_id'        => (int)$req->getPost('siswa_id'),
            'tahun_ajaran_id' => (int)$req->getPost('tahun_ajaran_id'),
            'mapel_id'        => (int)$req->getPost('mapel_id'),
            'kategori_id'     => (int)$req->getPost('kategori_id'),
            'skor'            => (float)$req->getPost('skor'),
            'tanggal'         => trim((string)$req->getPost('tanggal')),
            'keterangan'      => trim((string)$req->getPost('keterangan')),
        ];

        $rules = [
            'siswa_id'        => 'required|is_natural_no_zero',
            'tahun_ajaran_id' => 'required|is_natural_no_zero',
            'mapel_id'        => 'required|is_natural_no_zero',
            'kategori_id'     => 'required|is_natural_no_zero',
            'skor'            => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'tanggal'         => 'required|valid_date[Y-m-d]',
            'keterangan'      => 'permit_empty|max_length[255]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ===== 2) Hardening & otorisasi =====
        // A) siswa harus berada di kelas yang diajar guru
        $kelasIds = []; // kumpulkan ulang untuk pembuktian
        // kelas wali
        try {
            $kelasFields = $db->getFieldNames('tb_kelas');
            $waliCandidates = [
                'wali_guru_id',
                'wali_id',
                'id_guru_wali',
                'guru_wali_id',
                'id_wali_guru',
                'id_guru',
                'guru_id',
                'wali_kelas_id',
                'id_wali_kelas',
                'user_id_wali',
                'user_wali_id'
            ];
            $waliCol = null;
            foreach ($waliCandidates as $c) {
                if (in_array($c, $kelasFields, true)) {
                    $waliCol = $c;
                    break;
                }
            }
            if ($waliCol !== null) {
                $rows = $db->table('tb_kelas')->select('id_kelas')->where($waliCol, $guruId)->get()->getResultArray();
                foreach ($rows as $r) {
                    $kid = (int)($r['id_kelas'] ?? 0);
                    if ($kid > 0) $kelasIds[$kid] = true;
                }
            }
        } catch (\Throwable $e) {
        }
        // kelas dari guru_mapel
        $gmRows = $db->table('tb_guru_mapel')->select('id_kelas,id_mapel')->where('id_guru', $guruId)->get()->getResultArray();
        $mapelIds = [];
        foreach ($gmRows as $r) {
            $kid = (int)($r['id_kelas'] ?? 0);
            if ($kid > 0) $kelasIds[$kid] = true;
            $mid = (int)($r['id_mapel'] ?? 0);
            if ($mid > 0) $mapelIds[$mid] = true;
        }
        $kelasIds = array_keys($kelasIds);
        $mapelIds = array_keys($mapelIds);

        $siswa = $db->table('tb_siswa')->select('id_siswa, kelas_id')->where('id_siswa', $payload['siswa_id'])->get()->getRowArray();
        if (! $siswa || ! in_array((int)$siswa['kelas_id'], $kelasIds, true)) {
            return redirect()->back()->withInput()->with('sweet_error', 'Siswa tidak berada pada kelas yang Anda ampu.');
        }

        // B) mapel harus diajar guru & match kelas siswa
        $gmOk = $db->table('tb_guru_mapel')
            ->where('id_guru', $guruId)
            ->where('id_mapel', $payload['mapel_id'])
            ->where('id_kelas', (int)$siswa['kelas_id'])
            ->countAllResults();
        if ($gmOk < 1) {
            return redirect()->back()->withInput()->with('sweet_error', 'Anda tidak mengajar mapel tersebut di kelas siswa ini.');
        }

        // C) tahun ajaran & kategori harus valid
        $taOk  = $db->table('tb_tahun_ajaran')->where('id_tahun_ajaran', $payload['tahun_ajaran_id'])->countAllResults();
        $katOk = $db->table('tb_kategori_nilai')->where('id_kategori', $payload['kategori_id'])->countAllResults();
        if ($taOk < 1 || $katOk < 1) {
            return redirect()->back()->withInput()->with('sweet_error', 'Tahun ajaran/kategori tidak valid.');
        }

        // D) CEGAH DUPLIKASI: sudah ada nilai dengan kombinasi siswa–mapel–TA–(kategori)?
        $dupQ = $db->table('tb_nilai_siswa')
            ->where('siswa_id', $payload['siswa_id'])
            ->where('mapel_id', $payload['mapel_id'])
            ->where('tahun_ajaran_id', $payload['tahun_ajaran_id'])
            ->where('kategori_id', $payload['kategori_id']) // jika mau longgar, hapus baris ini untuk hanya per mapel+TA
            ->countAllResults();
        if ($dupQ > 0) {
            return redirect()->back()->withInput()->with('sweet_error', 'Nilai untuk siswa ini pada mapel, TA, dan kategori tersebut sudah ada.');
        }

        // ===== 3) Insert =====
        $nilaiModel = new \App\Models\NilaiSiswaModel();
        try {
            $nilaiModel->insert([
                'siswa_id'        => $payload['siswa_id'],
                'tahun_ajaran_id' => $payload['tahun_ajaran_id'],
                'mapel_id'        => $payload['mapel_id'],
                'kategori_id'     => $payload['kategori_id'],
                'skor'            => $payload['skor'],
                'tanggal'         => $payload['tanggal'],
                'keterangan'      => $payload['keterangan'],
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('sweet_error', 'Gagal menyimpan nilai. Coba lagi.');
        }

        // Redirect kembali ke daftar nilai membawa filter
        $qs = http_build_query([
            'tahunajaran' => (string)$payload['tahun_ajaran_id'],
            'mapel'       => (string)$payload['mapel_id'],
        ]);
        return redirect()->to(base_url('guru/laporan-nilai-siswa'))
            ->with('sweet_success', 'Nilai berhasil disimpan.');
    }


    public function page_profile()
    {
        $s = session();

        // pastikan login & role guru
        $userId = (int) ($s->get('id_user') ?? 0);
        $role   = (string) ($s->get('role') ?? '');

        if ($userId === 0) {
            $s->setFlashdata('sweet_error', 'Sesi berakhir. Silakan login kembali.');
            return redirect()->to(base_url('auth/logout'));
        }
        if ($role !== 'guru') {
            $s->setFlashdata('sweet_error', 'Akses ditolak. Halaman ini khusus guru.');
            return redirect()->to(base_url('auth/login'));
        }

        $guru = $this->UserModel
            ->select('id_user, username, role, email, is_active, created_at, updated_at')
            ->find($userId);

        if (!$guru) {
            $s->setFlashdata('sweet_error', 'User tidak ditemukan.');
            return redirect()->to(base_url('auth/logout'));
        }

        // Ambil validator yg dikirim via session saat redirect back
        $validation = session('validation') ?? \Config\Services::validation();
        $activeTab  = session('active_tab') ?? 'account'; // default tab

        return view('pages/guru/profile', [
            'title'       => 'Profil Guru | SDN Talun 2 Kota Serang',
            'nav_link'    => 'Profile',
            'sub_judul'   => 'Profil Guru',
            'user'        => $guru,
            'validation'  => $validation,
            'active_tab'  => $activeTab,
        ]);
    }
    public function aksi_update_username()
    {
        $session = session();

        // Cek login & role
        $uid  = (int) ($session->get('id_user') ?? 0);
        $role = (string) ($session->get('role') ?? '');
        if ($uid === 0) {
            return redirect()->to(base_url('auth/logout'))
                ->with('sweet_error', 'Sesi berakhir. Silakan login kembali.');
        }
        if ($role !== 'guru') {
            return redirect()->to(base_url('auth/login'))
                ->with('sweet_error', 'Akses ditolak. Halaman ini khusus guru.');
        }

        $user = $this->UserModel->find($uid);
        if (!$user) {
            return redirect()->to(base_url('auth/logout'))
                ->with('sweet_error', 'User tidak ditemukan.');
        }

        // Ambil input
        $username = trim((string) $this->request->getPost('username'));
        $email    = trim((string) $this->request->getPost('email'));

        // ——— RULES ———
        // Ganti `users` & `id_user` sesuai skema kamu.
        $rules = [
            'username' => [
                'label'  => 'Username',
                'rules'  => "required|min_length[4]|max_length[24]|regex_match[/^[A-Za-z0-9._]+$/]"
                    . "|is_unique[tb_users.username,id_user,{$uid}]",
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'min_length'  => '{field} minimal {param} karakter.',
                    'max_length'  => '{field} maksimal {param} karakter.',
                    'regex_match' => '{field} hanya boleh huruf, angka, titik, atau underscore.',
                    'is_unique'   => '{field} sudah digunakan.',
                ],
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => "required|valid_email|is_unique[tb_users.email,id_user,{$uid}]",
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'valid_email' => '{field} tidak valid.',
                    'is_unique'  => '{field} sudah digunakan.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            // kirim validator ke session agar ditangkap view
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('sweet_error', 'Periksa kembali input Anda.')
                ->with('active_tab', 'account');
        }

        // (Opsional) Normalisasi ringan
        $payload = [
            'username'   => $username,
            'email'      => strtolower($email),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $this->UserModel->update($uid, $payload);
            // sinkronkan session agar header/nav langsung ikut berubah
            $session->set('username', $payload['username']);
            $session->set('email', $payload['email']);
        } catch (\Throwable $e) {
            log_message('error', 'Update username/email gagal: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('validation', \Config\Services::validation())
                ->with('sweet_error', 'Gagal menyimpan perubahan.')
                ->with('active_tab', 'account');
        }

        $session->regenerate(); // keamanan

        return redirect()->to(base_url('guru/profile'))
            ->with('sweet_success', 'Data akun berhasil diperbarui.')
            ->with('active_tab', 'account');
    }

    public function aksi_update_password()
    {
        $session = session();

        // cek login & role
        $uid  = (int) ($session->get('id_user') ?? 0);
        $role = (string) ($session->get('role') ?? '');
        if ($uid === 0) {
            return redirect()->to(base_url('auth/logout'))->with('sweet_error', 'Sesi berakhir. Silakan login kembali.');
        }
        if ($role !== 'guru') {
            return redirect()->to(base_url('auth/login'))->with('sweet_error', 'Akses ditolak. Halaman ini khusus guru.');
        }

        $user = $this->UserModel->find($uid);
        if (!$user) {
            return redirect()->to(base_url('auth/logout'))->with('sweet_error', 'User tidak ditemukan.');
        }

        // RULES
        $rules = [
            'password' => [
                'label'  => 'Password Saat Ini',
                'rules'  => 'required',
                'errors' => ['required' => '{field} wajib diisi.'],
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

        if (!$this->validate($rules)) {
            // KIRIM validator → session agar bisa dibaca di view
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('sweet_error', 'Periksa kembali input Anda.')
                ->with('active_tab', 'security');
        }

        $curr = (string) $this->request->getPost('password');
        $new  = (string) $this->request->getPost('new_password');

        // VERIFIKASI PASSWORD SAAT INI
        $hashFromDb = (string) ($user['password'] ?? $user['password_hash'] ?? '');
        if ($hashFromDb === '' || !password_verify($curr, $hashFromDb)) {
            $validation = \Config\Services::validation();
            $validation->setError('password', 'Password saat ini salah.');
            return redirect()->back()
                ->withInput()
                ->with('validation', $validation)
                ->with('active_tab', 'security');
        }

        // HASH PASSWORD BARU
        $algo    = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $newHash = password_hash($new, $algo);

        try {
            $this->UserModel->update($uid, [
                'password'   => $newHash, // atau 'password_hash' => $newHash
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Update password gagal: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('validation', \Config\Services::validation()) // agar tidak null di view
                ->with('sweet_error', 'Gagal memperbarui password.')
                ->with('active_tab', 'security');
        }

        $session->regenerate();

        return redirect()->to(base_url('guru/profile'))
            ->with('sweet_success', 'Password berhasil diperbarui.')
            ->with('active_tab', 'security');
    }
}
