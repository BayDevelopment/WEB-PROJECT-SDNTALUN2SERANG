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

            /* Override: brand gradient biru–ungu */
            .btn-brand {
                background: linear-gradient(135deg, #2563eb, #7c3aed);
                color: #fff;
                border: 0;
                border-radius: .65rem;
                padding: .6rem 1rem;
                font-weight: 600;
                box-shadow: 0 6px 14px rgba(37, 99, 235, .22), 0 2px 6px rgba(124, 58, 237, .16);
                transition: transform .15s ease, box-shadow .2s ease, filter .2s ease, opacity .2s ease;
            }

            .btn-brand:hover {
                filter: brightness(1.03) saturate(1.05);
                transform: translateY(-1px);
                box-shadow: 0 10px 18px rgba(37, 99, 235, .26), 0 3px 8px rgba(124, 58, 237, .20);
            }

            .btn-brand:active {
                transform: translateY(0);
                box-shadow: 0 4px 10px rgba(37, 99, 235, .20), 0 2px 5px rgba(0, 0, 0, .06);
            }

            /* Fokus ring disesuaikan ke palet baru */
            .ring-focus:focus,
            .ring-focus:focus-visible {
                outline: none !important;
                box-shadow:
                    0 0 0 4px rgba(37, 99, 235, .18),
                    0 0 0 2px rgba(124, 58, 237, .55);
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
                            <button class="nav-link " id="tab-account" data-bs-toggle="tab" data-bs-target="#pane-account" type="button" role="tab">
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
                        <div class="tab-pane fade" id="pane-account" role="tabpanel">
                            <form action="<?= base_url('operator/profile') ?>" method="post" class="row g-3">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
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

                                <!-- Role (dropdown) -->
                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                                        <?php $roleVal = old('role', $user['role'] ?? 'operator'); ?>
                                        <select name="role" id="role"
                                            class="form-select <?= isset($v) && $v->hasError('role') ? 'is-invalid' : '' ?>">
                                            <option value="operator" <?= $roleVal === 'operator' ? 'selected' : ''; ?>>Operator</option>
                                            <option value="guru" <?= $roleVal === 'guru' ? 'selected' : ''; ?>>Guru</option>
                                            <option value="siswa" <?= $roleVal === 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                                        </select>
                                        <?php if (isset($v) && $v->hasError('role')): ?>
                                            <div class="invalid-feedback"><?= esc($v->getError('role')) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Status (dropdown) -->
                                <div class="col-md-6">
                                    <label for="is_active" class="form-label">Status</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <?php $activeVal = (int) old('is_active', (int)($user['is_active'] ?? 0)); ?>
                                        <select name="is_active" id="is_active"
                                            class="form-select <?= isset($v) && $v->hasError('is_active') ? 'is-invalid' : '' ?>">
                                            <option value="1" <?= $activeVal === 1 ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="0" <?= $activeVal === 0 ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                        <?php if (isset($v) && $v->hasError('is_active')): ?>
                                            <div class="invalid-feedback"><?= esc($v->getError('is_active')) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                    <button type="reset" class="btn btn-light rounded-pill py-2">Reset</button>
                                    <button type="submit" class="btn btn-brand ring-focus rounded-pill py-2">
                                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Keamanan -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel">
                            <form action="<?= base_url('operator/profile/password') ?>" method="post" class="row g-3">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

                                <!-- Current -->
                                <div class="col-md-6">
                                    <label class="form-label">Password Saat Ini</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <input id="current_password" type="text" name="password"
                                            class="form-control <?= $v->hasError('password') ? 'is-invalid' : '' ?>"
                                            placeholder="••••••••">
                                    </div>
                                    <?php if ($v->hasError('password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- New -->
                                <div class="col-md-6">
                                    <label class="form-label">Password Baru</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                        <input id="new_password" type="text" name="new_password"
                                            class="form-control <?= $v->hasError('new_password') ? 'is-invalid' : '' ?>"
                                            placeholder="Min. 8 karakter">
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
                                        <input id="new_password_confirm" type="text" name="new_password_confirm"
                                            class="form-control <?= $v->hasError('new_password_confirm') ? 'is-invalid' : '' ?>"
                                            placeholder="Ulangi password baru">
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
    document.addEventListener('DOMContentLoaded', function() {
        function toggleIcon(icon, show) {
            if (!icon) return;
            const isFA6 = !!document.querySelector('.fa-solid, .fa-regular, .fa-brands');
            icon.classList.remove('fa-regular', 'fa-solid', 'fas', 'fa-eye', 'fa-eye-slash');
            icon.classList.add(isFA6 ? 'fa-solid' : 'fas', show ? 'fa-eye-slash' : 'fa-eye');
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-toggle-pass');
            if (!btn) return;

            const sel = btn.getAttribute('data-target') || '';
            const input = sel ? document.querySelector(sel) : null;
            if (!input) return;

            const show = input.type === 'password';
            try {
                input.type = show ? 'text' : 'password';
            } catch (err) {
                const clone = input.cloneNode(true);
                clone.setAttribute('type', show ? 'text' : 'password');
                input.parentNode.replaceChild(clone, input);
            }

            toggleIcon(btn.querySelector('i'), show);
            btn.title = show ? 'Sembunyikan' : 'Tampilkan';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });
</script>


<?= $this->endSection() ?>