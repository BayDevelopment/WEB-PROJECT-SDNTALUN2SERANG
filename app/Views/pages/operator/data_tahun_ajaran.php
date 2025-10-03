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
                                    placeholder="Cari data Tahun Ajaran"
                                    aria-label="Pencarian Tahun Ajaran"
                                    autocomplete="off">
                            </div>
                        </div>
                    </form>
                </div>

                <?php if (!empty($d_TahunAjaran)): ?>
                    <!-- Tombol Tambah (di luar form) -->
                    <div class="col-12 col-md-3 text-md-end">
                        <a href="<?= base_url('operator/tambah/tahun-ajaran') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-md-auto">
                            <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah
                        </a>
                    </div>
                <?php else: ?>
                <?php endif; ?>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_TahunAjaran) && is_array($d_TahunAjaran)): ?>
                <div class="table-responsive">
                    <table id="tableDataMatpel" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Semester</th>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableMatpel">
                            <?php $no = 1; ?>
                            <?php if (!empty($d_TahunAjaran) && is_array($d_TahunAjaran)): ?>
                                <?php foreach ($d_TahunAjaran as $m): ?>
                                    <?php
                                    $id     = (int)($m['id_tahun_ajaran'] ?? $m['id'] ?? 0);
                                    $kode   = (string)($m['semester'] ?? $m['kode'] ?? '-');          // ganjil/genap
                                    $tahun  = (string)($m['tahun'] ?? '-');

                                    // Normalisasi status (menerima 1/0, true/false, '1'/'0', 'true'/'false')
                                    $rawStatus  = $m['is_active'] ?? 0;
                                    $isActive   = ($rawStatus === 1 || $rawStatus === true || $rawStatus === '1' || $rawStatus === 'true' || $rawStatus === 'TRUE');
                                    $statusText = $isActive ? 'Aktif' : 'Nonaktif';
                                    $statusCls  = $isActive ? 'bg-success' : 'bg-secondary'; // bisa diganti bg-danger kalau mau
                                    ?>
                                    <tr
                                        data-kode="<?= esc($kode, 'attr') ?>"
                                        data-tahun="<?= esc(mb_strtolower($tahun, 'UTF-8'), 'attr') ?>">
                                        <td class="text-muted"><?= $no++ ?>.</td>
                                        <td class="font-monospace"><?= esc(ucfirst($kode)) ?></td>
                                        <td class="fw-semibold"><?= esc($tahun) ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= esc($statusCls, 'attr') ?>">
                                                <?= esc($statusText) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Aksi tahun ajaran">
                                                <a href="#"
                                                    class="btn btn-outline-danger"
                                                    onclick="confirmDeleteMatpel('<?= esc((string)$id, 'js') ?>'); return false;"
                                                    title="Hapus" aria-label="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                                <!-- Sesuaikan segment URL dengan modul kamu.
                                 Jika ini memang modul Tahun Ajaran, ganti 'matpel' jadi 'tahun-ajaran' -->
                                                <a href="<?= base_url('operator/detail/tahun-ajaran/' . rawurlencode((string)$id)) ?>"
                                                    class="btn btn-outline-secondary" title="Detail" aria-label="Detail">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>

                                                <a href="<?= base_url('operator/edit/tahun-ajaran/' . rawurlencode((string)$id)) ?>"
                                                    class="btn btn-primary" title="Edit" aria-label="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada data tahun ajaran.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Empty state -->
                <div class="empty-card text-center p-5">
                    <i class="fa-solid fa-book-open fa-3x mb-3 text-muted"></i>
                    <h5 class="mb-1">Belum ada Tahun Ajaran</h5>
                    <p class="text-muted mb-3">Tambahkan data Tahun Ajaran untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/tambah/tahun-ajaran') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Filter GET (opsional, bila controller mendukung ?q=)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const q = document.getElementById('searchMatpel');
        let t = null;
        q?.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(() => form.submit(), 350);
        });
    });

    // DataTables (tanpa jQuery)
    document.addEventListener('DOMContentLoaded', function() {
        const tableEl = document.getElementById('tableDataMatpel');
        if (!tableEl) return;

        const dt = new DataTable(tableEl, {
            dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                className: 'btn btn-success rounded-pill',
                title: 'Laporan_TahunAjaran',
                filename: () => {
                    const d = new Date().toISOString().slice(0, 10);
                    return `Laporan_TahunAjaran_${d}`;
                },
                exportOptions: {
                    // No, Kode, Tahun, Status (tanpa kolom Aksi)
                    columns: [0, 1, 2, 3],
                    modifier: {
                        search: 'applied',
                        order: 'applied',
                        page: 'all'
                    },
                    format: {
                        body: function(data) {
                            // Ambil teks murni walau berisi badge/icon/link
                            if (data && data.nodeType === 1) return data.textContent.trim();
                            if (typeof data === 'string') {
                                const tmp = document.createElement('div');
                                tmp.innerHTML = data;
                                return (tmp.textContent || '').trim();
                            }
                            return data ?? '';
                        }
                    }
                }
            }],
            responsive: {
                details: {
                    type: 'inline',
                    target: 'tr'
                }
            },
            scrollX: true,
            autoWidth: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Semua']
            ],
            pageLength: 10,
            stateSave: true,
            language: {
                processing: 'Memproses...',
                lengthMenu: 'Tampilkan _MENU_ entri',
                zeroRecords: 'Tidak ditemukan data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                infoEmpty: 'Menampilkan 0–0 dari 0 entri',
                infoFiltered: '(disaring dari _MAX_ entri)',
                search: 'Cari:',
                paginate: {
                    first: 'Awal',
                    previous: '‹',
                    next: '›',
                    last: 'Akhir'
                }
            },
            columnDefs: [
                // Terapkan nowrap default
                {
                    targets: '_all',
                    className: 'dt-nowrap'
                },

                // Kolom No
                {
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    responsivePriority: 1
                },

                // Kolom Status (biarkan bisa diurut & dicari sesuai teks badge)
                // Tambahkan center jika mau: className: 'text-center'
                {
                    targets: 3,
                    orderable: true,
                    searchable: true
                },

                // Kolom Aksi (indeks 4)
                {
                    targets: 4,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    responsivePriority: 1
                }
            ],
            // Urut default: Kode asc
            order: [
                [1, 'asc']
            ],
            drawCallback: function() {
                const api = this.api();
                const info = api.page.info();
                api.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = (info.start + i + 1) + '.';
                });
            }
        });

        dt.on('init', () => {
            dt.columns.adjust();
            dt.responsive.recalc();
        });

        // Debounce pencarian DataTables bawaan
        const filterInput = dt.container().querySelector('input[type="search"]');
        if (filterInput) {
            const debounce = (fn, d = 350) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(null, a), d);
                };
            };
            filterInput.addEventListener('input', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        }
    });

    // Konfirmasi hapus
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
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?= base_url('operator/matpel/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>

<?= $this->endSection() ?>