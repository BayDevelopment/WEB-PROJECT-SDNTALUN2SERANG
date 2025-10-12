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

    .form-lock {
        position: relative
    }

    .form-lock::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .4);
        pointer-events: auto
    }
</style>

<?php
/** @var array $siswa */
$v = $validation ?? \Config\Services::validation();
$foto = trim((string)($siswa['photo'] ?? ''));
$imgCurrent = $foto !== '' ? base_url('assets/img/uploads/' . $foto) : base_url('assets/img/user.png');
// pakai NISN di URL (mengikuti link edit/detail kamu)
$nisnKey = urlencode((string)($siswa['nisn'] ?? ''));
?>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Edit Siswa') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-siswa') ?>">Data Siswa</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-pen me-2"></i> Form Edit Siswa
            </div>
        </div>

        <div class="card-body">
            <form id="formEditSiswa"
                action="<?= site_url('operator/edit-siswa/' . $nisnKey) ?>"
                method="post"
                enctype="multipart/form-data"
                autocomplete="off"
                novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <!-- simpan foto lama untuk fallback di controller bila tidak upload baru -->
                <input type="hidden" name="photo_old" value="<?= esc($siswa['photo'] ?? '', 'attr') ?>">
                <!-- kalau perlu deteksi perubahan NISN -->
                <input type="hidden" name="nisn_original" value="<?= esc($siswa['nisn'] ?? '', 'attr') ?>">

                <?php
                // Ambil error per-field dari flash session (controller: ->with('errors', $this->validator->getErrors()))
                $errors = session('errors') ?? [];
                $hasErr = fn(string $f) => isset($errors[$f]);
                $getErr = fn(string $f) => $errors[$f] ?? '';
                ?>

                <div class="row g-3 mb-3">
                    <!-- user (readonly + hidden agar tetap terkirim) -->
                    <div class="col-12">
                        <label class="form-label">User</label>
                        <input type="text" class="form-control" value="<?= esc($siswa['user_name'] ?? ($siswa['full_name'] ?? '—')) ?>" readonly>
                        <input type="hidden" name="user_id" value="<?= esc($siswa['user_id'] ?? '', 'attr') ?>">
                        <?php if ($hasErr('user_id')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('user_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- NISN -->
                    <div class="col-md-6">
                        <label for="nisn" class="form-label">NISN</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('nisn') ? ' is-invalid' : '' ?>"
                            id="nisn" name="nisn"
                            value="<?= esc(old('nisn', $siswa['nisn'] ?? '')) ?>"
                            required
                            maxlength="16"
                            inputmode="numeric"
                            pattern="\d{8,16}"
                            aria-describedby="nisnFeedback">
                        <?php if ($hasErr('nisn')): ?>
                            <div id="nisnFeedback" class="invalid-feedback d-block"><?= esc($getErr('nisn')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center justify-content-between">
                            <span>Kelas</span>
                            <?php if (!empty($siswa['kelas_name'])): ?>
                                <span class="badge bg-success">Saat ini: <?= esc($siswa['kelas_name']) ?></span>
                            <?php endif; ?>
                        </label>

                        <select name="id_kelas" class="form-select<?= $hasErr('id_kelas') ? ' is-invalid' : '' ?>" required>
                            <option value="" disabled <?= old('id_kelas', (int)($siswa['id_kelas'] ?? 0)) ? '' : 'selected' ?>>— Pilih Kelas —</option>
                            <?php foreach (($kelasList ?? []) as $k): ?>
                                <?php
                                $val = (int)$k['id_kelas'];
                                $selected = (int)old('id_kelas', (int)($siswa['id_kelas'] ?? 0)) === $val ? 'selected' : '';
                                ?>
                                <option value="<?= esc($val, 'attr') ?>" <?= $selected ?>>
                                    <?= esc($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($hasErr('id_kelas')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('id_kelas')) ?></div>
                        <?php endif; ?>

                        <!-- Simpan id_kelas awal (opsional, jika ingin bandingkan di controller) -->
                        <input type="hidden" name="id_kelas_now" value="<?= esc((int)($siswa['id_kelas'] ?? 0), 'attr') ?>">
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Nama Lengkap</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('full_name') ? ' is-invalid' : '' ?>"
                            id="full_name" name="full_name"
                            value="<?= esc(old('full_name', $siswa['full_name'] ?? '')) ?>"
                            placeholder="Masukan nama lengkap.."
                            required
                            aria-describedby="fullNameFeedback">
                        <?php if ($hasErr('full_name')): ?>
                            <div id="fullNameFeedback" class="invalid-feedback d-block"><?= esc($getErr('full_name')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="col-md-6">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <?php $gOld = old('gender', $siswa['gender'] ?? ''); ?>
                        <select
                            class="form-select<?= $hasErr('gender') ? ' is-invalid' : '' ?>"
                            id="gender" name="gender" required aria-describedby="genderFeedback">
                            <option value="" disabled <?= $gOld ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="L" <?= $gOld === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $gOld === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <?php if ($hasErr('gender')): ?>
                            <div id="genderFeedback" class="invalid-feedback d-block"><?= esc($getErr('gender')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tempat Lahir -->
                    <div class="col-md-6">
                        <label for="birth_place" class="form-label">Tempat Lahir</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('birth_place') ? ' is-invalid' : '' ?>"
                            id="birth_place" name="birth_place"
                            value="<?= esc(old('birth_place', $siswa['birth_place'] ?? '')) ?>"
                            required
                            aria-describedby="birthPlaceFeedback">
                        <?php if ($hasErr('birth_place')): ?>
                            <div id="birthPlaceFeedback" class="invalid-feedback d-block"><?= esc($getErr('birth_place')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="col-md-6">
                        <label for="birth_date" class="form-label">Tanggal Lahir</label>
                        <input
                            type="date"
                            class="form-control<?= $hasErr('birth_date') ? ' is-invalid' : '' ?>"
                            id="birth_date" name="birth_date"
                            value="<?= esc(old('birth_date', $siswa['birth_date'] ?? '')) ?>"
                            required
                            aria-describedby="birthDateFeedback">
                        <?php if ($hasErr('birth_date')): ?>
                            <div id="birthDateFeedback" class="invalid-feedback d-block"><?= esc($getErr('birth_date')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Alamat -->
                    <div class="col-12">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea
                            class="form-control<?= $hasErr('address') ? ' is-invalid' : '' ?>"
                            id="address" name="address" rows="3"
                            placeholder="Tulis alamat lengkap"
                            aria-describedby="addressFeedback"><?= esc(old('address', $siswa['address'] ?? '')) ?></textarea>
                        <?php if ($hasErr('address')): ?>
                            <div id="addressFeedback" class="invalid-feedback d-block"><?= esc($getErr('address')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Orang Tua/Wali -->
                    <div class="col-md-6">
                        <label for="parent_name" class="form-label">Nama Orang Tua/Wali</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('parent_name') ? ' is-invalid' : '' ?>"
                            id="parent_name" name="parent_name"
                            value="<?= esc(old('parent_name', $siswa['parent_name'] ?? '')) ?>"
                            required
                            aria-describedby="parentNameFeedback">
                        <?php if ($hasErr('parent_name')): ?>
                            <div id="parentNameFeedback" class="invalid-feedback d-block"><?= esc($getErr('parent_name')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label">No. HP / WhatsApp</label>
                        <input
                            type="tel"
                            class="form-control<?= $hasErr('phone') ? ' is-invalid' : '' ?>"
                            id="phone" name="phone"
                            value="<?= esc(old('phone', $siswa['phone'] ?? '')) ?>"
                            placeholder="08xxxxxxxxxx"
                            required minlength="8" maxlength="20"
                            pattern="\d{8,20}" inputmode="numeric"
                            aria-describedby="phoneFeedback">
                        <?php if ($hasErr('phone')): ?>
                            <div id="phoneFeedback" class="invalid-feedback d-block"><?= esc($getErr('phone')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Foto -->
                    <div class="col-md-6">
                        <label for="photo" class="form-label">Pas Foto (opsional)</label>
                        <div class="input-group">
                            <input
                                type="file"
                                class="form-control<?= $hasErr('photo') ? ' is-invalid' : '' ?>"
                                id="photo" name="photo"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                aria-describedby="photoFeedback">
                            <label class="input-group-text" for="photo">
                                <i class="fa-solid fa-upload me-2"></i> Ganti
                            </label>
                        </div>
                        <div class="form-text">JPG/PNG maks. 2MB. Kosongkan jika tidak ingin mengganti.</div>
                        <?php if ($hasErr('photo')): ?>
                            <div id="photoFeedback" class="invalid-feedback d-block"><?= esc($getErr('photo')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Preview -->
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="ms-md-auto text-center">
                            <img id="previewPhoto" src="<?= $imgCurrent ?>" alt="Preview" class="avatar-80 rounded mb-2">
                            <div>
                                <button type="button" id="btnResetFoto" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Reset Foto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill">
                        <span class="btn-text">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Update
                        </span>
                    </button>

                    <a href="<?= base_url('operator/data-siswa') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- JS: preview foto, reset foto, dan loading state submit -->
<script>
    (function() {
        const form = document.getElementById('formEditSiswa');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');
        const inputFile = document.getElementById('photo');
        const imgPrev = document.getElementById('previewPhoto');
        const btnResetFoto = document.getElementById('btnResetFoto');
        const originalSrc = "<?= $imgCurrent ?>";

        // Preview saat pilih foto
        inputFile?.addEventListener('change', function() {
            const f = this.files?.[0];
            if (f) {
                const url = URL.createObjectURL(f);
                imgPrev.src = url;
            } else {
                imgPrev.src = originalSrc;
            }
        });

        // Reset ke foto lama
        btnResetFoto?.addEventListener('click', function() {
            if (inputFile) inputFile.value = '';
            imgPrev.src = originalSrc;
        });

        // Submit overlay (tanpa disable field agar nilai terkirim)
        form?.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            }
            btnSubmit.disabled = true;
            form.classList.add('form-lock');

            // pastikan CSRF tidak pernah disabled
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    })();
</script>

<?= $this->endSection() ?>