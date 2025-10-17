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

    /* Freeze interaksi tanpa menghapus nilai/submit payload */
    #formEditSiswaTahun.is-submitting .freeze-area {
        pointer-events: none;
        /* blok klik */
        user-select: none;
        /* blok seleksi */
        opacity: .75;
        /* efek beku */
        filter: grayscale(.1);
    }

    /* Tampilkan overlay ketika submit */
    #formEditSiswaTahun.is-submitting #formBlocker {
        display: block !important;
    }

    /* Spinner on (span.spinner-border di tombol) */
    #formEditSiswaTahun.is-submitting #btnSubmit .spinner-border {
        display: inline-block !important;
    }

    /* Sembunyikan teks tombol saat loading (opsional) */
    #formEditSiswaTahun.is-submitting #btnSubmit .btn-text {
        opacity: .6;
    }

    /* Gaya overlay (kalau belum ada) */
    .form-blocker {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .6);
        display: none;
        z-index: 10;
    }

    .form-blocker .form-blocker-inner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        align-items: center;
    }

    #formEditSiswaTahun.is-submitting .freeze-area {
        pointer-events: none;
        user-select: none;
        opacity: .75;
        filter: grayscale(.1);
    }

    #formEditSiswaTahun.is-submitting #formBlocker {
        display: block !important;
    }

    #formEditSiswaTahun.is-submitting #btnSubmit .spinner-border {
        display: inline-block !important;
    }

    #formEditSiswaTahun.is-submitting #btnSubmit .btn-text {
        opacity: .6;
    }

    .form-blocker {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .6);
        display: none;
        z-index: 10;
    }

    .form-blocker .form-blocker-inner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        align-items: center;
    }
</style>

<div class="container-fluid px-4 page-section mb-3 fade-in-up delay-300">
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
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex flex-wrap gap-4">
                            <?php $v = $validation ?? \Config\Services::validation(); ?>

                            <!-- ...potongan view kamu di atas... -->

                            <form id="formEditSiswaTahun"
                                action="<?= site_url('operator/detail-siswa') ?>"
                                method="post"
                                enctype="multipart/form-data"
                                autocomplete="off"
                                novalidate
                                class="position-relative">

                                <?= csrf_field() ?>

                                <?php
                                $errors = session('errors') ?? [];
                                $hasErr = fn(string $f) => isset($errors[$f]);
                                $getErr = fn(string $f) => $errors[$f] ?? '';

                                $siswa           = $siswa    ?? [];
                                $d_siswa         = $d_siswa  ?? [];
                                $d_tahun         = $d_tahun  ?? [];
                                $siswaTahunEdit  = $siswaTahunEdit ?? null;

                                $idStEdit  = (int)($siswaTahunEdit['id_siswa_tahun'] ?? 0);
                                $selSiswaId = (int)(old('siswa_id') ?: ($siswa['id_siswa'] ?? 0));
                                $selTAId   = (int)(old('tahun_ajaran_id') ?: ($siswaTahunEdit['tahun_ajaran_id'] ?? 0));
                                $selStatus = (string)(old('status') ?: ($siswaTahunEdit['status'] ?? ''));
                                $tglMasuk  = old('tanggal_masuk')  ?? ($siswaTahunEdit['tanggal_masuk']  ?? '');
                                $tglKeluar = old('tanggal_keluar') ?? ($siswaTahunEdit['tanggal_keluar'] ?? '');

                                $nisn = $siswa['nisn'] ?? '';
                                ?>

                                <?php if ($idStEdit > 0): ?>
                                    <input type="hidden" name="id_siswa_tahun" value="<?= esc($idStEdit, 'attr') ?>">
                                <?php endif; ?>
                                <input type="hidden" name="nisn" value="<?= esc($nisn, 'attr') ?>">

                                <div class="row g-3 mb-3 freeze-area">
                                    <!-- Siswa (tampil saja; tidak dikirim) -->
                                    <div class="col-md-6">
                                        <label class="form-label">Siswa</label>
                                        <input type="text" class="form-control"
                                            value="<?= esc(($siswa['full_name'] ?? '—') . ' — ' . ($siswa['nisn'] ?? '')) ?>"
                                            readonly>
                                    </div>

                                    <!-- Tahun Ajaran -->
                                    <div class="col-md-6">
                                        <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                        <?php if (!empty($d_tahun) && is_array($d_tahun)): ?>
                                            <select name="tahun_ajaran_id" id="tahun_ajaran_id"
                                                class="form-select<?= $hasErr('tahun_ajaran_id') ? ' is-invalid' : '' ?>">
                                                <option value="" disabled <?= $selTAId ? '' : 'selected' ?>>— Pilih Tahun Ajaran —</option>
                                                <?php foreach ($d_tahun as $t): ?>
                                                    <?php
                                                    $tid   = (int)($t['id_tahun_ajaran'] ?? 0);
                                                    $nama  = (string)($t['tahun'] ?? '');
                                                    $sem   = (string)($t['semester'] ?? '');
                                                    $aktif = (int)($t['is_active'] ?? 0);
                                                    $label = trim($nama . ' - ' . ucfirst($sem) . ($aktif ? ' (Aktif)' : ''));
                                                    ?>
                                                    <option value="<?= esc($tid, 'attr') ?>" <?= $selTAId === $tid ? 'selected' : '' ?>>
                                                        <?= esc($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if ($hasErr('tahun_ajaran_id')): ?>
                                                <div class="invalid-feedback d-block"><?= esc($getErr('tahun_ajaran_id')) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-2">Tahun ajaran tidak ditemukan. Tambahkan tahun ajaran terlebih dahulu.</div>
                                            <select class="form-select" id="tahun_ajaran_id" disabled>
                                                <option>— Tidak ada data tahun ajaran —</option>
                                            </select>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select<?= $hasErr('status') ? ' is-invalid' : '' ?>"
                                            name="status" id="status" aria-describedby="statusFeedback">
                                            <option value="" disabled <?= $selStatus ? '' : 'selected' ?>>— Pilih —</option>
                                            <option value="aktif" <?= $selStatus === 'aktif'  ? 'selected' : '' ?>>Aktif</option>
                                            <option value="keluar" <?= $selStatus === 'keluar' ? 'selected' : '' ?>>Keluar</option>
                                            <option value="lulus" <?= $selStatus === 'lulus'  ? 'selected' : '' ?>>Lulus</option>
                                        </select>
                                        <?php if ($hasErr('status')): ?>
                                            <div id="statusFeedback" class="invalid-feedback d-block"><?= esc($getErr('status')) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tanggal Masuk -->
                                    <div class="col-md-6">
                                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                                        <input type="date"
                                            class="form-control<?= $hasErr('tanggal_masuk') ? ' is-invalid' : '' ?>"
                                            id="tanggal_masuk" name="tanggal_masuk"
                                            value="<?= esc($tglMasuk) ?>">
                                        <?php if ($hasErr('tanggal_masuk')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('tanggal_masuk')) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tanggal Keluar -->
                                    <div class="col-md-6">
                                        <label for="tanggal_keluar" class="form-label">Tanggal Keluar</label>
                                        <input type="date"
                                            class="form-control<?= $hasErr('tanggal_keluar') ? ' is-invalid' : '' ?>"
                                            id="tanggal_keluar" name="tanggal_keluar"
                                            value="<?= esc($tglKeluar) ?>">
                                        <?php if ($hasErr('tanggal_keluar')): ?>
                                            <div class="invalid-feedback d-block"><?= esc($getErr('tanggal_keluar')) ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Isi jika status Keluar/Lulus. Untuk Aktif, biarkan kosong.</div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill d-inline-flex align-items-center">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan</span>
                                    </button>

                                    <button type="reset" id="btnReset" class="btn btn-outline-secondary rounded-pill">
                                        <i class="fa-solid fa-rotate-left me-2"></i> Reset
                                    </button>

                                    <a href="<?= base_url('operator/data-siswa') ?>" class="btn btn-dark rounded-pill">
                                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                                    </a>
                                </div>

                                <!-- Overlay blocker -->
                                <div id="formBlocker" class="form-blocker d-none" aria-hidden="true">
                                    <div class="form-blocker-inner">
                                        <div class="spinner-border" role="status" aria-hidden="true"></div>
                                        <div class="ms-2">Loading…</div>
                                    </div>
                                </div>
                            </form>
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
    (function() {
        const form = document.getElementById('formEditSiswaTahun');
        if (!form) return;

        const btnSubmit = document.getElementById('btnSubmit');
        const btnReset = document.getElementById('btnReset');
        const statusEl = document.getElementById('status');
        const tMasuk = document.getElementById('tanggal_masuk');
        const tKeluar = document.getElementById('tanggal_keluar');

        let submitting = false;

        function applyStatusRules() {
            const val = (statusEl?.value || '').toLowerCase();
            if (!tKeluar) return;

            if (val === 'aktif') {
                tKeluar.value = '';
                tKeluar.setAttribute('disabled', 'disabled'); // tidak terkirim saat aktif
                tKeluar.removeAttribute('required');
            } else if (val === 'keluar' || val === 'lulus') {
                tKeluar.removeAttribute('disabled');
                tKeluar.setAttribute('required', 'required');
            }
        }

        function syncMinKeluar() {
            if (tMasuk && tKeluar && tMasuk.value) {
                tKeluar.min = tMasuk.value;
            } else if (tKeluar) {
                tKeluar.removeAttribute('min');
            }
        }

        statusEl?.addEventListener('change', applyStatusRules);
        tMasuk?.addEventListener('change', syncMinKeluar);

        // init
        applyStatusRules();
        syncMinKeluar();

        form.addEventListener('submit', function(e) {
            if (submitting) {
                e.preventDefault();
                return false;
            }
            submitting = true;

            // sinkron lagi sebelum submit
            applyStatusRules();
            syncMinKeluar();

            form.classList.add('is-submitting');

            // Disable tombol (payload aman)
            btnSubmit?.setAttribute('disabled', 'disabled');
            btnReset?.setAttribute('disabled', 'disabled');

            // Bekukan input tanpa men-disable (nilai tetap terkirim)
            form.querySelectorAll('input, textarea').forEach(el => el.setAttribute('readonly', 'readonly'));
            form.querySelectorAll('select').forEach(el => {
                el.setAttribute('aria-disabled', 'true');
                el.setAttribute('tabindex', '-1');
            });
        });

        btnReset?.addEventListener('click', function() {
            form.classList.remove('is-submitting');
            submitting = false;
            btnSubmit?.removeAttribute('disabled');
            btnReset?.removeAttribute('disabled');

            form.querySelectorAll('input[readonly], textarea[readonly]').forEach(el => el.removeAttribute('readonly'));
            form.querySelectorAll('select[aria-disabled="true"]').forEach(el => {
                el.removeAttribute('aria-disabled');
                el.removeAttribute('tabindex');
            });

            applyStatusRules();
            syncMinKeluar();
        });
    })();
</script>

<?= $this->endSection() ?>