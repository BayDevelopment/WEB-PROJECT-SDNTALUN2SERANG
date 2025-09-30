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

    .avatar-80 {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 1px solid #e5e7eb
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
        position: relative;
    }

    .form-lock::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .4);
        /* blok interaksi tanpa disabled */
        pointer-events: auto;
    }
</style>
<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul) ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>
        <div class="text-muted small mt-3 mt-sm-0">
            Total User: <strong><?= isset($d_user) ? number_format(count($d_user), 0, ',', '.') : 0 ?></strong>
        </div>
    </div>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-plus me-2"></i> Form Tambah Guru
            </div>
        </div>

        <div class="card-body">
            <form id="formTambahUser"
                action="<?= site_url('operator/tambah-user') ?>"
                method="post"
                autocomplete="off"
                novalidate>
                <?php
                $errors = session('errors') ?? [];
                $hasErr = fn($f) => isset($errors[$f]);
                $getErr = fn($f) => $errors[$f] ?? '';
                ?>

                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <!-- username -->
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('username') ? ' is-invalid' : '' ?>"
                            id="username" name="username"
                            placeholder="Masukkan username"
                            value="<?= esc(old('username') ?? '') ?>"
                            maxlength="50"
                            autocapitalize="off"
                            spellcheck="false"
                            aria-describedby="usernameFeedback">
                        <?php if ($hasErr('username')): ?>
                            <div id="usernameFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('username')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- password -->
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            class="form-control<?= $hasErr('password') ? ' is-invalid' : '' ?>"
                            id="password" name="password"
                            placeholder="Masukkan password"
                            aria-describedby="passwordFeedback">
                        <?php if ($hasErr('password')): ?>
                            <div id="passwordFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('password')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control<?= $hasErr('email') ? ' is-invalid' : '' ?>"
                            id="email" name="email"
                            value="<?= esc(old('email') ?? '') ?>"
                            placeholder="nama@domain.com"
                            autocomplete="email"
                            inputmode="email"
                            spellcheck="false"
                            aria-describedby="emailFeedback">
                        <?php if ($hasErr('email')): ?>
                            <div id="emailFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('email')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- role -->
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select
                            class="form-select<?= $hasErr('role') ? ' is-invalid' : '' ?>"
                            name="role" id="role" aria-describedby="roleFeedback">
                            <option value="" disabled <?= old('role') ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="guru" <?= old('role') === 'guru'     ? 'selected' : '' ?>>Guru</option>
                            <option value="siswa" <?= old('role') === 'siswa'    ? 'selected' : '' ?>>Siswa</option>
                            <option value="operator" <?= old('role') === 'operator' ? 'selected' : '' ?>>Operator</option>
                            <option value="admin" <?= old('role') === 'admin'    ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?php if ($hasErr('role')): ?>
                            <div id="roleFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('role')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- is_active -->
                    <div class="col-md-6">
                        <label class="form-label d-block">Status Akun</label>
                        <!-- default 0 saat tidak dicentang -->
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input<?= $hasErr('is_active') ? ' is-invalid' : '' ?>"
                                type="checkbox" id="is_active" name="is_active" value="1"
                                <?= old('is_active', '1') == '1' ? 'checked' : '' ?>>
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
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan
                        </span>
                    </button>

                    <button type="reset" id="btnReset" class="btn btn-outline-secondary rounded-pill">
                        <i class="fa-solid fa-rotate-left me-2"></i> Reset
                    </button>

                    <a href="<?= base_url('operator/data-user') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- JS: preview foto, Reset, dan loading state submit -->
<script>
    (function() {
        const form = document.getElementById('formTambahSiswa');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');

        form?.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            }
            // Cukup disable tombol submit
            btnSubmit.disabled = true;

            // Tambahkan overlay pengunci (tanpa disabled field)
            form.classList.add('form-lock');

            // Pastikan CSRF tidak tersentuh
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    })();
</script>
<?= $this->endSection() ?>