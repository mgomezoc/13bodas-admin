<?php
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
?>
<!DOCTYPE HTML>
<html lang="en">

<!-- Mirrored from duruthemes.com/demo/html/olivia-enrico/demo1/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 08 Feb 2026 07:57:09 GMT -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Olivia & Enrico Wedding Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Olivia & Enrico – Wedding Template is perfect for wedding planners, coordinators, photographers, and event organizers. Minimal, clean, responsive and SEO friendly design with Bootstrap 5+, video background, working contact form, and retina-ready layout.">
    <meta name="keywords" content="wedding template, wedding planner, wedding website, event organizer, wedding coordinator, wedding photographer, modern wedding template, bootstrap wedding theme, responsive wedding template, minimal wedding design">
    <meta name="author" content="DuruThemes">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/png" href="images/favicon.png" />
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
                <a href="index.html">
                    <img src="images/logo.png" alt="">
                    <span>Olivia <small>&</small> Enrico</span>
                    <h6>15.11.2026</h6>
                </a>
            </div>
            <!-- Menu -->
            <nav class="oliven-main-menu">
                <ul>
                    <li><a href="index.html#home">Home</a></li>
                    <li><a href="index.html#couple">Couple</a></li>
                    <li><a href="index.html#story">Our Story</a></li>
                    <li><a href="index.html#friends">Friends</a></li>
                    <li><a href="index.html#organization">Organization</a></li>
                    <li><a href="index.html#gallery">Gallery</a></li>
                    <li><a href="index.html#whenwhere">When & Where</a></li>
                    <li><a href="index.html#rsvp">R.S.V.P</a></li>
                    <li><a href="index.html#gift">Gift Registry</a></li>
                    <li><a href="blog.html">Blog</a></li>
                </ul>
            </nav>
            <!-- Sidebar Footer -->
            <div class="footer1"> <span class="separator"></span>
                <p>Olivia & Enrico wedding<br />15 Dec 2026, New York</p>
            </div>
        </aside>
        <!-- Content Section -->
        <div id="oliven-main">
            <!-- Header & Slider -->
            <header id="home" class="header valign bg-img parallaxie" data-background="images/slider.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 text-center caption">
                            <h1 class="animate-box" data-animate-effect="fadeInUp">Olivia & Enrico</h1>
                            <h5 class="animate-box" data-animate-effect="fadeInUp">15 December, 2026 – New York</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="arrow bounce text-center">
                                <a href="index.html#couple"> <i class="ti-heart"></i> </a>
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
                                <div class="img"> <img src="images/bride.jpg" alt=""> </div>
                                <div class="info valign">
                                    <div class="full-width">
                                        <h6>Olivia Martin <i class="ti-heart"></i></h6> <span>The Bride</span>
                                        <p>Olivia fringilla dui at elit finibus viverra thenec a lacus seda themo the miss druane semper non the fermen.</p>
                                        <div class="social">
                                            <div class="full-width">
                                                <a href="#0" class="icon"> <i class="ti-facebook"></i> </a>
                                                <a href="#0" class="icon"> <i class="ti-twitter"></i> </a>
                                                <a href="#0" class="icon"> <i class="ti-instagram"></i> </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item mb-30 animate-box" data-animate-effect="fadeInRight">
                                <div class="img"> <img src="images/groom.jpg" alt=""> </div>
                                <div class="info valign">
                                    <div class="full-width">
                                        <h6>Enrico Danilo <i class="ti-heart"></i></h6> <span>The Groom</span>
                                        <p>Enrico fringilla dui at elit finibus viverra thenec a lacus seda themo the miss druane semper non the fermen.</p>
                                        <div class="social">
                                            <div class="full-width">
                                                <a href="#0" class="icon"> <i class="ti-facebook"></i> </a>
                                                <a href="#0" class="icon"> <i class="ti-twitter"></i> </a>
                                                <a href="#0" class="icon"> <i class="ti-instagram"></i> </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center animate-box" data-animate-effect="fadeInUp">
                            <h3 class="oliven-couple-title">Are getting married!</h3>
                            <h4 class="oliven-couple-subtitle">December 15, 2026 — New York, Brooklyn</h4>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Countdown -->
            <div id="countdown" class="section-padding bg-img bg-fixed" data-background="images/banner-1.jpg">
                <div class="container">
                    <div class="row">
                        <div class="section-head col-md-12">
                            <h4>We will become a family in</h4>
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
                                <div class="img"> <img src="images/story.jpg" class="img-fluid" alt=""> </div>
                                <div class="story-img-2 story-wedding" style="background-image: url(images/wedding-logo.png);"></div>
                            </div>
                        </div>
                        <div class="col-md-7 animate-box" data-animate-effect="fadeInRight">
                            <h4 class="oliven-story-subtitle">Our love.</h4>
                            <h3 class="oliven-story-title">Our Story</h3>
                            <p>Curabit aliquet orci elit genes tristique lorem commodo vitae. Tuliaum tincidunt nete sede gravida aliquam, neque libero hendrerit magna, sit amet mollis lacus ithe maurise. Dunya erat volutpat edat themo the druanye semper.</p>
                            <p>Luality fringilla duiman at elit vinibus viverra nec a lacus themo the druanye sene sollicitudin mi suscipit non sagie the fermen.</p>
                            <p>Phasellus viverra tristique justo duis vitae diam neque nivamus ac est augue artine aringilla dui at elit finibus viverra nec a lacus. Nedana themo eros odio semper soe suscipit non. Curabit aliquet orci elit genes tristique.</p>
                            <h4>Dec 5th, 2026, We Said Yes!</h4>
                            <p>Luality fringilla duiman at elit finibus viverra nec a lacus themo the druanye sene sollicitudin mi suscipit non sagie the fermen.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Friends -->
            <div id="friends" class="friends section-padding bg-pink">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30">
                            <span class="oliven-title-meta">Our best friends ever</span>
                            <h2 class="oliven-title mb-30">Thanks for being there</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="owl-carousel owl-theme">
                                <div class="item">
                                    <div class="img"> <img src="images/friends/b1.jpg" alt=""> </div>
                                    <div class="info valign">
                                        <div class="full-width">
                                            <h6>Eleanor Chris</h6><span>Bridesmaids</span>
                                            <p>Enstibulum eringilla dui athe elitene miss minibus viverra nectar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="img"> <img src="images/friends/w1.jpg" alt=""> </div>
                                    <div class="info valign">
                                        <div class="full-width">
                                            <h6>Stefano Smiht</h6><span>Groomsmen</span>
                                            <p>Enstibulum eringilla dui athe elitene miss minibus viverra nectar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="img"> <img src="images/friends/b2.jpg" alt=""> </div>
                                    <div class="info valign">
                                        <div class="full-width">
                                            <h6>Vanessa Brown</h6><span>Bridesmaids</span>
                                            <p>Enstibulum eringilla dui athe elitene miss minibus viverra nectar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="img"> <img src="images/friends/w2.jpg" alt=""> </div>
                                    <div class="info valign">
                                        <div class="full-width">
                                            <h6>Matthew Brown</h6><span>Groomsmen</span>
                                            <p>Enstibulum eringilla dui athe elitene miss minibus viverra nectar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="img"> <img src="images/friends/b3.jpg" alt=""> </div>
                                    <div class="info valign">
                                        <div class="full-width">
                                            <h6>Fredia Halle</h6><span>Bridesmaids</span>
                                            <p>Enstibulum eringilla dui athe elitene miss minibus viverra nectar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="img"> <img src="images/friends/w3.jpg" alt=""> </div>
                                    <div class="info valign">
                                        <div class="full-width">
                                            <h6>Pablo Dante</h6><span>Groomsmen</span>
                                            <p>Enstibulum eringilla dui athe elitene miss minibus viverra nectar.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- See you -->
            <div id="seeyou" class="seeyou section-padding bg-img bg-fixed" data-background="images/banner-3.jpg">
                <div class="container">
                    <div class="row">
                        <div class="section-head col-md-12 text-center">
                            <span><i class="ti-heart"></i></span>
                            <h4>Looking forward to see you!</h4>
                            <h3>15.11.2026</h3>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Organization -->
            <div id="organization" class="organization section-padding bg-pink">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30">
                            <span class="oliven-title-meta">Wedding</span>
                            <h2 class="oliven-title">Organization</h2>
                        </div>
                    </div>
                    <div class="row bord-box bg-img" data-background="images/slider.jpg">
                        <div class="col-md-3 item-box">
                            <h2 class="custom-font numb">01</h2>
                            <h6 class="title">Ceremony</h6>
                            <p>Delta tristiu the jusone duise vitae diam neque nivami mis est augue artine aringilla the at elit finibus vivera.</p>
                        </div>
                        <div class="col-md-3 item-box">
                            <h2 class="custom-font numb">02</h2>
                            <h6 class="title">Lunch Time</h6>
                            <p>Delta tristiu the jusone duise vitae diam neque nivami mis est augue artine aringilla the at elit finibus vivera.</p>
                        </div>
                        <div class="col-md-3 item-box">
                            <h2 class="custom-font numb">03</h2>
                            <h6 class="title">Party</h6>
                            <p>Delta tristiu the jusone duise vitae diam neque nivami mis est augue artine aringilla the at elit finibus vivera.</p>
                        </div>
                        <div class="col-md-3 item-box">
                            <h2 class="custom-font numb">04</h2>
                            <h6 class="title">Cake Cutting</h6>
                            <p>Delta tristiu the jusone duise vitae diam neque nivami mis est augue artine aringilla the at elit finibus vivera.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Gallery -->
            <div id="gallery" class="section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30">
                            <span class="oliven-title-meta">Gallery</span>
                            <h2 class="oliven-title">Our Memories</h2>
                        </div>
                    </div>
                    <div class="row">
                        <ul class="col list-unstyled list-inline mb-0 gallery-menu" id="gallery-filter">
                            <li class="list-inline-item"><a class="active" data-filter="*">All</a></li>
                            <li class="list-inline-item"><a class="" data-filter=".ceremony">Ceremony</a></li>
                            <li class="list-inline-item"><a class="" data-filter=".party">Party</a></li>
                        </ul>
                    </div>
                </div>
                <div class="container">
                    <div class="row gallery-filter mt-3">
                        <div class="col-md-4 gallery-item ceremony">
                            <a href="images/gallery/1.jpg" class="img-zoom">
                                <div class="gallery-box">
                                    <div class="gallery-img"> <img src="images/gallery/1.jpg" class="img-fluid mx-auto d-block" alt=""> </div>
                                    <div class="gallery-detail">
                                        <h4 class="mb-0">Wedding Ceremony</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 gallery-item party">
                            <a href="images/gallery/2.jpg" class="img-zoom">
                                <div class="gallery-box">
                                    <div class="gallery-img"> <img src="images/gallery/2.jpg" class="img-fluid mx-auto d-block" alt=""> </div>
                                    <div class="gallery-detail">
                                        <h4 class="mb-0">Wedding Party</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 gallery-item ceremony">
                            <a href="images/gallery/3.jpg" class="img-zoom">
                                <div class="gallery-box">
                                    <div class="gallery-img"> <img src="images/gallery/3.jpg" class="img-fluid mx-auto d-block" alt=""> </div>
                                    <div class="gallery-detail">
                                        <h4 class="mb-0">Wedding Ceremony</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 gallery-item party">
                            <a href="images/gallery/4.jpg" class="img-zoom">
                                <div class="gallery-box">
                                    <div class="gallery-img"> <img src="images/gallery/4.jpg" class="img-fluid mx-auto d-block" alt=""> </div>
                                    <div class="gallery-detail">
                                        <h4 class="mb-0">Wedding Party</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 gallery-item ceremony">
                            <a href="images/gallery/5.jpg" class="img-zoom">
                                <div class="gallery-box">
                                    <div class="gallery-img"> <img src="images/gallery/5.jpg" class="img-fluid mx-auto d-block" alt=""> </div>
                                    <div class="gallery-detail">
                                        <h4 class="mb-0">Wedding Ceremony</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 gallery-item party">
                            <a href="images/gallery/6.jpg" class="img-zoom">
                                <div class="gallery-box">
                                    <div class="gallery-img"> <img src="images/gallery/6.jpg" class="img-fluid mx-auto d-block" alt=""> </div>
                                    <div class="gallery-detail">
                                        <h4 class="mb-0">Wedding Party</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- When & Where -->
            <div id="whenwhere" class="whenwhere section-padding bg-pink">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 mb-30"> <span class="oliven-title-meta">Questions</span>
                            <h2 class="oliven-title">When & Where</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="owl-carousel owl-theme">
                                <div class="item">
                                    <div class="whenwhere-img"> <img src="images/whenwhere/3.jpg" alt=""></div>
                                    <div class="content">
                                        <h5>Wedding Ceremony</h5>
                                        <p><i class="ti-location-pin"></i> 175 Broadway, Brooklyn, New York 11244, USA</p>
                                        <p><i class="ti-time"></i> <span>12:00am – 13:00pm</span></p>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="whenwhere-img"> <img src="images/whenwhere/1.jpg" alt=""></div>
                                    <div class="content">
                                        <h5>Weddding Party</h5>
                                        <p><i class="ti-location-pin"></i> Fortune Brooklyn restaurant, 149 Broadway, Brooklyn, NY, USA</p>
                                        <p><i class="ti-time"></i> <span>14:00pm</span></p>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="whenwhere-img"> <img src="images/whenwhere/2.jpg" alt=""></div>
                                    <div class="content">
                                        <h5>Accomodations</h5>
                                        <p><i class="ti-direction-alt"></i> Hotel and distance from wedding party restaurant:</p>
                                        <p><i class="ti-direction"></i> The William Vale (7 min)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Confirmation -->
            <div id="rsvp" class="section-padding bg-img bg-fixed" data-background="images/banner-2.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 offset-md-3 bg-white p-40"> <span class="oliven-title-meta text-center">Will you attend?</span>
                            <h2 class="oliven-title text-center">R.S.V.P</h2>
                            <br>
                            <form class="contact__form" method="post" action="https://duruthemes.com/demo/html/olivia-enrico/demo1/mail.php">
                                <!-- form message -->
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
                                            <input name="name" type="text" class="form-control" placeholder="Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="email" type="email" class="form-control" placeholder="Email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="guests" type="text" class="form-control" placeholder="Guests" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea name="message" id="message" cols="30" rows="7" class="form-control" placeholder="Message"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input name="submit" type="submit" class="btn buttono" value="SEND">
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
                            <br> <span class="oliven-title-meta">Gift</span>
                            <h2 class="oliven-title">Gift Registry</h2>
                        </div>
                        <div class="col-md-9">
                            <div class="owl-carousel owl-theme">
                                <div class="client-logo">
                                    <a href="#"><img src="images/gift/1.jpg" alt=""></a>
                                </div>
                                <div class="client-logo">
                                    <a href="#"><img src="images/gift/2.jpg" alt=""></a>
                                </div>
                                <div class="client-logo">
                                    <a href="#"><img src="images/gift/3.jpg" alt=""></a>
                                </div>
                                <div class="client-logo">
                                    <a href="#"><img src="images/gift/4.jpg" alt=""></a>
                                </div>
                                <div class="client-logo">
                                    <a href="#"><img src="images/gift/5.jpg" alt=""></a>
                                </div>
                                <div class="client-logo">
                                    <a href="#"><img src="images/gift/6.jpg" alt=""></a>
                                </div>
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
                                <a href="index.html"><img src="images/logo.png" alt=""><span>Olivia <small>&</small> Enrico</span></a>
                            </h2>
                            <p class="copyright">December 15, 2026 – New York, Brooklyn</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?= $assetsBase ?>/js/jquery.min.js"></script>
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