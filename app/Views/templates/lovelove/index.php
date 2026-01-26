<?php
// Helpers
$event         = $event ?? [];
$template      = $template ?? [];
$theme         = $theme ?? [];
$modules       = $modules ?? [];
$galleryAssets = $galleryAssets ?? [];
$registryItems = $registryItems ?? [];
$registryStats = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0];

$slug        = esc($event['slug'] ?? '');
$coupleTitle = esc($event['couple_title'] ?? 'Nuestra Boda');

$startRaw     = $event['event_date_start'] ?? null;
$endRaw       = $event['event_date_end'] ?? null;
$rsvpDeadline = $event['rsvp_deadline'] ?? null;

$venueName = esc($event['venue_name'] ?? '');
$venueAddr = esc($event['venue_address'] ?? '');
$lat       = $event['venue_geo_lat'] ?? '';
$lng       = $event['venue_geo_lng'] ?? '';

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

$eventDateLabel = formatDateLabel($startRaw, 'd M Y');
$eventDateISO   = $startRaw ? date('c', strtotime($startRaw)) : '';
$eventTimeRange = trim(formatTimeLabel($startRaw) . ($endRaw ? ' - ' . formatTimeLabel($endRaw) : ''));

$rsvpDeadlineLabel = formatDateLabel($rsvpDeadline, 'd M Y');

$assetsBase = base_url('templates/lovelove'); // public/templates/lovelove

// --- Theme (si aún no tienes theme_config, cae a schema_json o defaults) ---
$schema = [];
if (!empty($template['schema_json'])) {
    $schema = json_decode($template['schema_json'], true) ?: [];
}
$schemaFonts  = $schema['fonts']  ?? ['Great Vibes', 'Dosis'];
$schemaColors = $schema['colors'] ?? ['#E57373', '#FCE4EC'];

$fontHeading  = $theme['font_heading'] ?? ($schemaFonts[0] ?? 'Great Vibes');
$fontBody     = $theme['font_body']    ?? ($schemaFonts[1] ?? 'Dosis');
$colorPrimary = $theme['primary']      ?? ($schemaColors[0] ?? '#E57373');
$colorAccent  = $theme['accent']       ?? ($schemaColors[1] ?? '#FCE4EC');

// --- Modules helper ---
function findModule(array $modules, string $code): ?array
{
    foreach ($modules as $m) {
        if (($m['code'] ?? '') === $code) return $m;
    }
    return null;
}
$modStory = findModule($modules, 'story');

// --- Small helpers ---
function moneyFmt($val, string $currency = 'MXN'): string
{
    $n = is_numeric($val) ? (float)$val : 0.0;
    // En MX normalmente: $1,234.00
    return '$' . number_format($n, 2) . ' ' . $currency;
}
function safeText($v): string
{
    return esc(trim((string)$v));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $coupleTitle ?> | 13Bodas</title>

    <!-- Favicon and Touch Icons -->
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
    // Carga extra por si el schema trae otras fuentes
    $fontsToLoad = array_unique(array_filter([$fontHeading, $fontBody]));
    foreach ($fontsToLoad as $f) {
        $q = str_replace(' ', '+', $f);
        echo '<link href="https://fonts.googleapis.com/css2?family=' . $q . ':wght@300;400;500;600;700&display=swap" rel="stylesheet">' . PHP_EOL;
    }
    ?>

    <!-- Icon fonts -->
    <link href="<?= $assetsBase ?>/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/flaticon.css" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="<?= $assetsBase ?>/css/bootstrap.min.css" rel="stylesheet">

    <!-- Plugins -->
    <link href="<?= $assetsBase ?>/css/animate.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/owl.carousel.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/owl.theme.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/slick.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/slick-theme.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/owl.transitions.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/jquery.fancybox.css" rel="stylesheet">
    <link href="<?= $assetsBase ?>/css/magnific-popup.css" rel="stylesheet">

    <!-- Custom styles -->
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

        /* Regalos */
        .registry-kpis .kpi {
            background: #fff;
            border-radius: 10px;
            padding: 18px 18px;
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

        <!-- HERO -->
        <section class="hero">
            <div class="hero-slider hero-slider-s1">
                <div class="slide-item">
                    <img src="<?= $assetsBase ?>/images/slider/slide-1.jpg" alt="" class="slider-bg">
                </div>
                <div class="slide-item">
                    <img src="<?= $assetsBase ?>/images/slider/slide-2.jpg" alt="" class="slider-bg">
                </div>
            </div>

            <div class="wedding-announcement">
                <div class="couple-name-merried-text">
                    <h2 class="wow slideInUp" data-wow-duration="1s"><?= $coupleTitle ?></h2>

                    <div class="married-text wow fadeIn" data-wow-delay="1s">
                        <h4>
                            <span class="wow fadeInUp" data-wow-delay="1.05s">N</span>
                            <span class="wow fadeInUp" data-wow-delay="1.10s">o</span>
                            <span class="wow fadeInUp" data-wow-delay="1.15s">s</span>
                            <span>&nbsp;</span>
                            <span class="wow fadeInUp" data-wow-delay="1.20s">c</span>
                            <span class="wow fadeInUp" data-wow-delay="1.25s">a</span>
                            <span class="wow fadeInUp" data-wow-delay="1.30s">s</span>
                            <span class="wow fadeInUp" data-wow-delay="1.35s">a</span>
                            <span class="wow fadeInUp" data-wow-delay="1.40s">m</span>
                            <span class="wow fadeInUp" data-wow-delay="1.45s">o</span>
                            <span class="wow fadeInUp" data-wow-delay="1.50s">s</span>
                        </h4>
                    </div>
                </div>

                <div class="save-the-date">
                    <h4>Guarda la fecha</h4>
                    <span class="date"><?= esc($eventDateLabel ?: 'Próximamente') ?></span>
                    <?php if ($eventTimeRange): ?>
                        <div style="margin-top:6px; font-size:14px; opacity:.9;">
                            <?= esc($eventTimeRange) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- HEADER -->
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
                            <li class="nav-item"><a class="nav-link" href="#gallery">Galería</a></li>
                            <li class="nav-item"><a class="nav-link" href="#registry">Regalos</a></li>
                            <li class="nav-item"><a class="nav-link" href="#rsvp">Confirmación</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <!-- COUPLE -->
        <section class="wedding-couple-section section-padding" id="couple">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">

                        <div class="gb groom">
                            <div class="img-holder wow fadeInLeftSlow">
                                <img src="<?= $assetsBase ?>/images/couple/img-1.jpg" alt="">
                            </div>
                            <div class="details">
                                <div class="details-inner">
                                    <h3>El novio</h3>
                                    <p>Estamos muy felices de compartir contigo este día tan especial.</p>
                                    <span class="signature"><?= esc(($event['groom_name'] ?? '')) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="gb bride">
                            <div class="details">
                                <div class="details-inner">
                                    <h3>La novia</h3>
                                    <p>Gracias por ser parte de nuestra historia. Te esperamos para celebrar juntos.</p>
                                    <span class="signature"><?= esc(($event['bride_name'] ?? '')) ?></span>
                                </div>
                            </div>
                            <div class="img-holder wow fadeInRightSlow">
                                <img src="<?= $assetsBase ?>/images/couple/img-2.jpg" alt="">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- COUNTDOWN -->
        <section class="count-down-section section-padding parallax"
            data-bg-image="<?= $assetsBase ?>/images/countdown-bg.jpg" data-speed="7">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <h2><span>Falta poco para…</span> Nuestra celebración</h2>
                    </div>
                    <div class="col-lg-8">
                        <div class="count-down-clock">
                            <div id="clock"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STORY -->
        <section class="story-section section-padding" id="story">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2>Nuestra historia</h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-xs-12">
                        <div class="story-timeline">

                            <?php if ($modStory && !empty($modStory['content_html'])): ?>
                                <?= $modStory['content_html'] ?>
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

        <!-- CTA -->
        <section class="cta section-padding parallax" data-bg-image="<?= $assetsBase ?>/images/cta-bg.jpg" data-speed="7">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <h2><span>Te invitamos a…</span> Celebrar con nosotros</h2>
                    </div>
                </div>
            </div>
        </section>

        <!-- EVENTS -->
        <section class="events-section section-padding" id="events">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2>Detalles del evento</h2>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col col-md-10">
                        <div class="event">
                            <div class="img-holder">
                                <img src="<?= $assetsBase ?>/images/events/img-1.jpg" alt="" class="img img-fluid">
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
                                    <a class="see-location-btn popup-gmaps" href="<?= esc($mapsUrl) ?>">
                                        Ver ubicación <i class="fa fa-angle-right"></i>
                                    </a>
                                <?php elseif ($venueAddr): ?>
                                    <?php $mapsUrl = "https://www.google.com/maps?q=" . urlencode($venueAddr) . "&output=embed"; ?>
                                    <a class="see-location-btn popup-gmaps" href="<?= esc($mapsUrl) ?>">
                                        Ver ubicación <i class="fa fa-angle-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- GALLERY (DINÁMICA) -->
        <section class="gallery-section section-padding" id="gallery">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2>Galería</h2>
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
                                <!-- Fallback: imágenes del template -->
                                <?php for ($i = 1; $i <= 9; $i++): ?>
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

        <!-- REGALOS (DINÁMICO) -->
        <section class="getting-there-section section-padding" id="registry">
            <div class="container">

                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title-white">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2>Regalos</h2>
                        </div>
                    </div>
                </div>

                <?php if (!empty($registryItems)): ?>
                    <div class="row registry-kpis">
                        <div class="col-md-3">
                            <div class="kpi d-flex align-items-center">
                                <div class="icon"><i class="fa fa-gift"></i></div>
                                <div>
                                    <div style="font-weight:700; font-size:20px;"><?= (int)($registryStats['total'] ?? 0) ?></div>
                                    <div style="opacity:.75;">Total regalos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi d-flex align-items-center">
                                <div class="icon"><i class="fa fa-check"></i></div>
                                <div>
                                    <div style="font-weight:700; font-size:20px;"><?= (int)($registryStats['claimed'] ?? 0) ?></div>
                                    <div style="opacity:.75;">Reclamados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi d-flex align-items-center">
                                <div class="icon"><i class="fa fa-hourglass-half"></i></div>
                                <div>
                                    <div style="font-weight:700; font-size:20px;"><?= (int)($registryStats['available'] ?? 0) ?></div>
                                    <div style="opacity:.75;">Disponibles</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi d-flex align-items-center">
                                <div class="icon"><i class="fa fa-money"></i></div>
                                <div>
                                    <div style="font-weight:700; font-size:20px;">
                                        <?php
                                        $currency = 'MXN';
                                        // si manejas currency_code por item, lo dejamos en MXN global
                                        echo moneyFmt((float)($registryStats['total_value'] ?? 0), $currency);
                                        ?>
                                    </div>
                                    <div style="opacity:.75;">Valor total</div>
                                </div>
                            </div>
                        </div>
                    </div>

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

                            // Si te interesa, puedes ocultar reclamados:
                            // if ($claimed) continue;
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="registry-item">
                                    <?php if ($img): ?>
                                        <img class="thumb" src="<?= $img ?>" alt="<?= $title ?>">
                                    <?php else: ?>
                                        <img class="thumb" src="<?= $assetsBase ?>/images/gift/img-1.jpg" alt="<?= $title ?>">
                                    <?php endif; ?>

                                    <div class="body">
                                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                                            <h3 style="margin:0; font-size:22px; line-height:1.2;"><?= $title ?></h3>
                                            <span class="<?= $claimed ? 'badge-claimed' : 'badge-available' ?>">
                                                <?= $claimed ? 'Reclamado' : 'Disponible' ?>
                                            </span>
                                        </div>

                                        <?php if ($desc): ?>
                                            <p style="margin-top:10px; opacity:.85;"><?= $desc ?></p>
                                        <?php endif; ?>

                                        <?php if ($price > 0): ?>
                                            <div style="margin-top:12px; font-weight:800; font-size:18px;">
                                                <?= moneyFmt($price, $cur) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div style="margin-top:14px;">
                                            <?php if ($url): ?>
                                                <a class="btn-registry" href="<?= $url ?>" target="_blank" rel="noopener">
                                                    Ver detalle <i class="fa fa-external-link"></i>
                                                </a>
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

        <!-- RSVP -->
        <section class="rsvp-section section-padding parallax"
            data-bg-image="<?= $assetsBase ?>/images/rsvp-bg.jpg" data-speed="7" id="rsvp">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="section-title-white">
                            <div class="vertical-line"><span><i class="fi flaticon-two"></i></span></div>
                            <h2>Confirma tu asistencia</h2>
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

                        <form id="rsvp-form" class="form row" method="post" action="<?= site_url("i/{$slug}/rsvp") ?>">
                            <?= csrf_field() ?>

                            <div class="col-md-6 mb-4">
                                <input type="text" name="name" class="form-control" placeholder="Tu nombre*" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <input type="email" name="email" class="form-control" placeholder="Tu email (opcional)">
                            </div>

                            <div class="col-md-6 mb-4">
                                <select class="form-control" name="attending" required>
                                    <option disabled selected>¿Asistirás?*</option>
                                    <option value="accepted">Sí, asistiré</option>
                                    <option value="declined">No podré asistir</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-4">
                                <input type="text" class="form-control" value="1" disabled
                                    title="Modo público: por ahora registra 1 asistente"
                                    placeholder="Asistentes">
                            </div>

                            <div class="col-md-12 mb-4">
                                <textarea class="form-control" name="message" placeholder="Mensaje para los novios (opcional)"></textarea>
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

        <!-- FOOTER -->
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
            rsvpUrl: <?= json_encode(site_url("i/{$slug}/rsvp")) ?>,
            galleryCount: <?= json_encode(count($galleryAssets ?? [])) ?>,
            registryCount: <?= json_encode(count($registryItems ?? [])) ?>
        };
    </script>
    <script src="<?= $assetsBase ?>/js/script.js"></script>
    <script>
        (function($) {
            'use strict';

            // RSVP AJAX hacia Invitation::submitRsvp($slug)
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

                const url = $form.attr('action');
                const data = $form.serialize();

                setLoading(true);

                $.ajax({
                        url: url,
                        method: 'POST',
                        data: data,
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

            // Si quieres, aquí puedes esconder "Regalos" del menú cuando no haya items (pero ya dejamos fallback visual)
            // if (!window.__INVITATION__.registryCount) $('a[href="#registry"]').closest('li').hide();

        })(jQuery);
    </script>

</body>

</html>