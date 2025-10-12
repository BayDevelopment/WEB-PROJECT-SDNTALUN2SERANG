<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-4 page-section">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Kategori Penilaian') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-modern mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc($sub_judul ?? 'Kategori Penilaian') ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Form Tambah -->
    <div class="card card-elevated mb-3">
        <div class="card-body">
            <form id="formTambahKategori"
                action="<?= base_url('operator/kategori/tambah') ?>"
                method="post" autocomplete="off" class="row g-3">
                <?= csrf_field() ?>

                <?php
                $errors = session('errors') ?? [];
                $hasErr = fn($f) => isset($errors[$f]);
                $getErr = fn($f) => $errors[$f] ?? '';
                ?>

                <!-- kode -->
                <div class="col-md-3">
                    <label for="kode" class="form-label">Kode</label>
                    <input type="text" class="form-control<?= $hasErr('kode') ? ' is-invalid' : '' ?>"
                        id="kode" name="kode"
                        value="<?= esc(old('kode') ?? '') ?>"
                        placeholder="Mis: UTS/UAS/TGS" maxlength="20" required>
                    <?php if ($hasErr('kode')): ?>
                        <div class="invalid-feedback d-block"><?= esc($getErr('kode')) ?></div>
                    <?php endif; ?>
                </div>

                <!-- nama -->
                <div class="col-md-5">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control<?= $hasErr('nama') ? ' is-invalid' : '' ?>"
                        id="nama" name="nama"
                        value="<?= esc(old('nama') ?? '') ?>"
                        placeholder="Nama kategori" maxlength="100" required>
                    <?php if ($hasErr('nama')): ?>
                        <div class="invalid-feedback d-block"><?= esc($getErr('nama')) ?></div>
                    <?php endif; ?>
                </div>

                <!-- bobot -->
                <div class="col-md-2">
                    <label for="bobot" class="form-label">Bobot</label>
                    <input type="number" step="0.01" min="0" max="100"
                        class="form-control<?= $hasErr('bobot') ? ' is-invalid' : '' ?>"
                        id="bobot" name="bobot"
                        value="<?= esc(old('bobot') ?? '0') ?>"
                        placeholder="0-100" required>
                    <?php if ($hasErr('bobot')): ?>
                        <div class="invalid-feedback d-block"><?= esc($getErr('bobot')) ?></div>
                    <?php endif; ?>
                </div>

                <!-- is_wajib -->
                <div class="col-md-2">
                    <label class="form-label d-block">Status Wajib</label>
                    <input type="hidden" name="is_wajib" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input<?= $hasErr('is_wajib') ? ' is-invalid' : '' ?>"
                            type="checkbox" id="is_wajib" name="is_wajib" value="1"
                            <?= old('is_wajib', '1') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_wajib">Wajib</label>
                    </div>
                    <?php if ($hasErr('is_wajib')): ?>
                        <div class="invalid-feedback d-block"><?= esc($getErr('is_wajib')) ?></div>
                    <?php endif; ?>
                </div>

                <!-- actions -->
                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" id="btnSubmit" class="btn btn-primary">
                        <span class="btn-text"><i class="fa-solid fa-save me-2"></i> Simpan</span>
                    </button>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-rotate-left me-2"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toolbar Pencarian -->
    <div class="card card-elevated mb-3">
        <div class="card-body">
            <form id="filterForm" method="get" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="searchKategori" class="form-label form-label-sm">Cari Kode/Nama</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchKategori" type="text" name="q"
                            value="<?= esc($q ?? '') ?>" class="form-control"
                            placeholder="Ketik kode atau nama kategori..." autocomplete="off">
                    </div>
                </div>
                <div class="col-6 col-md-2 d-grid">
                    <button class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-filter me-2"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card card-elevated mb-3">
        <div class="card-body">
            <?php if (!empty($d_kategori)): ?>
                <div class="table-responsive">
                    <table id="tableDataKategori" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th class="text-center">Bobot</th>
                                <th class="text-center">Wajib?</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($d_kategori as $row):
                                $id      = (int)($row['id_kategori'] ?? 0);
                                $kode    = (string)($row['kode'] ?? '');
                                $nama    = (string)($row['nama'] ?? '');
                                $bobot   = (float)($row['bobot'] ?? 0);
                                $isWajib = (int)($row['is_wajib'] ?? 0) === 1;
                                $badgeClass = $isWajib ? 'bg-primary' : 'bg-secondary';
                                $badgeText  = $isWajib ? 'Wajib' : 'Opsional';
                            ?>
                                <tr data-kode="<?= esc(strtolower($kode)) ?>" data-nama="<?= esc(strtolower($nama)) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td class="font-monospace"><?= esc($kode) ?></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td class="text-center"><?= esc(rtrim(rtrim(number_format($bobot, 2, '.', ''), '0'), '.')) ?></td>
                                    <td class="text-center"><span class="badge <?= $badgeClass ?>"><?= esc($badgeText) ?></span></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#"
                                                class="btn btn-outline-danger"
                                                title="Hapus"
                                                onclick="confirmDeleteKategori('<?= esc((string)$id, 'js') ?>')">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-card text-center p-5">
                    <img src="<?= base_url('assets/img/empty-box.png') ?>" class="empty-illustration mb-3" alt="Kosong">
                    <h5 class="mb-1">Belum ada kategori penilaian</h5>
                    <p class="text-muted mb-3">Tambahkan data kategori terlebih dahulu (kode, nama, bobot, dan status wajib).</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // debounce pencarian
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inpQ = document.getElementById('searchKategori');
        let t = null;
        inpQ?.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(() => form.submit(), 350);
        });
    });

    // loading state submit form tambah
    (function() {
        const form = document.getElementById('formTambahKategori');
        const btn = document.getElementById('btnSubmit');
        const txt = btn?.querySelector('.btn-text');
        form?.addEventListener('submit', function() {
            if (txt) txt.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            btn.disabled = true;
        });
    })();

    // konfirmasi hapus kategori
    function confirmDeleteKategori(id) {
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
            focusCancel: true
        }).then((r) => {
            if (r.isConfirmed) {
                window.location.href = "<?= base_url('operator/kategori/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>

<?= $this->endSection() ?>