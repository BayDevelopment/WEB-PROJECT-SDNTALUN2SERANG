<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --accent: #0d6efd;
        --accent-100: #cfe2ff;
        --accent-200: #9ec5fe;
        --muted: #6b7280;
        --border: #e9ecef;
        --text: #0f172a;
        --shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .06);
        --shadow-lg: 0 .7rem 1.4rem rgba(15, 23, 42, .09);
    }

    .page-title {
        font-weight: 800;
        letter-spacing: .2px
    }

    .card-modern {
        position: relative;
        border-radius: 18px;
        background: #fff;
        box-shadow: var(--shadow);
        border: 1px solid transparent;
        background-image: linear-gradient(#fff, #fff),
            linear-gradient(180deg, rgba(13, 110, 253, .25), rgba(13, 110, 253, .05));
        background-origin: border-box;
        background-clip: padding-box, border-box;
    }

    .card-modern:hover {
        box-shadow: var(--shadow-lg)
    }

    .card-modern .card-header {
        background: #fff;
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        color: var(--text);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 10px
    }

    .card-modern .card-header:after {
        content: "";
        height: 3px;
        width: 36px;
        border-radius: 4px;
        background: var(--accent);
        display: inline-block;
        margin-left: auto;
        opacity: .25
    }

    .card-modern .card-body {
        padding: 18px
    }

    .badge-soft {
        background: var(--accent-100);
        color: var(--accent);
        border: 1px solid var(--accent-200)
    }

    .kpi-mini {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px
    }

    .kpi-mini .num {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0b172a
    }

    .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #0d6efd, #3d8bfd);
        color: #fff;
        box-shadow: 0 8px 20px rgba(13, 110, 253, .25), inset 0 1px rgba(255, 255, 255, .35)
    }

    .avatar-96 {
        width: 96px;
        height: 96px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid var(--border)
    }

    .profile-head .name {
        font-weight: 800
    }

    .profile-head .sub {
        color: var(--muted)
    }

    .divider-soft {
        border: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #eef2f6, transparent);
        margin: 16px 0
    }

    .list-kv {
        margin: 0;
        padding: 0;
        list-style: none
    }

    .list-kv li {
        display: flex;
        gap: 12px;
        justify-content: space-between;
        align-items: center;
        padding: .55rem 0;
        border-bottom: 1px dashed #f1f3f5
    }

    .list-kv li:last-child {
        border-bottom: 0
    }

    .kv-k {
        color: var(--muted);
        min-width: 46%
    }

    .kv-v {
        font-weight: 600;
        text-align: right;
        word-break: break-word
    }

    #chartMapel {
        min-height: 320px
    }

    /* table */
    .table-modern {
        --bs-table-striped-bg: #f8f9fa;
        border-color: var(--border)
    }

    .table-modern th {
        white-space: nowrap
    }

    .badge-cat {
        background: #eef2ff;
        color: #3d8bfd;
        border: 1px solid #dbe4ff
    }
</style>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mt-4 page-title">Laporan Nilai Saya</h1>
            <ol class="breadcrumb breadcrumb-modern mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Laporan Nilai</li>
            </ol>
        </div>
        <div class="text-muted small">
            Data ini otomatis dibatasi untuk akun yang sedang login.
        </div>
    </div>

    <?php
    $foto = trim((string)($me['photo'] ?? ''));
    $img  = $foto !== '' ? base_url('assets/img/uploads/' . $foto) : base_url('assets/img/user.png');
    $gRaw = strtoupper((string)($me['gender'] ?? ''));
    $genderLabel = $gRaw === 'L' ? 'Laki-laki' : ($gRaw === 'P' ? 'Perempuan' : '—');
    $active = (int)($me['user_active'] ?? 0) === 1;
    ?>

    <div class="row g-3 g-md-4 mb-3">
        <!-- Profil -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header"><i class="fa-regular fa-id-card text-primary"></i> Profil Siswa</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 profile-head">
                        <img src="<?= esc($img) ?>" alt="Foto Profil" class="avatar-96">
                        <div>
                            <div class="name h5 mb-1"><?= esc($me['full_name'] ?? '—') ?></div>
                            <div class="sub small mb-2">
                                <?= esc($me['user_name'] ?? ($me['username'] ?? '')) ?>
                                <?php if (!empty($me['user_email'])): ?> • <?= esc($me['user_email']) ?> <?php endif; ?>
                            </div>
                            <span class="badge badge-soft"><?= $active ? 'Akun Aktif' : 'Akun Nonaktif' ?></span>
                        </div>
                    </div>

                    <hr class="divider-soft">

                    <ul class="list-kv">
                        <li><span class="kv-k">NISN</span><span class="kv-v"><?= esc($me['nisn'] ?? '—') ?></span></li>
                        <li><span class="kv-k">Jenis Kelamin</span><span class="kv-v"><?= esc($genderLabel) ?></span></li>
                        <li>
                            <span class="kv-k">Kelas</span>
                            <span class="kv-v">
                                <?php
                                $tingkat = $me['kelas_tingkat'] ?? null;
                                $namaKls = $me['kelas_nama'] ?? '';
                                echo $tingkat ? 'Kelas ' . esc($tingkat) : esc($namaKls ?: '—');
                                ?>
                            </span>
                        </li>
                        <li>
                            <span class="kv-k">Tempat/Tanggal Lahir</span>
                            <span class="kv-v">
                                <?= esc($me['birth_place'] ?? '—') ?>
                                <?= (!empty($me['birth_place']) && !empty($me['birth_date'])) ? ' / ' : '' ?>
                                <?= esc($me['birth_date'] ?? '') ?>
                            </span>
                        </li>
                        <li><span class="kv-k">Orang Tua/Wali</span><span class="kv-v"><?= esc($me['parent_name'] ?? '—') ?></span></li>
                        <li><span class="kv-k">No. HP</span><span class="kv-v"><?= esc($me['phone'] ?? '—') ?></span></li>
                        <li><span class="kv-k">Alamat</span><span class="kv-v"><?= esc($me['address'] ?? '—') ?></span></li>
                        <li><span class="kv-k">Total Laporan Tahunan</span><span class="kv-v"><?= number_format((int)($me['laporan_count'] ?? 0), 0, ',', '.') ?></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- KPI & Chart -->
        <div class="col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="kpi-mini">
                        <div class="kpi-icon"><i class="fa-solid fa-database"></i></div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Total Nilai Terekam</div>
                            <div class="num"><?= number_format((int)($totalNilai ?? 0), 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="kpi-mini">
                        <div class="kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Rata-rata Nilai</div>
                            <div class="num"><?= $avgNilai !== null ? number_format((float)$avgNilai, 2, ',', '.') : '—' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-modern mb-3">
                <div class="card-header"><i class="fas fa-chart-bar text-primary me-2"></i> Nilai per Mapel</div>
                <div class="card-body">
                    <canvas id="chartMapel"></canvas>
                    <?php if (empty($mapelLabels ?? [])): ?>
                        <div class="text-muted small mt-2">Belum ada data nilai untuk ditampilkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Nilai (hanya milik saya) -->
    <div class="card-modern">
        <div class="card-header">
            <i class="fa-regular fa-clock text-primary"></i> Riwayat Nilai Saya
        </div>
        <div class="card-body">
            <?php if (!empty($d_nilai)): ?>
                <div class="table-responsive">
                    <table class="table table-modern table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tahun Ajaran</th>
                                <th>Semester</th>
                                <th>Tanggal</th>
                                <th>Mapel</th>
                                <th>Kategori</th>
                                <th class="text-end">Skor</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($d_nilai as $n): ?>
                                <tr>
                                    <td><?= esc($n['tahun_ajaran'] ?? '-') ?></td>
                                    <td><?= esc($n['semester'] ?? '-') ?></td>
                                    <td><?= esc($n['tanggal'] ?? '-') ?></td>
                                    <td><?= esc($n['mapel_nama'] ?? '-') ?></td>
                                    <td><span class="badge badge-cat"><?= esc($n['kategori_kode'] ?? ($n['kategori_nama'] ?? '-')) ?></span></td>
                                    <td class="text-end"><?= number_format((float)($n['skor'] ?? 0), 2, ',', '.') ?></td>
                                    <td><?= esc($n['keterangan'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">Belum ada riwayat nilai.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Data PHP -> JS -->
<script>
    const mapelLabels = <?= json_encode($mapelLabels ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const mapelScores = <?= json_encode($mapelScores ?? [], JSON_NUMERIC_CHECK) ?>;
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>

<!-- Init Chart -->
<script>
    (function bootCharts(attempt = 0) {
        if (!window.Chart) {
            if (attempt < 60) return setTimeout(() => bootCharts(attempt + 1), 100);
            console.error('Chart.js belum termuat.');
            return;
        }
        Chart.defaults.font.family = `'Inter',system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial`;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.legend.display = false;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,23,42,.92)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 10;

        const ACCENTS = ['#0d6efd', '#3d8bfd', '#0b5ed7', '#6ea8fe', '#9ec5fe', '#cfe2ff', '#74a5ff'];

        const el = document.getElementById('chartMapel');
        if (el && Array.isArray(mapelLabels) && mapelLabels.length) {
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: mapelLabels,
                    datasets: [{
                        label: 'Nilai',
                        data: mapelScores,
                        backgroundColor: mapelLabels.map((_, i) => ACCENTS[i % ACCENTS.length]),
                        borderWidth: 0,
                        borderRadius: 12,
                        maxBarThickness: 44
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(233,236,239,.8)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    })();
</script>

<?= $this->endSection() ?>