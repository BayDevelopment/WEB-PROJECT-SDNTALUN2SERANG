<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- CSS kecil -->
<style>
    .page-title {
        font-weight: 800
    }

    .breadcrumb-modern .breadcrumb-item+.breadcrumb-item::before {
        content: "›"
    }

    .card-elevated {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 .6rem 1.2rem rgba(0, 0, 0, .06)
    }

    .card-header-modern {
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(135deg, rgba(59, 130, 246, .12), rgba(99, 102, 241, .10));
        border-radius: 1rem 1rem 0 0
    }

    .card-header-modern .title-wrap {
        font-weight: 700
    }

    .btn-gradient {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border: 0;
        color: #fff
    }

    .btn-gradient:hover {
        filter: brightness(1.05);
        color: #fff
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
        border-color: #93c5fd
    }

    .btn[disabled],
    .form-control[disabled],
    .form-select[disabled],
    textarea[disabled] {
        cursor: not-allowed;
        opacity: .75
    }

    .form-lock {
        position: relative
    }

    .form-lock::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .4);
        pointer-events: auto
    }
</style>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between gap-2 gap-sm-3 mb-3 flex-wrap">
        <div class="order-2 order-sm-1">
            <h1 class="mt-4 mb-1 page-title"><?= esc($sub_judul ?? 'Edit Nilai') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-modern mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('operator/laporan/nilai-siswa') ?>">Nilai Siswa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>

        <div class="order-1 order-sm-2 d-flex align-items-center">
            <a href="<?= base_url('operator/kategori/tambah') ?>" class="btn btn-success btn-sm rounded-pill py-2">
                <i class="fa-solid fa-file-medical me-2"></i> Tambah Kategori
            </a>
        </div>
    </div>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-pen-to-square me-2"></i> Form Edit Nilai Siswa
            </div>
        </div>

        <div class="card-body">
            <?php
            // Bawa filter aktif agar controller bisa redirect dengan konteks yang sama
            $qs = http_build_query([
                'q'           => $q           ?? '',
                'tahunajaran' => $tahunajaran ?? '',
                'kategori'    => $kategori    ?? '',
                'mapel'       => $mapel       ?? '',
            ]);

            $action = site_url(
                'operator/laporan/edit-nilai/' . (int)($id_nilai ?? $row['id_nilai'] ?? 0)
            ) . ($qs ? ('?' . $qs) : '');
            ?>

            <form id="formEditNilai"
                action="<?= esc($action) ?>"
                method="post"
                autocomplete="off"
                novalidate>
                <?php
                $errors = session('errors') ?? [];
                $hasErr = fn($f) => isset($errors[$f]);
                $getErr = fn($f) => $errors[$f] ?? '';
                ?>
                <?= csrf_field() ?>

                <!-- Id nilai (hidden) -->
                <input type="hidden" name="id_nilai" value="<?= esc((int)($id_nilai ?? $row['id_nilai'] ?? 0)) ?>">

                <div class="row g-3 mb-3">
                    <!-- siswa_id -->
                    <div class="col-md-6">
                        <label for="siswa_id" class="form-label">Siswa</label>
                        <select class="form-select<?= $hasErr('siswa_id') ? ' is-invalid' : '' ?>"
                            id="siswa_id" name="siswa_id" aria-describedby="siswa_idFeedback" required>
                            <option value="" disabled>— Pilih Siswa —</option>
                            <?php foreach (($optSiswa ?? []) as $s): ?>
                                <?php $selected = (string)old('siswa_id', $row['siswa_id'] ?? '') === (string)($s['id_siswa'] ?? ''); ?>
                                <option value="<?= esc($s['id_siswa']) ?>" <?= $selected ? 'selected' : '' ?>>
                                    <?= esc(($s['full_name'] ?? '-') . ' — ' . ($s['nisn'] ?? '-')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('siswa_id')): ?>
                            <div id="siswa_idFeedback" class="invalid-feedback d-block"><?= esc($getErr('siswa_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- tahun_ajaran_id -->
                    <div class="col-md-6">
                        <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" class="form-select form-select-sm" required>
                            <?php foreach (($optTA ?? []) as $ta): ?>
                                <option value="<?= esc($ta['id_tahun_ajaran']) ?>"
                                    <?= ((int)$row['tahun_ajaran_id'] === (int)$ta['id_tahun_ajaran']) ? 'selected' : '' ?>>
                                    <?= esc($ta['tahun']) ?> - Semester <?= esc(ucfirst($ta['semester'])) ?>
                                    <?= !empty($ta['is_active']) ? ' (Aktif)' : '' ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                        <?php if ($hasErr('tahun_ajaran_id')): ?>
                            <div id="taFeedback" class="invalid-feedback d-block"><?= esc($getErr('tahun_ajaran_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- mapel_id -->
                    <div class="col-md-6">
                        <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                        <select class="form-select<?= $hasErr('mapel_id') ? ' is-invalid' : '' ?>"
                            id="mapel_id" name="mapel_id" aria-describedby="mapelFeedback" required>
                            <option value="" disabled>— Pilih Mapel —</option>
                            <?php foreach (($optMapel ?? []) as $m): ?>
                                <?php $selected = (string)old('mapel_id', $row['mapel_id'] ?? ($mapel ?? '')) === (string)($m['id_mapel'] ?? ''); ?>
                                <option value="<?= esc($m['id_mapel']) ?>" <?= $selected ? 'selected' : '' ?>>
                                    <?= esc($m['nama'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('mapel_id')): ?>
                            <div id="mapelFeedback" class="invalid-feedback d-block"><?= esc($getErr('mapel_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- kategori_id -->
                    <div class="col-md-6">
                        <label for="kategori_id" class="form-label">Kategori Penilaian</label>
                        <?php if (empty($optKategori)): ?>
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <strong>Tidak ada kategori saat ini</strong>, silakan tambah kategori terlebih dahulu.
                            </div>
                            <select class="form-select" id="kategori_id" name="kategori_id" disabled>
                                <option value="">— Kategori belum tersedia —</option>
                            </select>
                            <?php if ($hasErr('kategori_id')): ?>
                                <div id="katFeedback" class="invalid-feedback d-block"><?= esc($getErr('kategori_id')) ?></div>
                            <?php endif; ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const btnSubmit = document.getElementById('btnSubmit');
                                    if (btnSubmit) {
                                        btnSubmit.disabled = true;
                                        btnSubmit.title = 'Tambah kategori terlebih dahulu';
                                    }
                                });
                            </script>
                        <?php else: ?>
                            <select class="form-select<?= $hasErr('kategori_id') ? ' is-invalid' : '' ?>"
                                id="kategori_id" name="kategori_id" aria-describedby="katFeedback" required>
                                <option value="" disabled>— Pilih Kategori —</option>
                                <?php foreach ($optKategori as $k): ?>
                                    <?php
                                    // Prioritas preselect: old() -> $row -> (?kategori=KODE)
                                    $preVal = (string)old('kategori_id', $row['kategori_id'] ?? '');
                                    $sel = $preVal !== '' && $preVal === (string)($k['id_kategori'] ?? '');
                                    if (!$sel && empty($preVal) && !empty($kategori) && !empty($k['kode'])) {
                                        $sel = (strtoupper($k['kode']) === strtoupper((string)$kategori));
                                    }
                                    ?>
                                    <option value="<?= esc($k['id_kategori']) ?>" <?= $sel ? 'selected' : '' ?>>
                                        <?= esc(($k['nama'] ?? '') . (!empty($k['kode']) ? ' — ' . $k['kode'] : '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Filter tabel pakai <code>?kategori=UTS/UAS</code> (berdasar <b>kode</b>), yang disimpan adalah <b>kategori_id</b>.
                            </div>
                            <?php if ($hasErr('kategori_id')): ?>
                                <div id="katFeedback" class="invalid-feedback d-block"><?= esc($getErr('kategori_id')) ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- skor -->
                    <div class="col-md-4">
                        <label for="skor" class="form-label">Skor</label>
                        <input type="number" step="0.01" min="0" max="100"
                            class="form-control<?= $hasErr('skor') ? ' is-invalid' : '' ?>"
                            id="skor" name="skor"
                            value="<?= esc(old('skor', $row['skor'] ?? '')) ?>"
                            placeholder="0 - 100" required>
                        <?php if ($hasErr('skor')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('skor')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- tanggal -->
                    <div class="col-md-4">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date"
                            class="form-control<?= $hasErr('tanggal') ? ' is-invalid' : '' ?>"
                            id="tanggal" name="tanggal"
                            value="<?= esc(old('tanggal', $row['tanggal'] ?? date('Y-m-d'))) ?>"
                            required>
                        <?php if ($hasErr('tanggal')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('tanggal')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- keterangan -->
                    <div class="col-md-4">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <input type="text"
                            class="form-control<?= $hasErr('keterangan') ? ' is-invalid' : '' ?>"
                            id="keterangan" name="keterangan"
                            value="<?= esc(old('keterangan', $row['keterangan'] ?? '')) ?>"
                            placeholder="Opsional: deskripsi singkat">
                        <?php if ($hasErr('keterangan')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('keterangan')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill">
                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Update</span>
                    </button>

                    <a href="<?= esc($backUrl ?? base_url('operator/laporan/nilai-siswa')) ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>

                <?php if (!empty($allowedFields ?? [])): ?>
                    <input type="hidden" name="_allowed" value="<?= esc(implode(',', $allowedFields)) ?>">
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- JS: loading state submit -->
<script>
    (function() {
        const form = document.getElementById('formEditNilai');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');

        form?.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            }
            btnSubmit.disabled = true;
            form.classList.add('form-lock');

            // pastikan token CSRF tetap aktif
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    })();
</script>
<?= $this->endSection() ?>