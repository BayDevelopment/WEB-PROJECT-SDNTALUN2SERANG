<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    /* nowrap untuk kolom-kolom tertentu */
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-4 page-section">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul) ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('operator/data-guru') ?>">Data Guru</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>

        <?php
        // total baris
        $total = is_countable($d_penugasan ?? null) ? count($d_penugasan) : 0;
        ?>
        <div class="text-muted small mt-3 mt-sm-0">
            Total Penugasan: <strong><?= number_format($total, 0, ',', '.') ?></strong>
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
                                    id="searchText"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari (guru/mapel/kelas)..."
                                    aria-label="Pencarian penugasan"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <?php
                            // Expect: $tahun (string, mis. "2024/2025") dan
                            // $list_tahun_ajaran: array of ['id_tahun_ajaran'=>..., 'tahun'=>...]
                            $tahun     = (string)($tahun ?? '');
                            $listTahun = $list_tahun_ajaran ?? [];
                            ?>
                            <select id="filterTahun" name="tahun" class="form-select form-select-sm" aria-label="Filter Tahun Ajaran">
                                <option value="" <?= $tahun === '' ? 'selected' : '' ?>>Semua Tahun Ajaran</option>
                                <?php if (!empty($listTahun) && is_array($listTahun)): ?>
                                    <?php foreach ($listTahun as $ta): ?>
                                        <?php
                                        // Ambil string tahun (utama) + fallback ke 'tahun_ajaran' bila ada legacy code
                                        $val = (string)($ta['tahun'] ?? $ta['tahun_ajaran'] ?? '');
                                        if ($val === '') continue;
                                        ?>
                                        <option value="<?= esc($val) ?>" <?= $tahun === $val ? 'selected' : '' ?>>
                                            <?= esc($val) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_penugasan) && is_array($d_penugasan)): ?>
                <div class="table-responsive">
                    <table id="tableDataPenugasan" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px dt-nowrap">No</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th class="dt-nowrap">Tahun Ajaran</th>
                                <th class="dt-nowrap">Jam/Minggu</th>
                                <th>Keterangan</th>
                                <th class="text-end dt-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tablePenugasan">
                            <?php
                            $no = 1;
                            foreach ($d_penugasan as $p):
                                // Deteksi ID baris (fleksibel ke beberapa kemungkinan nama kolom)
                                $id = (int)($p['id_guru_mapel'] ?? 0);
                                $guru   = (string)($p['nama_lengkap'] ?? '-');
                                $mapel  = (string)($p['nama'] ?? '-');
                                $kelas  = (string)($p['kelas'] ?? '-');
                                $tahunA = (string)($p['tahun']  ?? '-');
                                $jam    = (string)($p['jam_per_minggu'] ?? '0');
                                $ket    = (string)($p['keterangan']    ?? '-');
                            ?>
                                <tr
                                    data-guru="<?= esc($guru) ?>"
                                    data-mapel="<?= esc($mapel) ?>"
                                    data-kelas="<?= esc($kelas) ?>"
                                    data-tahun="<?= esc($tahunA) ?>">
                                    <td class="text-muted dt-nowrap"><?= $no++ ?>.</td>
                                    <td class="fw-semibold"><?= esc($guru) ?></td>
                                    <td><?= esc($mapel) ?></td>
                                    <td><?= esc($kelas) ?></td>
                                    <td class="dt-nowrap"><?= esc($tahunA) ?></td>
                                    <td class="dt-nowrap"><span class="font-monospace"><?= esc($jam) ?></span></td>
                                    <td><?= esc($ket) ?></td>
                                    <td class="text-end dt-nowrap">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#"
                                                class="btn btn-outline-danger"
                                                onclick="confirmDeletePenugasan('<?= esc((string)$id, 'js') ?>')"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                            <a href="<?= base_url('operator/guru-mapel/edit/' . urlencode((string)$id)) ?>"
                                                class="btn btn-primary" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </div>
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
                    <h5 class="mb-1">Belum ada data penugasan</h5>
                    <p class="text-muted mb-3">Tambahkan penugasan guru–mapel–kelas untuk mulai mengelola jadwal.</p>
                    <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Penugasan
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Aksi filter (GET)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const q = document.getElementById('searchText');
        const tahun = document.getElementById('filterTahun');

        function resetPageParam() {
            const pageInput = form?.querySelector('input[name="page"]');
            if (pageInput) pageInput.value = '1';
        }

        // debounce input
        let t = null;
        q?.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(() => {
                resetPageParam();
                form.submit();
            }, 350);
        });

        tahun?.addEventListener('change', function() {
            resetPageParam();
            form.submit();
        });
    });

    function confirmDeletePenugasan(id) {
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
                // sesuaikan dengan route yang dipilih:
                window.location.href = "<?= base_url('operator/guru-mapel/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>

<?= $this->endSection() ?>