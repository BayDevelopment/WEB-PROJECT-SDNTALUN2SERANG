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

    .badge-operator {
        background: #e0f2fe;
        color: #0369a1
    }

    .badge-guru {
        background: #ecfccb;
        color: #4d7c0f
    }

    .badge-siswa {
        background: #fae8ff;
        color: #a21caf
    }

    .badge-admin {
        background: #fee2e2;
        color: #991b1b
    }

    .badge-unknown {
        background: #f3f4f6;
        color: #374151
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
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Detail User') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-user') ?>">Data User</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>

        <div class="no-print mt-3 mt-sm-0 d-flex gap-2">
            <a href="<?= base_url('operator/data-user') ?>" class="btn btn-dark rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
            <a href="<?= base_url('operator/edit-user/' . urlencode((string)($user['id_user'] ?? ''))) ?>" class="btn btn-primary rounded-pill">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit
            </a>
        </div>
    </div>

    <!-- Kartu Detail -->
    <div class="card card-elevated">
        <div class="card-header-modern">
            <div class="title-wrap"><i class="fa-regular fa-id-card me-2"></i> Profil User</div>
        </div>

        <div class="card-body">
            <?php
            $username = (string)($user['username'] ?? '—');
            $email    = (string)($user['email'] ?? '—');
            $roleRaw  = strtolower((string)($user['role'] ?? ''));
            $isActive = (string)($user['is_active'] ?? '0');

            // Badge role
            switch ($roleRaw) {
                case 'operator':
                    $roleTag = 'Operator';
                    $roleClass = 'badge-operator';
                    break;
                case 'guru':
                    $roleTag = 'Guru';
                    $roleClass = 'badge-guru';
                    break;
                case 'siswa':
                    $roleTag = 'Siswa';
                    $roleClass = 'badge-siswa';
                    break;
                case 'admin':
                    $roleTag = 'Admin';
                    $roleClass = 'badge-admin';
                    break;
                default:
                    $roleTag = '—';
                    $roleClass = 'badge-unknown';
            }

            // Status
            $statusText  = ($isActive === '1') ? 'Aktif' : 'Nonaktif';
            $statusClass = ($isActive === '1') ? 'bg-success' : 'bg-secondary';
            ?>

            <!-- Hero (tanpa foto) -->
            <div class="profile-hero mb-3">
                <div class="cover"></div>
                <div class="body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="mb-0 fw-bold"><?= esc($username) ?></h3>
                                <span class="badge <?= esc($roleClass) ?> px-2 py-1 rounded-pill"><?= esc($roleTag) ?></span>
                                <span class="badge <?= esc($statusClass) ?> px-2 py-1 rounded-pill"><?= esc($statusText) ?></span>
                            </div>
                            <div class="mt-1 text-muted small">
                                Email: <span class="fw-semibold"><?= esc($email) ?></span>
                                <?php if (!empty($email)): ?>
                                    <a class="btn btn-sm btn-outline-secondary ms-2 no-print" href="mailto:<?= esc($email) ?>">
                                        <i class="fa-regular fa-envelope me-1"></i> Kirim Email
                                    </a>
                                <?php endif; ?>
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
                            <h6 class="fw-bold mb-3"><i class="fa-regular fa-user me-2"></i>Informasi Akun</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-id-badge"></i></span>
                                    <div>
                                        <div class="text-muted small">ID User</div>
                                        <div class="fw-semibold font-monospace"><?= esc($user['id_user'] ?? '—') ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-user"></i></span>
                                    <div>
                                        <div class="text-muted small">Username</div>
                                        <div class="fw-semibold"><?= esc($username) ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-envelope"></i></span>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold"><?= esc($email) ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-solid fa-user-shield"></i></span>
                                    <div>
                                        <div class="text-muted small">Role</div>
                                        <div class="fw-semibold"><?= esc($roleTag) ?></div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-circle-check"></i></span>
                                    <div>
                                        <div class="text-muted small">Status</div>
                                        <div class="fw-semibold"><?= esc($statusText) ?></div>
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
                            <h6 class="fw-bold mb-3"><i class="fa-regular fa-clock me-2"></i>Meta Waktu</h6>
                            <ul class="meta-list">
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-calendar-plus"></i></span>
                                    <div>
                                        <div class="text-muted small">Dibuat</div>
                                        <div class="fw-semibold">
                                            <?= !empty($user['created_at'])
                                                ? \CodeIgniter\I18n\Time::parse($user['created_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                                : '—' ?>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <span class="meta-icon"><i class="fa-regular fa-pen-to-square"></i></span>
                                    <div>
                                        <div class="text-muted small">Diperbarui</div>
                                        <div class="fw-semibold">
                                            <?= !empty($user['updated_at'])
                                                ? \CodeIgniter\I18n\Time::parse($user['updated_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
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

<?= $this->endSection() ?>