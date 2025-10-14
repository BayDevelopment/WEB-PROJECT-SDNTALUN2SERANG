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
        <div class="text-muted small mt-3 mt-sm-0">
            Total Guru: <strong><?= number_format($totalGuru ?? 0, 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Aktif: <strong class="text-success"><?= number_format($GuruAktif ?? 0, 0, ',', '.') ?></strong>
            &nbsp;|&nbsp; Nonaktif: <strong class="text-muted"><?= number_format($GuruNonAktif ?? 0, 0, ',', '.') ?></strong>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input
                                    id="searchGuru"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari nama atau NIP..."
                                    aria-label="Pencarian nama atau NIP"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <?php $g = $gender ?? ''; ?>
                            <select id="filterGender" name="gender" class="form-select form-select-sm" aria-label="Filter gender">
                                <option value="" <?= $g === '' ? 'selected' : '' ?>>Semua Gender</option>
                                <option value="L" <?= $g === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $g === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <select id="filterTA" name="tahunajaran" class="form-select form-select-sm" aria-label="Filter Tahun Ajaran">
                                <option value="">Semua Tahun Ajaran</option>
                                <?php foreach ($listTA as $ta): ?>
                                    <option value="<?= esc($ta['id_tahun_ajaran']) ?>"
                                        <?= ($tahunajaran ?? '') == $ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                                        <?= esc($ta['tahun']) ?> - Semester <?= esc(ucfirst($ta['semester'])) ?>
                                        <?= $ta['is_active'] ? ' (Aktif)' : '' ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="fa-solid fa-filter me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <?php if (!empty($d_guru)): ?>
                <div class="table-responsive">
                    <table id="tableDataGuruLaporan" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Status (Tahunan)</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableGuru">
                            <?php
                            $no = 1;

                            // Formatter simpel: terima 'YYYY-MM-DD' atau null/zero-date -> tampil '—'
                            $fmtDMY = function ($val): string {
                                if (empty($val) || $val === '0000-00-00' || $val === '0000-00-00 00:00:00') return '—';
                                $ts = strtotime($val);
                                return $ts ? date('d/m/Y', $ts) : '—';
                            };

                            foreach ($d_guru as $g):
                                $id   = (int)($g['id_guru_tahun'] ?? 0);
                                $nip  = (string)($g['nip'] ?? '');
                                $nama = (string)($g['nama_lengkap'] ?? '');

                                // Jenis kelamin → badge (robust)
                                $jkRaw   = (string)($g['jenis_kelamin'] ?? '');
                                $jkTrim  = trim($jkRaw);
                                $JK_UP   = mb_strtoupper($jkTrim, 'UTF-8');
                                $JK_LET  = preg_replace('/[^A-Z]/u', '', $JK_UP);
                                $first   = $JK_LET !== '' ? $JK_LET[0] : '';

                                if ($first === 'L' || preg_match('/\b(LAKI|PRIA|MALE)\b/u', $JK_UP)) {
                                    $jkCode  = 'L';
                                    $jkTag   = 'Laki-laki';
                                    $badgeJK = 'badge bg-primary';
                                } elseif ($first === 'P' || preg_match('/\b(PEREMPUAN|WANITA|FEMALE)\b/u', $JK_UP)) {
                                    $jkCode  = 'P';
                                    $jkTag   = 'Perempuan';
                                    $badgeJK = 'badge bg-danger';
                                } else {
                                    $jkCode  = '-';
                                    $jkTag   = ($jkTrim !== '') ? $jkTrim : '—';
                                    $badgeJK = 'badge bg-secondary';
                                }

                                // Status tahunan
                                $stRaw        = mb_strtolower((string)($g['status'] ?? ''), 'UTF-8');
                                $isActiveYear = in_array($stRaw, ['1', 'aktif', 'active', 'ya', 'true'], true);
                                $statusText   = $isActiveYear ? 'Aktif' : 'Nonaktif';
                                $badgeStatus  = $isActiveYear ? 'badge bg-success' : 'badge bg-secondary';

                                // Tanggal (PAKAI ALIAS GABUNGAN DARI CONTROLLER)
                                $tMasuk  = $fmtDMY($g['tanggal_masuk_all']  ?? null);
                                $tKeluar = $fmtDMY($g['tanggal_keluar_all'] ?? null);

                                // (opsional) Tooltip audit jika tanggal GT dan master berbeda
                                $gtMasuk   = (string)($g['t_masuk_gt'] ?? '');
                                $msMasuk   = (string)($g['t_masuk_master'] ?? '');
                                $audTipMas = ($gtMasuk && $msMasuk && $gtMasuk !== $msMasuk)
                                    ? ' title="Tahunan: ' . $fmtDMY($gtMasuk) . ' • Master: ' . $fmtDMY($msMasuk) . '"'
                                    : '';

                                $gtKeluar   = (string)($g['t_keluar_gt'] ?? '');
                                $msKeluar   = (string)($g['t_keluar_master'] ?? '');
                                $audTipKel  = ($gtKeluar && $msKeluar && $gtKeluar !== $msKeluar)
                                    ? ' title="Tahunan: ' . $fmtDMY($gtKeluar) . ' • Master: ' . $fmtDMY($msKeluar) . '"'
                                    : '';
                            ?>
                                <tr data-name="<?= esc(mb_strtolower($nama, 'UTF-8')) ?>"
                                    data-nip="<?= esc($nip) ?>"
                                    data-gender="<?= esc($jkCode) ?>">
                                    <td class="text-muted"><?= $no++ ?>.</td>
                                    <td><span class="font-monospace"><?= esc($nip) ?></span></td>
                                    <td class="fw-semibold"><?= esc($nama) ?></td>
                                    <td><span class="<?= esc($badgeJK) ?>"><?= esc($jkTag) ?></span></td>
                                    <td><span class="<?= esc($badgeStatus) ?>"><?= esc($statusText) ?></span></td>

                                    <!-- Tanggal: gunakan alias gabungan; tidak perlu reformat lagi -->
                                    <td<?= $audTipMas ?>><?= esc(format_ddmmyyyy_ke_tanggal_indo($tMasuk)) ?></td>
                                        <td<?= $audTipKel ?>><?= esc(format_ddmmyyyy_ke_tanggal_indo($tKeluar)) ?></td>

                                            <td>
                                                <a href="#"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmDeleteLapGuru('<?= esc($id, 'js') ?>')"
                                                    title="Hapus">
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
                    <h5 class="mb-1">Belum ada data guru</h5>
                    <p class="text-muted mb-3">Tambahkan data guru terlebih dahulu untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/data-guru') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-user-plus me-2"></i> Data Guru
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inpQ = document.getElementById('searchGuru');
        const selG = document.getElementById('filterGender');

        // Debounce submit saat mengetik
        let timer = null;
        inpQ?.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 350);
        });

        // Submit otomatis saat dropdown berubah
        selG?.addEventListener('change', function() {
            form.submit();
        });
    });

    function confirmDeleteLapGuru(id) {
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
                window.location.href = "<?= base_url('operator/laporan-guru/delete/') ?>" + encodeURIComponent(String(id));
            }
        });
    }
</script>

<?= $this->endSection() ?>