<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

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

<?php
$errors  = session('errors') ?? [];
$hasErr  = fn(string $f) => isset($errors[$f]);
$getErr  = fn(string $f) => $errors[$f] ?? '';

$id       = (string)($idGuruMapel ?? $d_row['id_guru_mapel'] ?? ''); // <-- PK
$nip      = (string)($d_row['nip'] ?? '');
$namaG    = (string)($d_row['nama_lengkap'] ?? '');
$oldGuru  = old('id_guru',         $d_row['id_guru']         ?? '');
$oldMapel = old('id_mapel',        $d_row['id_mapel']        ?? '');
$oldTA    = old('id_tahun_ajaran', $d_row['id_tahun_ajaran'] ?? '');
$oldKelas = old('id_kelas',        $d_row['id_kelas']        ?? '');
$oldJam   = old('jam_per_minggu',  $d_row['jam_per_minggu']  ?? '');
$oldKet   = old('keterangan',      $d_row['keterangan']      ?? '');
?>

<div class="container-fluid px-4 page-section">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Edit Guru Mapel') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/guru-mapel') ?>">Guru Mapel</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Edit') ?></li>
            </ol>
        </div>
    </div>

    <?php if (session()->getFlashdata('sweet_error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('sweet_error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('sweet_success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('sweet_success') ?></div>
    <?php endif; ?>

    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-pen me-2"></i> Form <?= esc($sub_judul ?? 'Edit Guru Mapel') ?>
            </div>
        </div>

        <div class="card-body">
            <!-- ACTION: update by NIP -->
            <form id="formGuruMapel"
                action="<?= site_url('operator/guru-mapel/edit/' . urlencode($id)) ?>"
                method="post" autocomplete="off" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT"> <!-- SPOOF PUT -->
                <!-- WAJIB: kirim PK agar pasti ikut saat submit -->
                <input type="hidden" name="id_guru_mapel" value="<?= esc($id, 'attr') ?>">

                <div class="row g-3 mb-3">
                    <!-- Guru (NIP & Nama) read-only + hidden id_guru -->
                    <div class="col-md-4">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" value="<?= esc($nip) ?>" readonly>
                        <div class="form-text">Identifikasi edit berdasar NIP.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Guru</label>
                        <input type="text" class="form-control" value="<?= esc($namaG) ?>" readonly>
                    </div>
                    <input type="hidden" name="id_guru" value="<?= esc($oldGuru, 'attr') ?>">

                    <!-- Mapel -->
                    <div class="col-md-6">
                        <label for="id_mapel" class="form-label">Mata Pelajaran</label>
                        <select class="form-select<?= $hasErr('id_mapel') ? ' is-invalid' : '' ?>"
                            id="id_mapel" name="id_mapel" required aria-describedby="id_mapelFeedback">
                            <option value="" disabled <?= $oldMapel ? '' : 'selected' ?>>— Pilih Mapel —</option>
                            <?php foreach (($mapelList ?? []) as $m): ?>
                                <?php
                                $mid   = (string)($m['id_mapel'] ?? '');
                                $mnama = (string)($m['nama'] ?? $m['nama_mapel'] ?? '');
                                $mkode = (string)($m['kode'] ?? $m['kode_mapel'] ?? '');
                                ?>
                                <option value="<?= esc($mid, 'attr') ?>" <?= $oldMapel == $mid ? 'selected' : '' ?>>
                                    <?= esc(($mkode ? "[$mkode] " : '') . $mnama) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('id_mapel')): ?>
                            <div id="id_mapelFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_mapel')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tahun Ajaran -->
                    <div class="col-md-3">
                        <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran</label>
                        <select class="form-select<?= $hasErr('id_tahun_ajaran') ? ' is-invalid' : '' ?>"
                            id="id_tahun_ajaran" name="id_tahun_ajaran" required aria-describedby="id_tahun_ajaranFeedback">
                            <option value="" disabled <?= $oldTA ? '' : 'selected' ?>>— Pilih Tahun Ajaran —</option>
                            <?php foreach (($tahunList ?? []) as $t): ?>
                                <?php
                                $tid    = (string)($t['id_tahun_ajaran'] ?? '');
                                $ttahun = (string)($t['tahun'] ?? '');
                                $tsem   = ucfirst((string)($t['semester'] ?? ''));
                                $isAct  = (int)($t['is_active'] ?? 0) === 1;
                                ?>
                                <option value="<?= esc($tid, 'attr') ?>" <?= $oldTA == $tid ? 'selected' : '' ?>>
                                    <?= esc($ttahun . ' — ' . $tsem . ($isAct ? ' (Aktif)' : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('id_tahun_ajaran')): ?>
                            <div id="id_tahun_ajaranFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_tahun_ajaran')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Kelas -->
                    <div class="col-md-3">
                        <label for="id_kelas" class="form-label">Kelas</label>
                        <select class="form-select<?= $hasErr('id_kelas') ? ' is-invalid' : '' ?>"
                            id="id_kelas" name="id_kelas" required aria-describedby="id_kelasFeedback">
                            <option value="" disabled <?= $oldKelas ? '' : 'selected' ?>>— Pilih Kelas —</option>
                            <?php foreach (($kelasList ?? []) as $k): ?>
                                <?php
                                $kid    = (string)($k['id_kelas'] ?? '');
                                $knama  = (string)($k['nama_kelas'] ?? $k['nama'] ?? '');
                                $kting  = (string)($k['tingkat'] ?? '');
                                $kjur   = (string)($k['jurusan'] ?? '');
                                $label  = $knama ?: trim(($kting !== '' ? $kting . ' ' : '') . ($kjur ?: ''));
                                ?>
                                <option value="<?= esc($kid, 'attr') ?>" <?= $oldKelas == $kid ? 'selected' : '' ?>>
                                    <?= esc($label ?: ('Kelas #' . $kid)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('id_kelas')): ?>
                            <div id="id_kelasFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_kelas')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Jam/Minggu -->
                    <div class="col-md-4">
                        <label for="jam_per_minggu" class="form-label">Jam / Minggu</label>
                        <input type="number"
                            class="form-control<?= $hasErr('jam_per_minggu') ? ' is-invalid' : '' ?>"
                            id="jam_per_minggu" name="jam_per_minggu"
                            value="<?= esc($oldJam) ?>" placeholder="Contoh: 6"
                            min="0" step="1" inputmode="numeric" required
                            aria-describedby="jam_per_mingguFeedback">
                        <?php if ($hasErr('jam_per_minggu')): ?>
                            <div id="jam_per_mingguFeedback" class="invalid-feedback d-block"><?= esc($getErr('jam_per_minggu')) ?></div>
                        <?php else: ?>
                            <div id="jam_per_mingguFeedback" class="form-text">Masukkan jumlah jam per minggu (bilangan bulat).</div>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-12">
                        <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                        <textarea class="form-control<?= $hasErr('keterangan') ? ' is-invalid' : '' ?>"
                            id="keterangan" name="keterangan" rows="3"
                            placeholder="Catatan khusus penugasan..."><?= esc($oldKet) ?></textarea>
                        <?php if ($hasErr('keterangan')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('keterangan')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill">
                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan</span>
                    </button>
                    <a href="<?= base_url('operator/guru-mapel') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // tanpa IIFE — skrip diletakkan setelah form
    const form = document.getElementById('formGuruMapel');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnText = btnSubmit?.querySelector('.btn-text');

    if (form) {
        form.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            }
            btnSubmit.disabled = true;
            form.classList.add('form-lock');

            // pastikan token CSRF aktif
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    }
</script>

<?= $this->endSection() ?>