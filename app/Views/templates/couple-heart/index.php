<?php

declare(strict_types=1);

// ================================================================
// TEMPLATE: COUPLE-HEART — app/Views/templates/couple-heart/index.php
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
$timelineItems = $timelineItems ?? [];
$selectedGuest = $selectedGuest ?? null;
$selectedGuestName = '';
$selectedGuestEmail = '';
$selectedGuestPhone = '';
$selectedGuestCode = '';

if (!empty($selectedGuest)) {
    $selectedGuestName = trim((string) ($selectedGuest['first_name'] ?? '') . ' ' . (string) ($selectedGuest['last_name'] ?? ''));
    $selectedGuestEmail = (string) ($selectedGuest['email'] ?? '');
    $selectedGuestPhone = (string) ($selectedGuest['phone_number'] ?? '');
    $selectedGuestCode = (string) ($selectedGuest['access_code'] ?? '');
}

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
// Section visibility (override desde configuración del template)
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

$assetsBase = base_url('templates/couple-heart');

// --- Theme (schema_json + overrides del template) ---
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
    ? [$themeDefaults['colors']['primary'] ?? '#b88f6f', $themeDefaults['colors']['accent'] ?? '#f3f0ee']
    : ($schema['colors'] ?? ['#b88f6f', '#f3f0ee']);

// Retrocompatibilidad: soportar tanto estructura plana como anidada
$fontHeading = $theme['fonts']['heading'] ?? ($theme['font_heading'] ?? ($schemaFonts[0] ?? 'Great Vibes'));
$fontBody = $theme['fonts']['body'] ?? ($theme['font_body'] ?? ($schemaFonts[1] ?? 'Dosis'));
$colorPrimary = $theme['colors']['primary'] ?? ($theme['primary'] ?? ($schemaColors[0] ?? '#b88f6f'));
$colorAccent = $theme['colors']['accent'] ?? ($theme['accent'] ?? ($schemaColors[1] ?? '#f3f0ee'));

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
function coupleHeartGetText(array $copyPayload, array $defaults, string $key, string $hardcoded = ''): string
{
    return esc($copyPayload[$key] ?? ($defaults[$key] ?? $hardcoded));
}

$heroTagline = coupleHeartGetText($copyPayload, $defaults, 'hero_tagline', 'Nos casamos');
$countdownTitle = coupleHeartGetText($copyPayload, $defaults, 'countdown_title', 'Cuenta regresiva');
$countdownSubtitle = coupleHeartGetText($copyPayload, $defaults, 'countdown_subtitle', 'Nuestra celebración');
$ctaHeading = coupleHeartGetText($copyPayload, $defaults, 'cta_heading', 'Te invitamos a…');
$ctaSubheading = coupleHeartGetText($copyPayload, $defaults, 'cta_subheading', 'Celebrar con nosotros');
$rsvpHeading = coupleHeartGetText($copyPayload, $defaults, 'rsvp_heading', 'Confirma tu asistencia');
$brideSectionTitle = coupleHeartGetText($copyPayload, $defaults, 'bride_section_title', 'La novia');
$groomSectionTitle = coupleHeartGetText($copyPayload, $defaults, 'groom_section_title', 'El novio');
$storyTitle = coupleHeartGetText($copyPayload, $defaults, 'story_title', 'Nuestra historia');
$eventsTitle = 'Detalles del evento';
$agendaTitle = coupleHeartGetText($copyPayload, $defaults, 'agenda_title', 'Agenda');
$galleryTitle = coupleHeartGetText($copyPayload, $defaults, 'gallery_title', 'Galería');
$registryTitle = coupleHeartGetText($copyPayload, $defaults, 'registry_title', 'Regalos');
$partyTitle = coupleHeartGetText($copyPayload, $defaults, 'party_title', 'Cortejo nupcial');
$aboutTitle = coupleHeartGetText($copyPayload, $defaults, 'about_title', 'La pareja');
$eventIntroText = coupleHeartGetText(
    $copyPayload,
    $defaults,
    'event_description',
    ($templateMeta['description'] ?? 'Nos encantará verte ahí para compartir este momento con nosotros.')
);
$faqTitle = coupleHeartGetText($copyPayload, $defaults, 'faq_title', 'Preguntas frecuentes');

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
        $assetsBase . '/images/home/h1.jpg',
        $assetsBase . '/images/home/h2.jpg',
        $assetsBase . '/images/home/h3.jpg',
    ];
}
$heroMainImage = $heroImages[0] ?? ($assetsBase . '/images/home/h1.jpg');
$heroWallLeft = $heroImages[1] ?? ($assetsBase . '/images/home/h2.jpg');
$heroWallRight = $heroImages[2] ?? ($assetsBase . '/images/home/h3.jpg');

// Couple photos
$groomPhoto = getMediaUrl($mediaByCategory, 'groom') ?: ($assetsBase . '/images/about/groom.jpg');
$bridePhoto = getMediaUrl($mediaByCategory, 'bride') ?: ($assetsBase . '/images/about/bride.jpg');
$rsvpBg = getMediaUrl($mediaByCategory, 'rsvp_bg') ?: ($assetsBase . '/images/background/b3.jpg');
$countdownBg = getMediaUrl($mediaByCategory, 'countdown_bg');
if (!$countdownBg) {
    $countdownBg = $heroWallLeft ?: ($assetsBase . '/images/background/b1.jpg');
}

// Event images
$eventImagePrimary = getMediaUrl($mediaByCategory, 'event', 0) ?: ($assetsBase . '/images/event/1.jpg');
$eventImageSecondary = getMediaUrl($mediaByCategory, 'event', 1) ?: ($assetsBase . '/images/event/2.jpg');

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

function socialIconFromUrl(string $url): string
{
    $urlLower = strtolower($url);
    if (strpos($urlLower, 'facebook') !== false) {
        return 'fa-facebook';
    }
    if (strpos($urlLower, 'instagram') !== false) {
        return 'fa-instagram';
    }
    if (strpos($urlLower, 'pinterest') !== false) {
        return 'fa-pinterest';
    }
    if (strpos($urlLower, 'twitter') !== false || strpos($urlLower, 'x.com') !== false) {
        return 'fa-twitter';
    }
    if (strpos($urlLower, 'tiktok') !== false) {
        return 'fa-music';
    }
    return 'fa-link';
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
$hasSchedule = !empty($scheduleItems);
$hasFaqs = !empty($faqs);
$hasBlog = !empty($blogPosts);

$logoDisplay = $tplAssets['logo'] ?? 'images/header-logo.png';
$logoScrolled = $tplAssets['logo_scrolled'] ?? 'images/header-logo2.png';
if ($logoDisplay !== '' && !preg_match('#^https?://#i', $logoDisplay)) {
    $logoDisplay = $assetsBase . '/' . ltrim($logoDisplay, '/');
}
if ($logoScrolled !== '' && !preg_match('#^https?://#i', $logoScrolled)) {
    $logoScrolled = $assetsBase . '/' . ltrim($logoScrolled, '/');
}

$registryFallbacks = [
    $assetsBase . '/images/partners/partner1.png',
    $assetsBase . '/images/partners/partner2.png',
    $assetsBase . '/images/partners/partner3.png',
    $assetsBase . '/images/partners/partner4.png',
    $assetsBase . '/images/partners/partner5.png',
    $assetsBase . '/images/partners/partner6.png',
];

$galleryFallbacks = [
    $assetsBase . '/images/gallery/1.jpg',
    $assetsBase . '/images/gallery/1a.jpg',
    $assetsBase . '/images/gallery/2.jpg',
    $assetsBase . '/images/gallery/2a.jpg',
    $assetsBase . '/images/gallery/3.jpg',
    $assetsBase . '/images/gallery/3a.jpg',
    $assetsBase . '/images/gallery/4.jpg',
    $assetsBase . '/images/gallery/4a.jpg',
    $assetsBase . '/images/gallery/5.jpg',
    $assetsBase . '/images/gallery/5a.jpg',
    $assetsBase . '/images/gallery/6.jpg',
    $assetsBase . '/images/gallery/6a.jpg',
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

$brideSocial = parseSocialLinks($couplePayload['bride']['social_links'] ?? ($couplePayload['bride']['social'] ?? []));
$groomSocial = parseSocialLinks($couplePayload['groom']['social_links'] ?? ($couplePayload['groom']['social'] ?? []));

$contactEmail = esc($event['contact_email'] ?? ($defaults['contact_email'] ?? ''));
$contactPhone = esc($event['contact_phone'] ?? ($defaults['contact_phone'] ?? ''));
$contactAddress = esc($venueAddr);
?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<!-- Mirrored from unlockdesizn.com/html/wedding/couple-heart/index-singlepage.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 09 Feb 2026 12:01:29 GMT -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($pageDescription) ?>">
    <!-- css file -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/style.css">
    <!-- Responsive stylesheet -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/responsive.css">
    <!-- Custom enhancements -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/custom-enhancements.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <!-- Title -->
    <title><?= esc($pageTitle) ?> | 13Bodas</title>
    <!-- Favicon -->
    <link href="<?= $assetsBase ?>/images/favicon.ico" sizes="128x128" rel="shortcut icon" type="image/x-icon" />
    <link href="<?= $assetsBase ?>/images/favicon.ico" sizes="128x128" rel="shortcut icon" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

<?php if (!empty($isDemoMode)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/demo-watermark.css') ?>">
<?php endif; ?>
</head>

<body>
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-banner">🚀 Evento DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar por $800 MXN</a></div>
<?php endif; ?>

    <div class="wrapper">
        <div id="preloader" class="preloader">
            <div id="pre" class="preloader_container">
                <div class="preloader_disabler btn btn-default">Disable Preloader</div>
            </div>
        </div>
        <!-- Header Styles -->
        <header class="header-nav transparent">
            <div class="container">
                <!-- Start Navigation -->
                <nav class="navbar navbar-default navbar-fixed white no-background bootsnav navbar-scrollspy" data-minus-value-desktop="70" data-minus-value-mobile="55" data-speed="1000">
                    <div class="container">
                        <!-- Start Header Navigation -->
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                                <i class="fa fa-bars"></i>
                            </button>
                            <a class="navbar-brand ulockd-main-logo2" href="#brand">
                            </a>
                        </div>
                        <!-- End Header Navigation -->

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="collapse navbar-collapse" id="navbar-menu">
                            <ul class="nav navbar-nav navbar-center ulockd-pad9100 pull-right">
                                <li class="active scroll"><a href="#home">Inicio</a></li>
                                <li class="scroll"><a href="#about"><?= $aboutTitle ?></a></li>
                                <?php if ($hasStory): ?>
                                    <li class="scroll"><a href="#story"><?= $storyTitle ?></a></li>
                                <?php endif; ?>
                                <li class="scroll"><a href="#event"><?= esc($eventsTitle) ?></a></li>
                                <?php if ($hasSchedule): ?>
                                    <li class="scroll"><a href="#agenda"><?= esc($agendaTitle) ?></a></li>
                                <?php endif; ?>
                                <?php if ($hasWeddingParty): ?>
                                    <li class="scroll"><a href="#ourmaid"><?= esc($partyTitle) ?></a></li>
                                <?php endif; ?>
                                <?php if ($hasFaqs): ?>
                                    <li class="scroll"><a href="#faq"><?= esc($faqTitle) ?></a></li>
                                <?php endif; ?>
                                <?php if ($hasBlog): ?>
                                    <li class="scroll"><a href="#blog">Blog</a></li>
                                <?php endif; ?>
                                <?php if ($hasLocations): ?>
                                    <li class="scroll"><a href="#contact">Ubicación</a></li>
                                <?php endif; ?>
                            </ul>
                        </div><!-- /.navbar-collapse -->
                    </div>

                    <!-- Start Side Menu -->
                    <div class="side">
                        <a href="#" class="close-side"><i class="fa fa-times"></i></a>
                        <div class="widget">
                            <h6 class="title">Enlaces rápidos</h6>
                            <ul class="link">
                                <li><a href="#about"><?= $aboutTitle ?></a></li>
                                <li><a href="#event"><?= esc($eventsTitle) ?></a></li>
                                <?php if ($hasSchedule): ?>
                                    <li><a href="#agenda"><?= esc($agendaTitle) ?></a></li>
                                <?php endif; ?>
                                <li><a href="#rsvp">RSVP</a></li>
                            </ul>
                        </div>
                        <div class="widget">
                            <h6 class="title">Secciones</h6>
                            <ul class="link">
                                <li><a href="#photostack-3"><?= esc($galleryTitle) ?></a></li>
                                <?php if ($hasWeddingParty): ?>
                                    <li><a href="#ourmaid"><?= esc($partyTitle) ?></a></li>
                                <?php endif; ?>
                                <?php if ($hasFaqs): ?>
                                    <li><a href="#faq"><?= esc($faqTitle) ?></a></li>
                                <?php endif; ?>
                                <?php if ($hasLocations): ?>
                                    <li><a href="#contact">Ubicación</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <!-- End Side Menu -->
                </nav>
                <!-- End Navigation -->
            </div>
        </header>

        <!-- Home Design -->
        <div id="home" class="ulockd-home-slider snow_fall">
            <div class="container-fluid">
                <div class="row">
                    <div id="sg-carousel" class="carousel slide carousel-fade" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <?php foreach ($heroImages as $index => $image): ?>
                                <li data-target="#carousel" data-slide-to="<?= esc((string)$index) ?>" class="<?= $index === 0 ? 'active' : '' ?>"></li>
                            <?php endforeach; ?>
                        </ol>
                        <!-- Carousel items -->
                        <div class="carousel-inner carousel-zoom">
                            <?php foreach ($heroImages as $index => $image): ?>
                                <div class="item <?= $index === 0 ? 'active' : '' ?>">
                                    <img class="img-responsive" src="<?= esc($image) ?>" alt="hero-<?= esc((string)$index) ?>">
                                    <div class="carousel-caption<?= $index === 1 ? ' style2' : ($index === 2 ? ' animated slideInLeft style3' : '') ?>">
                                        <?php if ($index === 0): ?>
                                            <h2 data-animation="animated bounceInDown"><?= $heroTagline ?></h2>
                                            <h1 data-animation="animated zoomInLeft" class="cap-txt"><?= esc($brideName) ?> &amp; <?= esc($groomName) ?></h1>
                                            <p data-animation="animated zoomInRight"><?= esc($eventDateLabel) ?></p>
                                            <a data-animation="animated bounceInUp" href="#rsvp" class="btn btn-lg ulockd-btn-thm2 hidden-xs bdrs20" title="RSVP">RSVP</a>
                                        <?php elseif ($index === 1): ?>
                                            <h1 data-animation="animated zoomInLeft" class="cap-txt xxss"><?= $ctaHeading ?></h1>
                                            <h2 data-animation="animated bounceInDown"><?= esc($eventDateLabel) ?><br><?= $ctaSubheading ?></h2>
                                        <?php else: ?>
                                            <h1 data-animation="animated zoomInLeft" class="cap-txt"><?= $countdownSubtitle ?></h1>
                                            <p data-animation="animated zoomInRight"><?= esc($eventDateLabel) ?></p>
                                            <h2 data-animation="animated bounceInUp"><?= $countdownTitle ?></h2>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <!-- Carousel nav -->
                            <a class="carousel-control left" href="#sg-carousel" data-slide="prev">‹</a>
                            <a class="carousel-control right" href="#sg-carousel" data-slide="next">›</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our About -->
        <section id="about" class="ulockd-about bgc-overlay-white8 ulockd_bgp3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2"><?= $aboutTitle ?></h2>
                            <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6 ulockd-pad340">
                                <div class="about-box">
                                    <div class="about-details text-right tac-xsd">
                                        <h2 class="text-thm2"><?= $brideSectionTitle ?></h2>
                                        <h4><?= esc($brideName) ?></h4>
                                        <?php if ($brideSubtitle): ?>
                                            <h5><?= esc($brideSubtitle) ?></h5>
                                        <?php endif; ?>
                                        <p class="fz16"><?= $brideBio ?></p>
                                        <?php if (!empty($brideSocial)): ?>
                                            <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                                <?php foreach ($brideSocial as $link): ?>
                                                    <?php
                                                    $url = '';
                                                    if (is_string($link)) {
                                                        $url = $link;
                                                    } elseif (is_array($link)) {
                                                        $url = $link['url'] ?? ($link['link'] ?? '');
                                                    }
                                                    $url = trim((string)$url);
                                                    if ($url === '') {
                                                        continue;
                                                    }
                                                    $icon = socialIconFromUrl($url);
                                                    ?>
                                                    <li><a href="<?= esc($url) ?>" target="_blank" rel="noopener"><i class="fa <?= esc($icon) ?>"></i></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 ulockd-pad940">
                                <div class="about-box">
                                    <div class="about-thumb tac-xsd">
                                        <img src="<?= esc($bridePhoto) ?>" alt="<?= esc($brideName) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-12">
                        <div class="couple-img">
                            <img class="img-responsive" src="<?= $assetsBase ?>/images/about/couple-heart.png" alt="couple-heart">
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6 ulockd-pad340">
                                <div class="about-box">
                                    <div class="about-thumb tac-xsd">
                                        <img class="fn-xsd pull-right" src="<?= esc($groomPhoto) ?>" alt="<?= esc($groomName) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 ulockd-pad940">
                                <div class="about-box tac-xsd">
                                    <div class="about-details">
                                        <h2 class="text-thm2"><?= $groomSectionTitle ?></h2>
                                        <h4><?= esc($groomName) ?></h4>
                                        <?php if ($groomSubtitle): ?>
                                            <h5><?= esc($groomSubtitle) ?></h5>
                                        <?php endif; ?>
                                        <p class="fz16"><?= $groomBio ?></p>
                                        <?php if (!empty($groomSocial)): ?>
                                            <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                                <?php foreach ($groomSocial as $link): ?>
                                                    <?php
                                                    $url = '';
                                                    if (is_string($link)) {
                                                        $url = $link;
                                                    } elseif (is_array($link)) {
                                                        $url = $link['url'] ?? ($link['link'] ?? '');
                                                    }
                                                    $url = trim((string)$url);
                                                    if ($url === '') {
                                                        continue;
                                                    }
                                                    $icon = socialIconFromUrl($url);
                                                    ?>
                                                    <li><a href="<?= esc($url) ?>" target="_blank" rel="noopener"><i class="fa <?= esc($icon) ?>"></i></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our First Divider -->
        <section class="testimonial parallax ulockd_bgi1 overlay-tc8" data-stellar-background-ratio="0.3" style="background-image: linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), url('<?= esc($countdownBg) ?>');">
            <div class="container">
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-4 p0-mdd">
                        <div class="wedding-invitation tac-xsd">
                            <h2><?= $countdownTitle ?></h2>
                            <h3 class="ff-alex"><?= esc($eventDateLabel) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-8 text-center p0-mdd">
                        <div class="upcoming-wedding-event ulockd-flip-clock">
                            <div class="clock"></div>
                            <div class="message"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Story -->
        <?php if ($hasStory): ?>
            <section id="story" class="ulockd-about bgc-overlay-white9 ulockd_bgp3">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 text-center">
                            <div class="ulockd-main-title">
                                <h2 class="text-thm2"><?= $storyTitle ?></h2>
                                <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="our-story timeline">
                                <?php foreach ($storyItems as $index => $item): ?>
                                    <?php
                                    $title = esc($item['title'] ?? ($item['heading'] ?? ''));
                                    $subtitle = esc($item['subtitle'] ?? ($item['tagline'] ?? ''));
                                    $desc = esc($item['description'] ?? ($item['body'] ?? ''));
                                    $dateLabel = esc($item['date_label'] ?? ($item['year'] ?? ''));
                                    $imageUrl = (string)($item['image_url'] ?? ($item['image'] ?? ''));
                                    if ($imageUrl !== '' && !preg_match('#^https?://#i', $imageUrl)) {
                                        $imageUrl = base_url($imageUrl);
                                    }
                                    $imageUrl = $imageUrl ?: ($galleryFallbacks[$index % count($galleryFallbacks)] ?? $assetsBase . '/images/about/s1.jpg');
                                    $isInverted = $index % 2 === 1;
                                    ?>
                                    <li class="<?= $isInverted ? 'timeline-inverted' : '' ?>">
                                        <div class="timeline-badge<?= $isInverted ? ' warning' : '' ?>"><i class="glyphicon glyphicon-check"></i></div>
                                        <div class="timeline-panel <?= $isInverted ? 'right' : 'left' ?>">
                                            <div class="timeline-body w50prcnt pull-left ulockd-pdng15">
                                                <h3 class="timeline-title text-thm2"><?= $title ?></h3>
                                                <?php if ($subtitle): ?>
                                                    <h5><?= $subtitle ?></h5>
                                                <?php endif; ?>
                                                <?php if ($dateLabel): ?>
                                                    <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> <?= $dateLabel ?></small></p>
                                                <?php endif; ?>
                                                <?php if ($desc): ?>
                                                    <p><?= $desc ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="timeline-body w50prcnt <?= $isInverted ? 'pull-left' : 'pull-right' ?>">
                                                <img src="<?= esc($imageUrl) ?>" alt="<?= $title ?>">
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Our Gallery -->
        <section id="photostack-3" class="photostack ulockd-bgthm">
            <div>
                <?php
                $galleryUrls = getGalleryUrls($galleryAssets);
                if (empty($galleryUrls)) {
                    $galleryUrls = $galleryFallbacks;
                }
                foreach ($galleryUrls as $index => $url):
                    $title = esc($galleryAssets[$index]['title'] ?? ($galleryAssets[$index]['name'] ?? $galleryTitle));
                    $caption = esc($galleryAssets[$index]['caption'] ?? ($galleryAssets[$index]['description'] ?? ''));
                ?>
                    <figure <?= $index > 11 ? 'data-dummy' : '' ?>>
                        <a href="<?= esc($url) ?>" class="photostack-img popup-img"><img src="<?= esc($url) ?>" alt="gallery-<?= esc((string)$index) ?>" /></a>
                        <figcaption>
                            <h2 class="photostack-title"><?= $title ?></h2>
                            <?php if ($caption): ?>
                                <div class="photostack-back">
                                    <p><?= $caption ?></p>
                                </div>
                            <?php endif; ?>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Our Event -->
        <section id="event" class="events bgc-overlay-white7 ulockd_bgp3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2"><?= esc($eventsTitle) ?></h2>
                            <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxs-12 col-xs-12 col-sm-6 col-md-4 col-md-offset-4 ulockd-pdng5">
                        <div class="event-box">
                            <div class="thumb">
                                <img class="img-responsive" src="<?= esc($eventImagePrimary) ?>" alt="event">
                            </div>
                            <div class="details">
                                <h3><?= esc($venueName ?: $coupleTitle) ?></h3>
                                <ul class="list-unstyled">
                                    <?php if ($eventDateLabel): ?>
                                        <li><a href="#"><i class="fa fa-calendar text-thm2"></i> <?= esc($eventDateLabel) ?></a></li>
                                    <?php endif; ?>
                                    <?php if ($eventTimeRange): ?>
                                        <li><a href="#"><i class="fa fa-clock-o text-thm2"></i> <?= esc($eventTimeRange) ?></a></li>
                                    <?php endif; ?>
                                    <?php if ($venueName): ?>
                                        <li><a href="#"><i class="fa fa-home text-thm2"></i> <?= esc($venueName) ?></a></li>
                                    <?php endif; ?>
                                    <?php if ($venueAddr): ?>
                                        <li><a href="#"><i class="fa fa-map-marker text-thm2"></i> <?= esc($venueAddr) ?></a></li>
                                    <?php endif; ?>
                                </ul>
                                <p><?= $eventIntroText ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-12 text-center">
                        <a href="#rsvp" class="btn btn-lg ulockd-btn-thm2 bdrs20" title="RSVP">RSVP</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Divider -->
        <section class="ulockd-video parallax ulockd_bgi2 overlay-tc75" data-stellar-background-ratio="0.3" style="background-image: url('<?= esc($heroWallLeft) ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        &nbsp;
                    </div>
                </div>
            </div>
        </section>

        <!-- Agenda -->
        <?php if ($hasSchedule): ?>
            <section id="agenda" class="events bgc-overlay-white7 ulockd_bgp3">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 text-center">
                            <div class="ulockd-main-title">
                                <h2 class="text-thm2"><?= esc($agendaTitle) ?></h2>
                                <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($scheduleItems as $item): ?>
                            <?php
                            $itemTitle = esc($item['title'] ?? ($item['name'] ?? ''));
                            $itemDescription = esc($item['description'] ?? '');
                            $itemTime = formatScheduleTime($item);
                            $itemLocation = esc($item['location'] ?? ($item['venue'] ?? ''));
                            ?>
                            <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                                <div class="event-box">
                                    <div class="thumb">
                                        <img class="img-responsive" src="<?= esc($eventImageSecondary) ?>" alt="agenda">
                                    </div>
                                    <div class="details">
                                        <h3><?= $itemTitle ?></h3>
                                        <ul class="list-unstyled">
                                            <?php if ($itemTime): ?>
                                                <li><a href="#"><i class="fa fa-clock-o text-thm2"></i> <?= $itemTime ?></a></li>
                                            <?php endif; ?>
                                            <?php if ($itemLocation): ?>
                                                <li><a href="#"><i class="fa fa-map-marker text-thm2"></i> <?= $itemLocation ?></a></li>
                                            <?php endif; ?>
                                        </ul>
                                        <?php if ($itemDescription): ?>
                                            <p><?= $itemDescription ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Our Divider -->
        <section class="ulockd-video parallax ulockd_bgi2 overlay-tc75" data-stellar-background-ratio="0.3" style="background-image: url('<?= esc($heroWallLeft) ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        &nbsp;
                    </div>
                </div>
            </div>
        </section>

        <!-- Our BridesMaids -->
        <?php if ($hasWeddingParty): ?>
            <section id="ourmaid" class="our-team bgc-overlay-white8 ulockd_bgp3">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 text-center">
                            <div class="ulockd-main-title">
                                <h2 class="text-thm2"><?= esc($partyTitle) ?></h2>
                                <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                            </div>
                        </div>
                    </div>
                    <?php if ($hasBrideSide): ?>
                        <div class="row">
                            <div class="col-12">
                                <h3 class="text-center text-thm2"><?= esc($partyLabels['bride_side']) ?></h3>
                            </div>
                        </div>
                        <div class="row">
                            <?php foreach ($partyByCategory['bride_side'] as $member): ?>
                                <?php
                                $memberName = esc($member['name'] ?? ($member['full_name'] ?? ''));
                                $memberRole = esc($member['role'] ?? ($member['title'] ?? ''));
                                $memberPhoto = (string)($member['image_url'] ?? ($member['photo'] ?? ''));
                                if ($memberPhoto !== '' && !preg_match('#^https?://#i', $memberPhoto)) {
                                    $memberPhoto = base_url($memberPhoto);
                                }
                                $memberPhoto = $memberPhoto ?: ($assetsBase . '/images/team/1.jpg');
                                $memberNote = esc($member['note'] ?? ($member['message'] ?? ''));
                                ?>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                                    <div class="team-one text-center">
                                        <div class="team-thumb">
                                            <img class="img-responsive img-whp" src="<?= esc($memberPhoto) ?>" alt="<?= $memberName ?>">
                                        </div>
                                        <div class="team-details">
                                            <h3 class="member-name"><?= $memberName ?></h3>
                                            <?php if ($memberRole): ?>
                                                <h5 class="member-post"><?= $memberRole ?></h5>
                                            <?php endif; ?>
                                            <?php if ($memberNote): ?>
                                                <p><?= $memberNote ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($hasGroomSide): ?>
                    <div class="container">
                        <div class="row ulockd-mrgn1230">
                            <div class="col-md-6 col-md-offset-3 text-center">
                                <div class="ulockd-main-title">
                                    <h2 class="text-thm2"><?= esc($partyLabels['groom_side']) ?></h2>
                                    <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php foreach ($partyByCategory['groom_side'] as $member): ?>
                                <?php
                                $memberName = esc($member['name'] ?? ($member['full_name'] ?? ''));
                                $memberRole = esc($member['role'] ?? ($member['title'] ?? ''));
                                $memberPhoto = (string)($member['image_url'] ?? ($member['photo'] ?? ''));
                                if ($memberPhoto !== '' && !preg_match('#^https?://#i', $memberPhoto)) {
                                    $memberPhoto = base_url($memberPhoto);
                                }
                                $memberPhoto = $memberPhoto ?: ($assetsBase . '/images/team/5.jpg');
                                $memberNote = esc($member['note'] ?? ($member['message'] ?? ''));
                                ?>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                                    <div class="team-one text-center">
                                        <div class="team-thumb">
                                            <img class="img-responsive img-whp" src="<?= esc($memberPhoto) ?>" alt="<?= $memberName ?>">
                                        </div>
                                        <div class="team-details">
                                            <h3 class="member-name"><?= $memberName ?></h3>
                                            <?php if ($memberRole): ?>
                                                <h5 class="member-post"><?= $memberRole ?></h5>
                                            <?php endif; ?>
                                            <?php if ($memberNote): ?>
                                                <p><?= $memberNote ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <!-- FAQs -->
        <?php if ($hasFaqs): ?>
            <section id="faq">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 text-center">
                            <div class="ulockd-main-title">
                                <h2><span class="text-thm2"><?= esc($faqTitle) ?></span></h2>
                                <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <div class="faq-accordion">
                                <?php foreach ($faqs as $faqIndex => $faq): ?>
                                    <?php
                                    $question = esc($faq['question'] ?? ($faq['title'] ?? ''));
                                    $answer = esc($faq['answer'] ?? ($faq['content'] ?? ''));
                                    ?>
                                    <div class="faq-item<?= $faqIndex === 0 ? ' active' : '' ?>">
                                        <div class="faq-question" role="button" tabindex="0" aria-expanded="<?= $faqIndex === 0 ? 'true' : 'false' ?>">
                                            <div class="faq-question-text">
                                                <span class="faq-question-icon"><i class="fa fa-question"></i></span>
                                                <h4><?= $question ?></h4>
                                            </div>
                                            <span class="faq-toggle-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </span>
                                        </div>
                                        <?php if ($answer): ?>
                                            <div class="faq-answer">
                                                <div class="faq-answer-inner">
                                                    <p><?= $answer ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Our RSVP -->
        <section id="rsvp" class="ulockd-rsvp ulockd_bgi3 parallax" data-stellar-background-ratio="0.3" style="background-image: url('<?= esc($rsvpBg) ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <form id="rsvp_form3" name="rsvp_form3" class="rsvp_form3 bgc-overlay-white7" method="post" action="<?= esc(base_url(route_to('rsvp.submit', $slug))) ?>" novalidate="novalidate">
                            <?= csrf_field() ?>
                            <?php if (!empty($selectedGuest['id'])): ?>
                                <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                                <?php if ($selectedGuestCode !== ''): ?>
                                    <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="messages"></div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 text-center clearfix">
                                    <h1 class="text-thm2 ff-engnmt"><?= esc($brideName) ?> &amp; <?= esc($groomName) ?> RSVP</h1>
                                    <?php if ($rsvpDeadlineLabel): ?>
                                        <p>Por favor confirma antes del <?= esc($rsvpDeadlineLabel) ?>.</p>
                                    <?php else: ?>
                                        <p>Por favor confirma tu asistencia.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_name">Nombre completo <small>*</small></label>
                                        <input id="rsvp_name" name="name" class="form-control" placeholder="Ingresa tu nombre" required value="<?= esc($selectedGuestName) ?>">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_email">Email <small>*</small></label>
                                        <input id="rsvp_email" name="email" type="email" class="form-control" placeholder="tu@email.com" required value="<?= esc($selectedGuestEmail) ?>">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_phone">Teléfono</label>
                                        <input id="rsvp_phone" name="phone" class="form-control" placeholder="Tu teléfono" value="<?= esc($selectedGuestPhone) ?>">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_attending">¿Asistirás? <small>*</small></label>
                                        <select class="form-control" id="rsvp_attending" name="attending" required>
                                            <option value="" disabled selected>Selecciona una opción</option>
                                            <option value="accepted">Sí, asistiré</option>
                                            <option value="declined">No podré asistir</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_guests">Número de invitados</label>
                                        <input id="rsvp_guests" name="guests" type="number" min="1" class="form-control" placeholder="1">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_song">Canción sugerida</label>
                                        <input id="rsvp_song" name="song_request" class="form-control" placeholder="¿Qué canción no puede faltar?">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-12 col-sm-12 col-md-12 clearfix">
                                    <div class="form-group">
                                        <label for="rsvp_message">Mensaje</label>
                                        <textarea id="rsvp_message" name="message" class="form-control" rows="4" placeholder="Escribe un mensaje para los novios"></textarea>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-2 col-md-12 clearfix">
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-lg ulockd-btn-thm2 bdrs20">Enviar confirmación</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Blog -->
        <?php if ($hasBlog): ?>
            <section id="blog" class="ulockd-blog bgc-overlay-white8 ulockd_bgp3">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 text-center">
                            <div class="ulockd-main-title">
                                <h2><span class="text-thm2">Blog</span></h2>
                                <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($blogPosts as $post): ?>
                            <?php
                            $postTitle = esc($post['title'] ?? '');
                            $postExcerpt = esc($post['excerpt'] ?? ($post['summary'] ?? ''));
                            $postDate = esc($post['published_at'] ?? ($post['date'] ?? ''));
                            $postImage = (string)($post['image_url'] ?? '');
                            if ($postImage !== '' && !preg_match('#^https?://#i', $postImage)) {
                                $postImage = base_url($postImage);
                            }
                            $postImage = $postImage ?: ($assetsBase . '/images/blog/1.jpg');
                            ?>
                            <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                                <div class="blog-post text-center wow fadeInUp" data-wow-duration="1s">
                                    <div class="thumb">
                                        <img class="img-responsive img-whp" src="<?= esc($postImage) ?>" alt="<?= $postTitle ?>">
                                    </div>
                                    <div class="details">
                                        <h4 class="eventdate text-center ulockd-bgthm"><?= $postTitle ?></h4>
                                        <?php if ($postDate): ?>
                                            <h3 class="post-title tdu-hvr"><?= $postDate ?></h3>
                                        <?php endif; ?>
                                        <?php if ($postExcerpt): ?>
                                            <p><?= $postExcerpt ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Our Contact / Ubicación -->
        <?php if ($hasLocations): ?>
            <section id="contact">
                <div class="location-section-inner">
                    <div class="text-center">
                        <div class="ulockd-main-title">
                            <h2><span class="text-thm2">Ubicación</span></h2>
                            <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="map-wrapper">
                        <div id="map-location"></div>
                        <?php if ($lat !== '' && $lng !== ''): ?>
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= esc((string)$lat) ?>,<?= esc((string)$lng) ?>" target="_blank" rel="noopener" class="map-directions-btn">
                                <i class="fa fa-location-arrow"></i> Cómo llegar
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Venue info cards -->
                    <div class="venue-cards-row">
                        <?php if ($venueName): ?>
                            <div class="venue-card">
                                <div class="venue-card-icon"><i class="fa fa-home"></i></div>
                                <div class="venue-card-content">
                                    <h4>Lugar</h4>
                                    <p><?= esc($venueName) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($venueAddr): ?>
                            <div class="venue-card">
                                <div class="venue-card-icon"><i class="fa fa-map-marker"></i></div>
                                <div class="venue-card-content">
                                    <h4>Dirección</h4>
                                    <p><?= esc($venueAddr) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($eventDateLabel): ?>
                            <div class="venue-card">
                                <div class="venue-card-icon"><i class="fa fa-calendar"></i></div>
                                <div class="venue-card-content">
                                    <h4>Fecha</h4>
                                    <p><?= esc($eventDateLabel) ?><?= $eventTimeRange ? ' · ' . esc($eventTimeRange) : '' ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($contactPhone): ?>
                            <div class="venue-card">
                                <div class="venue-card-icon"><i class="fa fa-phone"></i></div>
                                <div class="venue-card-content">
                                    <h4>Contacto</h4>
                                    <p><a href="tel:<?= esc($contactPhone) ?>"><?= esc($contactPhone) ?></a></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Our Partner -->
        <section class="ulockd-partner">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2"><?= esc($registryTitle) ?></h2>
                            <img src="<?= $assetsBase ?>/images/resource/title-bottom.png" alt="title-bottom">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    $registryToRender = !empty($registryItems) ? $registryItems : array_map(fn($url) => ['image_url' => $url], $registryFallbacks);
                    foreach ($registryToRender as $item):
                        $itemTitle = esc($item['title'] ?? ($item['name'] ?? $registryTitle));
                        $itemImage = (string)($item['image_url'] ?? '');
                        if ($itemImage !== '' && !preg_match('#^https?://#i', $itemImage)) {
                            $itemImage = base_url($itemImage);
                        }
                        $itemImage = $itemImage ?: ($assetsBase . '/images/partners/partner1.png');
                        $itemUrl = esc($item['product_url'] ?? ($item['external_url'] ?? ''));
                    ?>
                        <div class="col-xs-6 col-sm-4 col-md-2">
                            <div class="ulockd-partner-thumb text-center">
                                <?php if ($itemUrl): ?>
                                    <a href="<?= $itemUrl ?>" target="_blank" rel="noopener"><img src="<?= esc($itemImage) ?>" alt="<?= $itemTitle ?>"></a>
                                <?php else: ?>
                                    <img src="<?= esc($itemImage) ?>" alt="<?= $itemTitle ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Our Footer -->
        <section class="ulockd-footers bgc-overlay-white8 ulockd_bgi4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="footer-box text-center">
                            <h2 class="text-thm2"><?= esc($brideName) ?> &amp; <?= esc($groomName) ?></h2>
                            <h1 class="text-thm2"><?= esc($coupleTitle) ?></h1>
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1250">
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="about-box2 text-center wow fadeInUp" data-wow-duration="1s">
                            <div class="ab-thumb">
                                <div class="about-icon2 text-center"><i class="flaticon-map-marker"></i></div>
                            </div>
                            <div class="ab-details">
                                <h3>Ubicación</h3>
                                <p><?= esc($venueAddr) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="about-box2 text-center wow fadeInUp" data-wow-duration="1.3s">
                            <div class="ab-thumb">
                                <div class="about-icon2 text-center"><i class="flaticon-black-back-closed-envelope-shape"></i></div>
                            </div>
                            <div class="ab-details">
                                <h3>Escríbenos</h3>
                                <p><?= $contactEmail ?: esc($pageTitle) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="about-box2 text-center wow fadeInUp" data-wow-duration="1.6s">
                            <div class="ab-thumb">
                                <div class="about-icon2 text-center"><i class="flaticon-telephone-1"></i></div>
                            </div>
                            <div class="ab-details">
                                <h3>Llámanos</h3>
                                <p><?= $contactPhone ?: esc($pageTitle) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our CopyRight Section -->
        <section class="ulockd-copy-right">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <p class="color-white">Couple Heart Copyright© 2017. <a href="https://goo.gl/8HdP67" target="_blank" rel="noopener">UnlockDesign</a> All right reserved.</p>
                    </div>
                </div>
            </div>
        </section>

        <a class="scrollToHome ulockd-bgthm" href="#"><i class="fa fa-home"></i></a>
    </div>
    <!-- Wrapper End -->
    <script>
        const slideshow_images = <?= json_encode($heroImages, JSON_UNESCAPED_SLASHES) ?>;
        const slidehow_images = slideshow_images;
        const c_days = <?= (int)$countdownDays ?>;
        const c_hours = <?= (int)$countdownHours ?>;
        const c_minutes = <?= (int)$countdownMinutes ?>;
        const c_seconds = <?= (int)$countdownSeconds ?>;
        const map_markers = <?= json_encode($mapMarkers, JSON_UNESCAPED_SLASHES) ?>;
        const map_initial_latitude = <?= $mapInitialLat !== null ? json_encode($mapInitialLat) : 'null' ?>;
        const map_initial_longitude = <?= $mapInitialLng !== null ? json_encode($mapInitialLng) : 'null' ?>;
    </script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/jquery-1.12.4.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/bootsnav.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/parallax.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/scrollto.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/jquery-scrolltofixed-min.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/jquery.counterup.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/gallery.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/wow.min.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/slider.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/video-player.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/jflickrfeed.min.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/jquery.barfiller.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/timepicker.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/tweetie.js"></script>
    <!-- Custom script for all pages -->
    <script type="text/javascript" src="<?= $assetsBase ?>/js/script.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>

    <script>
        // [].slice.call( document.querySelectorAll( '.photostack' ) ).forEach( function( el ) { new Photostack( el ); } );
        new Photostack(document.getElementById('photostack-3'), {
            callback: function(item) {
                //console.log(item)
            }
        });

        if (typeof L !== 'undefined' && map_initial_latitude !== null && map_initial_longitude !== null && document.getElementById('map-location')) {
            const leafletMap = L.map('map-location').setView([map_initial_latitude, map_initial_longitude], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(leafletMap);
            if (Array.isArray(map_markers)) {
                map_markers.forEach(function(marker) {
                    const markerItem = L.marker([marker.latitude, marker.longitude]).addTo(leafletMap);
                    const popupContent = marker.address ? '<strong>' + marker.title + '</strong><br>' + marker.address : marker.title;
                    markerItem.bindPopup(popupContent);
                });
            }
        }
    </script>
    <!-- FAQ Accordion -->
    <script>
        (function() {
            var faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(function(item) {
                var question = item.querySelector('.faq-question');
                if (!question) return;
                question.addEventListener('click', function() {
                    var isActive = item.classList.contains('active');
                    // Close all
                    faqItems.forEach(function(other) {
                        other.classList.remove('active');
                        var btn = other.querySelector('.faq-question');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                    });
                    // Toggle current
                    if (!isActive) {
                        item.classList.add('active');
                        question.setAttribute('aria-expanded', 'true');
                    }
                });
                question.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        question.click();
                    }
                });
            });
        })();
    </script>

    <script type='text/javascript'>
        var collectOnMe = document.querySelectorAll('.collectonme'),
            buttons = document.getElementsByTagName("input");

        for (var i = 0; i < collectOnMe.length; i++) {
            collectOnMe[i].style.display = "none";
        }

        //default options
        snowFall.snow(document.body);
        var testContainer = document.querySelector('.snow_fall'),
            testContainerIsSnowing = true;
        snowFall.snow(testContainer);

        testContainer.addEventListener('click', function(e) {
            testContainerIsSnowing = !testContainerIsSnowing;

            if (!testContainerIsSnowing) {
                snowFall.snow(testContainer, "shadows");
            } else {
                snowFall.snow(testContainer);
            }
        })

        document.getElementById("shadows", function() {
            document.body.className = "lightBg";
            snowFall.snow(document.body, "clear");
            snowFall.snow(document.body, {
                shadow: true,
                flakeCount: 1000
            });
        });
        /* More_Style_Name_Here_Is_commented_By_Class=clear,=round,=shadows,=roundshadows,=imagebut /*image : "images/resource/flake.png",*/
    </script>
    <?php if (!empty($sectionVisibility)): ?>
        <script>
            (function() {
                const visibility = <?= json_encode($sectionVisibility) ?>;
                const sectionMap = {
                    hero: ['home'],
                    couple: ['about'],
                    story: ['story'],
                    gallery: ['photostack-3'],
                    event: ['event', 'agenda'],
                    party: ['ourmaid'],
                    faq: ['faq'],
                    rsvp: ['rsvp'],
                    location: ['contact', 'map-location'],
                    blog: ['blog']
                };

                const isEnabled = (key) => {
                    if (Object.prototype.hasOwnProperty.call(visibility, key)) {
                        return visibility[key] !== false;
                    }
                    if (key === 'event' && Object.prototype.hasOwnProperty.call(visibility, 'events')) {
                        return visibility.events !== false;
                    }
                    return true;
                };

                Object.entries(sectionMap).forEach(([key, ids]) => {
                    if (isEnabled(key)) return;
                    ids.forEach((id) => {
                        const el = document.getElementById(id);
                        if (el) {
                            el.style.display = 'none';
                        }
                    });
                });
            })();
        </script>
    <?php endif; ?>
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-watermark">DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar</a></div>
<?php endif; ?>
</body>

<!-- Mirrored from unlockdesizn.com/html/wedding/couple-heart/index-singlepage.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 09 Feb 2026 12:01:47 GMT -->

</html>
