<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class LoginController extends BaseController
{
    protected $SiswaModel;
    protected $UserModel;
    public function __construct()
    {
        $this->SiswaModel = new SiswaModel();
        $this->UserModel = new UserModel();
    }
    public function index()
    {
        $data = [
            'title' => 'Login | SDN Talun 2 Kota Serang'
        ];
        return view('index', $data);
    }

    public function aksi_login()
    {
        $req  = $this->request;
        $sess = session();

        $username = trim((string) $req->getPost('username'));
        $password = (string) $req->getPost('password');

        // --- Validasi singkat
        if ($username === '' || $password === '') {
            $sess->setFlashdata('sweet_error', 'Username dan password wajib diisi.');
            return redirect()->to(site_url('auth/login'))->withInput();
        }
        if (strlen($password) < 8) {
            $sess->setFlashdata('sweet_error', 'Password minimal 8 karakter.');
            return redirect()->to(site_url('auth/login'))->withInput();
        }

        // --- Rate-limit sederhana (maks 5 salah per username di sesi)
        $attemptKey = 'login_attempts:' . strtolower($username);
        $attempts   = (int) ($sess->get($attemptKey) ?? 0);
        if ($attempts >= 5) {
            $sess->setFlashdata('sweet_error', 'Terlalu banyak percobaan gagal. Coba lagi nanti.');
            return redirect()->to(site_url('auth/login'))->withInput();
        }

        // --- Ambil user
        $user = (new \App\Models\UserModel())
            ->where('username', $username)
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            $attempts++;
            $sess->set($attemptKey, $attempts);
            $left = max(0, 5 - $attempts);
            $sess->setFlashdata('sweet_error', $user ? "Password salah. Sisa kesempatan: {$left}" : 'Username atau password salah.');
            return redirect()->to(site_url('auth/login'))->withInput();
        }

        // --- Cek aktif
        if ((int) ($user['is_active'] ?? 0) === 0) {
            $sess->setFlashdata('sweet_error', 'Maaf, akun Anda tidak aktif.');
            return redirect()->to(site_url('auth/login'))->withInput();
        }

        // --- (Opsional) ambil data siswa bila perlu
        $siswa = null;
        if (($user['role'] ?? '') === 'siswa') {
            $siswa = (new \App\Models\SiswaModel())
                ->where('user_id', (int) $user['id_user'])
                ->first();
        }

        // --- Sukses login
        $sess->remove($attemptKey);
        $sess->regenerate(); // anti session fixation

        $sess->set([
            'logged_in'     => true,
            'id_user'       => (int) $user['id_user'],
            'username'      => (string) $user['username'],
            'role'          => (string) $user['role'],   // 'operator' | 'guru' | 'siswa'
            'email'         => (string) $user['email'],
            'is_active'     => (int) $user['is_active'],
            'siswa'         => $siswa,
            'last_activity' => time(),
        ]);

        $sess->setFlashdata('sweet_success', 'Login berhasil, selamat datang!');

        // --- Redirect sesuai role (satu peta, beres)
        $redirectMap = [
            'operator' => 'operator/dashboard',
            'guru'     => 'guru/dashboard',
            'siswa'    => 'siswa/dashboard',
        ];
        $role = (string) ($user['role'] ?? '');
        $path = $redirectMap[$role] ?? 'siswa/dashboard'; // default ke siswa

        return redirect()->to(site_url($path));
    }


    private function bumpAttempts(string $attemptKey): void
    {
        $s = session();
        $s->set($attemptKey, (int)($s->get($attemptKey) ?? 0) + 1);
    }

    private function setAuthCookies(array $user): void
    {
        $resp = $this->response;
        $req  = $this->request;

        $ttl      = 60 * 30; // 30 menit
        $secure   = $req->isSecure(); // true di HTTPS
        $httpOnly = true;

        // Jangan plaintext: token turunan dari hash+UA (tetap disimpan di key "password")
        $passwordToken = hash('sha256', $user['password'] . '|' . (string)$req->getUserAgent());

        $resp->setCookie('id_user',   (string)$user['id_user'], $ttl, '', '/', '', $secure, $httpOnly, 'Lax');
        $resp->setCookie('username',  (string)$user['username'], $ttl, '', '/', '', $secure, $httpOnly, 'Lax');
        $resp->setCookie('password',  (string)$passwordToken,    $ttl, '', '/', '', $secure, $httpOnly, 'Lax');
        $resp->setCookie('role',      (string)$user['role'],     $ttl, '', '/', '', $secure, $httpOnly, 'Lax');
        $resp->setCookie('email',     (string)$user['email'],    $ttl, '', '/', '', $secure, $httpOnly, 'Lax');
        $resp->setCookie('is_active', (string)$user['is_active'], $ttl, '', '/', '', $secure, $httpOnly, 'Lax');
    }

    public function logout()
    {
        // hapus data & cookie
        helper('cookie');
        delete_cookie('id_user');
        delete_cookie('username');
        delete_cookie('password');
        delete_cookie('role');
        delete_cookie('email');
        delete_cookie('is_active');

        $sess = session();
        $sess->remove(['logged_in', 'user_id', 'username', 'role', 'email', 'is_active', 'siswa', 'last_activity']);
        $sess->regenerate(true); // ganti session id + kill session lama (anti-fixation)

        // set flash di session yang AKTIF
        $sess->setFlashdata('sweet_success', 'Selamat, berhasil logout!');
        return redirect()->to('/auth/login');
    }
}
