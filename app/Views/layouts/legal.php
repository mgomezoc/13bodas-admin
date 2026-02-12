<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= $this->renderSection('title') ?> | 13Bodas</title>
    <meta name="description" content="<?= $this->renderSection('description') ?>">
    <meta name="robots" content="noindex, follow">

    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/favicon.svg') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('img/favicon-32x32.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('img/apple-touch-icon.png') ?>">
    <meta name="theme-color" content="#0D1F33">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>

<body class="legal-body">
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <!-- Header simple -->
    <header class="header header-legal">
        <div class="container legal-header-container">
            <a href="<?= base_url('/') ?>" class="nav-logo" aria-label="13Bodas - Volver al inicio">
                <img src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>" alt="13Bodas" width="140" height="40">
            </a>
            <a href="<?= base_url('/') ?>" class="legal-back-link">← Volver al sitio</a>
        </div>
    </header>

    <main id="main-content" class="legal-main section-padding">
        <div class="container legal-container">
            <?= $this->renderSection('content') ?>

            <div class="legal-back-cta">
                <a href="<?= base_url('/') ?>" class="btn btn-secondary">Volver al sitio principal</a>
            </div>
        </div>
    </main>

    <!-- Footer simple -->
    <footer class="footer footer-legal">
        <div class="container footer-container">
            <div class="footer-bottom">
                <p>&copy; <span id="year"></span> 13Bodas. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            var yearEl = document.getElementById('year');
            if (yearEl) {
                yearEl.textContent = new Date().getFullYear();
            }
        })();
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
