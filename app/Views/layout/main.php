<!DOCTYPE html>
<html lang="en">

<head>
    <!-- header -->
    <?= $this->include('layout/header') ?>
</head>

<body class="sb-nav-fixed">
    <?php
    $s = session();
    $flashSuccess = $s->getFlashdata('sweet_success');
    $flashError   = $s->getFlashdata('sweet_error');
    $flashWarn    = $s->getFlashdata('flash_logout');
    ?>

    <!-- navbar -->
    <?= $this->include('layout/navbar') ?>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <!-- sidebar -->
            <?= $this->include('layout/sidebar') ?>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <?= $this->renderSection('content') ?>
            </main>
            <!-- footer -->
            <?= $this->include('layout/footer') ?>

            <style>
                .modern-logout .modal-dialog-zoom {
                    transform: translateY(0) scale(.98);
                }

                .modern-logout.show .modal-dialog-zoom {
                    transform: translateY(0) scale(1);
                    transition: transform .15s ease;
                }

                .modern-logout .modal-content {
                    border-radius: 18px;
                    background: #fff;
                }

                .modern-logout .logout-icon {
                    width: 38px;
                    height: 38px;
                    color: #fff;
                    background: linear-gradient(135deg, #0d6efd 0%, #3d8bfd 100%);
                    box-shadow: 0 8px 20px rgba(13, 110, 253, .25), inset 0 1px rgba(255, 255, 255, .35);
                }

                .modern-logout .btn-primary {
                    background-color: #0d6efd;
                    border-color: #0d6efd;
                }

                .modern-logout .btn-primary:hover {
                    filter: brightness(1.05);
                }

                .modern-logout .btn-outline-secondary {
                    border-color: #e9ecef;
                }

                .modern-logout .btn-outline-secondary:hover {
                    background: #f8f9fa;
                }
            </style>

            <!-- Modal Logout -->
            <div class="modal fade modern-logout" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm modal-dialog-zoom">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="logoutModalLabel">
                                <span class="logout-icon rounded-3 d-inline-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </span>
                                Konfirmasi Logout
                            </h5>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body pt-2">
                            <p class="mb-1">Yakin ingin keluar dari sesi ini?</p>
                            <div class="text-muted small">Anda dapat masuk kembali kapan saja.</div>
                        </div>

                        <div class="modal-footer border-0 pt-0 d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>

                            <!-- Logout via POST -->
                            <form id="logoutForm" action="<?= site_url('auth/logout') ?>" method="post" class="m-0">
                                <?= csrf_field() ?>
                                <button type="submit" id="btnLogout" class="btn btn-primary rounded-pill px-3">
                                    <span class="btn-text"><i class="fa-solid fa-power-off me-2"></i> Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- /Modal -->
        </div>
    </div>

    <!-- === JS RINGAN & KONDISIONAL === -->

    <!-- SweetAlert & Bootstrap (defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- (opsional) script lokal kamu, dibuat non-blocking -->
    <script defer src="<?= base_url('assets/js/scripts.js') ?>"></script>

    <!-- (Opsional) jQuery: boleh ada meski kita pakai API vanilla -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <!-- DataTables core + Bootstrap 5 adapter -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>

    <!-- ===== Chart.js ===== -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
        integrity="sha384-2gJ3cQzYwU5QXqv+g0h0mXzB1q2n8c3e0n8j2c+Kq+fKk8bC+3o6s6p0kF1Xo1wS"
        crossorigin="anonymous"></script>

    <?= $this->renderSection('scripts') ?>
    <script>
        // ===== Util loader dinamis =====
        function loadScript(src, {
            defer = true,
            async = false,
            id
        } = {}) {
            return new Promise((resolve, reject) => {
                if (id && document.getElementById(id)) return resolve();
                const s = document.createElement('script');
                s.src = src;
                s.defer = defer;
                s.async = async;
                if (id) s.id = id;
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            });
        }

        // ===== Toast Flash =====
        document.addEventListener('DOMContentLoaded', () => {
            const msgSuccess = <?= json_encode($flashSuccess) ?>;
            const msgError = <?= json_encode($flashError) ?>;
            const msgWarn = <?= json_encode($flashWarn) ?>;
            if (!msgSuccess && !msgError && !msgWarn) return;

            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: t => {
                    t.onmouseenter = Swal.stopTimer;
                    t.onmouseleave = Swal.resumeTimer;
                }
            });
            if (msgSuccess) Toast.fire({
                icon: "success",
                title: msgSuccess
            });
            if (msgError) Toast.fire({
                icon: "error",
                title: msgError
            });
            if (msgWarn) Toast.fire({
                icon: "warning",
                title: msgWarn
            });
        });

        // ===== Spinner tombol Logout =====
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('logoutForm');
            const btn = document.getElementById('btnLogout');
            const txt = btn?.querySelector('.btn-text');
            form?.addEventListener('submit', () => {
                btn.disabled = true;
                if (txt) txt.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Logout...';
            });
        });

        // ===== Buka submenu aktif (sidebar) =====
        document.addEventListener('DOMContentLoaded', () => {
            const activeLink = document.querySelector(".sb-sidenav .nav-link.active");
            const collapseParent = activeLink?.closest(".collapse");
            if (collapseParent) {
                collapseParent.classList.add("show");
                const trigger = document.querySelector('[data-bs-target="#' + collapseParent.id + '"]');
                if (trigger) {
                    trigger.classList.remove("collapsed");
                    trigger.setAttribute("aria-expanded", "true");
                }
            }
        });

        // =========================
        //  CHART.JS (kondisional)
        // =========================
        document.addEventListener('DOMContentLoaded', async () => {
            const needChart = document.querySelector('canvas[data-chart]');
            if (!needChart) return;
            try {
                await loadScript("https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js", {
                    id: "chartjs-4"
                });
                // fallback lokal kalau CDN gagal
                window.addEventListener('error', (e) => {
                    if (e.target?.tagName === 'SCRIPT' && e.target.src.includes('chart.umd.min.js')) {
                        loadScript("<?= base_url('assets/vendor/chart.js/chart.umd.min.js') ?>", {
                            id: "chartjs-local"
                        });
                    }
                }, true);
                // TODO: inisialisasi chart kamu (jika ada)
            } catch (e) {
                console.warn('Chart.js gagal dimuat:', e);
            }
        });

        // =========================================
        //  DATATABLES (kondisional, tanpa jQuery)
        // =========================================
        const dtSelectors = [
            '#tableDataSiswa', '#tableDataGuru', '#tableDataUser',
            '#tableDataPenugasan', '#tableDataSiswaLaporan',
            '#tableDataGuruLaporan', '#tableDataNilai', '#tableDataGuruSiswa', '#tableDataMatpel'
        ];

        async function ensureDataTables() {
            const core = "https://cdn.datatables.net/2.1.6/js/dataTables.min.js";
            const bs5 = "https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap5.min.js";
            const resp = "https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js";
            const respBs5 = "https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js";
            const btn = "https://cdn.datatables.net/buttons/3.1.1/js/dataTables.buttons.min.js";
            const btnBs5 = "https://cdn.datatables.net/buttons/3.1.1/js/buttons.bootstrap5.min.js";
            const jszip = "https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js";
            const btnHtml5 = "https://cdn.datatables.net/buttons/3.1.1/js/buttons.html5.min.js";

            await loadScript(core, {
                id: 'dt-core'
            });
            await loadScript(bs5, {
                id: 'dt-bs5'
            });
            await loadScript(resp, {
                id: 'dt-resp'
            });
            await loadScript(respBs5, {
                id: 'dt-resp-bs5'
            });
            await loadScript(btn, {
                id: 'dt-btn'
            });
            await loadScript(btnBs5, {
                id: 'dt-btn-bs5'
            });
            await loadScript(jszip, {
                id: 'dt-jszip'
            });
            await loadScript(btnHtml5, {
                id: 'dt-btn-html5'
            });
        }

        function initDT(id, options, excelColumns) {
            const el = document.querySelector(id);
            if (!el) return;
            const buttons = [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                className: 'btn btn-success rounded-pill',
                title: (options?.titleExport ?? 'Laporan'),
                filename: () => `${options?.filenameExport ?? 'Laporan'}_${new Date().toISOString().slice(0,10)}`,
                exportOptions: {
                    columns: excelColumns,
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
                                return (tmp.textContent || '').trim();
                            }
                            return data ?? '';
                        }
                    }
                }
            }];

            const dt = new DataTable(el, {
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons,
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
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
                },
                ...options
            });

            dt.on('draw', () => {
                const api = dt;
                const info = api.page.info();
                api.column(0, {
                    page: 'current'
                }).nodes().each((cell, i) => {
                    cell.innerHTML = (info.start + i + 1) + '.';
                });
            });

            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
                el.querySelectorAll('img').forEach(img =>
                    img.addEventListener('load', () => dt.columns.adjust().responsive.recalc())
                );
                const wrapper = el.closest('.dataTable-wrapper');
                const filter = wrapper?.querySelector('input[type=search]') ||
                    document.querySelector(`${id}_filter input[type=search]`);
                if (filter) {
                    let t;
                    filter.addEventListener('input', function() {
                        clearTimeout(t);
                        const v = this.value;
                        t = setTimeout(() => dt.search(v).draw(), 350);
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const needDT = dtSelectors.some(sel => document.querySelector(sel));
            if (!needDT) return;

            try {
                await ensureDataTables();

                // Inisialisasi per tabel (ringkas)
                initDT('#tableDataSiswa', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: [0, 1, 5],
                                orderable: false,
                                searchable: false
                            },
                            {
                                targets: 2,
                                responsivePriority: 2
                            },
                            {
                                targets: 3,
                                responsivePriority: 1
                            },
                            {
                                targets: 4,
                                responsivePriority: 3
                            }
                        ],
                        order: [
                            [2, 'asc']
                        ],
                        titleExport: 'Laporan_Siswa',
                        filenameExport: 'Laporan_Siswa'
                    },
                    [0, 2, 3, 4]
                );

                initDT('#tableDataGuru', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: [0, 1, 5],
                                orderable: false,
                                searchable: false
                            }
                        ],
                        order: [
                            [2, 'asc']
                        ],
                        titleExport: 'Laporan_Guru',
                        filenameExport: 'Laporan_Guru'
                    },
                    [0, 2, 3, 4]
                );

                initDT('#tableDataUser', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: [0, 1, 6],
                                orderable: false,
                                searchable: false
                            }
                        ],
                        order: [
                            [2, 'asc']
                        ],
                        titleExport: 'Laporan_User',
                        filenameExport: 'Laporan_User'
                    },
                    [0, 2, 3, 4, 5]
                );

                initDT('#tableDataPenugasan', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: [0, 7],
                                orderable: false,
                                searchable: false
                            },
                            {
                                targets: 5,
                                type: 'num'
                            }
                        ],
                        order: [
                            [1, 'asc'],
                            [4, 'desc']
                        ],
                        titleExport: 'Laporan_Penugasan',
                        filenameExport: 'Laporan_Penugasan'
                    },
                    [0, 1, 2, 3, 4, 5, 6]
                );

                initDT('#tableDataSiswaLaporan', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: 0,
                                orderable: false,
                                searchable: false
                            },
                            {
                                targets: [5, 6],
                                type: 'string'
                            }
                        ],
                        order: [
                            [2, 'asc']
                        ],
                        titleExport: 'Laporan_Siswa',
                        filenameExport: 'Laporan_Siswa'
                    },
                    [0, 1, 2, 3, 4, 5, 6]
                );

                initDT('#tableDataGuruLaporan', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: 0,
                                orderable: false,
                                searchable: false
                            },
                            {
                                targets: 7,
                                orderable: false,
                                searchable: false,
                                className: 'text-end'
                            },
                            {
                                targets: [5, 6],
                                type: 'string'
                            }
                        ],
                        order: [
                            [2, 'asc']
                        ],
                        titleExport: 'Laporan_Guru',
                        filenameExport: 'Laporan_Guru'
                    },
                    [0, 1, 2, 3, 4, 5, 6]
                );

                initDT('#tableDataNilai', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: 0,
                                orderable: false,
                                searchable: false
                            },
                            {
                                targets: 9,
                                type: 'string'
                            },
                            {
                                targets: 2,
                                responsivePriority: 1
                            },
                            {
                                targets: 1,
                                responsivePriority: 2
                            }
                        ],
                        order: [
                            [2, 'asc'],
                            [6, 'asc'],
                            [7, 'asc'],
                            [9, 'asc']
                        ],
                        titleExport: 'Laporan_Nilai_Siswa',
                        filenameExport: 'Laporan_Nilai_Siswa'
                    },
                    Array.from({
                        length: 11
                    }, (_, i) => i)
                );

                initDT('#tableDataGuruSiswa', {
                        columnDefs: [{
                                targets: '_all',
                                className: 'dt-nowrap'
                            },
                            {
                                targets: 0,
                                orderable: false,
                                searchable: false,
                                responsivePriority: 6
                            },
                            {
                                targets: 1,
                                orderable: false,
                                searchable: false,
                                responsivePriority: 5
                            },
                            {
                                targets: 2,
                                responsivePriority: 2
                            },
                            {
                                targets: 3,
                                responsivePriority: 1
                            },
                            {
                                targets: 4,
                                responsivePriority: 3
                            },
                            {
                                targets: 5,
                                responsivePriority: 4
                            }
                        ],
                        order: [
                            [3, 'asc']
                        ],
                        titleExport: 'Data_Guru',
                        filenameExport: 'Data_Guru'
                    },
                    [0, 2, 3, 4, 5]
                );
                initDT('#tableDataMatpel', {
                    columnDefs: [{
                            targets: '_all',
                            className: 'dt-nowrap'
                        },
                        {
                            targets: 0,
                            orderable: false,
                            searchable: false
                        }, // No
                        {
                            targets: 3,
                            orderable: false,
                            searchable: false,
                            className: 'text-end'
                        } // Aksi
                    ],
                    order: [
                        [2, 'asc']
                    ], // urut Nama Mata Pelajaran
                    titleExport: 'Data_Mata_Pelajaran',
                    filenameExport: 'Data_Mata_Pelajaran'
                }, [0, 1, 2]);
                // Tambahkan di blok inisialisasi DataTables kamu
                initDT('#tableDataTahunAjaran', {
                    columnDefs: [{
                            targets: '_all',
                            className: 'dt-nowrap'
                        },
                        {
                            targets: 0,
                            orderable: false,
                            searchable: false
                        }, // No
                        {
                            targets: 3, // Status (badge)
                            render: (data, type) => {
                                // Supaya saat export/print badge jadi teks polos
                                if (type === 'export' || type === 'print') {
                                    const tmp = document.createElement('div');
                                    tmp.innerHTML = data ?? '';
                                    return (tmp.textContent || '').trim();
                                }
                                return data;
                            }
                        },
                        {
                            targets: 4,
                            orderable: false,
                            searchable: false,
                            className: 'text-end'
                        } // Aksi
                    ],
                    // Urut default: Tahun DESC lalu Semester ASC
                    order: [
                        [2, 'desc'],
                        [1, 'asc']
                    ],
                    titleExport: 'Data_Tahun_Ajaran',
                    filenameExport: 'Data_Tahun_Ajaran'
                }, [0, 1, 2, 3]); // kolom diekspor (tanpa Aksi)


            } catch (e) {
                console.warn('DataTables gagal dimuat/inisialisasi:', e);
            }
        });

        // ===== Delete confirm (Swal) =====
        window.confirmDeleteSiswa = function(idOrNisn) {
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
                    window.location.href = "<?= base_url('operator/data-siswa/delete/') ?>" + encodeURIComponent(idOrNisn);
                }
            });
        };
    </script>
</body>

</html>