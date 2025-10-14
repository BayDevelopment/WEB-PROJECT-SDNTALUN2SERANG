<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-4 page-section">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title"><?= esc($sub_judul ?? 'Data Mata Pelajaran') ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul ?? 'Data Mata Pelajaran') ?></li>
            </ol>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input
                                    id="searchMatpel"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari data Mata Pelajaran (kode/nama)"
                                    aria-label="Pencarian Mata Pelajaran"
                                    autocomplete="off">
                            </div>
                        </div>
                    </form>
                </div>

                <?php if (!empty($d_mapel)): ?>
                    <!-- Tombol Tambah (di luar form) -->
                    <div class="col-12 col-md-3 text-md-end">
                        <a href="<?= base_url('operator/matpel/tambah') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-md-auto">
                            <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah
                        </a>
                    </div>
                <?php else: ?>
                <?php endif; ?>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_mapel) && is_array($d_mapel)): ?>
                <div class="table-responsive">
                    <table id="tableDataMatpel" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Kode</th>
                                <th>Nama Mata Pelajaran</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableMatpel">
                            <?php $no = 1;
                            foreach ($d_mapel as $m):
                                $id   = (int)($m['id_mapel'] ?? 0);
                                $kode = (string)($m['kode_mapel'] ?? $m['kode'] ?? '-');
                                $nama = (string)($m['nama_mapel'] ?? $m['nama'] ?? '-');
                            ?>
                                <tr data-kode="<?= esc($kode) ?>" data-nama="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td class="font-monospace"><?= esc($kode) ?></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-outline-danger" onclick="confirmDeleteMatpel('<?= esc((string)$id, 'js') ?>')" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                            <a href="<?= base_url('operator/matpel/detail/' . urlencode((string)$id)) ?>" class="btn btn-outline-secondary" title="Detail">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('operator/matpel/edit/' . urlencode((string)$id)) ?>" class="btn btn-primary" title="Edit">
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
                    <i class="fa-solid fa-book-open fa-3x mb-3 text-muted"></i>
                    <h5 class="mb-1">Belum ada data Mata Pelajaran</h5>
                    <p class="text-muted mb-3">Tambahkan data Mata Pelajaran pertama untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/matpel/tambah') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
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
        const q = document.getElementById('searchMatpel');

        // reset ?page ke 1 sebelum submit (jika kamu pakai pagination querystring)
        function resetPageParam() {
            const pageInput = form?.querySelector('input[name="page"]');
            if (pageInput) pageInput.value = '1';
        }

        // debounce input pencarian
        let t = null;
        q?.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(() => {
                resetPageParam();
                form.submit();
            }, 350);
        });
    });

    // optional: konfirmasi hapus (dipakai di tabel)
    function confirmDeleteMatpel(id) {
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
                window.location.href = "<?= base_url('operator/matpel/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>
<?= $this->endSection() ?>