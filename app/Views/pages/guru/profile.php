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
        </style>

        <!-- LEFT: Profile Card -->
        <div class="col-12 col-lg-4">
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
                        <div class="tab-pane fade" id="pane-account" role="tabpanel">
                            <form action="<?= base_url('guru/profile/username') ?>" method="post" class="row g-3">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

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

                                <!-- Role dikunci sebagai guru -->
                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                                        <?php $roleVal = 'guru'; ?>
                                        <select name="role_display" id="role" class="form-select" disabled>
                                            <option value="operator">Operator</option>
                                            <option value="guru" selected>Guru</option>
                                            <option value="siswa">Siswa</option>
                                        </select>
                                        <!-- kirim nilai sebenarnya -->
                                        <input type="hidden" name="role" value="guru">
                                    </div>
                                    <div class="form-text">Role akun ini dikunci sebagai <strong>Guru</strong>.</div>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <!-- tampilkan badge saja (read-only) -->
                                        <input type="text" class="form-control read-only-soft" value="Aktif" readonly>
                                        <!-- kirim nilai fix 1 ke server -->
                                        <input type="hidden" name="is_active" value="1">
                                    </div>
                                    <div class="form-text">Status akun dikunci sebagai <strong>Aktif</strong>.</div>
                                </div>


                                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                    <button type="reset" class="btn btn-outline-secondary rounded-pill py-2">Reset</button>

                                    <!-- UBAH: tombol submit pakai spinner & teks -->
                                    <button id="btnSubmitAccount" type="submit" class="btn btn-brand ring-focus rounded-pill py-2 btn-sm d-inline-flex align-items-center">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                        <span class="btn-text"><i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan</span>
                                    </button>
                                </div>

                            </form>
                        </div>

                        <!-- Keamanan -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel">
                            <form id="pwForm" action="<?= base_url('guru/profile/password') ?>" method="post" class="row g-3 position-relative">
                                <?php $v = $validation ?? \Config\Services::validation(); ?>
                                <?= csrf_field() ?>

                                <div class="col-md-6">
                                    <label class="form-label">Password Saat Ini</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <input id="current_password" type="password" name="password"
                                            class="form-control <?= $v->hasError('password') ? 'is-invalid' : '' ?>"
                                            placeholder="••••••••">
                                    </div>
                                    <?php if ($v->hasError('password')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Password Baru</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                        <input id="new_password" type="password" name="new_password"
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

                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-check"></i></span>
                                        <input id="new_password_confirm" type="password" name="new_password_confirm"
                                            class="form-control <?= $v->hasError('new_password_confirm') ? 'is-invalid' : '' ?>"
                                            placeholder="Ulangi password baru">
                                    </div>
                                    <?php if ($v->hasError('new_password_confirm')): ?>
                                        <div class="invalid-feedback d-block"><?= $v->getError('new_password_confirm') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                    <button type="reset" class="btn btn-outline-secondary rounded-pill py-2">Reset</button>

                                    <!-- UBAH: tombol submit pakai spinner & teks -->
                                    <button id="btnSubmitAccount" type="submit" class="btn btn-brand ring-focus rounded-pill py-2 btn-sm d-inline-flex align-items-center">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                        <span class="btn-text"><i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan</span>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyBtn = document.getElementById('copyEmail');
        if (!copyBtn) return;

        copyBtn.addEventListener('click', async function() {
            const email = copyBtn.getAttribute('data-email') || '';
            if (!email) return;

            // Coba Clipboard API dulu
            try {
                await (navigator.clipboard?.writeText(email));
            } catch (err) {
                // Fallback: execCommand
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

            // Feedback visual singkat
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

    document.addEventListener('DOMContentLoaded', function() {
        // Helper: aktifkan UI loading di tombol + (opsional) overlay
        function armLoading(btn, overlayEl) {
            if (!btn) return;
            if (btn.dataset.loading === '1') return; // cegah double
            btn.dataset.loading = '1';

            const spin = btn.querySelector('.spinner-border');
            const txt = btn.querySelector('.btn-text');

            if (spin) spin.classList.remove('d-none');
            if (txt) txt.textContent = 'Loading…';

            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('disabled');
            if (overlayEl) overlayEl.classList.remove('d-none');

            const form = btn.closest('form');
            if (!form) return;

            // Tahan submit sejenak supaya repaint terjadi → submit manual
            // (penting supaya spinner/teks sempat tampil sebelum reload)
            event?.preventDefault?.();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    form.submit();
                });
            });
        }

        // ====== Form DATA AKUN ======
        const btnAccount = document.getElementById('btnSubmitAccount');
        if (btnAccount) {
            btnAccount.addEventListener('click', function(e) {
                armLoading(btnAccount, null); // tanpa overlay
            });

            // Fallback kalau user tekan Enter (submit form langsung)
            const accountForm = btnAccount.closest('form');
            if (accountForm) {
                accountForm.addEventListener('submit', function(e) {
                    if (btnAccount.dataset.loading === '1') return;
                    e.preventDefault();
                    armLoading(btnAccount, null);
                });
            }
        }

        // ====== Form PASSWORD ======
        const btnPw = document.getElementById('btnSubmitPw');
        const blkPw = document.getElementById('formBlocker'); // overlay form password (kalau mau)
        if (btnPw) {
            btnPw.addEventListener('click', function(e) {
                armLoading(btnPw, blkPw);
            });

            const pwForm = btnPw.closest('form');
            if (pwForm) {
                pwForm.addEventListener('submit', function(e) {
                    if (btnPw.dataset.loading === '1') return;
                    e.preventDefault();
                    armLoading(btnPw, blkPw);
                });
            }
        }
    });
</script>

<?= $this->endSection() ?>