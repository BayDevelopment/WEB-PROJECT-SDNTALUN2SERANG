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

        // ===== Tahun Ajaran aktif / terbaru
        $taAktif = $this->TahunAjaran
            ->where('is_active', 'aktif')
            ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
            ->first();
        if (! $taAktif) {
            $taAktif = $this->TahunAjaran
                ->orderBy('tahun', 'DESC')->orderBy('semester', 'DESC')
                ->first();
        }
        $taId = (int)($taAktif['id_tahun_ajaran'] ?? 0);
        $data['ta_aktif'] = $taAktif;

        // ===== Guru Aktif
        $aktifSet = ['1', 'aktif', 'active', 'ya', 'true'];
        if ($taId > 0) {
            $guruCount = (clone $this->GuruTahunanModel)
                ->where('tahun_ajaran_id', $taId)
                ->groupStart()->whereIn('status', $aktifSet)->orWhere('status', 1)->groupEnd()
                ->countAllResults();
        } else {
            $guruCount = (clone $this->ModelGuru)
                ->groupStart()->whereIn('status', $aktifSet)->orWhere('status', 1)->groupEnd()
                ->countAllResults();
        }
        $data['guruCount'] = (int)$guruCount;

        // ===== Siswa aktif per kelas (tb_siswa JOIN tb_users)
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

        // ===== Distribusi Mapel (tb_mapel)
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
        $topNama = '—';
        $topKelas = null;
        if ($taId > 0) {
            try {
                $rowTop = $db->table('tb_nilai_siswa ns')
                    ->select('ns.skor, ns.siswa_id, s.full_name as siswa_nama, ns.tanggal')
                    ->join('tb_siswa s', 's.id_siswa = ns.siswa_id', 'left')
                    ->where('ns.tahun_ajaran_id', $taId)
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

        return view('pages/guru/dashboard_guru', $data);
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
