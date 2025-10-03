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
$foto = trim((string)($d_guru['photo'] ?? ''));
$imgCurrent = $foto !== '' ? base_url('assets/img/uploads/' . $foto) : base_url('assets/img/user.png');
// pakai NISN di URL (mengikuti link edit/detail kamu)
$nip = urlencode((string)($d_guru['nip'] ?? ''));
?>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Edit Siswa') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-guru') ?>">Data Guru</a></li>
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
            <form id="formEditGuru"
                action="<?= site_url('operator/edit-guru/' . $nip) ?>"
                method="post"
                enctype="multipart/form-data"
                autocomplete="off"
                novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <?php
                // Ambil error per-field dari flash session (controller harus: ->with('errors', $this->validator->getErrors()))
                $errors = session('errors') ?? [];
                $hasErr = fn(string $f) => isset($errors[$f]);
                $getErr = fn(string $f) => $errors[$f] ?? '';
                ?>

                <div class="row g-3 mb-3">
                    <!-- user (readonly + hidden agar tetap terkirim) -->
                    <div class="col-md-6">
                        <label class="form-label">User</label>
                        <input type="text" class="form-control"
                            value="<?= esc($d_guru['user_name'] ?? ($d_guru['nama_lengkap'] ?? '—')) ?>" readonly>
                        <input type="hidden" value="<?= esc($d_guru['user_id'] ?? '', 'attr') ?>">
                        <?php if ($hasErr('user_id')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('user_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- NIP -->
                    <div class="col-md-6">
                        <label for="nip" class="form-label">NIP</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('nip') ? ' is-invalid' : '' ?>"
                            id="nip" name="nip"
                            value="<?= esc(old('nip', $d_guru['nip'] ?? '')) ?>"
                            placeholder="Masukkan NIP"
                            required maxlength="30"
                            inputmode="numeric" pattern="\d{8,30}"
                            aria-describedby="nipFeedback">
                        <?php if ($hasErr('nip')): ?>
                            <div id="nipFeedback" class="invalid-feedback d-block"><?= esc($getErr('nip')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="col-md-6">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('nama_lengkap') ? ' is-invalid' : '' ?>"
                            id="nama_lengkap" name="nama_lengkap"
                            value="<?= esc(old('nama_lengkap', $d_guru['nama_lengkap'] ?? '')) ?>"
                            placeholder="Masukkan nama lengkap"
                            required aria-describedby="namaFeedback">
                        <?php if ($hasErr('nama_lengkap')): ?>
                            <div id="namaFeedback" class="invalid-feedback d-block"><?= esc($getErr('nama_lengkap')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <?php $jkOld = old('jenis_kelamin', $d_guru['jenis_kelamin'] ?? ''); ?>
                        <select
                            class="form-select<?= $hasErr('jenis_kelamin') ? ' is-invalid' : '' ?>"
                            id="jenis_kelamin" name="jenis_kelamin"
                            required aria-describedby="jkFeedback">
                            <option value="" disabled <?= $jkOld ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="L" <?= $jkOld === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $jkOld === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <?php if ($hasErr('jenis_kelamin')): ?>
                            <div id="jkFeedback" class="invalid-feedback d-block"><?= esc($getErr('jenis_kelamin')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="col-md-6">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input
                            type="date"
                            class="form-control<?= $hasErr('tgl_lahir') ? ' is-invalid' : '' ?>"
                            id="tgl_lahir" name="tgl_lahir"
                            value="<?= esc(old('tgl_lahir', $d_guru['tgl_lahir'] ?? '')) ?>"
                            required aria-describedby="tglFeedback">
                        <?php if ($hasErr('tgl_lahir')): ?>
                            <div id="tglFeedback" class="invalid-feedback d-block"><?= esc($getErr('tgl_lahir')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- No. HP / WA -->
                    <div class="col-md-6">
                        <label for="no_telp" class="form-label">No. HP / WhatsApp</label>
                        <input
                            type="tel"
                            class="form-control<?= $hasErr('no_telp') ? ' is-invalid' : '' ?>"
                            id="no_telp" name="no_telp"
                            value="<?= esc(old('no_telp', $d_guru['no_telp'] ?? '')) ?>"
                            placeholder="08xxxxxxxxxx"
                            required minlength="8" maxlength="20"
                            pattern="\d{8,20}" inputmode="numeric"
                            aria-describedby="telpFeedback">
                        <?php if ($hasErr('no_telp')): ?>
                            <div id="telpFeedback" class="invalid-feedback d-block"><?= esc($getErr('no_telp')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Alamat -->
                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea
                            class="form-control<?= $hasErr('alamat') ? ' is-invalid' : '' ?>"
                            id="alamat" name="alamat" rows="3"
                            placeholder="Tulis alamat lengkap"
                            aria-describedby="alamatFeedback"><?= esc(old('alamat', $d_guru['alamat'] ?? '')) ?></textarea>
                        <?php if ($hasErr('alamat')): ?>
                            <div id="alamatFeedback" class="invalid-feedback d-block"><?= esc($getErr('alamat')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Foto -->
                    <div class="col-md-6">
                        <label for="foto" class="form-label">Pas Foto (opsional)</label>
                        <div class="input-group">
                            <input
                                type="file"
                                class="form-control<?= $hasErr('foto') ? ' is-invalid' : '' ?>"
                                id="foto" name="foto"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                aria-describedby="fotoFeedback">
                            <label class="input-group-text" for="foto">
                                <i class="fa-solid fa-upload me-2"></i> Ganti
                            </label>
                        </div>
                        <div class="form-text">JPG/PNG maks. 2MB. Kosongkan jika tidak ingin mengganti.</div>
                        <?php if ($hasErr('foto')): ?>
                            <div id="fotoFeedback" class="invalid-feedback d-block"><?= esc($getErr('foto')) ?></div>
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

                    <!-- Status Aktif -->
                    <div class="col-md-6">
                        <label class="form-label d-block">Status Akun</label>
                        <input type="hidden" name="status_active" value="0">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input<?= $hasErr('status_active') ? ' is-invalid' : '' ?>"
                                type="checkbox" id="status_active" name="status_active" value="1"
                                <?= old('status_active', (string)($d_guru['status_active'] ?? '1')) == '1' ? 'checked' : '' ?>>
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
                            <i class="fa-solid fa-floppy-disk me-2"></i> Update
                        </span>
                    </button>

                    <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-dark rounded-pill">
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
        const form = document.getElementById('formEditGuru');
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