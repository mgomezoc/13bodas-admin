<?php

/**
 * Template: Solene
 * Regla: no consultar DB aquí. Consumir $event, $modules, $theme, $template.
 */

$tz = $event['time_zone'] ?? 'America/Mexico_City';

function soleneDateEs(string $datetime, string $tz): string
{
    try {
        $dt = new DateTime($datetime, new DateTimeZone($tz));
    } catch (Exception $e) {
        return $datetime;
    }

    $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $day = (int)$dt->format('d');
    $month = $months[(int)$dt->format('n')] ?? $dt->format('m');
    $year = $dt->format('Y');

    return "{$day} de {$month} de {$year}";
}

$primary = $theme['primary_color'] ?? '#B08D57';
$bg      = $theme['bg_color'] ?? '#F7F4EF';
$text    = $theme['text_color'] ?? '#232323';
$muted   = $theme['muted_text_color'] ?? '#6B6B6B';
$hFont   = $theme['font_heading'] ?? 'Cormorant Garamond';
$bFont   = $theme['font_body'] ?? 'Manrope';
$slug    = $event['slug'] ?? '';

function soleneFindModule(array $modules, string $type): ?array
{
    foreach ($modules as $module) {
        if (($module['module_type'] ?? '') === $type) {
            return $module;
        }
    }
    return null;
}

function solenePayload(?array $module): array
{
    if (!$module || empty($module['content_payload'])) {
        return [];
    }
    $decoded = json_decode($module['content_payload'], true);
    return is_array($decoded) ? $decoded : [];
}

function soleneFormatTime(?string $dt, string $tz): string
{
    if (!$dt) return '';
    try {
        $date = new DateTime($dt, new DateTimeZone($tz));
        return $date->format('H:i');
    } catch (Exception $e) {
        return $dt;
    }
}

function soleneScheduleLabel(array $item, string $tz): string
{
    if (!empty($item['time'])) {
        return (string)$item['time'];
    }
    $start = $item['starts_at'] ?? null;
    $end = $item['ends_at'] ?? null;
    if (!$start) return '';
    $startLabel = soleneFormatTime($start, $tz);
    $endLabel = $end ? soleneFormatTime($end, $tz) : '';
    return trim($startLabel . ($endLabel ? ' - ' . $endLabel : ''));
}

$couplePayload = solenePayload(soleneFindModule($modules, 'couple_info'));
$countdownPayload = solenePayload(soleneFindModule($modules, 'countdown'));
$timelinePayload = solenePayload(soleneFindModule($modules, 'timeline'));
$galleryPayload = solenePayload(soleneFindModule($modules, 'gallery'));
$venuePayload = solenePayload(soleneFindModule($modules, 'venue'));
$rsvpPayload = solenePayload(soleneFindModule($modules, 'rsvp'));
$schedulePayload = solenePayload(soleneFindModule($modules, 'schedule'));
$faqPayload = solenePayload(soleneFindModule($modules, 'faq'));

$coupleTitle = $couplePayload['couple_title'] ?? ($couplePayload['title'] ?? '');
$headline = $couplePayload['headline'] ?? '';
$subheadline = $couplePayload['subheadline'] ?? '';
$heroImage = $couplePayload['hero_image_url'] ?? ($couplePayload['hero_image'] ?? '');
$brideName = $couplePayload['bride_name'] ?? ($couplePayload['bride']['name'] ?? '');
$groomName = $couplePayload['groom_name'] ?? ($couplePayload['groom']['name'] ?? '');
$brideBio = $couplePayload['bride_bio'] ?? ($couplePayload['bride']['bio'] ?? '');
$groomBio = $couplePayload['groom_bio'] ?? ($couplePayload['groom']['bio'] ?? '');
$bridePhoto = $couplePayload['bride_photo'] ?? ($couplePayload['bride']['photo'] ?? '');
$groomPhoto = $couplePayload['groom_photo'] ?? ($couplePayload['groom']['photo'] ?? '');

$countdownDate = $countdownPayload['target_date'] ?? ($countdownPayload['date'] ?? '');
$eventDateHuman = $countdownDate ? soleneDateEs($countdownDate, $tz) : '';
$eventDateISO = '';
if ($countdownDate) {
    try {
        $eventDateISO = (new DateTime($countdownDate, new DateTimeZone($tz)))->format('c');
    } catch (Exception $e) {
        $eventDateISO = '';
    }
}

$timelineItems = $timelinePayload['items'] ?? ($timelinePayload['events'] ?? []);
$scheduleItems = $schedulePayload['items'] ?? ($schedulePayload['events'] ?? []);
$faqs = $faqPayload['items'] ?? [];
$galleryItems = $galleryPayload['images'] ?? ($galleryPayload['items'] ?? []);

$venueName = $venuePayload['name'] ?? ($venuePayload['venue_name'] ?? '');
$venueAddress = $venuePayload['address'] ?? ($venuePayload['venue_address'] ?? '');
$venueLat = $venuePayload['lat'] ?? ($venuePayload['latitude'] ?? '');
$venueLng = $venuePayload['lng'] ?? ($venuePayload['longitude'] ?? '');

$rsvpTitle = $rsvpPayload['title'] ?? '¿Confirmas asistencia?';
$rsvpText = $rsvpPayload['description'] ?? '';
$rsvpNote = $rsvpPayload['note'] ?? '';
$displayTitle = $coupleTitle ?: ($headline ?: '');
$hasHero = !empty($couplePayload) || $heroImage || $hasCountdown || $displayTitle;
$hasTimeline = !empty($timelineItems);
$hasCountdown = !empty($countdownDate);
$hasGallery = !empty($galleryItems);
$hasSchedule = !empty($scheduleItems);
$hasFaqs = !empty($faqs);
$hasVenue = !empty($venueName) || !empty($venueAddress) || (!empty($venueLat) && !empty($venueLng));
$hasRsvp = !empty($rsvpPayload);

$galleryList = [];
if (!empty($galleryItems) && is_array($galleryItems)) {
    foreach ($galleryItems as $item) {
        if (is_string($item)) {
            $galleryList[] = ['url' => $item, 'alt' => $displayTitle];
            continue;
        }
        if (!is_array($item)) continue;
        $url = $item['url'] ?? ($item['image'] ?? ($item['src'] ?? ($item['full'] ?? '')));
        if (!$url) continue;
        $galleryList[] = [
            'url' => $url,
            'thumb' => $item['thumb'] ?? ($item['thumbnail'] ?? $url),
            'alt' => $item['alt'] ?? $displayTitle,
        ];
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($displayTitle) ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('templates/solene/css/style.css') ?>">

    <style>
        :root {
            --solene-primary: <?= esc($primary) ?>;
            --solene-bg: <?= esc($bg) ?>;
            --solene-text: <?= esc($text) ?>;
            --solene-muted: <?= esc($muted) ?>;
            --solene-font-heading: <?= esc($hFont) ?>;
            --solene-font-body: <?= esc($bFont) ?>;
        }
    </style>
</head>

<body class="solene">
    <header class="solene-header<?= $hasHero ? ' solene-header--overlay' : '' ?>">
        <nav class="solene-nav<?= $hasHero ? ' solene-nav--overlay' : '' ?>" aria-label="Navegación principal">
            <a class="solene-brand" href="#hero">13Bodas</a>
            <div class="solene-nav-links">
                <?php if ($hasHero): ?><a href="#hero">Inicio</a><?php endif; ?>
                <?php if (!empty($couplePayload)): ?><a href="#pareja">Novios</a><?php endif; ?>
                <?php if ($hasTimeline): ?><a href="#historia">Historia</a><?php endif; ?>
                <?php if ($hasGallery): ?><a href="#galeria">Galería</a><?php endif; ?>
                <?php if ($hasSchedule): ?><a href="#agenda">Agenda</a><?php endif; ?>
                <?php if ($hasFaqs): ?><a href="#faqs">Preguntas</a><?php endif; ?>
                <?php if ($hasRsvp): ?><a href="#rsvp">Confirmación</a><?php endif; ?>
                <?php if ($hasVenue): ?><a href="#lugar">Cuándo y dónde</a><?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <?php if ($hasHero): ?>
        <section id="hero" class="solene-hero solene-hero--full">
            <div class="solene-hero-media" role="img" aria-label="Fotografía principal"
                style="<?= $heroImage ? 'background-image:url(' . esc($heroImage) . ');' : '' ?>">
                <div class="solene-hero-overlay"></div>
                <div class="solene-hero-inner">
                    <?php if ($headline): ?><p class="solene-eyebrow"><?= esc($headline) ?></p><?php endif; ?>
                    <h1 class="solene-title"><?= esc($displayTitle ?: 'Nuestra boda') ?></h1>
                    <?php if ($eventDateHuman): ?>
                        <p class="solene-subtitle"><?= esc($eventDateHuman) ?></p>
                    <?php endif; ?>
                    <?php if ($hasCountdown && $eventDateISO): ?>
                        <div class="solene-countdown-inline" data-solene-countdown data-target="<?= esc($eventDateISO) ?>">
                            <div><span data-part="days">00</span><small>Días</small></div>
                            <div><span data-part="hours">00</span><small>Horas</small></div>
                            <div><span data-part="minutes">00</span><small>Min</small></div>
                            <div><span data-part="seconds">00</span><small>Seg</small></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($subheadline): ?><p class="solene-text solene-muted"><?= esc($subheadline) ?></p><?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($couplePayload)): ?>
        <section id="pareja" class="solene-section solene-section--light">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Novios</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-couple-grid">
                    <article class="solene-couple-card">
                        <div class="solene-couple-photo" style="<?= $bridePhoto ? 'background-image:url(' . esc($bridePhoto) . ');' : '' ?>"></div>
                        <?php if ($brideName): ?><h3 class="solene-h3"><?= esc($brideName) ?></h3><?php endif; ?>
                        <?php if ($brideBio): ?><p class="solene-text"><?= esc($brideBio) ?></p><?php endif; ?>
                    </article>
                    <article class="solene-couple-card solene-couple-card--center">
                        <h3 class="solene-h3">Nuestro gran día</h3>
                        <?php if ($subheadline): ?>
                            <p class="solene-text"><?= esc($subheadline) ?></p>
                        <?php endif; ?>
                    </article>
                    <article class="solene-couple-card">
                        <div class="solene-couple-photo" style="<?= $groomPhoto ? 'background-image:url(' . esc($groomPhoto) . ');' : '' ?>"></div>
                        <?php if ($groomName): ?><h3 class="solene-h3"><?= esc($groomName) ?></h3><?php endif; ?>
                        <?php if ($groomBio): ?><p class="solene-text"><?= esc($groomBio) ?></p><?php endif; ?>
                    </article>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasTimeline): ?>
            <section id="historia" class="solene-section">
                <div class="solene-container">
                    <h2 class="solene-h2 solene-center"><?= esc($timelinePayload['title'] ?? 'Nuestra historia') ?></h2>
                    <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                    <div class="solene-timeline">
                        <?php foreach ($timelineItems as $item): ?>
                            <?php
                            if (!is_array($item)) continue;
                            $title = $item['title'] ?? '';
                            $date = $item['date'] ?? '';
                            $text = $item['text'] ?? ($item['description'] ?? '');
                            ?>
                            <article class="solene-timeline-item">
                                <div class="solene-timeline-mark" aria-hidden="true"></div>
                                <div class="solene-timeline-body">
                                    <?php if ($title): ?><h3 class="solene-h3"><?= esc($title) ?></h3><?php endif; ?>
                                    <?php if ($date): ?><p class="solene-text solene-muted"><?= esc($date) ?></p><?php endif; ?>
                                    <?php if ($text): ?><p class="solene-text"><?= esc($text) ?></p><?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($hasGallery && !empty($galleryList)): ?>
        <section id="galeria" class="solene-section">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Nuestra galería</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-gallery-grid">
                    <?php foreach ($galleryList as $index => $asset): ?>
                        <?php
                        $class = 'solene-gallery-item';
                        if ($index % 7 === 0) $class .= ' is-wide';
                        if ($index % 5 === 0) $class .= ' is-tall';
                        ?>
                        <figure class="<?= $class ?>">
                            <img src="<?= esc($asset['thumb'] ?? $asset['url']) ?>" alt="<?= esc($asset['alt'] ?? $displayTitle) ?>">
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasSchedule): ?>
        <section id="agenda" class="solene-section solene-section--light">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center"><?= esc($schedulePayload['title'] ?? 'Agenda del día') ?></h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-when-grid">
                    <?php foreach ($scheduleItems as $item): ?>
                        <?php
                        if (!is_array($item)) continue;
                        $title = $item['title'] ?? 'Actividad';
                        $desc = $item['description'] ?? '';
                        $timeLabel = soleneScheduleLabel($item, $tz);
                        ?>
                        <div class="solene-card">
                            <h3 class="solene-h3"><?= esc($title) ?></h3>
                            <?php if ($timeLabel): ?><p class="solene-text solene-muted"><?= esc($timeLabel) ?></p><?php endif; ?>
                            <?php if ($desc): ?><p class="solene-text"><?= esc($desc) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasFaqs): ?>
        <section id="faqs" class="solene-section">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Preguntas frecuentes</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-when-grid">
                    <?php foreach ($faqs as $faq): ?>
                        <?php if (!is_array($faq)) continue; ?>
                        <div class="solene-card">
                            <h3 class="solene-h3"><?= esc($faq['question'] ?? 'Pregunta') ?></h3>
                            <?php if (!empty($faq['answer'])): ?>
                                <p class="solene-text"><?= esc($faq['answer']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasRsvp): ?>
        <section id="rsvp" class="solene-section solene-section--light">
            <div class="solene-container solene-rsvp-grid">
                <form class="solene-rsvp-card" method="post" action="<?= site_url("i/{$slug}/rsvp") ?>">
                    <?= csrf_field() ?>
                    <h3 class="solene-h3"><?= esc($rsvpTitle) ?></h3>
                    <input class="solene-input" name="name" placeholder="Nombre*" required>
                    <input class="solene-input" type="email" name="email" placeholder="Email (opcional)">
                    <select class="solene-input" name="attending" required>
                        <option value="" disabled selected>¿Asistirás?*</option>
                        <option value="accepted">Sí, asistiré</option>
                        <option value="declined">No podré asistir</option>
                    </select>
                    <textarea class="solene-input" name="message" rows="3" placeholder="Mensaje para los novios"></textarea>
                    <button class="solene-btn solene-btn--solid" type="submit">Enviar</button>
                </form>
                <div class="solene-rsvp-copy">
                    <?php if ($rsvpText): ?><p class="solene-text solene-muted"><?= esc($rsvpText) ?></p><?php endif; ?>
                    <?php if ($rsvpNote): ?><p class="solene-text"><?= esc($rsvpNote) ?></p><?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasVenue): ?>
        <section id="lugar" class="solene-section" aria-label="Lugar">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Cuándo y dónde</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-when-grid">
                    <?php if ($venueName): ?>
                        <div class="solene-card">
                            <h3 class="solene-h3"><?= esc($venueName) ?></h3>
                            <?php if ($venueAddress): ?><p class="solene-text solene-muted"><?= esc($venueAddress) ?></p><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($venuePayload['note'] ?? ''): ?>
                        <div class="solene-card">
                            <h3 class="solene-h3">Detalles</h3>
                            <p class="solene-text"><?= esc($venuePayload['note']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php
        $mapSrc = '';
        if ($venueLat && $venueLng) {
            $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($venueLat . ',' . $venueLng) . '&z=15&output=embed';
        } elseif ($venueAddress) {
            $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($venueAddress) . '&z=15&output=embed';
        }
        ?>
        <?php if ($mapSrc): ?>
            <section class="solene-map">
                <iframe title="Mapa" src="<?= esc($mapSrc) ?>" loading="lazy"></iframe>
            </section>
        <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="solene-footer">
        <div class="solene-container">
            <p class="solene-muted">© <?= date('Y') ?> 13Bodas</p>
        </div>
    </footer>

    <script src="<?= base_url('templates/solene/js/main.js') ?>" defer></script>
</body>

</html>
