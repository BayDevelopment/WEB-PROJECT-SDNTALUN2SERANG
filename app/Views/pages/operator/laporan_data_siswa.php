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
            </div>
            <!-- Tabel -->
            <?php if (!empty($d_siswa)): ?>
                <div class="table-responsive">
                    <table id="tableDataSiswaLaporan" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Status</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableSiswa">
                            <?php
                            $no = 1;

                            $fmtDMY = function ($val): string {
                                $t = _indo_parse_time($val, 'Asia/Jakarta'); // helper yang sudah kamu buat
                                return $t ? $t->format('d/m/Y') : '—';
                            };

                            foreach ($d_siswa as $d_s):
                                $id = (int)($d_s['id_siswa_tahun'] ?? '');
                                $nisn = (string)($d_s['nisn'] ?? '');
                                // konsisten: pakai 'nama_lengkap' (atau ganti semua ke 'full_name' kalau itu yang di DB)
                                $nama = (string)($d_s['nama_lengkap'] ?? $d_s['full_name'] ?? '');

                                // Gender → tag + badge
                                $gndrRaw = (string)($d_s['gender'] ?? '');
                                $gndr    = mb_strtolower($gndrRaw, 'UTF-8');
                                $tag     = (str_contains($gndr, 'laki') || $gndr === 'l') ? 'Laki-laki'
                                    : ((str_contains($gndr, 'perem') || $gndr === 'p') ? 'Perempuan'
                                        : ($gndrRaw !== '' ? $gndrRaw : '—'));
                                // Pastikan class ini ada di CSS kamu; kalau tidak, pakai badge bootstrap standar
                                $badgeGender = ($tag === 'Laki-laki') ? 'badge bg-primary'
                                    : (($tag === 'Perempuan') ? 'badge bg-pink' : 'badge bg-secondary');

                                // Status enrol (dari tabel tahunan: $d_s['status'])
                                $stRaw = mb_strtolower((string)($d_s['status'] ?? ''), 'UTF-8');
                                $isEnrolActive = in_array($stRaw, ['1', 'aktif', 'active', 'ya', 'true'], true);
                                $statusText  = $isEnrolActive ? 'Aktif' : 'Nonaktif';
                                $badgeStatus = $isEnrolActive ? 'badge bg-success' : 'badge bg-secondary';

                                // Tanggal masuk / keluar → FORMAT LANGSUNG dari RAW
                                $tMasukFmt  = $fmtDMY($d_s['tanggal_masuk']  ?? null);
                                $tKeluarFmt = $fmtDMY($d_s['tanggal_keluar'] ?? null);
                            ?>
                                <tr data-name="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>"
                                    data-nisn="<?= esc($nisn) ?>"
                                    data-gender="<?= esc($gndr) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td><span class="font-monospace"><?= esc($nisn) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td><span class="<?= esc($badgeGender) ?>"><?= esc($tag) ?></span></td>
                                    <td><span class="<?= esc($badgeStatus) ?>"><?= esc($statusText) ?></span></td>
                                    <td><?= esc($tMasukFmt) ?></td>
                                    <td><?= esc($tKeluarFmt) ?></td>
                                    <td>
                                        <a href="#" class="btn btn-outline-danger"
                                            onclick="confirmDeleteLapSiswa('<?= esc($id, 'js') ?>')"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
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
                    <a href="<?= base_url('operator/data-siswa') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
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

    function confirmDeleteLapSiswa(idOrNisn) {
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?= base_url('operator/laporan-siswa/delete/') ?>" + encodeURIComponent(String(idOrNisn));
            }
        });
    }
</script>
<?= $this->endSection() ?>