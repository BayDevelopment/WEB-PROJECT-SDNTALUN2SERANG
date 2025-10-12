<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
    /* nowrap ke semua sel jika diperlukan */
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>
<div class="container-fluid px-4 page-section">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul) ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>
        <div class="text-muted small mt-3 mt-sm-0">
            Total Siswa: <strong><?= number_format($totalSiswa ?? 0, 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Aktif: <strong class="text-success"><?= number_format($SiswaAktif ?? 0, 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Nonaktif: <strong class="text-muted"><?= number_format($SiswaNonAktif ?? 0, 0, ',', '.') ?></strong>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input
                                    id="searchSiswa"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari nama atau NISN..."
                                    aria-label="Pencarian nama atau NISN"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <select id="filterGender" name="gender" class="form-select form-select-sm" aria-label="Filter gender">
                                <?php $g = $gender ?? ''; ?>
                                <option value="" <?= $g === '' ? 'selected' : '' ?>>Semua Gender</option>
                                <option value="L" <?= $g === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $g === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah (di luar form) -->
                <?php if (!empty($d_siswa)): ?>
                    <div class="col-12 col-md-3 text-md-end">
                        <a href="<?= base_url('operator/tambah-siswa') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-md-auto">
                            <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Tabel -->
            <?php if (!empty($d_siswa)): ?>
                <div class="table-responsive">
                    <table id="tableDataSiswa" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Foto</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Gender</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableSiswa">
                            <?php $no = 1;
                            foreach ($d_siswa as $d_s):
                                $foto = trim((string)($d_s['photo'] ?? ''));
                                $img  = $foto !== '' ? base_url('assets/img/uploads/' . esc($foto))
                                    : base_url('assets/img/user.png');
                                $nisn = (string)($d_s['nisn'] ?? '');
                                $nama = (string)($d_s['full_name'] ?? '');
                                $gndr = strtolower((string)($d_s['gender'] ?? ''));
                                $tag  = (str_contains($gndr, 'laki') || $gndr === 'l') ? 'Laki-laki'
                                    : ((str_contains($gndr, 'perem') || $gndr === 'p') ? 'Perempuan' : ($d_s['gender'] ?? '—'));
                                $badgeClass = ($tag === 'Laki-laki') ? 'badge-male' : (($tag === 'Perempuan') ? 'badge-female' : 'badge-unknown');
                            ?>
                                <tr data-name="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>"
                                    data-nisn="<?= esc($nisn) ?>"
                                    data-gender="<?= esc($gndr) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td>
                                        <div class="avatar-wrap">
                                            <img src="<?= $img ?>" alt="Foto <?= esc($nama) ?>" class="avatar-40 rounded-circle">
                                        </div>
                                    </td>
                                    <td><span class="font-monospace"><?= esc($nisn) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($tag) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-outline-danger"
                                                onclick="confirmDeleteSiswa('<?= esc($nisn, 'js') ?>')"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                            <a href="<?= base_url('operator/detail-siswa/' . urlencode($nisn)) ?>" class="btn btn-outline-secondary" title="Detail">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('operator/edit-siswa/' . urlencode($nisn)) ?>" class="btn btn-primary" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <?php
                                            $nisn = (string)($d_s['nisn'] ?? '');
                                            $alreadyIn = ((int)($d_s['laporan_count'] ?? 0) > 0);

                                            $btnClass = 'btn btn-outline-success' . ($alreadyIn ? ' disabled' : '');
                                            $btnTitle = $alreadyIn ? 'Sudah ada di Laporan' : 'Tambahkan ke Laporan';
                                            $aria     = $alreadyIn ? 'aria-disabled="true" tabindex="-1"' : '';
                                            $url      = base_url('operator/tambah-laporan/siswa/' . urlencode($nisn));
                                            ?>
                                            <a href="<?= $url ?>" class="<?= $btnClass ?>" title="<?= esc($btnTitle) ?>" <?= $aria ?>>
                                                <i class="fa-solid fa-file-import"></i>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Empty state -->
                <div class="empty-card text-center p-5">
                    <img src="<?= base_url('assets/img/empty-box.png') ?>" class="empty-illustration mb-3" alt="Kosong">
                    <h5 class="mb-1">Belum ada data siswa</h5>
                    <p class="text-muted mb-3">Tambahkan data siswa pertama Anda untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/tambah-siswa') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inpQ = document.getElementById('searchSiswa');
        const selG = document.getElementById('filterGender');

        // Debounce submit saat mengetik
        let timer = null;
        inpQ.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 350);
        });

        // Submit otomatis saat dropdown berubah
        selG.addEventListener('change', function() {
            form.submit();
        });
    });
</script>
<?= $this->endSection() ?>