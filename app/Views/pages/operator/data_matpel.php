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
                            <?php $no = 1; ?>
                            <?php foreach ($d_mapel as $m): ?>
                                <?php
                                $id   = (int)($m['id_mapel'] ?? 0);
                                $kode = (string)($m['kode_mapel'] ?? $m['kode'] ?? '-');
                                $nama = (string)($m['nama_mapel'] ?? $m['nama'] ?? '-');
                                ?>
                                <tr
                                    data-kode="<?= esc($kode) ?>"
                                    data-nama="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td class="font-monospace"><?= esc($kode) ?></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-outline-danger"
                                                onclick="confirmDeleteMatpel('<?= esc((string)$id, 'js') ?>')"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                            <a href="<?= base_url('operator/matpel/detail/' . urlencode((string)$id)) ?>"
                                                class="btn btn-outline-secondary" title="Detail">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('operator/matpel/edit/' . urlencode((string)$id)) ?>"
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

    document.addEventListener('DOMContentLoaded', function() {
        const tableEl = document.getElementById('tableDataMatpel');
        if (!tableEl) return;

        const dt = new DataTable(tableEl, {
            // ===== UI =====
            dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                className: 'btn btn-success rounded-pill',
                title: 'Laporan_Matpel',
                filename: () => {
                    const d = new Date().toISOString().slice(0, 10);
                    return `Laporan_Matpel_${d}`;
                },
                exportOptions: {
                    // Kolom: 0=No, 1=Kode, 2=Nama, 3=Aksi
                    columns: [0, 1, 2], // exclude Aksi
                    modifier: {
                        search: 'applied',
                        order: 'applied',
                        page: 'all'
                    },
                    format: {
                        body: function(data) {
                            if (data && data.nodeType === 1) return data.textContent.trim();
                            if (typeof data === 'string') {
                                const tmp = document.createElement('div');
                                tmp.innerHTML = data;
                                return tmp.textContent.trim();
                            }
                            return data ?? '';
                        }
                    }
                }
            }],

            // ===== Tabel =====
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

            // Tanpa fetch file bahasa (tidak pakai AJAX) — pakai teks lokal
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

            // ===== Kolom & Perilaku =====
            columnDefs: [{
                    targets: '_all',
                    className: 'dt-nowrap'
                }, // nowrap semua
                {
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    responsivePriority: 1
                }, // No
                {
                    targets: 3,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    responsivePriority: 1
                } // Aksi
            ],

            // Urut default by Kode (kolom 1)
            order: [
                [1, 'asc']
            ],

            // ===== Nomor urut dinamis =====
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

        // Recalculate setelah init (biar lebar kolom pas)
        dt.on('init', () => {
            dt.columns.adjust();
            dt.responsive.recalc();
        });

        // Debounce search bawaan (tanpa jQuery)
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
                // ganti route sesuai punyamu; encode biar aman
                window.location.href = "<?= base_url('operator/matpel/delete/') ?>" + encodeURIComponent(id);
            }
        });
    }
</script>
<?= $this->endSection() ?>