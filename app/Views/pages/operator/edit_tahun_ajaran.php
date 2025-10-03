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
        background: rgba(255, 255, 255, .35);
        pointer-events: auto
    }
</style>

<?php
$d_TahunAjaran = $d_TahunAjaran ?? [];
$id        = (int)($d_TahunAjaran['id_tahun_ajaran'] ?? 0);

$tahunVal  = old('tahun', (string)($d_TahunAjaran['tahun'] ?? ''));
$semVal    = old('semester', (string)($d_TahunAjaran['semester'] ?? 'ganjil'));
$startVal  = old('start_date', (string)($d_TahunAjaran['start_date'] ?? ''));
$endVal    = old('end_date', (string)($d_TahunAjaran['end_date'] ?? ''));

$statRaw   = old('is_active', (string)($d_TahunAjaran['is_active'] ?? '1'));
$isActive  = in_array($statRaw, ['1', 1, true, 'true', 'TRUE'], true) ? '1' : '0';

$errors = session('errors') ?? [];
$hasErr = fn($f) => isset($errors[$f]);
$getErr = fn($f) => $errors[$f] ?? '';
$sub    = $sub_judul ?? 'Edit Tahun Ajaran';
?>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub) ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/tahun-ajaran') ?>">Tahun Ajaran</a></li>
                <li class="breadcrumb-item active"><?= esc($sub) ?></li>
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
                <i class="fa-solid fa-calendar-days me-2"></i> Form Edit Tahun Ajaran
            </div>
        </div>

        <div class="card-body">
            <form id="formEditTahunAjaran"
                action="<?= site_url('operator/edit/tahun-ajaran/' . rawurlencode((string)$id)) ?>"
                method="post" autocomplete="off" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div class="row g-3 mb-3">
                    <!-- Tahun (format 2024/2025) -->
                    <div class="col-md-6">
                        <label for="tahun" class="form-label">Tahun</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('tahun') ? ' is-invalid' : '' ?>"
                            id="tahun" name="tahun"
                            placeholder="Contoh: 2024/2025"
                            value="<?= esc($tahunVal) ?>"
                            maxlength="9" pattern="^\d{4}/\d{4}$"
                            inputmode="numeric" aria-describedby="tahunFeedback">
                        <div class="form-text">Gunakan format <strong>YYYY/YYYY</strong> (mis. 2024/2025).</div>
                        <?php if ($hasErr('tahun')): ?>
                            <div id="tahunFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('tahun')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Semester -->
                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester</label>
                        <select
                            class="form-select<?= $hasErr('semester') ? ' is-invalid' : '' ?>"
                            id="semester" name="semester" aria-describedby="semesterFeedback" required>
                            <option value="" disabled <?= $semVal ? '' : 'selected' ?>>— Pilih Semester —</option>
                            <option value="ganjil" <?= $semVal === 'ganjil' ? 'selected' : '' ?>>Ganjil</option>
                            <option value="genap" <?= $semVal === 'genap'  ? 'selected' : '' ?>>Genap</option>
                        </select>
                        <?php if ($hasErr('semester')): ?>
                            <div id="semesterFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('semester')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Start / End Date -->
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Mulai</label>
                        <input
                            type="date"
                            class="form-control<?= $hasErr('start_date') ? ' is-invalid' : '' ?>"
                            id="start_date" name="start_date"
                            value="<?= esc($startVal) ?>"
                            aria-describedby="startDateFeedback">
                        <?php if ($hasErr('start_date')): ?>
                            <div id="startDateFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('start_date')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">Selesai</label>
                        <input
                            type="date"
                            class="form-control<?= $hasErr('end_date') ? ' is-invalid' : '' ?>"
                            id="end_date" name="end_date"
                            value="<?= esc($endVal) ?>"
                            aria-describedby="endDateFeedback">
                        <?php if ($hasErr('end_date')): ?>
                            <div id="endDateFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('end_date')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Status aktif (switch) -->
                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input<?= $hasErr('is_active') ? ' is-invalid' : '' ?>"
                                type="checkbox" id="is_active" name="is_active" value="1"
                                <?= $isActive === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        <?php if ($hasErr('is_active')): ?>
                            <div class="invalid-feedback d-block">
                                <?= esc($getErr('is_active')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill">
                        <span class="btn-text">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Perbarui
                        </span>
                    </button>

                    <a href="<?= base_url('operator/tahun-ajaran') ?>" class="btn btn-outline-secondary rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formEditTahunAjaran');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');

        form?.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memperbarui...';
            }
            btnSubmit.disabled = true;
            form.classList.add('form-lock');

            // pastikan token CSRF aktif
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    })();
</script>

<?= $this->endSection() ?>