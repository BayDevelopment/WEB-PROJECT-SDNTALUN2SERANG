<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    /* ===== Theme tokens (white + #0d6efd) ===== */
    :root {
        --bg: #fff;
        --text: #0f172a;
        --muted: #6b7280;
        --card-bg: #fff;
        --border: #e9ecef;
        --accent: #0d6efd;
        --accent-700: #0b5ed7;
        --accent-600: #3d8bfd;
        --accent-400: #6ea8fe;
        --accent-200: #9ec5fe;
        --accent-100: #cfe2ff;
        --accent-glow: rgba(13, 110, 253, .08);
        --ring: 0 0 0 .15rem rgba(13, 110, 253, .25)
    }

    body,
    .container-fluid {
        background: #fff
    }

    /* ===== Hero ===== */
    .dashboard-hero {
        position: relative;
        border-radius: 20px;
        background:
            radial-gradient(700px 260px at -10% -20%, var(--accent-glow) 0%, transparent 60%),
            radial-gradient(600px 220px at 110% -10%, rgba(13, 110, 253, .06) 0%, transparent 55%),
            var(--card-bg);
        border: 1px solid var(--border);
        box-shadow: 0 .6rem 1.4rem rgba(15, 23, 42, .06);
        padding: 24px 20px
    }

    .dashboard-hero .title {
        font-weight: 800;
        color: var(--text);
        letter-spacing: .2px
    }

    .dashboard-hero .subtitle {
        color: var(--muted)
    }

    /* ===== KPI Cards ===== */
    .kpi {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 .4rem 1rem rgba(15, 23, 42, .06);
        transition: transform .2s, box-shadow .2s
    }

    .kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 .6rem 1.4rem rgba(15, 23, 42, .08)
    }

    .kpi .eyebrow {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08rem;
        color: var(--muted)
    }

    .kpi .title {
        font-weight: 700;
        margin-top: .2rem;
        color: var(--text)
    }

    /* Angka adaptif di semua device */
    .kpi .number {
        font-weight: 800;
        margin-top: .2rem;
        color: #0b172a;
        /* dari 1.4rem (HP) naik sampai 2.4rem (desktop besar) */
        font-size: clamp(1.4rem, 2vw + .8rem, 2.4rem);
        line-height: 1.1
    }

    /* Baris konten dalam kartu KPI */
    .kpi .rowline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px
    }

    .kpi .meta {
        color: var(--muted);
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        align-items: center;
        max-width: 100%
    }

    /* Nama siswa: elipsis rapi di perangkat sempit */
    .kpi .name-ellipsis {
        max-width: 100%;
        display: inline-block;
        vertical-align: bottom;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis
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
        flex: 0 0 auto
    }

    /* Soft badge */
    .badge-soft {
        background: var(--accent-100);
        color: var(--accent);
        border: 1px solid var(--accent-200)
    }

    /* ===== Cards (charts) ===== */
    .card-modern {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 .4rem 1rem rgba(15, 23, 42, .06)
    }

    .card-modern .card-header {
        background: #fff;
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        color: var(--text)
    }

    /* Focus ring */
    .form-control:focus,
    .form-select:focus,
    .btn:focus {
        box-shadow: var(--ring)
    }

    /* Charts sizing (responsif) */
    #ChartMapelBar,
    #ChartSiswaBar {
        min-height: 260px
    }

    .card-body canvas {
        display: block;
        width: 100% !important;
        height: 220px !important;
    }

    /* ====== Responsive tweaks ====== */

    /* ≥1200px (desktop besar) – biarkan default */

    /* 992–1199px (laptop) */
    @media (max-width:1199.98px) {
        .kpi .icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 12px
        }

        .card-body canvas {
            height: 210px !important
        }
    }

    /* 768–991px (tablet) → grid 2 kolom card KPI sudah di-handle oleh col-md-6 */
    @media (max-width:991.98px) {
        .dashboard-hero {
            padding: 20px 16px
        }

        .kpi {
            padding: .15rem
        }

        .kpi .icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px
        }

        .kpi .number {
            font-size: clamp(1.35rem, 2.2vw + .6rem, 2rem)
        }

        .card-body canvas {
            height: 200px !important
        }
    }

    /* ≤767px (HP) → tiap KPI full width; jaga hierarki visual */
    @media (max-width:767.98px) {
        .dashboard-hero {
            padding: 16px 14px;
            border-radius: 16px
        }

        .kpi {
            border-radius: 16px
        }

        .kpi .rowline {
            gap: 10px
        }

        .kpi .icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 10px
        }

        .kpi .number {
            font-size: clamp(1.25rem, 4vw + .5rem, 1.8rem)
        }

        .kpi .meta {
            gap: .3rem
        }

        .badge,
        .badge-soft {
            font-size: .72rem
        }

        .card-body canvas {
            height: 190px !important
        }
    }

    /* Nama siswa & badge tidak “nabrak” ikon di layar sempit */
    @media (max-width:575.98px) {
        .kpi .meta {
            max-width: calc(100% - 56px)
        }

        /* kurangi area ikon */
    }

    /* ===== Dashboard Hero: mobile first tweaks ===== */
    @media (max-width: 576px) {

        /* Baris utama: stack vertikal, isi melebar penuh */
        .dashboard-hero .d-flex.flex-wrap.align-items-center.justify-content-between {
            flex-direction: column;
            align-items: stretch;
            gap: .5rem 0;
        }

        /* Tipografi judul & subjudul lebih kecil, tetap tegas */
        .dashboard-hero .title {
            font-size: 1.125rem;
            /* ~18px */
            line-height: 1.25;
            margin-bottom: .25rem;
        }

        .dashboard-hero .subtitle {
            font-size: .875rem;
            /* ~14px */
            line-height: 1.35;
            word-break: break-word;
            /* antisipasi teks panjang */
        }

        .dashboard-hero .badge {
            font-size: .75rem;
            /* ~12px */
            padding: .25rem .5rem;
            white-space: nowrap;
            /* biar badge nggak pecah aneh */
        }

        /* Kelompok tombol aksi: jadikan full-width & menumpuk */
        .dashboard-hero .d-flex.flex-wrap.gap-2 {
            width: 100%;
            justify-content: stretch;
            gap: .5rem;
            /* rapikan jarak antar tombol */
        }

        .dashboard-hero .d-flex.flex-wrap.gap-2 .btn {
            flex: 1 1 100%;
            width: 100%;
            padding: .5rem .75rem;
            /* kompak tapi nyaman */
            border-radius: 999px;
            /* pertahankan rounded-pill feel */
            font-size: .875rem;
        }

        /* ===== Filter Mapel: susun vertikal & mudah disentuh ===== */
        .dashboard-hero form .row.g-2 {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .5rem;
            align-items: stretch !important;
        }

        /* Label pindah ke atas (full width) */
        .dashboard-hero form .col-auto:first-child {
            width: 100%;
        }

        .dashboard-hero form .col-auto:first-child .col-form-label {
            display: block;
            font-size: .875rem;
            margin: 0 0 .25rem;
        }

        /* Select full width */
        .dashboard-hero form .col-12.col-md-5.col-lg-4 {
            width: 100%;
        }

        .dashboard-hero form select.form-select.form-select-sm {
            font-size: .875rem;
            padding: .375rem .75rem;
        }

        /* Tombol reset ikut full width */
        .dashboard-hero form .col-auto:last-child {
            width: 100%;
        }

        .dashboard-hero form .btn.btn-sm {
            width: 100%;
            padding: .5rem .75rem;
            font-size: .875rem;
            border-radius: 999px;
        }
    }
</style>


<div class="container-fluid px-3 px-md-4">

    <?php
    // normalisasi jabatan untuk badge & filter
    $jabatanLower = mb_strtolower(trim((string)($jabatan ?? '')), 'UTF-8');
    $isGuru = ($jabatanLower === 'guru');
    $isWali = ($jabatanLower === 'wali kelas');
    ?>

    <!-- Hero -->
    <section class="dashboard-hero mt-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-1 title">Dashboard Guru</h1>
                <div class="subtitle">
                    Ringkasan akademik & statistik (kelas yang Anda ajar)
                    <?php if (!empty($ta_aktif)): ?>
                        • <span class="badge badge-soft ms-1">TA: <?= esc($ta_aktif['tahun'] ?? '') ?> - Semester <?= esc($ta_aktif['semester'] ?? '') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('operator/laporan-nilai-siswa') ?>" class="btn btn-primary rounded-pill px-3">
                    <i class="fa-solid fa-table-list me-2"></i> Kelola Nilai
                </a>
                <a href="<?= base_url('guru/data-siswa') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-users me-2"></i> Data Siswa
                </a>
            </div>
        </div>

        <!-- FILTER MAPEL (muncul hanya untuk jabatan=Guru) -->
        <?php if ($isGuru && !empty($mapelList)): ?>
            <form method="get" class="mt-3">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label col-form-label-sm fw-semibold">
                            <i class="fa-solid fa-filter me-1"></i> Pilih Mapel
                        </label>
                    </div>
                    <div class="col-12 col-md-5 col-lg-4">
                        <select name="mapel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ($mapelList as $mid => $mname): ?>
                                <option value="<?= esc($mid) ?>" <?= (int)($mapelSelected ?? 0) === (int)$mid ? 'selected' : '' ?>>
                                    <?= esc($mname) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="<?= esc(current_url()) ?>" class="btn btn-outline-secondary btn-sm rounded-pill py-2">Reset</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <?php
    // ---- Context badges & scope text (ikut data dari controller) ----
    $jabatanLower   = mb_strtolower(trim((string)($jabatan ?? '')), 'UTF-8');
    $isGuru         = ($jabatanLower === 'guru');
    $isWali         = ($jabatanLower === 'wali kelas');

    $mapelNm        = trim((string)($mapelSelectedNm ?? ''));
    $kelasNmWali    = trim((string)($waliKelasNama ?? ''));  // di-set di controller saat wali

    // Tulis badge konteks kecil di bawah judul KPI “Nilai Tertinggi”
    $badgeMapel     = $isGuru && $mapelNm !== '' ? '<span class="badge bg-primary-subtle text-primary ms-1">' . esc($mapelNm) . '</span>' : '';
    $badgeKelasWali = $isWali && $kelasNmWali !== '' ? '<span class="badge bg-success-subtle text-success ms-1">' . esc($kelasNmWali) . '</span>' : '';

    // Scope teks untuk kotak “Siswa Aktif”
    $scopeSubtitle  = $isGuru
        ? 'Kelas yang Anda ajar (mapel: ' . ($mapelNm !== '' ? esc($mapelNm) : '—') . ')'
        : ($isWali
            ? ('Kelas ' . ($kelasNmWali !== '' ? esc($kelasNmWali) : '—'))
            : 'Cakupan kelas yang Anda ampu');

    // Format helper angka
    $fmtTopNilai    = esc(number_format((float)($topNilai ?? 0), 0, ',', '.'));
    $fmtGuruAktif   = esc(number_format((int)($guruCount ?? 0), 0, ',', '.'));
    $fmtSiswaAktif  = esc(number_format((int)($siswaTotal ?? 0), 0, ',', '.'));
    $fmtPadatJml    = esc(number_format((int)($kelasTerpadatJumlah ?? 0), 0, ',', '.'));

    $namaSiswaTop   = trim((string)($topNama ?? ''));
    $namaSiswaTop   = $namaSiswaTop !== '' ? esc($namaSiswaTop) : '—';

    $kelasTop       = trim((string)($topKelas ?? ''));
    $kelasTopBadge  = $kelasTop !== '' ? '<span class="badge badge-soft ms-1">Kelas ' . esc($kelasTop) . '</span>' : '';

    $kelasTerpadatV = trim((string)($kelasTerpadat ?? ''));
    $kelasTerpadatT = $kelasTerpadatV !== '' ? 'Kelas ' . esc($kelasTerpadatV) : '—';
    ?>
    <!-- KPI Row -->
    <div class="row g-3 g-md-4 mb-3">
        <!-- Nilai Tertinggi -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="rowline">
                    <div>
                        <div class="eyebrow">Pencapaian</div>
                        <div class="title">Nilai Tertinggi</div>
                        <div class="number" aria-label="Nilai tertinggi"><?= $fmtTopNilai ?></div>
                        <div class="meta">
                            <span class="name-ellipsis" title="<?= esc($namaSiswaTop) ?>"><?= $namaSiswaTop ?></span>
                        </div>
                    </div>
                    <div class="icon-wrap" aria-hidden="true">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Guru Aktif (Global) -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Tenaga Pendidik</div>
                        <div class="title">Guru Aktif</div>
                        <div class="number" aria-label="Jumlah guru aktif"><?= $fmtGuruAktif ?></div>
                        <div class="text-muted">Profesional & berdedikasi</div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siswa Aktif (kelas dalam scope) -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Peserta Didik</div>
                        <div class="title">Siswa Aktif</div>
                        <div class="number" aria-label="Jumlah siswa aktif"><?= $fmtSiswaAktif ?></div>
                        <div class="text-muted">Smart & Unggul</div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-user-graduate" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelas Terpadat (dalam scope) -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="eyebrow">Sorotan</div>
                        <div class="title">Kelas Terpadat</div>
                        <div class="number" aria-label="Kelas terpadat">
                            <?= $kelasTerpadatT ?>
                        </div>
                        <div class="text-muted"><?= $fmtPadatJml ?> siswa</div>
                    </div>
                    <div class="icon-wrap">
                        <i class="fa-solid fa-people-group" aria-hidden="true"></i>
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
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-layer-group me-2"></i> Distribusi Mapel</span>
                    <?php if (!empty($mapelSelectedNm ?? null)): ?>
                        <span class="badge bg-primary-subtle text-primary small">
                            Mapel aktif: <?= esc($mapelSelectedNm) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="min-height:260px">
                    <canvas id="ChartMapelBar" height="160" role="img" aria-label="Distribusi Mapel"></canvas>
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
                <div class="card-body" style="min-height:260px">
                    <canvas id="ChartSiswaBar" height="160" role="img" aria-label="Distribusi siswa per kelas"></canvas>
                    <?php
                    $totalSiswa = (int)($siswaTotal ?? 0);
                    ?>
                    <div class="text-muted small mt-2">Total siswa dalam cakupan: <strong><?= $totalSiswa ?></strong></div>
                </div>
            </div>
        </div>
    </div>


</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ------- 0) Pastikan Chart.js ada: auto load kalau belum ----------
    (function ensureChartJsLoaded(cb) {
        if (window.Chart) return cb();
        const id = 'chartjs-cdn-auto';
        if (document.getElementById(id)) {
            const wait = () => window.Chart ? cb() : setTimeout(wait, 80);
            return wait();
        }
        const s = document.createElement('script');
        s.id = id;
        s.src = "https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js";
        s.crossOrigin = "anonymous";
        s.referrerPolicy = "no-referrer";
        s.onload = cb;
        s.onerror = function() {
            console.error('Gagal memuat Chart.js dari CDN.');
        };
        document.head.appendChild(s);
    })(function bootCharts() {
        try {
            // ------- 1) Data PHP -> JS ----------
            const mapelLabels = <?= json_encode(array_values($mapelLabels ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            const mapelCounts = <?= json_encode(array_map('intval', $mapelCounts ?? []), JSON_UNESCAPED_UNICODE) ?>;

            const kelasLabels = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
            const kelasCounts = [
                <?= (int)($byClass[1] ?? 0) ?>,
                <?= (int)($byClass[2] ?? 0) ?>,
                <?= (int)($byClass[3] ?? 0) ?>,
                <?= (int)($byClass[4] ?? 0) ?>,
                <?= (int)($byClass[5] ?? 0) ?>,
                <?= (int)($byClass[6] ?? 0) ?>,
            ];

            // ------- 2) Global style ----------
            Chart.defaults.font.family = `'Inter',system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,'Noto Sans'`;
            Chart.defaults.color = '#6b7280';
            Chart.defaults.plugins.legend.display = false;
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,23,42,.92)';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 10;

            const ACCENTS = ['#0d6efd', '#3d8bfd', '#0b5ed7', '#6ea8fe', '#9ec5fe', '#cfe2ff', '#74a5ff'];

            // ------- 3) Util: destroy-if-exists + builder ----------
            const upsertBar = (canvas, labels, values, dsLabel) => {
                if (!canvas) return null;
                // Hapus chart lama jika ada (hindari tumpuk & memory leak)
                const prev = Chart.getChart(canvas);
                if (prev) prev.destroy();

                const ctx = canvas.getContext('2d');
                return new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: (labels && labels.length) ? labels : ['(kosong)'],
                        datasets: [{
                            label: dsLabel,
                            data: (labels && labels.length) ? values : [0],
                            backgroundColor: ((labels && labels.length) ? labels : ['(kosong)']).map((_, i) => ACCENTS[i % ACCENTS.length]),
                            borderWidth: 0,
                            borderRadius: 12,
                            maxBarThickness: 44
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        animation: {
                            duration: 200
                        },
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
            };

            // ------- 4) Render charts ----------
            const chartMapel = upsertBar(
                document.getElementById('ChartMapelBar'),
                Array.isArray(mapelLabels) ? mapelLabels : [],
                Array.isArray(mapelCounts) ? mapelCounts : [],
                'Jumlah'
            );

            const chartKelas = upsertBar(
                document.getElementById('ChartSiswaBar'),
                kelasLabels,
                kelasCounts,
                'Jumlah Siswa'
            );

            // ------- 5) Responsif dengan window.resize (debounce), BUKAN ResizeObserver ----------
            let rAF = 0;
            const onResize = () => {
                if (rAF) return;
                rAF = requestAnimationFrame(() => {
                    rAF = 0;
                    try {
                        chartMapel && chartMapel.resize();
                    } catch (e) {}
                    try {
                        chartKelas && chartKelas.resize();
                    } catch (e) {}
                });
            };
            window.addEventListener('resize', onResize);

            // Optional: re-render kalau ada tab/accordion yang baru dibuka
            document.addEventListener('shown.bs.tab', onResize);
            document.addEventListener('shown.bs.collapse', onResize);

        } catch (err) {
            console.error('Gagal inisialisasi chart:', err);
        }
    });
</script>
<?= $this->endSection() ?>