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

    .hero-mp {
        display: flex;
        gap: 1rem;
        align-items: center
    }

    .hero-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 26px
    }

    .badge-active {
        background: #dcfce7;
        color: #166534
    }

    .badge-inactive {
        background: #f3f4f6;
        color: #374151
    }

    .meta-list {
        margin: 0;
        padding: 0;
        list-style: none
    }

    .meta-list li {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        padding: .4rem 0
    }

    .meta-icon {
        width: 28px;
        height: 28px;
        display: inline-grid;
        place-items: center;
        border-radius: .5rem;
        background: #f3f4f6;
        color: #4b5563
    }
</style>

<?php
// Normalisasi data
$kode      = (string)($mapel['kode'] ?? $mapel['kode_mapel'] ?? '');
$nama      = (string)($mapel['nama'] ?? $mapel['nama_mapel'] ?? '');
$isActive  = (int)($mapel['is_active'] ?? 0) === 1;
$statusTxt = $isActive ? 'Aktif' : 'Tidak Aktif';
$statusCls = $isActive ? 'badge-active' : 'badge-inactive';
$idMapel   = (int)($mapel['id_mapel'] ?? 0);
?>

<div class="container-fluid px-4 page-section mb-3 fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Detail MatPel') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/matpel') ?>">Data MatPel</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>

        <div class="mt-3 mt-sm-0 d-flex gap-2">
            <a href="<?= base_url('operator/matpel') ?>" class="btn btn-dark rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
            <a href="<?= base_url('operator/matpel/edit/' . urlencode((string)$idMapel)) ?>" class="btn btn-primary rounded-pill">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit
            </a>
        </div>
    </div>

    <!-- Kartu Detail -->
    <div class="card card-elevated">
        <div class="card-header-modern">
            <div class="title-wrap"><i class="fa-regular fa-id-card me-2"></i> Detail Mata Pelajaran</div>
        </div>

        <div class="card-body">
            <!-- Hero -->
            <div class="hero-mp mb-3">
                <div class="hero-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h3 class="mb-0 fw-bold"><?= esc($nama !== '' ? $nama : '—') ?></h3>
                        <span class="badge <?= esc($statusCls) ?> px-2 py-1 rounded-pill"><?= esc($statusTxt) ?></span>
                    </div>
                    <div class="text-muted small mt-1">
                        Kode:
                        <span id="kodeText" class="font-monospace"><?= esc($kode !== '' ? $kode : '—') ?></span>
                        <?php if ($kode !== ''): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="btnCopyKode">
                                <i class="fa-regular fa-copy me-1"></i> Salin
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Grid Info -->
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-circle-info me-2"></i>Informasi Utama</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-hashtag"></i></span>
                                    <div>
                                        <div class="text-muted small">Kode</div>
                                        <div class="fw-semibold font-monospace"><?= esc($kode !== '' ? $kode : '—') ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-file-lines"></i></span>
                                    <div>
                                        <div class="text-muted small">Nama</div>
                                        <div class="fw-semibold"><?= esc($nama !== '' ? $nama : '—') ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-solid fa-toggle-on"></i></span>
                                    <div>
                                        <div class="text-muted small">Status</div>
                                        <div class="fw-semibold"><?= esc($statusTxt) ?></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Meta waktu -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="fa-regular fa-clock me-2"></i>Riwayat</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-calendar-plus"></i></span>
                                    <div>
                                        <div class="text-muted small">Dibuat</div>
                                        <div class="fw-semibold">
                                            <?= !empty($mapel['created_at'])
                                                ? \CodeIgniter\I18n\Time::parse($mapel['created_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                                : '—' ?>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-pen-to-square"></i></span>
                                    <div>
                                        <div class="text-muted small">Diperbarui</div>
                                        <div class="fw-semibold">
                                            <?= !empty($mapel['updated_at'])
                                                ? \CodeIgniter\I18n\Time::parse($mapel['updated_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                                : '—' ?>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div><!-- /row -->
        </div>
    </div>
</div>

<script>
    (() => {
        const btnCopy = document.getElementById('btnCopyKode');
        const kodeEl = document.getElementById('kodeText');
        const btnPrint = document.getElementById('btnPrint');

        btnCopy?.addEventListener('click', async () => {
            try {
                const text = (kodeEl?.textContent || '').trim();
                if (!text) return;
                await navigator.clipboard.writeText(text);
                btnCopy.innerHTML = '<i class="fa-solid fa-check me-1"></i> Disalin';
                setTimeout(() => btnCopy.innerHTML = '<i class="fa-regular fa-copy me-1"></i> Salin', 1500);
            } catch (e) {
                alert('Gagal menyalin kode.');
            }
        });

        btnPrint?.addEventListener('click', () => window.print());
    })();
</script>

<?= $this->endSection() ?>