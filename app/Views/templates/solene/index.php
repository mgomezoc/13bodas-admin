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
$hFont   = $theme['font_heading'] ?? 'Playfair Display';
$bFont   = $theme['font_body'] ?? 'Inter';
$slug    = $event['slug'] ?? '';
$galleryAssets = $galleryAssets ?? [];
$faqs          = $faqs ?? ($event['faqs'] ?? []);
$scheduleItems = $scheduleItems ?? ($event['schedule_items'] ?? []);

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

$schedulePayload = solenePayload(soleneFindModule($modules, 'schedule'));
$faqPayload = solenePayload(soleneFindModule($modules, 'faq'));

$scheduleItems = !empty($scheduleItems)
    ? $scheduleItems
    : ($schedulePayload['items'] ?? ($schedulePayload['events'] ?? []));

$faqs = !empty($faqs)
    ? $faqs
    : ($faqPayload['items'] ?? []);

$couplePayload = solenePayload(soleneFindModule($modules, 'couple_info'));
$heroImage = $couplePayload['hero_image_url'] ?? '';
$brideBio = $couplePayload['bride_bio'] ?? ($couplePayload['bride']['bio'] ?? '');
$groomBio = $couplePayload['groom_bio'] ?? ($couplePayload['groom']['bio'] ?? '');
$bridePhoto = $couplePayload['bride_photo'] ?? ($couplePayload['bride']['photo'] ?? '');
$groomPhoto = $couplePayload['groom_photo'] ?? ($couplePayload['groom']['photo'] ?? '');

$eventDateHuman = soleneDateEs($event['event_date_start'], $tz);
$eventDateISO   = (new DateTime($event['event_date_start'], new DateTimeZone($tz)))->format('c'); // ISO con offset
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($event['couple_title']) ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

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
    <header class="solene-header solene-header--overlay">
        <nav class="solene-nav solene-nav--overlay" aria-label="Navegación principal">
            <a class="solene-brand" href="#hero">13Bodas</a>
            <div class="solene-nav-links">
                <a href="#couple">Bride &amp; Groom</a>
                <a href="#gallery">Gallery</a>
                <a href="#rsvp">RSVP</a>
                <a href="#lugar">When &amp; Where</a>
            </div>
        </nav>
    </header>

    <main>
        <section id="hero" class="solene-hero solene-hero--full">
            <div class="solene-hero-media" role="img" aria-label="Fotografía principal"
                style="<?= $heroImage ? 'background-image:url(' . esc($heroImage) . ');' : '' ?>">
                <div class="solene-hero-overlay"></div>
                <div class="solene-hero-inner">
                    <p class="solene-eyebrow">13Bodas presenta</p>
                    <h1 class="solene-title"><?= esc($event['couple_title']) ?></h1>
                    <p class="solene-subtitle"><?= esc($eventDateHuman) ?></p>
                    <div class="solene-countdown-inline" data-solene-countdown data-target="<?= esc($eventDateISO) ?>">
                        <div><span data-part="days">00</span><small>Días</small></div>
                        <div><span data-part="hours">00</span><small>Horas</small></div>
                        <div><span data-part="minutes">00</span><small>Min</small></div>
                        <div><span data-part="seconds">00</span><small>Seg</small></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="couple" class="solene-section solene-section--light">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Bride &amp; Groom</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-couple-grid">
                    <article class="solene-couple-card">
                        <div class="solene-couple-photo" style="<?= $bridePhoto ? 'background-image:url(' . esc($bridePhoto) . ');' : '' ?>"></div>
                        <h3 class="solene-h3"><?= esc($event['bride_name'] ?? 'Bride') ?></h3>
                        <p class="solene-text"><?= esc($brideBio ?: 'Estamos emocionados de compartir este momento contigo.') ?></p>
                    </article>
                    <article class="solene-couple-card solene-couple-card--center">
                        <h3 class="solene-h3">The wedding day</h3>
                        <p class="solene-text">Nos encantaría que seas parte de nuestra historia.</p>
                    </article>
                    <article class="solene-couple-card">
                        <div class="solene-couple-photo" style="<?= $groomPhoto ? 'background-image:url(' . esc($groomPhoto) . ');' : '' ?>"></div>
                        <h3 class="solene-h3"><?= esc($event['groom_name'] ?? 'Groom') ?></h3>
                        <p class="solene-text"><?= esc($groomBio ?: 'Gracias por acompañarnos en este día especial.') ?></p>
                    </article>
                </div>
            </div>
        </section>

        <section id="gallery" class="solene-section">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Our Wedding Gallery</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-gallery-grid">
                    <?php if (!empty($galleryAssets)): ?>
                        <?php foreach ($galleryAssets as $asset): ?>
                            <?php $img = $asset['thumb'] ?? $asset['full'] ?? ''; ?>
                            <?php if ($img): ?>
                                <figure class="solene-gallery-item">
                                    <img src="<?= esc($img) ?>" alt="<?= esc($asset['alt'] ?? $event['couple_title']) ?>">
                                </figure>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for ($i = 0; $i < 8; $i++): ?>
                            <div class="solene-gallery-item solene-gallery-item--placeholder"></div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="rsvp" class="solene-section solene-section--light">
            <div class="solene-container solene-rsvp-grid">
                <form class="solene-rsvp-card" method="post" action="<?= site_url("i/{$slug}/rsvp") ?>">
                    <?= csrf_field() ?>
                    <h3 class="solene-h3">Will you attend?</h3>
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
                    <h3 class="solene-h3">It's a Wedding Rehearsal!</h3>
                    <p class="solene-text solene-muted">Gracias por acompañarnos. Confirma tu asistencia y comparte tus mejores deseos.</p>
                    <a class="solene-btn" href="#lugar">Read more</a>
                </div>
            </div>
        </section>

        <section class="solene-section">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Partners</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-partners">
                    <span>Confidante</span>
                    <span>Love Story</span>
                    <span>Wedding Studio</span>
                    <span>Simply Romance</span>
                </div>
            </div>
        </section>

        <section class="solene-date-banner">
            <div class="solene-container">
                <h2 class="solene-h2"><?= esc($eventDateHuman) ?></h2>
                <p class="solene-text solene-muted">Únete a nosotros para celebrar.</p>
                <a class="solene-btn solene-btn--solid" href="#rsvp">RSVP</a>
            </div>
        </section>

        <?php if (!empty($event['venue_name'])): ?>
            <section id="lugar" class="solene-section" aria-label="Lugar">
                <div class="solene-container">
                    <h2 class="solene-h2 solene-center">When &amp; Where</h2>
                    <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                    <div class="solene-when-grid">
                        <div class="solene-card">
                            <h3 class="solene-h3">Ceremonia</h3>
                            <p class="solene-text"><?= esc($eventDateHuman) ?></p>
                            <p class="solene-text solene-muted"><?= esc($event['venue_name']) ?></p>
                        </div>
                        <div class="solene-card">
                            <h3 class="solene-h3">Recepción</h3>
                            <p class="solene-text"><?= esc($eventDateHuman) ?></p>
                            <p class="solene-text solene-muted"><?= esc($event['venue_address'] ?? 'Dirección por confirmar') ?></p>
                        </div>
                        <div class="solene-card">
                            <h3 class="solene-h3">Detalles</h3>
                            <p class="solene-text solene-muted">Dress code, estacionamiento y recomendaciones.</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="solene-section">
            <div class="solene-container">
                <h2 class="solene-h2 solene-center">Articles to Inspire You</h2>
                <div class="solene-divider solene-divider--center" aria-hidden="true"></div>
                <div class="solene-articles">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <article class="solene-article-card">
                            <div class="solene-article-media"></div>
                            <h3 class="solene-h3">Wedding Inspiration</h3>
                            <p class="solene-text solene-muted">Ideas para tu gran día.</p>
                        </article>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <section class="solene-map">
            <?php
            $mapSrc = '';
            if (!empty($event['venue_geo_lat']) && !empty($event['venue_geo_lng'])) {
                $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($event['venue_geo_lat'] . ',' . $event['venue_geo_lng']) . '&z=15&output=embed';
            } elseif (!empty($event['venue_address'])) {
                $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($event['venue_address']) . '&z=15&output=embed';
            }
            ?>
            <?php if ($mapSrc): ?>
                <iframe title="Mapa" src="<?= esc($mapSrc) ?>" loading="lazy"></iframe>
            <?php endif; ?>
        </section>
    </main>

    <footer class="solene-footer">
        <div class="solene-container">
            <p class="solene-muted">© <?= date('Y') ?> 13Bodas</p>
        </div>
    </footer>

    <script src="<?= base_url('templates/solene/js/main.js') ?>" defer></script>
</body>

</html>
