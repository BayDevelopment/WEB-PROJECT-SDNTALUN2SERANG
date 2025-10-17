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
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Laporan Data Siswa') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('guru/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Laporan Data Siswa') ?></li>
            </ol>
        </div>
        <div class="text-muted small mt-3 mt-sm-0">
            Total: <strong><?= number_format((int)($totalSiswa ?? 0), 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Aktif: <strong class="text-success"><?= number_format((int)($SiswaAktif ?? 0), 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Nonaktif: <strong class="text-muted"><?= number_format((int)($SiswaNonAktif ?? 0), 0, ',', '.') ?></strong>
        </div>
    </div>

    <div class="card card-elevated mb-3 ">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-12">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <!-- Search -->
                        <div class="col-12 col-md-6">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
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

                        <!-- Tahun Ajaran -->
                        <div class="col-6 col-md-2">
                            <?php $tahunajaran = (string)($tahunajaran ?? ''); ?>
                            <select id="filterTA" name="tahunajaran" class="form-select form-select-sm" aria-label="Filter Tahun Ajaran">
                                <option value="" <?= $tahunajaran === '' ? 'selected' : '' ?>>Semua TA</option>
                                <?php foreach (($listTA ?? []) as $ta): ?>
                                    <?php $tid = (string)($ta['id_tahun_ajaran'] ?? ''); ?>
                                    <option value="<?= esc($tid) ?>" <?= $tahunajaran === $tid ? 'selected' : '' ?>>
                                        <?= esc($ta['label'] ?? (($ta['tahun'] ?? '') . ' - Sem ' . ucfirst($ta['semester'] ?? ''))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Kelas (NEW) -->
                        <div class="col-6 col-md-2">
                            <?php $kSel = (string)($kelas ?? ''); ?>
                            <select id="filterKelas" name="kelas" class="form-select form-select-sm" aria-label="Filter Kelas">
                                <option value="" <?= $kSel === '' ? 'selected' : '' ?>>Semua Kelas</option>
                                <?php if (!empty($listKelas)): ?>
                                    <?php foreach ($listKelas as $k): ?>
                                        <?php $kid = (string)($k['id_kelas'] ?? ''); ?>
                                        <option value="<?= esc($kid) ?>" <?= $kSel === $kid ? 'selected' : '' ?>>
                                            <?= esc($k['nama_kelas'] ?? ('Kelas #' . $kid)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>(Kelas belum tersedia)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Gender -->
                        <div class="col-6 col-md-2">
                            <?php $g = (string)($gender ?? ''); ?>
                            <select id="filterGender" name="gender" class="form-select form-select-sm" aria-label="Filter Gender">
                                <option value="" <?= $g === ''  ? 'selected' : '' ?>>Semua</option>
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
                                <th>Status Enrol</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableSiswa">
                            <?php
                            $no = 1;
                            $fmtDMY = function ($val): string {
                                if (empty($val)) return '—';
                                try {
                                    $t = \CodeIgniter\I18n\Time::parse($val, 'Asia/Jakarta');
                                    return $t ? $t->toLocalizedString('dd/MM/yyyy') : '—';
                                } catch (\Throwable $e) {
                                    return '—';
                                }
                            };

                            foreach ($d_siswa as $d_s):
                                $idSiswaTh = (int)($d_s['id_siswa_tahun'] ?? 0);
                                $nisn      = (string)($d_s['nisn'] ?? '');
                                $nama      = (string)($d_s['full_name'] ?? $d_s['nama_lengkap'] ?? '');

                                // === Normalisasi Jenis Kelamin ===
                                $gndrRaw = (string)($d_s['gender'] ?? '');
                                $gndrLow = mb_strtolower(trim($gndrRaw), 'UTF-8');
                                $isL = preg_match('/^(l|laki|lk|male|m)/i', $gndrRaw) || str_contains($gndrLow, 'laki');
                                $isP = preg_match('/^(p|perem|pr|wanita|female|f)/i', $gndrRaw) || str_contains($gndrLow, 'perem');

                                if ($isL) {
                                    $genderFull  = 'Laki-laki';
                                    $badgeGender = 'badge rounded-pill bg-primary';
                                } elseif ($isP) {
                                    $genderFull  = 'Perempuan';
                                    $badgeGender = 'badge rounded-pill bg-danger';
                                } else {
                                    $genderFull  = ($gndrRaw !== '' ? $gndrRaw : 'Tidak diketahui');
                                    $badgeGender = 'badge rounded-pill bg-secondary';
                                }

                                // === Status Enrol → standarisasi ke: Aktif | Lulus | Keluar ===
                                $stRaw         = mb_strtolower((string)($d_s['status'] ?? ''), 'UTF-8');
                                $hasExitDate   = !empty($d_s['tanggal_keluar']);
                                $mapAktif      = ['1', 'aktif', 'active', 'ya', 'true', 'ongoing', 'enrolled'];
                                $mapLulus      = ['2', 'lulus', 'graduated', 'graduate', 'wisuda', 'kelulusan'];
                                $mapKeluar     = ['3', 'keluar', 'drop out', 'do', 'pindah', 'nonaktif', 'non-active', 'false', 'tidak', 'resign', 'cutoff'];

                                if (in_array($stRaw, $mapAktif, true)) {
                                    $statusModel = 'aktif';
                                } elseif (in_array($stRaw, $mapLusus ?? $mapLulus, true)) { // guard typo jika pernah ada
                                    $statusModel = 'lulus';
                                } elseif (in_array($stRaw, $mapKeluar, true)) {
                                    $statusModel = 'keluar';
                                } elseif ($hasExitDate) {
                                    $statusModel = 'keluar';
                                } else {
                                    $statusModel = 'aktif'; // fallback aman
                                }

                                if ($statusModel === 'aktif') {
                                    $statusText  = 'Aktif';
                                    $badgeStatus = 'badge bg-success';
                                } elseif ($statusModel === 'lulus') {
                                    $statusText  = 'Lulus';
                                    $badgeStatus = 'badge bg-info';
                                } else { // keluar
                                    $statusText  = 'Keluar';
                                    $badgeStatus = 'badge bg-secondary';
                                }

                                // === Tanggal ===
                                $tMasukFmt  = $fmtDMY($d_s['tanggal_masuk']  ?? null);
                                $tKeluarFmt = $fmtDMY($d_s['tanggal_keluar'] ?? null);
                            ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td><span class="font-monospace"><?= esc($nisn) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td>
                                        <span class="<?= esc($badgeGender) ?>"
                                            title="<?= esc($genderFull) ?>"
                                            aria-label="Jenis kelamin: <?= esc($genderFull) ?>">
                                            <?= esc($genderFull) ?>
                                        </span>
                                    </td>
                                    <td><span class="<?= esc($badgeStatus) ?>"><?= esc($statusText) ?></span></td>
                                    <td><?= esc(format_ddmmyyyy_ke_tanggal_indo($tMasukFmt)) ?></td>
                                    <td><?= esc(format_ddmmyyyy_ke_tanggal_indo($tKeluarFmt)) ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('guru/detail-siswa/' . urlencode($nisn)) ?>"
                                            class="btn btn-outline-primary btn-sm" title="Detail">
                                            <i class="fa-regular fa-eye"></i>
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
                    <p class="text-muted mb-3">Silakan ubah filter atau tambahkan data siswa.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const search = document.getElementById('searchSiswa');
        const ta = document.getElementById('filterTA');
        const kelas = document.getElementById('filterKelas');
        const gender = document.getElementById('filterGender');

        // Auto-submit saat select berubah
        [ta, kelas, gender].forEach(el => el && el.addEventListener('change', () => form.submit()));

        // Enter di kolom pencarian
        if (search) {
            search.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') form.submit();
            });
        }
    });
</script>

<?= $this->endSection() ?>