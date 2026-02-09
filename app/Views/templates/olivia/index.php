<?php
declare(strict_types=1);
// ================================================================
// TEMPLATE: LOVELY — app/Views/templates/lovely/index.php
// Versión: 2.0 — Con soporte completo de datos dinámicos + fallbacks
// ================================================================

// --- Base data ---
$event         = $event ?? [];
$template      = $template ?? [];
$theme         = $theme ?? [];
$modules       = $modules ?? [];
$templateMeta  = $templateMeta ?? [];
$mediaByCategory = $mediaByCategory ?? [];
$galleryAssets = $galleryAssets ?? [];
$registryItems = $registryItems ?? [];
$registryStats = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0];
$menuOptions   = $menuOptions ?? [];
$weddingParty  = $weddingParty ?? [];
$faqs          = $faqs ?? ($event['faqs'] ?? []);
$scheduleItems = $scheduleItems ?? ($event['schedule_items'] ?? []);
$eventLocations = $eventLocations ?? [];
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
    $defaults  = $rawDefaults['copy'];
    $tplAssets = $rawDefaults['assets'] ?? [];
} else {
    // Estructura legacy: defaults plano + assets como hermano
    $defaults  = $rawDefaults;
    $tplAssets = $templateMeta['assets'] ?? [];
}
// Section visibility (override desde theme_config del evento)
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

$assetsBase = base_url('templates/olivia');

// --- Theme (schema_json + theme_config overrides) ---
$schema = [];
if (!empty($template['schema_json'])) {
    $schema = json_decode($template['schema_json'], true) ?: [];
}
// Retrocompatibilidad: soportar tanto estructura plana como anidada (theme_defaults)
$themeDefaults = $schema['theme_defaults'] ?? [];
$schemaFonts  = !empty($themeDefaults['fonts'])
    ? [$themeDefaults['fonts']['heading'] ?? 'Great Vibes', $themeDefaults['fonts']['body'] ?? 'Dosis']
    : ($schema['fonts'] ?? ['Great Vibes', 'Dosis']);
$schemaColors = !empty($themeDefaults['colors'])
    ? [$themeDefaults['colors']['primary'] ?? '#86B1A1', $themeDefaults['colors']['accent'] ?? '#F5F0EB']
    : ($schema['colors'] ?? ['#86B1A1', '#F5F0EB']);

// Retrocompatibilidad: soportar tanto estructura plana como anidada
$fontHeading  = $theme['fonts']['heading'] ?? ($theme['font_heading'] ?? ($schemaFonts[0] ?? 'Great Vibes'));
$fontBody     = $theme['fonts']['body']    ?? ($theme['font_body']    ?? ($schemaFonts[1] ?? 'Dosis'));
$colorPrimary = $theme['colors']['primary'] ?? ($theme['primary']     ?? ($schemaColors[0] ?? '#86B1A1'));
$colorAccent  = $theme['colors']['accent']  ?? ($theme['accent']      ?? ($schemaColors[1] ?? '#F5F0EB'));

// --- Module finder (busca por module_type, NO por code) ---
function findModule(array $modules, string $type): ?array
{
    foreach ($modules as $m) {
        if (($m['module_type'] ?? '') === $type) return $m;
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

$heroTagline       = getText($copyPayload, $defaults, 'hero_tagline', 'Nos casamos');
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
$whenWhereTitle    = getText($copyPayload, $defaults, 'whenwhere_title', 'Cuándo y dónde');
$whenWhereMeta     = getText($copyPayload, $defaults, 'whenwhere_meta', 'Questions');

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

// Hero slider images: event media → template defaults
$heroImages = getAllMediaUrls($mediaByCategory, 'hero');
if (empty($heroImages)) {
    $sliderDefaults = $tplAssets['slider_images'] ?? ['images/slider/slide-1.jpg', 'images/slider/slide-2.jpg'];
    foreach ($sliderDefaults as $s) {
        $heroImages[] = $assetsBase . '/' . $s;
    }
}

// Couple photos
$groomPhoto = getMediaUrl($mediaByCategory, 'groom');
if (!$groomPhoto) $groomPhoto = $assetsBase . '/' . ($tplAssets['couple_images'][0] ?? 'images/couple/img-1.jpg');

$bridePhoto = getMediaUrl($mediaByCategory, 'bride');
if (!$bridePhoto) $bridePhoto = $assetsBase . '/' . ($tplAssets['couple_images'][1] ?? 'images/couple/img-2.jpg');

// Background images
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

$storySubtitle = getText($copyPayload, $defaults, 'story_subtitle', 'Our love.');
$storyHighlight = getText($copyPayload, $defaults, 'story_highlight', '');

$storyParagraphs = [];
if (!empty($storyPayload['paragraphs']) && is_array($storyPayload['paragraphs'])) {
    $storyParagraphs = $storyPayload['paragraphs'];
} elseif (!empty($storyPayload['content']) && is_array($storyPayload['content'])) {
    $storyParagraphs = $storyPayload['content'];
} elseif (!empty($storyPayload['text'])) {
    $storyParagraphs = preg_split("/\n+/", (string) $storyPayload['text']);
} elseif (!empty($storyPayload['description'])) {
    $storyParagraphs = preg_split("/\n+/", (string) $storyPayload['description']);
} elseif (!empty($event['description'])) {
    $storyParagraphs = preg_split("/\n+/", (string) $event['description']);
}

$storyItems = !empty($timelineItems)
    ? $timelineItems
    : ($storyPayload['items'] ?? ($storyPayload['events'] ?? []));

$storyMedia = '';
if (!empty($storyItems[0])) {
    $storyMedia = (string) ($storyItems[0]['image_url'] ?? ($storyItems[0]['image'] ?? ''));
    if ($storyMedia !== '' && !preg_match('#^https?://#i', $storyMedia)) {
        $storyMedia = base_url($storyMedia);
    }
}
$storyMedia = $storyMedia ?: (getMediaUrl($mediaByCategory, 'story') ?: $eventImg);

$brideSocial = parseSocialLinks($couplePayload['bride']['social_links'] ?? ($couplePayload['bride']['social'] ?? []));
$groomSocial = parseSocialLinks($couplePayload['groom']['social_links'] ?? ($couplePayload['groom']['social'] ?? []));

$scheduleLookup = [];
foreach ($scheduleItems as $scheduleItem) {
    $locationId = $scheduleItem['location_id'] ?? null;
    if (!$locationId || isset($scheduleLookup[$locationId])) {
        continue;
    }
    $scheduleLookup[$locationId] = $scheduleItem;
}

$venueSummary = trim(($eventDateLabel !== '' ? $eventDateLabel : '') . ($venueName !== '' ? ' — ' . $venueName : ''));
$heroLocation = trim(($eventDateLabel !== '' ? $eventDateLabel : '') . ($venueName !== '' ? ' – ' . $venueName : ''));
$pageDescription = esc((string) ($event['description'] ?? $heroTagline));

$logoImage = $tplAssets['logo'] ?? 'images/logo.png';
$logoUrl = $assetsBase . '/' . ltrim($logoImage, '/');
?>
<!DOCTYPE HTML>
<html lang="en">

<!-- Mirrored from duruthemes.com/demo/html/olivia-enrico/demo1/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 08 Feb 2026 07:57:09 GMT -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $coupleTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $pageDescription ?>">
    <meta name="keywords" content="<?= esc($event['keywords'] ?? 'boda, wedding, invitacion') ?>">
    <meta name="author" content="<?= esc($event['organizer_name'] ?? ($templateMeta['author'] ?? '13Bodas')) ?>">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/png" href="<?= $assetsBase ?>/images/favicon.png" />
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/animate.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/themify-icons.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/magnific-popup.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/owl.carousel.min.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/style.css">
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="lds-heart">
            <div></div>
        </div>
    </div>
    <!-- Main -->
    <div id="oliven-page"> <a href="#" class="js-oliven-nav-toggle oliven-nav-toggle"><i></i></a>
        <!-- Sidebar Section -->
        <aside id="oliven-aside">
            <!-- Logo -->
            <div class="oliven-logo">
                <a href="#home">
                    <img src="<?= esc($logoUrl) ?>" alt="<?= $coupleTitle ?>">
                    <span><?= $coupleTitle ?></span>
                    <?php if ($eventDateLabel !== '') : ?>
                        <h6><?= esc($eventDateLabel) ?></h6>
                    <?php endif; ?>
                </a>
            </div>
            <!-- Menu -->
            <nav class="oliven-main-menu">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#couple">Couple</a></li>
                    <li><a href="#story">Our Story</a></li>
                    <li><a href="#friends">Friends</a></li>
                    <li><a href="#organization">Organization</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#whenwhere">When &amp; Where</a></li>
                    <li><a href="#rsvp">R.S.V.P</a></li>
                    <li><a href="#gift">Gift Registry</a></li>
                </ul>
            </nav>
            <!-- Sidebar Footer -->
            <div class="footer1"> <span class="separator"></span>
                <p><?= $coupleTitle ?> wedding<br /><?= esc($venueSummary) ?></p>
            </div>
        </aside>
        <!-- Content Section -->
        <div id="oliven-main">
            <!-- Header & Slider -->
            <header id="home" class="header valign bg-img parallaxie" data-background="<?= esc($heroImages[0] ?? $assetsBase . '/images/slider.jpg') ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 text-center caption">
                            <h1 class="animate-box" data-animate-effect="fadeInUp"><?= $coupleTitle ?></h1>
                            <?php if ($heroLocation !== '') : ?>
                                <h5 class="animate-box" data-animate-effect="fadeInUp"><?= esc($heroLocation) ?></h5>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="arrow bounce text-center">
                                <a href="#couple"> <i class="ti-heart"></i> </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Couple -->
            <div id="couple" class="bridegroom clear section-padding bg-pink">
                <div class="container">
                    <div class="row mb-60">
                        <div class="col-md-6">
                            <div class="item toright mb-30 animate-box" data-animate-effect="fadeInLeft">
                                <div class="img"> <img src="<?= esc($bridePhoto) ?>" alt="<?= esc($brideName) ?>"> </div>
                                <div class="info valign">
                                    <div class="full-width">
                                        <h6><?= esc($brideName) ?> <i class="ti-heart"></i></h6> <span><?= esc($brideSectionTitle) ?></span>
                                        <p><?= $brideBio ?></p>
                                        <?php if (!empty($brideSocial)) : ?>
                                            <div class="social">
                                                <div class="full-width">
                                                    <?php foreach ($brideSocial as $social) : ?>
                                                        <?php
                                                        $socialUrl = esc($social['url'] ?? ($social['link'] ?? '#'));
                                                        $socialIcon = esc($social['icon'] ?? ($social['type'] ?? 'ti-heart'));
                                                        ?>
                                                        <a href="<?= $socialUrl ?>" class="icon" target="_blank" rel="noopener">
                                                            <i class="<?= $socialIcon ?>"></i>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item mb-30 animate-box" data-animate-effect="fadeInRight">
                                <div class="img"> <img src="<?= esc($groomPhoto) ?>" alt="<?= esc($groomName) ?>"> </div>
                                <div class="info valign">
                                    <div class="full-width">
                                        <h6><?= esc($groomName) ?> <i class="ti-heart"></i></h6> <span><?= esc($groomSectionTitle) ?></span>
                                        <p><?= $groomBio ?></p>
                                        <?php if (!empty($groomSocial)) : ?>
                                            <div class="social">
                                                <div class="full-width">
                                                    <?php foreach ($groomSocial as $social) : ?>
                                                        <?php
                                                        $socialUrl = esc($social['url'] ?? ($social['link'] ?? '#'));
                                                        $socialIcon = esc($social['icon'] ?? ($social['type'] ?? 'ti-heart'));
                                                        ?>
                                                        <a href="<?= $socialUrl ?>" class="icon" target="_blank" rel="noopener">
                                                            <i class="<?= $socialIcon ?>"></i>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center animate-box" data-animate-effect="fadeInUp">
                            <h3 class="oliven-couple-title"><?= esc($ctaHeading) ?></h3>
                            <h4 class="oliven-couple-subtitle"><?= esc($ctaSubheading) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Countdown -->
            <div id="countdown" class="section-padding bg-img bg-fixed" data-background="<?= esc($countdownBg) ?>">
                <div class="container">
                    <div class="row">
                        <div class="section-head col-md-12">
                            <h4><?= esc($countdownTitle) ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <ul>
                                <li><span id="days"></span>Days</li>
                                <li><span id="hours"></span>Hours</li>
                                <li><span id="minutes"></span>Minutes</li>
                                <li><span id="seconds"></span>Seconds</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Our Story -->
            <div id="story" class="story section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-md-5 mb-30">
                            <div class="story-img animate-box" data-animate-effect="fadeInLeft">
                                <div class="img"> <img src="<?= esc($storyMedia) ?>" class="img-fluid" alt="<?= $coupleTitle ?>"> </div>
                                <div class="story-img-2 story-wedding" style="background-image: url('<?= $assetsBase ?>/images/wedding-logo.png');"></div>
                            </div>
                        </div>
                        <div class="col-md-7 animate-box" data-animate-effect="fadeInRight">
                            <h4 class="oliven-story-subtitle"><?= esc($storySubtitle) ?></h4>
                            <h3 class="oliven-story-title"><?= esc($storyTitle) ?></h3>
                            <?php if (!empty($storyItems)) : ?>
                                <?php foreach ($storyItems as $item) : ?>
                                    <?php
                                    $itemTitle = $item['title'] ?? null;
                                    $itemDate = $item['year'] ?? ($item['date'] ?? '');
                                    $itemText = $item['description'] ?? ($item['text'] ?? '');
                                    ?>
                                    <?php if ($itemTitle) : ?>
                                        <h4><?= esc((string) $itemTitle) ?></h4>
                                    <?php endif; ?>
                                    <?php if ($itemDate) : ?>
                                        <p><strong><?= esc((string) $itemDate) ?></strong></p>
                                    <?php endif; ?>
                                    <?php if ($itemText) : ?>
                                        <p><?= esc((string) $itemText) ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <?php foreach ($storyParagraphs as $paragraph) : ?>
                                    <?php if (trim((string) $paragraph) !== '') : ?>
                                        <p><?= esc((string) $paragraph) ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($storyHighlight !== '') : ?>
                                <h4><?= esc($storyHighlight) ?></h4>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Friends -->
            <div id="friends" class="friends section-padding bg-pink">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30">
                            <span class="oliven-title-meta"><?= esc($partyTitle) ?></span>
                            <h2 class="oliven-title mb-30"><?= esc($partyTitle) ?></h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="owl-carousel owl-theme">
                                <?php foreach ($weddingParty as $member) : ?>
                                    <?php
                                    $memberImage = (string) ($member['image_url'] ?? '');
                                    if ($memberImage !== '' && !preg_match('#^https?://#i', $memberImage)) {
                                        $memberImage = base_url($memberImage);
                                    }
                                    $memberImage = $memberImage ?: ($assetsBase . '/images/friends/b1.jpg');
                                    ?>
                                    <div class="item">
                                        <div class="img"> <img src="<?= esc($memberImage) ?>" alt="<?= esc($member['full_name'] ?? '') ?>"> </div>
                                        <div class="info valign">
                                            <div class="full-width">
                                                <h6><?= esc($member['full_name'] ?? '') ?></h6>
                                                <span><?= esc($member['role'] ?? ($partyLabels[$member['category'] ?? 'other'] ?? '')) ?></span>
                                                <?php if (!empty($member['bio'])) : ?>
                                                    <p><?= esc((string) $member['bio']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- See you -->
            <div id="seeyou" class="seeyou section-padding bg-img bg-fixed" data-background="<?= esc($ctaBg) ?>">
                <div class="container">
                    <div class="row">
                        <div class="section-head col-md-12 text-center">
                            <span><i class="ti-heart"></i></span>
                            <h4><?= esc($countdownSubtitle) ?></h4>
                            <?php if ($eventDateLabel !== '') : ?>
                                <h3><?= esc($eventDateLabel) ?></h3>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Organization -->
            <div id="organization" class="organization section-padding bg-pink">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30">
                            <span class="oliven-title-meta"><?= esc($eventsTitle) ?></span>
                            <h2 class="oliven-title"><?= esc($eventsTitle) ?></h2>
                        </div>
                    </div>
                    <div class="row bord-box bg-img" data-background="<?= esc($heroImages[1] ?? $heroImages[0] ?? ($assetsBase . '/images/slider.jpg')) ?>">
                        <?php foreach ($scheduleItems as $index => $item) : ?>
                            <div class="col-md-3 item-box">
                                <h2 class="custom-font numb"><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></h2>
                                <h6 class="title"><?= esc($item['title'] ?? '') ?></h6>
                                <?php if (!empty($item['description'])) : ?>
                                    <p><?= esc((string) $item['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Gallery -->
            <div id="gallery" class="section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30">
                            <span class="oliven-title-meta"><?= esc($galleryTitle) ?></span>
                            <h2 class="oliven-title"><?= esc($galleryTitle) ?></h2>
                        </div>
                    </div>
                    <div class="row"></div>
                </div>
                <div class="container">
                    <div class="row gallery-filter mt-3">
                        <?php foreach ($galleryAssets as $asset) : ?>
                            <div class="col-md-4 gallery-item">
                                <a href="<?= esc($asset['full'] ?? '') ?>" class="img-zoom">
                                    <div class="gallery-box">
                                        <div class="gallery-img">
                                            <img src="<?= esc($asset['thumb'] ?? ($asset['full'] ?? '')) ?>" class="img-fluid mx-auto d-block" alt="<?= esc($asset['alt'] ?? $coupleTitle) ?>">
                                        </div>
                                        <?php if (!empty($asset['caption'])) : ?>
                                            <div class="gallery-detail">
                                                <h4 class="mb-0"><?= esc((string) $asset['caption']) ?></h4>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- When & Where -->
            <div id="whenwhere" class="whenwhere section-padding bg-pink">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30"> <span class="oliven-title-meta"><?= esc($whenWhereMeta) ?></span>
                            <h2 class="oliven-title"><?= esc($whenWhereTitle) ?></h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="owl-carousel owl-theme">
                                <?php foreach ($eventLocations as $location) : ?>
                                    <?php
                                    $locationImage = (string) ($location['image_url'] ?? '');
                                    if ($locationImage !== '' && !preg_match('#^https?://#i', $locationImage)) {
                                        $locationImage = base_url($locationImage);
                                    }
                                    $locationImage = $locationImage ?: $eventImg;
                                    $locationSchedule = $scheduleLookup[$location['id'] ?? ''] ?? null;
                                    ?>
                                    <div class="item">
                                        <div class="whenwhere-img"> <img src="<?= esc($locationImage) ?>" alt="<?= esc($location['name'] ?? '') ?>"></div>
                                        <div class="content">
                                            <h5><?= esc($location['name'] ?? '') ?></h5>
                                            <?php if (!empty($location['address'])) : ?>
                                                <p><i class="ti-location-pin"></i> <?= esc((string) $location['address']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($locationSchedule)) : ?>
                                                <p><i class="ti-time"></i> <span><?= esc(formatScheduleTime($locationSchedule)) ?></span></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Confirmation -->
            <div id="rsvp" class="section-padding bg-img bg-fixed" data-background="<?= esc($rsvpBg) ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 offset-md-3 bg-white p-40"> <span class="oliven-title-meta text-center"><?= esc($rsvpHeading) ?></span>
                            <h2 class="oliven-title text-center"><?= esc($rsvpHeading) ?></h2>
                            <br>
                            <form class="contact__form" method="post" action="<?= esc(base_url(route_to('rsvp.submit', $slug))) ?>">
                                <!-- form message -->
                                <?= csrf_field() ?>
                                <?php if (!empty($selectedGuest['id'])): ?>
                                    <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                                    <?php if ($selectedGuestCode !== ''): ?>
                                        <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-success contact__msg" style="display: none" role="alert">
                                            Your message was sent successfully.
                                        </div>
                                    </div>
                                </div>
                                <!-- form element -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="name" type="text" class="form-control" placeholder="Nombre" required value="<?= esc($selectedGuestName) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="email" type="email" class="form-control" placeholder="Correo electrónico*" required value="<?= esc($selectedGuestEmail) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="phone" type="text" class="form-control" placeholder="Teléfono" value="<?= esc($selectedGuestPhone) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <select name="attending" class="form-control" required>
                                                <option value="" selected disabled>¿Asistirás?</option>
                                                <option value="accepted">Sí, asistiré</option>
                                                <option value="declined">No podré asistir</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea name="message" id="message" cols="30" rows="7" class="form-control" placeholder="Mensaje"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="song_request" type="text" class="form-control" placeholder="Canción sugerida">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="submit" type="submit" class="btn buttono" value="ENVIAR">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gift Registry -->
            <div id="gift" class="gift-section gift">
                <div class="container">
                    <div class="row">
                        <div class="col-md-3 mb-30">
                            <br> <span class="oliven-title-meta"><?= esc($registryTitle) ?></span>
                            <h2 class="oliven-title"><?= esc($registryTitle) ?></h2>
                        </div>
                        <div class="col-md-9">
                            <div class="owl-carousel owl-theme">
                                <?php foreach ($registryItems as $item) : ?>
                                    <?php
                                    $itemImage = (string) ($item['image_url'] ?? '');
                                    if ($itemImage !== '' && !preg_match('#^https?://#i', $itemImage)) {
                                        $itemImage = base_url($itemImage);
                                    }
                                    $itemLink = (string) ($item['product_url'] ?? ($item['external_url'] ?? '#'));
                                    ?>
                                    <div class="client-logo">
                                        <a href="<?= esc($itemLink) ?>" target="_blank" rel="noopener">
                                            <img src="<?= esc($itemImage ?: ($assetsBase . '/images/gift/1.jpg')) ?>" alt="<?= esc($item['title'] ?? $item['name'] ?? '') ?>">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer2">
                <div class="oliven-narrow-content">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h2>
                                <a href="#home"><img src="<?= esc($logoUrl) ?>" alt="<?= $coupleTitle ?>"><span><?= $coupleTitle ?></span></a>
                            </h2>
                            <p class="copyright"><?= esc($heroLocation) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?= $assetsBase ?>/js/jquery.min.js"></script>
        <script src="<?= $assetsBase ?>/js/modernizr-2.6.2.min.js"></script>
        <script src="<?= $assetsBase ?>/js/jquery.easing.1.3.js"></script>
        <script src="<?= $assetsBase ?>/js/bootstrap.min.js"></script>
        <script src="<?= $assetsBase ?>/js/jquery.waypoints.min.js"></script>
        <script src="<?= $assetsBase ?>/js/sticky-kit.min.js"></script>
        <script src="<?= $assetsBase ?>/js/isotope.js"></script>
        <script src="<?= $assetsBase ?>/js/jquery.magnific-popup.min.js"></script>
        <script src="<?= $assetsBase ?>/js/owl.carousel.min.js"></script>
        <script src="<?= $assetsBase ?>/js/main.js"></script>
    </div>
</body>

<!-- Mirrored from duruthemes.com/demo/html/olivia-enrico/demo1/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 08 Feb 2026 07:57:23 GMT -->

</html>
