<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<style>
    /* ===== Theme tokens (white + #0d6efd) ===== */
    :root {
        --bg: #ffffff;
        --text: #0f172a;
        --muted: #6b7280;
        --card-bg: #ffffff;
        --border: #e9ecef;

        --accent: #0d6efd;
        /* PRIMARY */
        --accent-700: #0b5ed7;
        --accent-600: #3d8bfd;
        --accent-400: #6ea8fe;
        --accent-200: #9ec5fe;
        --accent-100: #cfe2ff;
        --accent-glow: rgba(13, 110, 253, .08);

        --ring: 0 0 0 .15rem rgba(13, 110, 253, .25);
    }

    /* Page */
    body {
        background: #fff;
    }

    .container-fluid {
        background: #fff;
    }

    /* ===== Hero (white + subtle accent glow) ===== */
    .dashboard-hero {
        position: relative;
        border-radius: 20px;
        background:
            radial-gradient(700px 260px at -10% -20%, var(--accent-glow) 0%, transparent 60%),
            radial-gradient(600px 220px at 110% -10%, rgba(13, 110, 253, .06) 0%, transparent 55%),
            var(--card-bg);
        border: 1px solid var(--border);
        box-shadow: 0 .6rem 1.4rem rgba(15, 23, 42, .06);
        padding: 24px 20px;
    }

    .dashboard-hero .title {
        font-weight: 800;
        color: var(--text);
        letter-spacing: .2px;
    }

    .dashboard-hero .subtitle {
        color: var(--muted);
    }

    /* ===== KPI Cards (clean white) ===== */
    .kpi {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 .4rem 1rem rgba(15, 23, 42, .06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 .6rem 1.4rem rgba(15, 23, 42, .08);
    }

    .kpi .eyebrow {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08rem;
        color: var(--muted);
    }

    .kpi .title {
        font-weight: 700;
        margin-top: .2rem;
        color: var(--text);
    }

    .kpi .number {
        font-size: 2.1rem;
        font-weight: 800;
        margin-top: .2rem;
        color: #0b172a;
    }

    .kpi .icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-600) 100%);
        box-shadow: 0 8px 20px rgba(13, 110, 253, .25), inset 0 1px rgba(255, 255, 255, .35);
    }

    .kpi .footer {
        border-top: 1px solid var(--border);
        padding-top: .6rem;
        margin-top: .8rem;
        color: var(--muted);
        font-size: .92rem;
    }

    /* ===== Cards (white) ===== */
    .card-modern {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 .4rem 1rem rgba(15, 23, 42, .06);
    }

    .card-modern .card-header {
        background: #fff;
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        color: var(--text);
    }

    /* Inputs & buttons focus */
    .form-control:focus,
    .form-select:focus,
    .btn:focus {
        box-shadow: var(--ring);
    }

    /* Soft badge (accent tint) */
    .badge-soft {
        background: var(--accent-100);
        color: var(--accent);
        border: 1px solid var(--accent-200);
    }

    /* Charts sizing */
    #ChartMapelBar,
    #ChartSiswaBar {
        min-height: 260px;
    }
</style>

<div class="container-fluid px-3 px-md-4">

    <!-- Hero -->
    <section class="dashboard-hero mt-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-1 title">Dashboard Operator</h1>
                <div class="subtitle">
                    Ringkasan akademik & statistik sekolah
                    <?php if (!empty($ta_aktif)): ?>
                        • <span class="badge badge-soft ms-1">TA: <?= esc($ta_aktif['tahun'] ?? '') ?> - Semester <?= esc($ta_aktif['semester'] ?? '') ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('operator/laporan/nilai-siswa') ?>" class="btn btn-primary rounded-pill px-3">
                    <i class="fa-solid fa-table-list me-2"></i> Kelola Nilai
                </a>
                <a href="<?= base_url('operator/data-siswa') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-users me-2"></i> Data Siswa
                </a>
            </div>
        </div>
    </section>

    <!-- KPI Row -->
    <div class="row g-3 g-md-4 mb-3">
        <!-- Nilai Tertinggi -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Pencapaian</div>
                        <div class="title">Nilai Tertinggi</div>
                        <div class="number"><?= esc(number_format($topNilai ?? 0, 0, ',', '.')) ?></div>
                        <div class="text-muted">
                            <?= esc($topNama ?? '—') ?>
                            <?php if (!empty($topKelas)): ?>
                                <span class="badge badge-soft ms-1">Kelas <?= esc($topKelas) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guru Aktif -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Tenaga Pendidik</div>
                        <div class="title">Guru Aktif</div>
                        <div class="number"><?= esc(number_format($guruCount ?? 0, 0, ',', '.')) ?></div>
                        <div class="text-muted">Profesional & berdedikasi</div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siswa Aktif -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Peserta Didik</div>
                        <div class="title">Siswa Aktif (1–6)</div>
                        <div class="number"><?= esc(number_format($siswaTotal ?? 0, 0, ',', '.')) ?></div>
                        <div class="text-muted">Semua tingkat kelas</div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelas Terpadat -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Sorotan</div>
                        <div class="title">Kelas Terpadat</div>
                        <div class="number">
                            <?= !empty($kelasTerpadat) ? 'Kelas ' . esc($kelasTerpadat) : '—' ?>
                        </div>
                        <div class="text-muted"><?= esc(number_format($kelasTerpadatJumlah ?? 0, 0, ',', '.')) ?> siswa</div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 g-md-4">
        <!-- Chart 1: Distribusi Mapel -->
        <div class="col-xl-6">
            <div class="card-modern card mb-4">
                <div class="card-header">
                    <i class="fas fa-layer-group me-2"></i> Distribusi Mapel (tb_mapel)
                </div>
                <div class="card-body">
                    <canvas id="ChartMapelBar"></canvas>
                    <?php if (empty($mapelLabels ?? [])): ?>
                        <div class="text-muted small mt-2">Belum ada data mapel untuk ditampilkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chart 2: Distribusi Siswa per Kelas -->
        <div class="col-xl-6">
            <div class="card-modern card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Distribusi Siswa per Kelas
                </div>
                <div class="card-body">
                    <canvas id="ChartSiswaBar"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Data PHP -> JS
    const mapelLabels = <?= json_encode($mapelLabels ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const mapelCounts = <?= json_encode($mapelCounts ?? [], JSON_NUMERIC_CHECK) ?>;
    const kelasLabels = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
    const kelasCounts = [
        <?= (int)($byClass[1] ?? 0) ?>,
        <?= (int)($byClass[2] ?? 0) ?>,
        <?= (int)($byClass[3] ?? 0) ?>,
        <?= (int)($byClass[4] ?? 0) ?>,
        <?= (int)($byClass[5] ?? 0) ?>,
        <?= (int)($byClass[6] ?? 0) ?>,
    ];

    // Tunggu Chart.js benar-benar tersedia
    (function bootCharts(attempt = 0) {
        if (!window.Chart) {
            if (attempt < 60) return setTimeout(() => bootCharts(attempt + 1), 100); // max +/-6 detik
            console.error('Chart.js belum termuat. Cek CDN / file lokal / CSP.');
            return;
        }

        // Global style
        Chart.defaults.font.family = `'Inter',system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,'Noto Sans'`;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.legend.display = false;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,23,42,.92)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 10;

        const ACCENTS = ['#0d6efd', '#3d8bfd', '#0b5ed7', '#6ea8fe', '#9ec5fe', '#cfe2ff', '#74a5ff'];

        // Chart 1: Mapel
        const elMapel = document.getElementById('ChartMapelBar');
        if (elMapel && mapelLabels.length) {
            new Chart(elMapel.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: mapelLabels,
                    datasets: [{
                        label: 'Jumlah',
                        data: mapelCounts,
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
                            grid: {
                                color: 'rgba(233,236,239,.8)'
                            },
                            ticks: {
                                precision: 0
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

        // Chart 2: Siswa per Kelas
        const elKelas = document.getElementById('ChartSiswaBar');
        if (elKelas) {
            new Chart(elKelas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: kelasLabels,
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: kelasCounts,
                        backgroundColor: kelasLabels.map((_, i) => ACCENTS[i % ACCENTS.length]),
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