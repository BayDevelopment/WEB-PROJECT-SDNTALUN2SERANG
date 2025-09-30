<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4 page-title">Profil Operator</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>

    <?php $v = \Config\Services::validation(); ?>

    <div class="row g-4 mb-3">
        <!-- LEFT: Profile Card (Modern) -->
        <!-- Scoped styles untuk kartu -->
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
        </style>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden profile-modern">
                <div class="card-body text-center pt-0 mt-3">
                    <!-- Username -->
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

                    <!-- Badges -->
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

                <!-- Footer meta -->
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
                <!-- Header: Tabs -->
                <div class="card-header bg-transparent border-0 pb-0">
                    <ul class="nav nav-pills nav-fill gap-2 p-2 bg-pills rounded-3" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-account" data-bs-toggle="tab" data-bs-target="#pane-account" type="button" role="tab">
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
                        <div class="tab-pane fade show active" id="pane-account" role="tabpanel">
                            <form action="<?= base_url('operator/profile/update') ?>" method="post" class="row g-3">
                                <?= csrf_field() ?>

                                <!-- Username -->
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                        <input id="username" type="text" name="username"
                                            value="<?= old('username', $user['username'] ?? '') ?>"
                                            class="form-control <?= $v->hasError('username') ? 'is-invalid' : '' ?>"
                                            placeholder="Nama pengguna">
                                        <?php if ($v->hasError('username')): ?>
                                            <div class="invalid-feedback d-block"><?= $v->getError('username') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text">Gunakan 4–24 karakter (huruf, angka, titik/underscore).</div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                        <input id="email" type="email" name="email" inputmode="email"
                                            value="<?= old('email', $user['email'] ?? '') ?>"
                                            class="form-control <?= $v->hasError('email') ? 'is-invalid' : '' ?>"
                                            placeholder="nama@email.com">
                                        <?php if ($v->hasError('email')): ?>
                                            <div class="invalid-feedback d-block"><?= $v->getError('email') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Role (readonly soft) -->
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                                        <input type="text" class="form-control read-only-soft" value="<?= esc($user['role'] ?? 'operator') ?>" readonly>
                                    </div>
                                </div>

                                <!-- Status (readonly soft) -->
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <input type="text" class="form-control read-only-soft"
                                            value="<?= (int)($user['is_active'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif' ?>" readonly>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="reset" class="btn btn-light">Reset</button>
                                    <button type="submit" class="btn btn-brand ring-focus">
                                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Keamanan -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel">
                            <form action="<?= base_url('operator/profile/password') ?>" method="post" class="row g-3">
                                <?= csrf_field() ?>

                                <!-- Current -->
                                <div class="col-md-6">
                                    <label class="form-label">Password Saat Ini</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <input id="current_password" type="password" name="current_password"
                                            class="form-control <?= $v->hasError('current_password') ? 'is-invalid' : '' ?>"
                                            placeholder="••••••••">
                                        <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="#current_password" title="Tampilkan">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if ($v->hasError('current_password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('current_password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6"></div>

                                <!-- New -->
                                <div class="col-md-6">
                                    <label class="form-label">Password Baru</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                        <input id="new_password" type="password" name="new_password"
                                            class="form-control <?= $v->hasError('new_password') ? 'is-invalid' : '' ?>"
                                            placeholder="Min. 8 karakter">
                                        <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="#new_password" title="Tampilkan">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
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
                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <input id="new_password_confirm" type="password" name="new_password_confirm"
                                            class="form-control <?= $v->hasError('new_password_confirm') ? 'is-invalid' : '' ?>"
                                            placeholder="Ulangi password baru">
                                        <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="#new_password_confirm" title="Tampilkan">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if ($v->hasError('new_password_confirm')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('new_password_confirm') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-brand">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Update Password
                                    </button>
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
    (function() {
        const btn = document.getElementById('copyEmail');
        btn?.addEventListener('click', async () => {
            const email = btn.dataset.email || '';
            if (!email) return;
            try {
                await navigator.clipboard.writeText(email);
                const prev = btn.innerHTML;
                btn.innerHTML = '<span class="me-1">Tersalin</span><i class="fa-solid fa-check"></i>';
                setTimeout(() => btn.innerHTML = prev, 1200);
            } catch (_) {}
        });
    })();

    (function() {
        function resolveTarget(btn) {
            var sel = btn.getAttribute('data-target') || '';
            if (sel) {
                var id = sel[0] === '#' ? sel.slice(1) : sel;
                var byId = document.getElementById(id);
                if (byId) return byId;
                var byQS = document.querySelector(sel);
                if (byQS) return byQS;
            }
            var group = btn.closest('.input-group');
            return group ? group.querySelector('input.form-control') : null;
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-toggle-pass');
            if (!btn) return;
            e.preventDefault();

            var input = resolveTarget(btn);
            if (!input) return;

            var willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';

            var icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-eye', 'fa-eye-slash');
                icon.classList.add(willShow ? 'fa-eye-slash' : 'fa-eye');
            }

            btn.setAttribute('aria-pressed', willShow ? 'true' : 'false');
            btn.title = willShow ? 'Sembunyikan' : 'Tampilkan';
        });
    })();
</script>
<?= $this->endSection() ?>