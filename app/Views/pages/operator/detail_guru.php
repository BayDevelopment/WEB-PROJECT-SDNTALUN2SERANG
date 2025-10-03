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
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-guru') ?>">Data Guru</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>

        <div class="no-print mt-3 mt-sm-0 d-flex gap-2">
            <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-dark rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
            <a href="<?= base_url('operator/edit-guru/' . urlencode((string)($guru['nip'] ?? ''))) ?>" class="btn btn-primary rounded-pill">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit
            </a>
        </div>
    </div>

    <!-- Kartu Detail -->
    <div class="card card-elevated">
        <div class="card-header-modern">
            <div class="title-wrap"><i class="fa-regular fa-id-card me-2"></i> Profil Guru</div>
        </div>

        <div class="card-body">
            <?php
            // Siapkan foto
            $foto = trim((string)($guru['foto'] ?? ''));
            if ($foto !== '' && preg_match('~^https?://~i', $foto)) {
                $imgSrc = $foto;
            } elseif ($foto !== '') {
                $imgSrc = base_url('assets/img/uploads/' . $foto);
            } else {
                $imgSrc = base_url('assets/img/user.png');
            }

            // Jenis kelamin
            $jk   = strtoupper((string)($guru['jenis_kelamin'] ?? ''));
            $isL  = $jk === 'L';
            $isP = $jk === 'P';

            // Status aktif
            $isActive     = (int)($guru['status_active'] ?? 0) === 1;
            $statusText   = $isActive ? 'Aktif' : 'Nonaktif';
            $statusClass  = $isActive ? 'bg-success' : 'bg-secondary';

            // Username (opsional hasil join)
            $userName = (string)($guru['user_name'] ?? '');
            ?>
            <!-- Hero -->
            <div class="profile-hero mb-3">
                <div class="cover"></div>
                <div class="body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                        <img src="<?= esc($imgSrc) ?>"
                            alt="Foto <?= esc($guru['nama_lengkap'] ?? '—') ?>"
                            class="avatar-120">

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="mb-0 fw-bold"><?= esc($guru['nama_lengkap'] ?? '—') ?></h3>
                                <span class="badge <?= $isL ? 'badge-male' : ($isP ? 'badge-female' : 'badge-unknown') ?> px-2 py-1 rounded-pill">
                                    <?= $isL ? 'Laki-laki' : ($isP ? 'Perempuan' : '—') ?>
                                </span>
                                <span class="badge <?= esc($statusClass) ?> px-2 py-1 rounded-pill"><?= esc($statusText) ?></span>
                            </div>

                            <div class="mt-1 text-muted small">
                                NIP:
                                <span id="nipText" class="font-monospace"><?= esc($guru['nip'] ?? '') ?></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2 no-print" id="btnCopyNip">
                                    <i class="fa-regular fa-copy me-1"></i> Salin
                                </button>
                            </div>

                            <?php if ($userName !== ''): ?>
                                <div class="mt-1 text-muted small">
                                    Username: <span class="fw-semibold"><?= esc($userName) ?></span>
                                </div>
                            <?php endif; ?>
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
                                        <div class="text-muted small">NIP</div>
                                        <div class="fw-semibold font-monospace"><?= esc($guru['nip'] ?? '—') ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-calendar"></i></span>
                                    <div>
                                        <div class="text-muted small">Tanggal Lahir</div>
                                        <div class="fw-semibold">
                                            <?= !empty($guru['tgl_lahir'])
                                                ? \CodeIgniter\I18n\Time::parse($guru['tgl_lahir'], 'Asia/Jakarta')->toLocalizedString('d MMM y')
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
                            <h6 class="fw-bold mb-3"><i class="fa-regular fa-address-card me-2"></i>Kontak & Alamat</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-solid fa-phone"></i></span>
                                    <div>
                                        <div class="text-muted small">No. HP</div>
                                        <?php $tel = (string)($guru['no_telp'] ?? ''); ?>
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
                                    <span class="meta-icon"><i class="fa-regular fa-map"></i></span>
                                    <div>
                                        <div class="text-muted small">Alamat</div>
                                        <div class="fw-semibold"><?= esc($guru['alamat'] ?? '—') ?></div>
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
                                    <?= !empty($guru['created_at'])
                                        ? \CodeIgniter\I18n\Time::parse($guru['created_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                        : '—' ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-muted small"><i class="fa-regular fa-pen-to-square me-1"></i> Diperbarui</div>
                                <div class="fw-semibold">
                                    <?= !empty($guru['updated_at'])
                                        ? \CodeIgniter\I18n\Time::parse($guru['updated_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                        : '—' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // btn submit
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

    // Salin NIP
    document.getElementById('btnCopyNip')?.addEventListener('click', function() {
        const el = document.getElementById('nipText');
        if (!el) return;
        const text = el.textContent.trim();
        navigator.clipboard?.writeText(text).then(() => {
            this.innerHTML = '<i class="fa-solid fa-check me-1"></i> Disalin';
            setTimeout(() => this.innerHTML = '<i class="fa-regular fa-copy me-1"></i> Salin', 1500);
        });
    });
</script>

<?= $this->endSection() ?>