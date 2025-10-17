<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<style>
    /* nowrap ke semua sel jika diperlukan */
    table.dataTable td.dt-nowrap,
    table.dataTable th.dt-nowrap {
        white-space: nowrap;
    }

    /* Zebra rows + hover */
    #tableDataNilai tbody tr:nth-child(odd) {
        background: #f8fafc;
    }

    /* ganjil */
    #tableDataNilai tbody tr:nth-child(even) {
        background: #ffffff;
    }

    /* genap  */
    #tableDataNilai tbody tr:hover {
        background: #eef6ff;
        transition: background .15s ease;
    }

    /* Chip gender */
    .gender-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: .75rem;
        border: 1px solid transparent;
        line-height: 1;
    }

    .gender-male {
        color: #0d6efd;
        background: #e7f1ff;
        border-color: #cfe2ff;
        /* biru lembut */
    }

    .gender-female {
        color: #dc3545;
        background: #fdecef;
        border-color: #f5c2c7;
        /* merah lembut */
    }

    .gender-unknown {
        color: #6c757d;
        background: #f2f4f6;
        border-color: #e5e7eb;
    }

    /* Kolom nomor tipis & rata tengah */
    #tableDataNilai th.w-40px,
    #tableDataNilai td.w-40px {
        width: 40px;
        text-align: center;
    }

    /* Zebra & hover */
    #tableDataNilai.table-hover tbody tr:hover {
        background: #eef6ff;
        transition: background .15s ease;
    }

    #tableDataNilai tbody tr:nth-child(odd) {
        background: #fbfdff;
    }

    #tableDataNilai tbody tr:nth-child(even) {
        background: #ffffff;
    }

    /* Badge nomor ganjil-genap */
    .gg-badge {
        display: inline-block;
        min-width: 22px;
        padding: .15rem .5rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        line-height: 1;
        border: 1px solid transparent;
        text-align: center;
        user-select: none;
    }

    .gg-odd {
        color: #5b21b6;
        background: #f3e8ff;
        border-color: #e9d5ff;
    }

    /* violet lembut */
    .gg-even {
        color: #115e59;
        background: #ccfbf1;
        border-color: #99f6e4;
    }

    /* teal lembut   */

    /* Chip gender */
    .gender-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: .75rem;
        border: 1px solid transparent;
        line-height: 1;
    }

    .gender-male {
        color: #0d6efd;
        background: #e7f1ff;
        border-color: #cfe2ff;
    }

    .gender-female {
        color: #dc3545;
        background: #fdecef;
        border-color: #f5c2c7;
    }

    .gender-unknown {
        color: #6c757d;
        background: #f2f4f6;
        border-color: #e5e7eb;
    }

    /* Kolom nomor sempit & center */
    #tableDataNilai th.w-40px,
    #tableDataNilai td.w-40px {
        width: 40px;
        text-align: center;
    }

    /* Chip gender */
    .gender-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: .75rem;
        border: 1px solid transparent;
        line-height: 1;
    }

    .gender-male {
        color: #0d6efd;
        background: #e7f1ff;
        border-color: #cfe2ff;
    }

    .gender-female {
        color: #dc3545;
        background: #fdecef;
        border-color: #f5c2c7;
    }

    .gender-unknown {
        color: #6c757d;
        background: #f2f4f6;
        border-color: #e5e7eb;
    }
</style>
<div class="container-fluid px-4 page-section fade-in-up delay-300">
    <div class="d-sm-flex align-items-center justify-content-between gap-2 gap-sm-3 mb-3 flex-wrap">
        <div class="flex-grow-1">
            <h1 class="mt-2 mt-sm-4 page-title mb-1"><?= esc($sub_judul ?? 'Laporan') ?></h1>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-modern mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('/') ?>">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= esc($sub_judul ?? 'Laporan') ?>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Aksi kanan -->
        <?php if (!empty($d_nilai)): ?>
            <div class="ms-sm-3 w-100 w-sm-auto text-start text-sm-end">
                <a href="<?= base_url('operator/laporan/tambah-nilai') ?>"
                    class="btn btn-gradient rounded-pill btn-sm py-2 px-3 w-100 w-sm-auto">
                    <i class="fa-solid fa-file-circle-plus me-2" aria-hidden="true"></i>
                    <span>Tambah</span>
                </a>
            </div>
        <?php endif; ?>
    </div>


    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" role="search" class="row g-2 align-items-center">
                        <!-- Cari nama / NISN -->
                        <div class="col-12 col-md-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input id="searchSiswa" type="text" name="q" value="<?= esc($q ?? '') ?>"
                                    class="form-control" placeholder="Cari nama atau NISN..." autocomplete="off">
                            </div>
                        </div>

                        <!-- Tahun Ajaran -->
                        <div class="col-12 col-md-3">
                            <select id="filterTA" name="tahunajaran" class="form-select form-select-sm">
                                <option value="">Semua Tahun Ajaran</option>
                                <?php foreach (($listTA ?? []) as $ta): ?>
                                    <option value="<?= esc($ta['id_tahun_ajaran']) ?>"
                                        <?= (string)($tahunajaran ?? '') === (string)$ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                                        <?= esc($ta['tahun']) ?> - Semester <?= esc(ucfirst($ta['semester'])) ?><?= !empty($ta['is_active']) ? ' (Aktif)' : '' ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <!-- NEW: Kelas -->
                        <div class="col-12 col-md-3">
                            <select id="filterKelas" name="kelas" class="form-select form-select-sm">
                                <option value="">Semua Kelas</option>
                                <?php foreach (($listKelas ?? []) as $kls):
                                    $idk  = (int)($kls['id_kelas'] ?? 0);
                                    $nm   = (string)($kls['nama_kelas'] ?? '');
                                    $tgkt = (string)($kls['tingkat'] ?? '');
                                    $label = trim($nm !== '' ? $nm : ('Tingkat ' . $tgkt));
                                ?>
                                    <option value="<?= esc($idk) ?>" <?= (string)($kelas ?? '') === (string)$idk ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Kategori nilai (UTS/UAS) -->
                        <div class="col-12 col-md-2">
                            <?php $kat = strtoupper((string)($kategori ?? '')); ?>
                            <select id="filterKat" name="kategori" class="form-select form-select-sm">
                                <option value="" <?= $kat === '' ? 'selected' : '' ?>>Semua</option>
                                <?php foreach (($listKat ?? []) as $k):
                                    $kode = strtoupper($k['kode'] ?? '');
                                    $nama = trim((string)($k['nama'] ?? ''));
                                ?>
                                    <option value="<?= esc($kode) ?>" <?= $kat === $kode ? 'selected' : '' ?>>
                                        <?= esc($nama !== '' ? $nama : $kode) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <!-- Mapel -->
                        <div class="col-12 col-md-2">
                            <select id="filterMapel" name="mapel" class="form-select form-select-sm">
                                <option value="">Semua Mapel</option>
                                <?php foreach (($listMapel ?? []) as $m): ?>
                                    <option value="<?= esc($m['id_mapel']) ?>"
                                        <?= (string)($mapel ?? '') === (string)$m['id_mapel'] ? 'selected' : '' ?>>
                                        <?= esc($m['nama']) ?><?= !empty($m['kode']) ? ' [' . esc($m['kode']) . ']' : '' ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </form>

                    <script>
                        // Auto-submit saat dropdown berubah
                        ['filterTA', 'filterKelas', 'filterKat', 'filterMapel'].forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.addEventListener('change', () => document.getElementById('filterForm').submit());
                        });
                    </script>

                </div>
            </div>


            <!-- Tabel -->
            <?php if (!empty($d_nilai)): ?>
                <div class="table-responsive">
                    <table id="tableDataNilai" class="table table-modern table-hover align-middle text-capitalize">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Gender</th>
                                <th>Tahun Ajaran</th>
                                <th>Kelas</th>
                                <th>Mapel</th>
                                <th>Semester</th>
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
                            foreach ($d_nilai as $row):
                                $nisn       = (string)($row['nisn'] ?? $row['siswa_nisn'] ?? '');
                                $nama       = (string)($row['full_name'] ?? $row['siswa_nama'] ?? $row['nama_lengkap'] ?? '');
                                $genderRaw  = (string)($row['gender'] ?? $row['siswa_gender'] ?? '');
                                $gender     = $genderRaw !== '' ? strtoupper($genderRaw[0]) : ''; // L/P/''

                                $taTahun    = (string)($row['tahun_ajaran'] ?? $row['ta_tahun'] ?? '');
                                $taSmtr     = (string)($row['semester'] ?? $row['ta_semester'] ?? '');

                                // === KELAS (dari tb_siswa.kelas_id -> join tb_kelas) ===
                                $kelasNama     = (string)($row['kelas_nama'] ?? '');
                                $kelasTingkat  = isset($row['kelas_tingkat']) ? (string)$row['kelas_tingkat'] : '';
                                $kelasTampil   = $kelasNama !== '' ? $kelasNama : ($kelasTingkat !== '' ? 'Tingkat ' . $kelasTingkat : '—');

                                $mapelNama  = (string)($row['nama'] ?? $row['mapel_nama'] ?? '');
                                $katKode    = (string)($row['kategori_kode'] ?? '');
                                $skor       = (string)($row['skor'] ?? '');
                                $ket        = (string)($row['keterangan'] ?? $row['keterangan_nilai'] ?? '');

                                $tglRaw     = $row['tanggal_nilai'] ?? $row['tanggal'] ?? null;
                                $tglTampil  = '—';
                                if (!empty($tglRaw) && $tglRaw !== '0000-00-00' && $tglRaw !== '0000-00-00 00:00:00') {
                                    $ts = strtotime($tglRaw);
                                    if ($ts !== false) $tglTampil = date('d/m/Y', $ts);
                                }

                                $idNilai = (int)($row['id_nilai'] ?? 0);
                                $qs = http_build_query([
                                    'q'           => $q           ?? '',
                                    'tahunajaran' => $tahunajaran ?? '',
                                    'kategori'    => $kategori    ?? '',
                                    'mapel'       => $mapel       ?? '',
                                ]);
                                $hrefEdit = base_url('operator/laporan/edit-nilai/' . $idNilai) . ($qs ? ('?' . $qs) : '');
                            ?>
                                <tr>
                                    <!-- No -->
                                    <td class="w-40px text-center">
                                        <span class="<?= ($no % 2 ? 'btn btn-sm btn-outline-secondary' : 'btn btn-sm btn-outline-primary') ?> rounded-pill px-2 py-0">
                                            <?= $no ?>
                                        </span>
                                    </td>

                                    <td class="cell-nowrap"><span class="font-monospace"><?= esc($nisn) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>

                                    <!-- Gender chip -->
                                    <td class="cell-nowrap">
                                        <?php if ($gender === 'L'): ?>
                                            <span class="gender-chip gender-male" title="Laki-laki">
                                                <i class="fa-solid fa-person" aria-hidden="true"></i> Laki-laki
                                            </span>
                                        <?php elseif ($gender === 'P'): ?>
                                            <span class="gender-chip gender-female" title="Perempuan">
                                                <i class="fa-solid fa-person-dress" aria-hidden="true"></i> Perempuan
                                            </span>
                                        <?php else: ?>
                                            <span class="gender-chip gender-unknown" title="Tidak diketahui">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Urutan sesuai header -->
                                    <td class="cell-nowrap"><?= esc($taTahun) ?></td>
                                    <td class="cell-nowrap"><?= esc($kelasTampil) ?></td>
                                    <td><?= esc($mapelNama) ?></td>
                                    <td class="cell-nowrap"><?= esc($taSmtr) ?></td>
                                    <td class="cell-nowrap"><?= esc($katKode) ?></td>
                                    <td class="cell-nowrap"><?= esc($skor) ?></td>
                                    <td class="cell-nowrap"><?= esc(format_ddmmyyyy_ke_tanggal_indo($tglTampil)) ?></td>
                                    <td><?= esc($ket) ?></td>

                                    <td class="cell-nowrap">
                                        <a href="<?= $hrefEdit ?>" class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="#"
                                            class="btn btn-outline-danger btn-sm"
                                            title="Hapus baris nilai ini"
                                            onclick="confirmDeleteLapSiswa(<?= $idNilai ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
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
        const inpQ = document.getElementById('searchSiswa');
        const selects = ['filterGender', 'filterTA', 'filterKat', 'filterMapel']
            .map(id => document.getElementById(id)).filter(Boolean);

        let t;
        const submitNow = () => form.requestSubmit ? form.requestSubmit() : form.submit();

        if (inpQ) {
            inpQ.addEventListener('input', () => {
                clearTimeout(t);
                t = setTimeout(submitNow, 350);
            });
            inpQ.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitNow();
                }
            });
        }
        selects.forEach(sel => sel.addEventListener('change', submitNow));
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