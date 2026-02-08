<?php
// ================================================================
// TEMPLATE: FEELINGS — app/Views/templates/feelings/index.php
// Versión: 1.0
// ================================================================

// --- Base ---
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
$rawDefaults = $templateMeta['defaults'] ?? [];
if (isset($rawDefaults['copy']) && is_array($rawDefaults['copy'])) {
    $defaults  = $rawDefaults['copy'];
    $tplAssets = $rawDefaults['assets'] ?? [];
} else {
    $defaults  = $rawDefaults;
    $tplAssets = $templateMeta['assets'] ?? [];
}

// Section visibility
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

// Date for countdown (YYYY/MM/DD format for JS)
$countdownDate = $startRaw ? date('Y/m/d', strtotime($startRaw)) : '2026/12/31';

$assetsBase = base_url('templates/feelings');

// --- Theme configuration ---
$schema = [];
if (!empty($template['schema_json'])) {
    $schema = json_decode($template['schema_json'], true) ?: [];
}

$themeDefaults = $schema['theme_defaults'] ?? [];
$schemaFonts  = !empty($themeDefaults['fonts'])
    ? [$themeDefaults['fonts']['heading'] ?? 'Gilda Display', $themeDefaults['fonts']['body'] ?? 'Jost']
    : ($schema['fonts'] ?? ['Gilda Display', 'Jost']);
$schemaColors = !empty($themeDefaults['colors'])
    ? [$themeDefaults['colors']['primary'] ?? '#C4A875', $themeDefaults['colors']['accent'] ?? '#F5F5F5']
    : ($schema['colors'] ?? ['#C4A875', '#F5F5F5']);

$fontHeading  = $theme['fonts']['heading'] ?? ($theme['font_heading'] ?? ($schemaFonts[0] ?? 'Gilda Display'));
$fontBody     = $theme['fonts']['body']    ?? ($theme['font_body']    ?? ($schemaFonts[1] ?? 'Jost'));
$colorPrimary = $theme['colors']['primary'] ?? ($theme['primary']     ?? ($schemaColors[0] ?? '#C4A875'));
$colorAccent  = $theme['colors']['accent']  ?? ($theme['accent']      ?? ($schemaColors[1] ?? '#F5F5F5'));

// --- Module finder ---
function findModule(array $modules, string $type): ?array
{
    foreach ($modules as $m) {
        if (($m['module_type'] ?? '') === $type) return $m;
    }
    return null;
}

// --- Couple module ---
$modCouple = findModule($modules, 'feelings.couple') ?? findModule($modules, 'couple_info');
$couplePayload = [];
if ($modCouple && !empty($modCouple['content_payload'])) {
    $raw = $modCouple['content_payload'];
    $couplePayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}

// --- Copy module ---
$modCopy = findModule($modules, 'feelings.copy') ?? findModule($modules, 'lovely.copy');
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
$storyItems = $storyPayload['items'] ?? $storyPayload['events'] ?? [];

// --- Schedule module ---
$modSchedule = findModule($modules, 'schedule');
$schedulePayload = [];
if ($modSchedule && !empty($modSchedule['content_payload'])) {
    $raw = $modSchedule['content_payload'];
    $schedulePayload = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
}
$scheduleItems = !empty($scheduleItems) ? $scheduleItems : ($schedulePayload['items'] ?? ($schedulePayload['events'] ?? []));

// --- Dynamic text helper ---
function getText(array $copyPayload, array $defaults, string $key, string $hardcoded = ''): string
{
    return esc($copyPayload[$key] ?? ($defaults[$key] ?? $hardcoded));
}

$heroTagline       = getText($copyPayload, $defaults, 'hero_tagline', 'Nos casamos');
$countdownTitle    = getText($copyPayload, $defaults, 'countdown_title', 'Faltan');
$coupleTitle_txt   = getText($copyPayload, $defaults, 'couple_section_title', 'La pareja');
$storyTitle        = getText($copyPayload, $defaults, 'story_title', 'Nuestra historia');
$galleryTitle      = getText($copyPayload, $defaults, 'gallery_title', 'Momentos capturados');
$eventsTitle       = getText($copyPayload, $defaults, 'events_title', 'Detalles del evento');
$rsvpHeading       = getText($copyPayload, $defaults, 'rsvp_heading', '¿Nos acompañas?');
$registryTitle     = getText($copyPayload, $defaults, 'registry_title', 'Mesa de regalos');

$brideBio = esc($couplePayload['bride']['bio']
    ?? ($defaults['bride_bio'] ?? 'Gracias por ser parte de nuestra historia'));
$groomBio = esc($couplePayload['groom']['bio']
    ?? ($defaults['groom_bio'] ?? 'Estamos felices de compartir contigo este día'));

// --- Media helpers ---
function getMediaUrl(array $mediaByCategory, string $category, int $index = 0, string $size = 'original'): string
{
    $items = $mediaByCategory[$category] ?? [];
    if (empty($items) || !isset($items[$index])) return '';

    $m = $items[$index];
    $fieldMap = ['original' => 'file_url_original', 'large' => 'file_url_large', 'thumb' => 'file_url_thumbnail'];
    $field = $fieldMap[$size] ?? 'file_url_original';

    $url = $m[$field] ?? ($m['file_url_original'] ?? ($m['file_url_large'] ?? ($m['file_url_thumbnail'] ?? '')));
    return $url ? esc($url) : '';
}

$heroImage = getMediaUrl($mediaByCategory, 'hero', 0, 'large') ?: getMediaUrl($mediaByCategory, 'hero', 0, 'original');
$coupleImage = getMediaUrl($mediaByCategory, 'couple', 0, 'large') ?: getMediaUrl($mediaByCategory, 'couple', 0, 'original');
$brideImage = getMediaUrl($mediaByCategory, 'bride', 0, 'original');
$groomImage = getMediaUrl($mediaByCategory, 'groom', 0, 'original');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="theme-color" content="<?= $colorPrimary ?>">
    <title><?= $coupleTitle ?> - Invitación de Boda</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gilda+Display&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/flaticon.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/animate.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/owl.carousel.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/fancybox.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/styles.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/elements.css">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/responsive.css">

    <!-- Custom theme colors -->
    <style>
        :root {
            --primary-color: <?= $colorPrimary ?>;
            --accent-color: <?= $colorAccent ?>;
            --font-heading: '<?= $fontHeading ?>', serif;
            --font-body: '<?= $fontBody ?>', sans-serif;
        }

        body {
            font-family: var(--font-body);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-heading);
        }

        .wpo-section-title h2 {
            color: var(--primary-color);
        }
    </style>
</head>

<body class="home">
    <div class="page-wrapper layout-full">

        <!-- Preloader -->
        <div class="preloader">
            <div class="vertical-centered-box">
                <div class="content">
                    <div class="loader-circle"></div>
                    <div class="loader-line-mask">
                        <div class="loader-line"></div>
                    </div>
                    <svg width="80" height="80" viewBox="0 0 100 100">
                        <text x="50" y="60" font-size="60" text-anchor="middle" fill="<?= $colorPrimary ?>"><?= mb_substr($brideName, 0, 1) ?><?= mb_substr($groomName, 0, 1) ?></text>
                    </svg>
                </div>
            </div>
        </div>

        <!-- HERO SECTION -->
        <?php if ($sectionVisibility['hero'] ?? true): ?>
            <section class="static-hero">
                <div class="hero-container">
                    <div class="hero-inner">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col-xl-8 col-lg-6 col-12">
                                    <div class="wpo-static-hero-inner">
                                        <div class="shape-1">
                                            <img src="<?= $assetsBase ?>/images/shape.png" alt="">
                                        </div>
                                        <div data-swiper-parallax="300" class="slide-title">
                                            <h2><?= $groomName ?> & <?= $brideName ?></h2>
                                        </div>
                                        <div data-swiper-parallax="400" class="slide-text">
                                            <p><?= $heroTagline ?> <?= $eventDateLabel ?></p>
                                        </div>

                                        <!-- Countdown -->
                                        <?php if ($sectionVisibility['countdown'] ?? true): ?>
                                            <div class="wpo-wedding-date">
                                                <div class="clock-grids">
                                                    <div id="clock" data-date="<?= $countdownDate ?>"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="shape-2">
                                            <img src="<?= $assetsBase ?>/images/shape2.png" alt="">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="static-hero-right">
                    <div class="static-hero-img">
                        <div class="static-hero-img-inner">
                            <?php if ($heroImage): ?>
                                <img src="<?= $heroImage ?>" alt="<?= $coupleTitle ?>">
                            <?php endif; ?>
                        </div>
                        <div class="static-hero-shape-1">
                            <img src="<?= $assetsBase ?>/images/shape3.png" alt="">
                        </div>
                        <div class="static-hero-shape-2">
                            <img src="<?= $assetsBase ?>/images/shape4.png" alt="">
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- COUPLE SECTION -->
        <?php if ($sectionVisibility['couple'] ?? true): ?>
            <section class="feelings-couple couple-section" id="couple">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col col-xs-12">
                            <div class="couple-area clearfix">
                                <div class="text-grid bride">
                                    <h3><?= $brideName ?></h3>
                                    <p><?= $brideBio ?></p>
                                </div>

                                <div class="middle-couple-pic">
                                    <div class="middle-couple-pic-inner">
                                        <?php if ($coupleImage): ?>
                                            <img src="<?= $coupleImage ?>" alt="<?= $coupleTitle ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="text-grid groom">
                                    <h3><?= $groomName ?></h3>
                                    <p><?= $groomBio ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- STORY SECTION -->
        <?php if (($sectionVisibility['story'] ?? true) && !empty($storyItems)): ?>
            <section class="wpo-story-section" id="story">
                <div class="container">
                    <div class="wpo-section-title">
                        <div class="section-title-img">
                            <img src="<?= $assetsBase ?>/images/section-title.png" alt="">
                        </div>
                        <h2><?= $storyTitle ?></h2>
                    </div>

                    <div class="row align-items-center justify-content-center">
                        <div class="col col-lg-12 col-12">
                            <div class="tab-area">
                                <div class="tablinks">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <?php foreach ($storyItems as $idx => $item): ?>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link <?= $idx === 0 ? 'active' : '' ?>"
                                                    id="story-tab-<?= $idx ?>"
                                                    data-bs-toggle="tab"
                                                    href="#story-<?= $idx ?>"
                                                    role="tab">
                                                    <?= esc($item['title'] ?? "Historia $idx") ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div class="tab-content">
                                    <?php foreach ($storyItems as $idx => $item): ?>
                                        <div class="tab-pane <?= $idx === 0 ? 'active' : 'fade' ?>" id="story-<?= $idx ?>">
                                            <div class="wpo-story-item">
                                                <div class="wpo-story-img">
                                                    <?php if (!empty($item['image_url'])): ?>
                                                        <img src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title']) ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="wpo-story-content">
                                                    <div class="wpo-story-content-inner">
                                                        <h2><?= esc($item['title'] ?? '') ?></h2>
                                                        <?php if (!empty($item['date'])): ?>
                                                            <span><?= esc($item['date']) ?></span>
                                                        <?php endif; ?>
                                                        <p><?= esc($item['description'] ?? '') ?></p>
                                                        <div class="border-shape">
                                                            <img src="<?= $assetsBase ?>/images/shape.jpg" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- GALLERY SECTION -->
        <?php if (($sectionVisibility['gallery'] ?? true) && !empty($galleryAssets)): ?>
            <section class="wpo-portfolio-section" id="gallery">
                <div class="container">
                    <div class="wpo-section-title">
                        <div class="section-title-img">
                            <img src="<?= $assetsBase ?>/images/section-title.png" alt="">
                        </div>
                        <h2><?= $galleryTitle ?></h2>
                    </div>

                    <div class="sortable-gallery">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="portfolio-grids gallery-container clearfix">
                                    <?php foreach ($galleryAssets as $item): ?>
                                        <div class="grid">
                                            <div class="img-holder">
                                                <a href="<?= esc($item['file_url_original'] ?? '') ?>"
                                                    class="fancybox"
                                                    data-fancybox-group="gall-1">
                                                    <img src="<?= esc($item['file_url_large'] ?? $item['file_url_original'] ?? '') ?>"
                                                        alt="<?= esc($item['alt_text'] ?? '') ?>"
                                                        class="img img-responsive">
                                                    <div class="hover-content">
                                                        <i class="ti-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- RSVP SECTION -->
        <?php if ($sectionVisibility['rsvp'] ?? true): ?>
            <section class="wpo-contact-section" id="rsvp">
                <div class="container">
                    <div class="wpo-contact-section-wrapper">
                        <div class="wpo-contact-form-area">
                            <div class="wpo-section-title">
                                <div class="section-title-img">
                                    <img src="<?= $assetsBase ?>/images/section-title.png" alt="">
                                </div>
                                <h2><?= $rsvpHeading ?></h2>
                            </div>

                            <div class="form-area">
                                <form action="<?= base_url("i/{$slug}/rsvp") ?>" method="POST" class="contact-validation-active" id="rsvp-form">
                                    <?= csrf_field() ?>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <input type="text" class="form-control" name="name" placeholder="Nombre completo *" required>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <input type="email" class="form-control" name="email" placeholder="Email *" required>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <input type="tel" class="form-control" name="phone" placeholder="Teléfono">
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <select class="form-control" name="attending" required>
                                                <option value="">¿Asistirás? *</option>
                                                <option value="1">Sí, asistiré</option>
                                                <option value="0">No podré asistir</option>
                                            </select>
                                        </div>

                                        <?php if (!empty($menuOptions)): ?>
                                            <div class="col-lg-12 col-12">
                                                <select class="form-control" name="meal_option_id">
                                                    <option value="">Preferencia de menú</option>
                                                    <?php foreach ($menuOptions as $opt): ?>
                                                        <option value="<?= $opt['id'] ?>">
                                                            <?= esc($opt['name']) ?>
                                                            <?php if ($opt['description']): ?>
                                                                - <?= esc($opt['description']) ?>
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>

                                        <div class="col-lg-12 col-12">
                                            <input type="text" class="form-control" name="song_request" placeholder="Canción que te gustaría escuchar">
                                        </div>
                                        <div class="col-lg-12 col-12">
                                            <textarea class="form-control" name="message" rows="4" placeholder="Mensaje para los novios"></textarea>
                                        </div>
                                        <div class="col-lg-12 col-12 text-center">
                                            <button type="submit" class="theme-btn-s3">Enviar confirmación</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="border-style"></div>
                        </div>
                        <div class="vector-1">
                            <img src="<?= $assetsBase ?>/images/rsvp-1.png" alt="">
                        </div>
                        <div class="vector-2">
                            <img src="<?= $assetsBase ?>/images/rsvp-2.png" alt="">
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- EVENTS SECTION -->
        <?php if (($sectionVisibility['events'] ?? true) && !empty($scheduleItems)): ?>
            <section class="wpo-event-section" id="events">
                <div class="container">
                    <div class="wpo-section-title">
                        <div class="section-title-img">
                            <img src="<?= $assetsBase ?>/images/section-title2.png" alt="">
                        </div>
                        <h2><?= $eventsTitle ?></h2>
                    </div>

                    <div class="wpo-event-wrap">
                        <div class="row">
                            <?php foreach ($scheduleItems as $item): ?>
                                <div class="col col-lg-4 col-md-6 col-12">
                                    <div class="wpo-event-item">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <div class="wpo-event-img">
                                                <img src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title']) ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="wpo-event-text">
                                            <h2><?= esc($item['title'] ?? '') ?></h2>
                                            <ul>
                                                <?php if (!empty($item['date'])): ?>
                                                    <li><?= esc($item['date']) ?></li>
                                                <?php endif; ?>
                                                <?php $timeStr = formatScheduleTime($item);
                                                if ($timeStr): ?>
                                                    <li><?= $timeStr ?></li>
                                                <?php endif; ?>
                                                <?php if (!empty($item['location'])): ?>
                                                    <li><?= esc($item['location']) ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- REGISTRY SECTION -->
        <?php if (($sectionVisibility['registry'] ?? true) && !empty($registryItems)): ?>
            <section class="wpo-registry-section" id="registry">
                <div class="container">
                    <div class="wpo-section-title">
                        <div class="section-title-img">
                            <img src="<?= $assetsBase ?>/images/section-title.png" alt="">
                        </div>
                        <h2><?= $registryTitle ?></h2>
                    </div>

                    <div class="row">
                        <?php foreach ($registryItems as $item):
                            if (!($item['is_visible'] ?? true)) continue;
                        ?>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="registry-item">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <div class="registry-img">
                                            <img src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title']) ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="registry-content">
                                        <h3><?= esc($item['title'] ?? $item['name']) ?></h3>
                                        <?php if (!empty($item['description'])): ?>
                                            <p><?= esc($item['description']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['price'])): ?>
                                            <span class="price"><?= esc($item['currency_code'] ?? 'MXN') ?> $<?= number_format($item['price'], 2) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['external_url'])): ?>
                                            <a href="<?= esc($item['external_url']) ?>" target="_blank" class="registry-link">Ver regalo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- FOOTER -->
        <footer class="wpo-site-footer">
            <div class="wpo-lower-footer text-center">
                <div class="container">
                    <div class="row">
                        <div class="col col-xs-12">
                            <p class="copyright">
                                <?= $coupleTitle ?> © <?= date('Y') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div><!-- .page-wrapper -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= $assetsBase ?>/js/bootstrap.min.js"></script>
    <script src="<?= $assetsBase ?>/js/countdown.js"></script>
    <script src="<?= $assetsBase ?>/js/owl-carousel.js"></script>
    <script src="<?= $assetsBase ?>/js/fancybox.min.js"></script>
    <script src="<?= $assetsBase ?>/js/scripts.js"></script>

    <script>
        jQuery(document).ready(function($) {
            // Countdown
            if ($("#clock").length) {
                var weddingDate = $('#clock').data('date');
                $('#clock').countdown(weddingDate, function(event) {
                    $(this).html(event.strftime('' +
                        '<div class="box"><div><div class="time">%D</div> <span>Días</span></div></div>' +
                        '<div class="box"><div><div class="time">%H</div> <span>Horas</span></div></div>' +
                        '<div class="box"><div><div class="time">%M</div> <span>Minutos</span> </div></div>' +
                        '<div class="box"><div><div class="time">%S</div> <span>Segundos</span> </div></div>'));
                });
            }

            // Gallery Fancybox
            if ($('.fancybox').length) {
                $('.fancybox').fancybox();
            }

            // Preloader
            $(window).on('load', function() {
                $('.preloader').fadeOut(500);
            });

            // RSVP Form
            $('#rsvp-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');

                submitBtn.prop('disabled', true).text('Enviando...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('¡Gracias! Tu confirmación ha sido registrada.');
                            form[0].reset();
                        } else {
                            alert(response.message || 'Ocurrió un error. Por favor intenta de nuevo.');
                        }
                    },
                    error: function() {
                        alert('Error al enviar el formulario. Por favor intenta de nuevo.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('Enviar confirmación');
                    }
                });
            });
        });
    </script>
</body>

</html>