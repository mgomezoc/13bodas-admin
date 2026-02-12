<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Crear Cuenta | 13Bodas</title>

    <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/13bodas-logo-invitaciones-digitales.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/44d0a6ee3c.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        :root {
            --color-primary-50: #EBF5FF;
            --color-primary-200: #ADCFFF;
            --color-primary-400: #4A90D9;
            --color-primary-500: #2E6DB8;
            --color-primary-600: #1B5297;
            --color-primary-900: #0D1F33;
            --color-gray-100: #F1F4F7;
            --color-gray-200: #E4E8EC;
            --color-gray-300: #CDD3DA;
            --color-gray-500: #6B7785;
            --color-gray-700: #2D3748;
            --color-gray-800: #1A202C;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-lg: 0 20px 40px rgba(15, 31, 52, 0.18);
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at 10% 10%, #234a70 0%, #142d47 45%, #0d1f33 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .register-container {
            width: min(1100px, 100%);
            background: #fff;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            display: grid;
            grid-template-columns: 360px 1fr;
            border: 1px solid rgba(255,255,255,.16);
        }

        .register-aside {
            color: #fff;
            padding: 42px 32px;
            background:
                radial-gradient(120% 70% at 0% 0%, rgba(74,144,217,.38) 0%, rgba(74,144,217,0) 60%),
                linear-gradient(160deg, #142d47 0%, #0d1f33 100%);
        }

        .register-aside img {
            width: 138px;
            margin-bottom: 26px;
        }

        .register-aside h1 {
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .register-aside p {
            color: rgba(255,255,255,.82);
            margin-bottom: 28px;
            font-size: 1rem;
        }

        .register-benefits {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 14px;
        }

        .register-benefits li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            color: rgba(255,255,255,.93);
            font-size: 0.98rem;
            line-height: 1.45;
        }

        .register-benefits i {
            margin-top: 3px;
            color: #9ed0ff;
            font-size: 0.95rem;
        }

        .register-form-wrap {
            padding: 38px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-gray-800);
            margin-bottom: 6px;
        }

        .form-subtitle {
            color: var(--color-gray-500);
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 600;
            color: var(--color-gray-700);
            margin-bottom: 7px;
            font-size: .94rem;
        }

        .form-control {
            border-radius: 12px;
            border: 1.5px solid var(--color-gray-300);
            min-height: 48px;
            background: #fff;
            padding: .625rem .9rem;
        }

        .form-control::placeholder {
            color: #9ba5b0;
        }

        .form-control:focus {
            border-color: var(--color-primary-400);
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.16);
        }

        .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            margin-top: .2rem;
            border-color: var(--color-gray-300);
        }

        .form-check-input:checked {
            background-color: var(--color-primary-500);
            border-color: var(--color-primary-500);
        }

        .form-check-label {
            color: var(--color-gray-700);
        }

        .form-check-label a {
            color: var(--color-primary-600);
            font-weight: 500;
        }

        .btn-register {
            min-height: 50px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-600));
            border: none;
            font-weight: 700;
            font-size: 1.03rem;
            letter-spacing: .01em;
            box-shadow: 0 10px 20px rgba(27, 82, 151, 0.22);
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #255ea0, #174783);
            transform: translateY(-1px);
        }

        .text-muted {
            color: var(--color-gray-500) !important;
        }

        .text-muted a {
            color: var(--color-primary-600);
            font-weight: 600;
            text-decoration: none;
        }

        .alert {
            border-radius: 12px;
            border: none;
            border-left: 4px solid;
            font-size: .92rem;
        }

        .alert-danger {
            background: #FFF5F5;
            border-left-color: #E53E3E;
            color: #B83232;
        }

        label.error {
            color: #dc2626;
            font-size: .82rem;
            margin-top: 6px;
            display: block;
        }

        .flatpickr-input[readonly] {
            background: #fff;
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .register-container {
                grid-template-columns: 1fr;
                max-width: 700px;
            }

            .register-aside {
                padding: 28px;
            }

            .register-form-wrap {
                padding: 28px;
            }
        }
    </style>
</head>
<body>
    <section class="register-container">
        <aside class="register-aside">
            <img src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>" alt="13Bodas">
            <h1>Tu evento, listo en minutos</h1>
            <p>Regístrate y lanza tu invitación digital con una experiencia clara, moderna y lista para personalizar.</p>
            <ul class="register-benefits">
                <li><i class="fa-solid fa-circle-check"></i><span>Editor completo del evento desde tu panel.</span></li>
                <li><i class="fa-solid fa-bolt"></i><span>Activa cuando estés listo con pago único de $800 MXN.</span></li>
                <li><i class="fa-solid fa-wand-magic-sparkles"></i><span>Acceso inmediato para elegir template y diseñar tu invitación.</span></li>
            </ul>
        </aside>

        <div class="register-form-wrap">
            <h2 class="form-title">Crear cuenta</h2>
            <p class="form-subtitle">Completa tus datos para iniciar con tu evento demo.</p>

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <?= esc((string) session('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ((array) session('errors') as $error): ?>
                            <li><?= esc((string) $error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="<?= site_url(route_to('register.store')) ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nombre completo *</label>
                        <input class="form-control" id="name" name="name" type="text" value="<?= esc(old('name')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email *</label>
                        <input class="form-control" id="email" name="email" type="email" value="<?= esc(old('email')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input class="form-control" id="phone" name="phone" type="text" value="<?= esc(old('phone')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="couple_title">Título del evento *</label>
                        <input class="form-control" id="couple_title" name="couple_title" type="text" placeholder="Ej: Cesary & Angie" value="<?= esc(old('couple_title')) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password">Contraseña *</label>
                        <input class="form-control" id="password" name="password" type="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirm">Confirmar contraseña *</label>
                        <input class="form-control" id="password_confirm" name="password_confirm" type="password" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="event_date">Fecha del evento *</label>
                        <input class="form-control" id="event_date" name="event_date" type="text" value="<?= esc(old('event_date')) ?>" required>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" id="terms" name="terms" type="checkbox" value="1" required>
                            <label class="form-check-label" for="terms">
                                Acepto los <a href="<?= base_url('terminos') ?>" target="_blank" rel="noopener">términos y condiciones</a>
                            </label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-primary btn-register w-100" type="submit">Crear cuenta gratis</button>
                    </div>
                </div>
            </form>

            <p class="text-muted text-center mt-3 mb-0">
                ¿Ya tienes cuenta? <a href="<?= base_url('admin/login') ?>">Inicia sesión</a>
            </p>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/localization/messages_es.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        flatpickr('#event_date', {
            locale: 'es',
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disableMobile: true,
            allowInput: false,
        });

        $('#registerForm').validate({
            errorElement: 'label',
            errorClass: 'error',
            rules: {
                name: { required: true, minlength: 3, maxlength: 120 },
                email: { required: true, email: true },
                phone: { maxlength: 30 },
                couple_title: { required: true, minlength: 3, maxlength: 255 },
                password: { required: true, minlength: 6 },
                password_confirm: { required: true, equalTo: '#password' },
                event_date: { required: true, dateISO: true },
                terms: { required: true },
            },
        });
    </script>
</body>
</html>
