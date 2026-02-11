<?php declare(strict_types=1); ?>
<?php
$homeUrl = '/';
$registerUrl = '/register';
$contactUrl = '/#contacto';
$plansUrl = '/#paquetes';
$servicesUrl = '/#servicios';

if (function_exists('site_url') && function_exists('route_to')) {
    $homeUrl = site_url(route_to('home'));
    $registerUrl = site_url(route_to('register.index'));
    $contactUrl = site_url(route_to('home')) . '#contacto';
    $plansUrl = site_url(route_to('home')) . '#paquetes';
    $servicesUrl = site_url(route_to('home')) . '#servicios';
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Página no encontrada - 13Bodas</title>
    <meta name="robots" content="noindex, follow">
    <style>
        :root { --bg:#0b1220; --text:#e5e7eb; --muted:#9ca3af; --primary:#3b82f6; --primary-soft:#1d4ed8; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Inter,Arial,Helvetica,sans-serif; background:radial-gradient(circle at top,#17233e 0%,var(--bg) 55%); color:var(--text); display:grid; place-items:center; padding:20px; }
        .card { width:min(920px,100%); background:linear-gradient(180deg,#111a2c 0%,#0f172a 100%); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:28px; box-shadow:0 20px 50px rgba(0,0,0,.35); }
        .tag { display:inline-block; padding:6px 10px; border-radius:999px; background:rgba(59,130,246,.16); color:#bfdbfe; font-size:12px; }
        h1 { margin:14px 0 8px; font-size:clamp(32px,6vw,52px); line-height:1; }
        p { margin:0 0 14px; color:var(--muted); font-size:16px; line-height:1.5; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:18px; }
        .btn { text-decoration:none; border-radius:10px; padding:12px 16px; font-weight:600; font-size:14px; transition:transform .12s ease,opacity .2s; }
        .btn:hover { transform:translateY(-1px); }
        .btn-primary { background:linear-gradient(135deg,var(--primary),var(--primary-soft)); color:#fff; }
        .btn-ghost { border:1px solid rgba(255,255,255,.18); color:var(--text); background:transparent; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:12px; margin-top:22px; }
        .item { border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:12px; background:rgba(255,255,255,.02); }
        .item strong { display:block; margin-bottom:6px; }
        .item span { color:var(--muted); font-size:14px; }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="error-title">
        <span class="tag">Error 404</span>
        <h1 id="error-title">Esta página no existe</h1>
        <p>Puede que el enlace esté desactualizado o que la URL haya cambiado. Si estabas buscando planes o registro, te dejamos accesos directos.</p>

        <div class="actions">
            <a class="btn btn-primary" href="<?= htmlspecialchars((string) $registerUrl, ENT_QUOTES, 'UTF-8') ?>">Crear cuenta gratis</a>
            <a class="btn btn-ghost" href="<?= htmlspecialchars((string) $homeUrl, ENT_QUOTES, 'UTF-8') ?>">Ir al inicio</a>
            <a class="btn btn-ghost" href="<?= htmlspecialchars((string) $contactUrl, ENT_QUOTES, 'UTF-8') ?>">Enviar formulario</a>
        </div>

        <section class="grid" aria-label="Acciones sugeridas">
            <article class="item">
                <strong>Ver planes</strong>
                <span>Conoce opciones y empieza con demo gratis.</span><br>
                <a class="btn btn-ghost" style="margin-top:8px;display:inline-block;" href="<?= htmlspecialchars((string) $plansUrl, ENT_QUOTES, 'UTF-8') ?>">Ir a planes</a>
            </article>
            <article class="item">
                <strong>¿Qué es RSVP?</strong>
                <span>Aprende cómo confirmar asistencia en línea.</span><br>
                <a class="btn btn-ghost" style="margin-top:8px;display:inline-block;" href="<?= htmlspecialchars((string) $servicesUrl, ENT_QUOTES, 'UTF-8') ?>">Ver explicación</a>
            </article>
            <article class="item">
                <strong>MagicCam</strong>
                <span>Explora experiencias interactivas para eventos.</span><br>
                <a class="btn btn-ghost" style="margin-top:8px;display:inline-block;" href="https://magiccam.13bodas.com" rel="noopener" target="_blank">Ver demo</a>
            </article>
        </section>

        <?php if ((defined('ENVIRONMENT') ? ENVIRONMENT : 'production') !== 'production' && isset($message) && $message !== ''): ?>
            <p style="margin-top:18px;font-size:13px;opacity:.8;"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </main>
</body>
</html>
