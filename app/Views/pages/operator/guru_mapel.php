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

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
        border-color: #93c5fd
    }

    /* freeze tanpa menghapus nilai */
    .form-lock {
        position: relative
    }

    .form-lock::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .4);
        pointer-events: auto
    }

    /* overlay blocker (pusat) */
    .form-blocker {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .6);
        backdrop-filter: blur(1px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5
    }

    .form-blocker.d-none {
        display: none
    }

    .form-blocker-inner {
        display: flex;
        align-items: center;
        padding: .5rem .75rem;
        border-radius: .75rem;
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 .4rem 1rem rgba(0, 0, 0, .08);
        font-weight: 600
    }
</style>

<?php
$errors  = session('errors') ?? [];
$hasErr  = fn(string $f) => isset($errors[$f]);
$getErr  = fn(string $f) => $errors[$f] ?? '';

$oldGuru   = old('id_guru',         $d_row['id_guru']          ?? '');
$oldMapel  = old('id_mapel',        $d_row['id_mapel']         ?? '');
$oldTA     = old('id_tahun_ajaran', $d_row['id_tahun_ajaran']  ?? '');
$oldKelas  = old('id_kelas',        $d_row['id_kelas']         ?? '');
$oldJam    = old('jam_per_minggu',  $d_row['jam_per_minggu']   ?? '');
$oldKet    = old('keterangan',      $d_row['keterangan']       ?? '');
?>

<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Tambah Guru Mapel') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('operator/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-guru') ?>">Guru Mapel</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Tambah') ?></li>
            </ol>
        </div>
    </div>

    <?php if (session()->getFlashdata('sweet_error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('sweet_error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('sweet_success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('sweet_success') ?></div>
    <?php endif; ?>

    <!-- Card -->
    <div class="card card-elevated mb-3">
        <div class="card-header-modern">
            <div class="title-wrap">
                <i class="fa-solid fa-user-pen me-2"></i> Form <?= esc($sub_judul ?? 'Tambah Guru Mapel') ?>
            </div>
        </div>

        <div class="card-body">
            <form id="formGuruMapel"
                action="<?= site_url('operator/guru-mapel/tambah') ?>"
                method="post" autocomplete="off" novalidate
                class="position-relative">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <!-- id_guru -->
                    <div class="col-md-6">
                        <label for="id_guru" class="form-label">ID Guru</label>
                        <input type="number" class="form-control<?= $hasErr('id_guru') ? ' is-invalid' : '' ?>"
                            id="id_guru" name="id_guru" value="<?= esc($oldGuru) ?>"
                            placeholder="Masukkan ID guru (angka)"
                            min="1" step="1" inputmode="numeric"
                            aria-describedby="id_guruHelp id_guruFeedback" required <?= $oldGuru !== '' ? 'readonly' : '' ?>>
                        <div id="id_guruHelp" class="form-text">Masukkan ID sesuai master guru (contoh: 123).</div>
                        <?php if ($hasErr('id_guru')): ?>
                            <div id="id_guruFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_guru')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- id_tahun_ajaran -->
                    <div class="col-md-6">
                        <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran</label>
                        <select class="form-select<?= $hasErr('id_tahun_ajaran') ? ' is-invalid' : '' ?>"
                            id="id_tahun_ajaran" name="id_tahun_ajaran" required aria-describedby="id_tahun_ajaranFeedback">
                            <option value="" disabled <?= $oldTA ? '' : 'selected' ?>>— Pilih Tahun Ajaran —</option>
                            <?php foreach (($tahunList ?? []) as $t): ?>
                                <?php $tid = (string)($t['id_tahun_ajaran'] ?? '');
                                $ttahun = (string)($t['tahun'] ?? '');
                                $tsem = (string)($t['semester'] ?? ''); ?>
                                <option value="<?= esc($tid, 'attr') ?>" <?= $oldTA == $tid ? 'selected' : '' ?>>
                                    <?= esc("$ttahun — " . ucfirst($tsem)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($hasErr('id_tahun_ajaran')): ?>
                            <div id="id_tahun_ajaranFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_tahun_ajaran')) ?></div>
                        <?php endif; ?>
                    </div>

                    <?php
                    $oldMapelArr = old('id_mapel');
                    if (!is_array($oldMapelArr)) {
                        $oldMapelArr = isset($d_row['id_mapel_list']) && is_array($d_row['id_mapel_list'])
                            ? array_map('strval', $d_row['id_mapel_list'])
                            : [];
                    }
                    $hasErrorMapel = $hasErr('id_mapel');
                    ?>

                    <!-- MAPEL (MULTI) -->
                    <div class="col-md-12">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>Mata Pelajaran</span>
                            <button type="button" id="btnAddMapel" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-plus"></i> Tambah Mapel
                            </button>
                        </label>

                        <div id="mapelContainer" class="d-flex flex-column gap-2">
                            <?php $rows = count($oldMapelArr) > 0 ? $oldMapelArr : [''];
                            foreach ($rows as $i => $selId): ?>
                                <div class="mapel-row d-flex gap-2">
                                    <select class="form-select<?= $hasErrorMapel ? ' is-invalid' : '' ?>" name="id_mapel[]" required aria-describedby="id_mapelFeedback">
                                        <option value="" disabled <?= $selId === '' ? 'selected' : '' ?>>— Pilih Mapel —</option>
                                        <?php foreach (($mapelList ?? []) as $m): ?>
                                            <?php
                                            $mid = (string)($m['id_mapel'] ?? '');
                                            $mnama = (string)($m['nama_mapel'] ?? $m['nama'] ?? '');
                                            $mkode = (string)($m['kode'] ?? $m['kode_mapel'] ?? '');
                                            $label = trim(($mkode ? "[$mkode] " : '') . $mnama);
                                            ?>
                                            <option value="<?= esc($mid, 'attr') ?>" <?= $selId === $mid ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-danger btnRemoveMapel<?= $i === 0 ? ' d-none' : '' ?>" title="Hapus baris">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($hasErrorMapel): ?>
                            <div id="id_mapelFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_mapel')) ?></div>
                        <?php else: ?>
                            <div class="form-text">Anda bisa menambahkan lebih dari satu mapel. Duplikasi akan ditolak.</div>
                        <?php endif; ?>
                    </div>

                    <!-- TEMPLATE MAPEL -->
                    <template id="mapelRowTpl">
                        <div class="mapel-row d-flex gap-2">
                            <select class="form-select" name="id_mapel[]" required>
                                <option value="" disabled selected>— Pilih Mapel —</option>
                                <?php foreach (($mapelList ?? []) as $m): ?>
                                    <?php
                                    $mid = (string)($m['id_mapel'] ?? '');
                                    $mnama = (string)($m['nama_mapel'] ?? $m['nama'] ?? '');
                                    $mkode = (string)($m['kode'] ?? $m['kode_mapel'] ?? '');
                                    $label = trim(($mkode ? "[$mkode] " : '') . $mnama);
                                    ?>
                                    <option value="<?= esc($mid, 'attr') ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline-danger btnRemoveMapel" title="Hapus baris">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </template>

                    <!-- KELAS (MULTI) -->
                    <div class="col-md-12">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>Kelas</span>
                            <button type="button" id="btnAddKelas" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-plus"></i> Tambah Kelas
                            </button>
                        </label>

                        <div id="kelasContainer" class="d-flex flex-column gap-2">
                            <div class="kelas-row d-flex gap-2">
                                <select class="form-select<?= $hasErr('id_kelas') ? ' is-invalid' : '' ?>" name="id_kelas[]" required aria-describedby="id_kelasFeedback">
                                    <option value="" disabled selected>— Pilih Kelas —</option>
                                    <?php foreach (($kelasList ?? []) as $k): ?>
                                        <?php
                                        $kid = (string)($k['id_kelas'] ?? '');
                                        $knama = (string)($k['nama_kelas'] ?? $k['nama'] ?? '');
                                        $kting = (string)($k['tingkat'] ?? '');
                                        $kjur = (string)($k['jurusan'] ?? '');
                                        $label = $knama ?: trim(($kting !== '' ? $kting . ' ' : '') . ($kjur ?: ''));
                                        ?>
                                        <option value="<?= esc($kid, 'attr') ?>"><?= esc($label ?: ('Kelas #' . $kid)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-danger btnRemoveKelas d-none">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <?php if ($hasErr('id_kelas')): ?>
                            <div id="id_kelasFeedback" class="invalid-feedback d-block"><?= esc($getErr('id_kelas')) ?></div>
                        <?php endif; ?>
                        <div class="form-text">Anda dapat menambahkan lebih dari satu kelas.</div>
                    </div>

                    <!-- TEMPLATE KELAS -->
                    <template id="kelasRowTpl">
                        <div class="kelas-row d-flex gap-2">
                            <select class="form-select" name="id_kelas[]" required>
                                <option value="" disabled selected>— Pilih Kelas —</option>
                                <?php foreach (($kelasList ?? []) as $k): ?>
                                    <?php
                                    $kid = (string)($k['id_kelas'] ?? '');
                                    $knama = (string)($k['nama_kelas'] ?? $k['nama'] ?? '');
                                    $kting = (string)($k['tingkat'] ?? '');
                                    $kjur = (string)($k['jurusan'] ?? '');
                                    $label = $knama ?: trim(($kting !== '' ? $kting . ' ' : '') . ($kjur ?: ''));
                                    ?>
                                    <option value="<?= esc($kid, 'attr') ?>"><?= esc($label ?: ('Kelas #' . $kid)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline-danger btnRemoveKelas">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </template>

                    <!-- jam_per_minggu -->
                    <div class="col-md-4">
                        <label for="jam_per_minggu" class="form-label">Jam / Minggu</label>
                        <input type="number" class="form-control<?= $hasErr('jam_per_minggu') ? ' is-invalid' : '' ?>"
                            id="jam_per_minggu" name="jam_per_minggu" value="<?= esc($oldJam) ?>"
                            placeholder="Contoh: 6" min="0" step="1" inputmode="numeric" required
                            aria-describedby="jam_per_mingguFeedback">
                        <?php if ($hasErr('jam_per_minggu')): ?>
                            <div id="jam_per_mingguFeedback" class="invalid-feedback d-block"><?= esc($getErr('jam_per_minggu')) ?></div>
                        <?php else: ?>
                            <div id="jam_per_mingguFeedback" class="form-text">Masukkan jumlah jam per minggu (bilangan bulat).</div>
                        <?php endif; ?>
                    </div>

                    <!-- keterangan -->
                    <div class="col-md-8">
                        <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                        <textarea class="form-control<?= $hasErr('keterangan') ? ' is-invalid' : '' ?>"
                            id="keterangan" name="keterangan" rows="3"
                            placeholder="Catatan khusus penugasan guru/mapel/kelas..."><?= esc($oldKet) ?></textarea>
                        <?php if ($hasErr('keterangan')): ?>
                            <div class="invalid-feedback d-block"><?= esc($getErr('keterangan')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-gradient rounded-pill d-inline-flex align-items-center">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan</span>
                    </button>
                    <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-dark rounded-pill">
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

<script>
    /* ==== KELAS MULTI ==== */
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('kelasContainer');
        const tpl = document.getElementById('kelasRowTpl');
        const btnAdd = document.getElementById('btnAddKelas');

        function syncRemoveButtons() {
            const rows = container.querySelectorAll('.kelas-row');
            rows.forEach((row, idx) => {
                row.querySelector('.btnRemoveKelas')?.classList.toggle('d-none', idx === 0);
            });
        }

        btnAdd?.addEventListener('click', () => {
            container.appendChild(tpl.content.cloneNode(true));
            syncRemoveButtons();
        });

        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.btnRemoveKelas');
            if (!btn) return;
            const row = btn.closest('.kelas-row');
            if (!row) return;
            row.remove();
            syncRemoveButtons();
        });

        syncRemoveButtons();
    });

    /* ==== MAPEL MULTI ==== */
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('mapelContainer');
        const tpl = document.getElementById('mapelRowTpl');
        const btnAdd = document.getElementById('btnAddMapel');

        function values() {
            return Array.from(container.querySelectorAll('select[name="id_mapel[]"]'))
                .map(s => s.value).filter(Boolean);
        }

        function hasDup(val) {
            if (!val) return false;
            const v = values();
            return v.filter(x => x === val).length > 1;
        }

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.mapel-row');
            rows.forEach((row, idx) => {
                row.querySelector('.btnRemoveMapel')?.classList.toggle('d-none', rows.length === 1 && idx === 0);
            });
        }

        btnAdd?.addEventListener('click', () => {
            const node = tpl.content.firstElementChild.cloneNode(true);
            container.appendChild(node);
            updateRemoveButtons();
        });

        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.btnRemoveMapel');
            if (!btn) return;
            const row = btn.closest('.mapel-row');
            if (!row) return;
            if (container.querySelectorAll('.mapel-row').length <= 1) return;
            row.remove();
            updateRemoveButtons();
        });

        container.addEventListener('change', (e) => {
            const sel = e.target;
            if (!(sel instanceof HTMLSelectElement)) return;
            if (sel.name !== 'id_mapel[]') return;
            if (hasDup(sel.value)) {
                sel.value = '';
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        icon: 'warning',
                        title: 'Mapel sudah dipilih.'
                    });
                } else {
                    alert('Mapel sudah dipilih.');
                }
            }
        });

        updateRemoveButtons();
    });

    /* ==== ANIMASI SUBMIT (spinner, overlay, freeze input) ==== */
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formGuruMapel');
        const btn = document.getElementById('btnSubmit');
        const spin = btn ? btn.querySelector('.spinner-border') : null;
        const txt = btn ? btn.querySelector('.btn-text') : null;
        const blk = document.getElementById('formBlocker');
        if (!form || !btn) return;

        let loading = false;

        function freezeInputs(container) {
            const textLike = 'input[type="text"],input[type="email"],input[type="password"],input[type="number"],input[type="date"],input[type="time"],input[type="datetime-local"],input[type="search"],input[type="tel"],textarea';
            container.querySelectorAll(textLike).forEach(el => {
                el.setAttribute('readonly', 'readonly');
                el.setAttribute('aria-readonly', 'true');
            });
            container.querySelectorAll('select,input[type="checkbox"],input[type="radio"]').forEach(el => {
                el.setAttribute('aria-disabled', 'true');
            });
            // file input dibiarkan; overlay akan memblok interaksi
        }

        function armLoading(e) {
            if (loading) return;
            loading = true;

            if (spin) spin.classList.remove('d-none');
            if (txt) txt.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Menyimpan…';

            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('disabled');

            if (blk) blk.classList.remove('d-none');
            form.setAttribute('aria-busy', 'true');
            form.classList.add('form-lock');
            freezeInputs(form);

            // pastikan CSRF tidak disable
            const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrf) csrf.disabled = false;

            if (e && e.preventDefault) e.preventDefault();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => form.submit());
            });
        }

        btn.addEventListener('click', armLoading);
        form.addEventListener('submit', armLoading);
    });
</script>

<?= $this->endSection() ?>