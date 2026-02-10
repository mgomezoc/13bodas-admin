<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | 13Bodas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 680px;">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Crea tu evento demo</h1>

                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger"><?= esc((string) session('error')) ?></div>
                <?php endif; ?>

                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ((array) session('errors') as $error): ?>
                                <li><?= esc((string) $error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url(route_to('register.store')) ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Nombre completo</label>
                        <input class="form-control" id="name" name="name" type="text" value="<?= esc(old('name')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" value="<?= esc(old('email')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input class="form-control" id="phone" name="phone" type="text" value="<?= esc(old('phone')) ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password">Contraseña</label>
                            <input class="form-control" id="password" name="password" type="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                            <input class="form-control" id="password_confirm" name="password_confirm" type="password" required>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label" for="couple_title">Título del evento</label>
                        <input class="form-control" id="couple_title" name="couple_title" type="text" value="<?= esc(old('couple_title')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="event_date">Fecha del evento</label>
                        <input class="form-control" id="event_date" name="event_date" type="date" value="<?= esc(old('event_date')) ?>" required>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="terms" name="terms" type="checkbox" value="1" required>
                        <label class="form-check-label" for="terms">Acepto términos y condiciones</label>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Crear cuenta gratis</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
