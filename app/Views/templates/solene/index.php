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
    <header class="solene-header">
        <nav class="solene-nav" aria-label="Navegación principal">
            <a class="solene-brand" href="#hero">13Bodas</a>
            <div class="solene-nav-links">
                <a href="#historia">Historia</a>
                <a href="#countdown">Cuenta regresiva</a>
                <?php if (!empty($event['venue_name'])): ?>
                    <a href="#lugar">Lugar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <?php foreach ($modules as $module): ?>
            <?php
            $type = $module['module_type'] ?? '';
            $cssId = $module['css_id'] ?? '';
            $payload = [];

            if (!empty($module['content_payload'])) {
                $decoded = json_decode($module['content_payload'], true);
                $payload = is_array($decoded) ? $decoded : [];
            }
            ?>

            <?php if ($type === 'couple_info'): ?>
                <?php
                $heroImage = $payload['hero_image_url'] ?? '';
                $headline = $payload['headline'] ?? '';
                $subheadline = $payload['subheadline'] ?? '';
                $ctaLabel = $payload['cta_label'] ?? '';
                $ctaTarget = $payload['cta_target'] ?? '#historia';
                ?>
                <section id="<?= esc($cssId ?: 'hero') ?>" class="solene-hero" aria-label="Portada">
                    <div class="solene-hero-media" role="img" aria-label="Fotografía principal"
                        style="<?= $heroImage ? 'background-image:url(' . esc($heroImage) . ');' : '' ?>">
                    </div>

                    <div class="solene-hero-content">
                        <?php if ($headline): ?><p class="solene-eyebrow"><?= esc($headline) ?></p><?php endif; ?>
                        <h1 class="solene-title"><?= esc($event['couple_title']) ?></h1>

                        <div class="solene-meta" aria-label="Detalles del evento">
                            <span><?= esc($eventDateHuman) ?></span>
                            <?php if (!empty($event['venue_name'])): ?>
                                <span class="solene-dot" aria-hidden="true">•</span>
                                <span><?= esc($event['venue_name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($subheadline): ?>
                            <p class="solene-subtitle"><?= esc($subheadline) ?></p>
                        <?php endif; ?>

                        <?php if ($ctaLabel): ?>
                            <a class="solene-btn" href="<?= esc($ctaTarget) ?>"><?= esc($ctaLabel) ?></a>
                        <?php endif; ?>
                    </div>
                </section>

            <?php elseif ($type === 'timeline'): ?>
                <?php
                $title = $payload['title'] ?? 'Nuestra historia';
                $items = $payload['items'] ?? [];
                $items = is_array($items) ? $items : [];
                ?>
                <section id="<?= esc($cssId ?: 'historia') ?>" class="solene-section" aria-label="Historia">
                    <div class="solene-container">
                        <h2 class="solene-h2"><?= esc($title) ?></h2>
                        <div class="solene-divider" aria-hidden="true"></div>

                        <?php if ($items): ?>
                            <div class="solene-timeline">
                                <?php foreach ($items as $it): ?>
                                    <?php
                                    $itTitle = is_array($it) ? ($it['title'] ?? '') : '';
                                    $itDate  = is_array($it) ? ($it['date'] ?? '') : '';
                                    $itText  = is_array($it) ? ($it['text'] ?? '') : '';
                                    ?>
                                    <article class="solene-timeline-item">
                                        <div class="solene-timeline-mark" aria-hidden="true"></div>
                                        <div class="solene-timeline-body">
                                            <header class="solene-timeline-head">
                                                <?php if ($itTitle): ?><h3 class="solene-h3"><?= esc($itTitle) ?></h3><?php endif; ?>
                                                <?php if ($itDate): ?><p class="solene-muted"><?= esc($itDate) ?></p><?php endif; ?>
                                            </header>
                                            <?php if ($itText): ?><p class="solene-text"><?= esc($itText) ?></p><?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="solene-text solene-muted">Próximamente compartiremos nuestra historia.</p>
                        <?php endif; ?>
                    </div>
                </section>

            <?php elseif ($type === 'countdown'): ?>
                <?php
                $title = $payload['title'] ?? 'Cuenta regresiva';
                $subtitle = $payload['subtitle'] ?? '';
                ?>
                <section id="<?= esc($cssId ?: 'countdown') ?>" class="solene-section solene-countdown" aria-label="Cuenta regresiva">
                    <div class="solene-container">
                        <h2 class="solene-h2"><?= esc($title) ?></h2>
                        <?php if ($subtitle): ?><p class="solene-text solene-muted"><?= esc($subtitle) ?></p><?php endif; ?>

                        <div class="solene-countdown-grid" data-solene-countdown data-target="<?= esc($eventDateISO) ?>">
                            <div class="solene-count-box">
                                <div class="solene-count" data-part="days">--</div>
                                <div class="solene-label">Días</div>
                            </div>
                            <div class="solene-count-box">
                                <div class="solene-count" data-part="hours">--</div>
                                <div class="solene-label">Horas</div>
                            </div>
                            <div class="solene-count-box">
                                <div class="solene-count" data-part="minutes">--</div>
                                <div class="solene-label">Min</div>
                            </div>
                            <div class="solene-count-box">
                                <div class="solene-count" data-part="seconds">--</div>
                                <div class="solene-label">Seg</div>
                            </div>
                        </div>
                    </div>
                </section>

            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($event['venue_name'])): ?>
            <section id="lugar" class="solene-section" aria-label="Lugar">
                <div class="solene-container">
                    <h2 class="solene-h2">Lugar</h2>
                    <div class="solene-divider" aria-hidden="true"></div>
                    <p class="solene-text"><strong><?= esc($event['venue_name']) ?></strong></p>
                    <?php if (!empty($event['venue_address'])): ?>
                        <p class="solene-text solene-muted"><?= esc($event['venue_address']) ?></p>
                    <?php endif; ?>
                </div>
            </section>
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