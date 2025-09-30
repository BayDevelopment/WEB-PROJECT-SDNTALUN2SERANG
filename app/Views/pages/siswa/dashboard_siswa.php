<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4 page-title">Dashboard</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <div class="row g-4">

        <!-- Penilaian -->
        <div class="col-xl-4 col-md-6">
            <a href="<?= base_url('siswa/nilai') ?>" class="text-decoration-none">
                <div class="card card-modern kpi-card kpi-primary shadow-sm lift mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-eyebrow">Akses Cepat</div>
                            <div class="kpi-title">Penilaian</div>
                            <div class="kpi-sub text-muted">Lihat progres belajarmu</div>
                        </div>
                        <i class="fa-solid fa-clipboard-check kpi-icon"></i>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="kpi-link">Selengkapnya</span>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Profil Saya -->
        <div class="col-xl-4 col-md-6">
            <a href="<?= base_url('siswa/profile') ?>" class="text-decoration-none">
                <div class="card card-modern kpi-card kpi-primary shadow-sm lift mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-eyebrow">Identitas</div>
                            <div class="kpi-title">Profil Saya</div>
                            <div class="kpi-sub text-muted">Perbarui data personalmu</div>
                        </div>
                        <i class="fa-solid fa-id-badge kpi-icon"></i>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="kpi-link">Kelola Profil</span>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tenaga Pendidik (ada jumlah guru) -->
        <div class="col-xl-4 col-md-6">
            <a href="<?= base_url('siswa/guru') ?>" class="text-decoration-none">
                <div class="card card-modern kpi-card kpi-primary shadow-sm lift mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-eyebrow">Statistik</div>
                            <div class="kpi-title">Tenaga Pendidik</div>
                            <div class="kpi-sub text-muted">
                                <?= number_format((int)($guruCount ?? 0), 0, ',', '.') ?> guru aktif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="kpi-number">
                                <?= number_format((int)($guruCount ?? 0), 0, ',', '.') ?>
                            </div>
                            <i class="fa-solid fa-chalkboard-user kpi-icon-sm"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="kpi-link">Lihat Daftar Guru</span>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </a>
        </div>

    </div>

</div>

<?= $this->endSection() ?>