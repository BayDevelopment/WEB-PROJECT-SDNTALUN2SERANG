<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid dashboard-modern px-4">

    <!-- Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mt-4 mb-4">
        <div>
            <h1 class="h3 mb-0 page-title">Dashboard Operator</h1>
            <div class="text-muted small">Ringkasan akademik & statistik sekolah</div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-4">

        <!-- Nilai Tertinggi -->
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card kpi-gradient mb-4 lift">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-eyebrow">Pencapaian</div>
                        <div class="kpi-title">Nilai Tertinggi</div>
                        <div class="kpi-number"><?= esc(number_format($topNilai ?? 0, 0, ',', '.')) ?></div>
                        <div class="kpi-sub text-muted">
                            <?= esc($topNama ?? '—') ?>
                            <?php if (!empty($topKelas)): ?>
                                <span class="badge bg-light text-dark ms-1">Kelas <?= esc($topKelas) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-trophy kpi-icon"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="kpi-link">Detail Penilaian</span>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>

        <!-- Jumlah Guru -->
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card kpi-soft mb-4 lift">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-eyebrow">Tenaga Pendidik</div>
                        <div class="kpi-title">Guru Aktif</div>
                        <div class="kpi-number"><?= esc(number_format($guruCount ?? 0, 0, ',', '.')) ?></div>
                        <div class="kpi-sub text-muted">Profesional & berdedikasi</div>
                    </div>
                    <i class="fa-solid fa-chalkboard-user kpi-icon"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="kpi-link">Lihat Daftar Guru</span>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>

        <!-- Jumlah Siswa Kelas 1–6 -->
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card kpi-soft-2 mb-4 lift">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-eyebrow">Peserta Didik</div>
                        <div class="kpi-title">Siswa Aktif (1–6)</div>
                        <div class="kpi-number"><?= esc(number_format($siswaTotal ?? 0, 0, ',', '.')) ?></div>
                        <div class="kpi-sub text-muted">Semua tingkat kelas</div>
                    </div>
                    <i class="fa-solid fa-user-graduate kpi-icon"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="kpi-link">Kelola Data Siswa</span>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>

        <!-- Kelas Terpadat -->
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card kpi-outline mb-4 lift">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-eyebrow">Sorotan</div>
                        <div class="kpi-title">Kelas Terpadat</div>
                        <div class="kpi-number">
                            <?= !empty($kelasTerpadat) ? 'Kelas ' . esc($kelasTerpadat) : '—' ?>
                        </div>
                        <div class="kpi-sub text-muted">
                            <?= esc(number_format($kelasTerpadatJumlah ?? 0, 0, ',', '.')) ?> siswa
                        </div>
                    </div>
                    <i class="fa-solid fa-people-group kpi-icon"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="kpi-link">Rincian Per Kelas</span>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Tren Siswa per Kelas (1–6)
                </div>
                <div class="card-body">
                    <canvas id="ChartSiswaLine" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Distribusi Siswa per Kelas
                </div>
                <div class="card-body">
                    <canvas id="ChartSiswaBar" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Data untuk chart (PHP -> JS) -->
<script>
    const kelasLabels = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
    const kelasCounts = [
        <?= (int)($byClass[1] ?? 0) ?>,
        <?= (int)($byClass[2] ?? 0) ?>,
        <?= (int)($byClass[3] ?? 0) ?>,
        <?= (int)($byClass[4] ?? 0) ?>,
        <?= (int)($byClass[5] ?? 0) ?>,
        <?= (int)($byClass[6] ?? 0) ?>,
    ];
</script>

<!-- Init Chart.js (anggap Chart.js sudah di-include oleh template) -->
<script>
    (function() {
        if (typeof Chart === 'undefined') return;

        // Line
        const ctxLine = document.getElementById('ChartSiswaLine');
        if (ctxLine) {
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: kelasLabels,
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: kelasCounts,
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Bar
        const ctxBar = document.getElementById('ChartSiswaBar');
        if (ctxBar) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: kelasLabels,
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: kelasCounts
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    })();
</script>
<?= $this->endSection() ?>