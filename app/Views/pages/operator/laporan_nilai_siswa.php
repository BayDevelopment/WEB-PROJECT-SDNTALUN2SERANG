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
            <h1 class="mt-4 page-title"><?= esc($sub_judul) ?></h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" role="search" class="row g-2 align-items-center">
                        <!-- Cari nama / NISN -->
                        <div class="col-12 col-md-6">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                </span>
                                <input
                                    id="searchSiswa"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari nama atau NISN..."
                                    aria-label="Pencarian nama atau NISN"
                                    autocomplete="off">
                            </div>
                        </div>

                        <!-- Tahun Ajaran (id) -->
                        <div class="col-6 col-md-3">
                            <input
                                type="text"
                                name="tahunajaran"
                                value="<?= esc($tahunajaran ?? '') ?>"
                                class="form-control form-control-sm"
                                placeholder="Tahun Ajaran"
                                aria-label="Filter Tahun Ajaran (id)">
                        </div>

                        <!-- Kategori nilai (kode: UTS/UAS) -->
                        <div class="col-6 col-md-3">
                            <?php $kat = strtoupper((string)($kategori ?? '')); ?>
                            <select name="kategori" class="form-select form-select-sm" aria-label="Filter kategori nilai">
                                <option value="" <?= $kat === ''   ? 'selected' : '' ?>>Semua Kategori</option>
                                <option value="UTS" <?= $kat === 'UTS' ? 'selected' : '' ?>>UTS</option>
                                <option value="UAS" <?= $kat === 'UAS' ? 'selected' : '' ?>>UAS</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Aksi kanan -->
                <div class="col-12 col-md-3 text-md-end">
                    <?php if (!empty($d_nilai)): ?>
                        <a href="<?= base_url('operator/laporan/tambah-nilai') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-md-auto">
                            <i class="fa-solid fa-file-circle-plus me-2" aria-hidden="true"></i> Tambah
                        </a>
                    <?php endif; ?>
                </div>
            </div>


            <!-- Tabel -->
            <?php if (!empty($d_nilai)): ?>
                <div class="table-responsive">
                    <table id="tableDataNilai" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Gender</th>
                                <th>Tahun Ajaran</th>
                                <th>Semester</th>
                                <th>Mapel</th>
                                <th>Kategori</th>
                                <th>Skor</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;
                            $fmtDMY = function ($val): string {
                                $t = _indo_parse_time($val, 'Asia/Jakarta'); // helper milikmu
                                return $t ? $t->format('d/m/Y') : '—';
                            };

                            foreach ($d_nilai as $row):
                                $nisn       = (string)($row['nisn'] ?? '');
                                $nama       = (string)($row['full_name'] ?? $row['nama_lengkap'] ?? '');
                                $genderRaw  = (string)($row['gender'] ?? '');
                                $gender     = $genderRaw !== '' ? $genderRaw : '—';

                                $taTahun    = (string)($row['tahun_ajaran'] ?? '');   // ta.tahun AS tahun_ajaran
                                $taSmtr     = (string)($row['semester'] ?? '');
                                $mapelNama  = (string)($row['nama'] ?? '');           // m.nama
                                $katKode    = (string)($row['kategori_kode'] ?? '');  // k.kode
                                $skor       = (string)($row['skor'] ?? '');
                                $tgl        = $fmtDMY($row['tanggal'] ?? null);
                                $ket        = (string)($row['keterangan'] ?? '');
                            ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td><span class="font-monospace"><?= esc($nisn) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td><?= esc($gender) ?></td>
                                    <td><?= esc($taTahun) ?></td>
                                    <td><?= esc($taSmtr) ?></td>
                                    <td><?= esc($mapelNama) ?></td>
                                    <td><?= esc($katKode) ?></td>
                                    <td><?= esc($skor) ?></td>
                                    <td><?= esc(tgl_indo($tgl, true)) ?></td>
                                    <td><?= esc($ket) ?></td>
                                    <td>
                                        <?php
                                        $qs = http_build_query([
                                            'q'           => $q           ?? '',
                                            'tahunajaran' => $tahunajaran ?? '',
                                            'kategori'    => $kategori    ?? '',
                                            'mapel'       => $mapel       ?? '',
                                        ]);
                                        $hrefEdit = base_url('operator/laporan/edit-nilai/' . (int)($row['id_nilai'] ?? 0)) . ($qs ? ('?' . $qs) : '');
                                        ?>
                                        <a href="<?= $hrefEdit ?>" class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <?php $idNilai = (int)($row['id_nilai'] ?? 0); ?>
                                        <a href="#"
                                            class="btn btn-outline-danger btn-sm"
                                            title="Hapus baris nilai ini"
                                            onclick="confirmDeleteLapSiswa(<?= $idNilai ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
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
                    <h5 class="mb-1">Belum ada data nilai</h5>
                    <p class="text-muted mb-3">Silakan atur filter atau tambahkan data nilai pada menu terkait.</p>
                    <a href="<?= base_url('operator/laporan/tambah-nilai') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('filterForm');
        if (!form) return;

        const inpQ = document.getElementById('searchSiswa'); // q
        const selects = form.querySelectorAll('select'); // contoh: kategori
        const inputs = form.querySelectorAll('input[type="text"], input[type="number"]'); // tahunajaran, mapel, dsb.

        let typingTimer = null;
        let isSubmitting = false;

        const submitFormSafe = () => {
            if (isSubmitting) return;
            isSubmitting = true;
            // requestSubmit lebih aman (respect type="submit"), fallback ke submit()
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        };

        const debounce = (fn, delay = 350) => {
            return (...args) => {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => fn.apply(null, args), delay);
            };
        };

        // 1) Debounce saat mengetik di kolom pencarian
        if (inpQ) {
            inpQ.addEventListener('input', debounce(() => submitFormSafe(), 350));
            // Enter = submit instan (tanpa debounce)
            inpQ.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitFormSafe();
                }
            });
        }

        // 2) Submit otomatis saat select berubah (mis. kategori)
        selects.forEach((sel) => {
            sel.addEventListener('change', () => submitFormSafe(), {
                passive: true
            });
        });

        // 3) Untuk input filter selain pencarian (ID TA, ID Mapel), pakai debounce ringan
        inputs.forEach((inp) => {
            if (inp === inpQ) return; // sudah di-handle di atas
            // Ketik -> debounce submit; blur -> submit instan
            inp.addEventListener('input', debounce(() => submitFormSafe(), 500));
            inp.addEventListener('change', () => submitFormSafe(), {
                passive: true
            });
            inp.addEventListener('blur', () => submitFormSafe(), {
                passive: true
            });
        });

        // 4) Cegah submit ganda: reset flag saat halaman benar-benar mulai unload
        window.addEventListener('beforeunload', () => {
            clearTimeout(typingTimer);
            isSubmitting = false;
        }, {
            passive: true
        });
    });


    let _deletingLapSiswa = false;

    function confirmDeleteLapSiswa(idOrNisn) {
        if (_deletingLapSiswa) return;

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
                _deletingLapSiswa = true;

                const base = "<?= base_url('operator/laporan/nilai-siswa/delete/') ?>";
                // bawa filter aktif agar controller bisa memakainya saat redirect balik
                const qs = new URLSearchParams({
                    q: "<?= esc($q ?? '') ?>",
                    tahunajaran: "<?= esc($tahunajaran ?? '') ?>",
                    kategori: "<?= esc($kategori ?? '') ?>",
                    mapel: "<?= esc($mapel ?? '') ?>"
                });

                const url = base + encodeURIComponent(String(idOrNisn)) + (qs.toString() ? ('?' + qs.toString()) : '');
                window.location.href = url;
            }
        });
    }
</script>
<?= $this->endSection() ?>