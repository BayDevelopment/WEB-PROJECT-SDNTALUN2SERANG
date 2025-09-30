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
            Total Guru: <strong><?= isset($d_guru) ? number_format(count($d_guru), 0, ',', '.') : 0 ?></strong>
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
            <?php $v = $validation ?? \Config\Services::validation(); ?>

            <form id="formTambahGuru"
                action="<?= site_url('operator/tambah-guru') ?>"
                method="post"
                enctype="multipart/form-data"
                autocomplete="off"
                novalidate>
                <?= csrf_field() ?>

                <?php
                // Ambil error per-field dari flash session (controller: ->with('errors', $this->validator->getErrors()))
                $errors = session('errors') ?? [];
                $hasErr = fn(string $f) => isset($errors[$f]);
                $getErr = fn(string $f) => $errors[$f] ?? '';
                ?>

                <div class="row g-3 mb-3">
                    <!-- user_id -->
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Data User</label>
                        <?php if (!empty($d_user) && is_array($d_user)): ?>
                            <select name="user_id" id="user_id"
                                class="form-select<?= $hasErr('user_id') ? ' is-invalid' : '' ?>"
                                required aria-describedby="userIdFeedback">
                                <option value="" disabled <?= old('user_id') ? '' : 'selected' ?>>— Pilih User —</option>
                                <?php foreach ($d_user as $u): ?>
                                    <?php
                                    $id   = (int)($u['id_user'] ?? 0);
                                    $name = trim((string)($u['full_name'] ?? $u['username'] ?? '—'));
                                    ?>
                                    <option value="<?= esc($id, 'attr') ?>" <?= (int)old('user_id') === $id ? 'selected' : '' ?>>
                                        <?= esc($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($hasErr('user_id')): ?>
                                <div id="userIdFeedback" class="invalid-feedback d-block"><?= esc($getErr('user_id')) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning mb-2">User tidak ditemukan. Tambahkan user terlebih dahulu.</div>
                            <select class="form-select" id="user_id" disabled>
                                <option>— Tidak ada data user —</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- nip -->
                    <div class="col-md-6">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text"
                            class="form-control<?= $hasErr('nip') ? ' is-invalid' : '' ?>"
                            id="nip" name="nip"
                            placeholder="Masukkan NIP"
                            value="<?= esc(old('nip') ?? '') ?>"
                            required maxlength="30" inputmode="numeric" pattern="\d{8,30}"
                            aria-describedby="nipFeedback">
                        <?php if ($hasErr('nip')): ?>
                            <div id="nipFeedback" class="invalid-feedback d-block"><?= esc($getErr('nip')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- nama_lengkap -->
                    <div class="col-md-6">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text"
                            class="form-control<?= $hasErr('nama_lengkap') ? ' is-invalid' : '' ?>"
                            id="nama_lengkap" name="nama_lengkap"
                            placeholder="Masukkan nama lengkap"
                            value="<?= esc(old('nama_lengkap') ?? '') ?>"
                            required aria-describedby="namaFeedback">
                        <?php if ($hasErr('nama_lengkap')): ?>
                            <div id="namaFeedback" class="invalid-feedback d-block"><?= esc($getErr('nama_lengkap')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- jenis_kelamin -->
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <?php $jkOld = old('jenis_kelamin'); ?>
                        <select class="form-select<?= $hasErr('jenis_kelamin') ? ' is-invalid' : '' ?>"
                            name="jenis_kelamin" id="jenis_kelamin" required aria-describedby="jkFeedback">
                            <option value="" disabled <?= $jkOld ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="L" <?= $jkOld === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $jkOld === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <?php if ($hasErr('jenis_kelamin')): ?>
                            <div id="jkFeedback" class="invalid-feedback d-block"><?= esc($getErr('jenis_kelamin')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- tgl_lahir -->
                    <div class="col-md-6">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date"
                            class="form-control<?= $hasErr('tgl_lahir') ? ' is-invalid' : '' ?>"
                            id="tgl_lahir" name="tgl_lahir"
                            value="<?= esc(old('tgl_lahir') ?? '') ?>"
                            required aria-describedby="tglFeedback">
                        <?php if ($hasErr('tgl_lahir')): ?>
                            <div id="tglFeedback" class="invalid-feedback d-block"><?= esc($getErr('tgl_lahir')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- no_telp -->
                    <div class="col-md-6">
                        <label for="no_telp" class="form-label">No. Handphone / WA</label>
                        <input type="tel"
                            class="form-control<?= $hasErr('no_telp') ? ' is-invalid' : '' ?>"
                            id="no_telp" name="no_telp"
                            value="<?= esc(old('no_telp') ?? '') ?>"
                            placeholder="08xxxxxxxxxx"
                            required minlength="8" maxlength="20" pattern="\d{8,20}" inputmode="numeric"
                            aria-describedby="telpFeedback">
                        <?php if ($hasErr('no_telp')): ?>
                            <div id="telpFeedback" class="invalid-feedback d-block"><?= esc($getErr('no_telp')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- alamat -->
                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control<?= $hasErr('alamat') ? ' is-invalid' : '' ?>"
                            id="alamat" name="alamat" rows="3"
                            placeholder="Tulis alamat lengkap"
                            aria-describedby="alamatFeedback"><?= esc(old('alamat') ?? '') ?></textarea>
                        <?php if ($hasErr('alamat')): ?>
                            <div id="alamatFeedback" class="invalid-feedback d-block"><?= esc($getErr('alamat')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- foto -->
                    <div class="col-md-6">
                        <label for="foto" class="form-label">Pas Foto</label>
                        <div class="input-group">
                            <input type="file"
                                class="form-control<?= $hasErr('foto') ? ' is-invalid' : '' ?>"
                                id="foto" name="foto" accept="image/jpeg,image/png"
                                aria-describedby="fotoFeedback">
                            <label class="input-group-text" for="foto">
                                <i class="fa-solid fa-upload me-2"></i> Upload
                            </label>
                        </div>
                        <div class="form-text">Format JPG/PNG, maks. ±2MB.</div>
                        <?php if ($hasErr('foto')): ?>
                            <div id="fotoFeedback" class="invalid-feedback d-block"><?= esc($getErr('foto')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="avatar-preview ms-md-auto">
                            <img id="previewPhoto" src="<?= base_url('assets/img/user.png') ?>" alt="Preview" class="avatar-80 rounded">
                        </div>
                    </div>

                    <!-- status_active -->
                    <div class="col-md-6">
                        <label class="form-label d-block">Status Akun</label>
                        <input type="hidden" name="status_active" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input<?= $hasErr('status_active') ? ' is-invalid' : '' ?>"
                                type="checkbox" id="status_active" name="status_active" value="1"
                                <?= old('status_active', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_active">Aktif</label>
                        </div>
                        <?php if ($hasErr('status_active')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('status_active')) ?></div>
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

                    <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-dark rounded-pill">
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