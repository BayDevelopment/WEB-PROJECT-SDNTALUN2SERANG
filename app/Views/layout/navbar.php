<nav class="sb-topnav navbar navbar-expand navbar-dark bg-native">
    <!-- Navbar Brand -->
    <a class="navbar-brand ps-3" href="<?= base_url('/') ?>">
        <img src="<?= base_url('assets/img/logo-panel-sdntalun2.svg') ?>" alt="Bootstrap" class="navbar-logo">
    </a>

    <!-- Sidebar Toggle -->
    <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Right -->
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <?php $role = session()->get('role'); ?>
                <?php if ($role === 'operator'): ?>
                    <li><a class="dropdown-item" href="<?= base_url('operator/profile') ?>">Settings</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">Logout</a></li>
                <?php elseif ($role === 'guru'): ?>
                    <li><a class="dropdown-item" href="<?= base_url('guru/profile') ?>">Settings</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">Logout</a></li>
                <?php elseif ($role === 'siswa'): ?>
                    <li><a class="dropdown-item" href="<?= base_url('siswa/profile') ?>">Settings</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">Logout</a></li>
                <?php endif; ?>
            </ul>
        </li>
    </ul>
</nav>