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

$assetsBase = base_url('templates/couple-heart');

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
    'event_description',
    'Nos encantará verte ahí para compartir este momento con nosotros.'
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
<html dir="ltr" lang="en">

<!-- Mirrored from unlockdesizn.com/html/wedding/couple-heart/index-singlepage.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 09 Feb 2026 12:01:29 GMT -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- css file -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/style.css">
    <!-- Responsive stylesheet -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/responsive.css">
    <!-- Title -->
    <title>Couple Heart - Wedding Event And Planner HTML Template</title>
    <!-- Favicon -->
    <link href="images/favicon.ico" sizes="128x128" rel="shortcut icon" type="image/x-icon" />
    <link href="images/favicon.ico" sizes="128x128" rel="shortcut icon" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
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
                                <img src="images/header-logo.png" class="logo logo-display hidden-md" alt="header-logo.png">
                                <img src="images/header-logo2.png" class="logo logo-scrolled" alt="">
                            </a>
                        </div>
                        <!-- End Header Navigation -->

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="collapse navbar-collapse" id="navbar-menu">
                            <ul class="nav navbar-nav navbar-center ulockd-pad9100 pull-right">
                                <li class="active scroll"><a href="#home">Home</a></li>
                                <li class="scroll"><a href="#about">About Us</a></li>
                                <li class="scroll"><a href="#story">Story</a></li>
                                <li class="scroll"><a href="#event">Event</a></li>
                                <li class="scroll"><a href="#ourmaid">Maids</a></li>
                                <li class="scroll"><a href="#blog">Blog</a></li>
                                <li class="scroll"><a href="#contact">Contact</a></li>
                            </ul>
                        </div><!-- /.navbar-collapse -->
                    </div>

                    <!-- Start Side Menu -->
                    <div class="side">
                        <a href="#" class="close-side"><i class="fa fa-times"></i></a>
                        <div class="widget">
                            <h6 class="title">Custom Pages</h6>
                            <ul class="link">
                                <li><a href="#">About</a></li>
                                <li><a href="#">Services</a></li>
                                <li><a href="#">Blog</a></li>
                                <li><a href="#">Portfolio</a></li>
                                <li><a href="#">Contact</a></li>
                            </ul>
                        </div>
                        <div class="widget">
                            <h6 class="title">Additional Links</h6>
                            <ul class="link">
                                <li><a href="#">Retina Homepage</a></li>
                                <li><a href="#">New Page Examples</a></li>
                                <li><a href="#">Parallax Sections</a></li>
                                <li><a href="#">Shortcode Central</a></li>
                                <li><a href="#">Ultimate Font Collection</a></li>
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
                            <li data-target="#carousel" data-slide-to="0" class="active"></li>
                            <li data-target="#carousel" data-slide-to="1" class=""></li>
                            <li data-target="#carousel" data-slide-to="2" class=""></li>
                        </ol>
                        <!-- Carousel items -->
                        <div class="carousel-inner carousel-zoom">
                            <div class="item active"><img class="img-responsive" src="images/home/h1.jpg" alt="h1.jpg">
                                <div class="carousel-caption">
                                    <h2 data-animation="animated bounceInDown">WE ARE GETTING MARRIED</h2>
                                    <h1 data-animation="animated zoomInLeft" class="cap-txt">Jessica & William</h1>
                                    <p data-animation="animated zoomInRight">15 November 2018</p>
                                    <a data-animation="animated bounceInUp" href="#rsvp_forms" data-toggle="modal" class="btn btn-lg ulockd-btn-thm2 hidden-xs bdrs20" title="CLick On It">RSVP NOW</a>
                                </div>
                            </div>
                            <div class="item"><img class="img-responsive" src="images/home/h2.jpg" alt="h2.jpg">
                                <div class="carousel-caption style2">
                                    <h1 data-animation="animated zoomInLeft" class="cap-txt xxss">The Wedding Day</h1>
                                    <h2 data-animation="animated bounceInDown">15 November 2018 <br>Save The Date</h2>
                                </div>
                            </div>
                            <div class="item"><img class="img-responsive" src="images/home/h3.jpg" alt="h3.jpg">
                                <div class="carousel-caption animated slideInLeft style3">
                                    <h1 data-animation="animated zoomInLeft" class="cap-txt">The Wedding Day</h1>
                                    <p data-animation="animated zoomInRight">15 November 2018</p>
                                    <h2 data-animation="animated bounceInUp">Save The Date</h2>
                                </div>
                            </div>
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
                            <h2 class="text-thm2">The Couple</h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6 ulockd-pad340">
                                <div class="about-box">
                                    <div class="about-details text-right tac-xsd">
                                        <h2 class="text-thm2">Bride</h2>
                                        <h4>Jessica Rothe</h4>
                                        <p class="fz16">Hi, it's jessica rothe Nostrum similique quae vel libero inventore et magni provident quaerat obcaecati commodi aliquam, nisi voluptate, consectetur ipsum ipsam ad sequi sit excepturi.</p>
                                        <img alt="signeture.png" src="images/about/bride-signeture.png">
                                        <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                            <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                            <li><a href="#"><i class="fa fa-google"></i></a></li>
                                            <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                            <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 ulockd-pad940">
                                <div class="about-box">
                                    <div class="about-thumb tac-xsd">
                                        <img src="images/about/bride.jpg" alt="bride.jpg">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-12">
                        <div class="couple-img">
                            <img class="img-responsive" src="images/about/couple-heart.png" alt="couple-heart.png">
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6 ulockd-pad340">
                                <div class="about-box">
                                    <div class="about-thumb tac-xsd">
                                        <img class="fn-xsd pull-right" src="images/about/groom.jpg" alt="groom.jpg">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 ulockd-pad940">
                                <div class="about-box tac-xsd">
                                    <div class="about-details">
                                        <h2 class="text-thm2">Groom</h2>
                                        <h4>William Moseley</h4>
                                        <p class="fz16">Hi, it's william moseley Nostrum similique quae vel libero inventore et magni provident quaerat obcaecati commodi aliquam, nisi voluptate, consectetur ipsum ipsam ad sequi sit excepturi.</p>
                                        <img alt="signeture.png" src="images/about/groom-signeture.png">
                                        <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                            <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                            <li><a href="#"><i class="fa fa-google"></i></a></li>
                                            <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                            <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our First Divider -->
        <section class="testimonial parallax ulockd_bgi1 overlay-tc8" data-stellar-background-ratio="0.3">
            <div class="container">
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-4 p0-mdd">
                        <div class="wedding-invitation tac-xsd">
                            <h2>Left The Event</h2>
                            <h3 class="ff-alex">15 November 2018.</h3>
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
        <section id="story" class="ulockd-about bgc-overlay-white9 ulockd_bgp3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2">Our Love Story</h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="our-story timeline">
                            <li>
                                <div class="timeline-badge"><i class="glyphicon glyphicon-check"></i></div>
                                <div class="timeline-panel left">
                                    <div class="timeline-body w50prcnt pull-left ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">OUR FIRST MEET</h3>
                                        <h5>FEEL CHANGED THE LIFE</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, temporibus, doloremque. Excepturi iusto perferendis ad esse!</span></p>
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right">
                                        <img src="images/about/s1.jpg" alt="1.png">
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-inverted">
                                <div class="timeline-badge warning"><i class="glyphicon glyphicon-credit-card"></i></div>
                                <div class="timeline-panel right">
                                    <div class="timeline-body w50prcnt pull-left">
                                        <img src="images/about/s2.jpg" alt="s2.jpg">
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">OUR FIRST DATE</h3>
                                        <h5>SPENDING BEAUTIFUL DAY</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, temporibus, doloremque. Excepturi iusto perferendis ad esse!</span></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="timeline-badge danger"><i class="glyphicon glyphicon-credit-card"></i></div>
                                <div class="timeline-panel left">
                                    <div class="timeline-body w50prcnt pull-left ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">LOVE PROPOSAL</h3>
                                        <h5>THAT WAS AWESOME</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, temporibus, doloremque. Excepturi iusto perferendis ad esse!</span></p>
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right">
                                        <img src="images/about/s3.jpg" alt="s3.jpg">
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-inverted">
                                <div class="timeline-panel right">
                                    <div class="timeline-body w50prcnt pull-left">
                                        <img src="images/about/s4.jpg" alt="s4.jpg">
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">OUR FIRST KISS</h3>
                                        <h5>FEELING WAS AWESOME</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, temporibus, doloremque. Excepturi iusto perferendis ad esse!</span></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="timeline-badge info"><i class="glyphicon glyphicon-floppy-disk"></i></div>
                                <div class="timeline-panel left">
                                    <div class="timeline-body w50prcnt pull-left ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">WE TOGETHER NOW</h3>
                                        <h5>LOVELY TIME SPENDING</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, temporibus, doloremque. Excepturi iusto perferendis ad esse!</span></p>
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right">
                                        <img src="images/about/s5.jpg" alt="s5.jpg">
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="timeline-panel left">
                                    <div class="timeline-body w50prcnt pull-left ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">WEDDING PROPOSAL</h3>
                                        <h5>INSPIRATION OF LIFE</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, doloremque.</span></p>
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right">
                                        <img src="images/about/s6.jpg" alt="s6.jpg">
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-inverted">
                                <div class="timeline-badge success"><i class="glyphicon glyphicon-thumbs-up"></i></div>
                                <div class="timeline-panel right">
                                    <div class="timeline-body w50prcnt pull-left">
                                        <img src="images/about/s7.jpg" alt="s7.jpg">
                                    </div>
                                    <div class="timeline-body w50prcnt pull-right ulockd-pdng15">
                                        <h3 class="timeline-title text-thm2">ACCEPT PROPOSAL</h3>
                                        <h5>FEEL CHANGED THE LIFE</h5>
                                        <p><small class="text-muted badge ulockd-bgthm"><span class="text-thm2"></span> 31 Oct 2015</small></p>
                                        <p>Amet consectetur adipisicing elit. Voluptatem, <span class="hiden414"> at placeat fugit magnam. Quam, doloremque.</span></p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Gallery -->
        <section id="photostack-3" class="photostack ulockd-bgthm">
            <div>
                <figure>
                    <a href="images/gallery/1.jpg" class="photostack-img popup-img"><img src="images/gallery/1.jpg" alt="1.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">The Wedding</h2>
                        <div class="photostack-back">
                            <p>Iorem ipsum dolor sit amet, consectetur adipisicing elit. Et fugiat a fugit nostrum officia corporis quos sed suscipit laudantium dolore! Reprehenderit praesentium nam odio perferendis perspiciatis, voluptatem? </p>
                        </div>
                    </figcaption>
                </figure>
                <figure>
                    <a href="images/gallery/1a.jpg" class="photostack-img popup-img"><img src="images/gallery/1a.jpg" alt="1a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Happy Days</h2>
                        <div class="photostack-back">
                            <p>Iorem ipsum dolor sit amet, consectetur adipisicing elit. Et fugiat a fugit nostrum officia corporis quos sed suscipit laudantium dolore! Reprehenderit praesentium nam odio perferendis perspiciatis, voluptatem? </p>
                        </div>
                    </figcaption>
                </figure>
                <figure>
                    <a href="images/gallery/2.jpg" class="photostack-img popup-img"><img src="images/gallery/2.jpg" alt="2.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Beautywood</h2>
                        <div class="photostack-back">
                            <p>Iorem ipsum dolor sit amet, consectetur adipisicing elit. Et fugiat a fugit nostrum officia corporis quos sed suscipit laudantium dolore! Reprehenderit praesentium nam odio perferendis perspiciatis, voluptatem? </p>
                        </div>
                    </figcaption>
                </figure>
                <figure>
                    <a href="images/gallery/2a.jpg" class="photostack-img popup-img"><img src="images/gallery/2a.jpg" alt="2a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Serenity Beach</h2>
                        <div class="photostack-back">
                            <p>Iorem ipsum dolor sit amet, consectetur adipisicing elit. Et fugiat a fugit nostrum officia corporis quos sed suscipit laudantium dolore! Reprehenderit praesentium nam odio perferendis perspiciatis, voluptatem? </p>
                        </div>
                    </figcaption>
                </figure>
                <figure>
                    <a href="images/gallery/3.jpg" class="photostack-img popup-img"><img src="images/gallery/3.jpg" alt="3.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Heaven of time</h2>
                        <div class="photostack-back">
                            <p>Iorem ipsum dolor sit amet, consectetur adipisicing elit. Et fugiat a fugit nostrum officia corporis quos sed suscipit laudantium dolore! Reprehenderit praesentium nam odio perferendis perspiciatis, voluptatem? </p>
                        </div>
                    </figcaption>
                </figure>
                <figure>
                    <a href="images/gallery/3a.jpg" class="photostack-img popup-img"><img src="images/gallery/3a.jpg" alt="3a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Forever this</h2>
                        <div class="photostack-back">
                            <p>Iorem ipsum dolor sit amet, consectetur adipisicing elit. Et fugiat a fugit nostrum officia corporis quos sed suscipit laudantium dolore! Reprehenderit praesentium nam odio perferendis perspiciatis, voluptatem? </p>
                        </div>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/4.jpg" class="photostack-img popup-img"><img src="images/gallery/4.jpg" alt="4.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Lovely Green</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/4a.jpg" class="photostack-img popup-img"><img src="images/gallery/4a.jpg" alt="4a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Wonderful</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/5.jpg" class="photostack-img popup-img"><img src="images/gallery/5.jpg" alt="5.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Love Addict</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/5a.jpg" class="photostack-img popup-img"><img src="images/gallery/5a.jpg" alt="5a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Friendship</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/6.jpg" class="photostack-img popup-img"><img src="images/gallery/6.jpg" alt="6.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">White Nights</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/6a.jpg" class="photostack-img popup-img"><img src="images/gallery/6a.jpg" alt="6a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Serendipity</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/7.jpg" class="photostack-img popup-img"><img src="images/gallery/7.jpg" alt="7.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Pure Soul</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/7a.jpg" class="photostack-img popup-img"><img src="images/gallery/7a.jpg" alt="7a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Winds of Peace</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/8.jpg" class="photostack-img popup-img"><img src="images/gallery/8.jpg" alt="8.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Shades of blue</h2>
                    </figcaption>
                </figure>
                <figure data-dummy>
                    <a href="images/gallery/8a.jpg" class="photostack-img popup-img"><img src="images/gallery/8a.jpg" alt="8a.jpg" /></a>
                    <figcaption>
                        <h2 class="photostack-title">Lightness</h2>
                    </figcaption>
                </figure>
            </div>
        </section>

        <!-- Our Event -->
        <section id="event" class="events bgc-overlay-white7 ulockd_bgp3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2">Our Events</h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="event-box">
                            <div class="thumb">
                                <img class="img-responsive" src="images/event/1.jpg" alt="1.jpg">
                            </div>
                            <div class="details">
                                <h3>Event Location</h3>
                                <ul class="list-unstyled">
                                    <li><a href="#"><i class="fa fa-calendar text-thm2"></i> Friday, 15 November 2018</a></li>
                                    <li><a href="#"><i class="fa fa-clock-o text-thm2"></i> 2:30 PM - 5:30 PM</a></li>
                                    <li><a href="#"><i class="fa fa-home text-thm2"></i> City Hall Park, </a></li>
                                    <li><a href="#"><i class="fa fa-map-marker text-thm2"></i> New York, NY 10007</a></li>
                                </ul>
                                <p>Our Chosen Place, You And Your Family Are Invited, <strong class="text-thm2"> Thanks</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="event-box">
                            <div class="thumb">
                                <img class="img-responsive" src="images/event/2.jpg" alt="2.jpg">
                            </div>
                            <div class="details">
                                <h3>The Wedding</h3>
                                <ul class="list-unstyled">
                                    <li><a href="#"><i class="fa fa-calendar text-thm2"></i> Friday, 15 November 2018</a></li>
                                    <li><a href="#"><i class="fa fa-clock-o text-thm2"></i> 2:30 PM - 5:30 PM</a></li>
                                    <li><a href="#"><i class="fa fa-home text-thm2"></i> City Hall Park, </a></li>
                                    <li><a href="#"><i class="fa fa-map-marker text-thm2"></i> New York, NY 10007</a></li>
                                </ul>
                                <p>Our Chosen Place, You And Your Family Are Invited, <strong class="text-thm2"> Thanks</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="event-box">
                            <div class="thumb">
                                <img class="img-responsive" src="images/event/3.jpg" alt="3.jpg">
                            </div>
                            <div class="details">
                                <h3>Gift Registry</h3>
                                <ul class="list-unstyled">
                                    <li><a href="#"><i class="fa fa-calendar text-thm2"></i> Friday, 15 November 2018</a></li>
                                    <li><a href="#"><i class="fa fa-clock-o text-thm2"></i> 2:30 PM - 5:30 PM</a></li>
                                    <li><a href="#"><i class="fa fa-home text-thm2"></i> City Hall Park, </a></li>
                                    <li><a href="#"><i class="fa fa-map-marker text-thm2"></i> New York, NY 10007</a></li>
                                </ul>
                                <p>Our Chosen Place, You And Your Family Are Invited, <strong class="text-thm2"> Thanks</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="event-box">
                            <div class="thumb">
                                <img class="img-responsive" src="images/event/4.jpg" alt="4.jpg">
                            </div>
                            <div class="details">
                                <h3>Reception/Party</h3>
                                <ul class="list-unstyled">
                                    <li><a href="#"><i class="fa fa-calendar text-thm2"></i> Friday, 15 November 2018</a></li>
                                    <li><a href="#"><i class="fa fa-clock-o text-thm2"></i> 2:30 PM - 5:30 PM</a></li>
                                    <li><a href="#"><i class="fa fa-home text-thm2"></i> City Hall Park, </a></li>
                                    <li><a href="#"><i class="fa fa-map-marker text-thm2"></i> New York, NY 10007</a></li>
                                </ul>
                                <p>Our Chosen Place, You And Your Family Are Invited, <strong class="text-thm2"> Thanks</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row ulockd-mrgn1240">
                    <div class="col-md-12 text-center">
                        <a href="#rsvp_forms" data-toggle="modal" class="btn btn-lg ulockd-btn-thm2 bdrs20" title="CLick On It">RSVP NOW</a>
                        <!-- Product Popup View Start-->
                        <div class="modal fade rsvp_forms" id="rsvp_forms">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header text-center">
                                        <a href="#" data-dismiss="modal" class="class pull-right"><span class="glyphicon glyphicon-remove"></span></a>
                                        <h3 class="modal-title">RSVP Form. </h3>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-10 col-md-offset-1">
                                                <!-- RSVP Form Starts -->
                                                <form id="rsvp_form" name="rsvp_form" class="rsvp_form text-left" method="post" novalidate="novalidate">
                                                    <div class="messages"></div>
                                                    <div class="row">
                                                        <div class="col-xs-12 col-sm-12 col-md-12 text-center clearfix">
                                                            <h1 class="text-thm2 ff-engnmt">Jessica & William Wedding RSVP</h1>
                                                            <h5>You're Invited To Jessica & William Wedding Reception On 15 November 2018.</h5>
                                                        </div>
                                                        <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                                            <div class="form-group">
                                                                <label for="form_name">Full Name <small>*</small></label>
                                                                <input id="form_name" name="form_name" class="form-control" placeholder="Enter a name">
                                                            </div>
                                                        </div>
                                                        <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                                            <div class="form-group">
                                                                <label for="FormControlSelect1">Are You Attending?</label>
                                                                <select class="form-control" id="FormControlSelect1">
                                                                    <option>Select an option</option>
                                                                    <option>All event</option>
                                                                    <option>Wedding</option>
                                                                    <option>Reception</option>
                                                                    <option>No</option>
                                                                    <option>Other</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                                            <div class="form-group">
                                                                <label for="FormControlSelect2">Your Meal Selection</label>
                                                                <select class="form-control" id="FormControlSelect2">
                                                                    <option>Select an option</option>
                                                                    <option>Chicken</option>
                                                                    <option>Beef</option>
                                                                    <option>vegetarian</option>
                                                                    <option>Other</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                                            <div class="form-group">
                                                                <label for="FormControlSelect3">Are You Bringing a guest?</label>
                                                                <select class="form-control" id="FormControlSelect3">
                                                                    <option>Select an option</option>
                                                                    <option>1</option>
                                                                    <option>2</option>
                                                                    <option>3</option>
                                                                    <option>More..</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                                            <div class="form-group">
                                                                <label for="form_name2">Guest Name</label>
                                                                <input id="form_name2" name="form_name2" class="form-control" placeholder="Enter a name">
                                                            </div>
                                                        </div>
                                                        <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                                            <div class="form-group">
                                                                <label for="FormControlSelect4">Meal Selection</label>
                                                                <select class="form-control" id="FormControlSelect4">
                                                                    <option>Select an option</option>
                                                                    <option>Chicken</option>
                                                                    <option>Beef</option>
                                                                    <option>vegetarian</option>
                                                                    <option>Other</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group text-center">
                                                        <input id="form_botcheck" name="form_botcheck" class="form-control" value="" type="hidden">
                                                        <button type="submit" class="btn btn-lg ulockd-btn-thm2 bdrs20">Send Us</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Product Popup View End-->
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Divider -->
        <section class="ulockd-video parallax ulockd_bgi2 overlay-tc75" data-stellar-background-ratio="0.3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="video-button text-center">
                            <a class="mfp-iframe mfp-youtube color-white fz60" href="https://www.youtube.com/watch?time_continue=18&amp;v=teLhLLlhfzc" title="About Our Company"><span class="flaticon-play-button"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our BridesMaids -->
        <section id="ourmaid" class="our-team bgc-overlay-white8 ulockd_bgp3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2">BridesMaids</h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/1.jpg" alt="1.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Madeleine</h3>
                                <h5 class="member-post">BrideMaids</h5>
                                <p>Beautiful Comment From <a href="#">her..</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/2.jpg" alt="2.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Mackenzie</h3>
                                <h5 class="member-post">BrideMaids</h5>
                                <p>Beautiful Comment From <a href="#">her..</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/3.jpg" alt="3.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Savannah</h3>
                                <h5 class="member-post">BrideMaids</h5>
                                <p>Beautiful Comment From <a href="#">her..</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/4.jpg" alt="4.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Isabella</h3>
                                <h5 class="member-post">BrideMaids</h5>
                                <p>Beautiful Comment From <a href="#">her..</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row ulockd-mrgn1230">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2 class="text-thm2">GroomsMen</h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/5.jpg" alt="5.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Andrew Garfield</h3>
                                <h5 class="member-post">GroomsMen</h5>
                                <p>Beautiful Comment From <a href="#">Him..</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/6.jpg" alt="6.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Chris Evans</h3>
                                <h5 class="member-post">GroomsMen</h5>
                                <p>Beautiful Comment From <a href="#">Him..</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/7.jpg" alt="7.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Tobey Maguire</h3>
                                <h5 class="member-post">GroomsMen</h5>
                                <p>Beautiful Comment From <a href="#">Him..</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 ulockd-pdng5">
                        <div class="team-one text-center">
                            <div class="team-thumb">
                                <img class="img-responsive img-whp" src="images/team/8.jpg" alt="8.jpg">
                                <div class="small-layer">
                                    <ul class="icon-font-thm list-inline ulockd-mrgn1225">
                                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google"></i></a></li>
                                        <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="team-details">
                                <h3 class="member-name">Ben Affleck</h3>
                                <h5 class="member-post">GroomsMen</h5>
                                <p>Beautiful Comment From <a href="#">Him..</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our RSVP -->
        <section class="ulockd-rsvp ulockd_bgi3 parallax" data-stellar-background-ratio="0.3">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <form id="rsvp_form3" name="rsvp_form3" class="rsvp_form3 bgc-overlay-white7" method="post" novalidate="novalidate">
                            <div class="messages"></div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 text-center clearfix">
                                    <h1 class="text-thm2 ff-engnmt">Jessica & William Wedding RSVP</h1>
                                    <p>You're Invited To Jessica & William Wedding Reception On 15 November 2018.</p>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="form_name3">Full Name <small>*</small></label>
                                        <input id="form_name3" name="form_name3" class="form-control" placeholder="Enter a name">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="FormControlSelect5">Are You Attending?</label>
                                        <select class="form-control" id="FormControlSelect5">
                                            <option>Select an option</option>
                                            <option>All event</option>
                                            <option>Wedding</option>
                                            <option>Reception</option>
                                            <option>No</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="FormControlSelect6">Your Meal Selection</label>
                                        <select class="form-control" id="FormControlSelect6">
                                            <option>Select an option</option>
                                            <option>Chicken</option>
                                            <option>Beef</option>
                                            <option>vegetarian</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="FormControlSelect7">Are You Bringing a guest?</label>
                                        <select class="form-control" id="FormControlSelect7">
                                            <option>Select an option</option>
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                            <option>More..</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="form_name4">Guest Name</label>
                                        <input id="form_name4" name="form_name4" class="form-control" placeholder="Enter a name">
                                    </div>
                                </div>
                                <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-6 clearfix">
                                    <div class="form-group">
                                        <label for="FormControlSelect8">Meal Selection</label>
                                        <select class="form-control" id="FormControlSelect8">
                                            <option>Select an option</option>
                                            <option>Chicken</option>
                                            <option>Beef</option>
                                            <option>vegetarian</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-2 col-md-12 clearfix">
                                    <div class="form-group text-center">
                                        <input id="form_botcheck2" name="form_botcheck2" class="form-control" value="" type="hidden">
                                        <button type="submit" class="btn btn-lg ulockd-btn-thm2 bdrs20">Send Us</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Blog -->
        <section id="blog" class="ulockd-blog bgc-overlay-white8 ulockd_bgp3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2><span class="text-thm2">Our Blog</span></h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="blog-post text-center wow fadeInUp" data-wow-duration="1s">
                            <div class="thumb">
                                <img class="img-responsive img-whp" src="images/blog/1.jpg" alt="1.jpg">
                            </div>
                            <div class="details">
                                <h4 class="eventdate text-center ulockd-bgthm">Let's Move With Our Blog</h4>
                                <h5>Our Beautiful Events</h5>
                                <h3 class="post-title tdu-hvr">Tue 15 May 2018</h3>
                                <p>Uorem ipsum dolor sit amet, consectetur adipisicing elit. Officiis nostrum hic quae ab nihil fuga? Maiores.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="blog-post text-center wow fadeInUp" data-wow-duration="1.3s">
                            <div class="thumb">
                                <img class="img-responsive img-whp" src="images/blog/2.jpg" alt="2.jpg">
                            </div>
                            <div class="details">
                                <h4 class="eventdate text-center ulockd-bgthm">Let's Move With Our Blog</h4>
                                <h5>Our Beautiful Events</h5>
                                <h3 class="post-title tdu-hvr">Tue 15 May 2018</h3>
                                <p>Uorem ipsum dolor sit amet, consectetur adipisicing elit. Officiis nostrum hic quae ab nihil fuga? Maiores.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="blog-post text-center wow fadeInUp" data-wow-duration="1.6s">
                            <div class="thumb">
                                <img class="img-responsive img-whp" src="images/blog/3.jpg" alt="3.jpg">
                            </div>
                            <div class="details">
                                <h4 class="eventdate text-center ulockd-bgthm">Let's Move With Our Blog</h4>
                                <h5>Our Beautiful Events</h5>
                                <h3 class="post-title tdu-hvr">Tue 15 May 2018</h3>
                                <p>Uorem ipsum dolor sit amet, consectetur adipisicing elit. Officiis nostrum hic quae ab nihil fuga? Maiores.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Contact -->
        <section id="contact" class="ulockd-contact">
            <div class="container-fluid ulockd-padz">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <div class="ulockd-main-title">
                            <h2><span class="text-thm2">Our Contact</span></h2>
                            <img src="images/resource/title-bottom.png" alt="title-bottom.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="ulockd-google-map">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3145.2543150778074!2d23.710822715553515!3d37.97119390860681!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14a1bce23216bc8b%3A0xfad295e22d902ed!2zzqTOsc6yzq3Pgc69zrEgzpTOuc6_zrzOsc-EzqzPgc63z4I!5e0!3m2!1sen!2sgr!4v1464458530549" width="600" height="550" frameborder="0" style="border:0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row ulockd-mrgn12-150 dib">
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-5 ulockd-bgthm">
                        <div class="contact-info ulockd-mrgn1225">
                            <ul class="list-unstyled">
                                <li><span class="flaticon-black-back-closed-envelope-shape" title="CHeart@email.com"> <small>CHeart@email.com </small></span></li>
                                <li><span class="flaticon-old-handphone" title="99 55 66 88 526"> <small> +99 55 66 88 526 </small></span></li>
                                <li><span class="flaticon-map-marker" title="Victoria 8007 Australia Envato HQ 121 King Street, Melbourne"> <small>Victoria 8007 Australia Envato </small></span></li>
                                <li><span class="flaticon-checkbox-pen-outline" title="Written Your Message"> <small>Left Some Word </small></span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-7 bgc-white">
                        <div class="ulockd-contact-form ulockd-mrgn1225">
                            <form id="contact_form" name="contact_form" class="contact-form" action="#" method="post" novalidate="novalidate">
                                <div class="messages"></div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input id="form_name" name="form_name" class="form-control ulockd-form-fg required" placeholder="Your name" required="required" data-error="Name is required." type="text">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input id="form_email" name="form_email" class="form-control ulockd-form-fg required email" placeholder="Your email" required="required" data-error="Email is required." type="email">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input id="form_phone" name="form_phone" class="form-control ulockd-form-fg required" placeholder="Phone" required="required" data-error="Phone Number is required." type="text">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input id="form_subject" name="form_subject" class="form-control ulockd-form-fg required" placeholder="Subject" required="required" data-error="Subject is required." type="text">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea id="form_message" name="form_message" class="form-control ulockd-form-tb required" rows="8" placeholder="Your massage" required="required" data-error="Message is required."></textarea>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                        <div class="form-group ulockd-contact-btn">
                                            <input id="form_botcheck" name="form_botcheck" class="form-control" value="" type="hidden">
                                            <button type="submit" class="btn btn-default ulockd-btn-thm2" data-loading-text="Getting Few Sec...">SUBMIT</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Partner -->
        <section class="ulockd-partner">
            <div class="container">
                <div class="row">
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <div class="ulockd-partner-thumb text-center"><img src="images/partners/partner1.png" alt="partner1.png"></div>
                    </div>
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <div class="ulockd-partner-thumb text-center"><img src="images/partners/partner2.png" alt="partner2.png"></div>
                    </div>
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <div class="ulockd-partner-thumb text-center"><img src="images/partners/partner3.png" alt="partner3.png"></div>
                    </div>
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <div class="ulockd-partner-thumb text-center"><img src="images/partners/partner4.png" alt="partner4.png"></div>
                    </div>
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <div class="ulockd-partner-thumb text-center"><img src="images/partners/partner5.png" alt="partner5.png"></div>
                    </div>
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <div class="ulockd-partner-thumb text-center"><img src="images/partners/partner6.png" alt="partner6.png"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Footer -->
        <section class="ulockd-footers bgc-overlay-white8 ulockd_bgi4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="footer-box text-center">
                            <h2 class="text-thm2">Jessica & William</h2>
                            <h1 class="text-thm2">Are Getting Married</h1>
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
                                <h3>Location</h3>
                                <p>Government buildings with lawns, benches & a fountain. 43 Park Row, New York, NY 10038.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="about-box2 text-center wow fadeInUp" data-wow-duration="1.3s">
                            <div class="ab-thumb">
                                <div class="about-icon2 text-center"><i class="flaticon-black-back-closed-envelope-shape"></i></div>
                            </div>
                            <div class="ab-details">
                                <h3>Mail Us</h3>
                                <p>coupleheart@mail.com <br> heartcouple@info.com </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxs-12 col-xs-6 col-sm-6 col-md-4">
                        <div class="about-box2 text-center wow fadeInUp" data-wow-duration="1.6s">
                            <div class="ab-thumb">
                                <div class="about-icon2 text-center"><i class="flaticon-telephone-1"></i></div>
                            </div>
                            <div class="ab-details">
                                <h3>Call Us</h3>
                                <p>+99 55 66 88 526 <br> +55 99 66 88 526</p>
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
                        <p class="color-white">Couple Heart Copyright© 2017. <a href="https://goo.gl/8HdP67" target="_blank">UnlockDesign</a> All right reserved.</p>
                    </div>
                </div>
            </div>
        </section>

        <a class="scrollToHome ulockd-bgthm" href="#"><i class="fa fa-home"></i></a>
    </div>
    <!-- Wrapper End -->
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
    <script type="text/javascript" src="<?= $assetsBase ?>/js/color-switcher.js"></script>
    <script type="text/javascript" src="<?= $assetsBase ?>/js/script.js"></script>

    <script>
        // [].slice.call( document.querySelectorAll( '.photostack' ) ).forEach( function( el ) { new Photostack( el ); } );
        new Photostack(document.getElementById('photostack-3'), {
            callback: function(item) {
                //console.log(item)
            }
        });
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
</body>

<!-- Mirrored from unlockdesizn.com/html/wedding/couple-heart/index-singlepage.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 09 Feb 2026 12:01:47 GMT -->

</html>