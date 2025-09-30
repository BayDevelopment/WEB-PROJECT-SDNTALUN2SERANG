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
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active"><?= esc($sub_judul) ?></li>
            </ol>
        </div>
        <div class="text-muted small mt-3 mt-sm-0">
            Total User: <strong><?= isset($d_user) ? number_format(count($d_user), 0, ',', '.') : 0 ?></strong>
        </div>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row g-2 align-items-center mb-3 toolbar">
                <!-- Filter (Form GET) -->
                <div class="col-12 col-md-9">
                    <form id="filterForm" method="get" class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group input-group-sm search-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input
                                    id="searchUser"
                                    type="text"
                                    name="q"
                                    value="<?= esc($q ?? '') ?>"
                                    class="form-control"
                                    placeholder="Cari data user (username/email)..."
                                    aria-label="Pencarian user"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <?php $r = strtolower(trim((string)($role ?? ''))); ?>
                            <select id="filterRole" name="role" class="form-select form-select-sm" aria-label="Filter role">
                                <option value="" <?= $r === '' ? 'selected' : '' ?>>Semua Role</option>
                                <option value="operator" <?= $r === 'operator' ? 'selected' : '' ?>>Operator</option>
                                <option value="guru" <?= $r === 'guru' ? 'selected' : '' ?>>Guru</option>
                                <option value="siswa" <?= $r === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah (di luar form) -->
                <?php if (!empty($d_user)): ?>
                    <div class="col-12 col-md-3 text-md-end">
                        <a href="<?= base_url('operator/tambah-user') ?>" class="btn btn-gradient rounded-pill btn-sm py-2 w-100 w-md-auto">
                            <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Tabel -->
            <?php if (!empty($d_user)): ?>
                <div class="table-responsive">
                    <table id="tableDataUser" class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="w-40px">No</th>
                                <th>Foto</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableUser">
                            <?php if (!empty($d_user) && is_array($d_user)): ?>
                                <?php
                                $no         = 1;
                                $uploadsRel = 'assets/img/uploads/';
                                $defaultRel = 'assets/img/user.png';
                                ?>
                                <?php foreach ($d_user as $u): ?>
                                    <?php
                                    $idUser   = (int)($u['id_user'] ?? 0);
                                    $username = (string)($u['username'] ?? '-');
                                    $email    = (string)($u['email'] ?? '-');
                                    $role     = (string)($u['role'] ?? '-');                 // langsung dari data
                                    $isActive = (int)($u['is_active'] ?? 0);                 // 1/0

                                    // Foto: upload jika ada, selain itu default (tanpa cek file bertele-tele)
                                    $photo = trim((string)($u['photo'] ?? $u['foto'] ?? ''));
                                    if ($photo !== '' && preg_match('~^https?://~i', $photo)) {
                                        $img = $photo;
                                    } elseif ($photo !== '') {
                                        $img = base_url($uploadsRel . $photo);
                                    } else {
                                        $img = base_url($defaultRel);
                                    }

                                    // Status badge sederhana
                                    $statusText  = $isActive === 1 ? 'Aktif' : 'Nonaktif';
                                    $statusClass = $isActive === 1 ? 'bg-success' : 'bg-secondary';
                                    ?>
                                    <tr
                                        data-username="<?= esc($username) ?>"
                                        data-email="<?= esc($email) ?>"
                                        data-role="<?= esc($role) ?>"
                                        data-status="<?= esc((string)$isActive) ?>">
                                        <td class="text-muted"><?= $no++ ?>.</td>
                                        <td>
                                            <div class="avatar-wrap">
                                                <img src="<?= esc($img) ?>" alt="Foto <?= esc($username) ?>" class="avatar-40 rounded-circle">
                                            </div>
                                        </td>
                                        <td class="fw-semibold"><?= esc($username) ?></td>
                                        <td><span class="font-monospace"><?= esc($email) ?></span></td>
                                        <td><?= esc($role) ?></td>
                                        <td><span class="badge <?= esc($statusClass) ?>"><?= esc($statusText) ?></span></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-danger"
                                                    onclick="confirmDeleteUser('<?= esc((string)$idUser, 'js') ?>')"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                                <a href="<?= base_url('operator/detail-user/' . urlencode((string)$idUser)) ?>"
                                                    class="btn btn-outline-secondary" title="Detail">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('operator/edit-user/' . urlencode((string)$idUser)) ?>"
                                                    class="btn btn-primary" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Data pengguna tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            <?php else: ?>
                <!-- Empty state -->
                <div class="empty-card text-center p-5">
                    <img src="<?= base_url('assets/img/empty-box.png') ?>" class="empty-illustration mb-3" alt="Kosong">
                    <h5 class="mb-1">Belum ada data guru</h5>
                    <p class="text-muted mb-3">Tambahkan data guru pertama Anda untuk mulai mengelola informasi.</p>
                    <a href="<?= base_url('operator/tambah-user') ?>" class="btn btn-gradient rounded-pill btn-sm py-2">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const q = document.getElementById('searchUser');
        const role = document.getElementById('filterRole');

        // reset page ke 1 sebelum submit (kalau kamu pakai pagination via ?page=)
        function resetPageParam() {
            const pageInput = form.querySelector('input[name="page"]');
            if (pageInput) pageInput.value = '1';
        }

        // debounce input
        let t = null;
        q.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(() => {
                resetPageParam();
                form.submit();
            }, 350);
        });

        role.addEventListener('change', function() {
            resetPageParam();
            form.submit();
        });
    });
</script>
<?= $this->endSection() ?>