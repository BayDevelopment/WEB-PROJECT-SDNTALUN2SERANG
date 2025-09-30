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
            Total Siswa: <strong><?= isset($d_siswa) ? number_format(count($d_siswa), 0, ',', '.') : 0 ?></strong>
        </div>
    </div>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-plus me-2"></i> Form Tambah Siswa
            </div>
        </div>

        <div class="card-body">
            <?php $v = $validation ?? \Config\Services::validation(); ?>

            <form id="formTambahSiswa"
                action="<?= site_url('operator/tambah-siswa') ?>"
                method="post"
                enctype="multipart/form-data"
                autocomplete="off">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <!-- user_id -->
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Data User</label>

                        <?php if (!empty($d_user) && is_array($d_user)): ?>
                            <select name="user_id" id="user_id"
                                class="form-select<?= $v->hasError('user_id') ? ' is-invalid' : '' ?>"
                                required>
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
                            <?php if ($v->hasError('user_id')): ?>
                                <div class="invalid-feedback d-block"><?= esc($v->getError('user_id')) ?></div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-warning mb-2">User tidak ditemukan. Tambahkan user terlebih dahulu.</div>
                            <select class="form-select" id="user_id" disabled>
                                <option>— Tidak ada data user —</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- nisn -->
                    <div class="col-md-6">
                        <label for="nisn" class="form-label">NISN</label>
                        <input type="text"
                            class="form-control<?= $v->hasError('nisn') ? ' is-invalid' : '' ?>"
                            id="nisn" name="nisn"
                            placeholder="Masukkan NISN"
                            value="<?= esc(old('nisn') ?? '') ?>"
                            required maxlength="16" inputmode="numeric">
                        <?php if ($v->hasError('nisn')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('nisn')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- full_name -->
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Nama Lengkap</label>
                        <input type="text"
                            class="form-control<?= $v->hasError('full_name') ? ' is-invalid' : '' ?>"
                            id="full_name" name="full_name"
                            placeholder="Masukkan nama lengkap"
                            value="<?= esc(old('full_name') ?? '') ?>"
                            required>
                        <?php if ($v->hasError('full_name')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('full_name')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- gender -->
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <?php $oldGender = old('gender'); ?>
                        <select class="form-select<?= $v->hasError('gender') ? ' is-invalid' : '' ?>"
                            name="gender" id="jenis_kelamin" required>
                            <option value="" disabled <?= $oldGender ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="L" <?= $oldGender === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $oldGender === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <?php if ($v->hasError('gender')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('gender')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- birth_place -->
                    <div class="col-md-6">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text"
                            class="form-control<?= $v->hasError('birth_place') ? ' is-invalid' : '' ?>"
                            id="tempat_lahir" name="birth_place"
                            placeholder="Contoh: Jakarta"
                            value="<?= esc(old('birth_place') ?? '') ?>"
                            required>
                        <?php if ($v->hasError('birth_place')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('birth_place')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- birth_date -->
                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date"
                            class="form-control<?= $v->hasError('birth_date') ? ' is-invalid' : '' ?>"
                            id="tanggal_lahir" name="birth_date"
                            value="<?= esc(old('birth_date') ?? '') ?>"
                            required>
                        <?php if ($v->hasError('birth_date')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('birth_date')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- address -->
                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control<?= $v->hasError('address') ? ' is-invalid' : '' ?>"
                            id="alamat" name="address" rows="3"
                            placeholder="Tulis alamat lengkap"><?= esc(old('address') ?? '') ?></textarea>
                        <?php if ($v->hasError('address')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('address')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- parent_name -->
                    <div class="col-md-6">
                        <label for="nama_ortu" class="form-label">Nama Orang Tua/Wali</label>
                        <input type="text"
                            class="form-control<?= $v->hasError('parent_name') ? ' is-invalid' : '' ?>"
                            id="nama_ortu" name="parent_name"
                            placeholder="Nama wali utama"
                            value="<?= esc(old('parent_name') ?? '') ?>"
                            required>
                        <?php if ($v->hasError('parent_name')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('parent_name')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- phone -->
                    <div class="col-md-6">
                        <label for="no_hp" class="form-label">No. HP / WhatsApp</label>
                        <input type="tel"
                            class="form-control<?= $v->hasError('phone') ? ' is-invalid' : '' ?>"
                            id="no_hp" name="phone"
                            placeholder="08xxxxxxxxxx"
                            value="<?= esc(old('phone') ?? '') ?>"
                            required>
                        <?php if ($v->hasError('phone')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('phone')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- photo -->
                    <div class="col-md-6">
                        <label for="pas_photo" class="form-label">Pas Foto</label>
                        <div class="input-group">
                            <input type="file"
                                class="form-control<?= $v->hasError('photo') ? ' is-invalid' : '' ?>"
                                id="pas_photo" name="photo" accept="image/*">
                            <label class="input-group-text" for="pas_photo">
                                <i class="fa-solid fa-upload me-2"></i> Upload
                            </label>
                        </div>
                        <div class="form-text">Format JPG/PNG, maks. ±2MB.</div>
                        <?php if ($v->hasError('photo')): ?>
                            <div class="invalid-feedback d-block"><?= esc($v->getError('photo')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="avatar-preview ms-md-auto">
                            <img id="previewPhoto" src="<?= base_url('assets/img/user.png') ?>" alt="Preview" class="avatar-80 rounded">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill">
                        <span class="btn-text">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan
                        </span>
                    </button>

                    <button type="button" id="btnReset" class="btn btn-outline-secondary rounded-pill">
                        <i class="fa-solid fa-rotate-left me-2"></i> Reset
                    </button>

                    <a href="<?= base_url('operator/data-siswa') ?>" class="btn btn-dark rounded-pill">
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