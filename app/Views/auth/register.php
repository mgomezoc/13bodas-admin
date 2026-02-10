<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Crear Cuenta | 13Bodas</title>

    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/favicon.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-dark: #141722;
            --brand-accent: #c89d67;
            --brand-primary: #2563eb;
            --brand-muted: #6b7280;
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top right, #2b3245 0%, #141722 50%, #0f1119 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .register-container {
            width: 100%;
            max-width: 980px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            display: grid;
            grid-template-columns: 360px 1fr;
        }

        .register-aside {
            background: linear-gradient(160deg, #171c2c 0%, #10131d 100%);
            color: #fff;
            padding: 42px 32px;
        }

        .register-aside img {
            width: 120px;
            margin-bottom: 24px;
        }

        .register-aside h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .register-aside p {
            color: #c9ceda;
            margin-bottom: 22px;
        }

        .register-benefits {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .register-benefits li {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            color: #d7dcef;
            margin-bottom: 14px;
            font-size: 0.95rem;
        }

        .register-benefits i {
            color: var(--brand-accent);
        }

        .register-form-wrap {
            padding: 36px;
        }

        .form-title {
            font-size: 1.65rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .form-subtitle {
            color: var(--brand-muted);
            margin-bottom: 26px;
        }

        .form-label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 10px;
            border-color: #d3d8e2;
            min-height: 46px;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }

        .form-check-input {
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.15rem;
        }

        .btn-register {
            min-height: 48px;
            border-radius: 10px;
            background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 100%);
            border: none;
            font-weight: 600;
        }

        .btn-register:hover {
            background: linear-gradient(90deg, #1e40af 0%, #1d4ed8 100%);
        }

        label.error {
            color: #dc2626;
            font-size: 0.85rem;
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
                max-width: 680px;
            }

            .register-aside {
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
            <p>Regístrate y crea tu invitación digital en modo DEMO para comenzar a personalizarla.</p>
            <ul class="register-benefits">
                <li><i class="bi bi-check2-circle"></i><span>Editor completo del evento desde tu panel.</span></li>
                <li><i class="bi bi-check2-circle"></i><span>Activa cuando estés listo con pago único de $800 MXN.</span></li>
                <li><i class="bi bi-check2-circle"></i><span>Acceso inmediato para elegir template y diseñar tu invitación.</span></li>
            </ul>
        </aside>

        <div class="register-form-wrap">
            <h2 class="form-title">Crear cuenta</h2>
            <p class="form-subtitle">Completa tus datos para iniciar con tu evento demo.</p>

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i>
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
