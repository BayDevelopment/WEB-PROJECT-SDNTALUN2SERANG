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

    .badge-soft {
        background: rgba(37, 99, 235, .12);
        color: #1e3a8a;
        border: 1px solid rgba(37, 99, 235, .25)
    }

    .form-control[disabled],
    .form-select[disabled] {
        background: #f8fafc;
        color: #111827;
        opacity: 1
    }
</style>

<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Detail Kelas') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/kelas') ?>">Data Kelas</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Detail Kelas') ?></li>
            </ol>
        </div>
    </div>

    <?php if (session()->getFlashdata('sweet_error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('sweet_error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('sweet_success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('sweet_success') ?></div>
    <?php endif; ?>

    <?php
    $row        = $data_kelas ?? [];
    $idKelas    = $row['id_kelas']   ?? '';
    $nama       = $row['nama_kelas'] ?? '';
    $tingkat    = $row['tingkat']    ?? '';
    $jurusan    = $row['jurusan']    ?? '';
    $isActive   = $row['is_active']  ?? null;
    $createdAt  = $row['created_at'] ?? null;
    $updatedAt  = $row['updated_at'] ?? null;

    $fmt = static function ($v) {
        return ($v === null || $v === '') ? '—' : esc($v);
    };
    ?>

    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-school me-2"></i> Detail Kelas
                <?php if ($isActive !== null): ?>
                    <span class="ms-2 badge <?= (string)$isActive === '1' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= (string)$isActive === '1' ? 'Aktif' : 'Tidak Aktif' ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Nama Kelas</label>
                    <input type="text" class="form-control" value="<?= $fmt($nama) ?>" disabled>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tingkat</label>
                    <input type="text" class="form-control" value="<?= $fmt($tingkat) ?>" disabled>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Jurusan</label>
                    <input type="text" class="form-control" value="<?= $fmt($jurusan) ?>" disabled>
                </div>

                <?php if ($createdAt || $updatedAt): ?>
                    <div class="col-12">
                        <hr class="my-2">
                    </div>
                <?php endif; ?>

                <?php if ($createdAt): ?>
                    <div class="col-md-3">
                        <label class="form-label">Dibuat</label>
                        <input type="text" class="form-control" value="<?= $fmt(dt_indo($createdAt)) ?>" disabled>
                    </div>
                <?php endif; ?>

                <?php if ($updatedAt): ?>
                    <div class="col-md-3">
                        <label class="form-label">Diperbarui</label>
                        <input type="text" class="form-control" value="<?= $fmt(dt_indo($updatedAt)) ?>" disabled>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <?php if ($idKelas !== ''): ?>
                    <a href="<?= site_url('operator/kelas/edit/' . urlencode($idKelas)) ?>"
                        class="btn btn-gradient rounded-pill">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Edit
                    </a>
                <?php endif; ?>

                <a href="<?= base_url('operator/kelas') ?>" class="btn btn-dark rounded-pill">
                    <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>