<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
    :root {
        --accent: #0d6efd;
        --accent-100: #cfe2ff;
        --accent-200: #9ec5fe;
        --muted: #6b7280;
        --border: #e9ecef;
        --text: #0f172a;
        --shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .06);
        --shadow-lg: 0 .7rem 1.4rem rgba(15, 23, 42, .09);
    }

    .page-title {
        font-weight: 800;
        letter-spacing: .2px
    }

    .card-modern {
        border-radius: 18px;
        background: #fff;
        box-shadow: var(--shadow);
        border: 1px solid transparent;
        background-image: linear-gradient(#fff, #fff),
            linear-gradient(180deg, rgba(13, 110, 253, .25), rgba(13, 110, 253, .05));
        background-origin: border-box;
        background-clip: padding-box, border-box;
    }

    .card-modern .card-header {
        background: #fff;
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        color: var(--text);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-modern .card-header:after {
        content: "";
        height: 3px;
        width: 36px;
        border-radius: 4px;
        background: var(--accent);
        display: inline-block;
        margin-left: auto;
        opacity: .25;
    }

    .avatar-lg {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid var(--border)
    }

    .badge-soft {
        background: var(--accent-100);
        color: var(--accent);
        border: 1px solid var(--accent-200)
    }

    .divider-soft {
        border: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #eef2f6, transparent);
        margin: 16px 0
    }

    .list-kv {
        margin: 0;
        padding: 0;
        list-style: none
    }

    .list-kv li {
        display: flex;
        gap: 12px;
        justify-content: space-between;
        align-items: center;
        padding: .55rem 0;
        border-bottom: 1px dashed #f1f3f5
    }

    .list-kv li:last-child {
        border-bottom: 0
    }

    .kv-k {
        color: var(--muted);
        min-width: 46%
    }

    .kv-v {
        font-weight: 600;
        text-align: right;
        word-break: break-word
    }

    .profile-narrow {
        max-width: 720px;
        margin: 0 auto;
    }

    /* batasi lebar isi card */
    .list-kv {
        margin: 0 auto;
        padding: 0;
        list-style: none;
        max-width: 720px
    }

    .list-kv li {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: .6rem .25rem;
        border-bottom: 1px dashed #f1f3f5
    }

    .list-kv li:last-child {
        border-bottom: 0
    }

    .kv-k {
        color: #6b7280;
        flex: 0 0 42%;
        text-align: left
    }

    /* kunci */
    .kv-v {
        flex: 1 0 58%;
        font-weight: 600;
        text-align: left
    }

    /* nilai */
    .avatar-lg {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e9ecef
    }

    /* batasi lebar area konten & kasih padding samping */
    .page-content-narrow {
        max-width: 980px;
        /* atur sesuai selera (860–1100px) */
        margin: 0 auto;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    /* tambahkan padding dalam card */
    .card-modern .card-body {
        padding: 20px 24px;
    }

    @media (min-width: 992px) {
        .card-modern .card-body {
            padding: 24px 28px;
        }
    }

    /* daftar key/value supaya tidak mepet sisi */
    .list-kv {
        padding: 0 .25rem;
    }

    /* Batasi lebar area konten di tengah + kasih padding samping */
    .page-narrow {
        max-width: 1080px;
        /* atur sesuai selera: 960–1140px */
        margin: 0 auto;
        padding-left: 12px;
        padding-right: 12px;
    }

    @media (min-width: 1400px) {
        .page-narrow {
            max-width: 1140px;
        }
    }

    /* Persempit blok header profil & daftar key/value di dalam card */
    .profile-narrow {
        max-width: 640px;
        margin-inline: auto;
    }

    .list-kv {
        max-width: 640px;
        margin-inline: auto;
        padding-inline: .25rem;
    }
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4 page-title">Profil Saya</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item"><a href="<?= base_url('siswa/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>

    <?php
    /** @var \CodeIgniter\Validation\Validation $v */
    $v = \Config\Services::validation();

    // Foto: prioritas dari SiswaModel->photo lalu UserModel->avatar_url
    $fotoSiswa  = trim((string)($siswa['photo'] ?? ''));
    $avatarUser = trim((string)($user['avatar_url'] ?? ''));
    if ($fotoSiswa !== '') {
        $avatar = base_url('assets/img/uploads/' . $fotoSiswa);
    } elseif ($avatarUser !== '') {
        $avatar = $avatarUser;
    } else {
        $avatar = base_url('assets/img/avatar-default.png');
    }

    // Status & gender
    $isActive = (int)($user['is_active'] ?? 0) === 1;
    $gRaw = strtoupper((string)($siswa['gender'] ?? ''));
    $genderLabel = $gRaw === 'L' ? 'Laki-laki' : ($gRaw === 'P' ? 'Perempuan' : '—');
    ?>

    <!-- WRAPPER pusat + lebar terbatas -->
    <div class="row justify-content-center page-narrow mb-3">
        <div class="col-12 col-xl-10">
            <div class="row g-4">

                <!-- CARD: Data Diri (kode kamu apa adanya) -->
                <div class="col-12 col-lg-6">
                    <div class="card-modern">
                        <div class="card-header">
                            <i class="fa-regular fa-id-card text-primary"></i> Data Diri
                        </div>

                        <div class="card-body">
                            <!-- Header profil (dipersempit & center) -->
                            <div class="profile-narrow">
                                <div class="row align-items-center g-3 mb-2 text-center text-md-start">
                                    <div class="col-12 col-md-auto">
                                        <img src="<?= esc($avatar) ?>" alt="Foto Profil" class="avatar-lg">
                                    </div>
                                    <div class="col">
                                        <div class="h5 mb-1"><?= esc($siswa['full_name'] ?? '—') ?></div>
                                        <div class="small text-muted">
                                            <i class="fa-regular fa-envelope me-1"></i><?= esc($user['email'] ?? '—') ?>
                                        </div>
                                        <div class="mt-2 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                            <span class="badge badge-soft">
                                                <i class="fa-solid fa-user-shield me-1"></i><?= esc(session('role') ?? 'User') ?>
                                            </span>
                                            <span class="badge <?= $isActive ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                                <i class="fa-regular fa-circle-check me-1"></i><?= $isActive ? 'Akun Aktif' : 'Akun Nonaktif' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="divider-soft">

                            <!-- Daftar key/value (dipersempit & rata kiri) -->
                            <ul class="list-kv">
                                <li><span class="kv-k">NISN</span><span class="kv-v"><?= esc($siswa['nisn'] ?? '—') ?></span></li>
                                <li><span class="kv-k">Jenis Kelamin</span><span class="kv-v"><?= esc($genderLabel) ?></span></li>
                                <li><span class="kv-k">No. HP</span><span class="kv-v"><?= esc($siswa['phone'] ?? '—') ?></span></li>
                                <li>
                                    <span class="kv-k">Tempat/Tanggal Lahir</span>
                                    <span class="kv-v">
                                        <?= esc($siswa['birth_place'] ?? '—') ?>
                                        <?= (!empty($siswa['birth_place']) && !empty($siswa['birth_date'])) ? ' / ' : '' ?>
                                        <?= esc($siswa['birth_date'] ?? '') ?>
                                    </span>
                                </li>
                                <li><span class="kv-k">Alamat</span><span class="kv-v"><?= esc($siswa['address'] ?? '—') ?></span></li>
                                <li>
                                    <span class="kv-k">Terakhir Diperbarui</span>
                                    <span class="kv-v"><?= esc($siswa['updated_at'] ?? $user['updated_at'] ?? '—') ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CARD: Keamanan Akun (kode kamu apa adanya) -->
                <div class="col-12 col-lg-6">
                    <div class="card-modern">
                        <div class="card-header">
                            <i class="fa-solid fa-lock text-primary"></i> Keamanan Akun
                        </div>
                        <div class="card-body">
                            <!-- Ganti Username -->
                            <form id="formUsername" action="<?= base_url('siswa/profile/username') ?>" method="post" class="row g-3 mb-3" autocomplete="on">
                                <?= csrf_field() ?>
                                <div class="col-12">
                                    <label for="username" class="form-label">Username</label>
                                    <input
                                        type="text"
                                        id="username"
                                        name="username"
                                        value="<?= old('username', $user['username'] ?? '') ?>"
                                        class="form-control <?= $v->hasError('username') ? 'is-invalid' : '' ?>"
                                        autocomplete="username"
                                        inputmode="text"
                                        minlength="4" maxlength="20"
                                        placeholder="mis. budi_21">
                                    <div class="invalid-feedback"><?= $v->getError('username') ?></div>
                                    <div class="form-text">Gunakan 4–20 karakter tanpa spasi.</div>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" id="btnUsername" class="btn btn-primary">
                                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                                        <i class="fa-regular fa-pen-to-square me-1"></i> Simpan Username
                                    </button>
                                </div>
                            </form>

                            <hr class="divider-soft">

                            <!-- Ganti Password -->
                            <form id="formPassword" action="<?= base_url('siswa/profile/password') ?>" method="post" class="row g-3" autocomplete="on">
                                <?= csrf_field() ?>

                                <div class="col-md-12">
                                    <label for="old_password" class="form-label">Password Saat Ini</label>
                                    <input
                                        type="password"
                                        id="old_password"
                                        name="old_password"
                                        class="form-control <?= $v->hasError('old_password') ? 'is-invalid' : '' ?>"
                                        autocomplete="current-password"
                                        placeholder="••••••••">
                                    <div class="invalid-feedback"><?= $v->getError('old_password') ?></div>
                                </div>

                                <div class="col-md-12">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control <?= $v->hasError('password') ? 'is-invalid' : '' ?>"
                                        autocomplete="new-password"
                                        placeholder="Minimal 8 karakter">
                                    <div class="invalid-feedback"><?= $v->getError('password') ?></div>
                                    <div class="form-text">Disarankan kombinasi huruf besar/kecil, angka, dan simbol.</div>
                                </div>

                                <div class="col-md-12">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input
                                        type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        class="form-control <?= $v->hasError('confirm_password') ? 'is-invalid' : '' ?>"
                                        autocomplete="new-password"
                                        placeholder="Ulangi password baru">
                                    <div class="invalid-feedback"><?= $v->getError('confirm_password') ?></div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" id="btnPassword" class="btn btn-success">
                                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formUsername = document.getElementById('formUsername');
        const btnUsername = document.getElementById('btnUsername');
        const spinnerUsername = btnUsername.querySelector('.spinner-border');

        const formPassword = document.getElementById('formPassword');
        const btnPassword = document.getElementById('btnPassword');
        const spinnerPassword = btnPassword.querySelector('.spinner-border');
        formUsername?.addEventListener('submit', function(e) {
            btnUsername.disabled = true; // Disable tombol submit
            spinnerUsername.classList.remove('d-none'); // Tampilkan spinner
            // Jangan disable input agar data bisa dikirim
        });

        formPassword?.addEventListener('submit', function(e) {
            btnPassword.disabled = true;
            spinnerPassword.classList.remove('d-none');
            // Jangan disable input
        });

    });
</script>

<?= $this->endSection() ?>