<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4 page-title">Dashboard</h1>
    <ol class="breadcrumb mb-4 breadcrumb-modern">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <div class="row g-4">

        <!-- Penilaian -->
        <div class="col-xl-4 col-md-6">
            <a href="<?= base_url('siswa/nilai-siswa') ?>" class="text-decoration-none">
                <div class="card card-modern kpi-card kpi-primary shadow-sm lift mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-eyebrow">Akses Cepat</div>
                            <div class="kpi-title">Penilaian</div>
                            <div class="kpi-sub text-muted">Lihat progres belajarmu</div>
                        </div>
                        <i class="fa-solid fa-clipboard-check kpi-icon"></i>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="kpi-link">Selengkapnya</span>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Profil Saya -->
        <div class="col-xl-4 col-md-6">
            <a href="<?= base_url('siswa/profile') ?>" class="text-decoration-none">
                <div class="card card-modern kpi-card kpi-primary shadow-sm lift mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-eyebrow">Identitas</div>
                            <div class="kpi-title">Profil Saya</div>
                            <div class="kpi-sub text-muted">Perbarui data personalmu</div>
                        </div>
                        <i class="fa-solid fa-id-badge kpi-icon"></i>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="kpi-link">Kelola Profil</span>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tenaga Pendidik (ada jumlah guru) -->
        <div class="col-xl-4 col-md-6">
            <a href="<?= base_url('siswa/data-guru') ?>" class="text-decoration-none">
                <div class="card card-modern kpi-card kpi-primary shadow-sm lift mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-eyebrow">Statistik</div>
                            <div class="kpi-title">Tenaga Pendidik</div>
                            <div class="kpi-sub text-muted">
                                <?= number_format((int)($guruCount ?? 0), 0, ',', '.') ?> guru aktif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="kpi-number">
                                <?= number_format((int)($guruCount ?? 0), 0, ',', '.') ?>
                            </div>
                            <i class="fa-solid fa-chalkboard-user kpi-icon-sm"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="kpi-link">Lihat Daftar Guru</span>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <!-- ===== CANVAS SESUAI CONTROLLER ===== -->
    <div class="row g-3 g-md-4">
        <!-- Bar: Nilai per Mapel -->
        <div class="col-xl-6">
            <div class="card card-modern mb-4">
                <div class="card-header"><i class="fas fa-chart-bar me-2"></i> Nilai per Mapel</div>
                <div class="card-body">
                    <canvas id="chartMapel" style="min-height:300px"></canvas>
                    <?php if (empty($mapelLabels ?? [])): ?>
                        <div class="text-muted small mt-2">Belum ada data mapel untuk ditampilkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Line: Tren Nilai -->
        <div class="col-xl-6">
            <div class="card card-modern mb-4">
                <div class="card-header"><i class="fas fa-chart-line me-2"></i> Tren Nilai</div>
                <div class="card-body">
                    <canvas id="chartTren" style="min-height:300px"></canvas>
                    <?php if (empty($trendLabels ?? [])): ?>
                        <div class="text-muted small mt-2">Belum ada data tren untuk ditampilkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<!-- ===== Data PHP → JS (sinkron dengan controller) ===== -->
<script>
    // Bar (mapel)
    const mapelLabels = <?= json_encode($mapelLabels ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const mapelScores = <?= json_encode($mapelScores ?? [], JSON_NUMERIC_CHECK) ?>;

    // Line (tren per waktu)
    const trendLabels = <?= json_encode($trendLabels ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const trendScores = <?= json_encode($trendScores ?? [], JSON_NUMERIC_CHECK) ?>;
</script>

<!-- ===== Init Charts ===== -->
<script>
    (function bootCharts(attempt = 0) {
        // Tunggu Chart.js siap (maks 6 detik)
        if (!window.Chart) {
            if (attempt < 60) return setTimeout(() => bootCharts(attempt + 1), 100);
            console.error('Chart.js belum termuat. Cek CDN / file lokal / CSP.');
            return;
        }

        // Global theme
        Chart.defaults.font.family = `'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial`;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.legend.display = false;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,23,42,.92)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 10;

        const ACCENTS = ['#0d6efd', '#3d8bfd', '#0b5ed7', '#6ea8fe', '#9ec5fe', '#cfe2ff', '#74a5ff'];

        // Bar: Mapel
        const elMapel = document.getElementById('chartMapel');
        if (elMapel && Array.isArray(mapelLabels) && mapelLabels.length) {
            new Chart(elMapel.getContext('2d'), {
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

        // Line: Tren
        const elTren = document.getElementById('chartTren');
        if (elTren && Array.isArray(trendLabels) && trendLabels.length) {
            const ctx = elTren.getContext('2d');
            const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
            grad.addColorStop(0, 'rgba(13,110,253,.35)');
            grad.addColorStop(1, 'rgba(13,110,253,0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Nilai',
                        data: trendScores,
                        borderColor: '#0d6efd',
                        backgroundColor: grad,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#0d6efd',
                        tension: .35,
                        fill: true
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