<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Laporan Nilai Siswa') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('guru/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Laporan Nilai Siswa') ?></li>
            </ol>
        </div>

        <?php if (!empty($d_nilai)): ?>
            <div class="text-end">
                <a href="<?= base_url('guru/laporan/tambah-nilai') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                    <i class="fa-solid fa-file-circle-plus me-2" aria-hidden="true"></i> Tambah
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <div class="col-12">
                    <form id="filterForm" method="get" role="search" class="row g-2 align-items-center">
                        <!-- Cari nama / NISN -->
                        <div class="col-12 col-md-3">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
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

                        <!-- Dropdown Tahun Ajaran -->
                        <div class="col-12 col-md-2">
                            <?php $tahunajaran = (string)($tahunajaran ?? ''); ?>
                            <select id="filterTA" name="tahunajaran" class="form-select form-select-sm" aria-label="Filter Tahun Ajaran">
                                <option value="" <?= $tahunajaran === '' ? 'selected' : '' ?>>Semua TA</option>
                                <?php foreach (($listTA ?? []) as $ta): ?>
                                    <?php $tid = (string)($ta['id_tahun_ajaran'] ?? ''); ?>
                                    <option value="<?= esc($tid) ?>" <?= $tahunajaran === $tid ? 'selected' : '' ?>>
                                        <?= esc($ta['label'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dropdown Kelas (hanya kelas yang diampu guru) -->
                        <div class="col-12 col-md-2">
                            <?php $kelasSel = (string)($kelas ?? ''); ?>
                            <select id="filterKelas" name="kelas" class="form-select form-select-sm" aria-label="Filter Kelas">
                                <option value="" <?= $kelasSel === '' ? 'selected' : '' ?>>Semua Kelas</option>
                                <?php foreach (($listKelas ?? []) as $kl): ?>
                                    <?php $kid = (string)($kl['id_kelas'] ?? ''); ?>
                                    <option value="<?= esc($kid) ?>" <?= $kelasSel === $kid ? 'selected' : '' ?>>
                                        <?= esc($kl['nama_kelas'] ?? ('Kelas #' . $kid)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dropdown Mapel (tergantung kelas terpilih) -->
                        <div class="col-12 col-md-2">
                            <?php $mapelSel = (string)($mapel ?? ''); ?>
                            <select id="filterMapel" name="mapel" class="form-select form-select-sm" aria-label="Filter Mapel">
                                <option value="" <?= $mapelSel === '' ? 'selected' : '' ?>>
                                    <?= ($kelasSel !== '' ? 'Semua Mapel (kelas ini)' : 'Semua Mapel') ?>
                                </option>
                                <?php foreach (($listMapel ?? []) as $mp): ?>
                                    <?php $mid = (string)($mp['id_mapel'] ?? ''); ?>
                                    <option value="<?= esc($mid) ?>" <?= $mapelSel === $mid ? 'selected' : '' ?>>
                                        <?= esc($mp['nama'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Kategori nilai (kode: UTS/UAS) -->
                        <div class="col-12 col-md-3">
                            <?php $kat = strtoupper((string)($kategori ?? '')); ?>
                            <select id="filterKat" name="kategori" class="form-select form-select-sm" aria-label="Filter Kategori">
                                <option value="" <?= $kat === ''   ? 'selected' : '' ?>>Semua Kategori</option>
                                <option value="UTS" <?= $kat === 'UTS' ? 'selected' : '' ?>>UTS</option>
                                <option value="UAS" <?= $kat === 'UAS' ? 'selected' : '' ?>>UAS</option>
                            </select>
                        </div>
                    </form>



                </div>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_nilai)): ?>
                <div class="table-responsive">
                    <table id="tableDataNilai" class="table table-modern align-middle text-capitalize">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Gender</th>
                                <th>Tahun</th>
                                <th>Kelas</th>
                                <th>Semester</th>
                                <th>Mapel</th>
                                <th>Kategori</th>
                                <th>Skor</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
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

                            foreach ($d_nilai as $row):
                                $idNilai     = (int)($row['id_nilai'] ?? 0);
                                $nisn        = (string)($row['nisn'] ?? '');
                                $nama        = (string)($row['full_name'] ?? $row['nama_lengkap'] ?? '');

                                // === Normalisasi Gender -> L/P ===
                                $genderRaw = (string)($row['gender'] ?? '');
                                $genderLow = mb_strtolower(trim($genderRaw), 'UTF-8');
                                $isL = preg_match('/^(l|laki|lk|male|m)/i', $genderRaw) || str_contains($genderLow, 'laki');
                                $isP = preg_match('/^(p|perem|pr|wanita|female|f)/i', $genderRaw) || str_contains($genderLow, 'perem');
                                if ($isL) {
                                    $genderShort = 'L';
                                    $genderFull  = 'Laki-laki';
                                    $badgeGender = 'badge rounded-pill bg-primary';
                                } elseif ($isP) {
                                    $genderShort = 'P';
                                    $genderFull  = 'Perempuan';
                                    $badgeGender = 'badge rounded-pill bg-danger';
                                } else {
                                    $genderShort = '—';
                                    $genderFull  = ($genderRaw !== '' ? $genderRaw : 'Tidak diketahui');
                                    $badgeGender = 'badge rounded-pill bg-secondary';
                                }

                                $taTahun    = (string)($row['tahun_ajaran'] ?? '');
                                $taSmtr     = (string)($row['semester'] ?? '');
                                $kelasNama  = (string)($row['nama_kelas'] ?? '—');   // <-- NEW
                                $mapelNama  = (string)($row['nama'] ?? '');
                                $katKode    = (string)($row['kategori_kode'] ?? '');
                                $skor       = (string)($row['skor'] ?? '');
                                $tgl        = $fmtDMY($row['tanggal_nilai'] ?? null);
                                $ket        = (string)($row['nilai_keterangan'] ?? '');

                                // QS untuk kembali ke filter terakhir (tambahkan kelas)
                                $qs = http_build_query([
                                    'q'           => $q           ?? '',
                                    'tahunajaran' => $tahunajaran ?? '',
                                    'kategori'    => $kategori    ?? '',
                                    'mapel'       => $mapel       ?? '',   // gunakan $mapel, bukan $mapelSel
                                    'kelas'       => $kelas       ?? '',   // <-- NEW
                                ]);
                                $hrefEdit = base_url('guru/laporan/edit-nilai/' . $idNilai) . ($qs ? ('?' . $qs) : '');
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
                                    <td><?= esc($taTahun) ?></td> <!-- Tahun -->
                                    <td><?= esc($kelasNama) ?></td> <!-- Kelas (NEW) -->
                                    <td><?= esc($taSmtr) ?></td> <!-- Semester -->
                                    <td><?= esc($mapelNama) ?></td>
                                    <td><?= esc($katKode) ?></td>
                                    <td><?= esc($skor) ?></td>
                                    <td><?= esc(format_ddmmyyyy_ke_tanggal_indo($tgl)) ?></td>
                                    <td><?= esc($ket) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            <?php else: ?>
                <!-- Empty state -->
                <div class="empty-card text-center p-5">
                    <img src="<?= base_url('assets/img/empty-box.png') ?>" class="empty-illustration mb-3" alt="Kosong">
                    <h5 class="mb-1">Belum ada data nilai</h5>
                    <p class="text-muted mb-3">Silakan atur filter atau tambahkan data nilai pada menu terkait.</p>
                    <a href="<?= base_url('guru/laporan/tambah-nilai') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('filterForm');
        if (!form) return;

        const submitSafe = () => {
            if (form.dataset.submitting === '1') return;
            form.dataset.submitting = '1';
            form.submit();
        };

        // Saat Kelas berubah, submit agar Mapel direfresh sesuai kelas
        const selKelas = document.getElementById('filterKelas');
        if (selKelas) selKelas.addEventListener('change', submitSafe);

        // (Opsional) auto-submit untuk filter lain juga
        const selTA = document.getElementById('filterTA');
        const selMap = document.getElementById('filterMapel');
        const selKat = document.getElementById('filterKat');

        [selTA, selMap, selKat].forEach(el => {
            if (el) el.addEventListener('change', submitSafe);
        });
    })();
</script>

<?= $this->endSection() ?>