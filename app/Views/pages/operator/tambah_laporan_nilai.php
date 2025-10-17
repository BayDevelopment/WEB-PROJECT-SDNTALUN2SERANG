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

    /* kunci interaksi + overlay loading */
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

    .form-blocker {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .6);
        backdrop-filter: blur(1px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5
    }

    .form-blocker.d-none {
        display: none
    }

    .form-blocker-inner {
        display: flex;
        align-items: center;
        padding: .5rem .75rem;
        border-radius: .75rem;
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 .4rem 1rem rgba(0, 0, 0, .08);
        font-weight: 600
    }
</style>

<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between gap-2 gap-sm-3 mb-3 flex-wrap">
        <div class="order-2 order-sm-1">
            <h1 class="mt-4 mb-1 page-title"><?= esc($sub_judul ?? 'Tambah Nilai Siswa') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-modern mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('operator/laporan/nilai-siswa') ?>">Nilai Siswa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah</li>
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
                <i class="fa-solid fa-file-circle-plus me-2"></i> Form Tambah Nilai Siswa
            </div>
        </div>

        <div class="card-body">
            <!-- FILTER BAR (GET) -->
            <form id="filterSiswaForm" method="get" class="row g-2 align-items-center mb-3" role="search">
                <!-- Cari nama/NISN atau ketik "kelas 1" -->
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="q" value="<?= esc($q ?? '') ?>"
                            class="form-control" placeholder="Cari nama, NISN, atau ketik 'kelas 1' ..."
                            autocomplete="off">
                    </div>
                </div>

                <!-- Dropdown Kelas -->
                <div class="col-8 col-md-4">
                    <select id="filterKelas" name="kelas" class="form-select form-select-sm">
                        <option value="">Semua Kelas</option>
                        <?php foreach (($optKelas ?? []) as $k):
                            $idk  = (int)($k['id_kelas'] ?? 0);
                            $nm   = (string)($k['nama_kelas'] ?? '');
                            $tgkt = (string)($k['tingkat'] ?? '');
                            $label = $nm !== '' ? $nm : ('Kelas ' . $tgkt);
                        ?>
                            <option value="<?= esc($idk) ?>" <?= (string)($kelas ?? '') === (string)$idk ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-4 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Terapkan</button>
                </div>
            </form>

            <script>
                // opsional: auto-submit saat ganti kelas
                document.getElementById('filterKelas')?.addEventListener('change', () => {
                    document.getElementById('filterSiswaForm')?.submit();
                });
            </script>

            <!-- FORM POST TAMBAH NILAI -->
            <form id="formTambahNilai"
                action="<?= site_url('operator/laporan/tambah-nilai') ?>"
                method="post" autocomplete="off" novalidate
                class="position-relative">
                <?php
                $errors  = session('errors') ?? [];
                $hasErr  = fn($f) => isset($errors[$f]);
                $getErr  = fn($f) => $errors[$f] ?? '';
                ?>
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <!-- siswa_id (group by kelas) -->
                    <div class="col-md-6">
                        <label for="siswa_id" class="form-label">Siswa</label>
                        <select class="form-select<?= $hasErr('siswa_id') ? ' is-invalid' : '' ?>"
                            id="siswa_id" name="siswa_id" aria-describedby="siswa_idFeedback" required>
                            <option value="" disabled <?= old('siswa_id') ? '' : 'selected' ?>>— Pilih Siswa —</option>

                            <?php
                            // Kelompokkan siswa per kelas untuk optgroup
                            $byKelas = [];
                            foreach (($optSiswa ?? []) as $s) {
                                $nmKelas = trim((string)($s['nama_kelas'] ?? ''));
                                $tgkt    = (string)($s['tingkat'] ?? '');
                                $labelKelas = $nmKelas !== '' ? $nmKelas : ($tgkt !== '' ? 'Kelas ' . $tgkt : '— Tanpa Kelas —');
                                $byKelas[$labelKelas][] = $s;
                            }
                            foreach ($byKelas as $label => $items):
                            ?>
                                <optgroup label="<?= esc($label) ?>">
                                    <?php foreach ($items as $s): ?>
                                        <option value="<?= esc($s['id_siswa']) ?>"
                                            <?= old('siswa_id') == ($s['id_siswa'] ?? null) ? 'selected' : '' ?>>
                                            <?= esc(($s['full_name'] ?? '-') . ' — ' . ($s['nisn'] ?? '-')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('siswa_id')): ?>
                            <div id="siswa_idFeedback" class="invalid-feedback d-block"><?= esc($getErr('siswa_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- tahun_ajaran_id -->
                    <div class="col-md-6">
                        <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                        <select class="form-select<?= $hasErr('tahun_ajaran_id') ? ' is-invalid' : '' ?>"
                            id="tahun_ajaran_id" name="tahun_ajaran_id" aria-describedby="taFeedback" required>
                            <option value="" disabled <?= old('tahun_ajaran_id') ? '' : 'selected' ?>>— Pilih Tahun Ajaran —</option>
                            <?php foreach (($optTA ?? []) as $ta): ?>
                                <option value="<?= esc($ta['id_tahun_ajaran']) ?>"
                                    <?= (old('tahun_ajaran_id', $tahunajaran ?? '') == ($ta['id_tahun_ajaran'] ?? null)) ? 'selected' : '' ?>>
                                    <?= esc(($ta['tahun'] ?? '') . ' - ' . ($ta['semester'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
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
                            <option value="" disabled <?= old('mapel_id') ? '' : 'selected' ?>>— Pilih Mapel —</option>
                            <?php foreach (($optMapel ?? []) as $m): ?>
                                <option value="<?= esc($m['id_mapel']) ?>"
                                    <?= (old('mapel_id', $mapel ?? '') == ($m['id_mapel'] ?? null)) ? 'selected' : '' ?>>
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
                                <strong>Tidak ada kategori saat ini</strong>, silakan isi terlebih dahulu.
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
                                        btnSubmit.title = 'Tambah kategori penilaian terlebih dahulu';
                                    }
                                });
                            </script>
                        <?php else: ?>
                            <select class="form-select<?= $hasErr('kategori_id') ? ' is-invalid' : '' ?>"
                                id="kategori_id" name="kategori_id" aria-describedby="katFeedback" required>
                                <option value="" disabled <?= old('kategori_id') ? '' : 'selected' ?>>— Pilih Kategori —</option>
                                <?php foreach ($optKategori as $k): ?>
                                    <?php
                                    $preSelect = '';
                                    if (empty(old('kategori_id')) && !empty($kategori) && !empty($k['kode'])) {
                                        $preSelect = (strtoupper($k['kode']) === strtoupper((string)$kategori)) ? 'selected' : '';
                                    }
                                    ?>
                                    <option value="<?= esc($k['id_kategori']) ?>"
                                        <?= old('kategori_id') == ($k['id_kategori'] ?? null) ? 'selected' : $preSelect ?>>
                                        <?= esc(($k['nama'] ?? '') . (!empty($k['kode']) ? ' — ' . $k['kode'] : '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Filter daftar di tabel pakai <code>?kategori=UTS/UAS</code> (berdasar <b>kode</b>), data yang disimpan adalah <b>kategori_id</b>.
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
                            value="<?= esc(old('skor') ?? '') ?>"
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
                            value="<?= esc(old('tanggal', date('Y-m-d'))) ?>" required>
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
                            value="<?= esc(old('keterangan') ?? '') ?>"
                            placeholder="Opsional: deskripsi singkat">
                        <?php if ($hasErr('keterangan')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('keterangan')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill d-inline-flex align-items-center">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan</span>
                    </button>

                    <button type="reset" id="btnReset" class="btn btn-outline-secondary rounded-pill">
                        <i class="fa-solid fa-rotate-left me-2"></i> Reset
                    </button>

                    <a href="<?= base_url('operator/laporan/nilai-siswa') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>

                <?php if (!empty($allowedFields ?? [])): ?>
                    <input type="hidden" name="_allowed" value="<?= esc(implode(',', $allowedFields)) ?>">
                <?php endif; ?>

                <!-- overlay loading -->
                <div id="formBlocker" class="form-blocker d-none" aria-hidden="true">
                    <div class="form-blocker-inner">
                        <div class="spinner-border" role="status" aria-hidden="true"></div>
                        <div class="ms-2">Menyimpan…</div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- JS: loading state submit (spinner + overlay + freeze inputs) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formTambahNilai');
        const btn = document.getElementById('btnSubmit');
        const spin = btn ? btn.querySelector('.spinner-border') : null;
        const txt = btn ? btn.querySelector('.btn-text') : null;
        const blk = document.getElementById('formBlocker');
        if (!form || !btn) return;

        let loading = false;

        function freezeInputs(container) {
            const textLike = 'input[type="text"],input[type="email"],input[type="password"],input[type="number"],input[type="date"],input[type="time"],input[type="datetime-local"],input[type="search"],input[type="tel"],textarea';
            container.querySelectorAll(textLike).forEach(el => {
                el.setAttribute('readonly', 'readonly');
                el.setAttribute('aria-readonly', 'true');
            });
            container.querySelectorAll('select,input[type="checkbox"],input[type="radio"]').forEach(el => {
                el.setAttribute('aria-disabled', 'true');
            });
        }

        function armLoading(e) {
            if (loading) return;
            loading = true;

            if (spin) spin.classList.remove('d-none');
            if (txt) txt.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Menyimpan…';
            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('disabled');

            if (blk) blk.classList.remove('d-none');
            form.setAttribute('aria-busy', 'true');
            form.classList.add('form-lock');
            freezeInputs(form);

            // pastikan token CSRF tetap aktif
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;

            // submit setelah repaint (cegah double tap)
            e && e.preventDefault();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => form.submit());
            });
        }

        btn.addEventListener('click', armLoading);
        form.addEventListener('submit', armLoading);
    });
</script>

<?= $this->endSection() ?>