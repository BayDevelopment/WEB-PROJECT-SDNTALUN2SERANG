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

    .btn[disabled],
    .form-control[disabled],
    .form-select[disabled] {
        cursor: not-allowed;
        opacity: .75
    }

    /* lock klik */
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

    /* overlay loading */
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
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Tambah Tahun Ajaran') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Tambah Tahun Ajaran') ?></li>
            </ol>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-calendar-plus me-2"></i> Form Tambah Tahun Ajaran
            </div>
        </div>

        <div class="card-body">
            <form id="formTahunAjaran"
                action="<?= site_url('operator/tambah/tahun-ajaran') ?>"
                method="post" autocomplete="off" novalidate
                class="position-relative">
                <?= csrf_field() ?>

                <?php
                $errors = session('errors') ?? [];
                $hasErr = fn($f) => isset($errors[$f]);
                $getErr = fn($f) => $errors[$f] ?? '';
                ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="tahun" class="form-label">Tahun</label>
                        <input type="text"
                            class="form-control<?= $hasErr('tahun') ? ' is-invalid' : '' ?>"
                            id="tahun" name="tahun"
                            placeholder="Contoh: 2024/2025"
                            value="<?= esc(old('tahun') ?? '') ?>"
                            maxlength="9" pattern="^\d{4}/\d{4}$" inputmode="numeric"
                            aria-describedby="tahunFeedback">
                        <div class="form-text">Gunakan format <strong>YYYY/YYYY</strong> (mis. 2024/2025).</div>
                        <?php if ($hasErr('tahun')): ?>
                            <div id="tahunFeedback" class="invalid-feedback d-block"><?= esc($getErr('tahun')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester</label>
                        <?php $semOld = old('semester', 'ganjil'); ?>
                        <select class="form-select<?= $hasErr('semester') ? ' is-invalid' : '' ?>"
                            id="semester" name="semester" required aria-describedby="semesterFeedback">
                            <option value="" disabled <?= $semOld ? '' : 'selected' ?>>— Pilih Semester —</option>
                            <option value="ganjil" <?= $semOld === 'ganjil' ? 'selected' : '' ?>>Ganjil</option>
                            <option value="genap" <?= $semOld === 'genap'  ? 'selected' : '' ?>>Genap</option>
                        </select>
                        <?php if ($hasErr('semester')): ?>
                            <div id="semesterFeedback" class="invalid-feedback d-block"><?= esc($getErr('semester')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Mulai</label>
                        <input type="date"
                            class="form-control<?= $hasErr('start_date') ? ' is-invalid' : '' ?>"
                            id="start_date" name="start_date"
                            value="<?= esc(old('start_date') ?? '') ?>"
                            aria-describedby="startDateFeedback">
                        <?php if ($hasErr('start_date')): ?>
                            <div id="startDateFeedback" class="invalid-feedback d-block"><?= esc($getErr('start_date')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label">Selesai</label>
                        <input type="date"
                            class="form-control<?= $hasErr('end_date') ? ' is-invalid' : '' ?>"
                            id="end_date" name="end_date"
                            value="<?= esc(old('end_date') ?? '') ?>"
                            aria-describedby="endDateFeedback">
                        <?php if ($hasErr('end_date')): ?>
                            <div id="endDateFeedback" class="invalid-feedback d-block"><?= esc($getErr('end_date')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input<?= $hasErr('is_active') ? ' is-invalid' : '' ?>"
                                type="checkbox" id="is_active" name="is_active" value="1"
                                <?= old('is_active', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        <?php if ($hasErr('is_active')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('is_active')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill d-inline-flex align-items-center">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan</span>
                    </button>

                    <button type="reset" id="btnReset" class="btn btn-outline-secondary rounded-pill">
                        <i class="fa-solid fa-rotate-left me-2"></i> Reset
                    </button>

                    <a href="<?= base_url('operator/tahun-ajaran') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>

                <!-- overlay loading -->
                <div id="formBlocker" class="form-blocker d-none" aria-hidden="true">
                    <div class="form-blocker-inner">
                        <div class="spinner-border" role="status" aria-hidden="true"></div>
                        <div class="ms-2">Loading…</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formTahunAjaran');
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

            // pastikan CSRF tidak ter-disable
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;

            // submit setelah repaint agar UI terlihat
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