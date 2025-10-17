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
    // $routes->get('logout', 'LoginController::logout', ['filter' => 'auth']);
    $routes->post('logout', 'LoginController::logout', ['filter' => 'auth']);
});



$routes->group('siswa', ['filter' => ['auth', 'role:siswa']], static function ($routes) {
    $routes->get('dashboard', 'SiswaController::index');
    $routes->get('profile', 'SiswaController::profile');
    $routes->get('data-diri', 'SiswaController::data_diri');
    $routes->get('data-guru', 'SiswaController::data_guru');
    $routes->get('nilai-siswa', 'SiswaController::nilai_siswa');
    $routes->post('profile/username', 'SiswaController::updateUsername');
    $routes->post('profile/password', 'SiswaController::updatePassword');
});


// (opsional) konsistenkan juga untuk operator & guru
$routes->group('guru', ['filter' => ['auth', 'role:guru']], static function ($routes) {
    $routes->get('dashboard', 'GuruController::index');
    $routes->get('data-siswa', 'GuruController::Data_siswa');
    $routes->get('detail-siswa/(:num)', 'GuruController::page_detail_siswa/$1');
    $routes->get('laporan-siswa', 'GuruController::page_laporan_d_siswa');
    $routes->get('laporan-nilai-siswa', 'GuruController::page_laporan_nilai_siswa');
    $routes->get('laporan/tambah-nilai', 'GuruController::page_tambah_nilai');
    $routes->post('laporan/tambah-nilai', 'GuruController::aksi_tambah_nilai');
    $routes->get('profile',  'GuruController::page_profile');
    $routes->post('profile/username',  'GuruController::aksi_update_username');
    $routes->post('profile/password',  'GuruController::aksi_update_password');
});

$routes->group('operator', ['filter' => ['auth', 'role:operator']], static function ($routes) {
    $routes->get('dashboard', 'OperatorController::index');
    $routes->get('data-siswa',  'OperatorController::Data_siswa');
    $routes->get('tambah-siswa',  'OperatorController::page_tambah_siswa');
    $routes->post('tambah-siswa',  'OperatorController::aksi_insert_siswa');
    $routes->get('edit-siswa/(:num)',  'OperatorController::page_edit_siswa/$1');
    $routes->PUT('edit-siswa/(:num)',  'OperatorController::aksi_update_siswa/$1');
    $routes->get('detail-siswa/(:num)',  'OperatorController::page_detail_siswa/$1');
    $routes->post('detail-siswa',  'OperatorController::aksi_edit_detail_siswa');
    $routes->get('data-siswa/delete/(:num)',  'OperatorController::aksi_delete_siswa/$1');
    $routes->get('profile',  'OperatorController::page_profile');
    $routes->post('profile',  'OperatorController::aksi_update_profile');
    $routes->post('profile/password',  'OperatorController::aksi_update_password');
    // data user
    $routes->get('data-user',  'OperatorController::data_user');
    $routes->get('tambah-user',  'OperatorController::page_tambah_user');
    $routes->post('tambah-user',  'OperatorController::aksi_insert_user');
    $routes->get('edit-user/(:num)',  'OperatorController::page_edit_user/$1');
    $routes->PUT('edit-user/(:num)',  'OperatorController::aksi_update_user/$1');
    $routes->get('detail-user/(:num)',  'OperatorController::page_detail_user/$1');
    $routes->get('data-user/delete/(:num)',  'OperatorController::aksi_delete_data_user/$1');

    // data guru
    $routes->get('data-guru',  'OperatorController::data_guru');
    $routes->get('tambah-guru',  'OperatorController::page_tambah_guru');
    $routes->post('tambah-guru',  'OperatorController::aksi_tambah_guru');
    $routes->get('edit-guru/(:num)',  'OperatorController::page_edit_guru/$1');
    $routes->PUT('edit-guru/(:num)',  'OperatorController::aksi_update_guru/$1');
    $routes->get('detail-guru/(:num)',  'OperatorController::page_detail_guru/$1');
    $routes->PUT('detail-guru/(:num)/update', 'OperatorController::aksi_detail_update_guru_mapel/$1');
    $routes->delete('detail-guru/(:num)/delete-all', 'OperatorController::aksi_hapus_semua_guru_mapel/$1');
    $routes->get('data-guru/delete/(:num)',  'OperatorController::delete_data_guru/$1');

    // data mapel
    $routes->get('matpel',  'OperatorController::data_matpel');
    $routes->get('matpel/tambah',  'OperatorController::page_tambah_matpel');
    $routes->post('matpel/tambah',  'OperatorController::aksi_insert_matpel');
    $routes->get('matpel/edit/(:num)',  'OperatorController::page_edit_matpel/$1');
    $routes->PUT('matpel/edit/(:num)',  'OperatorController::aksi_update_matpel/$1');
    $routes->get('matpel/detail/(:num)',  'OperatorController::page_detail_matpel/$1');
    $routes->get('matpel/delete/(:num)',  'OperatorController::aksi_delete_matpel/$1');

    // DATA TAHUN AJARAN
    $routes->get('tahun-ajaran',  'OperatorController::data_tahun_ajaran');
    $routes->get('tambah/tahun-ajaran',  'OperatorController::tambah_tahun_ajaran');
    $routes->post('tambah/tahun-ajaran',  'OperatorController::aksi_tahun_ajaran');
    $routes->get('edit/tahun-ajaran/(:num)',  'OperatorController::page_edit_tahun_ajaran/$1');
    $routes->PUT('edit/tahun-ajaran/(:num)',  'OperatorController::aksi_edit_tahun_ajaran/$1');
    $routes->get('detail/tahun-ajaran/(:num)',  'OperatorController::page_edit_detail_tahun_ajaran/$1');
    $routes->get('tahun-ajaran/delete/(:num)', 'OperatorController::delete_tahun_ajaran/$1');

    // GURU MAPEL
    $routes->get('guru-mapel/tambah/(:segment)', 'OperatorController::page_tambah_guru_mapel/$1');
    $routes->post('guru-mapel/tambah', 'OperatorController::aksi_tambah_guru_mapel');
    $routes->get('guru-mapel/edit/(:num)', 'OperatorController::page_edit_guru_mapel/$1');
    $routes->PUT('guru-mapel/edit/(:num)', 'OperatorController::aksi_update_guru_mapel/$1');
    $routes->get('guru-mapel/delete/(:num)', 'OperatorController::aksi_delete_guru_mapel/$1');
    $routes->get('penugasan-guru', 'OperatorController::data_penugasan');

    // DATA KELAS
    $routes->get('kelas', 'OperatorController::data_kelas');
    $routes->get('kelas/tambah', 'OperatorController::page_tambah_kelas');
    $routes->post('kelas/tambah', 'OperatorController::aksi_insert_kelas');
    $routes->get('kelas/edit/(:num)', 'OperatorController::page_edit_kelas/$1');
    $routes->post('kelas/edit/(:num)', 'OperatorController::aksi_update_kelas/$1');
    $routes->get('kelas/detail/(:num)', 'OperatorController::page_detail_kelas/$1');
    $routes->get('kelas/delete/(:num)', 'OperatorController::aksi_delete_kelas/$1');

    // Laporan Data Siswa
    $routes->get('laporan/siswa', 'OperatorController::page_laporan_d_siswa');
    $routes->get('tambah-laporan/siswa/(:num)', 'OperatorController::page_tambah_laporan_siswa/$1');
    $routes->post('tambah-laporan/siswa', 'OperatorController::aksi_laporan_data_siswa');
    $routes->get('laporan-siswa/delete/(:num)', 'OperatorController::aksi_delete_laporan_siswa/$1');


    // Laporan Data Guru
    $routes->get('laporan/guru', 'OperatorController::page_laporan_guru');
    $routes->get('tambah-laporan/guru/(:num)', 'OperatorController::page_tambah_laporan_guru/$1');
    $routes->post('tambah-laporan/guru', 'OperatorController::aksi_laporan_data_guru');
    $routes->get('laporan-guru/delete/(:num)', 'OperatorController::aksi_delete_laporan_guru/$1');

    // Laporan data nilai siswa
    $routes->get('laporan/nilai-siswa', 'OperatorController::page_laporan_nilai_siswa');
    $routes->get('laporan/tambah-nilai', 'OperatorController::page_tambah_nilai_siswa');
    $routes->post('laporan/tambah-nilai', 'OperatorController::aksi_tambah_nilai_siswa');
    $routes->get('laporan/edit-nilai/(:num)', 'OperatorController::page_edit_nilai_siswa/$1');
    $routes->post('laporan/edit-nilai/(:num)', 'OperatorController::aksi_edit_nilai_siswa/$1');
    $routes->get('laporan/nilai-siswa/delete/(:segment)', 'OperatorController::aksi_delete_nilai_siswa/$1');

    // Kategori
    $routes->get('kategori/tambah', 'OperatorController::page_tambah_kategori');
    $routes->post('kategori/tambah', 'OperatorController::aksi_tambah_kategori');
    $routes->get('kategori/delete/(:num)', 'OperatorController::aksi_delete/$1');
});
