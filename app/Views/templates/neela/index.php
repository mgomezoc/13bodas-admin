<?php

declare(strict_types=1);
// ================================================================
// TEMPLATE: NEELA — app/Views/templates/neela/index.php
// Versión: 2.0 — Con soporte completo de datos dinámicos + fallbacks
// ================================================================

// --- Base data ---
$event           = $event ?? [];
$template        = $template ?? [];
$theme           = $theme ?? [];
$modules         = $modules ?? [];
$templateMeta    = $templateMeta ?? [];
$mediaByCategory = $mediaByCategory ?? [];
$galleryAssets   = $galleryAssets ?? [];
$registryItems   = $registryItems ?? [];
$registryStats   = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0];
$menuOptions     = $menuOptions ?? [];
$weddingParty    = $weddingParty ?? [];
$faqs            = $faqs ?? ($event['faqs'] ?? []);
$scheduleItems   = $scheduleItems ?? ($event['schedule_items'] ?? []);
$eventLocations  = $eventLocations ?? [];
$selectedGuest   = $selectedGuest ?? null;
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
$rawDefaults = $templateMeta['defaults'] ?? [];
if (isset($rawDefaults['copy']) && is_array($rawDefaults['copy'])) {
    $defaults  = $rawDefaults['copy'];
    $tplAssets = $rawDefaults['assets'] ?? [];
} else {
    $defaults  = $rawDefaults;
    $tplAssets = $templateMeta['assets'] ?? [];
}
$sectionVisibility = $theme['sections'] ?? ($templateMeta['section_visibility'] ?? []);

$slug        = esc($event['slug'] ?? '');
$coupleTitle = esc($event['couple_title'] ?? 'Nuestra Boda');
$brideName   = esc($event['bride_name'] ?? '');
$groomName   = esc($event['groom_name'] ?? '');

$startRaw     = $event['event_date_start'] ?? null;
$endRaw       = $event['event_date_end'] ?? null;
$rsvpDeadline = $event['rsvp_deadline'] ?? null;

$primaryLocation = $eventLocations[0] ?? [];
$venueName = esc($primaryLocation['name'] ?? ($event['venue_name'] ?? ''));
$venueAddr = esc($primaryLocation['address'] ?? ($event['venue_address'] ?? ''));
$lat       = $primaryLocation['geo_lat'] ?? ($event['venue_geo_lat'] ?? '');
$lng       = $primaryLocation['geo_lng'] ?? ($event['venue_geo_lng'] ?? '');
if (empty($eventLocations) && ($venueName !== '' || $venueAddr !== '' || ($lat !== '' && $lng !== ''))) {
    $eventLocations = [[
        'name' => $venueName,
        'address' => $venueAddr,
        'geo_lat' => $lat,
        'geo_lng' => $lng,
        'starts_at' => $startRaw,
    ]];
}

// --- Helper functions ---
function formatDateLabel(?string $dt, string $fmt = 'd M Y'): string
{
    if (!$dt) return '';
    try {
        return date($fmt, strtotime($dt));
    } catch (\Throwable $e) {
        return '';
    }
}
function formatTimeLabel(?string $dt, string $fmt = 'H:i'): string
{
    if (!$dt) return '';
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
    if (!$start) return '';
    $startLabel = formatTimeLabel($start);
    $endLabel = $end ? formatTimeLabel($end) : '';
    return trim($startLabel . ($endLabel ? ' - ' . $endLabel : ''));
}

$eventDateLabel = formatDateLabel($startRaw, 'd M Y');
$eventDateISO   = $startRaw ? date('c', strtotime($startRaw)) : '';
$eventTimeRange = trim(formatTimeLabel($startRaw) . ($endRaw ? ' - ' . formatTimeLabel($endRaw) : ''));
$rsvpDeadlineLabel = formatDateLabel($rsvpDeadline, 'd M Y');

$assetsBase = base_url('templates/neela');

// --- Theme (schema_json + overrides del template) ---
$schema = [];
if (!empty($template['schema_json'])) {
    $schema = json_decode($template['schema_json'], true) ?: [];
}
$themeDefaults = $schema['theme_defaults'] ?? [];
$schemaFonts  = !empty($themeDefaults['fonts'])
    ? [$themeDefaults['fonts']['heading'] ?? 'Great Vibes', $themeDefaults['fonts']['body'] ?? 'Dosis']
    : ($schema['fonts'] ?? ['Great Vibes', 'Dosis']);
$schemaColors = !empty($themeDefaults['colors'])
    ? [$themeDefaults['colors']['primary'] ?? '#86B1A1', $themeDefaults['colors']['accent'] ?? '#F5F0EB']
    : ($schema['colors'] ?? ['#86B1A1', '#F5F0EB']);

$fontHeading  = $theme['fonts']['heading'] ?? ($theme['font_heading'] ?? ($schemaFonts[0] ?? 'Great Vibes'));
$fontBody     = $theme['fonts']['body']    ?? ($theme['font_body']    ?? ($schemaFonts[1] ?? 'Dosis'));
$colorPrimary = $theme['colors']['primary'] ?? ($theme['primary']     ?? ($schemaColors[0] ?? '#86B1A1'));
$colorAccent  = $theme['colors']['accent']  ?? ($theme['accent']      ?? ($schemaColors[1] ?? '#F5F0EB'));

// --- Module finder ---
function findModule(array $modules, string $type): ?array
{
    foreach ($modules as $m) {
        if (($m['module_type'] ?? '') === $type) return $m;
    }
    return null;
}

$modCouple = findModule($modules, 'lovely.couple') ?? findModule($modules, 'couple_info');
$couplePayload = [];
if ($modCouple && !empty($modCouple['content_payload'])) {
    $raw = $modCouple['content_payload'];
    $couplePayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

$modCopy = findModule($modules, 'lovely.copy');
$copyPayload = [];
if ($modCopy && !empty($modCopy['content_payload'])) {
    $raw = $modCopy['content_payload'];
    $copyPayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

$modStory = findModule($modules, 'story') ?? findModule($modules, 'timeline');
$storyPayload = [];
if ($modStory && !empty($modStory['content_payload'])) {
    $raw = $modStory['content_payload'];
    $storyPayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

$modSchedule = findModule($modules, 'schedule');
$schedulePayload = [];
if ($modSchedule && !empty($modSchedule['content_payload'])) {
    $raw = $modSchedule['content_payload'];
    $schedulePayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}
$scheduleItems = !empty($scheduleItems) ? $scheduleItems : ($schedulePayload['items'] ?? ($schedulePayload['events'] ?? []));

$modFaq = findModule($modules, 'faq');
$faqPayload = [];
if ($modFaq && !empty($modFaq['content_payload'])) {
    $raw = $modFaq['content_payload'];
    $faqPayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}
$faqs = !empty($faqs) ? $faqs : ($faqPayload['items'] ?? []);

// --- Dynamic text with cascading fallback ---
function getText(array $copyPayload, array $defaults, string $key, string $hardcoded = ''): string
{
    return esc($copyPayload[$key] ?? ($defaults[$key] ?? $hardcoded));
}

$heroTagline       = getText($copyPayload, $defaults, 'hero_tagline', 'Save the Date');
$countdownTitle    = getText($copyPayload, $defaults, 'countdown_title', 'Falta poco para…');
$countdownSubtitle = getText($copyPayload, $defaults, 'countdown_subtitle', 'Nuestra celebración');
$ctaHeading        = getText($copyPayload, $defaults, 'cta_heading', 'Te invitamos a…');
$ctaSubheading     = getText($copyPayload, $defaults, 'cta_subheading', 'Celebrar con nosotros');
$rsvpHeading       = getText($copyPayload, $defaults, 'rsvp_heading', 'Confirma tu asistencia');
$brideSectionTitle = getText($copyPayload, $defaults, 'bride_section_title', 'La novia');
$groomSectionTitle = getText($copyPayload, $defaults, 'groom_section_title', 'El novio');
$storyTitle        = getText($copyPayload, $defaults, 'story_title', 'Nuestra historia');
$eventsTitle       = getText($copyPayload, $defaults, 'events_title', 'Detalles del evento');
$galleryTitle      = getText($copyPayload, $defaults, 'gallery_title', 'Galería');
$registryTitle     = getText($copyPayload, $defaults, 'registry_title', 'Regalos');
$partyTitle        = getText($copyPayload, $defaults, 'party_title', 'Cortejo nupcial');
$aboutTitle        = getText($copyPayload, $defaults, 'about_title', 'Nos casamos');
$aboutSubtitle     = getText($copyPayload, $defaults, 'about_subtitle', 'Estamos muy felices de compartir contigo este día tan especial.');
$timelineFooter    = getText($copyPayload, $defaults, 'timeline_footer', 'Aquí comienza nuestro para siempre');
$giftDescription   = getText($copyPayload, $defaults, 'gift_description', 'Gracias por ser parte de nuestra historia. Si deseas apoyarnos, aquí tienes algunas opciones.');
$locationTitle     = getText($copyPayload, $defaults, 'location_title', 'Ubicación');
$rsvpSubmitLabel   = getText($copyPayload, $defaults, 'rsvp_submit', 'Enviar');

$brideBio = esc($couplePayload['bride']['bio']
    ?? ($defaults['bride_bio'] ?? ($defaults['bride_bio_default'] ?? 'Gracias por ser parte de nuestra historia. Te esperamos para celebrar juntos.')));
$groomBio = esc($couplePayload['groom']['bio']
    ?? ($defaults['groom_bio'] ?? ($defaults['groom_bio_default'] ?? 'Estamos muy felices de compartir contigo este día tan especial.')));

// --- Media helpers ---
function getMediaUrl(array $mediaByCategory, string $category, int $index = 0, string $size = 'original'): string
{
    $items = $mediaByCategory[$category] ?? [];
    if (empty($items) || !isset($items[$index])) return '';

    $m = $items[$index];
    $fieldMap = ['original' => 'file_url_original', 'large' => 'file_url_large', 'thumb' => 'file_url_thumbnail'];
    $field = $fieldMap[$size] ?? 'file_url_original';

    $url = $m[$field]
        ?? ($m['file_url_original']
            ?? ($m['file_url_large'] ?? ($m['file_url_thumbnail'] ?? '')));
    if ($url !== '' && !preg_match('#^https?://#i', $url)) $url = base_url($url);
    return $url;
}

function getAllMediaUrls(array $mediaByCategory, string $category): array
{
    $items = $mediaByCategory[$category] ?? [];
    $urls = [];
    foreach ($items as $m) {
        $url = $m['file_url_large'] ?? ($m['file_url_original'] ?? '');
        if ($url !== '' && !preg_match('#^https?://#i', $url)) $url = base_url($url);
        if ($url !== '') $urls[] = $url;
    }
    return $urls;
}

$heroImages = getAllMediaUrls($mediaByCategory, 'hero');
if (empty($heroImages) && !empty($galleryAssets)) {
    foreach ($galleryAssets as $asset) {
        $heroImages[] = $asset['full'] ?? '';
    }
    $heroImages = array_values(array_filter($heroImages));
}
if (empty($heroImages)) {
    $sliderDefaults = $tplAssets['slider_images'] ?? ['images/slider/slide-1.jpg', 'images/slider/slide-2.jpg'];
    foreach ($sliderDefaults as $s) {
        $heroImages[] = $assetsBase . '/' . $s;
    }
}

$groomPhoto = getMediaUrl($mediaByCategory, 'groom');
if (!$groomPhoto) $groomPhoto = $assetsBase . '/' . ($tplAssets['couple_images'][0] ?? 'images/groom.jpg');

$bridePhoto = getMediaUrl($mediaByCategory, 'bride');
if (!$bridePhoto) $bridePhoto = $assetsBase . '/' . ($tplAssets['couple_images'][1] ?? 'images/bride.jpg');

$countdownBg = getMediaUrl($mediaByCategory, 'countdown_bg') ?: ($assetsBase . '/' . ($tplAssets['countdown_bg'] ?? 'images/countdown-bg.jpg'));
$ctaBg       = getMediaUrl($mediaByCategory, 'cta_bg') ?: ($assetsBase . '/' . ($tplAssets['cta_bg'] ?? 'images/cta-bg.jpg'));
$rsvpBg      = getMediaUrl($mediaByCategory, 'rsvp_bg') ?: ($assetsBase . '/' . ($tplAssets['rsvp_bg'] ?? 'images/rsvp-bg.jpg'));

$eventImg = $primaryLocation['image_url'] ?? '';
if ($eventImg !== '' && !preg_match('#^https?://#i', $eventImg)) {
    $eventImg = base_url($eventImg);
}
$eventImg = $eventImg ?: (getMediaUrl($mediaByCategory, 'event') ?: ($assetsBase . '/' . ($tplAssets['event_image'] ?? 'images/events/img-1.jpg')));

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
    if (empty($raw)) return [];
    if (is_array($raw)) return $raw;
    $decoded = json_decode((string)$raw, true);
    return is_array($decoded) ? $decoded : [];
}

$partyLabels = [
    'bride_side' => 'Lado de la novia',
    'groom_side' => 'Lado del novio',
    'officiant'  => 'Oficiante',
    'other'      => 'Otros',
];

$partyByCategory = [];
foreach ($weddingParty as $member) {
    $cat = $member['category'] ?? 'other';
    $partyByCategory[$cat][] = $member;
}

$brideSocialLinks = parseSocialLinks($couplePayload['bride']['social_links'] ?? ($couplePayload['bride']['social'] ?? []));
$groomSocialLinks = parseSocialLinks($couplePayload['groom']['social_links'] ?? ($couplePayload['groom']['social'] ?? []));

$timelineEntries = !empty($timelineItems)
    ? $timelineItems
    : ($storyPayload['items'] ?? ($storyPayload['events'] ?? []));

$galleryItems = $galleryAssets;
if (empty($galleryItems)) {
    $galleryUrls = getAllMediaUrls($mediaByCategory, 'gallery');
    if (!empty($galleryUrls)) {
        foreach ($galleryUrls as $url) {
            $galleryItems[] = ['full' => $url, 'thumb' => $url, 'alt' => $coupleTitle, 'caption' => ''];
        }
    } else {
        $galleryDefaults = $tplAssets['gallery_images'] ?? [];
        foreach ($galleryDefaults as $img) {
            $url = $assetsBase . '/' . $img;
            $galleryItems[] = ['full' => $url, 'thumb' => $url, 'alt' => $coupleTitle, 'caption' => ''];
        }
    }
}

$hasTimeline = !empty($timelineEntries);
$hasGallery = !empty($galleryItems);
$hasRegistry = !empty($registryItems);
$hasBrideSide = !empty($partyByCategory['bride_side']);
$hasGroomSide = !empty($partyByCategory['groom_side']);
$hasLocations = !empty($eventLocations) || ($lat !== '' && $lng !== '');

$heroTimestamp = $startRaw ? strtotime($startRaw) : null;
$heroMonth = $heroTimestamp ? strtoupper(date('M', $heroTimestamp)) : '';
$heroDay = $heroTimestamp ? date('d', $heroTimestamp) : '';
$heroYear = $heroTimestamp ? date('Y', $heroTimestamp) : '';

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
        'icon' => 'fas fa-bell',
        'infoWindow' => $locName,
    ];
}
if (empty($mapMarkers) && $lat !== '' && $lng !== '') {
    $mapMarkers[] = [
        'title' => $venueName,
        'latitude' => (float)$lat,
        'longitude' => (float)$lng,
        'icon' => 'fas fa-bell',
        'infoWindow' => $venueName,
    ];
}

$mapInitialLat = $lat !== '' ? (float)$lat : ($mapMarkers[0]['latitude'] ?? null);
$mapInitialLng = $lng !== '' ? (float)$lng : ($mapMarkers[0]['longitude'] ?? null);

$pageTitle = $templateMeta['title'] ?? ($coupleTitle !== '' ? $coupleTitle : 'Invitación');
$pageDescription = $templateMeta['description'] ?? $coupleTitle;
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="es"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="es"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="es"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="es"> <!--<![endif]-->

<head>
    <meta charset="utf-8">

    <!-- Page Title -->
    <title><?= esc($pageTitle) ?></title>

    <meta name="keywords" content="<?= esc($templateMeta['keywords'] ?? 'wedding, invitation, event') ?>">
    <meta name="description" content="<?= esc($pageDescription) ?>">
    <meta name="author" content="<?= esc($templateMeta['author'] ?? '13Bodas') ?>">

    <!-- Mobile Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Fav and touch icons -->
    <link rel="icon" href="<?= $assetsBase ?>/images/fav_touch_icons/favicon.ico" sizes="any">
    <link rel="icon" href="<?= $assetsBase ?>/images/fav_touch_icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= $assetsBase ?>/images/fav_touch_icons/apple-touch-icon-180x180.png">
    <link rel="manifest" href="<?= $assetsBase ?>/images/fav_touch_icons/manifest.json">

    <!-- IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Google Web Fonts -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&amp;display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&amp;display=swap" rel="stylesheet" type="text/css" />
    </noscript>

    <!-- Bootstrap CSS -->
    <link href="<?= $assetsBase ?>/css/bootstrap.min.css" rel="stylesheet" />

    <!-- FontAwesome CSS -->
    <link href="<?= $assetsBase ?>/css/fontawesome-all.min.css" rel="stylesheet" />

    <!-- Neela Icon Set CSS -->
    <link href="<?= $assetsBase ?>/css/neela-icon-set.css" rel="stylesheet" />

    <!-- Owl Carousel CSS -->
    <link href="<?= $assetsBase ?>/css/owl.carousel.min.css" rel="stylesheet" />

    <!-- Template CSS -->
    <link href="<?= $assetsBase ?>/css/style.css" rel="stylesheet" />

    <!-- Modernizr JS -->
    <script src="<?= $assetsBase ?>/js/modernizr-3.6.0.min.js"></script>
<?php if (!empty($isDemoMode)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/demo-watermark.css') ?>">
<?php endif; ?>
<?= $jsonLdEvent ?? '' ?>
</head>

<body>
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-banner">🚀 Evento DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar por $800 MXN</a></div>
<?php endif; ?>


    <!-- BEGIN PRELOADER -->
    <div id="preloader">
        <div class="loading-heart">
            <svg viewBox="0 0 512 512" width="100">
                <path d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z" />
            </svg>
            <div class="preloader-title">
                <?= esc($brideName) ?><br>
                <small>&</small><br>
                <?= esc($groomName) ?>
            </div>
        </div>
    </div>
    <!-- END PRELOADER -->


    <!-- BEGIN WRAPPER -->
    <div id="wrapper">

        <!-- BEGIN HEADER -->
        <header id="header">
            <div class="nav-section">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="#hero" class="nav-logo"><?= esc($coupleTitle) ?></a>

                            <!-- BEGIN MAIN MENU -->
                            <nav class="navbar">

                                <ul class="nav navbar-nav">
                                    <li><a href="#hero">Inicio</a></li>

                                    <li class="dropdown">
                                        <a href="#about-us" data-toggle="dropdown" data-hover="dropdown">Nosotros<b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <?php if ($hasTimeline): ?>
                                                <li><a href="#loveline">Loveline</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </li>

                                    <li class="dropdown">
                                        <a href="#the-wedding" data-toggle="dropdown" data-hover="dropdown">La boda<b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="#the-wedding">Invitación</a></li>
                                            <?php if ($hasLocations): ?>
                                                <li><a href="#location">Ubicación</a></li>
                                            <?php endif; ?>
                                            <?php if ($hasBrideSide): ?>
                                                <li><a href="#bridesmaids">Bridesmaids</a></li>
                                            <?php endif; ?>
                                            <?php if ($hasGroomSide): ?>
                                                <li><a href="#groomsmen">Groomsmen</a></li>
                                            <?php endif; ?>
                                            <?php if ($hasRegistry): ?>
                                                <li><a href="#giftregistry">Gift Registry</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </li>

                                    <?php if ($hasGallery): ?>
                                        <li><a href="#gallery">Galería</a></li>
                                    <?php endif; ?>

                                    <li><a href="#rsvp">RSVP</a></li>
                                </ul>

                                <button id="nav-mobile-btn"><i class="fas fa-bars"></i></button><!-- Mobile menu button -->
                            </nav>
                            <!-- END MAIN MENU -->

                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- END HEADER -->


        <!-- BEGIN HERO SECTION -->
        <section id="hero" class="bg-slideshow section-divider-bottom-1">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="hero-wrapper v-center">
                            <h2 data-animation-direction="fade" data-animation-delay="600"><?= $heroTagline ?></h2>

                            <h1 class="hero-title light ">
                                <span data-animation-direction="from-right" data-animation-delay="300"><?= esc($brideName) ?></span>
                                <small data-animation-direction="from-top" data-animation-delay="300">&</small>
                                <span data-animation-direction="from-left" data-animation-delay="300"><?= esc($groomName) ?></span>
                            </h1>

                            <?php if ($heroMonth !== '' && $heroDay !== '' && $heroYear !== ''): ?>
                                <div class="hero-subtitle light" data-animation-direction="fade" data-animation-delay="1000">
                                    <?= esc($heroMonth) ?> <span><?= esc($heroDay) ?></span> <?= esc($heroYear) ?>
                                </div>
                            <?php endif; ?>

                            <div data-animation-direction="fade" data-animation-delay="1000">
                                <a href="#rsvp" class="btn btn-light scrollto">RSVP</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
        <!-- END HERO SECTION -->


        <!-- BEGIN ABOUT US SECTION -->
        <section id="about-us">
            <div class="container">
                <div class="row about-elems-wrapper">
                    <div class="element col-md-6 col-xl-4 offset-xl-2" data-animation-direction="from-left" data-animation-delay="300">
                        <div class="image">
                            <img src="<?= esc($groomPhoto) ?>" alt="<?= esc($groomName) ?>" width="600" height="714" />
                            <div class="hover-info neela-style">
                                <div class="content">
                                    <h3><?= esc($groomName) ?><small><?= $groomSectionTitle ?></small></h3>
                                    <p><?= $groomBio ?></p>
                                    <?php if (!empty($groomSocialLinks)): ?>
                                        <ul class="sn-icons">
                                            <?php foreach ($groomSocialLinks as $link): ?>
                                                <?php
                                                $url = $link['url'] ?? ($link['link'] ?? '');
                                                $icon = $link['icon'] ?? 'fab fa-instagram-square';
                                                ?>
                                                <?php if ($url): ?>
                                                    <li><a href="<?= esc($url) ?>" target="_blank" rel="noopener"><i class="<?= esc($icon) ?>"></i></a></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="divider-about-us" data-animation-direction="fade" data-animation-delay="500">
                        <i class="icon-two-hearts"></i>
                    </div>

                    <div class="element col-md-6 col-xl-4" data-animation-direction="from-right" data-animation-delay="400">
                        <div class="image">
                            <img src="<?= esc($bridePhoto) ?>" alt="<?= esc($brideName) ?>" width="600" height="714" />
                            <div class="hover-info neela-style">
                                <div class="content">
                                    <h3><?= esc($brideName) ?><small><?= $brideSectionTitle ?></small></h3>
                                    <p><?= $brideBio ?></p>
                                    <?php if (!empty($brideSocialLinks)): ?>
                                        <ul class="sn-icons">
                                            <?php foreach ($brideSocialLinks as $link): ?>
                                                <?php
                                                $url = $link['url'] ?? ($link['link'] ?? '');
                                                $icon = $link['icon'] ?? 'fab fa-instagram-square';
                                                ?>
                                                <?php if ($url): ?>
                                                    <li><a href="<?= esc($url) ?>" target="_blank" rel="noopener"><i class="<?= esc($icon) ?>"></i></a></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="about-us-desc col-lg-8 offset-lg-2" data-animation-direction="from-bottom" data-animation-delay="300">
                        <h3><small><?= $ctaHeading ?></small><?= $ctaSubheading ?></h3>
                        <p><?= $aboutSubtitle ?></p>
                    </div>
                </div>
            </div>
        </section>
        <!-- END ABOUT US SECTION -->

        <?php if ($hasTimeline): ?>
            <!-- BEGIN OUR STORY TITLE SECTION -->
            <section id="our-story-title" class="parallax-background bg-color-overlay section-divider-bottom-2 padding-divider-top">
                <div class="section-divider-top-1 off-section"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <h1 class="section-title light"><?= $storyTitle ?></h1>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END OUR STORY TITLE SECTION -->

            <!-- BEGIN TIMELINE SECTION -->
            <section id="loveline">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
                            <div class="timeline">
                                <?php foreach ($timelineEntries as $idx => $item): ?>
                                    <?php
                                    $itemDate = (string)($item['date'] ?? $item['year'] ?? '');
                                    $itemYear = $item['year'] ?? ($itemDate ? date('Y', strtotime($itemDate)) : '');
                                    $itemTitle = esc($item['title'] ?? 'Momento especial');
                                    $itemText = esc($item['description'] ?? ($item['text'] ?? ''));
                                    $fallbackImg = getMediaUrl($mediaByCategory, 'timeline', $idx) ?: getMediaUrl($mediaByCategory, 'story', $idx) ?: ($assetsBase . '/images/timeline-first-date.jpg');
                                    $itemImg = trim((string)($item['image_url'] ?? ($item['image'] ?? '')));
                                    $imgPrimary = $itemImg !== '' ? $itemImg : $fallbackImg;
                                    if ($imgPrimary !== '' && !preg_match('#^https?://#i', $imgPrimary)) {
                                        $imgPrimary = base_url($imgPrimary);
                                    }
                                    $isEven = ($idx % 2 === 0);
                                    ?>
                                    <?php if ($itemYear): ?>
                                        <div class="year" data-animation-direction="from-top" data-animation-delay="250">
                                            <span class="neela-style"><?= esc((string)$itemYear) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="<?= $isEven ? 'template-1' : 'template-2' ?>">
                                        <div class="date" data-parallax="3" data-animation-direction="fade" data-animation-delay="350">
                                            <span class="neela-style"><?= esc($itemDate) ?></span>
                                        </div>

                                        <div class="image-1" data-parallax="-4" data-animation-direction="from-left" data-animation-delay="250">
                                            <img src="<?= esc($imgPrimary) ?>" alt="<?= $itemTitle ?>" width="600" height="600">
                                        </div>

                                        <div class="description-wrapper" data-parallax="-6" data-animation-direction="from-bottom" data-animation-delay="250">
                                            <div class="description" data-parallax="-6" data-animation-direction="from-bottom" data-animation-delay="250">
                                                <div class="neela-style">
                                                    <h4><?= $itemTitle ?></h4>
                                                    <p><?= $itemText ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="timeline_footer">
                                <div data-animation-direction="from-top" data-animation-delay="250"><i class="icon-diamond-ring"></i></div>
                                <div class="punchline" data-animation-direction="from-bottom" data-animation-delay="250"><small><?= $ctaHeading ?></small><?= $timelineFooter ?></div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
            <!-- END TIMELINE SECTION -->
        <?php endif; ?>

        <!-- BEGIN THE WEDDING SECTION -->
        <section id="the-wedding" class="parallax-background bg-color-overlay padding-divider-top section-divider-bottom-1">
            <div class="section-divider-top-1 off-section"></div>
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h1 class="section-title light"><?= $eventsTitle ?></h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 center">
                        <div class="invite neela-style" data-animation-direction="from-left" data-animation-delay="100">
                            <div class="invite_title">
                                <div class="text">
                                    Save<small>the</small>Date
                                </div>
                            </div>

                            <div class="invite_info">
                                <h2><?= esc($brideName) ?> <small>&</small> <?= esc($groomName) ?></h2>

                                <div class="uppercase"><?= esc($defaults['invite_intro'] ?? 'Nos encantaría contar con tu presencia en nuestra boda') ?></div>
                                <div class="date"><?= esc($eventDateLabel) ?><small><?= esc($eventTimeRange) ?></small></div>
                                <div class="uppercase"><?= esc($venueName) ?><br><?= esc($venueAddr) ?></div>

                                <?php if (!empty($defaults['invite_note'])): ?>
                                    <h5><?= esc($defaults['invite_note']) ?></h5>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- END THE WEDDING SECTION -->

        <?php if ($hasLocations): ?>
            <!-- BEGIN WEDDING LOCATION SECTION -->
            <section id="location">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <h2 class="section-title"><?= $locationTitle ?></h2>
                        </div>
                    </div>
                </div>

                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 col-xl-10 offset-xl-1">

                            <div class="map-info-container">
                                <div class="info-wrapper" data-animation-direction="from-bottom" data-animation-delay="100">
                                    <?php foreach ($eventLocations as $loc): ?>
                                        <?php
                                        $locName = esc($loc['name'] ?? $venueName);
                                        $locAddr = esc($loc['address'] ?? $venueAddr);
                                        $locLat = $loc['geo_lat'] ?? '';
                                        $locLng = $loc['geo_lng'] ?? '';
                                        $locTime = esc($loc['starts_at'] ?? $loc['time'] ?? $eventTimeRange);
                                        ?>
                                        <div class="location-info">
                                            <div class="neela-style">
                                                <h4 class="has-icon"><i class="icon-big-church"></i><?= $locName ?><small><?= $locTime ?></small></h4>
                                                <h5><?= $locName ?></h5>
                                                <p><?= $locAddr ?><br><?= esc($locLat) ?>, <?= esc($locLng) ?></p>

                                                <div class="info-map-divider"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="map-wrapper" data-animation-direction="fade" data-animation-delay="100">
                                    <div id="map_canvas" class="gmap"></div>

                                    <div class="map_pins">
                                        <div class="map_pin neela-style"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="center">
                                <a href="#rsvp" class="btn btn-primary scrollto">RSVP</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END WEDDING LOCATION SECTION -->
        <?php endif; ?>

        <?php if ($hasBrideSide): ?>
            <!-- BEGIN BRIDESMAIDS SECTION -->
            <section id="bridesmaids" class="parallax-background bg-color-overlay">
                <div class="section-divider-top-1 off-section"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <h2 class="section-title light"><?= esc($partyLabels['bride_side']) ?></h2>
                        </div>
                    </div>

                    <div class="row">
                        <?php foreach ($partyByCategory['bride_side'] as $member): ?>
                            <?php
                            $memberName = esc($member['name'] ?? '');
                            $memberRole = esc($member['role'] ?? $member['title'] ?? $partyLabels['bride_side']);
                            $memberImg = $member['photo_url'] ?? $member['image_url'] ?? '';
                            if ($memberImg !== '' && !preg_match('#^https?://#i', $memberImg)) {
                                $memberImg = base_url($memberImg);
                            }
                            $memberImg = $memberImg ?: ($assetsBase . '/images/bridesmaids-img1.jpg');
                            ?>
                            <div class="col-md-6 col-xl-4" data-animation-direction="from-bottom" data-animation-delay="200">
                                <div class="element">
                                    <div class="image">
                                        <img src="<?= esc($memberImg) ?>" alt="<?= $memberName ?>" width="434" height="434" />
                                        <div class="hover-info neela-style">
                                            <div class="content center">
                                                <h3><?= $memberName ?><small><?= $memberRole ?></small></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <!-- END BRIDESMAIDS SECTION -->
        <?php endif; ?>

        <?php if ($hasGroomSide): ?>
            <!-- BEGIN GROOMSMEN SECTION -->
            <section id="groomsmen" class="parallax-background bg-color-overlay">
                <div class="section-divider-top-1 off-section"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <h2 class="section-title light"><?= esc($partyLabels['groom_side']) ?></h2>
                        </div>
                    </div>

                    <div class="row">
                        <?php foreach ($partyByCategory['groom_side'] as $member): ?>
                            <?php
                            $memberName = esc($member['name'] ?? '');
                            $memberRole = esc($member['role'] ?? $member['title'] ?? $partyLabels['groom_side']);
                            $memberImg = $member['photo_url'] ?? $member['image_url'] ?? '';
                            if ($memberImg !== '' && !preg_match('#^https?://#i', $memberImg)) {
                                $memberImg = base_url($memberImg);
                            }
                            $memberImg = $memberImg ?: ($assetsBase . '/images/groomsmen-img1.jpg');
                            ?>
                            <div class="col-md-6 col-xl-4" data-animation-direction="from-bottom" data-animation-delay="200">
                                <div class="element">
                                    <div class="image">
                                        <img src="<?= esc($memberImg) ?>" alt="<?= $memberName ?>" width="434" height="434" />
                                        <div class="hover-info neela-style">
                                            <div class="content center">
                                                <h3><?= $memberName ?><small><?= $memberRole ?></small></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <!-- END GROOMSMEN SECTION -->
        <?php endif; ?>

        <?php if ($hasRegistry): ?>
            <!-- BEGIN WEDDING GIFTS SECTION -->
            <section id="giftregistry" class="section-bg-color parallax-background">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 col-xl-6">
                            <h2 class="section-title-lg uppercase desc"><small><?= esc($registryTitle) ?></small><strong>Registry</strong></h2>
                            <div class="section-desc"><?= $giftDescription ?></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 col-xl-6">
                            <ul class="wedding-gifts">
                                <?php foreach ($registryItems as $item): ?>
                                    <?php
                                    $itemTitle = esc($item['title'] ?? $item['name'] ?? 'Regalo');
                                    $itemDesc = esc($item['description'] ?? '');
                                    $itemUrl = $item['product_url'] ?? $item['external_url'] ?? '';
                                    $itemImg = $item['image_url'] ?? '';
                                    if ($itemImg !== '' && !preg_match('#^https?://#i', $itemImg)) {
                                        $itemImg = base_url($itemImg);
                                    }
                                    $itemPrice = moneyFmt($item['price'] ?? 0, $item['currency_code'] ?? 'MXN');
                                    ?>
                                    <li data-animation-direction="from-bottom" data-animation-delay="300">
                                        <div class="neela-style">
                                            <i class="icon-wedding"></i>
                                            <h3><?= $itemTitle ?></h3>

                                            <div class="info">
                                                <?php if ($itemImg): ?>
                                                    <span class="img"><img src="<?= esc($itemImg) ?>" alt="<?= $itemTitle ?>" /></span>
                                                <?php endif; ?>
                                                <?php if ($itemDesc): ?>
                                                    <p><?= $itemDesc ?></p>
                                                <?php endif; ?>
                                                <?php if ($itemUrl): ?>
                                                    <a href="<?= esc($itemUrl) ?>" class="btn btn-primary reverse" target="_blank" rel="noopener">Ver regalo</a>
                                                <?php endif; ?>
                                                <span class="btn btn-primary reverse"><?= esc($itemPrice) ?></span>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END WEDDING GIFTS SECTION -->
        <?php endif; ?>

        <?php if ($hasGallery): ?>
            <!-- BEGIN GALLERY SECTION -->
            <section id="gallery" class="section-bg-color">

                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <h1 class="section-title"><?= $galleryTitle ?></h1>
                        </div>
                    </div>
                </div>

                <div class="gallery-wrapper">
                    <div class="gallery-left"><i class="fas fa-chevron-left"></i></div>
                    <div class="gallery-right"><i class="fas fa-chevron-right"></i></div>

                    <div class="gallery-scroller">
                        <ul>
                            <?php foreach ($galleryItems as $asset): ?>
                                <?php
                                $full = $asset['full'] ?? '';
                                $thumb = $asset['thumb'] ?? $full;
                                $alt = $asset['alt'] ?? $coupleTitle;
                                if ($full !== '' && !preg_match('#^https?://#i', $full)) {
                                    $full = base_url($full);
                                }
                                if ($thumb !== '' && !preg_match('#^https?://#i', $thumb)) {
                                    $thumb = base_url($thumb);
                                }
                                ?>
                                <?php if ($full): ?>
                                    <li>
                                        <div class="hover-info">
                                            <a class="btn btn-light btn-sm only-icon" href="<?= esc($full) ?>" data-lightbox="WeddingPhotos" title="<?= esc($alt) ?>">
                                                <i class="fa fa-link"></i>
                                            </a>
                                        </div>
                                        <img src="<?= esc($thumb) ?>" alt="<?= esc($alt) ?>" width="380" height="380" />
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>
            <!-- END GALLERY SECTION -->
        <?php endif; ?>

        <!-- BEGIN CONTACTS SECTION -->
        <section id="rsvp" class="section-bg-color extra-padding-section">
            <div class="container">

                <div class="row">
                    <div class="col-lg-10 offset-lg-1 col-xl-8 offset-xl-2  col-xxl-6 offset-xxl-3">

                        <div class="form-wrapper flowers neela-style">
                            <h1 class="section-title"><?= $rsvpHeading ?></h1>

                            <form id="form-rsvp" method="post" action="<?= esc(base_url(route_to('rsvp.submit', $slug))) ?>">
                                <?= csrf_field() ?>
                                <?php if (!empty($selectedGuest['id'])): ?>
                                    <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                                    <?php if ($selectedGuestCode !== ''): ?>
                                        <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="form-floating">
                                    <input type="text" name="name" id="name" placeholder="Nombre" class="form-control required fromName" value="<?= esc($selectedGuestName) ?>">
                                    <label for="name">Nombre*</label>
                                </div>

                                <div class="form-floating">
                                    <input type="email" name="email" id="email" placeholder="E-mail" class="form-control required fromEmail" value="<?= esc($selectedGuestEmail) ?>">
                                    <label for="email">E-mail*</label>
                                </div>

                                <div class="form-floating">
                                    <input type="tel" name="phone" id="phone" placeholder="Teléfono" class="form-control" value="<?= esc($selectedGuestPhone) ?>">
                                    <label for="phone">Teléfono</label>
                                </div>

                                <div class="form-check-wrapper">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input required" type="radio" name="attending" id="attend_wedding_yes" value="yes">
                                        <label for="attend_wedding_yes">Sí, asistiré.</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input required" type="radio" name="attending" id="attend_wedding_no" value="no">
                                        <label for="attend_wedding_no">No podré asistir.</label>
                                    </div>
                                </div>

                                <div class="form-floating">
                                    <select class="form-select" aria-label="Número de invitados" name="guests" id="num_guests">
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                    </select>

                                    <label for="num_guests">Número de invitados</label>
                                </div>

                                <div class="form-floating">
                                    <input type="text" name="song_request" id="song_request" placeholder="Canción" class="form-control">
                                    <label for="song_request">Canción solicitada</label>
                                </div>

                                <div class="form-floating">
                                    <textarea id="message" name="message" placeholder="Mensaje" class="form-control" rows="4"></textarea>
                                    <label for="message">Mensaje</label>
                                </div>

                                <div class="form_status_message"></div>

                                <div class="center">
                                    <button type="submit" class="btn btn-primary"><?= $rsvpSubmitLabel ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- END CONTACTS SECTION -->


        <!-- BEGIN FOOTER -->
        <footer id="footer-onepage" class="bg-color">
            <div class="footer-widget-area">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="footer-info left">
                                <?= esc($eventDateLabel) ?><?= $eventTimeRange ? ' ' . esc($eventTimeRange) : '' ?><br>
                                <?= esc($venueAddr) ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="footer-logo">
                                <?= esc($brideName) ?><br>
                                <small>&</small><br>
                                <?= esc($groomName) ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="footer-info right">
                                <?php if (!empty($event['contact_phone'] ?? '')): ?>
                                    Tel.: <?= esc($event['contact_phone']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($event['contact_email'] ?? '')): ?>
                                    E-mail: <?= esc($event['contact_email']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="copyright">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            &copy; <?= esc((string)date('Y')) ?> <?= esc($templateMeta['footer_owner'] ?? '13Bodas') ?>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- END FOOTER -->

    </div>
    <!-- END WRAPPER -->


    <!-- Google Maps API and Map Richmarker Library -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBHOXsTqoSDPQ5eC5TChvgOf3pAVGapYog"></script>
    <script src="<?= $assetsBase ?>/js/richmarker.js"></script>

    <!-- Libs -->
    <script src="<?= $assetsBase ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?= $assetsBase ?>/js/jquery-ui.min.js"></script>
    <script src="<?= $assetsBase ?>/js/jquery-migrate-3.3.2.min.js"></script>
    <script src="<?= $assetsBase ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $assetsBase ?>/js/jquery.placeholder.min.js"></script>
    <script src="<?= $assetsBase ?>/js/ismobile.js"></script>
    <script src="<?= $assetsBase ?>/js/retina.min.js"></script>
    <script src="<?= $assetsBase ?>/js/waypoints.min.js"></script>
    <script src="<?= $assetsBase ?>/js/waypoints-sticky.min.js"></script>
    <script src="<?= $assetsBase ?>/js/owl.carousel.min.js"></script>
    <script src="<?= $assetsBase ?>/js/lightbox.min.js"></script>

    <!-- Nicescroll script to handle gallery section touch swipe -->
    <script src="<?= $assetsBase ?>/js/jquery.nicescroll.js"></script>

    <!-- Hero Background Slideshow Script -->
    <script src="<?= $assetsBase ?>/js/jquery.zoomslider.js"></script>

    <!-- Template Scripts -->
    <script src="<?= $assetsBase ?>/js/variables.js"></script>
    <script>
        var c_days = <?= (int)$countdownDays ?>;
        var c_hours = <?= (int)$countdownHours ?>;
        var c_minutes = <?= (int)$countdownMinutes ?>;
        var c_seconds = <?= (int)$countdownSeconds ?>;
        var countdown_end_msg = <?= json_encode($defaults['countdown_end_msg'] ?? '¡El evento ya comenzó!', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        var map_initial_latitude = <?= $mapInitialLat !== null ? (float)$mapInitialLat : 'null' ?>;
        var map_initial_longitude = <?= $mapInitialLng !== null ? (float)$mapInitialLng : 'null' ?>;
        var map_initial_zoom = <?= (int)($defaults['map_initial_zoom'] ?? 15) ?>;
        var map_markers = <?= json_encode($mapMarkers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        var slidehow_images = <?= json_encode($heroImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="<?= $assetsBase ?>/js/scripts.js"></script>
    <script>
        (function($) {
            'use strict';

            const $form = $('#form-rsvp');
            const $status = $form.find('.form_status_message');

            if (!$form.length) {
                return;
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                $status.html('');

                $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        dataType: 'json'
                    })
                    .done(function(resp) {
                        if (resp && resp.success) {
                            $status.html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + (resp.message || 'Confirmación registrada. ¡Gracias!') + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                            $form.trigger('reset');
                        } else {
                            $status.html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' + ((resp && resp.message) ? resp.message : 'No fue posible registrar tu confirmación.') + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                    })
                    .fail(function() {
                        $status.html('<div class="alert alert-danger alert-dismissible fade show" role="alert">No fue posible registrar tu confirmación.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    });
            });
        })(jQuery);
    </script>
    <?php if (!empty($sectionVisibility)): ?>
        <script>
            (function() {
                const visibility = <?= json_encode($sectionVisibility) ?>;
                const sectionMap = {
                    hero: ['hero'],
                    couple: ['about-us'],
                    story: ['our-story-title', 'loveline'],
                    event: ['the-wedding'],
                    location: ['location'],
                    party: ['bridesmaids', 'groomsmen'],
                    registry: ['giftregistry'],
                    gifts: ['giftregistry'],
                    gallery: ['gallery'],
                    rsvp: ['rsvp']
                };

                const isEnabled = (key) => {
                    if (Object.prototype.hasOwnProperty.call(visibility, key)) {
                        return visibility[key] !== false;
                    }
                    if (key === 'event' && Object.prototype.hasOwnProperty.call(visibility, 'events')) {
                        return visibility.events !== false;
                    }
                    if (key === 'registry' && Object.prototype.hasOwnProperty.call(visibility, 'gifts')) {
                        return visibility.gifts !== false;
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

</html>
