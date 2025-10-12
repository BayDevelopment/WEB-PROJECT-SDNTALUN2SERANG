<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- CSS kecil -->
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

    .btn[disabled],
    .form-control[disabled],
    .form-select[disabled] {
        cursor: not-allowed;
        opacity: .75
    }

    .form-lock {
        position: relative;
    }

    .form-lock::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .4);
        pointer-events: auto;
    }
</style>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Laporan Data Guru') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Laporan Data Guru') ?></li>
            </ol>
        </div>
    </div>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-tie me-2"></i> Form Data Laporan Guru
            </div>
        </div>

        <div class="card-body">
            <?php $v = $validation ?? \Config\Services::validation(); ?>

            <form id="formTambahGuru"
                action="<?= site_url('operator/tambah-laporan/guru') ?>"
                method="post"
                autocomplete="off"
                novalidate>
                <?= csrf_field() ?>

                <?php
                // Ambil error dari flash session (di-set di controller simpan: ->with('errors', [...]))
                $errors   = session('errors') ?? [];
                $hasErr   = fn(string $f) => isset($errors[$f]);
                $getErr   = fn(string $f) => $errors[$f] ?? '';

                // Preselect guru: prioritas old('guru_id') → $preselectId → $guruTerpilih['id_guru']
                $selGuruId  = (int)(
                    old('guru_id')
                    ?: ($preselectId ?? 0)
                    ?: ($guruTerpilih['id_guru'] ?? 0)
                );
                $selTAId    = (int) old('tahun_ajaran_id');
                $selStatus  = (string) old('status');

                // Optional: lock berdasarkan NIP jika halaman dibuka via NIP (kirim dari controller sebagai $nip_lock)
                $nipLock = $nip_lock ?? null;
                ?>

                <div class="row g-3 mb-3">
                    <!-- Guru -->
                    <div class="col-md-6">
                        <label for="guru_id" class="form-label">Guru</label>
                        <?php if (!empty($d_guru) && is_array($d_guru)): ?>
                            <select name="guru_id" id="guru_id"
                                class="form-select<?= $hasErr('guru_id') ? ' is-invalid' : '' ?>"
                                <?= $nipLock ? 'disabled' : '' ?>>
                                <option value="" disabled <?= $selGuruId ? '' : 'selected' ?>>— Pilih Guru —</option>
                                <?php foreach ($d_guru as $g): ?>
                                    <?php
                                    $gid  = (int)($g['id_guru'] ?? 0);
                                    $nama = trim((string)($g['nama_lengkap'] ?? '—'));
                                    $nip  = (string)($g['nip'] ?? '');
                                    ?>
                                    <option value="<?= esc($gid, 'attr') ?>" <?= $selGuruId === $gid ? 'selected' : '' ?>>
                                        <?= esc($nama . ' — ' . $nip) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($nipLock): ?>
                                <!-- mirror hidden supaya nilai tetap terkirim saat select disabled -->
                                <input type="hidden" name="guru_id" value="<?= (int)$selGuruId ?>">
                                <input type="hidden" name="nip_lock" value="<?= esc((string)$nipLock, 'attr') ?>">
                            <?php endif; ?>
                            <?php if ($hasErr('guru_id')): ?>
                                <div class="invalid-feedback d-block"><?= esc($getErr('guru_id')) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning mb-2">Guru tidak ditemukan. Tambahkan data guru terlebih dahulu.</div>
                            <select class="form-select" id="guru_id" disabled>
                                <option>— Tidak ada data guru —</option>
                            </select>
                        <?php endif; ?>
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

                    <!-- Status (sesuai enum tb_guru_tahun: aktif / nonaktif) -->
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select<?= $hasErr('status') ? ' is-invalid' : '' ?>"
                            name="status" id="status" aria-describedby="statusFeedback">
                            <option value="" disabled <?= $selStatus ? '' : 'selected' ?>>— Pilih —</option>
                            <option value="aktif" <?= $selStatus === 'aktif'     ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $selStatus === 'nonaktif'  ? 'selected' : '' ?>>Nonaktif</option>
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
                            value="<?= esc(old('tanggal_masuk') ?? '') ?>">
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
                            value="<?= esc(old('tanggal_keluar') ?? '') ?>">
                        <?php if ($hasErr('tanggal_keluar')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('tanggal_keluar')) ?></div>
                        <?php endif; ?>
                        <div class="form-text">Isi jika status Nonaktif (mutasi/pensiun/cuti, dsb.).</div>
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

                    <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-dark rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS: submit loading + lock -->
<script>
    (function() {
        const form = document.getElementById('formTambahGuru');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');

        form?.addEventListener('submit', function() {
            if (btnText) {
                btnText.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            }
            btnSubmit.disabled = true;
            form.classList.add('form-lock');

            // pastikan CSRF tidak disable
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;
        });
    })();
</script>
<?= $this->endSection() ?>