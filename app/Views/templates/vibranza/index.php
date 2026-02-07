<?php
$event            = $event ?? [];
$template         = $template ?? [];
$theme            = $theme ?? [];
$modules          = $modules ?? [];
$galleryAssets    = $galleryAssets ?? [];
$registryItems    = $registryItems ?? [];
$registryStats    = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0.0];
$guestGroups      = $guestGroups ?? [];
$guests           = $guests ?? [];
$rsvpResponses    = $rsvpResponses ?? [];
$menuOptions      = $menuOptions ?? [];
$weddingParty     = $weddingParty ?? [];
$faqs             = $faqs ?? [];
$scheduleItems    = $scheduleItems ?? [];

$assetsBase = base_url('templates/vibranza');

$coupleTitle = esc($event['couple_title'] ?? 'Nuestra Boda');
$eventSlug   = esc($event['slug'] ?? '');
$startRaw    = $event['event_date_start'] ?? null;
$endRaw      = $event['event_date_end'] ?? null;
$rsvpDeadline = $event['rsvp_deadline'] ?? null;

$venueName = esc($event['venue_name'] ?? '');
$venueAddr = esc($event['venue_address'] ?? '');
$venueLat  = $event['venue_geo_lat'] ?? '';
$venueLng  = $event['venue_geo_lng'] ?? '';

$primaryColor = $theme['primary'] ?? '#ff6b6b';
$accentColor  = $theme['accent'] ?? '#6c5ce7';
$bgColor      = $theme['background'] ?? '#fff3f0';

function safeText($value): string
{
    return esc(trim((string) $value));
}

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

function decodePayload(?string $payload): array
{
    if (!$payload) return [];
    $decoded = json_decode($payload, true);
    return is_array($decoded) ? $decoded : [];
}

$eventDateLabel = formatDateLabel($startRaw, 'd M Y');
$eventTimeRange = trim(formatTimeLabel($startRaw) . ($endRaw ? ' - ' . formatTimeLabel($endRaw) : ''));
$rsvpDeadlineLabel = formatDateLabel($rsvpDeadline, 'd M Y');

$moduleData = [];
foreach ($modules as $module) {
    $type = $module['module_type'] ?? 'custom';
    $moduleData[$type] = decodePayload($module['content_payload'] ?? null);
}

$coupleInfo = $moduleData['couple_info'] ?? [];
$timeline   = $moduleData['timeline']['events'] ?? [];
$schedule   = $scheduleItems ?: ($moduleData['schedule']['items'] ?? []);
$faqItems   = $faqs ?: ($moduleData['faq']['items'] ?? []);
$customHtml = $moduleData['custom_html']['html'] ?? '';
$musicInfo  = $moduleData['music'] ?? [];
$accommodation = $moduleData['accommodation']['items'] ?? [];
$registryNote = $moduleData['registry']['note'] ?? '';
$venueNote = $moduleData['venue']['note'] ?? '';
$storyText = $moduleData['timeline']['story'] ?? ($moduleData['couple_info']['story'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $coupleTitle ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $assetsBase ?>/css/style.css">

    <style>
        :root {
            --primary: <?= esc($primaryColor) ?>;
            --accent: <?= esc($accentColor) ?>;
            --background: <?= esc($bgColor) ?>;
        }
    </style>
</head>

<body>
    <header class="hero">
        <div class="hero__bg"></div>
        <div class="container hero__content">
            <div class="hero__text">
                <span class="hero__tag">Fiesta de amor</span>
                <h1><?= $coupleTitle ?></h1>
                <?php if ($eventDateLabel): ?>
                    <p class="hero__date"><?= esc($eventDateLabel) ?><?= $eventTimeRange ? ' · ' . esc($eventTimeRange) : '' ?></p>
                <?php endif; ?>
                <?php if ($venueName || $venueAddr): ?>
                    <p class="hero__place"><?= $venueName ?><?= $venueName && $venueAddr ? ' · ' : '' ?><?= $venueAddr ?></p>
                <?php endif; ?>
                <div class="hero__actions">
                    <?php if ($eventSlug): ?>
                        <a class="btn btn-primary" href="<?= base_url('i/' . $eventSlug . '/rsvp') ?>">Confirmar asistencia</a>
                    <?php endif; ?>
                    <?php if ($rsvpDeadlineLabel): ?>
                        <span class="hero__note">RSVP antes del <?= esc($rsvpDeadlineLabel) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero__panel">
                <div class="panel">
                    <h3>Información clave</h3>
                    <ul>
                        <?php if (!empty($event['service_status'])): ?>
                            <li><strong>Estado:</strong> <?= safeText($event['service_status']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['site_mode'])): ?>
                            <li><strong>Modo:</strong> <?= safeText($event['site_mode']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['visibility'])): ?>
                            <li><strong>Visibilidad:</strong> <?= safeText($event['visibility']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['access_mode'])): ?>
                            <li><strong>Acceso:</strong> <?= safeText($event['access_mode']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['primary_contact_email'])): ?>
                            <li><strong>Contacto:</strong> <?= safeText($event['primary_contact_email']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['time_zone'])): ?>
                            <li><strong>Zona horaria:</strong> <?= safeText($event['time_zone']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($venueLat) && !empty($venueLng)): ?>
                            <li><strong>Coordenadas:</strong> <?= safeText($venueLat) ?>, <?= safeText($venueLng) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="countdown" data-date="<?= esc($startRaw ?? '') ?>"></div>
            </div>
        </div>
    </header>

    <main>
        <section class="section section--intro">
            <div class="container grid grid--split">
                <div>
                    <h2>Bienvenidos</h2>
                    <p><?= $storyText ? esc($storyText) : 'Estamos felices de compartir este gran día. Explora cada sección para conocer toda la información disponible de nuestro evento.' ?></p>
                    <?php if ($venueNote): ?>
                        <p class="badge-note"><?= esc($venueNote) ?></p>
                    <?php endif; ?>
                </div>
                <div class="stats-card">
                    <h3>Resumen del evento</h3>
                    <div class="stats">
                        <div>
                            <span>Invitados</span>
                            <strong><?= count($guests) ?></strong>
                        </div>
                        <div>
                            <span>RSVP</span>
                            <strong><?= count($rsvpResponses) ?></strong>
                        </div>
                        <div>
                            <span>Regalos</span>
                            <strong><?= (int) ($registryStats['total'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($galleryAssets)): ?>
            <section class="section section--gallery">
                <div class="container">
                    <div class="section__header">
                        <h2>Galería</h2>
                        <p>Colores, sonrisas y momentos inolvidables.</p>
                    </div>
                    <div class="gallery">
                        <?php foreach ($galleryAssets as $asset): ?>
                            <?php $img = $asset['full'] ?? $asset['thumb'] ?? ''; ?>
                            <?php if ($img): ?>
                                <figure>
                                    <img src="<?= esc($img) ?>" alt="<?= esc($asset['alt'] ?? $coupleTitle) ?>">
                                    <?php if (!empty($asset['caption'])): ?>
                                        <figcaption><?= esc($asset['caption']) ?></figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($timeline)): ?>
            <section class="section">
                <div class="container">
                    <h2>Nuestra historia</h2>
                    <div class="timeline">
                        <?php foreach ($timeline as $item): ?>
                            <article>
                                <span class="timeline__date"><?= esc($item['date'] ?? '') ?></span>
                                <h3><?= esc($item['title'] ?? 'Momento especial') ?></h3>
                                <p><?= esc($item['description'] ?? '') ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($schedule)): ?>
            <section class="section section--stripe">
                <div class="container">
                    <h2>Itinerario</h2>
                    <div class="schedule">
                        <?php foreach ($schedule as $item): ?>
                            <div class="schedule__item">
                                <div>
                                    <h3><?= esc($item['title'] ?? 'Actividad') ?></h3>
                                    <p><?= esc($item['description'] ?? '') ?></p>
                                </div>
                                <?php if (!empty($item['time'])): ?>
                                    <span class="schedule__time"><?= esc($item['time']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($menuOptions)): ?>
            <section class="section">
                <div class="container">
                    <h2>Menú</h2>
                    <div class="cards">
                        <?php foreach ($menuOptions as $option): ?>
                            <article class="card">
                                <h3><?= esc($option['name'] ?? 'Platillo') ?></h3>
                                <?php if (!empty($option['description'])): ?>
                                    <p><?= esc($option['description']) ?></p>
                                <?php endif; ?>
                                <div class="chips">
                                    <?php if (!empty($option['is_vegan'])): ?><span>Vegano</span><?php endif; ?>
                                    <?php if (!empty($option['is_gluten_free'])): ?><span>Sin gluten</span><?php endif; ?>
                                    <?php if (!empty($option['is_kid_friendly'])): ?><span>Niños</span><?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($weddingParty)): ?>
            <section class="section section--party">
                <div class="container">
                    <h2>Cortejo nupcial</h2>
                    <div class="cards cards--party">
                        <?php foreach ($weddingParty as $member): ?>
                            <article class="card card--party">
                                <h3><?= esc($member['full_name'] ?? 'Integrante') ?></h3>
                                <?php if (!empty($member['role'])): ?>
                                    <p class="muted"><?= esc($member['role']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($member['description'])): ?>
                                    <p><?= esc($member['description']) ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($registryItems)): ?>
            <section class="section">
                <div class="container">
                    <h2>Mesa de regalos</h2>
                    <div class="summary">
                        <span>Total: <?= (int) ($registryStats['total'] ?? 0) ?></span>
                        <span>Disponibles: <?= (int) ($registryStats['available'] ?? 0) ?></span>
                        <span>Reservados: <?= (int) ($registryStats['claimed'] ?? 0) ?></span>
                    </div>
                    <?php if ($registryNote): ?>
                        <p class="badge-note"><?= esc($registryNote) ?></p>
                    <?php endif; ?>
                    <div class="cards">
                        <?php foreach ($registryItems as $item): ?>
                            <article class="card">
                                <h3><?= esc($item['title'] ?? $item['name'] ?? 'Regalo') ?></h3>
                                <?php if (!empty($item['description'])): ?>
                                    <p><?= esc($item['description']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['price'])): ?>
                                    <p class="muted">$<?= number_format((float) $item['price'], 2) ?> <?= esc($item['currency_code'] ?? 'MXN') ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['external_url'])): ?>
                                    <a href="<?= esc($item['external_url']) ?>" target="_blank" rel="noopener" class="link">Ver regalo</a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($accommodation)): ?>
            <section class="section section--stripe">
                <div class="container">
                    <h2>Alojamiento</h2>
                    <div class="cards">
                        <?php foreach ($accommodation as $item): ?>
                            <article class="card">
                                <h3><?= esc($item['name'] ?? 'Opción') ?></h3>
                                <?php if (!empty($item['address'])): ?>
                                    <p><?= esc($item['address']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['link'])): ?>
                                    <a class="link" href="<?= esc($item['link']) ?>" target="_blank" rel="noopener">Reservar</a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($faqItems)): ?>
            <section class="section">
                <div class="container">
                    <h2>Preguntas frecuentes</h2>
                    <div class="faq">
                        <?php foreach ($faqItems as $item): ?>
                            <details>
                                <summary><?= esc($item['question'] ?? 'Pregunta') ?></summary>
                                <p><?= esc($item['answer'] ?? '') ?></p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($musicInfo) || $customHtml): ?>
            <section class="section section--accent">
                <div class="container grid grid--split">
                    <?php if (!empty($musicInfo)): ?>
                        <div>
                            <h2>Música</h2>
                            <?php if (!empty($musicInfo['playlist'])): ?>
                                <p><?= esc($musicInfo['playlist']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($musicInfo['note'])): ?>
                                <p class="badge-note"><?= esc($musicInfo['note']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($customHtml): ?>
                        <div class="card card--accent">
                            <h2>Contenido especial</h2>
                            <div class="custom-html">
                                <?= $customHtml ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($guestGroups) || !empty($guests) || !empty($rsvpResponses)): ?>
            <section class="section">
                <div class="container">
                    <h2>Invitados y confirmaciones</h2>
                    <div class="grid grid--three">
                        <div class="card">
                            <h3>Grupos</h3>
                            <p class="muted"><?= count($guestGroups) ?> grupos</p>
                            <?php if (!empty($guestGroups)): ?>
                                <ul class="list">
                                    <?php foreach ($guestGroups as $group): ?>
                                        <li>
                                            <?= esc($group['group_name'] ?? $group['name'] ?? 'Grupo') ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card">
                            <h3>Invitados</h3>
                            <p class="muted"><?= count($guests) ?> invitados</p>
                            <?php if (!empty($guests)): ?>
                                <ul class="list">
                                    <?php foreach ($guests as $guest): ?>
                                        <li>
                                            <?= esc(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')) ?>
                                            <?php if (!empty($guest['attending_status'])): ?>
                                                <span class="pill"><?= esc($guest['attending_status']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card">
                            <h3>Respuestas RSVP</h3>
                            <p class="muted"><?= count($rsvpResponses) ?> respuestas</p>
                            <?php if (!empty($rsvpResponses)): ?>
                                <ul class="list">
                                    <?php foreach ($rsvpResponses as $resp): ?>
                                        <li>
                                            <strong><?= esc($resp['attending_status'] ?? 'Pendiente') ?></strong>
                                            <?php if (!empty($resp['song_request'])): ?>
                                                <span class="muted">· <?= esc($resp['song_request']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p><?= $coupleTitle ?> · <?= $eventDateLabel ?: 'Celebración' ?></p>
            <p class="muted">13Bodas · Plantilla Vibranza</p>
        </div>
    </footer>

    <script src="<?= $assetsBase ?>/js/main.js" defer></script>
</body>

</html>
