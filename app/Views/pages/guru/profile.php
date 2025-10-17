<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4 page-title">Profil Guru</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item"><a href="<?= base_url('guru/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>

    <?php $v = \Config\Services::validation(); ?>

    <div class="row g-4 mb-3">
        <style>
            .profile-modern .profile-cover .cover-bg {
                height: 110px;
                background:
                    radial-gradient(900px 200px at -10% -50%, rgba(99, 102, 241, .18) 0, transparent 60%),
                    radial-gradient(700px 180px at 110% 0, rgba(59, 130, 246, .18) 0, transparent 60%),
                    linear-gradient(135deg, #eef2ff, #eff6ff);
            }

            .avatar-120 {
                width: 120px;
                height: 120px;
                object-fit: cover;
                background: #fff
            }

            .avatar-stack {
                position: relative;
                display: inline-block;
                margin-top: -60px
            }

            .status-dot {
                position: absolute;
                right: 8px;
                bottom: 8px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                border: 2px solid #fff;
                display: inline-block
            }

            .btn-change-photo {
                position: absolute;
                right: -6px;
                top: 50%;
                transform: translateY(-50%);
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: grid;
                place-items: center;
                background: #111827;
                color: #fff;
                cursor: pointer;
                border: 3px solid #fff;
                box-shadow: 0 .2rem .6rem rgba(0, 0, 0, .15)
            }

            .btn-change-photo:hover {
                filter: brightness(1.05)
            }

            .bg-pills {
                background: rgba(99, 102, 241, .06)
            }

            .nav-pills .nav-link {
                border-radius: 9999px
            }

            .nav-pills .nav-link.active {
                background: linear-gradient(135deg, #2563eb, #7c3aed)
            }

            .profile-form .form-label {
                font-weight: 600
            }

            .input-icon .input-group-text {
                background: #f8fafc
            }

            .form-control:focus {
                box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
                border-color: #93c5fd
            }

            .read-only-soft {
                background: #f8fafc
            }

            .password-meter .progress-bar.bg-weak {
                background: #ef4444
            }

            .password-meter .progress-bar.bg-fair {
                background: #f59e0b
            }

            .password-meter .progress-bar.bg-good {
                background: #10b981
            }

            .btn-brand {
                background: linear-gradient(135deg, #2563eb, #7c3aed);
                color: #fff;
                border: 0;
                border-radius: .65rem;
                padding: .6rem 1rem;
                font-weight: 600;
                box-shadow: 0 6px 14px rgba(37, 99, 235, .22), 0 2px 6px rgba(124, 58, 237, .16);
                transition: transform .15s ease, box-shadow .2s ease, filter .2s ease, opacity .2s ease
            }

            .btn-brand:hover {
                filter: brightness(1.03) saturate(1.05);
                transform: translateY(-1px);
                box-shadow: 0 10px 18px rgba(37, 99, 235, .26), 0 3px 8px rgba(124, 58, 237, .20)
            }

            .btn-brand:active {
                transform: translateY(0);
                box-shadow: 0 4px 10px rgba(37, 99, 235, .20), 0 2px 5px rgba(0, 0, 0, .06)
            }

            .ring-focus:focus,
            .ring-focus:focus-visible {
                outline: none !important;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, .18), 0 0 0 2px rgba(124, 58, 237, .55)
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

            .form-blocker .form-blocker-inner {
                display: flex;
                align-items: center;
                padding: .5rem .75rem;
                border-radius: .75rem;
                background: rgba(255, 255, 255, .9);
                box-shadow: 0 .4rem 1rem rgba(0, 0, 0, .08);
                font-weight: 600;
            }

            .position-relative .input-icon.btn {
                position: absolute;
                right: .75rem;
                top: 50%;
                transform: translateY(-50%);
                line-height: 1;
                color: var(--bs-secondary-color);
            }

            .position-relative .input-icon.btn:hover {
                color: var(--bs-body-color);
            }

            /* Saat field di-freeze (opsional, sinkron dengan JS freeze yang kemarin) */
            .is-readonly {
                background-color: var(--bs-secondary-bg);
                opacity: .9;
            }

            .password-meter .progress-bar {
                transition: width .25s ease;
            }
        </style>

        <!-- LEFT: Profile Card -->
        <div class="col-12 col-lg-4 fade-in-up delay-300">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden profile-modern">
                <div class="card-body text-center pt-0 mt-3">
                    <h5 class="mb-1 fw-semibold"><?= esc($user['username'] ?? 'Guru') ?></h5>

                    <div class="small text-muted d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-regular fa-envelope"></i>
                        <?php $email = trim((string)($user['email'] ?? '')); ?>
                        <?php if ($email !== ''): ?>
                            <button
                                type="button"
                                id="copyEmail"
                                class="btn btn-link p-0 link-secondary text-decoration-none align-baseline"
                                data-email="<?= esc($email, 'attr') ?>"
                                aria-label="Salin email">
                                <?= esc($email) ?> <i class="fa-regular fa-copy ms-1"></i>
                            </button>
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3 d-flex justify-content-center flex-wrap gap-2">
                        <span class="badge rounded-pill bg-brand-soft text-dark">
                            <i class="fa-solid fa-user-shield me-1"></i><?= esc($user['role'] ?? 'guru') ?>
                        </span>

                        <?php $isActive = (int)($user['is_active'] ?? 0) === 1; ?>
                        <span class="badge rounded-pill <?= $isActive ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                            <i class="fa-regular fa-circle-check me-1"></i><?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </div>
                </div>

                <div class="card-footer bg-transparent">
                    <div class="small text-muted mb-1">
                        <i class="fa-regular fa-calendar-plus me-1"></i>Dibuat:
                        <strong><?= esc(dt_indo($user['created_at'] ?? '—')) ?></strong>
                    </div>
                    <div class="small text-muted">
                        <i class="fa-regular fa-clock me-1"></i>Terakhir diperbarui:
                        <strong><?= esc(dt_indo($user['updated_at'] ?? '—')) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Tabs -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden profile-form">
                <div class="card-header bg-transparent border-0 pb-0">
                    <ul class="nav nav-pills nav-fill gap-2 p-2 bg-pills rounded-3" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-account" data-bs-toggle="tab" data-bs-target="#pane-account" type="button" role="tab">
                                <i class="fa-solid fa-user-pen me-1"></i> Data Akun
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-security" data-bs-toggle="tab" data-bs-target="#pane-security" type="button" role="tab">
                                <i class="fa-solid fa-lock me-1"></i> Keamanan
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="profileTabContent">
                        <!-- Data Akun -->
                        <div class="tab-pane fade" id="pane-account" role="tabpanel" aria-labelledby="tab-account" tabindex="0">
                            <form id="formAccount" data-freeze-on-submit action="<?= base_url('guru/profile/username') ?>" method="post" class="row g-3 row-cols-1 row-cols-md-2 position-relative">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

                                <!-- Username -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                        <input id="username" type="text" name="username"
                                            value="<?= old('username', $user['username'] ?? '') ?>"
                                            class="form-control <?= $v->hasError('username') ? 'is-invalid' : '' ?>"
                                            placeholder="Nama pengguna" autocomplete="username" required>
                                    </div>
                                    <?php if ($v->hasError('username')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('username') ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Gunakan 4–24 karakter (huruf, angka, titik/underscore).</div>
                                </div>

                                <!-- Email -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                        <input id="email" type="email" name="email" inputmode="email"
                                            value="<?= old('email', $user['email'] ?? '') ?>"
                                            class="form-control <?= $v->hasError('email') ? 'is-invalid' : '' ?>"
                                            placeholder="nama@email.com" autocomplete="email" required>
                                    </div>
                                    <?php if ($v->hasError('email')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('email') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Role (dikunci) -->
                                <div class="col-12 col-md-6">
                                    <label for="role_display" class="form-label">Role</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                                        <select id="role_display" class="form-select" disabled>
                                            <option value="operator">Operator</option>
                                            <option value="guru" selected>Guru</option>
                                            <option value="siswa">Siswa</option>
                                        </select>
                                        <input type="hidden" name="role" value="guru">
                                    </div>
                                    <div class="form-text">Role akun ini dikunci sebagai <strong>Guru</strong>.</div>
                                </div>

                                <!-- Status (dikunci aktif) -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <input type="text" class="form-control read-only-soft" value="Aktif" readonly>
                                        <input type="hidden" name="is_active" value="1">
                                    </div>
                                    <div class="form-text">Status akun dikunci sebagai <strong>Aktif</strong>.</div>
                                </div>

                                <div class="col-12 col-md-12 mt-3">
                                    <div class="d-grid gap-2">
                                        <button type="reset" class="btn btn-outline-secondary rounded-pill py-2 w-100">
                                            Reset
                                        </button>
                                        <button id="btnSubmitAccount" type="submit" class="btn btn-brand ring-focus rounded-pill py-2 w-100">
                                            <i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Keamanan -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel" aria-labelledby="tab-security" tabindex="0">
                            <form id="pwForm" data-freeze-on-submit action="<?= base_url('guru/profile/password') ?>" method="post" class="row g-3 row-cols-1 row-cols-md-2 position-relative">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

                                <!-- Password Saat Ini -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Password Saat Ini</label>
                                    <div class="form-floating mb-3 position-relative">
                                        <input
                                            class="form-control <?= $v->hasError('password') ? 'is-invalid' : '' ?>"
                                            id="current_password" name="password" type="password"
                                            placeholder="Password saat ini" autocomplete="current-password" required
                                            aria-describedby="currentPwHelp">
                                        <label for="current_password">Password saat ini</label>

                                        <button type="button"
                                            class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                            data-target="#current_password"
                                            aria-label="Tampilkan/sembunyikan password saat ini"
                                            aria-pressed="false">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <small id="currentPwHelp" class="visually-hidden">
                                            Tekan ikon mata untuk menampilkan atau menyembunyikan password.
                                        </small>
                                    </div>
                                    <?php if ($v->hasError('password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Password Baru -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Password Baru</label>
                                    <div class="form-floating mb-2 position-relative">
                                        <input
                                            class="form-control <?= $v->hasError('new_password') ? 'is-invalid' : '' ?>"
                                            id="new_password" name="new_password" type="password"
                                            placeholder="Password baru" autocomplete="new-password" required
                                            aria-describedby="newPwHelp pwStrengthText">
                                        <label for="new_password">Password baru (min. 8 karakter)</label>

                                        <button type="button"
                                            class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                            data-target="#new_password"
                                            aria-label="Tampilkan/sembunyikan password baru"
                                            aria-pressed="false">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <small id="newPwHelp" class="visually-hidden">
                                            Tekan ikon mata untuk menampilkan atau menyembunyikan password.
                                        </small>
                                    </div>

                                    <!-- Meter kekuatan -->
                                    <div class="progress password-meter" style="height:6px;">
                                        <div id="pwStrengthBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                                    </div>
                                    <div id="pwStrengthText" class="form-text small"></div>

                                    <?php if ($v->hasError('new_password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('new_password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Konfirmasi Password Baru -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div class="form-floating mb-3 position-relative">
                                        <input
                                            class="form-control <?= $v->hasError('new_password_confirm') ? 'is-invalid' : '' ?>"
                                            id="new_password_confirm" name="new_password_confirm" type="password"
                                            placeholder="Ulangi password baru" autocomplete="new-password" required
                                            aria-describedby="confirmPwHelp">
                                        <label for="new_password_confirm">Ulangi password baru</label>

                                        <button type="button"
                                            class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                            data-target="#new_password_confirm"
                                            aria-label="Tampilkan/sembunyikan konfirmasi password"
                                            aria-pressed="false">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <small id="confirmPwHelp" class="visually-hidden">
                                            Tekan ikon mata untuk menampilkan atau menyembunyikan password.
                                        </small>
                                    </div>
                                    <div id="confirmPwHint" class="form-text"></div>

                                    <?php if ($v->hasError('new_password_confirm')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('new_password_confirm') ?></div>
                                    <?php endif; ?>
                                </div>


                                <!-- Actions (Keamanan) -->
                                <div class="col-12 col-md-12 mt-3">
                                    <div class="d-grid gap-2">
                                        <button type="reset" class="btn btn-outline-secondary rounded-pill py-2 w-100">
                                            Reset
                                        </button>
                                        <button id="btnSubmitAccount" type="submit" class="btn btn-brand ring-focus rounded-pill py-2 w-100">
                                            <i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // copy email (tetap)
    document.addEventListener('DOMContentLoaded', function() {
        const copyBtn = document.getElementById('copyEmail');
        if (!copyBtn) return;
        copyBtn.addEventListener('click', async function() {
            const email = copyBtn.getAttribute('data-email') || '';
            if (!email) return;
            try {
                await (navigator.clipboard?.writeText(email));
            } catch (_) {
                const ta = document.createElement('textarea');
                ta.value = email;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                } catch (_) {}
                document.body.removeChild(ta);
            }
            copyBtn.classList.add('text-success');
            copyBtn.setAttribute('aria-live', 'polite');
            const original = copyBtn.innerHTML;
            copyBtn.innerHTML = 'Disalin! <i class="fa-regular fa-check ms-1"></i>';
            setTimeout(() => {
                copyBtn.classList.remove('text-success');
                copyBtn.innerHTML = original;
            }, 1200);
        });
    });

    // toggle password + meter (tetap)
    (function() {
        document.querySelectorAll('.btn-toggle-pass').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.querySelector(btn.dataset.target);
                if (!target) return;
                const isPwd = target.type === 'password';
                target.type = isPwd ? 'text' : 'password';
                btn.setAttribute('aria-pressed', String(isPwd));
                btn.innerHTML = isPwd ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
                target.focus();
            });
        });

        const newPw = document.getElementById('new_password');
        const bar = document.getElementById('pwStrengthBar');
        const text = document.getElementById('pwStrengthText');

        function scorePassword(pw) {
            let s = 0;
            if (!pw) return 0;
            if (pw.length >= 8) s++;
            if (pw.length >= 12) s++;
            if (/[a-z]/.test(pw)) s++;
            if (/[A-Z]/.test(pw)) s++;
            if (/\d/.test(pw)) s++;
            if (/[^A-Za-z0-9]/.test(pw)) s++;
            return Math.min(s, 6);
        }

        function renderMeter() {
            if (!(newPw && bar && text)) return;
            const s = scorePassword(newPw.value),
                pct = (s / 6) * 100;
            bar.style.width = pct + '%';
            bar.className = 'progress-bar';
            if (s <= 2) bar.classList.add('bg-danger');
            else if (s <= 3) bar.classList.add('bg-warning');
            else if (s <= 4) bar.classList.add('bg-info');
            else bar.classList.add('bg-success');
            text.textContent = newPw.value ? `Kekuatan: ${s<=2?'Lemah':s<=3?'Cukup':s<=4?'Baik':'Kuat'}` : '';
        }
        newPw && newPw.addEventListener('input', renderMeter);
        renderMeter();

        const confirmPw = document.getElementById('new_password_confirm');
        const hint = document.getElementById('confirmPwHint');

        function renderMatch() {
            if (!(confirmPw && hint)) return;
            if (!confirmPw.value) {
                hint.textContent = '';
                return;
            }
            if (confirmPw.value === newPw.value) {
                hint.classList.remove('text-danger');
                hint.classList.add('text-success');
                hint.textContent = 'Konfirmasi cocok.';
            } else {
                hint.classList.remove('text-success');
                hint.classList.add('text-danger');
                hint.textContent = 'Konfirmasi tidak cocok.';
            }
        }
        confirmPw && confirmPw.addEventListener('input', renderMatch);
        newPw && newPw.addEventListener('input', renderMatch);
    })();

    // FREEZE ON SUBMIT: cukup sekali dan generik (tetap)
    (function() {
        document.querySelectorAll('form[data-freeze-on-submit]').forEach((form) => {

            function cloneFieldValue(el) {
                if (!el.name || el.type === 'hidden') return;
                form.querySelectorAll(`input[type="hidden"][data-frozen-clone="1"][name="${CSS.escape(el.name)}"]`)
                    .forEach(n => n.remove());
                const makeHidden = (name, value) => {
                    const h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = name;
                    h.value = value;
                    h.setAttribute('data-frozen-clone', '1');
                    form.appendChild(h);
                };
                const tag = el.tagName.toLowerCase();
                if (tag === 'select') {
                    if (el.multiple) Array.from(el.selectedOptions).forEach(opt => makeHidden(el.name, opt.value));
                    else makeHidden(el.name, el.value);
                } else if (el.type === 'checkbox' || el.type === 'radio') {
                    if (el.checked) makeHidden(el.name, el.value || 'on');
                } else {
                    makeHidden(el.name, el.value);
                }
            }

            form.addEventListener('submit', function() {
                if (form.dataset.frozen === '1') return;
                form.dataset.frozen = '1';

                const fields = form.querySelectorAll('input, select, textarea');
                fields.forEach(el => {
                    if (['submit', 'button', 'reset'].includes(el.type)) return;
                    if (el.disabled && el.type !== 'hidden') return;
                    cloneFieldValue(el);
                });

                fields.forEach(el => {
                    if (el.type === 'hidden') return;
                    el.disabled = true;
                    el.setAttribute('aria-disabled', 'true');
                    el.classList.add('is-readonly');
                });

                const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.dataset.originalHtml = btn.innerHTML || '';
                    if (btn.tagName === 'BUTTON') {
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
                    }
                }
                // tidak preventDefault -> submit normal
            });

            form.addEventListener('reset', function() {
                delete form.dataset.frozen;
                form.querySelectorAll('input[type="hidden"][data-frozen-clone="1"]').forEach(n => n.remove());
                form.querySelectorAll('input, select, textarea, button').forEach(el => {
                    if (el.type === 'hidden') return;
                    el.disabled = false;
                    el.removeAttribute('aria-disabled');
                    el.classList.remove('is-readonly', 'disabled');
                });
                const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (btn && btn.dataset.originalHtml && btn.tagName === 'BUTTON') {
                    btn.innerHTML = btn.dataset.originalHtml;
                    delete btn.dataset.originalHtml;
                }
            });

        });
    })();
</script>

<?= $this->endSection() ?>