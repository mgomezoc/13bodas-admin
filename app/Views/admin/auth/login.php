<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>Iniciar Sesión | 13Bodas Admin</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/13bodas-logo-invitaciones-digitales.svg') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/44d0a6ee3c.js" crossorigin="anonymous"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-primary-400: #4A90D9;
            --color-primary-500: #2E6DB8;
            --color-primary-600: #1B5297;
            --color-primary-700: #1B3A5C;
            --color-primary-900: #0D1F33;
            --color-gray-100: #F1F4F7;
            --color-gray-200: #E4E8EC;
            --color-gray-300: #CDD3DA;
            --color-gray-500: #6B7785;
            --color-gray-700: #2D3748;
            --color-gray-800: #1A202C;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background:
                radial-gradient(120% 80% at 90% 0%, rgba(74, 144, 217, 0.26) 0%, rgba(74, 144, 217, 0) 58%),
                linear-gradient(140deg, #0d1f33 0%, #142d47 55%, #0f243a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1fr 440px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(7, 21, 34, 0.3);
        }

        .login-aside {
            padding: 42px;
            color: #fff;
            background:
                radial-gradient(80% 60% at 10% 10%, rgba(116, 180, 240, 0.28) 0%, rgba(116, 180, 240, 0) 70%),
                linear-gradient(170deg, #193c5e 0%, #0d1f33 100%);
        }

        .login-aside img {
            max-width: 150px;
            margin-bottom: 28px;
        }

        .login-aside h2 {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .login-aside p {
            color: rgba(255, 255, 255, 0.83);
            margin-bottom: 22px;
            max-width: 34ch;
        }

        .login-highlights {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 12px;
        }

        .login-highlights li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.96rem;
        }

        .login-highlights i {
            color: #9fd0ff;
            font-size: 0.9rem;
        }

        .login-card {
            background: #fff;
            padding: 42px;
        }

        .login-title {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2rem;
            line-height: 1.1;
            margin-bottom: .45rem;
            color: var(--color-gray-800);
        }

        .login-subtitle {
            color: var(--color-gray-500);
            margin-bottom: 1.8rem;
            font-size: .96rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.89rem;
            color: var(--color-gray-700);
            margin-bottom: 0.42rem;
        }

        .form-control {
            border-radius: 12px;
            border: 1.5px solid var(--color-gray-300);
            min-height: 48px;
            padding: .72rem .95rem;
        }

        .form-control:focus {
            border-color: var(--color-primary-400);
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.16);
        }

        .input-group-text {
            background: var(--color-gray-100);
            border: 1.5px solid var(--color-gray-300);
            color: var(--color-gray-500);
        }

        .input-group>.form-control,
        .input-group>.input-group-text {
            border-radius: 12px;
        }

        .btn-login {
            width: 100%;
            padding: 0.9rem;
            font-weight: 700;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-600));
            border: none;
            letter-spacing: .01em;
            color: #fff;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #255ea0, #174783);
            transform: translateY(-1px);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.25rem;
            color: var(--color-gray-500);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: var(--color-primary-500);
        }

        .alert {
            border-radius: 12px;
            border: none;
            border-left: 4px solid;
            font-size: 0.9rem;
        }

        .alert-danger {
            border-left-color: #E53E3E;
            background: #FFF5F5;
            color: #B83232;
        }

        .alert-success {
            border-left-color: #38A169;
            background: #F0FFF4;
            color: #2F855A;
        }

        .password-toggle {
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 560px;
            }

            .login-aside {
                padding: 28px;
            }

            .login-card {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
<div class="login-container">
    <aside class="login-aside">
        <img src="<?= base_url('img/13bodas-logo-blanco-transparente.svg') ?>" alt="13Bodas">
        <h2>Acceso seguro</h2>
        <p>Administra eventos, invitados y confirmaciones desde un panel más claro y rápido.</p>
        <ul class="login-highlights">
            <li><i class="fa-solid fa-shield-heart"></i><span>Panel privado para tu operación diaria</span></li>
            <li><i class="fa-solid fa-chart-simple"></i><span>Métricas y estados en tiempo real</span></li>
            <li><i class="fa-solid fa-bolt"></i><span>Flujos rápidos para edición de eventos</span></li>
        </ul>
    </aside>

    <div class="login-card">
        <h1 class="login-title">Panel de Administración</h1>
        <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <?= esc((string) session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc((string) session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/login') ?>" method="POST" id="loginForm">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="email">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fa-solid fa-at"></i>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="tu@email.com"
                        value="<?= esc((string) old('email')) ?>"
                        required
                        autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required>
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <i class="fa-solid fa-right-to-bracket me-2"></i>
                Iniciar Sesión
            </button>
        </form>

        <a href="<?= base_url('/') ?>" class="back-link">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Volver al sitio principal
        </a>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>
</body>

</html>
