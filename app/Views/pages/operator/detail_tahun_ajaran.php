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

    .badge-soft {
        padding: .5rem .75rem;
        border-radius: 9999px
    }
</style>

<?php
$ta = $d_TahunAjaran ?? [];
$id = (int)($ta['id_tahun_ajaran'] ?? 0);

$tahun     = (string)($ta['tahun'] ?? '-');
$semester  = (string)($ta['semester'] ?? '-');
$startDate = (string)($ta['start_date'] ?? '');
$endDate   = (string)($ta['end_date'] ?? '');
$isActive  = (int)($ta['is_active'] ?? 0);

$semText = $semester ? ucfirst($semester) : '-';
$statText = $isActive ? 'Aktif' : 'Nonaktif';
$statCls  = $isActive ? 'bg-success' : 'bg-secondary';

// util format tanggal sederhana -> 07 Okt 2025
$fmtDate = function ($d) {
    if (!$d) return '—';
    $ts = strtotime($d);
    if (! $ts) return '—';
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
};
?>

<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Detail Tahun Ajaran') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/tahun-ajaran') ?>">Tahun Ajaran</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Detail Tahun Ajaran') ?></li>
            </ol>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('operator/tahun-ajaran') ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
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
                <i class="fa-solid fa-calendar-days me-2"></i> Informasi Tahun Ajaran
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-8">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted">Tahun</th>
                                <td class="fw-semibold"><?= esc($tahun) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Semester</th>
                                <td><?= esc($semText) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Mulai</th>
                                <td><?= esc($fmtDate($startDate)) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Selesai</th>
                                <td><?= esc($fmtDate($endDate)) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge <?= esc($statCls, 'attr') ?> badge-soft"><?= esc($statText) ?></span>
                                </td>
                            </tr>
                            <?php if (!empty($ta['created_at']) || !empty($ta['updated_at'])): ?>
                                <tr>
                                    <th class="text-muted">Dibuat</th>
                                    <td><?= esc($fmtDate($ta['created_at'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Diperbarui</th>
                                    <td><?= esc($fmtDate($ta['updated_at'] ?? '')) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-semibold">Ringkas</div>
                            <span class="badge <?= esc($statCls, 'attr') ?>"><?= esc($statText) ?></span>
                        </div>
                        <div class="small text-muted">Periode</div>
                        <div class="fw-semibold mb-2">
                            <?= esc($fmtDate($startDate)) ?> &ndash; <?= esc($fmtDate($endDate)) ?>
                        </div>
                        <div class="small text-muted">Semester</div>
                        <div class="fw-semibold"><?= esc($semText) ?> (<?= esc($tahun) ?>)</div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="<?= base_url('operator/edit/tahun-ajaran/' . rawurlencode((string)$id)) ?>" class="btn btn-gradient rounded-pill">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit Data
                </a>
                <a href="<?= base_url('operator/tahun-ajaran') ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>