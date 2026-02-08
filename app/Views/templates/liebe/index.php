<?php
// ================================================================
// TEMPLATE: LIEBE — app/Views/templates/liebe/index.php
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

$assetsBase = base_url('templates/liebe');

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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <!--[if IE]>
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Page title -->
    <title>Liebe - Wedding HTML5 Template</title>
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
    <link rel="apple-touch-icon" sizes="72x72" href="../../apple-icon-72x72.html">
    <link rel="apple-touch-icon" sizes="114x114" href="../../apple-icon-114x114.html">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
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
    <div class="demo_changer">
        <div class="demo-icon">
            <i class="fa fa-cog fa-spin fa-2x"></i>
        </div>
        <!-- end opener icon -->
        <div class="form_holder text-center">
            <div class="row">
                <div class="col-lg-12">
                    <div class="predefined_styles">
                        <h5>Choose a Color Skin</h5>
                        <!-- MODULE #3 -->
                        <a href="summer.html" class="styleswitch"><img src="switcher/images/summer.png" alt="Summer"></a>
                        <a href="serenity.html" class="styleswitch"><img src="switcher/images/serenity.png" alt="Serenity"></a>
                        <a href="lavender.html" class="styleswitch"><img src="switcher/images/lavender.png" alt="Lavender"></a>
                        <!-- END MODULE #3 -->
                        <h5>Choose a Header style</h5>
                        <div class="headerimg">
                            <a href="index-2.html"><img src="switcher/images/photoheader.jpg" alt="Photo Header" class=""></a>
                            <a href="index2.html"><img src="switcher/images/slideheader.jpg" alt="Slide Header" class=""></a>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end form_holder -->
    </div>
    <!-- end demo_changer -->
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
                <div class="navbar-brand navbar-brand-centered page-scroll">
                    <a href="#page-top">
                        <!-- logo  -->
                        <img src="img/logo.png" class="img-responsive" alt="">
                    </a>
                </div>
                <!--/navbar-brand -->
            </div>
            <!--/navbar-header -->
            <!-- Collect the nav links, forms, and other content for toggling  -->
            <div class="collapse navbar-collapse" id="navbar-brand-centered">
                <ul class="nav navbar-nav page-scroll">
                    <li class="active"><a href="#page-top">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#story">Our Story</a></li>
                    <li><a href="#attendants">Attendants</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right page-scroll">
                    <li><a href="#event">The Event</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#rsvp">RSVP</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Pages<b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="blog.html">Blog Home</a></li>
                            <li><a href="blog-single.html">Blog Post</a></li>
                            <li><a href="elements.html">Elements</a></li>
                        </ul>
                    </li>
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
                <img src="img/couple1.jpg" alt="" class="rotate1 img-photo img-responsive">
            </div>
            <!-- /col-md-4 -->
            <div class="col-md-4  col-md-offset-4">
                <img src="img/couple2.jpg" alt="" class="rotate2 img-photo img-responsive">
            </div>
            <!-- /col-md-4 -->
        </div>
        <!-- Main Picture -->
        <div class="main-picture col-md-6 col-centered"
            data-100="margin-top:0px;transform: rotate(4deg);"
            data-center-center="margin-top:50px;transform: rotate(-10deg);">
            <!-- image-->
            <img src="img/couplemain.jpg" alt="" class="img-photo img-responsive">
        </div>
        <!--/main picture-->
        <div class="intro-heading col-md-12 text-center" data-0="opacity:1;"
            data--100-start="transform:translatey(0%);"
            data-center-bottom="transform:translatey(30%);">
            <h1>Maria <span class="italic"> & </span> Jonas
            </h1>
            <h5 class="margin1 text-ornament">Are getting married</h5>
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
                        <h2>June 19, 2024</h2>
                        <!-- divider -->
                        <div class="hr"></div>
                    </div>
                    <!--/section heading -->
                    <h5 class="margin1">Please RSVP before 15th January 2024</h5>
                    <p>
                        Viverra elit liquam erat volut pat phas ellus ac lorem ipsuet sodales Lorem ipsum dolor sit amet, consectetur adipisicing elit uasi quidem minus id iprum omnis metus.
                    </p>
                    <div class="margin1">
                        <!-- countdown tag -->
                        <span id="countdown"></span>
                        <!-- edit the countdown in the main.js file-->
                    </div>
                    <!-- /margin1-->
                    <div class="page-scroll">
                        <a href="#rsvp" class="btn">RSVP now</a>
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
                <h2>About The Couple</h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!-- /section-heading-->
            <!-- Bride Info -->
            <div class="col-md-5 col-md-offset-1">
                <img src="img/bride.jpg" alt="" class="main-img img-responsive img-circle" />
                <h4 class="text-ornament">Maria</h4>
                <h6 class="main-subheader">A Free-Spirited Woman</h6>
                <p>
                    Imperdiet interdum donec eget metus auguen unc vel lorem ispuet Ibu lum orci eget, viverra elit liquam erat volut pat phas ellus ac sodales Lorem ipsum dolor sit amet, consectetur adipisicing elit uasi quidem minus id iprum omnis.
                    Lorem ipsum dolor Phas ellus ac sodales felis tiam.
                </p>
                <!-- small social-icons -->
                <div class="social-media smaller">
                    <a href="#" title=""><i class="fa fa-twitter"></i></a>
                    <a href="#" title=""><i class="fa fa-facebook"></i></a>
                    <a href="#" title=""><i class="fa fa-linkedin"></i></a>
                    <a href="#" title=""><i class="fa fa-pinterest"></i></a>
                    <a href="#" title=""><i class="fa fa-instagram"></i></a>
                </div>
                <!-- /social-icons -->
            </div>
            <!-- /col-md-5 -->
            <!-- Groom Info -->
            <div class="col-md-5 res-margin">
                <img src="img/groom.jpg" alt="" class="main-img img-responsive img-circle" />
                <h4 class="text-ornament">Jonas</h4>
                <h6 class="main-subheader">Hopeless Romantic</h6>
                <p>
                    Imperdiet interdum donec eget metus auguen unc vel lorem ispuet Ibu lum orci eget, viverra elit liquam erat volut pat phas ellus ac sodales Lorem ipsum dolor sit amet, consectetur adipisicing elit uasi quidem minus id iprum omnis.
                    Lorem ipsum dolor Phas ellus ac sodales felis tiam.
                </p>
                <!-- small social-icons -->
                <div class="social-media smaller">
                    <a href="#" title=""><i class="fa fa-twitter"></i></a>
                    <a href="#" title=""><i class="fa fa-facebook"></i></a>
                    <a href="#" title=""><i class="fa fa-linkedin"></i></a>
                    <a href="#" title=""><i class="fa fa-pinterest"></i></a>
                    <a href="#" title=""><i class="fa fa-instagram"></i></a>
                </div>
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
    <section id="story">
        <div class="container">
            <div class="section-heading">
                <h2>Our Story</h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!-- /section-heading -->
            <!-- Polaroids -->
            <div class="row">
                <ul id="story-carousel" class="polaroids owl-carousel margin1">
                    <!-- image1 -->
                    <li class="polaroid-item"
                        data-0="transform:translatey(0%);"
                        data-center="transform:translatey(0%);transform:rotate(-4deg)">
                        <a href="img/polaroid1.jpg" data-gal="prettyPhoto[gallery]">
                            <img alt="" src="img/polaroid1.jpg" class="img-responsive" />
                            <span>2010</span>
                            <p>Our first trip together after dating for 3 Months</p>
                        </a>
                    </li>
                    <!-- image2 -->
                    <li class="polaroid-item" data-0="transform:translatey(-0%);"
                        data-center="transform:translatey(0%);transform:rotate(8deg)">
                        <a href="img/polaroid2.jpg" data-gal="prettyPhoto[gallery]">
                            <img alt="" src="img/polaroid2.jpg" class="img-responsive" />
                            <span>2012</span>
                            <p>Moving Together...</p>
                        </a>
                    </li>
                    <!-- image3 -->
                    <li class="polaroid-item" data-0="transform:translatey(0%);rotate:0;"
                        data-center="transform:translatey(0%); transform:rotate(-2deg)">
                        <a href="img/polaroid3.jpg" data-gal="prettyPhoto[gallery]">
                            <img alt="" src="img/polaroid3.jpg" class="img-responsive" />
                            <span>2014</span>
                            <p>We even got a dog!</p>
                        </a>
                    </li>
                    <!-- image4 -->
                    <li class="polaroid-item" data-0="transform:translatey(0%);"
                        data-center="transform:translatey(0%);transform:rotate(-12deg)">
                        <a href="img/polaroid4.jpg" data-gal="prettyPhoto[gallery]">
                            <img alt="" src="img/polaroid4.jpg" class="img-responsive" />
                            <span>2016</span>
                            <p>Valentines Day Surprise</p>
                        </a>
                    </li>
                    <!-- image5 -->
                    <li class="polaroid-item" data-0="transform:translatey(0%);"
                        data-center="transform:translatey(0%);transform:rotate(-12deg)">
                        <a href="img/polaroid5.jpg" data-gal="prettyPhoto[gallery]">
                            <img alt="" src="img/polaroid5.jpg" class="img-responsive" />
                            <span>2017</span>
                            <p>The Proposal</p>
                        </a>
                    </li>
                    <!-- /li polaroid -->
                </ul>
                <!-- /ul-polaroids -->
            </div>
            <!-- /row-fluid -->
        </div>
        <!-- /container-->
    </section>
    <!-- /section ends -->
    <!-- Section:attendants -->
    <section id="attendants" class="watercolor">
        <!-- parallax ornament -->
        <div class="ornament5 hidden-sm hidden-xs hidden-md" data-0="opacity:1;"
            data--100-start="transform:translatex(-10%);"
            data-center-bottom="transform:translatex(100%);">
            <!-- illustration path in the color template CSS -->
        </div>
        <div class="container">
            <div class="section-heading">
                <h2>Bridemaids <span class="italic">&</span> Groomsman</h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!-- /section-heading -->
            <!-- /col-md-3 -->
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#bridemaids" data-toggle="tab">The Ladies</a></li>
                    <li><a href="#groomsman" data-toggle="tab">The Gentlemen</a></li>
                </ul>
                <!--/nav nav-tabs -->
                <div class="tabbable">
                    <div class="tab-content">
                        <!-- tab 1 -->
                        <div class="tab-pane active in fade" id="bridemaids">
                            <!-- attendants carousel 1-->
                            <div id="owl-attendants1" class="owl-carousel">
                                <!-- attendants member 1 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant1.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Jolie Smith</h5>
                                                <h4 class="description">Best Friend</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 2 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant2.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Maria Smith</h5>
                                                <h4 class="description">Sister</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 3 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant3.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Paula Larson</h5>
                                                <h4 class="description">Cousin</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 4-->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant4.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Anna Luise </h5>
                                                <h4 class="description">School Friend</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 5 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant5.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Jane Mars</h5>
                                                <h4 class="description">Aunt</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                            </div>
                            <!-- /owl-carousel -->
                        </div>
                        <!--/ tab 1 ends -->
                        <!-- tab 2 -->
                        <div class="tab-pane fade" id="groomsman">
                            <!-- Attendants carousel 2 -->
                            <div id="owl-attendants2" class="owl-carousel">
                                <!-- attendants member 6 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant6.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Jonas Smith</h5>
                                                <h4 class="description">Best Friend</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 7 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant7.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Lucas Fonseca</h5>
                                                <h4 class="description">Cousin</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 8 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant8.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Paul Larson</h5>
                                                <h4 class="description">Business Partner</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 9 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <div class="wrap">
                                            <!-- image -->
                                            <img src="img/attendant9.jpg" alt="" class="img-circle img-responsive">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">John Doe </h5>
                                                <h4 class="description">Friend</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                                <!-- attendants member 10 -->
                                <div class="attendants-wrap col-md-12">
                                    <div class="member text-center">
                                        <!-- image -->
                                        <img src="img/attendant10.jpg" alt="" class="img-circle img-responsive">
                                        <div class="wrap">
                                            <!-- Info -->
                                            <div class="info">
                                                <h5 class="name">Marlon Mars</h5>
                                                <h4 class="description">Uncle</h4>
                                            </div>
                                            <!-- /info -->
                                        </div>
                                        <!-- /wrap -->
                                    </div>
                                    <!-- / member -->
                                </div>
                                <!--/ attendants-wrap -->
                            </div>
                            <!-- /owl-carousel -->
                        </div>
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
    <!-- /Section ends -->
    <!-- Section: Event-->
    <section id="event">
        <div class="section-heading">
            <h2>The Event</h2>
            <!-- divider -->
            <div class="hr"></div>
        </div>
        <!--/section-heading -->
        <div class="container">
            <div class="row">
                <div class="col-md-6" data--100-start="transform:translatey(-60%);"
                    data-center-bottom="transform:translatey(20%);">
                    <!-- image -->
                    <img src="img/party2.jpg" alt="" class="img-photo rotate1 img-responsive">
                </div>
                <!-- paper well -->
                <div class="well col-md-6">
                    <h3>Celebrate With Us</h3>
                    <p> Imperdiet interdum donec eget metus auguen unc vel lorem ispuet Ibu lum orci eget, viverra elit liquam erat Elit uasi quidem minus id omnis a nibh fusce mollis imperdie tlorem ipuset phas ellus ac sodales Lorem ipsum dolor Phas ellus
                    </p>
                    <p>
                        Sed eu odio interdum, molestie lorem nec, interdum leo. Suspendisse et auctor justo. Donec fermentum, nibh sit amet commodo hendrerit, enim risus mattis dui, tincidunt ornare dolor purus vel eros. Integer porta ex massa. Morbi ut nisl mauris. Nullam mollis consectetur ex vitae bibendum. Phasellus rhoncus placerat scelerisque.
                    </p>
                </div>
                <!-- /well -->
            </div>
            <!-- /row -->
            <div class="row margin1">
                <!-- paper well -->
                <div class="well col-md-7">
                    <h5>A very special day...</h5>
                    <p>Imperdiet interdum donec eget metus auguen unc vel lorem ispuet Ibu lum orci eget, viverra elit liquam erat Elit uasi quidem minus id omnis a nibh fusce mollis imperdie tlorem ipuset phas ellus ac sodales Lorem ipsum dolor Phas ellus
                    </p>
                    <p class="alert">
                        Yincidunt ornare dolor purus vel eros. Integer porta ex massa. Morbi ut nisl mauris. Nullam mollis consectetur ex vitae bibendum. Phasellus rhoncus placerat scelerisque.
                    </p>
                </div>
                <!-- /well -->
                <div class="col-md-5" data--100-start="transform:translatey(-60%);"
                    data-center-bottom="transform:translatey(20%);">
                    <!-- image -->
                    <img src="img/party1.jpg" alt="" class="img-photo rotate2 img-responsive">
                </div>
                <!-- /col-md-5 -->
            </div>
            <!-- /row-->
        </div>
        <!-- /container -->
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
                    <h3 class="date">June 19, 2022</h3>
                    <h6>6 PM - 11 PM</h6>
                    <h6>Westminster Cathedral, London UK</h6>
                    <hr>
                    <p>Ispuet Ibu lum orci eget, viverra elit liquam erat Elit uasi quidem minus id omnis a nibh fusce mollis imperdie tlorem ipuset phas ellus ac sodales Lorem ipsum dolor Phas ellus
                    </p>
                </div>
            </div>
            <!-- /well -->
        </div>
        <!-- /row-fluid -->
    </section>
    <!-- Section ends -->
    <!-- Section: Quote -->
    <section id="quote" class="container-fluid">
        <div class="col-md-7 col-centered" data-center-top="opacity: 1" data-center-bottom="opacity: 0">
            <blockquote>
                <h2>Being deeply loved by someone gives you strength, while loving someone deeply gives you courage.</h2>
            </blockquote>
        </div>
        <!-- /col-md-7-->
    </section>
    <!-- /section ends -->
    <!-- Section: Registry -->
    <section id="registry">
        <div class="section-heading text-center">
            <h2>Registry</h2>
            <!-- divider -->
            <div class="hr"></div>
        </div>
        <!--/section-heading -->
        <div class="container text-center">
            <div class="row">
                <!-- Brand 1 -->
                <div class="col-sm-6 col-md-3">
                    <a href="#"><img src="img/brand1.png" alt="" class="brand col-centered img-responsive" /></a>
                </div>
                <!-- Brand 2 -->
                <div class="col-sm-6 col-md-3 res-margin">
                    <a href="#"><img src="img/brand2.png" alt="" class="brand col-centered img-responsive" /></a>
                </div>
                <!-- Brand 3 -->
                <div class="col-sm-6 col-md-3 res-margin">
                    <a href="#"><img src="img/brand3.png" alt="" class="brand col-centered img-responsive" /></a>
                </div>
                <!-- Brand 4 -->
                <div class="col-sm-6 col-md-3 res-margin">
                    <a href="#"><img src="img/brand4.png" alt="" class="brand col-centered img-responsive" /></a>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </section>
    <!-- Section ends -->
    <!-- Section: Gallery -->
    <section id="gallery" class="watercolor">
        <div class="section-heading text-center">
            <h2>Gallery</h2>
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
                        <li class="active"><a href="#" data-toggle="tab" data-filter="*">All</a>
                        <li><a href="#" data-toggle="tab" data-filter=".our-photos">Our Photos</a></li>
                        <li><a href="#" data-toggle="tab" data-filter=".wedding">Wedding</a></li>
                    </ul>
                </div>
                <!-- Gallery -->
                <div class="col-md-12 gallery margin1">
                    <div id="lightbox">
                        <!-- Image 1 -->
                        <div class="wedding col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery1.jpg" alt="">
                                    <a href="img/gallery1.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Image 2 -->
                        <div class="our-photos col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery2.jpg" alt="">
                                    <a href="img/gallery2.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Image 3 -->
                        <div class="wedding col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery3.jpg" alt="">
                                    <a href="img/gallery3.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Image 4 -->
                        <div class="wedding col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery4.jpg" alt="">
                                    <a href="img/gallery4.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Image 5 -->
                        <div class="wedding col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery5.jpg" alt="">
                                    <a href="img/gallery5.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Image 6 -->
                        <div class="our-photos col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery6.jpg" alt="">
                                    <a href="img/gallery6.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Image 7 -->
                        <div class="our-photos col-lg-4 col-sm-6 col-md-6">
                            <div class="isotope-item">
                                <div class="gallery-thumb">
                                    <img class="img-responsive" src="img/gallery7.jpg" alt="">
                                    <a href="img/gallery7.jpg" data-gal="prettyPhoto[gallery]" title="You can add caption to pictures.">
                                        <span class="overlay-mask"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
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
                <h2>RSVP</h2>
                <!-- divider -->
                <div class="hr"></div>
            </div>
            <!-- /section-heading -->
            <div class="col-lg-5">
                <!-- image -->
                <img src="img/rsvp.jpg" alt="" class="margin1 img-photo rotate2 img-responsive">
            </div>
            <!-- well -->
            <div class="col-lg-7 well">
                <div id="rsvp_form">
                    <div class="form-group text-center">
                        <!-- name field-->
                        <h5>Full Name<span class="required">*</span></h5>
                        <input type="text" name="name" class="form-control input-field" required="">
                        <!-- checkbox attending-->
                        <input id="yes" type="radio" value="Accepts with pleasure" name="attending" />
                        <label for="yes" class="side-label">Accepts with pleasure</label>
                        <input id="no" type="radio" value="Declines with regrets" name="attending" />
                        <label for="no" class="side-label">Declines with regrets</label>
                        <!-- if attending=yes then the form bellow will show -->
                        <div class="accept-form">
                            <!-- guests checkbox -->
                            <h5>Are you bringing guests?<span class="required">*</span></h5>
                            <input id="bringing-guests" type="radio" value="yes" name="guest" /><label for="bringing-guests" class="side-label">Yes</label>
                            <input type="radio" id="just-me" value="no" name="guest" /><label for="just-me" class="side-label">No</label><br>
                            <!-- guest name text field-->
                            <div id="guest-name">
                                <h5>Guest Names</h5>
                                <input type="text" name="guests" class="form-control input-field">
                            </div>
                            <!--/guest-name -->
                        </div>
                        <!--/accept form -->
                        <!-- if attending=no then only the message box will show -->
                        <div class="message-comments">
                            <h5>Message</h5>
                            <textarea name="message" id="message-box" class="textarea-field form-control" rows="3"></textarea>
                        </div>
                        <!--/message-comments -->
                        <div class="text-center">
                            <button type="submit" id="submit_rsvp" value="Submit" class="btn">Submit</button>
                        </div>
                        <!-- /col-md-12 -->
                    </div>
                    <!-- /Form-group -->
                    <!-- Contact results -->
                    <div id="contact_results"></div>
                </div>
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
            <div class="col-md-12 text-center">
                <!-- Footer logo -->
                <img src="img/logo.png" alt="" class="center-block img-responsive">
            </div>
            <!-- /col-md-12 -->
            <!-- Credits-->
            <div class="credits col-md-12 text-center">
                Copyright © 2022 - Designed by <a href="http://www.ingridkuhn.com/">Ingrid Kuhn</a>
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
    <script src="../../../ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js" type="text/javascript"></script>
    <!-- All Scripts & Plugins -->
    <script src="<?= $assetsBase ?>/switcher/js/dmss.js"></script>
</body>

</html>