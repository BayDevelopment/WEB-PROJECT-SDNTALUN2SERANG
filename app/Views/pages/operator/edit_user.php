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

    /* Lock layer ala baseline */
    .form-lock {
        position: relative
    }

    .form-blocker {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .6);
        backdrop-filter: blur(1px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
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
        font-weight: 600;
    }

    /* Ikon mata */
    .position-relative .input-icon.btn {
        position: absolute;
        right: .75rem;
        top: 50%;
        transform: translateY(-50%);
        line-height: 1;
        color: var(--bs-secondary-color);
    }

    .position-relative .input-icon.btn:hover {
        color: var(--bs-body-color)
    }
</style>

<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Edit Siswa') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-user') ?>">Data User</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-pen me-2"></i> Form Edit User
            </div>
        </div>

        <div class="card-body">
            <form id="formEditUser"
                action="<?= site_url('operator/edit-user/' . $d_user['id_user']) ?>"
                method="post"
                enctype="multipart/form-data"
                autocomplete="off"
                novalidate
                class="position-relative">

                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <?php
                $errors = session('errors') ?? [];
                $hasErr = fn(string $f) => isset($errors[$f]);
                $getErr = fn(string $f) => $errors[$f] ?? '';
                ?>

                <div class="row g-3 mb-3">
                    <!-- Username -->
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text"
                            class="form-control<?= $hasErr('username') ? ' is-invalid' : '' ?>"
                            id="username" name="username"
                            value="<?= esc(old('username', $d_user['username'] ?? '')) ?>"
                            placeholder="Masukkan username"
                            required minlength="3" maxlength="50"
                            aria-describedby="usernameFeedback">
                        <?php if ($hasErr('username')): ?>
                            <div id="usernameFeedback" class="invalid-feedback d-block"><?= esc($getErr('username')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password (opsional) -->
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password (opsional)</label>
                        <div class="position-relative">
                            <input type="password"
                                class="form-control<?= $hasErr('password') ? ' is-invalid' : '' ?>"
                                id="password" name="password"
                                placeholder="Kosongkan jika tidak ingin mengubah"
                                aria-describedby="passwordFeedback togglePwHelp">
                            <button type="button"
                                class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                data-target="#password"
                                aria-label="Tampilkan/sembunyikan password"
                                aria-pressed="false">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small id="togglePwHelp" class="visually-hidden">Tekan ikon mata untuk menampilkan atau menyembunyikan password.</small>
                        <div class="form-text">Minimal 6 karakter bila diisi.</div>
                        <?php if ($hasErr('password')): ?>
                            <div id="passwordFeedback" class="invalid-feedback d-block"><?= esc($getErr('password')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Role -->
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <?php $roleOld = old('role', $d_user['role'] ?? ''); ?>
                        <select class="form-select<?= $hasErr('role') ? ' is-invalid' : '' ?>"
                            id="role" name="role" required aria-describedby="roleFeedback">
                            <option value="" disabled <?= $roleOld ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="siswa" <?= $roleOld === 'siswa'    ? 'selected' : '' ?>>Siswa</option>
                            <option value="guru" <?= $roleOld === 'guru'     ? 'selected' : '' ?>>Guru</option>
                            <option value="operator" <?= $roleOld === 'operator' ? 'selected' : '' ?>>Operator</option>
                        </select>
                        <?php if ($hasErr('role')): ?>
                            <div id="roleFeedback" class="invalid-feedback d-block"><?= esc($getErr('role')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                            class="form-control<?= $hasErr('email') ? ' is-invalid' : '' ?>"
                            id="email" name="email"
                            value="<?= esc(old('email', $d_user['email'] ?? '')) ?>"
                            placeholder="nama@domain.com"
                            required autocomplete="email" inputmode="email"
                            aria-describedby="emailFeedback">
                        <?php if ($hasErr('email')): ?>
                            <div id="emailFeedback" class="invalid-feedback d-block"><?= esc($getErr('email')) ?></div>
                        <?php else: ?>
                            <div id="emailFeedback" class="form-text">Masukkan email aktif yang valid.</div>
                        <?php endif; ?>
                    </div>

                    <!-- Status Aktif -->
                    <div class="col-md-6">
                        <label class="form-label d-block">Status Akun</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input<?= $hasErr('is_active') ? ' is-invalid' : '' ?>"
                                type="checkbox" id="is_active" name="is_active" value="1"
                                <?= old('is_active', (string)($d_user['is_active'] ?? '1')) == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        <?php if ($hasErr('is_active')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('is_active')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill d-inline-flex align-items-center">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Update</span>
                    </button>

                    <a href="<?= base_url('operator/data-user') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>

                <!-- Overlay blocker ala baseline -->
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

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Toggle password (ikut baseline)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-toggle-pass');
        if (!btn) return;
        const target = document.querySelector(btn.dataset.target);
        if (!target) return;

        const isPwd = target.type === 'password';
        const start = target.selectionStart,
            end = target.selectionEnd;

        try {
            target.type = isPwd ? 'text' : 'password';
        } catch (e) {
            const clone = target.cloneNode(true);
            clone.type = isPwd ? 'text' : 'password';
            target.parentNode.replaceChild(clone, target);
        }

        // sinkronkan ikon + a11y
        btn.setAttribute('aria-pressed', String(isPwd));
        btn.innerHTML = isPwd ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';

        target.focus();
        if (start != null && end != null && target.setSelectionRange) target.setSelectionRange(start, end);
    });

    // Preview & reset foto (aman kalau elemen tidak ada)
    (function() {
        const inputFile = document.getElementById('photo');
        const imgPrev = document.getElementById('previewPhoto');
        const btnReset = document.getElementById('btnResetFoto');

        const originalSrc = imgPrev?.getAttribute('data-original') || imgPrev?.src || '';

        inputFile?.addEventListener('change', function() {
            const f = this.files?.[0];
            imgPrev && (imgPrev.src = f ? URL.createObjectURL(f) : originalSrc);
        });
        btnReset?.addEventListener('click', function() {
            if (inputFile) inputFile.value = '';
            if (imgPrev) imgPrev.src = originalSrc;
        });
    })();

    // Loading state submit (seragam dengan baseline)
    (function() {
        const form = document.getElementById('formEditUser');
        const btn = document.getElementById('btnSubmit');
        const spin = btn ? btn.querySelector('.spinner-border') : null;
        const txt = btn ? btn.querySelector('.btn-text') : null;
        const blk = document.getElementById('formBlocker');
        if (!form || !btn) return;

        let loading = false;

        // Bekukan input teks (readonly) dan blokir interaksi via overlay
        function freezeTextInputs(container) {
            const selsText = 'input[type="text"],input[type="email"],input[type="password"],input[type="number"],input[type="date"],input[type="time"],input[type="datetime-local"],input[type="search"],input[type="tel"],textarea';
            container.querySelectorAll(selsText).forEach(el => {
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

            spin && spin.classList.remove('d-none');
            txt && (txt.textContent = 'Menyimpan...');
            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('disabled');

            blk && blk.classList.remove('d-none');
            form.classList.add('form-lock');
            form.setAttribute('aria-busy', 'true');

            freezeTextInputs(form);

            // pastikan CSRF tidak disabled (jaga-jaga)
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;

            // biarkan repaint lalu submit normal
            if (e && e.preventDefault) e.preventDefault();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => form.submit());
            });
        }

        btn.addEventListener('click', armLoading);
        form.addEventListener('submit', armLoading);
    })();
</script>
<?= $this->endSection() ?>