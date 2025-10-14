<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
    /* nowrap ke semua sel jika diperlukan */
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-4 page-section">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title">
                <i class="fa-solid fa-chalkboard-user me-2"></i><?= esc($sub_judul) ?>
            </h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>

        <?php
        // Hitung total kelas
        $total = is_countable($d_kelas ?? null) ? count($d_kelas) : 0;
        ?>
        <div class="text-muted small mt-3 mt-sm-0">
            Total Kelas: <strong><?= number_format($total, 0, ',', '.') ?></strong>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <?php
                // Nilai filter dari controller (jika ada) atau fallback dari GET
                $q        = (string)($q ?? ($_GET['q'] ?? ''));
                $tingkatF = (string)($tingkat ?? ($_GET['tingkat'] ?? ''));
                $jurusanF = (string)($jurusan ?? ($_GET['jurusan'] ?? ''));

                // Siapkan opsi filter tingkat & jurusan
                $optTingkat = [];
                $optJurusan = [];

                if (!empty($list_tingkat) && is_array($list_tingkat)) {
                    foreach ($list_tingkat as $t) {
                        $t = (string)$t;
                        if ($t !== '' && !in_array($t, $optTingkat, true)) $optTingkat[] = $t;
                    }
                } elseif (!empty($d_kelas) && is_array($d_kelas)) {
                    foreach ($d_kelas as $row) {
                        $t = (string)($row['tingkat'] ?? '');
                        if ($t !== '' && !in_array($t, $optTingkat, true)) $optTingkat[] = $t;

                        $j = (string)($row['jurusan'] ?? '');
                        if ($j !== '' && !in_array($j, $optJurusan, true)) $optJurusan[] = $j;
                    }
                }

                if (!empty($list_jurusan) && is_array($list_jurusan)) {
                    foreach ($list_jurusan as $j) {
                        $j = (string)$j;
                        if ($j !== '' && !in_array($j, $optJurusan, true)) $optJurusan[] = $j;
                    }
                }

                sort($optTingkat);
                sort($optJurusan);
                ?>

                <!-- Filter (Form GET) -->
                <div class="col-12 col-lg-9">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <div class="col-12 col-md-9">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input
                                    id="searchKelas"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q) ?>"
                                    class="form-control"
                                    placeholder="Cari ID atau Nama Kelas..."
                                    aria-label="Pencarian kelas"
                                    autocomplete="off">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah -->
                <?php if (!empty($d_kelas)): ?>
                    <div class="col-12 col-lg-3 text-lg-end">
                        <a href="<?= base_url('operator/kelas/tambah') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-lg-auto">
                            <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_kelas)): ?>
                <div class="table-responsive">
                    <table id="tableDataKelas" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableKelas">
                            <?php $no = 1; ?>
                            <?php foreach ($d_kelas as $row): ?>
                                <?php
                                $id_kelas = (int)($row['id_kelas']);
                                $nama_kelas = (string)($row['nama_kelas'] ?? '');
                                $tingkat    = (string)($row['tingkat'] ?? '');
                                $jurusan    = (string)($row['jurusan'] ?? '');
                                ?>
                                <tr
                                    data-nama="<?= esc(mb_strtolower($nama_kelas, 'UTF-8')) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td class="fw-semibold"><?= esc($nama_kelas) ?></td>
                                    <td><?= esc($tingkat) ?></td>
                                    <?php if (!empty($jurusan)): ?>
                                        <td><?= esc($jurusan) ?></td>
                                    <?php else: ?>
                                        <td class="text-danger">Tidak Ada</td>
                                    <?php endif; ?>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('operator/kelas/detail/' . urlencode($id_kelas)) ?>"
                                                class="btn btn-outline-secondary" title="Detail">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('operator/kelas/edit/' . urlencode($id_kelas)) ?>"
                                                class="btn btn-primary" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="confirmDeleteKelas('<?= esc($id_kelas, 'js') ?>')"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
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
                    <h5 class="mb-1">Belum ada data kelas</h5>
                    <p class="text-muted mb-3">Tambahkan data kelas pertama Anda untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/kelas/tambah') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
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
        const inpQ = document.getElementById('searchKelas');
        const selT = document.getElementById('filterTingkat');
        const selJ = document.getElementById('filterJurusan');

        // Debounce submit saat mengetik
        let timer = null;
        inpQ && inpQ.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 350);
        });

        // Submit otomatis saat dropdown berubah
        selT && selT.addEventListener('change', function() {
            form.submit();
        });
        selJ && selJ.addEventListener('change', function() {
            form.submit();
        });
    });

    function confirmDeleteKelas(id) {
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
                window.location.href = "<?= base_url('operator/kelas/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>

<?= $this->endSection() ?>