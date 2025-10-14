<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
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

    .avatar-40 {
        width: 40px;
        height: 40px;
        object-fit: cover;
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
        <div class="text-muted small mt-3 mt-sm-0">
            Total Guru: <strong><?= number_format((int)($totalGuru ?? 0), 0, ',', '.') ?></strong>
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
                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input
                                    id="searchGuru"
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
                            <?php $g = strtoupper((string)($gender ?? '')); ?>
                            <select id="filterGender" name="gender" class="form-select form-select-sm" aria-label="Filter gender">
                                <option value="" <?= $g === ''  ? 'selected' : '' ?>>Semua Gender</option>
                                <option value="L" <?= $g === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $g === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_guru)): ?>
                <div class="table-responsive">
                    <table id="tableDataGuruSiswa" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Foto</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tableGuru">
                            <?php
                            $no         = 1;
                            $uploadsRel = 'assets/img/uploads/';
                            $defaultRel = 'assets/img/user.png';
                            foreach ($d_guru as $g):
                                $idGuru = (int)($g['id_guru'] ?? 0);
                                $nip    = (string)($g['nip'] ?? '');
                                $nama   = (string)($g['nama_lengkap'] ?? '');

                                $jkRaw  = strtoupper((string)($g['jenis_kelamin'] ?? '')); // L / P
                                $tagJK  = ($jkRaw === 'L') ? 'Laki-laki' : (($jkRaw === 'P') ? 'Perempuan' : '—');
                                $badge  = ($jkRaw === 'L') ? 'badge-male' : (($jkRaw === 'P') ? 'badge-female' : 'badge-unknown');

                                $aktif  = (int)($g['status_active'] ?? 0) === 1;

                                // foto (opsional di DB): jika tidak ada → default
                                $foto = trim((string)($g['foto'] ?? ''));
                                if ($foto !== '' && preg_match('~^https?://~i', $foto)) {
                                    $img = $foto;
                                } elseif ($foto !== '' && preg_match('/^[a-zA-Z0-9._-]+$/', $foto) && is_file(FCPATH . $uploadsRel . $foto)) {
                                    $img = base_url($uploadsRel . $foto);
                                } else {
                                    $img = base_url($defaultRel);
                                }

                                $hasLaporanTa = !empty($g['has_laporan_ta']);
                            ?>
                                <tr data-name="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>" data-nip="<?= esc($nip) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td><img src="<?= esc($img) ?>" alt="Foto <?= esc($nama) ?>" class="avatar-40 rounded-circle border"></td>
                                    <td><span class="font-monospace"><?= esc($nip) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td><span class="badge <?= esc($badge) ?>"><?= esc($tagJK) ?></span></td>
                                    <td>
                                        <?php if ($aktif): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Empty state -->
                <div class="empty-card text-center p-5">
                    <img src="<?= base_url('assets/img/empty-box.png') ?>" class="empty-illustration mb-3" alt="Kosong">
                    <h5 class="mb-1">Belum ada data guru</h5>
                    <p class="text-muted mb-3">Silahkan hubungi operator</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inpQ = document.getElementById('searchGuru');
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

    function confirmDeleteGuru(id) {
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
                window.location.href = "<?= base_url('operator/data-guru/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>
<?= $this->endSection() ?>