<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
    /* nowrap ke semua sel jika diperlukan */
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>
<div class="container-fluid px-4 page-section fade-in-up delay-300">
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
                        <!-- Search -->
                        <div class="col-12 col-md-3">
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

                        <!-- Gender -->
                        <div class="col-12 col-md-3">
                            <select id="filterGender" name="gender" class="form-select form-select-sm" aria-label="Filter gender">
                                <?php $g = $gender ?? ''; ?>
                                <option value="" <?= $g === '' ? 'selected' : '' ?>>Semua Gender</option>
                                <option value="L" <?= $g === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $g === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <!-- Tahun Ajaran -->
                        <div class="col-12 col-md-3">
                            <select id="filterTA" name="tahunajaran" class="form-select form-select-sm" aria-label="Filter Tahun Ajaran">
                                <option value="">Semua Tahun Ajaran</option>
                                <?php foreach ($listTA as $ta): ?>
                                    <option value="<?= esc($ta['id_tahun_ajaran']) ?>"
                                        <?= ($tahunajaran ?? '') == $ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                                        <?= esc($ta['tahun']) ?> - Semester <?= esc(ucfirst($ta['semester'])) ?>
                                        <?= $ta['is_active'] ? ' (Aktif)' : '' ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <!-- Kelas (NEW) -->
                        <div class="col-12 col-md-3">
                            <select id="filterKelas" name="kelas" class="form-select form-select-sm" aria-label="Filter Kelas">
                                <?php $kSel = $kelas ?? ''; ?>
                                <option value="" <?= $kSel === '' ? 'selected' : '' ?>>Semua Kelas</option>
                                <?php if (!empty($listKelas)): ?>
                                    <?php foreach ($listKelas as $k): ?>
                                        <option value="<?= esc($k['id_kelas']) ?>" <?= $kSel == $k['id_kelas'] ? 'selected' : '' ?>>
                                            <?= esc($k['nama_kelas'] ?? ('Kelas #' . $k['id_kelas'])) ?>
                                        </option>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <option value="" disabled>(Kelas belum tersedia)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('filterForm');
                            const selects = ['filterGender', 'filterTA', 'filterKelas']
                                .map(id => document.getElementById(id))
                                .filter(Boolean);

                            // Auto submit ketika select berubah
                            selects.forEach(el => el.addEventListener('change', () => form.submit()));

                            // Optional: submit saat tekan Enter di kolom pencarian
                            const search = document.getElementById('searchSiswa');
                            if (search) {
                                search.addEventListener('keydown', function(e) {
                                    if (e.key === 'Enter') form.submit();
                                });
                            }
                        });
                    </script>

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
                                $t = _indo_parse_time($val, 'Asia/Jakarta'); // helper kamu
                                return $t ? $t->format('d/m/Y') : '—';
                            };

                            foreach ($d_siswa as $d_s):
                                $id   = (int)($d_s['id_siswa_tahun'] ?? '');
                                $nisn = (string)($d_s['nisn'] ?? '');
                                $nama = (string)($d_s['nama_lengkap'] ?? $d_s['full_name'] ?? '');

                                // ====== Gender → tag + badge ======
                                $gndrRaw = (string)($d_s['gender'] ?? '');
                                $gndr    = mb_strtolower($gndrRaw, 'UTF-8');

                                $jkRaw = (string)($d_s['gender'] ?? $d_s['jk'] ?? $d_s['jenis_kelamin'] ?? '');
                                $jk    = strtoupper(trim($jkRaw));
                                if ($jk !== '' && strlen($jk) > 1) {
                                    $jk = substr($jk, 0, 1);
                                }

                                if ($jk === 'L') {
                                    $jkLabel = 'Laki-laki';
                                    $badgeGender = 'badge bg-primary';
                                } elseif ($jk === 'P') {
                                    $jkLabel = 'Perempuan';
                                    $badgeGender = 'badge bg-danger';
                                } else {
                                    $jkLabel = ($jkRaw !== '' ? $jkRaw : '—');
                                    $badgeGender = 'badge bg-secondary';
                                }

                                // ====== Status (PATEN: hanya 'aktif' | 'lulus' | 'keluar') ======
                                // Terima input bervariasi → distandarkan ke 3 nilai model
                                $stRaw = mb_strtolower((string)($d_s['status'] ?? ''), 'UTF-8');
                                $stRaw = trim($stRaw);

                                // Jika ada tanggal_keluar, default ke "keluar" (fallback aman)
                                $hasExitDate = !empty($d_s['tanggal_keluar']);

                                // Kamus mapping longgar -> baku
                                $mapAktif = ['1', 'aktif', 'active', 'ya', 'true', 'ongoing', 'enrolled'];
                                $mapLulus = ['2', 'lulus', 'graduated', 'graduate', 'wisuda', 'kelulusan'];
                                $mapKeluar = ['3', 'keluar', 'drop out', 'do', 'pindah', 'nonaktif', 'non-active', 'false', 'tidak', 'resign', 'cutoff'];

                                if (in_array($stRaw, $mapAktif, true)) {
                                    $statusModel = 'aktif';
                                } elseif (in_array($stRaw, $mapLulus, true)) {
                                    $statusModel = 'lulus';
                                } elseif (in_array($stRaw, $mapKeluar, true)) {
                                    $statusModel = 'keluar';
                                } elseif ($hasExitDate) {
                                    // fallback logis jika ada tanggal keluar tapi status tak jelas
                                    $statusModel = 'keluar';
                                } else {
                                    // fallback netral → anggap aktif (atau ganti ke 'keluar' jika kebijakanmu berbeda)
                                    $statusModel = 'aktif';
                                }

                                // Label & badge final
                                if ($statusModel === 'aktif') {
                                    $statusText  = 'Aktif';
                                    $badgeStatus = 'badge bg-success';
                                } elseif ($statusModel === 'lulus') {
                                    $statusText  = 'Lulus';
                                    $badgeStatus = 'badge bg-info';
                                } else { // 'keluar'
                                    $statusText  = 'Keluar';
                                    $badgeStatus = 'badge bg-secondary';
                                }

                                // ====== Tanggal masuk / keluar ======
                                $tMasukFmt  = $fmtDMY($d_s['tanggal_masuk']  ?? null);
                                $tKeluarFmt = $fmtDMY($d_s['tanggal_keluar'] ?? null);
                            ?>
                                <tr data-name="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>"
                                    data-nisn="<?= esc($nisn) ?>"
                                    data-gender="<?= esc($gndr) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td><span class="font-monospace"><?= esc($nisn) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td><span class="<?= esc($badgeGender) ?>"><?= esc($jkLabel) ?></span></td>
                                    <td><span class="<?= esc($badgeStatus) ?>"><?= esc($statusText) ?></span></td>
                                    <td><?= esc(format_ddmmyyyy_ke_tanggal_indo($tMasukFmt)) ?></td>
                                    <td><?= esc(format_ddmmyyyy_ke_tanggal_indo($tKeluarFmt)) ?></td>
                                    <td>
                                        <a href="#" class="btn btn-outline-danger btn-sm"
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
        const selTA = document.getElementById('filterTA');

        let t = null;
        const debSubmit = () => {
            clearTimeout(t);
            t = setTimeout(() => form.submit(), 350);
        };

        if (inpQ) {
            inpQ.addEventListener('input', debSubmit);
            inpQ.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }
        if (selG) selG.addEventListener('change', () => form.submit());
        if (selTA) selTA.addEventListener('change', () => form.submit());
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