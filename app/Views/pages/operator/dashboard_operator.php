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
        <!-- Chart 1: Distribusi Mata Pelajaran -->
        <div class="col-xl-6">
            <div class="card-modern card mb-4">
                <div class="card-header">
                    <i class="fas fa-layer-group me-2"></i> Distribusi Mata Pelajaran
                </div>
                <div class="card-body" style="min-height:340px">
                    <canvas id="ChartMapelBar" data-chart="bar" aria-label="Distribusi Mata Pelajaran" role="img"></canvas>
                    <?php if (empty($mapelLabels ?? [])): ?>
                        <div class="text-muted small mt-2">Belum ada data mata pelajaran untuk ditampilkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chart 2: Siswa Aktif per Kelas -->
        <div class="col-xl-6">
            <div class="card-modern card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Siswa Aktif per Kelas
                </div>
                <div class="card-body" style="min-height:280px">
                    <canvas id="ChartSiswaBar" data-chart="bar" aria-label="Siswa Aktif per Kelas" role="img"></canvas>
                    <?php if (empty($byClass ?? [])): ?>
                        <div class="text-muted small mt-2">Belum ada data siswa aktif per kelas.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    (function() {
        const mapelLabels = <?= json_encode(array_values($mapelLabels ?? []), JSON_UNESCAPED_UNICODE) ?>;
        const mapelCounts = <?= json_encode(array_values($mapelCounts ?? []), JSON_NUMERIC_CHECK) ?>;
        const byClass = <?= json_encode($byClass ?? [], JSON_NUMERIC_CHECK) ?>;

        const kelasOrder = [1, 2, 3, 4, 5, 6];
        const kelasLabels = kelasOrder.map(n => 'Kelas ' + n);
        const kelasCounts = kelasOrder.map(n => Number(byClass?.[n] ?? 0));

        function whenChartReady(fn) {
            if (window.Chart) return fn();
            const id = 'chartjs-4.4.4-cdn';
            if (!document.getElementById(id)) {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js';
                s.id = id;
                s.async = true;
                s.onload = fn;
                document.head.appendChild(s);
            } else {
                document.getElementById(id).addEventListener('load', fn, {
                    once: true
                });
            }
        }

        // Pecah label panjang biar muat (maks 10 char per baris)
        function wrapLabel(label, max = 10) {
            const words = String(label).split(/\s+/);
            const lines = [];
            let line = '';
            for (const w of words) {
                if ((line + ' ' + w).trim().length > max) {
                    if (line) lines.push(line);
                    line = w;
                } else {
                    line = (line ? line + ' ' : '') + w;
                }
            }
            if (line) lines.push(line);
            return lines.join('\n'); // Chart.js akan buat baris baru dengan \n
        }

        function initCharts() {
            const ctxMapel = document.getElementById('ChartMapelBar')?.getContext('2d');
            const ctxSiswa = document.getElementById('ChartSiswaBar')?.getContext('2d');

            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y ?? ctx.parsed}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            autoSkip: false, // << kunci
                            font: {
                                size: 10
                            }, // kecilkan biar muat
                            maxRotation: 60,
                            minRotation: 0,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            };

            // Distribusi Mapel — tampilkan SEMUA label
            if (ctxMapel && mapelLabels.length && mapelCounts.length) {
                new Chart(ctxMapel, {
                    type: 'bar',
                    data: {
                        labels: mapelLabels.map(l => wrapLabel(l, 12)),
                        datasets: [{
                            label: 'Jumlah',
                            data: mapelCounts.map(Number),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...baseOptions,
                        scales: {
                            ...baseOptions.scales,
                            x: {
                                ...baseOptions.scales.x,
                                ticks: {
                                    ...baseOptions.scales.x.ticks,
                                    callback: function(value, index) {
                                        return this.getLabelForValue(value);
                                    }
                                }
                            },
                            y: {
                                ...baseOptions.scales.y,
                                title: {
                                    display: true,
                                    text: 'Jumlah'
                                }
                            }
                        }
                    }
                });
            }

            // Siswa Aktif per Kelas
            if (ctxSiswa) {
                new Chart(ctxSiswa, {
                    type: 'bar',
                    data: {
                        labels: kelasLabels,
                        datasets: [{
                            label: 'Siswa Aktif',
                            data: kelasCounts,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...baseOptions,
                        scales: {
                            ...baseOptions.scales,
                            y: {
                                ...baseOptions.scales.y,
                                title: {
                                    display: true,
                                    text: 'Jumlah Siswa'
                                }
                            }
                        }
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => whenChartReady(initCharts));
        } else {
            whenChartReady(initCharts);
        }
    })();
</script>
<?= $this->endSection() ?>