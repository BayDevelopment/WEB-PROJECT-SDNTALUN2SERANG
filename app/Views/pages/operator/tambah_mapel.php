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

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
        border-color: #93c5fd
    }

    .form-lock {
        position: relative
    }

    .form-lock::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .35);
        pointer-events: auto
    }
</style>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Tambah MatPel') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Tambah MatPel') ?></li>
            </ol>
        </div>
    </div>

    <?php if (session()->getFlashdata('sweet_error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('sweet_error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('sweet_success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('sweet_success') ?></div>
    <?php endif; ?>

    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-book-open me-2"></i> Form Tambah Mata Pelajaran
            </div>
        </div>

        <div class="card-body">
            <form id="formTambahMatpel"
                action="<?= site_url('operator/matpel/tambah') ?>"
                method="post" autocomplete="off" novalidate>
                <?= csrf_field() ?>
                <?php
                $errors = session('errors') ?? [];
                $hasErr = fn($f) => isset($errors[$f]);
                $getErr = fn($f) => $errors[$f] ?? '';
                ?>

                <div class="row g-3 mb-3">
                    <!-- Kode (info saja, tidak dikirim ke server) -->
                    <div class="col-md-6">
                        <label for="kode_info" class="form-label">Kode MatPel (otomatis)</label>
                        <input
                            type="text"
                            class="form-control"
                            id="kode_info"
                            value="<?= esc($kode_saran ?? '—') ?>"
                            placeholder="Akan dibuat otomatis di server"
                            disabled>
                        <div class="form-text">Kode dibuat otomatis di server saat disimpan.</div>
                    </div>

                    <!-- Nama MatPel -->
                    <div class="col-md-6">
                        <label for="nama_mapel" class="form-label">Nama MatPel</label>
                        <input
                            type="text"
                            class="form-control<?= $hasErr('nama') ? ' is-invalid' : '' ?>"
                            id="nama" name="nama"
                            value="<?= esc(old('nama') ?? '') ?>"
                            placeholder="Masukkan nama mata pelajaran" required
                            aria-describedby="namaFeedback">
                        <?php if ($hasErr('nama')): ?>
                            <div id="namaFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('nama')) ?>
                            </div>
                        <?php endif; ?>
                    </div>


                    <!-- Status -->
                    <div class="col-md-6">
                        <label for="is_active" class="form-label">Status MatPel</label>
                        <select
                            class="form-select<?= $hasErr('is_active') ? ' is-invalid' : '' ?>"
                            name="is_active" id="is_active" required aria-describedby="isActiveFeedback">
                            <option value="" disabled <?= (old('is_active', '') === '') ? 'selected' : '' ?>>— Pilih Status —</option>
                            <option value="1" <?= (old('is_active', '1') === '1') ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (old('is_active', '') === '0') ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                        <?php if ($hasErr('is_active')): ?>
                            <div id="isActiveFeedback" class="invalid-feedback d-block">
                                <?= esc($getErr('is_active')) ?>
                            </div>
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

                    <a href="<?= base_url('operator/matpel') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formTambahMatpel');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');

        form?.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            }
            btnSubmit.disabled = true;
            form.classList.add('form-lock');
            // pastikan token CSRF aktif
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    })();
</script>

<?= $this->endSection() ?>