<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4 fade-in-up delay-300">
    <h1 class="mt-4 page-title">Profil Operator</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
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
                    linear-gradient(135deg, #eef2ff, #eff6ff)
            }

            .avatar-120 {
                width: 120px;
                height: 120px;
                object-fit: cover;
                background: #fff
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

            .btn-brand {
                background: linear-gradient(135deg, #2563eb, #7c3aed);
                color: #fff;
                border: 0;
                border-radius: .65rem;
                padding: .6rem 1rem;
                font-weight: 600
            }

            .input-icon {
                position: absolute;
                right: .75rem;
                top: 50%;
                transform: translateY(-50%);
                opacity: .85;
                z-index: 2
            }

            .input-icon:hover {
                opacity: 1
            }

            .form-floating .form-control {
                padding-right: 2.25rem
            }
        </style>

        <!-- LEFT -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden profile-modern">
                <div class="card-body text-center pt-0 mt-3">
                    <h5 class="mb-1 fw-semibold"><?= esc($user['username'] ?? 'Operator') ?></h5>

                    <!-- Email (klik untuk salin) -->
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
                            <i class="fa-solid fa-user-shield me-1"></i><?= esc($user['role'] ?? 'operator') ?>
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

        <!-- RIGHT -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden profile-form">
                <div class="card-header bg-transparent border-0 pb-0">
                    <ul class="nav nav-pills nav-fill gap-2 p-2 bg-pills rounded-3" id="profileTabs" role="tablist">
                        <!-- Jadikan Data Akun aktif -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-account" data-bs-toggle="tab" data-bs-target="#pane-account"
                                type="button" role="tab" aria-controls="pane-account" aria-selected="true">
                                <i class="fa-solid fa-user-pen me-1"></i> Data Akun
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-security" data-bs-toggle="tab" data-bs-target="#pane-security"
                                type="button" role="tab" aria-controls="pane-security" aria-selected="false">
                                <i class="fa-solid fa-lock me-1"></i> Keamanan
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="profileTabContent">
                        <!-- Data Akun (aktif & show) -->
                        <div class="tab-pane fade show active" id="pane-account" role="tabpanel" aria-labelledby="tab-account" tabindex="0">
                            <form action="<?= base_url('operator/profile') ?>" method="post" class="row g-3 row-cols-1 row-cols-md-2">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

                                <!-- Username -->
                                <div class="col">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                        <input id="username" type="text" name="username"
                                            value="<?= old('username', $user['username'] ?? '') ?>"
                                            class="form-control <?= $v->hasError('username') ? 'is-invalid' : '' ?>"
                                            placeholder="Nama pengguna">
                                    </div>
                                    <?php if ($v->hasError('username')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('username') ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Gunakan 4–24 karakter (huruf, angka, titik/underscore).</div>
                                </div>

                                <!-- Email -->
                                <div class="col">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                        <input id="email" type="email" name="email" inputmode="email"
                                            value="<?= old('email', $user['email'] ?? '') ?>"
                                            class="form-control <?= $v->hasError('email') ? 'is-invalid' : '' ?>"
                                            placeholder="nama@email.com">
                                    </div>
                                    <?php if ($v->hasError('email')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('email') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Role -->
                                <div class="col">
                                    <label for="role" class="form-label">Role</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                                        <?php $roleVal = old('role', $user['role'] ?? 'operator'); ?>
                                        <select name="role" id="role"
                                            class="form-select <?= isset($v) && $v->hasError('role') ? 'is-invalid' : '' ?>">
                                            <option value="operator" <?= $roleVal === 'operator' ? 'selected' : ''; ?>>Operator</option>
                                            <option value="guru" <?= $roleVal === 'guru' ? 'selected' : ''; ?>>Guru</option>
                                            <option value="siswa" <?= $roleVal === 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                                        </select>
                                    </div>
                                    <?php if (isset($v) && $v->hasError('role')): ?>
                                        <div class="invalid-feedback d-block"><?= esc($v->getError('role')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Status -->
                                <div class="col">
                                    <label for="is_active" class="form-label">Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <?php $activeVal = (int) old('is_active', (int)($user['is_active'] ?? 0)); ?>
                                        <select name="is_active" id="is_active"
                                            class="form-select <?= isset($v) && $v->hasError('is_active') ? 'is-invalid' : '' ?>">
                                            <option value="1" <?= $activeVal === 1 ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="0" <?= $activeVal === 0 ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                    <?php if (isset($v) && $v->hasError('is_active')): ?>
                                        <div class="invalid-feedback d-block"><?= esc($v->getError('is_active')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions (full width) -->
                                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                    <button type="reset" class="btn btn-outline-secondary rounded-pill py-2">Reset</button>
                                    <button type="submit" class="btn btn-brand ring-focus rounded-pill py-2">
                                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Keamanan -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel" aria-labelledby="tab-security" tabindex="0">
                            <form action="<?= base_url('operator/profile/password') ?>" method="post" class="row g-3 row-cols-1 row-cols-md-2">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

                                <!-- Current -->
                                <div class="">
                                    <label class="form-label">Password Saat Ini</label>
                                    <div class="form-floating mb-3 position-relative">
                                        <input class="form-control" id="current_password" name="password" type="password"
                                            placeholder="Password saat ini" autocomplete="current-password" required
                                            aria-describedby="currentPwHelp">
                                        <label for="current_password">Password saat ini</label>
                                        <button type="button" class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                            data-target="#current_password" aria-label="Tampilkan/sembunyikan password saat ini"
                                            aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                        <small id="currentPwHelp" class="visually-hidden">Tekan ikon mata untuk menampilkan atau menyembunyikan password.</small>
                                    </div>
                                    <?php if ($v->hasError('password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- New -->
                                <div class="">
                                    <label class="form-label">Password Baru</label>
                                    <div class="form-floating mb-3 position-relative">
                                        <input class="form-control" id="new_password" name="new_password" type="password"
                                            placeholder="Password baru" autocomplete="new-password" required
                                            aria-describedby="newPwHelp">
                                        <label for="new_password">Password baru</label>
                                        <button type="button" class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                            data-target="#new_password" aria-label="Tampilkan/sembunyikan password baru"
                                            aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                        <small id="newPwHelp" class="visually-hidden">Tekan ikon mata untuk menampilkan atau menyembunyikan password.</small>
                                    </div>
                                    <?php if ($v->hasError('new_password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('new_password') ?></div>
                                    <?php endif; ?>
                                    <div class="progress mt-2 password-meter" style="height:6px;">
                                        <div id="pwStrengthBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                                    </div>
                                    <div id="pwStrengthText" class="form-text"></div>
                                </div>

                                <!-- Confirm -->
                                <div class="">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div class="form-floating mb-3 position-relative">
                                        <input class="form-control" id="new_password_confirm" name="new_password_confirm" type="password"
                                            placeholder="Ulangi password baru" autocomplete="new-password" required
                                            aria-describedby="confirmPwHelp">
                                        <label for="new_password_confirm">Konfirmasi password baru</label>
                                        <button type="button" class="input-icon btn btn-link p-0 border-0 btn-toggle-pass"
                                            data-target="#new_password_confirm" aria-label="Tampilkan/sembunyikan konfirmasi password"
                                            aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                        <small id="confirmPwHelp" class="visually-hidden">Tekan ikon mata untuk menampilkan atau menyembunyikan password.</small>
                                    </div>
                                    <?php if ($v->hasError('new_password_confirm')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('new_password_confirm') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions (paling bawah form) -->
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-grid d-md-flex justify-content-md-end gap-2">
                                        <!-- opsional: tombol reset -->
                                        <button type="reset" class="btn btn-outline-secondary rounded-pill py-2">
                                            Reset
                                        </button>

                                        <button id="btnSubmitSecurity" type="submit" class="btn btn-brand ring-focus rounded-pill py-2 px-4">
                                            <i class="fa-solid fa-shield-halved me-1"></i> Update Password
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
    // password lemah, kuat ( kriteria )
    (function() {
        const newPw = document.getElementById('new_password');
        const confirm = document.getElementById('new_password_confirm');
        const bar = document.getElementById('pwStrengthBar');
        const textEl = document.getElementById('pwStrengthText');

        // Toggle show/hide untuk semua tombol .btn-toggle-pass (eye ↔ eye-slash)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-toggle-pass');
            if (!btn) return;
            const target = document.querySelector(btn.dataset.target);
            if (!target) return;

            const isPwd = target.type === 'password';
            target.type = isPwd ? 'text' : 'password';
            btn.setAttribute('aria-pressed', String(isPwd));
            btn.innerHTML = isPwd ?
                '<i class="fa-solid fa-eye-slash"></i>' :
                '<i class="fa-solid fa-eye"></i>';
            target.focus();
        });

        // ====== Meter Kekuatan ======
        // Skor 0..6 berdasarkan panjang + variasi + penalti pola umum
        function scorePassword(pw) {
            if (!pw) return 0;
            let s = 0;

            // + panjang
            if (pw.length >= 8) s++;
            if (pw.length >= 12) s++;

            // + variasi karakter
            if (/[a-z]/.test(pw)) s++;
            if (/[A-Z]/.test(pw)) s++;
            if (/\d/.test(pw)) s++;
            if (/[^A-Za-z0-9]/.test(pw)) s++;

            // Penalti: berkurang jika pola terlalu mudah
            const low = pw.toLowerCase();

            // daftar umum (ringkas)
            const common = ['password', 'admin', 'qwerty', '123456', 'welcome', 'iloveyou', 'abc123', '111111', 'letmein', 'user'];
            if (common.some(w => low.includes(w))) s -= 2;

            // urutan keyboard/angka sederhana
            const sequences = ['1234', 'abcd', 'qwer', 'asdf', 'zxcv'];
            if (sequences.some(seq => low.includes(seq))) s -= 1;

            // terlalu repetitif (aaaa, 1111, !!!)
            if (/(.)\1{2,}/.test(pw)) s -= 1;

            // clamp 0..6
            return Math.max(0, Math.min(6, s));
        }

        function setBarClass(el, cls) {
            el.className = 'progress-bar'; // reset
            el.classList.add(cls);
        }

        function labelForScore(s) {
            if (s <= 2) return 'Lemah';
            if (s <= 3) return 'Cukup';
            if (s <= 4) return 'Baik';
            return 'Kuat';
        }

        function classForScore(s) {
            // pakai util kelas Bootstrap
            if (s <= 2) return 'bg-danger';
            if (s <= 3) return 'bg-warning';
            if (s <= 4) return 'bg-info';
            return 'bg-success';
        }

        function renderStrength() {
            if (!newPw || !bar || !textEl) return;
            const s = scorePassword(newPw.value);
            const pct = (s / 6) * 100;

            bar.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', String(Math.round(pct)));
            setBarClass(bar, classForScore(s));
            textEl.textContent = newPw.value ? `Kekuatan: ${labelForScore(s)}` : '';
        }

        // ====== Cek Konfirmasi ======
        function renderConfirm() {
            const hintId = 'confirmPwInlineHint';
            let hint = document.getElementById(hintId);
            if (!hint) {
                hint = document.createElement('div');
                hint.id = hintId;
                hint.className = 'form-text';
                // taruh tepat setelah container konfirmasi (aman)
                const confirmWrap = confirm?.closest('.col, .form-floating, div');
                (confirmWrap?.parentNode || document.body).insertBefore(hint, (confirmWrap?.nextSibling) || null);
            }

            if (!confirm || !newPw || !confirm.value) {
                hint.textContent = '';
                hint.classList.remove('text-danger', 'text-success');
                return;
            }
            if (confirm.value === newPw.value) {
                hint.textContent = 'Konfirmasi cocok.';
                hint.classList.remove('text-danger');
                hint.classList.add('text-success');
            } else {
                hint.textContent = 'Konfirmasi tidak cocok.';
                hint.classList.remove('text-success');
                hint.classList.add('text-danger');
            }
        }

        // Inisialisasi
        newPw && newPw.addEventListener('input', () => {
            renderStrength();
            renderConfirm();
        });
        confirm && confirm.addEventListener('input', renderConfirm);

        // Render awal
        renderStrength();
    })();

    // Copy email (klik tombol dengan id="copyEmail")
    document.addEventListener('DOMContentLoaded', function() {
        const copyBtn = document.getElementById('copyEmail');
        if (!copyBtn) return;

        copyBtn.addEventListener('click', async function() {
            const email = copyBtn.getAttribute('data-email') || copyBtn.textContent.trim();
            if (!email) return;

            // Clipboard API + fallback
            try {
                await (navigator.clipboard?.writeText(email));
            } catch {
                const ta = document.createElement('textarea');
                ta.value = email;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                } catch {}
                document.body.removeChild(ta);
            }

            // Feedback visual singkat
            const original = copyBtn.innerHTML;
            copyBtn.classList.add('text-success');
            copyBtn.innerHTML = 'Disalin! <i class="fa-regular fa-check ms-1"></i>';
            setTimeout(() => {
                copyBtn.classList.remove('text-success');
                copyBtn.innerHTML = original;
            }, 1200);
        });
    });

    // loading + disable UI tapi data tetap terkirim (pakai hidden mirror)
    document.addEventListener('DOMContentLoaded', () => {
        function makeMirror(form, el) {
            // Hanya kalau punya name dan bukan submit/reset/button/hidden
            const name = el.getAttribute('name');
            if (!name) return;

            // Untuk type yang tidak perlu mirror
            const t = (el.type || '').toLowerCase();
            if (t === 'hidden' || t === 'submit' || t === 'reset' || t === 'button') return;

            // FILE: jangan dimatikan & jangan dibuat mirror
            if (t === 'file') return;

            // SELECT (support multiple)
            if (el.tagName === 'SELECT') {
                const isMultiple = el.multiple;
                if (isMultiple) {
                    Array.from(el.selectedOptions).forEach((opt) => {
                        const h = document.createElement('input');
                        h.type = 'hidden';
                        h.name = name + '[]';
                        h.value = opt.value;
                        form.appendChild(h);
                    });
                } else {
                    const h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = name;
                    h.value = el.value;
                    form.appendChild(h);
                }
                return;
            }

            // CHECKBOX / RADIO: hanya yang checked
            if (t === 'checkbox' || t === 'radio') {
                if (!el.checked) return;
                const h = document.createElement('input');
                h.type = 'hidden';
                h.name = name;
                h.value = el.value;
                form.appendChild(h);
                return;
            }

            // INPUT TEXT/EMAIL/NUMBER/PASSWORD/DATE… & TEXTAREA
            const h = document.createElement('input');
            h.type = 'hidden';
            h.name = name;
            h.value = el.value;
            form.appendChild(h);
        }

        function attachLoading(formSelector, submitBtnSelector) {
            const form = document.querySelector(formSelector);
            if (!form) return;

            form.addEventListener('submit', () => {
                if (form.dataset.submitting === '1') return;
                form.dataset.submitting = '1';

                const submitBtn =
                    form.querySelector(submitBtnSelector) ||
                    form.querySelector('button[type="submit"], input[type="submit"]');

                // 1) Buat mirror untuk semua field agar nilai tetap terkirim
                const fields = form.querySelectorAll('input, select, textarea');
                fields.forEach((el) => {
                    // Jangan mirrorkan yang hidden, submit, button, file, atau yang tidak punya name
                    makeMirror(form, el);
                });

                // 2) Ganti tombol submit jadi spinner + matikan
                if (submitBtn) {
                    submitBtn.dataset.original = submitBtn.innerHTML || submitBtn.value || '';
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';
                    } else {
                        submitBtn.value = 'Loading...';
                    }
                    submitBtn.disabled = true;
                }

                // 3) Disable semua kontrol (kecuali hidden & file & tombol submit sudah dimatikan)
                fields.forEach((el) => {
                    const t = (el.type || '').toLowerCase();
                    if (t === 'hidden') return; // biarkan
                    if (t === 'file') return; // biar tetap kebawa
                    if (el === submitBtn) return; // sudah dimatikan
                    el.disabled = true;
                    // Opsional: kasih class visual
                    el.classList.add('is-readonly');
                });

                // Biarkan submit jalan normal (tidak preventDefault)
            });
        }

        // Pasang ke kedua form
        attachLoading('#pane-account form', '.btn.btn-brand[type="submit"]');
        attachLoading('#pane-security form', '.btn.btn-brand[type="submit"]');
    });

    // Toggle password (delegated)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-toggle-pass');
        if (!btn) return;

        const sel = btn.getAttribute('data-target');
        if (!sel) return;

        const input = document.querySelector(sel);
        if (!input) return;

        const show = input.type === 'password';
        try {
            input.type = show ? 'text' : 'password';
        } catch (err) {
            const clone = input.cloneNode(true);
            clone.setAttribute('type', show ? 'text' : 'password');
            input.parentNode.replaceChild(clone, input);
        }

        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-regular', 'far', 'fas', 'fa-solid', 'fa-eye', 'fa-eye-slash');
            icon.classList.add('fa-solid', show ? 'fa-eye-slash' : 'fa-eye');
        }
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.title = show ? 'Sembunyikan' : 'Tampilkan';

        input.focus();
        const v = input.value;
        input.value = '';
        input.value = v;
    });
</script>

<style>
    /* Opsional: efek visual saat form di-lock */
    .is-readonly {
        pointer-events: none;
        opacity: .85;
    }
</style>
<?= $this->endSection() ?>