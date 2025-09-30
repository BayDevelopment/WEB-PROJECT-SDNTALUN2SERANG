<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// ---------- AUTH (HANYA UNTUK GUEST) ----------
// ✅ Pisahkan logout dari guest
$routes->get('/', function () {
    return redirect()->to('auth/login');
});

$routes->group('auth', static function ($routes) {

    // hanya tamu yang boleh akses halaman login
    $routes->group('', ['filter' => 'guest'], static function ($routes) {
        $routes->get('login', 'LoginController::index', ['as' => 'auth.login']);
        $routes->post('login', 'LoginController::aksi_login');
    });

    // logout untuk user yang sudah login
    $routes->get('logout', 'LoginController::logout', ['filter' => 'auth']);
});



$routes->group('siswa', ['filter' => ['auth', 'role:siswa']], static function ($routes) {
    $routes->get('dashboard', 'SiswaController::index');
    $routes->get('profile', 'SiswaController::profile');
});


// (opsional) konsistenkan juga untuk operator & guru
$routes->group('guru', ['filter' => ['auth', 'role:guru']], static function ($routes) {
    $routes->get('dashboard', 'GuruController::index',   ['as' => 'guru_dashboard']);
    $routes->get('profile',  'GuruController::profile', ['as' => 'guru_profile']);
});

$routes->group('operator', ['filter' => ['auth', 'role:operator']], static function ($routes) {
    $routes->get('dashboard', 'OperatorController::index');
    $routes->get('data-siswa',  'OperatorController::Data_siswa');
    $routes->get('tambah-siswa',  'OperatorController::page_tambah_siswa');
    $routes->post('tambah-siswa',  'OperatorController::aksi_insert_siswa');
    $routes->get('edit-siswa/(:num)',  'OperatorController::page_edit_siswa/$1');
    $routes->PUT('edit-siswa/(:num)',  'OperatorController::aksi_update_siswa/$1');
    $routes->get('detail-siswa/(:num)',  'OperatorController::page_detail_siswa/$1');
    $routes->get('data-siswa/delete/(:num)',  'OperatorController::aksi_delete_siswa/$1');
    $routes->get('profile',  'OperatorController::page_profile');
    $routes->post('profile',  'OperatorController::aksi_update_profile');
    $routes->post('profile/password',  'OperatorController::aksi_update_password');
    // data user
    $routes->get('data-user',  'OperatorController::data_user');
    $routes->get('tambah-user',  'OperatorController::page_tambah_user');
    $routes->post('tambah-user',  'OperatorController::aksi_insert_user');

    // data guru
    $routes->get('data-guru',  'OperatorController::data_guru');
    $routes->get('tambah-guru',  'OperatorController::page_tambah_guru');
    $routes->post('tambah-guru',  'OperatorController::aksi_tambah_guru');
});
