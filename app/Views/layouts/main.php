<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title><?= $this->renderSection('title') ?> | 13Bodas</title>
    <meta name="description" content="<?= $this->renderSection('description') ?? 'Invitaciones digitales elegantes y filtros de realidad aumentada personalizados para bodas, XV años y eventos.' ?>">
    <?= $this->renderSection('meta_tags') ?>
    
    <meta name="author" content="13Bodas">
    <meta name="robots" content="<?= $this->renderSection('robots') ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' ?>">
    <link rel="canonical" href="<?= esc(current_url()) ?>">
    <link rel="alternate" hreflang="es-MX" href="<?= esc(current_url()) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= esc(base_url()) ?>">
    <link rel="alternate" type="text/plain" title="LLMs" href="<?= esc(base_url('llms.txt')) ?>">

    <!-- Geo Tags -->
    <meta name="geo.region" content="MX">
    <meta name="geo.placename" content="Monterrey">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="13Bodas">
    <meta property="og:title" content="<?= $this->renderSection('og_title') ?? $this->renderSection('title') . ' | 13Bodas' ?>">
    <meta property="og:description" content="<?= $this->renderSection('og_description') ?? 'Invitaciones web y filtros de realidad aumentada personalizados para bodas, XV años y eventos.' ?>">
    <meta property="og:url" content="<?= esc(current_url()) ?>">
    <meta property="og:image" content="<?= esc(base_url('img/og-image-13bodas.jpg')) ?>">
    <meta property="og:image:alt" content="13Bodas plataforma para invitaciones digitales y RSVP">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="es_MX">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $this->renderSection('twitter_title') ?? '13Bodas | Invitaciones Digitales y Filtros AR' ?>">
    <meta name="twitter:description" content="<?= $this->renderSection('twitter_description') ?? 'Invitaciones web y filtros AR personalizados para bodas, XV años y eventos.' ?>">
    <meta name="twitter:image" content="<?= esc(base_url('img/og-image-13bodas.jpg')) ?>">
    <meta name="twitter:site" content="@13bodas">

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="<?= esc(base_url('favicon-96x96.png')) ?>" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('favicon.svg')) ?>">
    <link rel="shortcut icon" href="<?= esc(base_url('favicon.ico')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= esc(base_url('apple-touch-icon.png')) ?>">
    <meta name="apple-mobile-web-app-title" content="13Bodas">
    <link rel="manifest" href="<?= esc(base_url('site.webmanifest')) ?>">
    <meta name="theme-color" content="#0a0510">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Google Analytics -->
    <?php if (ENVIRONMENT === 'production'): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SBKT31SXZX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-SBKT31SXZX');
    </script>
    <?php endif; ?>

    <!-- GSAP -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= esc(base_url('css/style.css')) ?>">
    
    <?= $this->renderSection('styles') ?>

    <!-- Structured Data -->
    <?= $this->renderSection('structured_data') ?>
</head>

<body class="<?= $this->renderSection('body_class') ?>">
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <!-- WhatsApp Flotante -->
    <?= $this->include('partials/whatsapp_float') ?>

    <!-- Header/Nav -->
    <?= $this->renderSection('header') ?>

    <!-- Main Content -->
    <main id="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <?= $this->renderSection('footer') ?>

    <!-- Scripts -->
    <script defer src="<?= esc(base_url('js/app.js')) ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
