<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Core</div>
            <?php $role = (string) session('role'); ?>

            <?php if ($role === 'operator'): ?>
                <a class="nav-link <?= ($nav_link === 'Dashboard' ? 'active' : '') ?>" href="<?= base_url('operator/dashboard') ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
            <?php elseif ($role === 'guru'): ?>
                <a class="nav-link <?= ($nav_link === 'Dashboard' ? 'active' : '') ?>" href="<?= base_url('guru/dashboard') ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
            <?php elseif ($role === 'siswa'): ?>
                <a class="nav-link <?= ($nav_link === 'Dashboard' ? 'active' : '') ?>" href="<?= base_url('siswa/dashboard') ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
            <?php endif; ?>

            <div class="sb-sidenav-menu-heading">Master Data</div>

            <?php if ($role === 'operator'): ?>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseData" aria-expanded="false" aria-controls="collapseData">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                    Data
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                <div class="collapse" id="collapseData" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?= ($nav_link === 'Data User' ? 'active' : '') ?> mb-2" href="<?= base_url('operator/data-user') ?>">
                            <i class="fa-solid fa-users me-2"></i> Data User
                        </a>
                        <a class="nav-link <?= ($nav_link === 'Data Siswa' ? 'active' : '') ?> mb-2" href="<?= base_url('operator/data-siswa') ?>">
                            <i class="fa-regular fa-id-badge me-2"></i> Data Siswa
                        </a>
                        <a class="nav-link <?= ($nav_link === 'Data Guru' ? 'active' : '') ?> mb-2" href="<?= base_url('operator/data-guru') ?>">
                            <i class="fa-solid fa-chalkboard-user me-2"></i> Data Guru
                        </a>
                    </nav>
                </div>

            <?php elseif ($role === 'guru'): ?>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseData" aria-expanded="false" aria-controls="collapseData">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                    Data
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                <div class="collapse" id="collapseData" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="<?= base_url('operator/profile') ?>">
                            <i class="fa-regular fa-id-badge me-2"></i> Data Siswa
                        </a>
                    </nav>
                </div>
            <?php elseif ($role === 'siswa'): ?>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseData" aria-expanded="false" aria-controls="collapseData">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                    Data
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                <div class="collapse" id="collapseData" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="<?= base_url('operator/profile') ?>">
                            <i class="fa-regular fa-id-badge me-2"></i> Data Diri
                        </a>
                        <a class="nav-link" href="<?= base_url('operator/guru') ?>">
                            <i class="fa-solid fa-chalkboard-user me-2"></i> Data Guru
                        </a>
                    </nav>
                </div>

            <?php endif; ?>


            <div class="sb-sidenav-menu-heading">Lainnya</div>

            <?php if ($role === "operator"): ?>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLaporan" aria-expanded="false" aria-controls="collapseLaporan">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Laporan
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLaporan" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link text-capitalize" href=""><span><i class="fa-regular fa-id-badge me-2"></i></span> Data siswa</a>
                        <a class="nav-link text-capitalize" href=""><span><i class="fa-solid fa-chalkboard-user me-2"></i></span> Data Guru</a>
                        <a class="nav-link text-capitalize" href=""><span><i class="fa-solid fa-trophy kpi-icon me-2"></i></span> Nilai Siswa</a>
                    </nav>
                </div>
            <?php elseif ($role === "guru"): ?>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLaporan" aria-expanded="false" aria-controls="collapseLaporan">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Laporan
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLaporan" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link text-capitalize" href=""><span><i class="fa-regular fa-id-badge me-2"></i></span> Data siswa</a>
                        <a class="nav-link text-capitalize" href=""><span><i class="fa-solid fa-chalkboard-user me-2"></i></span> Data Guru</a>
                    </nav>
                </div>
            <?php elseif ($role === "siswa"): ?>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLaporan" aria-expanded="false" aria-controls="collapseLaporan">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Laporan
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLaporan" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link text-capitalize" href=""><span><i class="fa-solid fa-trophy kpi-icon me-2"></i></span> Nilai Siswa</a>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="sb-sidenav-footer">
        <div class="small text-white">Logged in as:</div>
        <p class="text-white"><?= session()->get('role') ?></p>
    </div>
</nav>