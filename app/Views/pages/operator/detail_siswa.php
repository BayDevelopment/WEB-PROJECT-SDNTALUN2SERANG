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
        background:
            linear-gradient(135deg, rgba(59, 130, 246, .12), rgba(99, 102, 241, .10));
        border-radius: 1rem 1rem 0 0
    }

    .card-header-modern .title-wrap {
        font-weight: 700
    }

    .profile-hero {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        background:
            linear-gradient(135deg, #dbeafe, #ede9fe)
    }

    .profile-hero .cover {
        height: 110px;
        background:
            radial-gradient(1200px 200px at -10% -50%, rgba(99, 102, 241, .15) 0, transparent 60%),
            radial-gradient(900px 180px at 110% 0, rgba(59, 130, 246, .15) 0, transparent 60%)
    }

    .profile-hero .body {
        padding: 1rem 1.25rem 1.25rem
    }

    .avatar-120 {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 9999px;
        border: 3px solid #fff;
        box-shadow: 0 .3rem .8rem rgba(0, 0, 0, .08);
        background: #fff
    }

    .badge-male {
        background: #dbeafe;
        color: #1d4ed8
    }

    .badge-female {
        background: #fae8ff;
        color: #a21caf
    }

    .badge-unknown {
        background: #f3f4f6;
        color: #374151
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

    @media print {
        .no-print {
            display: none !important
        }

        body {
            padding: 0;
            margin: 0
        }

        .container-fluid,
        .card {
            box-shadow: none !important
        }
    }
</style>

<div class="container-fluid px-4 page-section mb-3">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Detail Siswa') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-siswa') ?>">Data Siswa</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>

        <div class="no-print mt-3 mt-sm-0 d-flex gap-2">
            <a href="<?= base_url('operator/data-siswa') ?>" class="btn btn-dark rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
            <a href="<?= base_url('operator/edit-siswa/' . urlencode((string)($siswa['nisn'] ?? ''))) ?>" class="btn btn-primary rounded-pill">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit
            </a>
        </div>
    </div>

    <!-- Kartu Detail -->
    <div class="card card-elevated">
        <div class="card-header-modern">
            <div class="title-wrap"><i class="fa-regular fa-id-card me-2"></i> Profil Siswa</div>
        </div>

        <div class="card-body">
            <!-- Hero -->
            <div class="profile-hero mb-3">
                <div class="cover"></div>
                <div class="body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                        <img
                            src="<?= !empty($siswa['photo']) ? base_url('assets/img/uploads/' . esc($siswa['photo'])) : base_url('assets/img/user.png') ?>"
                            alt="Foto <?= esc($siswa['full_name'] ?? '—') ?>" class="avatar-120">

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="mb-0 fw-bold"><?= esc($siswa['full_name'] ?? '—') ?></h3>

                                <?php
                                $g = (string)($siswa['gender'] ?? '');
                                $isL = $g === 'L';
                                $isP = $g === 'P';
                                ?>
                                <span class="badge <?= $isL ? 'badge-male' : ($isP ? 'badge-female' : 'badge-unknown') ?> px-2 py-1 rounded-pill">
                                    <?= $isL ? 'Laki-laki' : ($isP ? 'Perempuan' : '—') ?>
                                </span>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill">
                                    Kelas: <?= esc($siswa['nama_kelas'] ?? '—') ?>
                                </span>
                            </div>

                            <div class="mt-1 text-muted small">
                                NISN:
                                <span id="nisnText" class="font-monospace"><?= esc($siswa['nisn'] ?? '') ?></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2 no-print" id="btnCopyNisn">
                                    <i class="fa-regular fa-copy me-1"></i> Salin
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Grid Informasi -->
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="fa-regular fa-user me-2"></i>Informasi Utama</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-id-badge"></i></span>
                                    <div>
                                        <div class="text-muted small">NISN</div>
                                        <div class="fw-semibold font-monospace"><?= esc($siswa['nisn'] ?? '—') ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-calendar"></i></span>
                                    <div>
                                        <div class="text-muted small">Tempat, Tanggal Lahir</div>
                                        <div class="fw-semibold">
                                            <?= esc($siswa['birth_place'] ?? '—') ?>
                                            <?= !empty($siswa['birth_place']) && !empty($siswa['birth_date']) ? ', ' : '' ?>
                                            <?= !empty($siswa['birth_date'])
                                                ? \CodeIgniter\I18n\Time::parse($siswa['birth_date'], 'Asia/Jakarta')->toLocalizedString('d MMM y')
                                                : '—' ?>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-solid fa-venus-mars"></i></span>
                                    <div>
                                        <div class="text-muted small">Jenis Kelamin</div>
                                        <div class="fw-semibold"><?= $isL ? 'Laki-laki' : ($isP ? 'Perempuan' : '—') ?></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="fa-regular fa-address-card me-2"></i>Kontak & Wali</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-solid fa-phone"></i></span>
                                    <div>
                                        <div class="text-muted small">No. HP</div>
                                        <?php $tel = (string)($siswa['phone'] ?? ''); ?>
                                        <?php if ($tel !== ''): ?>
                                            <div class="fw-semibold">
                                                <a class="text-decoration-none" href="<?= 'tel:' . esc(preg_replace('/\s+/', '', $tel)) ?>">
                                                    <?= esc($tel) ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="fw-semibold">—</div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-user"></i></span>
                                    <div>
                                        <div class="text-muted small">Orang Tua/Wali</div>
                                        <div class="fw-semibold"><?= esc($siswa['parent_name'] ?? '—') ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-map"></i></span>
                                    <div>
                                        <div class="text-muted small">Alamat</div>
                                        <div class="fw-semibold"><?= esc($siswa['address'] ?? '—') ?></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Meta waktu -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex flex-wrap gap-4">
                            <div>
                                <div class="text-muted small"><i class="fa-regular fa-clock me-1"></i> Dibuat</div>
                                <div class="fw-semibold">
                                    <?= !empty($siswa['created_at'])
                                        ? \CodeIgniter\I18n\Time::parse($siswa['created_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                        : '—' ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-muted small"><i class="fa-regular fa-pen-to-square me-1"></i> Diperbarui</div>
                                <div class="fw-semibold">
                                    <?= !empty($siswa['updated_at'])
                                        ? \CodeIgniter\I18n\Time::parse($siswa['updated_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                        : '—' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /row -->
        </div>
    </div>
</div>

<script>
    (function() {
        const btnCopy = document.getElementById('btnCopyNisn');
        const nisnEl = document.getElementById('nisnText');
        const btnPrint = document.getElementById('btnPrint');

        btnCopy?.addEventListener('click', async () => {
            try {
                const text = (nisnEl?.textContent || '').trim();
                await navigator.clipboard.writeText(text);
                btnCopy.innerHTML = '<i class="fa-solid fa-check me-1"></i> Disalin';
                setTimeout(() => btnCopy.innerHTML = '<i class="fa-regular fa-copy me-1"></i> Salin', 1500);
            } catch (e) {
                alert('Gagal menyalin NISN.');
            }
        });

        btnPrint?.addEventListener('click', () => window.print());
    })();
</script>

<?= $this->endSection() ?>