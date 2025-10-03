<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
    /* nowrap ke semua sel jika diperlukan */
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }

    .badge-male {
        background: #e7f1ff;
        color: #0d6efd;
    }

    .badge-female {
        background: #ffe7f3;
        color: #d63384;
    }

    .badge-unknown {
        background: #eee;
        color: #666;
    }
</style>
<div class="container-fluid px-4 page-section">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul) ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>
        <?php
        $total = is_countable($d_guru ?? null) ? count($d_guru) : 0;

        $aktif = 0;
        if (!empty($d_guru) && is_array($d_guru)) {
            foreach ($d_guru as $u) {
                $flag = (string)($u['is_active'] ?? $u['status_active'] ?? '0');
                if ($flag === '1') $aktif++;
            }
        }
        $nonaktif = max($total - $aktif, 0);
        ?>
        <div class="text-muted small mt-3 mt-sm-0">
            Total User: <strong><?= number_format($total, 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Aktif: <strong class="text-success"><?= number_format($aktif, 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Nonaktif: <strong class="text-muted"><?= number_format($nonaktif, 0, ',', '.') ?></strong>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input
                                    id="searchSiswa"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari nama atau NIP..."
                                    aria-label="Pencarian nama atau NIP"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <select id="filterGender" name="gender" class="form-select form-select-sm" aria-label="Filter gender">
                                <?php $g = $gender ?? ''; ?>
                                <option value="" <?= $g === '' ? 'selected' : '' ?>>Semua Gender</option>
                                <option value="L" <?= $g === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $g === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah (di luar form) -->
                <?php if (!empty($d_guru)): ?>
                    <div class="col-12 col-md-3 text-md-end">
                        <a href="<?= base_url('operator/tambah-guru') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-md-auto">
                            <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Tabel -->
            <?php if (!empty($d_guru)): ?>
                <div class="table-responsive">
                    <table id="tableDataGuru" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Foto</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableGuru">
                            <?php
                            $no = 1; // <- inisialisasi counter sekali di luar foreach

                            // lokasi file untuk foto
                            $uploadsRel = 'assets/img/uploads/';   // relatif dari public/
                            $defaultRel = 'assets/img/user.png';   // fallback default
                            ?>

                            <?php foreach ($d_guru as $d_g): ?>
                                <?php
                                $nip   = (string)($d_g['nip'] ?? '');
                                $nama  = (string)($d_g['nama_lengkap'] ?? '');
                                $jk    = strtoupper((string)($d_g['jenis_kelamin'] ?? '')); // L/P/-
                                $tag   = ($jk === 'L') ? 'Laki-laki' : (($jk === 'P') ? 'Perempuan' : '—');
                                $badgeClass = ($jk === 'L') ? 'badge-male' : (($jk === 'P') ? 'badge-female' : 'badge-unknown');

                                // tentukan URL gambar
                                $foto = trim((string)($d_g['foto'] ?? ''));
                                if ($foto !== '' && preg_match('~^https?://~i', $foto)) {
                                    $img = $foto; // URL absolut
                                } elseif ($foto !== '' && preg_match('/^[a-zA-Z0-9._-]+$/', $foto) && is_file(FCPATH . $uploadsRel . $foto)) {
                                    $img = base_url($uploadsRel . $foto); // file lokal valid
                                } else {
                                    $img = base_url($defaultRel); // fallback default
                                }
                                ?>

                                <tr data-name="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>"
                                    data-nip="<?= esc($nip) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td>
                                        <div class="avatar-wrap">
                                            <img src="<?= esc($img) ?>" alt="Foto <?= esc($nama) ?>" class="avatar-40 rounded-circle">
                                        </div>
                                    </td>
                                    <td><span class="font-monospace"><?= esc($nip) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td>
                                        <span class="badge <?= esc($badgeClass) ?>"><?= esc($tag) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-outline-danger"
                                                onclick="confirmDeleteGuru('<?= esc($nip, 'js') ?>')"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                            <a href="<?= base_url('operator/detail-guru/' . urlencode($nip)) ?>"
                                                class="btn btn-outline-secondary" title="Detail">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('operator/edit-guru/' . urlencode($nip)) ?>"
                                                class="btn btn-primary" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($d_guru)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data guru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            <?php else: ?>
                <!-- Empty state -->
                <div class="empty-card text-center p-5">
                    <img src="<?= base_url('assets/img/empty-box.png') ?>" class="empty-illustration mb-3" alt="Kosong">
                    <h5 class="mb-1">Belum ada data guru</h5>
                    <p class="text-muted mb-3">Tambahkan data guru pertama Anda untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/tambah-guru') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inpQ = document.getElementById('searchSiswa');
        const selG = document.getElementById('filterGender');

        // Debounce submit saat mengetik
        let timer = null;
        inpQ.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 350);
        });

        // Submit otomatis saat dropdown berubah
        selG.addEventListener('change', function() {
            form.submit();
        });
    });
</script>
<?= $this->endSection() ?>