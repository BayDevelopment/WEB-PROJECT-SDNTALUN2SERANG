<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4 page-title">Profil Saya</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item"><a href="<?= base_url('siswa/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>

    <?php $v = \Config\Services::validation();
    $s = session(); ?>

    <div class="row g-4">
        <!-- LEFT: Profile Card -->
        <div class="col-12 col-lg-4">
            <div class="card card-modern profile-card">
                <div class="profile-cover"></div>

                <div class="card-body text-center pb-0">
                    <div class="avatar-wrap">
                        <img src="<?= esc($user['avatar_url'] ?? base_url('assets/img/avatar-default.png')) ?>" class="avatar-xl" alt="Avatar">
                        <form action="<?= base_url('profile/avatar') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="file" name="avatar" id="avatar-input" class="d-none" accept="image/*">
                            <label for="avatar-input" class="btn-ghost btn-change-photo" title="Ganti foto">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                        </form>
                    </div>
                    <h5 class="mt-3 mb-1"><?= esc($user['nama_lengkap'] ?? 'Nama Pengguna') ?></h5>
                    <div class="text-sub small">
                        <i class="fa-regular fa-envelope me-1"></i><?= esc($user['email'] ?? '-') ?>
                    </div>
                    <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge rounded-pill bg-brand-soft"><i class="fa-solid fa-user-shield me-1"></i><?= esc(session('role') ?? 'User') ?></span>
                        <span class="badge rounded-pill bg-brand-soft"><i class="fa-regular fa-id-badge me-1"></i><?= esc($user['nip'] ?? 'N/A') ?></span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i>Terakhir diperbarui: <?= esc($user['updated_at'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Tabs -->
        <div class="col-12 col-lg-8">
            <div class="card card-modern">
                <div class="card-body pb-0">
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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-preference" data-bs-toggle="tab" data-bs-target="#pane-preference" type="button" role="tab">
                                <i class="fa-solid fa-gear me-1"></i> Preferensi
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="profileTabContent">
                        <!-- Data Akun -->
                        <div class="tab-pane fade show active" id="pane-account" role="tabpanel">
                            <form action="<?= base_url('profile/update') ?>" method="post" class="row g-3">
                                <?= csrf_field() ?>

                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" value="<?= old('nama_lengkap', $user['nama_lengkap'] ?? '') ?>"
                                        class="form-control <?= $v->hasError('nama_lengkap') ? 'is-invalid' : '' ?>">
                                    <div class="invalid-feedback"><?= $v->getError('nama_lengkap') ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>"
                                        class="form-control <?= $v->hasError('email') ? 'is-invalid' : '' ?>">
                                    <div class="invalid-feedback"><?= $v->getError('email') ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="no_telp" value="<?= old('no_telp', $user['no_telp'] ?? '') ?>" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-select">
                                        <option value="" <?= (old('jenis_kelamin', $user['jenis_kelamin'] ?? '') === '') ? 'selected' : '' ?>>— Pilih —</option>
                                        <option value="L" <?= (old('jenis_kelamin', $user['jenis_kelamin'] ?? '') === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="P" <?= (old('jenis_kelamin', $user['jenis_kelamin'] ?? '') === 'P') ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir" value="<?= old('tgl_lahir', $user['tgl_lahir'] ?? '') ?>" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" rows="3" class="form-control"><?= old('alamat', $user['alamat'] ?? '') ?></textarea>
                                </div>

                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="reset" class="btn btn-light">Reset</button>
                                    <button type="submit" class="btn btn-brand ring-focus"><i class="fa-regular fa-floppy-disk me-1"></i> Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>

                        <!-- Keamanan -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel">
                            <form action="<?= base_url('profile/password') ?>" method="post" class="row g-3">
                                <?= csrf_field() ?>

                                <div class="col-md-6">
                                    <label class="form-label">Password Saat Ini</label>
                                    <input type="password" name="current_password"
                                        class="form-control <?= $v->hasError('current_password') ? 'is-invalid' : '' ?>">
                                    <div class="invalid-feedback"><?= $v->getError('current_password') ?></div>
                                </div>

                                <div class="col-md-6"></div>

                                <div class="col-md-6">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="new_password"
                                        class="form-control <?= $v->hasError('new_password') ? 'is-invalid' : '' ?>">
                                    <div class="invalid-feedback"><?= $v->getError('new_password') ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" name="new_password_confirm"
                                        class="form-control <?= $v->hasError('new_password_confirm') ? 'is-invalid' : '' ?>">
                                    <div class="invalid-feedback"><?= $v->getError('new_password_confirm') ?></div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-brand"><i class="fa-solid fa-shield-halved me-1"></i> Update Password</button>
                                </div>
                            </form>
                        </div>

                        <!-- Preferensi -->
                        <div class="tab-pane fade" id="pane-preference" role="tabpanel">
                            <form action="<?= base_url('profile/preference') ?>" method="post" class="row g-3">
                                <?= csrf_field() ?>

                                <div class="col-md-6">
                                    <label class="form-label">Tema</label>
                                    <select name="theme" class="form-select">
                                        <option value="light" <?= (old('theme', $user['theme'] ?? 'light') === 'light') ? 'selected' : '' ?>>Light</option>
                                        <option value="dark" <?= (old('theme', $user['theme'] ?? 'light') === 'dark')  ? 'selected' : '' ?>>Dark</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Bahasa</label>
                                    <select name="lang" class="form-select">
                                        <option value="id" <?= (old('lang', $user['lang'] ?? 'id') === 'id') ? 'selected' : '' ?>>Indonesia</option>
                                        <option value="en" <?= (old('lang', $user['lang'] ?? 'id') === 'en') ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-brand"><i class="fa-regular fa-circle-check me-1"></i> Simpan Preferensi</button>
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
    // Preview avatar + auto submit
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const img = document.querySelector('.avatar-xl');
            const url = URL.createObjectURL(file);
            img.src = url;
            avatarInput.closest('form').submit();
        });
    }

    // Simpan tab aktif via hash (#account/#security/#preference)
    const pills = document.querySelectorAll('#profileTabs [data-bs-toggle="tab"]');
    pills.forEach(btn => btn.addEventListener('shown.bs.tab', () => {
        const target = btn.getAttribute('data-bs-target');
        history.replaceState(null, '', target.replace('#pane-', '#'));
    }));
    // Restore tab dari hash
    const currentHash = location.hash.replace('#', '');
    if (currentHash) {
        const btn = document.querySelector(`[data-bs-target="#pane-${currentHash}"]`);
        if (btn) new bootstrap.Tab(btn).show();
    }
</script>
<?= $this->endSection() ?>