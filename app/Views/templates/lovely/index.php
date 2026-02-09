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
// Section visibility (override desde configuración del template)
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

$assetsBase = base_url('templates/lovely');

// --- Theme (schema_json + overrides del template) ---
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
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $coupleTitle ?> | 13Bodas</title>

    <!-- Favicon -->
    <link href="<?= $assetsBase ?>/images/favicon/favicon.png" rel="shortcut icon" type="image/png">
    <link href="<?= $assetsBase ?>/images/favicon/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="<?= $assetsBase ?>/images/favicon/apple-touch-icon-72x72.png" rel="apple-touch-icon" sizes="72x72">
    <link href="<?= $assetsBase ?>/images/favicon/apple-touch-icon-114x114.png" rel="apple-touch-icon" sizes="114x114">
    <link href="<?= $assetsBase ?>/images/favicon/apple-touch-icon-144x144.png" rel="apple-touch-icon" sizes="144x144">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php
    $fontsToLoad = array_unique(array_filter([$fontHeading, $fontBody]));
    foreach ($fontsToLoad as $f) {
        $q = str_replace(' ', '+', $f);
        echo '<link href="https://fonts.googleapis.com/css2?family=' . $q . ':wght@300;400;500;600;700&display=swap" rel="stylesheet">' . PHP_EOL;
    }
    ?>

    <!-- Icon fonts + CSS -->
    <link href="<?= $assetsBase ?>/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/flaticon.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/animate.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/owl.carousel.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/owl.theme.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/slick.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/slick-theme.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/owl.transitions.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/jquery.fancybox.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/magnific-popup.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/style.css" rel="stylesheet">

    <!-- Theme overrides -->
    <style>
        :root {
            --t-primary: <?= esc($colorPrimary) ?>;
            --t-accent: <?= esc($colorAccent) ?>;
            --t-heading-font: "<?= esc($fontHeading) ?>", cursive;
            --t-body-font: "<?= esc($fontBody) ?>", sans-serif;
        }

        body {
            font-family: var(--t-body-font);
        }

        h1,
        h2,
        h3,
        .couple-name-merried-text h2 {
            font-family: var(--t-heading-font);
        }

        .navbar-brand,
        .submit,
        .back-to-top-btn span {
            background: var(--t-primary);
        }

        .section-title .vertical-line span,
        .section-title-white .vertical-line span {
            color: var(--t-primary);
        }

        .wedding-announcement .save-the-date .date {
            color: var(--t-primary);
        }

        .submit:hover {
            opacity: .92;
        }

        /* Registry styles */
        .registry-kpis .kpi {
            background: #fff;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
            margin-bottom: 18px;
        }

        .registry-kpis .kpi .icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            background: var(--t-accent);
        }

        .registry-item {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .06);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .registry-item .thumb {
            width: 100%;
            height: 230px;
            object-fit: cover;
            background: #f3f3f3;
        }

        .registry-item .body {
            padding: 18px;
        }

        .badge-claimed {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #1f9d55;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-available {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-registry {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--t-primary);
            color: #fff;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
        }

        .btn-registry:hover {
            opacity: .92;
            color: #fff;
        }

        /* 2025 polish */
        body {
            background: #f7f7f9;
            color: #111827;
        }

        .section-padding {
            padding: 110px 0;
        }

        .section-title h2,
        .section-title-white h2 {
            letter-spacing: .02em;
        }

        .navbar-dark .navbar-nav .nav-link {
            font-weight: 600;
            letter-spacing: .04em;
        }

        .hero .wedding-announcement {
            backdrop-filter: blur(6px);
        }

        .lovely-modern-section {
            background: #fff;
        }

        .lovely-card {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .08);
            margin-bottom: 24px;
            min-height: 180px;
        }

        .lovely-card h3 {
            margin-top: 12px;
            font-size: 22px;
        }

        .lovely-card p {
            margin-bottom: 0;
            opacity: .85;
        }

        .lovely-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(15, 23, 42, 0.08);
            color: #0f172a;
        }

        .lovely-faq {
            background: #fff;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
            margin-bottom: 20px;
        }

        .lovely-faq__question {
            width: 100%;
            border: none;
            background: transparent;
            padding: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 16px;
            color: #0f172a;
        }

        .lovely-faq__answer {
            margin-top: 12px;
            display: none;
            color: #374151;
        }

        .lovely-faq.is-open .lovely-faq__answer {
            display: block;
        }

        .lovely-faq.is-open .lovely-faq__question i {
            transform: rotate(180deg);
        }

        @media (max-width: 991px) {
            .section-padding {
                padding: 90px 0;
            }
        }

        @media (max-width: 767px) {
            .section-padding {
                padding: 70px 0;
            }

            .lovely-card {
                padding: 20px;
            }
        }

        .site-footer {
            background: url("<?= esc($countdownBg) ?>") center center/cover no-repeat local;
            text-align: center;
            color: #fff;
            padding: 160px 0;
            position: relative;
        }
    </style>
</head>

<body id="home">

    <div class="page-wrapper">

        <!-- preloader -->
        <div class="preloader">
            <div class="inner">
                <span class="icon"><i class="fi flaticon-two"></i></span>
            </div>
        </div>

        <!-- ============ HERO ============ -->
        <section class="hero">
            <div class="hero-slider hero-slider-s1">
                <?php foreach ($heroImages as $heroImg): ?>
                    <div class="slide-item">
                        <img src="<?= esc($heroImg) ?>" alt="<?= $coupleTitle ?>" class="slider-bg">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wedding-announcement">
                <div class="couple-name-merried-text">
                    <h2 class="wow slideInUp" data-wow-duration="1s"><?= $coupleTitle ?></h2>
                    <div class="married-text wow fadeIn" data-wow-delay="1s">
                        <h4>
                            <?php foreach (mb_str_split($heroTagline) as $i => $char): ?>
                                <?php if ($char === ' '): ?>
                                    <span>&nbsp;</span>
                                <?php else: ?>
                                    <span class="wow fadeInUp" data-wow-delay="<?= number_format(1.05 + ($i * 0.05), 2) ?>s"><?= esc($char) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </h4>
                    </div>
                </div>

                <div class="save-the-date">
                    <h4>Guarda la fecha</h4>
                    <span class="date"><?= esc($eventDateLabel ?: 'Próximamente') ?></span>
                    <?php if ($eventTimeRange): ?>
                        <div style="margin-top:6px; font-size:14px; opacity:.9;"><?= esc($eventTimeRange) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ============ HEADER NAV ============ -->
        <header id="myHeader" class="site-header header-style-1">
            <nav class="navbar navbar-expand-lg navbar-dark" id="mainNav">
                <div class="container">
                    <a class="navbar-brand" href="#home">
                        <?= mb_substr($coupleTitle, 0, 1) ?>
                        <i class="fi flaticon-shape-1"></i>
                        <?= mb_substr(trim(str_replace('&', '', $coupleTitle)), -1) ?>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                        aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarResponsive">
                        <ul class="navbar-nav text-uppercase ms-auto">
                            <li class="nav-item"><a class="nav-link" href="#home">Inicio</a></li>
                            <li class="nav-item"><a class="nav-link" href="#couple">Nosotros</a></li>
                            <li class="nav-item"><a class="nav-link" href="#story">Historia</a></li>
                            <li class="nav-item"><a class="nav-link" href="#events">Evento</a></li>
                            <?php if (!empty($scheduleItems)): ?>
                                <li class="nav-item"><a class="nav-link" href="#schedule">Agenda</a></li>
                            <?php endif; ?>
                            <?php if (!empty($weddingParty)): ?>
                                <li class="nav-item"><a class="nav-link" href="#people">Cortejo</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="#gallery">Galería</a></li>
                            <?php if (!empty($faqs)): ?>
                                <li class="nav-item"><a class="nav-link" href="#faqs">FAQs</a></li>
                            <?php endif; ?>
                            <?php if (!empty($menuOptions)): ?>
                                <li class="nav-item"><a class="nav-link" href="#menu">Menú</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="#registry">Regalos</a></li>
                            <li class="nav-item"><a class="nav-link" href="#rsvp">Confirmación</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <!-- ============ COUPLE ============ -->
        <section class="wedding-couple-section section-padding" id="couple">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">

                        <div class="gb groom">
                            <div class="img-holder wow fadeInLeftSlow">
                                <img src="<?= esc($groomPhoto) ?>" alt="<?= $groomName ?: $groomSectionTitle ?>">
                            </div>
                            <div class="details">
                                <div class="details-inner">
                                    <h3><?= $groomSectionTitle ?></h3>
                                    <p><?= $groomBio ?></p>
                                    <?php if ($groomName): ?>
                                        <span class="signature"><?= $groomName ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="gb bride">
                            <div class="details">
                                <div class="details-inner">
                                    <h3><?= $brideSectionTitle ?></h3>
                                    <p><?= $brideBio ?></p>
                                    <?php if ($brideName): ?>
                                        <span class="signature"><?= $brideName ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="img-holder wow fadeInRightSlow">
                                <img src="<?= esc($bridePhoto) ?>" alt="<?= $brideName ?: $brideSectionTitle ?>">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- ============ COUNTDOWN ============ -->
        <section class="count-down-section section-padding parallax"
            data-bg-image="<?= esc($countdownBg) ?>" data-speed="7">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <h2><span><?= $countdownTitle ?></span> <?= $countdownSubtitle ?></h2>
                    </div>
                    <div class="col-lg-8">
                        <div class="count-down-clock">
                            <div id="clock"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ STORY ============ -->
        <section class="story-section section-padding" id="story">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2><?= $storyTitle ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-xs-12">
                        <div class="story-timeline">
                            <?php
                            $storyItems = !empty($timelineItems)
                                ? $timelineItems
                                : ($storyPayload['items'] ?? ($storyPayload['events'] ?? []));
                            ?>
                            <?php if (!empty($storyItems)): ?>
                                <?php foreach ($storyItems as $idx => $item): ?>
                                    <?php
                                    $isEven = ($idx % 2 === 0);
                                    $fallbackImg = getMediaUrl($mediaByCategory, 'story', $idx) ?: ($assetsBase . '/images/story/img-' . (($idx % 8) + 1) . '.jpg');
                                    $itemImg = trim((string)($item['image_url'] ?? ($item['image'] ?? '')));
                                    $storyImg = $itemImg !== '' ? $itemImg : $fallbackImg;
                                    if ($storyImg !== '' && !preg_match('#^https?://#i', $storyImg)) {
                                        $storyImg = base_url($storyImg);
                                    }
                                    ?>
                                    <div class="row <?= $isEven ? '' : 'row-reverse' ?>">
                                        <?php if ($isEven): ?>
                                            <div class="col col-lg-6">
                                                <div class="story-text right-align-text">
                                                    <h3><?= esc($item['title'] ?? 'Momento especial') ?></h3>
                                                    <span class="date"><?= esc($item['year'] ?? ($item['date'] ?? '')) ?></span>
                                                    <p><?= esc($item['description'] ?? ($item['text'] ?? '')) ?></p>
                                                </div>
                                            </div>
                                            <div class="col col-lg-6">
                                                <div class="img-holder">
                                                    <img src="<?= esc($storyImg) ?>" alt="" class="img img-fluid">
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="col col-lg-6">
                                                <div class="img-holder">
                                                    <img src="<?= esc($storyImg) ?>" alt="" class="img img-fluid">
                                                </div>
                                            </div>
                                            <div class="col col-lg-6">
                                                <div class="story-text">
                                                    <h3><?= esc($item['title'] ?? 'Momento especial') ?></h3>
                                                    <span class="date"><?= esc($item['year'] ?? ($item['date'] ?? '')) ?></span>
                                                    <p><?= esc($item['description'] ?? ($item['text'] ?? '')) ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="row">
                                    <div class="col col-lg-6">
                                        <div class="story-text right-align-text">
                                            <h3>Cómo comenzó</h3>
                                            <span class="date"><?= esc($eventDateLabel ?: '—') ?></span>
                                            <p>Muy pronto agregaremos aquí los momentos más importantes de nuestra historia.</p>
                                        </div>
                                    </div>
                                    <div class="col col-lg-6">
                                        <div class="img-holder">
                                            <img src="<?= $assetsBase ?>/images/story/img-1.jpg" alt="" class="img img-fluid">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ CTA ============ -->
        <section class="cta section-padding parallax" data-bg-image="<?= esc($ctaBg) ?>" data-speed="7">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <h2><span><?= $ctaHeading ?></span> <?= $ctaSubheading ?></h2>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ EVENTS ============ -->
        <section class="events-section section-padding" id="events">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2><?= $eventsTitle ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col col-md-10">
                        <div class="event">
                            <div class="img-holder">
                                <img src="<?= esc($eventImg) ?>" alt="" class="img img-fluid">
                            </div>
                            <div class="details">
                                <h3><?= $venueName ?: 'Recepción' ?></h3>
                                <ul>
                                    <?php if ($venueAddr): ?>
                                        <li><i class="fa fa-map-marker"></i> <?= $venueAddr ?></li>
                                    <?php endif; ?>
                                    <?php if ($eventDateLabel): ?>
                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <?= esc($eventDateLabel) ?>
                                            <?php if ($eventTimeRange): ?>, <?= esc($eventTimeRange) ?><?php endif; ?>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                <p>Nos encantará verte ahí para compartir este momento con nosotros.</p>

                                <?php if ($lat !== '' && $lng !== ''): ?>
                                    <?php $mapsUrl = "https://www.google.com/maps?q=" . urlencode($lat . "," . $lng) . "&z=16&output=embed"; ?>
                                    <a class="see-location-btn popup-gmaps" href="<?= esc($mapsUrl) ?>">Ver ubicación <i class="fa fa-angle-right"></i></a>
                                <?php elseif ($venueAddr): ?>
                                    <?php $mapsUrl = "https://www.google.com/maps?q=" . urlencode($venueAddr) . "&output=embed"; ?>
                                    <a class="see-location-btn popup-gmaps" href="<?= esc($mapsUrl) ?>">Ver ubicación <i class="fa fa-angle-right"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($scheduleItems)): ?>
            <!-- ============ SCHEDULE ============ -->
            <section class="lovely-modern-section section-padding" id="schedule">
                <div class="container">
                    <div class="row">
                        <div class="col col-xs-12">
                            <div class="section-title">
                                <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                                <h2>Agenda</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($scheduleItems as $item): ?>
                            <?php
                            $title = safeText($item['title'] ?? 'Actividad');
                            $desc = safeText($item['description'] ?? '');
                            $timeLabel = formatScheduleTime($item);
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <article class="lovely-card">
                                    <div class="lovely-card__meta">
                                        <span class="lovely-pill"><?= $timeLabel ?: 'Horario por confirmar' ?></span>
                                    </div>
                                    <h3><?= $title ?></h3>
                                    <?php if ($desc): ?><p><?= $desc ?></p><?php endif; ?>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($faqs)): ?>
            <!-- ============ FAQS ============ -->
            <section class="lovely-modern-section section-padding" id="faqs">
                <div class="container">
                    <div class="row">
                        <div class="col col-xs-12">
                            <div class="section-title">
                                <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                                <h2>Preguntas frecuentes</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="col-md-6">
                                <div class="lovely-faq" data-faq>
                                    <button class="lovely-faq__question" type="button" data-faq-trigger aria-expanded="false">
                                        <span><?= esc($faq['question'] ?? 'Pregunta') ?></span>
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                    <div class="lovely-faq__answer" data-faq-content>
                                        <p><?= esc($faq['answer'] ?? '') ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($weddingParty)): ?>
            <!-- ============ WEDDING PARTY ============ -->
            <section class="inportant-people-section section-padding" id="people">
                <div class="container">
                    <div class="row">
                        <div class="col col-xs-12">
                            <div class="section-title">
                                <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                                <h2><?= $partyTitle ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-xs-12">
                            <div class="inportant-people-content">
                                <div class="tablist">
                                    <ul class="nav nav-pills" role="tablist">
                                        <?php
                                        $tabs = array_keys(array_intersect_key($partyLabels, $partyByCategory));
                                        $tabs = !empty($tabs) ? $tabs : array_keys($partyByCategory);
                                        $firstTab = $tabs[0] ?? null;
                                        ?>
                                        <?php foreach ($tabs as $tab): ?>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link <?= $tab === $firstTab ? 'active' : '' ?>"
                                                    id="tab-<?= esc($tab) ?>"
                                                    data-bs-toggle="pill"
                                                    data-bs-target="#pane-<?= esc($tab) ?>"
                                                    type="button" role="tab"
                                                    aria-selected="<?= $tab === $firstTab ? 'true' : 'false' ?>">
                                                    <?= esc($partyLabels[$tab] ?? 'Integrantes') ?>
                                                </button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div class="tab-content">
                                    <?php foreach ($tabs as $tab): ?>
                                        <div class="tab-pane fade <?= $tab === $firstTab ? 'show active' : '' ?> grid-wrapper"
                                            id="pane-<?= esc($tab) ?>">
                                            <?php foreach (($partyByCategory[$tab] ?? []) as $idx => $member): ?>
                                                <?php
                                                $memberName = safeText($member['full_name'] ?? 'Integrante');
                                                $memberRole = safeText($member['role'] ?? '');
                                                $memberBio  = safeText($member['bio'] ?? '');
                                                $memberImg  = safeText($member['image_url'] ?? '');
                                                $socialLinks = parseSocialLinks($member['social_links'] ?? '');
                                                $fallbackImg = $assetsBase . '/images/groomsmen/img-' . (($idx % 6) + 1) . '.jpg';
                                                ?>
                                                <div class="grid">
                                                    <div class="img-holder">
                                                        <a href="<?= $memberImg ?: $fallbackImg ?>" class="popup-image">
                                                            <img src="<?= $memberImg ?: $fallbackImg ?>" alt="<?= $memberName ?>" class="img img-fluid">
                                                        </a>
                                                    </div>
                                                    <div class="details">
                                                        <h3><?= $memberName ?></h3>
                                                        <?php if ($memberRole): ?><span><?= $memberRole ?></span><?php endif; ?>
                                                        <?php if ($memberBio): ?><p style="margin-top:10px;"><?= $memberBio ?></p><?php endif; ?>
                                                        <?php if (!empty($socialLinks)): ?>
                                                            <ul class="social-links">
                                                                <?php foreach ($socialLinks as $key => $link): ?>
                                                                    <?php
                                                                    $url = is_array($link) ? ($link['url'] ?? '') : $link;
                                                                    $label = is_array($link) ? ($link['label'] ?? '') : $key;
                                                                    $iconKey = strtolower((string)$label);
                                                                    $iconMap = ['facebook' => 'facebook', 'instagram' => 'instagram', 'twitter' => 'twitter', 'tiktok' => 'music', 'youtube' => 'youtube-play', 'web' => 'globe'];
                                                                    $icon = $iconMap[$iconKey] ?? 'link';
                                                                    if (!$url) continue;
                                                                    ?>
                                                                    <li><a href="<?= esc($url) ?>" target="_blank" rel="noopener"><i class="fa fa-<?= esc($icon) ?>"></i></a></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ============ GALLERY ============ -->
        <section class="gallery-section section-padding" id="gallery">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2><?= $galleryTitle ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-xs-12 sortable-gallery">
                        <div class="gallery-container gallery-fancybox masonry-gallery">
                            <?php if (!empty($galleryAssets)): ?>
                                <?php foreach ($galleryAssets as $g): ?>
                                    <?php
                                    $full  = safeText($g['full'] ?? '');
                                    $thumb = safeText($g['thumb'] ?? $full);
                                    $alt   = safeText($g['alt'] ?? $coupleTitle);
                                    if ($full === '') continue;
                                    ?>
                                    <div class="grid wedding">
                                        <a href="<?= $full ?>" class="fancybox" data-fancybox-group="gall-1">
                                            <img src="<?= $thumb ?>" alt="<?= $alt ?>" class="img img-fluid">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php $fallbackCount = (int)($tplAssets['gallery_fallback_count'] ?? 9); ?>
                                <?php for ($i = 1; $i <= $fallbackCount; $i++): ?>
                                    <div class="grid wedding">
                                        <a href="<?= $assetsBase ?>/images/gallery/img-<?= $i ?>.jpg" class="fancybox" data-fancybox-group="gall-1">
                                            <img src="<?= $assetsBase ?>/images/gallery/img-<?= $i ?>.jpg" alt="" class="img img-fluid">
                                        </a>
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($menuOptions)): ?>
            <!-- ============ MENU ============ -->
            <section class="getting-there-section section-padding" id="menu">
                <div class="container">
                    <div class="row">
                        <div class="col col-xs-12">
                            <div class="section-title-white">
                                <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                                <h2>Menú</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row content">
                        <?php foreach ($menuOptions as $option): ?>
                            <div class="col col-lg-4 col-md-6">
                                <h3><?= esc($option['name'] ?? 'Platillo') ?></h3>
                                <?php if (!empty($option['description'])): ?><p><?= esc($option['description']) ?></p><?php endif; ?>
                                <ul style="margin-top:10px;">
                                    <?php if (!empty($option['is_vegan'])): ?><li>Vegano</li><?php endif; ?>
                                    <?php if (!empty($option['is_gluten_free'])): ?><li>Sin gluten</li><?php endif; ?>
                                    <?php if (!empty($option['is_kid_friendly'])): ?><li>Opción para niños</li><?php endif; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ============ REGISTRY ============ -->
        <section class="getting-there-section section-padding" id="registry">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title-white">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2><?= $registryTitle ?></h2>
                        </div>
                    </div>
                </div>

                <?php if (!empty($registryItems)): ?>
                    <div class="row" style="margin-top:10px;">
                        <?php foreach ($registryItems as $it): ?>
                            <?php
                            $title = safeText($it['title'] ?? ($it['name'] ?? 'Regalo'));
                            $desc  = safeText($it['description'] ?? '');
                            $price = (float)($it['price'] ?? 0);
                            $cur   = safeText($it['currency_code'] ?? 'MXN');
                            $img   = safeText($it['image_url'] ?? '');
                            $url   = safeText($it['product_url'] ?? ($it['external_url'] ?? ''));
                            $claimed = (int)($it['is_claimed'] ?? 0) === 1;
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="registry-item">
                                    <img class="thumb" src="<?= $img ?: ($assetsBase . '/images/gift/img-1.jpg') ?>" alt="<?= $title ?>">
                                    <div class="body">
                                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                                            <h3 style="margin:0; font-size:22px; line-height:1.2;"><?= $title ?></h3>
                                            <span class="<?= $claimed ? 'badge-claimed' : 'badge-available' ?>">
                                                <?= $claimed ? 'Reclamado' : 'Disponible' ?>
                                            </span>
                                        </div>
                                        <?php if ($desc): ?><p style="margin-top:10px; opacity:.85;"><?= $desc ?></p><?php endif; ?>
                                        <?php if ($price > 0): ?>
                                            <div style="margin-top:12px; font-weight:800; font-size:18px;"><?= moneyFmt($price, $cur) ?></div>
                                        <?php endif; ?>
                                        <div style="margin-top:14px;">
                                            <?php if ($url): ?>
                                                <a class="btn-registry" href="<?= $url ?>" target="_blank" rel="noopener">Ver detalle <i class="fa fa-external-link"></i></a>
                                            <?php else: ?>
                                                <span style="opacity:.75;">Este regalo no tiene liga.</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-lg-10">
                            <h3>Próximamente</h3>
                            <p>Muy pronto publicaremos la lista de regalos y fondos para este evento.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ============ RSVP ============ -->
        <section class="rsvp-section section-padding parallax"
            data-bg-image="<?= esc($rsvpBg) ?>" data-speed="7" id="rsvp">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title-white">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2><?= $rsvpHeading ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row content justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <?php if ($rsvpDeadlineLabel): ?>
                            <p>Por favor confirma antes del <?= esc($rsvpDeadlineLabel) ?>.</p>
                        <?php else: ?>
                            <p>Por favor confirma tu asistencia.</p>
                        <?php endif; ?>

                        <form id="rsvp-form" class="form row" method="post" action="<?= esc(base_url(route_to('rsvp.submit', $slug))) ?>">
                            <?= csrf_field() ?>
                            <?php if (!empty($selectedGuest['id'])): ?>
                                <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                                <?php if ($selectedGuestCode !== ''): ?>
                                    <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="col-md-6 mb-4">
                                <input type="text" name="name" class="form-control" placeholder="Tu nombre*" required value="<?= esc($selectedGuestName) ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <input type="email" name="email" class="form-control" placeholder="Tu email*" required value="<?= esc($selectedGuestEmail) ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <input type="text" name="phone" class="form-control" placeholder="Teléfono (opcional)" value="<?= esc($selectedGuestPhone) ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <select class="form-control" name="attending" required>
                                    <option disabled selected>¿Asistirás?*</option>
                                    <option value="accepted">Sí, asistiré</option>
                                    <option value="declined">No podré asistir</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <input type="text" class="form-control" value="1" disabled placeholder="Asistentes">
                            </div>
                            <div class="col-md-12 mb-4">
                                <textarea class="form-control" name="message" placeholder="Mensaje para los novios (opcional)"></textarea>
                            </div>
                            <div class="col-md-12 mb-4">
                                <input type="text" name="song_request" class="form-control" placeholder="¿Qué canción no puede faltar? (opcional)">
                            </div>
                            <div class="col-md-12 mb-4 submit-btn">
                                <button type="submit" class="submit">Enviar confirmación</button>
                                <span id="loader" style="display:none;"><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></span>
                            </div>
                            <div class="col-md-12 mb-4 success-error-message">
                                <div id="success" style="display:none;">Gracias. Tu confirmación fue registrada.</div>
                                <div id="error" style="display:none;">Ocurrió un error. Intenta de nuevo.</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FOOTER ============ -->
        <footer class="site-footer">
            <div class="back-to-top">
                <a href="#" class="back-to-top-btn"><span><i class="fi flaticon-cupid"></i></span></a>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-xs-12">
                        <h2><?= $coupleTitle ?></h2>
                        <span>13Bodas</span>
                    </div>
                </div>
            </div>
        </footer>

    </div><!-- /page-wrapper -->

    <!-- JS -->
    <script src="<?= $assetsBase ?>/js/jquery.min.js"></script>
    <script src="<?= $assetsBase ?>/js/bootstrap.min.js"></script>
    <script src="<?= $assetsBase ?>/js/jquery-plugin-collection.js"></script>

    <script>
        window.__INVITATION__ = {
            eventDateISO: <?= json_encode($eventDateISO) ?>,
            slug: <?= json_encode($slug) ?>,
            rsvpUrl: <?= json_encode(base_url(route_to('rsvp.submit', $slug))) ?>,
            galleryCount: <?= json_encode(count($galleryAssets ?? [])) ?>,
            registryCount: <?= json_encode(count($registryItems ?? [])) ?>
        };
    </script>
    <script src="<?= $assetsBase ?>/js/script.js"></script>
    <script>
        (function($) {
            'use strict';

            const $form = $('#rsvp-form');
            const $btn = $form.find('button[type="submit"]');
            const $loader = $('#loader');
            const $ok = $('#success');
            const $err = $('#error');

            function setLoading(on) {
                $btn.prop('disabled', on);
                $loader.toggle(!!on);
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                $ok.hide();
                $err.hide();

                setLoading(true);

                $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        dataType: 'json'
                    })
                    .done(function(resp) {
                        if (resp && resp.success) {
                            $ok.text(resp.message || 'Confirmación registrada. ¡Gracias!').show();
                            $form.trigger('reset');
                        } else {
                            $err.text((resp && resp.message) ? resp.message : 'No fue posible registrar tu confirmación.').show();
                        }
                    })
                    .fail(function() {
                        $err.text('No fue posible registrar tu confirmación.').show();
                    })
                    .always(function() {
                        setLoading(false);
                    });
            });
        })(jQuery);
    </script>
    <script>
        (function() {
            const items = document.querySelectorAll('[data-faq]');
            items.forEach((item) => {
                const trigger = item.querySelector('[data-faq-trigger]');
                if (!trigger) return;
                trigger.addEventListener('click', () => {
                    const open = item.classList.toggle('is-open');
                    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });
        })();
    </script>

</body>

</html>
