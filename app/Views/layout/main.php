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
        </div>
    </div>
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
                    filename: () => {
                        const d = new Date().toISOString().slice(0, 10);
                        return `Laporan_Siswa_${d}`;
                    },
                    exportOptions: {
                        // Kolom: 0=No, 1=Foto, 2=NISN, 3=Nama, 4=Gender, 5=Aksi
                        columns: [0, 2, 3, 4], // exclude Foto & Aksi
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        // Rapikan isi sel (hapus HTML badge, dsb.)
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
                scrollX: true, // kalau kolom mepet, biar ada scroll horizontal
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
                        targets: 5,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        responsivePriority: 1
                    } // Aksi
                ],
                order: [
                    [2, 'asc']
                ], // urut default pakai NISN (kolom index 2)

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

            // Optional: debounce search agar halus
            const $filter = $('#tableDataSiswa_filter input[type=search]');
            const debounce = (fn, d = 400) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), d);
                }
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
    </script>
</body>

</html>