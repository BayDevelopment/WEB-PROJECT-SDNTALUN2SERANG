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
            <!-- Modal Logout (letakkan di layout/main, sebelum </body>) -->
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

                            <!-- Disarankan: logout via POST -->
                            <form id="logoutForm" action="<?= site_url('auth/logout') ?>" method="post" class="m-0">
                                <?= csrf_field() ?>
                                <button type="submit" id="btnLogout" class="btn btn-primary rounded-pill px-3">
                                    <span class="btn-text"><i class="fa-solid fa-power-off me-2"></i> Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 1) MUAT Chart.js (pastikan tidak diblokir CSP / internet) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"
        integrity="sha384-7qvNohkz0bN+g8iV3f1n3lOk/4rX6w3r8Zf5zq3hFwxj4u8fHh8uU1n6Qv1b9p2y"
        crossorigin="anonymous" defer></script>

    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"></script>

    <!-- Buttons + Excel export -->
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.html5.min.js"></script>
    <script>
        // modal logout 
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

        // Fallback ke file lokal kalau CDN gagal (misal CSP / offline)
        window.addEventListener('error', function(e) {
            if (e.target && e.target.tagName === 'SCRIPT' && e.target.src.includes('chart.umd.min.js')) {
                var s = document.createElement('script');
                s.src = "<?= base_url('assets/vendor/chart.js/chart.umd.min.js') ?>";
                s.defer = true;
                document.head.appendChild(s);
            }
        }, true);

        // sweetalert js
        window.addEventListener('DOMContentLoaded', function() {
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

        // ul li active
        document.addEventListener("DOMContentLoaded", function() {
            // cari link yang punya class active
            var activeLink = document.querySelector(".sb-sidenav .nav-link.active");

            if (activeLink) {
                // cari parent collapse
                var collapseParent = activeLink.closest(".collapse");
                if (collapseParent) {
                    collapseParent.classList.add("show"); // buka submenu
                    // cari trigger (a.nav-link collapsed)
                    var trigger = document.querySelector('[data-bs-target="#' + collapseParent.id + '"]');
                    if (trigger) {
                        trigger.classList.remove("collapsed");
                        trigger.setAttribute("aria-expanded", "true");
                    }
                }
            }
        });

        // datatables siswa
        $(function() {
            const $table = $('#tableDataSiswa');

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_Siswa',
                    filename: () => `Laporan_Siswa_${new Date().toISOString().slice(0,10)}`,
                    exportOptions: {
                        // 0=No, 1=Foto, 2=NISN, 3=Nama, 4=Gender, 5=Aksi
                        columns: [0, 2, 3, 4], // exclude Foto & Aksi
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data) {
                                // element DOM → ambil text
                                if (data && data.nodeType === 1) return data.textContent.trim();
                                // string HTML → strip tag
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
                },

                // ===== Kolom & Perilaku =====
                columnDefs: [{
                        targets: '_all',
                        className: 'dt-nowrap'
                    },
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 6
                    }, // No
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 5
                    }, // Foto
                    {
                        targets: 2,
                        responsivePriority: 2
                    }, // NISN
                    {
                        targets: 3,
                        responsivePriority: 1
                    }, // Nama (paling penting)
                    {
                        targets: 4,
                        responsivePriority: 3
                    }, // Gender
                    {
                        targets: 5,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        responsivePriority: 1
                    } // Aksi
                ],
                order: [
                    [2, 'asc']
                ], // urut default: NISN

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

            // Recalc setelah init
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Jika gambar terlambat load, recalc responsive
            $table.find('img').on('load', () => dt.columns.adjust().responsive.recalc());

            // Debounce search (lebih halus)
            const $filter = $('#tableDataSiswa_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });

        // datatables guru
        $(function() {
            const $table = $('#tableDataGuru');

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_Guru',
                    filename: () => {
                        const d = new Date().toISOString().slice(0, 10);
                        return `Laporan_Guru_${d}`;
                    },
                    exportOptions: {
                        // Kolom: 0=No, 1=Foto, 2=NIP, 3=Nama, 4=JK, 5=Aksi
                        columns: [0, 2, 3, 4], // exclude Foto & Aksi
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data, row, column) {
                                // kalau data elemen DOM, ambil textContent
                                if (data && data.nodeType === 1) return data.textContent.trim();
                                // jika string HTML, singkirkan tag
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
                    } // collapse rapi di mobile
                },
                scrollX: true, // biar ada scroll horizontal kalau mepet
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

                // ===== Kolom & Perilaku =====
                columnDefs: [{
                        targets: '_all',
                        className: 'dt-nowrap'
                    }, // nowrap semua kolom
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1
                    }, // No
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 2
                    }, // Foto
                    {
                        targets: 5,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        responsivePriority: 1
                    } // Aksi
                ],
                order: [
                    [2, 'asc']
                ], // urut default by NIP (kolom index 2)

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

            // Recalculate kolom setelah init (untuk lebar kolom yang pas)
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Jika ada gambar yang terlambat load, recalibrate responsive
            $table.find('img').on('load', () => dt.columns.adjust().responsive.recalc());

            // Debounce search agar halus
            const $filter = $('#tableDataGuru_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });

        // datatbles user
        $(function() {
            const $table = $('#tableDataUser');

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_User',
                    filename: () => {
                        const d = new Date().toISOString().slice(0, 10);
                        return `Laporan_User_${d}`;
                    },
                    exportOptions: {
                        // Kolom: 0=No, 1=Foto, 2=Username, 3=Email, 4=Role, 5=Status, 6=Aksi
                        columns: [0, 2, 3, 4, 5], // exclude Foto & Aksi
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data) {
                                // Kalau data elemen DOM, ambil textContent
                                if (data && data.nodeType === 1) return data.textContent.trim();
                                // Jika string HTML, singkirkan tag
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
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
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 2
                    }, // Foto
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        responsivePriority: 1
                    } // Aksi
                ],
                order: [
                    [2, 'asc']
                ], // urut default by Username (kolom 2)

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

            // Recalculate kolom setelah init (untuk lebar kolom yang pas)
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Jika ada gambar yang terlambat load, recalibrate responsive
            $table.find('img').on('load', () => dt.columns.adjust().responsive.recalc());

            // Debounce search agar halus
            const $filter = $('#tableDataUser_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });

        // Penugasan
        $(function() {
            const $table = $('#tableDataPenugasan');

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_Penugasan',
                    filename: () => {
                        const d = new Date().toISOString().slice(0, 10);
                        return `Laporan_Penugasan_${d}`;
                    },
                    exportOptions: {
                        // Kolom ekspor: 0..6 (exclude Aksi/7)
                        columns: [0, 1, 2, 3, 4, 5, 6],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data) {
                                // Element DOM -> ambil text
                                if (data && data.nodeType === 1) return data.textContent.trim();
                                // String HTML -> strip tag
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
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
                        targets: 7,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        responsivePriority: 1
                    }, // Aksi
                    {
                        targets: 5,
                        type: 'num'
                    } // Jam/Minggu numeric
                ],
                order: [
                    [1, 'asc'], // Guru
                    [4, 'desc'] // Tahun Ajaran
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

            // Recalc saat init
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Debounce search
            const $filter = $('#tableDataPenugasan_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return function() {
                    clearTimeout(t);
                    const ctx = this,
                        args = arguments;
                    t = setTimeout(() => fn.apply(ctx, args), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });

        // laporan siswa
        $(function() {
            const $table = $('#tableDataSiswaLaporan');

            if ($table.length === 0) return;

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_Siswa',
                    filename: () => `Laporan_Siswa_${new Date().toISOString().slice(0,10)}`,
                    exportOptions: {
                        // Kolom ekspor: 0..6 (semua kolom di tabel)
                        columns: [0, 1, 2, 3, 4, 5, 6],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data) {
                                // Element DOM -> ambil text
                                if (data && data.nodeType === 1) return data.textContent.trim();
                                // String HTML -> strip tag
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
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
                    // Kolom 5 & 6 = tanggal dd/mm/YYYY → jadikan type num untuk sort kasar (hari dulu). 
                    // Jika ingin sort akurat, gunakan plugin date-eu atau data-order di server.
                    {
                        targets: [5, 6],
                        type: 'string'
                    }
                ],

                // Urutan default: Nama (kolom 2) A-Z
                order: [
                    [2, 'asc']
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

            // Recalc saat init
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Debounce search bawaan datatables (input di header)
            const $filter = $('#tableDataSiswaLaporan_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return function() {
                    clearTimeout(t);
                    const ctx = this,
                        args = arguments;
                    t = setTimeout(() => fn.apply(ctx, args), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });

        // laporan guru 
        $(function() {
            const $table = $('#tableDataGuruLaporan');
            if ($table.length === 0) return;

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_Guru',
                    filename: () => `Laporan_Guru_${new Date().toISOString().slice(0,10)}`,
                    exportOptions: {
                        // Kolom ekspor: 0..6 (exclude kolom Aksi = 7)
                        columns: [0, 1, 2, 3, 4, 5, 6],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data) {
                                if (data && data.nodeType === 1) return data.textContent.trim(); // DOM element
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
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
                        targets: 7,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        responsivePriority: 1
                    }, // Aksi
                    // Tanggal (dd/mm/YYYY) → pakai string; jika perlu sort akurat, gunakan plugin date-eu
                    {
                        targets: [5, 6],
                        type: 'string'
                    }
                ],

                // Urutan default: Nama (kolom index 2) A-Z
                order: [
                    [2, 'asc']
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

            // Recalc saat init
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Debounce search bawaan datatables (input di header)
            const $filter = $('#tableDataGuruLaporan_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return function() {
                    clearTimeout(t);
                    const a = arguments,
                        c = this;
                    t = setTimeout(() => fn.apply(c, a), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });

        // lporan nilai
        $(function() {
            const $table = $('#tableDataNilai');
            if ($table.length === 0) return;

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Laporan_Nilai_Siswa',
                    filename: () => `Laporan_Nilai_Siswa_${new Date().toISOString().slice(0,10)}`,
                    exportOptions: {
                        // Ekspor semua kolom (0..10)
                        columns: Array.from({
                            length: 11
                        }, (_, i) => i),
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body(data) {
                                if (data && data.nodeType === 1) return data.textContent.trim(); // DOM
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
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
                    // Tanggal dd/mm/YYYY → string (kalau perlu, pakai plugin date-eu untuk sort akurat)
                    {
                        targets: 9,
                        type: 'string'
                    },
                    // Prioritas responsif: Nama paling penting, lalu NISN
                    {
                        targets: 2,
                        responsivePriority: 1
                    },
                    {
                        targets: 1,
                        responsivePriority: 2
                    }
                ],

                // Urutan default mirroring controller: Nama (2) → Mapel (6) → Kategori (7) → Tanggal (9)
                order: [
                    [2, 'asc'],
                    [6, 'asc'],
                    [7, 'asc'],
                    [9, 'asc']
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

            // Recalc saat init
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Debounce pencarian bawaan DataTables (input di header)
            const $filter = $('#tableDataNilai_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return function() {
                    clearTimeout(t);
                    const args = arguments,
                        ctx = this;
                    t = setTimeout(() => fn.apply(ctx, args), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });


        // delete
        function confirmDeleteSiswa(idOrNisn) {
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
                    window.location.href = "<?= base_url('operator/data-siswa/delete/') ?>" + encodeURIComponent(idOrNisn);
                }
            });
        }

        $(function() {
            const $table = $('#tableDataGuruSiswa');

            const dt = new DataTable($table[0], {
                // ===== UI =====
                dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i>Download Excel',
                    className: 'btn btn-success rounded-pill',
                    title: 'Data_Guru',
                    filename: () => `Data_Guru_${new Date().toISOString().slice(0,10)}`,
                    exportOptions: {
                        // 0=No, 1=Foto, 2=NIP, 3=Nama, 4=JK, 5=Status
                        columns: [0, 2, 3, 4, 5], // exclude Foto (1)
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function(data) {
                                // DOM element → ambil text
                                if (data && data.nodeType === 1) return data.textContent.trim();
                                // string HTML → strip tag
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
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.1.6/i18n/id.json"
                },

                // ===== Kolom & Prioritas =====
                columnDefs: [{
                        targets: '_all',
                        className: 'dt-nowrap'
                    },

                    // 0: No
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 6
                    },

                    // 1: Foto
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 5
                    },

                    // 2: NIP
                    {
                        targets: 2,
                        responsivePriority: 2
                    },

                    // 3: Nama Lengkap
                    {
                        targets: 3,
                        responsivePriority: 1
                    },

                    // 4: Jenis Kelamin
                    {
                        targets: 4,
                        responsivePriority: 3
                    },

                    // 5: Status
                    {
                        targets: 5,
                        responsivePriority: 4
                    }
                ],

                // Urut default: Nama (kolom 3) asc
                order: [
                    [3, 'asc']
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

            // Recalc setelah init
            dt.on('init', () => {
                dt.columns.adjust();
                dt.responsive.recalc();
            });

            // Jika gambar terlambat load, recalc responsive
            $table.find('img').on('load', () => dt.columns.adjust().responsive.recalc());

            // Debounce search bawaan DataTables (lebih halus)
            const $filter = $('#tableDataGuruSiswa_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), d);
                };
            };
            $filter.off('keyup.DT input.DT').on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 350));
        });
    </script>
</body>

</html>