<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    /* ===== Base ===== */
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

    /* ===== Hero ===== */
    .profile-hero {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        background: linear-gradient(135deg, #dbeafe, #ede9fe)
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

    /* ===== Meta list ===== */
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

    /* ===== Responsiveness ===== */
    .min-w-0 {
        min-width: 0
    }

    .header-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap
    }

    .kelas-row {
        align-items: stretch
    }

    .kelas-row>.form-select {
        flex: 1 1 auto;
        min-width: 0
    }

    .kelas-row>.btn {
        flex: 0 0 auto
    }

    .form-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        justify-content: flex-end
    }

    .empty-illustration {
        width: 220px;
        max-width: 75%;
        height: auto;
        opacity: .9
    }

    @media (max-width:576px) {
        .avatar-120 {
            width: 84px;
            height: 84px
        }

        .profile-hero .body {
            padding: .75rem 1rem 1rem
        }

        .card-body {
            padding: 1rem
        }

        .card-header-modern {
            padding: .75rem 1rem
        }

        .header-actions .btn {
            width: 100%
        }

        .empty-illustration {
            width: 160px;
            max-width: 85%
        }
    }

    /* ===== Overlay submit ===== */
    .form-blocker {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .5);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
        z-index: 3
    }

    .form-wrap-relative {
        position: relative
    }

    .d-none {
        display: none !important
    }

    /* ===== Print ===== */
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

<div class="container-fluid px-4 page-section mb-3 fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Detail Guru') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-guru') ?>">Data Guru</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>

        <div class="no-print mt-3 mt-sm-0 header-actions">
            <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-dark rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
            <a href="<?= base_url('operator/edit-guru/' . urlencode((string)($guru['nip'] ?? ''))) ?>" class="btn btn-gradient rounded-pill">
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
            // Foto
            $foto = trim((string)($guru['foto'] ?? ''));
            if ($foto !== '' && preg_match('~^https?://~i', $foto)) {
                $imgSrc = $foto;
            } elseif ($foto !== '') {
                $imgSrc = base_url('assets/img/uploads/' . $foto);
            } else {
                $imgSrc = base_url('assets/img/user.png');
            }

            // JK & Status
            $jk         = strtoupper((string)($guru['jenis_kelamin'] ?? ''));
            $isL        = $jk === 'L';
            $isP        = $jk === 'P';
            $isActive   = (int)($guru['status_active'] ?? 0) === 1;
            $statusText = $isActive ? 'Aktif' : 'Nonaktif';
            $statusClass = $isActive ? 'bg-success' : 'bg-secondary';
            $userName   = (string)($guru['user_name'] ?? '');

            // Error helper
            $errors = session('errors') ?? [];
            $hasErr = fn($f) => isset($errors[$f]);
            $getErr = fn($f) => $errors[$f] ?? '';
            ?>

            <!-- Hero -->
            <div class="profile-hero mb-3">
                <div class="cover"></div>
                <div class="body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                        <img src="<?= esc($imgSrc) ?>" alt="Foto <?= esc($guru['nama_lengkap'] ?? '—') ?>" class="avatar-120">

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="mb-0 fw-bold text-truncate"><?= esc($guru['nama_lengkap'] ?? '—') ?></h3>

                                <span class="badge <?= $isL ? 'badge-male' : ($isP ? 'badge-female' : 'badge-unknown') ?> px-2 py-1 rounded-pill">
                                    <?= $isL ? 'Laki-laki' : ($isP ? 'Perempuan' : '—') ?>
                                </span>

                                <span class="badge <?= esc($statusClass) ?> px-2 py-1 rounded-pill">
                                    <?= esc($statusText) ?>
                                </span>

                                <?php $jabatan = trim((string)($guru['jabatan'] ?? '')); ?>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill">
                                    Jabatan: <?= $jabatan !== '' ? esc($jabatan) : '—' ?>
                                </span>
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
                <!-- Informasi Utama -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="fa-regular fa-user me-2"></i>Informasi Utama
                            </h6>
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

                <!-- Kontak & Alamat -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="fa-regular fa-address-card me-2"></i>Kontak & Alamat
                            </h6>
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

                <!-- Meta Waktu -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex flex-wrap gap-4">
                            <div>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-clock me-1"></i> Dibuat
                                </div>
                                <div class="fw-semibold">
                                    <?= !empty($guru['created_at'])
                                        ? \CodeIgniter\I18n\Time::parse($guru['created_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                        : '—' ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-pen-to-square me-1"></i> Diperbarui
                                </div>
                                <div class="fw-semibold">
                                    <?= !empty($guru['updated_at'])
                                        ? \CodeIgniter\I18n\Time::parse($guru['updated_at'], 'Asia/Jakarta')->toLocalizedString('d MMM y, HH:mm')
                                        : '—' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form: Tambah Penugasan Mapel -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header-modern">
                            <div class="title-wrap">
                                <i class="fa-solid fa-circle-info"></i> Detail Penugasan Mapel
                            </div>
                        </div>

                        <?php
                        $hasGuruMapel =
                            (!empty($gmEdit) && (!empty($gmEdit['id_mapel']) || !empty($gmEdit['id_kelas']) || !empty($gmEdit['id_kelas_list'])))
                            || (!empty($penugasanRows) && is_array($penugasanRows) && count($penugasanRows) > 0);
                        ?>

                        <?php if ($hasGuruMapel): ?>
                            <div class="card-body">
                                <!-- Update form -->
                                <form id="formEditGuruMapel"
                                    action="<?= base_url('operator/detail-guru/' . rawurlencode($guru['nip']) . '/update') ?>"
                                    method="post" class="row g-3 form-wrap-relative" novalidate>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="PUT">

                                    <!-- KONTEKS LAMA (tetap, agar controller tahu mapel/TA sebelum diedit) -->
                                    <input type="hidden" name="old_id_mapel" value="<?= esc((int)($gmEdit['id_mapel'] ?? 0), 'attr') ?>">
                                    <input type="hidden" name="old_id_tahun_ajaran" value="<?= esc((int)($gmEdit['id_tahun_ajaran'] ?? 0), 'attr') ?>">


                                    <!-- MAPEL (MULTI) -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Mata Pelajaran</span>
                                            <button type="button" id="btnAddMapel" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-plus"></i> Tambah Mapel
                                            </button>
                                        </label>

                                        <?php
                                        // Ambil nilai lama (old) => array; kalau tidak ada, pakai dari data edit (satu/lebih)
                                        $preMapelIds = old('id_mapel'); // bisa null/string/array
                                        if (!is_array($preMapelIds)) {
                                            $preMapelIds = $preMapelIds !== null && $preMapelIds !== '' ? [$preMapelIds] : [];
                                        }
                                        // fallback dari $gmEdit (jika ada struktur list mapel)
                                        if (empty($preMapelIds)) {
                                            // jika view kamu hanya punya satu id sebelumnya:
                                            if (!empty($gmEdit['id_mapel'])) {
                                                $preMapelIds = [(string)$gmEdit['id_mapel']];
                                            }
                                            // kalau kamu punya array mapel lama ($gmEdit['mapel_ids']):
                                            if (!empty($gmEdit['mapel_ids']) && is_array($gmEdit['mapel_ids'])) {
                                                $preMapelIds = array_map('strval', $gmEdit['mapel_ids']);
                                            }
                                        }
                                        if (empty($preMapelIds)) {
                                            // wajib ada satu row kosong minimal
                                            $preMapelIds = [''];
                                        }

                                        // helper render option
                                        $renderMapelOption = function (array $optMapel, $selected = '') {
                                            foreach ($optMapel as $m) {
                                                $mid = (string)($m['id_mapel'] ?? '');
                                                $nama = (string)($m['nama_mapel'] ?? $m['nama'] ?? 'Mapel');
                                                $kode = (string)($m['kode'] ?? $m['kode_mapel'] ?? '');
                                                $label = ($kode !== '' ? "[$kode] " : '') . $nama;
                                                $sel = ($mid !== '' && $mid === (string)$selected) ? 'selected' : '';
                                                echo '<option value="' . esc($mid, 'attr') . '" ' . $sel . '>' . esc($label) . '</option>';
                                            }
                                        };
                                        ?>

                                        <div id="mapelContainer" class="d-flex flex-column gap-2">
                                            <?php foreach ($preMapelIds as $i => $preId): ?>
                                                <div class="mapel-row d-flex gap-2">
                                                    <select class="form-select<?= $hasErr('id_mapel') ? ' is-invalid' : '' ?>"
                                                        name="id_mapel[]" required aria-label="Pilih Mapel">
                                                        <option value="" disabled <?= $preId === '' ? 'selected' : '' ?>>— Pilih Mapel —</option>
                                                        <?php if (!empty($optMapel)) $renderMapelOption($optMapel, $preId); ?>
                                                    </select>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btnRemoveMapel <?= $i === 0 ? 'd-none' : '' ?>"
                                                        aria-label="Hapus baris mapel">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php if ($hasErr('id_mapel')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('id_mapel')) ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Anda dapat menambahkan lebih dari satu mata pelajaran.</div>
                                    </div>

                                    <!-- TEMPLATE (disembunyikan) -->
                                    <template id="mapelRowTpl">
                                        <div class="mapel-row d-flex gap-2">
                                            <select class="form-select" name="id_mapel[]" required aria-label="Pilih Mapel">
                                                <option value="" disabled selected>— Pilih Mapel —</option>
                                                <?php if (!empty($optMapel)) $renderMapelOption($optMapel, ''); ?>
                                            </select>
                                            <button type="button" class="btn btn-outline-danger btnRemoveMapel" aria-label="Hapus baris mapel">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </template>


                                    <!-- Kelas (dinamis) -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label d-flex align-items-center justify-content-between">
                                            <span>Kelas</span>
                                            <button type="button" id="btnAddKelas" class="btn btn-sm btn-outline-primary">+ Tambah Kelas</button>
                                        </label>

                                        <div id="kelasContainer" class="d-grid gap-2">
                                            <?php
                                            $selectedKelas = (array) (old('id_kelas', isset($gmEdit['id_kelas_list']) ? $gmEdit['id_kelas_list'] : ($gmEdit['id_kelas'] ?? [])));
                                            if (empty($selectedKelas)) $selectedKelas = [''];
                                            ?>
                                            <?php foreach ($selectedKelas as $selVal): ?>
                                                <div class="d-flex gap-2 kelas-row">
                                                    <select name="id_kelas[]" class="form-select<?= $hasErr('id_kelas') || $hasErr('id_kelas.*') ? ' is-invalid' : '' ?>" required>
                                                        <option value="" hidden>— Pilih Kelas —</option>
                                                        <?php if (!empty($optKelas)): foreach ($optKelas as $k):
                                                                $val = (int)($k['id_kelas'] ?? 0);
                                                                $sel = (string)$selVal === (string)$val ? 'selected' : ''; ?>
                                                                <option value="<?= esc($val, 'attr') ?>" <?= $sel ?>><?= esc($k['nama_kelas'] ?? $k['nama'] ?? 'Kelas') ?></option>
                                                        <?php endforeach;
                                                        endif; ?>
                                                    </select>
                                                    <button type="button" class="btn btn-outline-danger btnRemoveKelas" title="Hapus baris">&times;</button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php if ($hasErr('id_kelas')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('id_kelas')) ?></div>
                                        <?php endif; ?>
                                        <?php if ($hasErr('id_kelas.*')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('id_kelas.*')) ?></div>
                                        <?php endif; ?>

                                        <template id="kelasRowTpl">
                                            <div class="d-flex gap-2 kelas-row">
                                                <select name="id_kelas[]" class="form-select" required>
                                                    <option value="" hidden>— Pilih Kelas —</option>
                                                    <?php if (!empty($optKelas)): foreach ($optKelas as $k): ?>
                                                            <option value="<?= esc((int)($k['id_kelas'] ?? 0), 'attr') ?>"><?= esc($k['nama_kelas'] ?? $k['nama'] ?? 'Kelas') ?></option>
                                                    <?php endforeach;
                                                    endif; ?>
                                                </select>
                                                <button type="button" class="btn btn-outline-danger btnRemoveKelas" title="Hapus baris">&times;</button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Tahun Ajaran -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Tahun Ajaran</label>
                                        <select name="id_tahun_ajaran" class="form-select<?= $hasErr('id_tahun_ajaran') ? ' is-invalid' : '' ?> text-capitalize" required>
                                            <?php if (!empty($optTahunAjaran)): foreach ($optTahunAjaran as $ta):
                                                    $val = (int)($ta['id_tahun_ajaran'] ?? 0);
                                                    $label = $ta['nama'] ?? (($ta['semester'] ?? ''));
                                                    $sel = (string)old('id_tahun_ajaran', (string)($gmEdit['id_tahun_ajaran'] ?? '')) === (string)$val ? 'selected' : ''; ?>
                                                    <option value="<?= esc($val, 'attr') ?>" <?= $sel ?>><?= esc($label ?: 'Tahun Ajaran') ?></option>
                                            <?php endforeach;
                                            endif; ?>
                                        </select>
                                        <?php if ($hasErr('id_tahun_ajaran')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('id_tahun_ajaran')) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Jam per Minggu -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Jam per Minggu</label>
                                        <input type="number" name="jam_per_minggu" min="1" max="40" step="1"
                                            value="<?= esc(old('jam_per_minggu', (string)($gmEdit['jam_per_minggu'] ?? '')), 'attr') ?>"
                                            class="form-control<?= $hasErr('jam_per_minggu') ? ' is-invalid' : '' ?>" placeholder="cth: 2" required>
                                        <?php if ($hasErr('jam_per_minggu')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('jam_per_minggu')) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="col-12 col-md-8">
                                        <label class="form-label">Keterangan</label>
                                        <textarea name="keterangan" rows="3" maxlength="190"
                                            class="form-control<?= $hasErr('keterangan') ? ' is-invalid' : '' ?>"
                                            placeholder="(opsional)"><?= esc(old('keterangan', (string)($gmEdit['keterangan'] ?? ''))) ?></textarea>
                                        <?php if ($hasErr('keterangan')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('keterangan')) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Aksi (update + delete form terpisah, tidak nested) -->
                                    <div class="col-12">
                                        <div class="form-actions mt-2">
                                            <button type="submit" class="btn btn-gradient rounded-pill d-inline-flex align-items-center">
                                                <i class="fa-solid fa-pen-to-square me-2"></i> Update
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Delete ALL (separate form) -->
                                <div class="form-actions mt-2">
                                    <form id="formHapusSemuaGuruMapel"
                                        action="<?= base_url('operator/detail-guru/' . rawurlencode($guru['nip']) . '/delete-all') ?>"
                                        method="post" class="m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="button" id="btnHapusSemua"
                                            class="btn btn-outline-danger rounded-pill d-inline-flex align-items-center">
                                            <i class="fa-solid fa-trash-can me-2"></i> Hapus Semua
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card-body">
                                <div class="card-body">
                                    <?php
                                    // Ambil jabatan dari DATA GURU (bukan session)
                                    $srcJabatan = $gmEdit['jabatan'] ?? ($guru['jabatan'] ?? '');
                                    // Normalisasi: trim spasi, rapikan spasi ganda, lowercase
                                    $jabatan = strtolower(preg_replace('/\s+/', ' ', trim((string)$srcJabatan)));

                                    // Daftar role yang TIDAK BOLEH melihat tombol "Tambah Guru"
                                    // (Silakan sesuaikan: tambahkan 'guru' jika memang harus disembunyikan juga)
                                    $blokir = [
                                        'kepala sekolah',
                                        'wakil kepala',
                                        'operator',
                                        'staff',
                                        'staf',
                                        'guru',          // ← aktifkan/biarkan jika GURU juga dilarang
                                        'guru mapel',
                                        'guru kelas'
                                    ];

                                    $terblokir = in_array($jabatan, $blokir, true);
                                    ?>

                                    <div class="empty-card text-center p-5">
                                        <img
                                            src="<?= base_url('assets/img/empty-box.png') ?>"
                                            class="empty-illustration mb-3"
                                            alt="Data kosong"
                                            width="160" height="160"
                                            loading="lazy">

                                        <h5 class="mb-1">Belum ada data guru</h5>
                                        <p class="text-muted mb-3">
                                            Tambahkan data guru pertama Anda untuk mulai mengelola informasi.
                                        </p>

                                        <?php if (!$terblokir): ?>
                                            <a href="<?= site_url('operator/guru/tambah') ?>" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Tambah Guru
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <!-- /Form: Tambah Penugasan Mapel -->
        </div><!-- /card-body -->
    </div><!-- /card -->
</div><!-- /container -->

<script>
    // mapel multi
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('mapelContainer');
        const tpl = document.getElementById('mapelRowTpl');
        const btnAdd = document.getElementById('btnAddMapel');

        // tambah baris
        btnAdd?.addEventListener('click', () => {
            const node = tpl.content.firstElementChild.cloneNode(true);
            container.appendChild(node);
            syncRemoveButtons();
        });

        // hapus baris (event delegation)
        container?.addEventListener('click', (e) => {
            const btn = e.target.closest('.btnRemoveMapel');
            if (!btn) return;
            const row = btn.closest('.mapel-row');
            if (!row) return;

            // minimal 1 baris tersisa
            const rows = container.querySelectorAll('.mapel-row');
            if (rows.length <= 1) return;

            row.remove();
            syncRemoveButtons();
        });

        function syncRemoveButtons() {
            const rows = container.querySelectorAll('.mapel-row');
            rows.forEach((r, idx) => {
                const delBtn = r.querySelector('.btnRemoveMapel');
                if (!delBtn) return;
                // baris pertama tidak boleh dihapus supaya selalu ada minimal satu baris
                delBtn.classList.toggle('d-none', idx === 0);
            });
        }
        syncRemoveButtons();
    });

    // Salin NIP
    document.getElementById('btnCopyNip')?.addEventListener('click', () => {
        const el = document.getElementById('nipText');
        if (!el) return;
        const txt = el.textContent.trim();
        if (!txt) return;
        navigator.clipboard?.writeText(txt).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'NIP disalin ke clipboard',
                timer: 1200,
                showConfirmButton: false
            });
        });
    });

    // SweetAlert: Hapus semua
    (function() {
        const btn = document.getElementById('btnHapusSemua');
        const form = document.getElementById('formHapusSemuaGuruMapel');
        if (!btn || !form) return;

        btn.addEventListener('click', async function() {
            const res = await Swal.fire({
                title: 'Hapus semua penugasan?',
                text: 'Tindakan ini akan menghapus SEMUA penugasan mapel guru ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            });
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
                form.submit();
            }
        });
    })();

    // Overlay submit (untuk form edit)
    (function() {
        const form = document.getElementById('formEditGuruMapel');
        if (!form) return;

        // overlay
        const overlay = document.createElement('div');
        overlay.className = 'form-blocker d-none';
        overlay.innerHTML = '<div class="d-inline-flex align-items-center gap-2"><span class="spinner-border" role="status" aria-hidden="true"></span><span>Loading…</span></div>';
        form.classList.add('form-wrap-relative');
        form.appendChild(overlay);

        let submitting = false;
        form.addEventListener('submit', function(e) {
            if (submitting) {
                e.preventDefault();
                return;
            }
            submitting = true;
            overlay.classList.remove('d-none');
            form.setAttribute('aria-busy', 'true');
            form.style.pointerEvents = 'none';
        });
    })();

    // Tambah/Hapus baris kelas (dinamis)
    (function() {
        const wrap = document.getElementById('kelasContainer');
        const tpl = document.getElementById('kelasRowTpl');
        const add = document.getElementById('btnAddKelas');
        if (!wrap || !tpl) return;

        add?.addEventListener('click', () => {
            const node = tpl.content.cloneNode(true);
            wrap.appendChild(node);
        });

        wrap.addEventListener('click', (e) => {
            if (e.target.classList.contains('btnRemoveKelas')) {
                const row = e.target.closest('.kelas-row');
                if (row) {
                    const rows = wrap.querySelectorAll('.kelas-row');
                    if (rows.length > 1) row.remove();
                }
            }
        });
    })();
</script>

<?= $this->endSection() ?>