<?php

declare(strict_types=1);

// ================================================================
// TEMPLATE: LIEBE — app/Views/templates/liebe/index.php
// Versión: 2.0 — Con soporte completo de datos dinámicos + fallbacks
// ================================================================

// --- Base data ---
$event = $event ?? [];
$template = $template ?? [];
$theme = $theme ?? [];
$modules = $modules ?? [];
$templateMeta = $templateMeta ?? [];
$mediaByCategory = $mediaByCategory ?? [];
$galleryAssets = $galleryAssets ?? [];
$registryItems = $registryItems ?? [];
$registryStats = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0];
$menuOptions = $menuOptions ?? [];
$weddingParty = $weddingParty ?? [];
$faqs = $faqs ?? ($event['faqs'] ?? []);
$scheduleItems = $scheduleItems ?? ($event['schedule_items'] ?? []);
$eventLocations = $eventLocations ?? [];
$blogPosts = $blogPosts ?? [];

// --- Defaults from template meta_json ---
// Retrocompatibilidad: soportar tanto estructura plana antigua como nueva con sub-objetos
$rawDefaults = $templateMeta['defaults'] ?? [];
if (isset($rawDefaults['copy']) && is_array($rawDefaults['copy'])) {
    // Nueva estructura: defaults.copy + defaults.assets
    $defaults = $rawDefaults['copy'];
    $tplAssets = $rawDefaults['assets'] ?? [];
} else {
    // Estructura legacy: defaults plano + assets como hermano
    $defaults = $rawDefaults;
    $tplAssets = $templateMeta['assets'] ?? [];
}
// Section visibility (override desde theme_config del evento)
$sectionVisibility = $theme['sections'] ?? ($templateMeta['section_visibility'] ?? []);

$slug = esc($event['slug'] ?? '');
$coupleTitle = esc($event['couple_title'] ?? 'Nuestra Boda');

$startRaw = $event['event_date_start'] ?? null;
$endRaw = $event['event_date_end'] ?? null;
$rsvpDeadline = $event['rsvp_deadline'] ?? null;

$primaryLocation = $eventLocations[0] ?? [];
$venueName = esc($primaryLocation['name'] ?? ($event['venue_name'] ?? ''));
$venueAddr = esc($primaryLocation['address'] ?? ($event['venue_address'] ?? ''));
$lat = $primaryLocation['geo_lat'] ?? ($event['venue_geo_lat'] ?? '');
$lng = $primaryLocation['geo_lng'] ?? ($event['venue_geo_lng'] ?? '');

// --- Helper functions ---
function formatDateLabel(?string $dt, string $fmt = 'd M Y'): string
{
    if (!$dt) {
        return '';
    }
    try {
        return date($fmt, strtotime($dt));
    } catch (\Throwable $e) {
        return '';
    }
}

function formatTimeLabel(?string $dt, string $fmt = 'H:i'): string
{
    if (!$dt) {
        return '';
    }
    try {
        return date($fmt, strtotime($dt));
    } catch (\Throwable $e) {
        return '';
    }
}

function formatScheduleTime(array $item): string
{
    if (!empty($item['time'])) {
        return esc((string)$item['time']);
    }
    $start = $item['starts_at'] ?? null;
    $end = $item['ends_at'] ?? null;
    if (!$start) {
        return '';
    }
    $startLabel = formatTimeLabel($start);
    $endLabel = $end ? formatTimeLabel($end) : '';
    return trim($startLabel . ($endLabel ? ' - ' . $endLabel : ''));
}

$eventDateLabel = formatDateLabel($startRaw, 'd M Y');
$eventDateISO = $startRaw ? date('c', strtotime($startRaw)) : '';
$eventTimeRange = trim(formatTimeLabel($startRaw) . ($endRaw ? ' - ' . formatTimeLabel($endRaw) : ''));
$rsvpDeadlineLabel = formatDateLabel($rsvpDeadline, 'd M Y');

$assetsBase = base_url('templates/liebe');

// --- Theme (schema_json + theme_config overrides) ---
$schema = [];
if (!empty($template['schema_json'])) {
    $schema = json_decode($template['schema_json'], true) ?: [];
}
// Retrocompatibilidad: soportar tanto estructura plana como anidada (theme_defaults)
$themeDefaults = $schema['theme_defaults'] ?? [];
$schemaFonts = !empty($themeDefaults['fonts'])
    ? [$themeDefaults['fonts']['heading'] ?? 'Great Vibes', $themeDefaults['fonts']['body'] ?? 'Dosis']
    : ($schema['fonts'] ?? ['Great Vibes', 'Dosis']);
$schemaColors = !empty($themeDefaults['colors'])
    ? [$themeDefaults['colors']['primary'] ?? '#86B1A1', $themeDefaults['colors']['accent'] ?? '#F5F0EB']
    : ($schema['colors'] ?? ['#86B1A1', '#F5F0EB']);

// Retrocompatibilidad: soportar tanto estructura plana como anidada
$fontHeading = $theme['fonts']['heading'] ?? ($theme['font_heading'] ?? ($schemaFonts[0] ?? 'Great Vibes'));
$fontBody = $theme['fonts']['body'] ?? ($theme['font_body'] ?? ($schemaFonts[1] ?? 'Dosis'));
$colorPrimary = $theme['colors']['primary'] ?? ($theme['primary'] ?? ($schemaColors[0] ?? '#86B1A1'));
$colorAccent = $theme['colors']['accent'] ?? ($theme['accent'] ?? ($schemaColors[1] ?? '#F5F0EB'));

// --- Module finder (busca por module_type, NO por code) ---
function findModule(array $modules, string $type): ?array
{
    foreach ($modules as $m) {
        if (($m['module_type'] ?? '') === $type) {
            return $m;
        }
    }
    return null;
}

// --- Couple module (lovely.couple or couple_info) ---
$modCouple = findModule($modules, 'lovely.couple') ?? findModule($modules, 'couple_info');
$couplePayload = [];
if ($modCouple && !empty($modCouple['content_payload'])) {
    $raw = $modCouple['content_payload'];
    $couplePayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

// --- Copy module (lovely.copy) ---
$modCopy = findModule($modules, 'lovely.copy');
$copyPayload = [];
if ($modCopy && !empty($modCopy['content_payload'])) {
    $raw = $modCopy['content_payload'];
    $copyPayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

// --- Story module ---
$modStory = findModule($modules, 'story') ?? findModule($modules, 'timeline');
$storyPayload = [];
if ($modStory && !empty($modStory['content_payload'])) {
    $raw = $modStory['content_payload'];
    $storyPayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

// --- Schedule module ---
$modSchedule = findModule($modules, 'schedule');
$schedulePayload = [];
if ($modSchedule && !empty($modSchedule['content_payload'])) {
    $raw = $modSchedule['content_payload'];
    $schedulePayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}
$scheduleItems = !empty($scheduleItems) ? $scheduleItems : ($schedulePayload['items'] ?? ($schedulePayload['events'] ?? []));

// --- FAQ module ---
$modFaq = findModule($modules, 'faq');
$faqPayload = [];
if ($modFaq && !empty($modFaq['content_payload'])) {
    $raw = $modFaq['content_payload'];
    $faqPayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}
$faqs = !empty($faqs) ? $faqs : ($faqPayload['items'] ?? []);

// --- Dynamic text with cascading fallback: copyPayload → defaults → hardcoded ---
function getText(array $copyPayload, array $defaults, string $key, string $hardcoded = ''): string
{
    return esc($copyPayload[$key] ?? ($defaults[$key] ?? $hardcoded));
}

$heroTagline = getText($copyPayload, $defaults, 'hero_tagline', 'Nos casamos');
$countdownTitle = getText($copyPayload, $defaults, 'countdown_title', 'Guarda la fecha');
$countdownSubtitle = getText($copyPayload, $defaults, 'countdown_subtitle', 'Nuestra celebración');
$ctaHeading = getText($copyPayload, $defaults, 'cta_heading', 'Te invitamos a…');
$ctaSubheading = getText($copyPayload, $defaults, 'cta_subheading', 'Celebrar con nosotros');
$rsvpHeading = getText($copyPayload, $defaults, 'rsvp_heading', 'Confirma tu asistencia');
$brideSectionTitle = getText($copyPayload, $defaults, 'bride_section_title', 'La novia');
$groomSectionTitle = getText($copyPayload, $defaults, 'groom_section_title', 'El novio');
$storyTitle = getText($copyPayload, $defaults, 'story_title', 'Nuestra historia');
$eventsTitle = getText($copyPayload, $defaults, 'events_title', 'Detalles del evento');
$galleryTitle = getText($copyPayload, $defaults, 'gallery_title', 'Galería');
$registryTitle = getText($copyPayload, $defaults, 'registry_title', 'Mesa de regalos');
$partyTitle = getText($copyPayload, $defaults, 'party_title', 'Damas y Caballeros');
$aboutTitle = getText($copyPayload, $defaults, 'about_title', 'Sobre la pareja');
$quoteText = getText(
    $copyPayload,
    $defaults,
    'quote_text',
    'Ser profundamente amado por alguien te da fuerza, mientras que amar profundamente a alguien te da valentía.'
);
$saveDateText = getText(
    $copyPayload,
    $defaults,
    'save_date_text',
    'Gracias por acompañarnos en este día tan especial.'
);
$eventIntroTitle = getText($copyPayload, $defaults, 'event_intro_title', 'Celebra con nosotros');
$eventIntroText = getText(
    $copyPayload,
    $defaults,
    'event_intro_text',
    'Será un honor compartir este momento contigo.'
);
$eventDetailsTitle = getText($copyPayload, $defaults, 'event_details_title', 'Un día muy especial...');
$eventDetailsText = getText(
    $copyPayload,
    $defaults,
    'event_details_text',
    'Gracias por formar parte de nuestra historia.'
);
$eventAlertText = getText(
    $copyPayload,
    $defaults,
    'event_alert_text',
    'Esperamos celebrar contigo.'
);

$brideName = esc($event['bride_name'] ?? ($couplePayload['bride']['name'] ?? ''));
$groomName = esc($event['groom_name'] ?? ($couplePayload['groom']['name'] ?? ''));

$brideBio = esc($couplePayload['bride']['bio']
    ?? ($defaults['bride_bio'] ?? ($defaults['bride_bio_default'] ?? 'Gracias por ser parte de nuestra historia.')));
$groomBio = esc($couplePayload['groom']['bio']
    ?? ($defaults['groom_bio'] ?? ($defaults['groom_bio_default'] ?? 'Estamos muy felices de compartir contigo este día.')));
$brideSubtitle = esc($couplePayload['bride']['subtitle'] ?? ($defaults['bride_subtitle'] ?? ''));
$groomSubtitle = esc($couplePayload['groom']['subtitle'] ?? ($defaults['groom_subtitle'] ?? ''));

// --- Media helpers ---
function getMediaUrl(array $mediaByCategory, string $category, int $index = 0, string $size = 'original'): string
{
    $items = $mediaByCategory[$category] ?? [];
    if (empty($items) || !isset($items[$index])) {
        return '';
    }

    $m = $items[$index];
    $fieldMap = ['original' => 'file_url_original', 'large' => 'file_url_large', 'thumb' => 'file_url_thumbnail'];
    $field = $fieldMap[$size] ?? 'file_url_original';

    $url = $m[$field]
        ?? ($m['file_url_original']
            ?? ($m['file_url_large'] ?? ($m['file_url_thumbnail'] ?? '')));
    if ($url !== '' && !preg_match('#^https?://#i', $url)) {
        $url = base_url($url);
    }
    return $url;
}

function getAllMediaUrls(array $mediaByCategory, string $category): array
{
    $items = $mediaByCategory[$category] ?? [];
    $urls = [];
    foreach ($items as $m) {
        $url = $m['file_url_large'] ?? ($m['file_url_original'] ?? '');
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $url = base_url($url);
        }
        if ($url !== '') {
            $urls[] = $url;
        }
    }
    return $urls;
}

function getGalleryUrls(array $galleryAssets): array
{
    $urls = [];
    foreach ($galleryAssets as $asset) {
        $url = (string)($asset['full'] ?? ($asset['thumb'] ?? ''));
        if ($url !== '') {
            $urls[] = $url;
        }
    }
    return $urls;
}

// Hero slider images: event media → gallery → template defaults
$heroImages = getAllMediaUrls($mediaByCategory, 'hero');
if (empty($heroImages)) {
    $heroImages = getGalleryUrls($galleryAssets);
}
if (empty($heroImages)) {
    $heroImages = [
        $assetsBase . '/img/couplemain.jpg',
        $assetsBase . '/img/couple1.jpg',
        $assetsBase . '/img/couple2.jpg',
    ];
}
$heroMainImage = $heroImages[0] ?? ($assetsBase . '/img/couplemain.jpg');
$heroWallLeft = $heroImages[1] ?? ($assetsBase . '/img/couple1.jpg');
$heroWallRight = $heroImages[2] ?? ($assetsBase . '/img/couple2.jpg');

// Couple photos
$groomPhoto = getMediaUrl($mediaByCategory, 'groom') ?: ($assetsBase . '/img/groom.jpg');
$bridePhoto = getMediaUrl($mediaByCategory, 'bride') ?: ($assetsBase . '/img/bride.jpg');
$rsvpBg = getMediaUrl($mediaByCategory, 'rsvp_bg') ?: ($assetsBase . '/img/rsvp.jpg');

// Event images
$eventImagePrimary = getMediaUrl($mediaByCategory, 'event', 0) ?: ($assetsBase . '/img/party2.jpg');
$eventImageSecondary = getMediaUrl($mediaByCategory, 'event', 1) ?: ($assetsBase . '/img/party1.jpg');

$eventMediaItems = $mediaByCategory['event'] ?? [];
$eventMediaTextPrimary = '';
$eventMediaTextSecondary = '';
$eventMediaTextAlert = '';
if (!empty($eventMediaItems[0])) {
    $eventMediaTextPrimary = (string)($eventMediaItems[0]['caption'] ?? ($eventMediaItems[0]['alt_text'] ?? ''));
}
if (!empty($eventMediaItems[1])) {
    $eventMediaTextSecondary = (string)($eventMediaItems[1]['caption'] ?? ($eventMediaItems[1]['alt_text'] ?? ''));
}
if (!empty($eventMediaItems[2])) {
    $eventMediaTextAlert = (string)($eventMediaItems[2]['caption'] ?? ($eventMediaItems[2]['alt_text'] ?? ''));
}

if ($eventMediaTextPrimary !== '') {
    $eventIntroText = esc($eventMediaTextPrimary);
}
if ($eventMediaTextSecondary !== '') {
    $eventDetailsText = esc($eventMediaTextSecondary);
}
if ($eventMediaTextAlert !== '') {
    $eventAlertText = esc($eventMediaTextAlert);
}

// --- Small helpers ---
function moneyFmt($val, string $currency = 'MXN'): string
{
    $n = is_numeric($val) ? (float)$val : 0.0;
    return '$' . number_format($n, 2) . ' ' . $currency;
}

function safeText($v): string
{
    return esc(trim((string)$v));
}

function parseSocialLinks($raw): array
{
    if (empty($raw)) {
        return [];
    }
    if (is_array($raw)) {
        return $raw;
    }
    $decoded = json_decode((string)$raw, true);
    return is_array($decoded) ? $decoded : [];
}

$partyLabels = [
    'bride_side' => 'Lado de la novia',
    'groom_side' => 'Lado del novio',
    'officiant' => 'Oficiante',
    'other' => 'Otros',
];

$partyByCategory = [];
foreach ($weddingParty as $member) {
    $cat = $member['category'] ?? 'other';
    $partyByCategory[$cat][] = $member;
}

$storyItems = !empty($timelineItems)
    ? $timelineItems
    : ($storyPayload['items'] ?? ($storyPayload['events'] ?? []));

$hasStory = !empty($storyItems);
$hasBrideSide = !empty($partyByCategory['bride_side']);
$hasGroomSide = !empty($partyByCategory['groom_side']);
$hasWeddingParty = $hasBrideSide || $hasGroomSide;
$hasRegistry = !empty($registryItems);
$hasLocations = !empty($eventLocations) || ($lat !== '' && $lng !== '');

$logoUrl = $tplAssets['logo'] ?? 'img/logo.png';
if ($logoUrl !== '' && !preg_match('#^https?://#i', $logoUrl)) {
    $logoUrl = $assetsBase . '/' . ltrim($logoUrl, '/');
}

$registryFallbacks = [
    $assetsBase . '/img/brand1.png',
    $assetsBase . '/img/brand2.png',
    $assetsBase . '/img/brand3.png',
    $assetsBase . '/img/brand4.png',
];

$galleryFallbacks = [
    $assetsBase . '/img/gallery1.jpg',
    $assetsBase . '/img/gallery2.jpg',
    $assetsBase . '/img/gallery3.jpg',
    $assetsBase . '/img/gallery4.jpg',
    $assetsBase . '/img/gallery5.jpg',
    $assetsBase . '/img/gallery6.jpg',
    $assetsBase . '/img/gallery7.jpg',
];

$now = new DateTimeImmutable('now');
$eventDate = $startRaw ? new DateTimeImmutable($startRaw) : null;
$diffSeconds = $eventDate ? max(0, $eventDate->getTimestamp() - $now->getTimestamp()) : 0;
$countdownDays = (int)floor($diffSeconds / 86400);
$countdownHours = (int)floor(($diffSeconds % 86400) / 3600);
$countdownMinutes = (int)floor(($diffSeconds % 3600) / 60);
$countdownSeconds = (int)($diffSeconds % 60);

$mapMarkers = [];
foreach ($eventLocations as $loc) {
    $locLat = $loc['geo_lat'] ?? null;
    $locLng = $loc['geo_lng'] ?? null;
    if ($locLat === null || $locLng === null || $locLat === '' || $locLng === '') {
        continue;
    }
    $locName = (string)($loc['name'] ?? $venueName);
    $mapMarkers[] = [
        'title' => $locName,
        'latitude' => (float)$locLat,
        'longitude' => (float)$locLng,
        'address' => (string)($loc['address'] ?? ''),
    ];
}
if (empty($mapMarkers) && $lat !== '' && $lng !== '') {
    $mapMarkers[] = [
        'title' => $venueName,
        'latitude' => (float)$lat,
        'longitude' => (float)$lng,
        'address' => $venueAddr,
    ];
}
$mapInitialLat = $lat !== '' ? (float)$lat : ($mapMarkers[0]['latitude'] ?? null);
$mapInitialLng = $lng !== '' ? (float)$lng : ($mapMarkers[0]['longitude'] ?? null);

$pageTitle = $templateMeta['title'] ?? ($coupleTitle !== '' ? $coupleTitle : 'Invitación');
$pageDescription = $templateMeta['description'] ?? $coupleTitle;
$countdownDateString = $startRaw ? date('Y/m/d H:i:s', strtotime($startRaw)) : '';

$brideSocial = parseSocialLinks($couplePayload['bride']['social_links'] ?? ($couplePayload['bride']['social'] ?? []));
$groomSocial = parseSocialLinks($couplePayload['groom']['social_links'] ?? ($couplePayload['groom']['social'] ?? []));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <!--[if IE]>
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="<?= esc($pageDescription) ?>">
    <meta name="author" content="">
    <!-- Page title -->
    <title><?= esc($pageTitle) ?> | 13Bodas</title>
    <!--[if lt IE 9]>
      <script src="js/respond.js"></script>
      <![endif]-->
    <!-- Bootstrap Core CSS -->
    <link href="<?= $assetsBase ?>/css/bootstrap.css" rel="stylesheet" type="text/css">
    <!-- Icon fonts -->
    <link href="<?= $assetsBase ?>/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="<?= $assetsBase ?>/fonts/glyphicons/bootstrap-glyphicons.css" rel="stylesheet" type="text/css">
    <!-- Google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700i" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,700,700i" rel="stylesheet" type="text/css">
    <!-- Style CSS -->
    <link href="<?= $assetsBase ?>/css/style.css" rel="stylesheet">
    <!-- Plugins CSS -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/plugins.css">
    <!-- Color Style CSS -->
    <link href="<?= $assetsBase ?>/styles/lavender.css" rel="stylesheet">
    <!-- Favicons-->
    <link rel="apple-touch-icon" sizes="72x72" href="<?= $assetsBase ?>/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= $assetsBase ?>/apple-icon-114x114.png">
    <link rel="shortcut icon" href="<?= $assetsBase ?>/favicon.ico" type="image/x-icon">
    <!-- Switcher Only -->
    <link rel="stylesheet" id="switcher-css" type="text/css" href="<?= $assetsBase ?>/switcher/css/switcher.css" media="all" />
    <!-- END Switcher Styles -->
    <!-- Demo Examples (For Module #3) -->
    <link rel="alternate stylesheet" type="text/css" href="<?= $assetsBase ?>/styles/summer.css" title="summer" media="all" />
    <link rel="alternate stylesheet" type="text/css" href="<?= $assetsBase ?>/styles/serenity.css" title="serenity" media="all" />
    <link rel="alternate stylesheet" type="text/css" href="<?= $assetsBase ?>/styles/lavender.css" title="lavender" media="all" />
    <!-- END Demo Examples -->
</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-custom">
    <!-- Start Switcher -->

    <!-- Preloader -->
    <div id="preloader">
        <div class="spinner">
            <div class="bounce1"></div>
        </div>
    </div>
    <!-- Preloader ends -->
    <nav class="navbar navbar-custom navbar-fixed-top">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display  -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-brand-centered">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <!--/navbar-header -->
            <!-- Collect the nav links, forms, and other content for toggling  -->
            <div class="collapse navbar-collapse" id="navbar-brand-centered">
                <ul class="nav navbar-nav page-scroll">
                    <li class="active"><a href="#page-top">Inicio</a></li>
                    <li><a href="#about">Sobre nosotros</a></li>
                    <?php if ($hasStory): ?>
                        <li><a href="#story">Nuestra historia</a></li>
                    <?php endif; ?>
                    <?php if ($hasWeddingParty): ?>
                        <li><a href="#attendants">Cortejo</a></li>
                    <?php endif; ?>
                    <?php if (!empty($scheduleItems)): ?>
                        <li><a href="#schedule">Agenda</a></li>
                    <?php endif; ?>
                    <?php if (!empty($faqs)): ?>
                        <li><a href="#faqs">Preguntas</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="nav navbar-nav navbar-right page-scroll">
                    <li><a href="#event">El evento</a></li>
                    <li><a href="#gallery">Galería</a></li>
                    <li><a href="#rsvp">Confirmación</a></li>
                    <?php if (!empty($blogPosts)): ?>
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Páginas<b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="blog.html">Blog</a></li>
                                <li><a href="blog-single.html">Entrada</a></li>
                                <li><a href="elements.html">Elementos</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>
    <!-- /navbar ends -->
    <!-- section: intro-->
    <section id="intro" class="container-fluid">
        <!-- parallax ornament -->
        <div class="ornament1 hidden-sm hidden-xs hidden-md"
            data-100="transform:translatey(150%);"
            data-center-center="transform:translatey(-10%);">
            <!-- illustration path in the color template CSS -->
        </div>
        <!--/ornament 1-->
        <!-- Background blurred images -->
        <div class="photowall">
            <div class="col-md-4 margin1">
                <img src="<?= esc($heroWallLeft) ?>" alt="<?= esc($coupleTitle) ?>" class="rotate1 img-photo img-responsive">
            </div>
            <!-- /col-md-4 -->
            <div class="col-md-4  col-md-offset-4">
                <img src="<?= esc($heroWallRight) ?>" alt="<?= esc($coupleTitle) ?>" class="rotate2 img-photo img-responsive">
            </div>
            <!-- /col-md-4 -->
        </div>
        <!-- Main Picture -->
        <div class="main-picture col-md-6 col-centered"
            data-100="margin-top:0px;transform: rotate(4deg);"
            data-center-center="margin-top:50px;transform: rotate(-10deg);">
            <!-- image-->
            <img src="<?= esc($heroMainImage) ?>" alt="<?= esc($coupleTitle) ?>" class="img-photo img-responsive">
        </div>
        <!--/main picture-->
        <div class="intro-heading col-md-12 text-center" data-0="opacity:1;"
            data--100-start="transform:translatey(0%);"
            data-center-bottom="transform:translatey(30%);">
            <h1><?= esc($brideName !== '' ? $brideName : 'Amor') ?> <span class="italic"> & </span> <?= esc($groomName !== '' ? $groomName : 'Historia') ?>
            </h1>
            <h5 class="margin1 text-ornament"><?= $heroTagline ?></h5>
        </div>
        <!-- /col-md-6-->
    </section>
    <!-- /section ends -->
    <!-- Section: Save date-->
    <section id="save-date">
        <!-- parallax ornaments -->
        <div class="ornament2 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
            data-100="transform:translatex(0%);"
            data-center-center="transform:translatex(90%);">
            <!-- illustration path in the color template CSS -->
        </div>
        <div class="ornament3 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
            data-100="transform:translatex(80%);"
            data-center-center="transform:translatex(30%);">
            <!-- illustration path in the color template CSS -->
        </div>
        <!-- /ornament3 -->
        <!-- container -->
        <div class="container">
            <div class="row">
                <!-- well starts-->
                <div class="well col-md-10 col-md-offset-1 text-center">
                    <div class="section-heading">
                        <h2><?= $eventDateLabel !== '' ? esc($eventDateLabel) : $countdownTitle ?></h2>
                        <!-- divider -->
                        <div class="hr"></div>
                    </div>
                    <!--/section heading -->
                    <?php if ($rsvpDeadlineLabel !== ''): ?>
                        <h5 class="margin1">Por favor confirma antes del <?= esc($rsvpDeadlineLabel) ?></h5>
                    <?php endif; ?>
                    <p>
                        <?= $saveDateText ?>
                    </p>
                    <div class="margin1">
                        <!-- countdown tag -->
                        <span id="countdown"></span>
                        <!-- edit the countdown in the main.js file-->
                    </div>
                    <!-- /margin1-->
                    <div class="page-scroll">
                        <a href="#rsvp" class="btn">Confirma ahora</a>
                    </div>
                    <!-- /page-scroll -->
                </div>
                <!-- /well-->
            </div>
            <!-- /row-->
        </div>
        <!-- /container -->
    </section>
    <!-- /section ends -->
    <!-- section:about-->
    <section id="about" class="watercolor">
        <!-- container -->
        <div class="container">
            <div class="section-heading">
                <h2><?= $aboutTitle ?></h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!-- /section-heading-->
            <!-- Bride Info -->
            <div class="col-md-5 col-md-offset-1">
                <img src="<?= esc($bridePhoto) ?>" alt="<?= esc($brideName) ?>" class="main-img img-responsive img-circle" />
                <h4 class="text-ornament"><?= esc($brideName !== '' ? $brideName : $brideSectionTitle) ?></h4>
                <?php if ($brideSubtitle !== ''): ?>
                    <h6 class="main-subheader"><?= esc($brideSubtitle) ?></h6>
                <?php endif; ?>
                <p>
                    <?= $brideBio ?>
                </p>
                <!-- small social-icons -->
                <?php if (!empty($brideSocial)): ?>
                    <div class="social-media smaller">
                        <?php foreach ($brideSocial as $social): ?>
                            <?php
                            $socialUrl = $social['url'] ?? ($social['link'] ?? '');
                            $socialIcon = $social['icon'] ?? ($social['platform'] ?? 'link');
                            if ($socialUrl === '') {
                                continue;
                            }
                            ?>
                            <a href="<?= esc($socialUrl) ?>" title="" target="_blank" rel="noopener">
                                <i class="fa fa-<?= esc($socialIcon) ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- /social-icons -->
            </div>
            <!-- /col-md-5 -->
            <!-- Groom Info -->
            <div class="col-md-5 res-margin">
                <img src="<?= esc($groomPhoto) ?>" alt="<?= esc($groomName) ?>" class="main-img img-responsive img-circle" />
                <h4 class="text-ornament"><?= esc($groomName !== '' ? $groomName : $groomSectionTitle) ?></h4>
                <?php if ($groomSubtitle !== ''): ?>
                    <h6 class="main-subheader"><?= esc($groomSubtitle) ?></h6>
                <?php endif; ?>
                <p>
                    <?= $groomBio ?>
                </p>
                <!-- small social-icons -->
                <?php if (!empty($groomSocial)): ?>
                    <div class="social-media smaller">
                        <?php foreach ($groomSocial as $social): ?>
                            <?php
                            $socialUrl = $social['url'] ?? ($social['link'] ?? '');
                            $socialIcon = $social['icon'] ?? ($social['platform'] ?? 'link');
                            if ($socialUrl === '') {
                                continue;
                            }
                            ?>
                            <a href="<?= esc($socialUrl) ?>" title="" target="_blank" rel="noopener">
                                <i class="fa fa-<?= esc($socialIcon) ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- /social-icons -->
            </div>
            <!-- /col-md-5 -->
        </div>
        <!-- /container -->
    </section>
    <!-- /section ends -->
    <!-- parallax ornament -->
    <div class="ornament4 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
        data--100-start="transform:translatex(90%);"
        data-center-bottom="transform:translatex(0%);">
        <!-- illustration path in the color template CSS -->
    </div>
    <!-- /ornament4 -->
    <!-- section:story -->
    <?php if ($hasStory): ?>
        <section id="story">
            <div class="container">
                <div class="section-heading">
                    <h2><?= $storyTitle ?></h2>
                    <!-- divider -->
                    <div class="hr"></div>
                </div>
                <!-- /section-heading -->
                <!-- Polaroids -->
                <div class="row">
                    <ul id="story-carousel" class="polaroids owl-carousel margin1">
                        <?php foreach ($storyItems as $idx => $item): ?>
                            <?php
                            $itemImage = trim((string)($item['image_url'] ?? ($item['image'] ?? '')));
                            $fallbackImage = getMediaUrl($mediaByCategory, 'story', $idx) ?: ($assetsBase . '/img/polaroid' . (($idx % 5) + 1) . '.jpg');
                            $storyImage = $itemImage !== '' ? $itemImage : $fallbackImage;
                            if ($storyImage !== '' && !preg_match('#^https?://#i', $storyImage)) {
                                $storyImage = base_url($storyImage);
                            }
                            $storyYear = $item['year'] ?? ($item['date'] ?? '');
                            $storyText = $item['description'] ?? ($item['text'] ?? '');
                            ?>
                            <li class="polaroid-item"
                                data-0="transform:translatey(0%);"
                                data-center="transform:translatey(0%);transform:rotate(-4deg)">
                                <a href="<?= esc($storyImage) ?>" data-gal="prettyPhoto[gallery]">
                                    <img alt="" src="<?= esc($storyImage) ?>" class="img-responsive" />
                                    <span><?= esc($storyYear) ?></span>
                                    <p><?= esc($storyText) ?></p>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <!-- /li polaroid -->
                    </ul>
                    <!-- /ul-polaroids -->
                </div>
                <!-- /row-fluid -->
            </div>
            <!-- /container-->
        </section>
    <?php endif; ?>
    <!-- /section ends -->
    <!-- Section:attendants -->
    <?php if ($hasWeddingParty): ?>
        <section id="attendants" class="watercolor">
            <!-- parallax ornament -->
            <div class="ornament5 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
                data--100-start="transform:translatex(-10%);"
                data-center-bottom="transform:translatex(100%);">
                <!-- illustration path in the color template CSS -->
            </div>
            <div class="container">
                <div class="section-heading">
                    <h2><?= $partyTitle ?></h2>
                    <!-- divider -->
                    <div class="hr"></div>
                </div>
                <!-- /section-heading -->
                <!-- /col-md-3 -->
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        <?php if ($hasBrideSide): ?>
                            <li class="<?= $hasBrideSide ? 'active' : '' ?>"><a href="#bridemaids" data-toggle="tab">The Ladies</a></li>
                        <?php endif; ?>
                        <?php if ($hasGroomSide): ?>
                            <li class="<?= !$hasBrideSide ? 'active' : '' ?>"><a href="#groomsman" data-toggle="tab">The Gentlemen</a></li>
                        <?php endif; ?>
                    </ul>
                    <!--/nav nav-tabs -->
                    <div class="tabbable">
                        <div class="tab-content">
                            <!-- tab 1 -->
                            <?php if ($hasBrideSide): ?>
                                <div class="tab-pane active in fade" id="bridemaids">
                                    <!-- attendants carousel 1-->
                                    <div id="owl-attendants1" class="owl-carousel">
                                        <?php foreach ($partyByCategory['bride_side'] as $idx => $member): ?>
                                            <?php
                                            $memberImage = (string)($member['image_url'] ?? ($member['photo_url'] ?? ''));
                                            if ($memberImage !== '' && !preg_match('#^https?://#i', $memberImage)) {
                                                $memberImage = base_url($memberImage);
                                            }
                                            $memberImage = $memberImage ?: ($assetsBase . '/img/attendant' . (($idx % 5) + 1) . '.jpg');
                                            $memberName = esc($member['full_name'] ?? ($member['name'] ?? ''));
                                            $memberRole = esc($member['role'] ?? ($partyLabels[$member['category'] ?? 'other'] ?? ''));
                                            ?>
                                            <!-- attendants member -->
                                            <div class="attendants-wrap col-md-12">
                                                <div class="member text-center">
                                                    <div class="wrap">
                                                        <!-- image -->
                                                        <img src="<?= esc($memberImage) ?>" alt="<?= esc($memberName) ?>" class="img-circle img-responsive">
                                                        <!-- Info -->
                                                        <div class="info">
                                                            <h5 class="name"><?= esc($memberName) ?></h5>
                                                            <h4 class="description"><?= esc($memberRole) ?></h4>
                                                        </div>
                                                        <!-- /info -->
                                                    </div>
                                                    <!-- /wrap -->
                                                </div>
                                                <!-- / member -->
                                            </div>
                                            <!--/ attendants-wrap -->
                                        <?php endforeach; ?>
                                    </div>
                                    <!-- /owl-carousel -->
                                </div>
                            <?php endif; ?>
                            <!--/ tab 1 ends -->
                            <!-- tab 2 -->
                            <?php if ($hasGroomSide): ?>
                                <div class="tab-pane fade <?= !$hasBrideSide ? 'active in' : '' ?>" id="groomsman">
                                    <!-- Attendants carousel 2 -->
                                    <div id="owl-attendants2" class="owl-carousel">
                                        <?php foreach ($partyByCategory['groom_side'] as $idx => $member): ?>
                                            <?php
                                            $memberImage = (string)($member['image_url'] ?? ($member['photo_url'] ?? ''));
                                            if ($memberImage !== '' && !preg_match('#^https?://#i', $memberImage)) {
                                                $memberImage = base_url($memberImage);
                                            }
                                            $memberImage = $memberImage ?: ($assetsBase . '/img/attendant' . (($idx % 5) + 6) . '.jpg');
                                            $memberName = esc($member['full_name'] ?? ($member['name'] ?? ''));
                                            $memberRole = esc($member['role'] ?? ($partyLabels[$member['category'] ?? 'other'] ?? ''));
                                            ?>
                                            <!-- attendants member -->
                                            <div class="attendants-wrap col-md-12">
                                                <div class="member text-center">
                                                    <div class="wrap">
                                                        <!-- image -->
                                                        <img src="<?= esc($memberImage) ?>" alt="<?= esc($memberName) ?>" class="img-circle img-responsive">
                                                        <!-- Info -->
                                                        <div class="info">
                                                            <h5 class="name"><?= esc($memberName) ?></h5>
                                                            <h4 class="description"><?= esc($memberRole) ?></h4>
                                                        </div>
                                                        <!-- /info -->
                                                    </div>
                                                    <!-- /wrap -->
                                                </div>
                                                <!-- / member -->
                                            </div>
                                            <!--/ attendants-wrap -->
                                        <?php endforeach; ?>
                                    </div>
                                    <!-- /owl-carousel -->
                                </div>
                            <?php endif; ?>
                            <!-- /tab-pane -->
                        </div>
                        <!-- /tab-content -->
                    </div>
                    <!-- /tabbable -->
                </div>
                <!-- /col-md-12 -->
            </div>
            <!-- /.container -->
        </section>
    <?php endif; ?>
    <!-- /Section ends -->
    <!-- Section: Event-->
    <section id="event">
        <div class="section-heading">
            <h2><?= $eventsTitle ?></h2>
            <!-- divider -->
            <div class="hr"></div>
        </div>
        <!--/section-heading -->
        <div class="container">
            <div class="row">
                <div class="col-md-6" data--100-start="transform:translatey(-60%);"
                    data-center-bottom="transform:translatey(20%);">
                    <!-- image -->
                    <img src="<?= esc($eventImagePrimary) ?>" alt="<?= esc($eventsTitle) ?>" class="img-photo rotate1 img-responsive">
                </div>
                <!-- paper well -->
                <div class="well col-md-6">
                    <h3><?= $eventIntroTitle ?></h3>
                    <p><?= $eventIntroText ?></p>
                </div>
                <!-- /well -->
            </div>
            <!-- /row -->
            <div class="row margin1">
                <!-- paper well -->
                <div class="well col-md-7">
                    <h5><?= $eventDetailsTitle ?></h5>
                    <p><?= $eventDetailsText ?></p>
                    <p class="alert">
                        <?= $eventAlertText ?>
                    </p>
                </div>
                <!-- /well -->
                <div class="col-md-5" data--100-start="transform:translatey(-60%);"
                    data-center-bottom="transform:translatey(20%);">
                    <!-- image -->
                    <img src="<?= esc($eventImageSecondary) ?>" alt="<?= esc($eventsTitle) ?>" class="img-photo rotate2 img-responsive">
                </div>
                <!-- /col-md-5 -->
            </div>
            <!-- /row-->
        </div>
        <!-- /container -->
        <?php if ($hasLocations): ?>
            <!-- row-fluid -->
            <div class="container-fluid" id="scroll-effect">
                <div class="col-lg-7 col-md-7 no-padding">
                    <!-- map-->
                    <div id="map-canvas"></div>
                    <!-- image1 -->
                </div>
                <!-- paper well with effect -->
                <div class="well col-lg-5 col-lg-offset-7 col-md-5 col-md-offset-7 " data-start="left: 20%;" data-center="left:0%;">
                    <div class="col-md-12 text-center">
                        <h3 class="date"><?= esc($eventDateLabel) ?></h3>
                        <?php if ($eventTimeRange !== ''): ?>
                            <h6><?= esc($eventTimeRange) ?></h6>
                        <?php endif; ?>
                        <?php if ($venueName !== '' || $venueAddr !== ''): ?>
                            <h6><?= esc(trim($venueName . ($venueAddr ? ', ' . $venueAddr : ''))) ?></h6>
                        <?php endif; ?>
                        <hr>
                        <?php if (!empty($primaryLocation['description']) || !empty($primaryLocation['notes']) || !empty($event['venue_description'])): ?>
                            <p><?= esc($primaryLocation['description'] ?? ($primaryLocation['notes'] ?? ($event['venue_description'] ?? ''))) ?></p>
                        <?php else: ?>
                            <p><?= esc($ctaSubheading) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- /well -->
            </div>
            <!-- /row-fluid -->
        <?php endif; ?>
    </section>
    <!-- Section ends -->
    <?php if (!empty($scheduleItems)): ?>
        <!-- Section: Schedule -->
        <section id="schedule" class="watercolor">
            <div class="container">
                <div class="section-heading">
                    <h2>Agenda</h2>
                    <!-- divider -->
                    <div class="hr"></div>
                </div>
                <div class="row">
                    <?php foreach ($scheduleItems as $item): ?>
                        <?php
                        $title = (string)($item['title'] ?? 'Actividad');
                        $desc = (string)($item['description'] ?? '');
                        $timeLabel = formatScheduleTime($item);
                        $location = (string)($item['location'] ?? ($item['venue'] ?? ''));
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="well">
                                <h4><?= esc($title) ?></h4>
                                <?php if ($timeLabel !== ''): ?>
                                    <p><strong><?= $timeLabel ?></strong></p>
                                <?php endif; ?>
                                <?php if ($location !== ''): ?>
                                    <p><?= esc($location) ?></p>
                                <?php endif; ?>
                                <?php if ($desc !== ''): ?>
                                    <p><?= esc($desc) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- /Section ends -->
    <?php endif; ?>
    <?php if (!empty($faqs)): ?>
        <!-- Section: FAQ -->
        <section id="faqs">
            <div class="container">
                <div class="section-heading">
                    <h2>Preguntas frecuentes</h2>
                    <!-- divider -->
                    <div class="hr"></div>
                </div>
                <div class="row">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="col-md-6">
                            <div class="well">
                                <h5><?= esc($faq['question'] ?? 'Pregunta') ?></h5>
                                <p><?= esc($faq['answer'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- /Section ends -->
    <?php endif; ?>
    <!-- Section: Quote -->
    <section id="quote" class="container-fluid">
        <div class="col-md-7 col-centered" data-center-top="opacity: 1" data-center-bottom="opacity: 0">
            <blockquote>
                <h2><?= $quoteText ?></h2>
            </blockquote>
        </div>
        <!-- /col-md-7-->
    </section>
    <!-- /section ends -->
    <!-- Section: Registry -->
    <?php if ($hasRegistry): ?>
        <section id="registry">
            <div class="section-heading text-center">
                <h2><?= $registryTitle ?></h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!--/section-heading -->
            <div class="container text-center">
                <div class="row">
                    <?php foreach ($registryItems as $idx => $item): ?>
                        <?php
                        $itemTitle = safeText($item['title'] ?? ($item['name'] ?? ''));
                        $itemImage = (string)($item['image_url'] ?? '');
                        if ($itemImage !== '' && !preg_match('#^https?://#i', $itemImage)) {
                            $itemImage = base_url($itemImage);
                        }
                        $itemImage = $itemImage ?: ($registryFallbacks[$idx % count($registryFallbacks)] ?? $registryFallbacks[0]);
                        $itemUrl = safeText($item['product_url'] ?? ($item['external_url'] ?? '#'));
                        ?>
                        <div class="col-sm-6 col-md-3<?= $idx > 0 ? ' res-margin' : '' ?>">
                            <a href="<?= esc($itemUrl) ?>" target="_blank" rel="noopener">
                                <img src="<?= esc($itemImage) ?>" alt="<?= esc($itemTitle) ?>" class="brand col-centered img-responsive" />
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </section>
    <?php endif; ?>
    <!-- Section ends -->
    <!-- Section: Gallery -->
    <section id="gallery" class="watercolor">
        <div class="section-heading text-center">
            <h2><?= $galleryTitle ?></h2>
            <!-- divider -->
            <div class="hr"></div>
        </div>
        <!--/section-heading -->
        <div class="container-fluid">
            <!-- row fluid -->
            <div class="row-fluid">
                <!-- Navigation -->
                <div class="text-center col-md-12">
                    <ul class="nav nav-pills category text-center" role="tablist" id="gallerytab">
                        <li class="active"><a href="#" data-toggle="tab" data-filter="*">Todas</a>
                        <li><a href="#" data-toggle="tab" data-filter=".our-photos">Nuestras fotos</a></li>
                        <li><a href="#" data-toggle="tab" data-filter=".wedding">Boda</a></li>
                    </ul>
                </div>
                <!-- Gallery -->
                <div class="col-md-12 gallery margin1">
                    <div id="lightbox">
                        <?php if (!empty($galleryAssets)): ?>
                            <?php foreach ($galleryAssets as $idx => $g): ?>
                                <?php
                                $full = safeText($g['full'] ?? '');
                                $thumb = safeText($g['thumb'] ?? $full);
                                $alt = safeText($g['alt'] ?? $coupleTitle);
                                if ($full === '') {
                                    continue;
                                }
                                $class = $idx % 2 === 0 ? 'wedding' : 'our-photos';
                                ?>
                                <div class="<?= esc($class) ?> col-lg-4 col-sm-6 col-md-6">
                                    <div class="isotope-item">
                                        <div class="gallery-thumb">
                                            <img class="img-responsive" src="<?= esc($thumb) ?>" alt="<?= esc($alt) ?>">
                                            <a href="<?= esc($full) ?>" data-gal="prettyPhoto[gallery]" title="<?= esc($alt) ?>">
                                                <span class="overlay-mask"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($galleryFallbacks as $idx => $fallback): ?>
                                <?php $class = $idx % 2 === 0 ? 'wedding' : 'our-photos'; ?>
                                <div class="<?= esc($class) ?> col-lg-4 col-sm-6 col-md-6">
                                    <div class="isotope-item">
                                        <div class="gallery-thumb">
                                            <img class="img-responsive" src="<?= esc($fallback) ?>" alt="<?= esc($coupleTitle) ?>">
                                            <a href="<?= esc($fallback) ?>" data-gal="prettyPhoto[gallery]" title="<?= esc($coupleTitle) ?>">
                                                <span class="overlay-mask"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- /lightbox-->
                </div>
                <!-- /col-lg-12 -->
            </div>
            <!-- /row fluid -->
        </div>
        <!-- /container fluid-->
    </section>
    <!-- /Section ends -->
    <!-- Section: RSVP  -->
    <section id="rsvp">
        <!-- parallax ornament -->
        <div class="ornament6 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
            data-100="transform:translatex(90%);"
            data-center-center="transform:translatex(30%);">
            <!-- illustration path in the color template CSS -->
        </div>
        <!-- parallax ornament -->
        <div class="ornament7 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
            data-100="transform:translatex(0%);"
            data-center-center="transform:translatex(90%);">
            <!-- illustration path in the color template CSS -->
        </div>
        <div class="container">
            <div class="section-heading">
                <h2><?= $rsvpHeading ?></h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!-- /section-heading -->
            <div class="col-lg-5">
                <!-- image -->
                <img src="<?= esc($rsvpBg) ?>" alt="<?= esc($rsvpHeading) ?>" class="margin1 img-photo rotate2 img-responsive">
            </div>
            <!-- well -->
            <div class="col-lg-7 well">
                <form id="rsvp_form" method="post" action="<?= esc(route_to('rsvp.submit', $slug)) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group text-center">
                        <!-- name field-->
                        <h5>Nombre completo<span class="required">*</span></h5>
                        <input type="text" name="name" class="form-control input-field" required="">
                        <h5>Correo electrónico<span class="required">*</span></h5>
                        <input type="email" name="email" class="form-control input-field" required="">
                        <h5>Teléfono<span class="required">*</span></h5>
                        <input type="text" name="phone" class="form-control input-field" required="">
                        <!-- checkbox attending-->
                        <input id="yes" type="radio" value="yes" name="attending" required="" />
                        <label for="yes" class="side-label">Acepta con gusto</label>
                        <input id="no" type="radio" value="no" name="attending" />
                        <label for="no" class="side-label">No podrá asistir</label>
                        <!-- if attending=yes then the form bellow will show -->
                        <div class="accept-form">
                            <!-- guests checkbox -->
                            <h5>¿Traerás invitados?<span class="required">*</span></h5>
                            <input id="bringing-guests" type="radio" value="yes" name="guest" /><label for="bringing-guests" class="side-label">Sí</label>
                            <input type="radio" id="just-me" value="no" name="guest" /><label for="just-me" class="side-label">No</label><br>
                            <!-- guest name text field-->
                            <div id="guest-name">
                                <h5>Nombres de invitados</h5>
                                <input type="text" name="guests" class="form-control input-field">
                            </div>
                            <!--/guest-name -->
                        </div>
                        <!--/accept form -->
                        <!-- if attending=no then only the message box will show -->
                        <div class="message-comments">
                            <h5>Canción sugerida</h5>
                            <input type="text" name="song_request" class="form-control input-field">
                            <h5>Mensaje</h5>
                            <textarea name="message" id="message-box" class="textarea-field form-control" rows="3"></textarea>
                        </div>
                        <!--/message-comments -->
                        <div class="text-center">
                            <button type="submit" id="submit_rsvp" value="Submit" class="btn">Enviar</button>
                        </div>
                        <!-- /col-md-12 -->
                    </div>
                    <!-- /Form-group -->
                    <!-- Contact results -->
                    <div id="contact_results"></div>
                </form>
                <!-- /rsvp form-->
            </div>
            <!-- /well-->
        </div>
        <!-- /container -->
    </section>
    <!-- /Section ends -->
    <!-- Footer -->
    <footer>
        <div class="container">
            <!-- Credits-->
            <div class="credits col-md-12 text-center">
                Copyright © <?= esc((string)date('Y')) ?> - <?= esc($templateMeta['footer_owner'] ?? '13Bodas') ?>
                <!-- Go To Top Link -->
                <div class="page-scroll hidden-sm hidden-xs">
                    <a href="#page-top" class="back-to-top"><i class="fa fa-angle-up"></i></a>
                </div>
            </div>
            <!-- /credits -->
        </div>
        <!-- /.container -->
    </footer>
    <!-- /footer ends -->
    <!-- Core JavaScript Files -->
    <script src="<?= $assetsBase ?>/js/jquery.min.js"></script>
    <script src="<?= $assetsBase ?>/js/bootstrap.min.js"></script>
    <script>
        var c_days = <?= (int)$countdownDays ?>;
        var c_hours = <?= (int)$countdownHours ?>;
        var c_minutes = <?= (int)$countdownMinutes ?>;
        var c_seconds = <?= (int)$countdownSeconds ?>;
        var map_markers = <?= json_encode($mapMarkers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        var map_initial_latitude = <?= $mapInitialLat !== null ? (float)$mapInitialLat : 'null' ?>;
        var map_initial_longitude = <?= $mapInitialLng !== null ? (float)$mapInitialLng : 'null' ?>;
        var slidehow_images = <?= json_encode($heroImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        var map_marker_icon = <?= json_encode($assetsBase . '/img/mapmarker.png', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        var countdown_date = <?= json_encode($countdownDateString, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <!-- Main Js -->
    <script src="<?= $assetsBase ?>/js/main.js"></script>
    <!-- RSVP form -->
    <script src="<?= $assetsBase ?>/js/rsvp.js"></script>
    <!--Other Plugins -->
    <script src="<?= $assetsBase ?>/js/plugins.js"></script>
    <!-- Prefix free CSS -->
    <script src="<?= $assetsBase ?>/js/prefixfree.js"></script>
    <!-- maps-->
    <script src="<?= $assetsBase ?>/js/map.js"></script>
    <!-- Bootstrap Select Tool (For Module #4) -->
    <script src="<?= $assetsBase ?>/switcher/js/bootstrap-select.js"></script>
    <!-- UI jQuery (For Module #5 and #6) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js" type="text/javascript"></script>
    <!-- All Scripts & Plugins -->
    <script src="<?= $assetsBase ?>/switcher/js/dmss.js"></script>
</body>

</html>