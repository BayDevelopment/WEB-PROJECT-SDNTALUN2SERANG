<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= esc($title ?? 'Login SDN') ?></title>

    <!-- Preconnect untuk font -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Styles kamu -->
    <link href="<?= base_url('assets/css/styles.css') ?>" rel="stylesheet" />

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <!-- Gunakan CSS Font Awesome (lebih ringan daripada JS loader) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" integrity="sha512-SRMMg8p5nqU9e6eJwz0+6vLqKXy5wqQqL4m9a2wQ7V0y9v4q0s7aSb+KQy0sXq6Q8C3G4Uu5m1iVQXnX3c1C0A==" crossorigin="anonymous">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;800&display=swap');

        :root {
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --blue-300: #93c5fd;
            --white: #fff;
            --ink: #0f172a;
        }

        * {
            box-sizing: border-box
        }

        body {
            font-family: "Poppins", sans-serif;
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(900px 600px at 10% 10%, rgba(147, 197, 253, .28), transparent 60%),
                radial-gradient(800px 500px at 90% 90%, rgba(59, 130, 246, .18), transparent 60%),
                linear-gradient(135deg, #0ea5e9 0%, #2563eb 50%, #1d4ed8 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Particles.js canvas */
        #particles-js {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .auth-wrap {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 32px 16px;
        }

        .card-login {
            border: 0;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .92), rgba(255, 255, 255, .88));
            backdrop-filter: saturate(150%) blur(6px);
            box-shadow: 0 20px 45px rgba(13, 38, 76, .18);
            overflow: hidden;
        }

        .card-header-modern {
            background: linear-gradient(135deg, var(--blue-500), var(--blue-700));
            color: var(--white);
            padding: 18px 22px;
        }

        .brand-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 0;
            font-weight: 800;
            letter-spacing: .2px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--blue-300), var(--blue-600));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 6px 16px rgba(37, 99, 235, .35);
        }

        .form-floating>.form-control {
            background: #fff;
            border: 1px solid #e5e7eb;
        }

        .form-floating>.form-control:focus {
            border-color: var(--blue-500);
            box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--blue-600), var(--blue-700));
            border: 0;
            box-shadow: 0 8px 20px rgba(29, 78, 216, .35);
        }

        .btn-primary:hover {
            filter: brightness(1.04)
        }

        .link-inverse {
            color: var(--blue-600);
            text-decoration: none;
        }

        .link-inverse:hover {
            text-decoration: underline;
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            opacity: .75;
            user-select: none;
        }

        .input-icon:hover {
            opacity: 1
        }

        footer.bg-light-modern {
            background: rgba(255, 255, 255, .85);
            backdrop-filter: saturate(140%) blur(4px);
            border-top: 1px solid rgba(255, 255, 255, .6);
        }

        @media (max-width:576px) {
            .brand-title {
                font-size: 1.05rem
            }
        }
    </style>
</head>

<body>
    <?php
    $s = session();
    $flashSuccess = $s->getFlashdata('sweet_success');
    $flashError   = $s->getFlashdata('sweet_error');
    $flashWarn    = null; // gak dipakai
    ?>

    <!-- Particles.js container -->
    <div id="particles-js" aria-hidden="true"></div>

    <div class="auth-wrap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-10 col-md-7 col-lg-5">
                    <div class="card card-login rounded-4">
                        <div class="card-header-modern">
                            <h3 class="brand-title mb-2">
                                <span class="brand-logo"><i class="fa-solid fa-school"></i></span>
                                <span>Welcome</span>
                            </h3>
                            <p class="text-center mb-0">Silahkan login dengan akun yang sudah disiapkan!</p>
                        </div>

                        <div class="card-body p-4 p-lg-4">
                            <form action="<?= base_url('auth/login') ?>" method="post" id="loginForm">
                                <?= csrf_field() ?>
                                <div class="form-floating mb-3">
                                    <input
                                        class="form-control"
                                        id="inputUsername"
                                        name="username"
                                        type="text"
                                        placeholder="Username.."
                                        autocomplete="username"
                                        inputmode="text"
                                        enterkeyhint="next"
                                        required
                                        autofocus>
                                    <label for="inputUsername">Username</label>
                                </div>
                                <div class="form-floating mb-3 position-relative">
                                    <input
                                        class="form-control"
                                        id="inputPassword"
                                        name="password"
                                        type="password"
                                        placeholder="Password"
                                        autocomplete="current-password"
                                        enterkeyhint="go"
                                        required
                                        aria-describedby="passwordHelp">
                                    <label for="inputPassword">Password</label>
                                    <button type="button" class="input-icon btn btn-link p-0 border-0" id="togglePass" aria-label="Tampilkan/sembunyikan password" aria-pressed="false">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <small id="passwordHelp" class="visually-hidden">Tekan ikon mata untuk menampilkan atau menyembunyikan password.</small>
                                </div>
                                <button type="submit" id="btnLogin" class="btn btn-primary w-100 py-2">
                                    <i class="fas fa-right-to-bracket me-1"></i> Masuk
                                </button>
                            </form>
                        </div>
                    </div><!-- /card -->
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-light-modern mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">© <?= date('Y') ?> Sekolah Dasar Negeri Talun 2 Kota Cilegon</div>
                <div>Developed by &middot; <a class="link-inverse" href="#">Alfiyan</a></div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script>
        // Util: load script dinamis
        function loadScript(src, attrs = {}) {
            return new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.defer = true;
                Object.entries(attrs).forEach(([k, v]) => s.setAttribute(k, v));
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            });
        }

        // Toggle password + a11y
        (function() {
            const toggle = document.getElementById('togglePass');
            const inputPass = document.getElementById('inputPassword');
            toggle?.addEventListener('click', () => {
                const isPass = inputPass.type === 'password';
                inputPass.type = isPass ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(isPass));
                toggle.innerHTML = isPass ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
            });
        })();

        // Cegah double submit
        (function() {
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('btnLogin');
            form?.addEventListener('submit', () => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';
            });
        })();

        // Particles.js: hanya kalau user tidak reduce-motion, dan muat setelah idle
        (function() {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) return;
            const initParticles = () => {
                loadScript('https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js')
                    .then(() => {
                        if (typeof particlesJS !== 'function') return;
                        particlesJS("particles-js", {
                            particles: {
                                number: {
                                    value: 70,
                                    density: {
                                        enable: true,
                                        value_area: 900
                                    }
                                },
                                color: {
                                    value: ["#ffffff", "#eaf2ff"]
                                },
                                shape: {
                                    type: "circle"
                                },
                                opacity: {
                                    value: 0.5
                                },
                                size: {
                                    value: 3,
                                    random: true
                                },
                                line_linked: {
                                    enable: true,
                                    distance: 140,
                                    color: "#eaf2ff",
                                    opacity: 0.35,
                                    width: 1
                                },
                                move: {
                                    enable: true,
                                    speed: 1.2,
                                    out_mode: "out"
                                }
                            },
                            interactivity: {
                                detect_on: "canvas",
                                events: {
                                    onhover: {
                                        enable: true,
                                        mode: "grab"
                                    },
                                    onclick: {
                                        enable: true,
                                        mode: "push"
                                    },
                                    resize: true
                                },
                                modes: {
                                    grab: {
                                        distance: 160,
                                        line_linked: {
                                            opacity: 0.6
                                        }
                                    },
                                    push: {
                                        particles_nb: 3
                                    }
                                }
                            },
                            retina_detect: true
                        });
                    })
                    .catch(() => {
                        /* diem aja kalau gagal, halaman tetap jalan */ });
            };
            if ('requestIdleCallback' in window) {
                requestIdleCallback(initParticles, {
                    timeout: 2000
                });
            } else {
                window.addEventListener('load', () => setTimeout(initParticles, 600));
            }
        })();

        // SweetAlert toast: muat library hanya jika ada flash message
        (function() {
            const msgSuccess = <?= json_encode($flashSuccess) ?>;
            const msgError = <?= json_encode($flashError) ?>;
            const flashWarn = <?= json_encode($flashWarn) ?>;
            if (!msgSuccess && !msgError && !flashWarn) return;

            loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11')
                .then(() => {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: t => {
                            t.onmouseenter = Swal.stopTimer;
                            t.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    if (msgSuccess) Toast.fire({
                        icon: 'success',
                        title: msgSuccess
                    });
                    if (msgError) Toast.fire({
                        icon: 'error',
                        title: msgError
                    });
                    if (flashWarn) Toast.fire({
                        icon: 'warning',
                        title: flashWarn
                    });
                })
                .catch(() => {
                    /* ignore */ });
        })();
    </script>
</body>

</html>