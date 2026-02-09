<?php
$event            = $event ?? [];
$template         = $template ?? [];
$theme            = $theme ?? [];
$modules          = $modules ?? [];
$mediaByCategory  = $mediaByCategory ?? [];
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
$eventLocations   = $eventLocations ?? [];

$assetsBase = base_url('templates/granboda');

$coupleTitle = esc($event['couple_title'] ?? 'Nuestra Boda');
$eventSlug   = esc($event['slug'] ?? '');
$startRaw    = $event['event_date_start'] ?? null;
$endRaw      = $event['event_date_end'] ?? null;
$rsvpDeadline = $event['rsvp_deadline'] ?? null;

$primaryLocation = $eventLocations[0] ?? [];
$venueName = esc($primaryLocation['name'] ?? ($event['venue_name'] ?? ''));
$venueAddr = esc($primaryLocation['address'] ?? ($event['venue_address'] ?? ''));
$venueLat  = $primaryLocation['geo_lat'] ?? ($event['venue_geo_lat'] ?? '');
$venueLng  = $primaryLocation['geo_lng'] ?? ($event['venue_geo_lng'] ?? '');

$primaryColor = $theme['primary'] ?? '#7b4b94';
$accentColor  = $theme['accent'] ?? '#f2c7cf';
$bgColor      = $theme['background'] ?? '#faf7f9';

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

function getMediaUrl(array $mediaByCategory, string $category, int $index = 0): string
{
    $items = $mediaByCategory[$category] ?? [];
    if (!isset($items[$index]) || !is_array($items[$index])) {
        return '';
    }

    $candidate = (string)($items[$index]['file_url_large'] ?? $items[$index]['file_url_thumbnail'] ?? $items[$index]['file_url_original'] ?? '');
    if ($candidate === '') {
        return '';
    }

    if (!preg_match('#^https?://#i', $candidate)) {
        $candidate = base_url($candidate);
    }

    return $candidate;
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
$timeline   = !empty($timelineItems) ? $timelineItems : ($moduleData['timeline']['events'] ?? []);
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
        <div class="hero__overlay"></div>
        <div class="container hero__content">
            <span class="hero__eyebrow">Celebración de amor</span>
            <h1 class="hero__title"><?= $coupleTitle ?></h1>
            <?php if ($eventDateLabel): ?>
                <p class="hero__date">
                    <?= esc($eventDateLabel) ?>
                    <?php if ($eventTimeRange): ?>
                        · <span><?= esc($eventTimeRange) ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if ($venueName || $venueAddr): ?>
                <p class="hero__location"><?= $venueName ?><?= $venueName && $venueAddr ? ' · ' : '' ?><?= $venueAddr ?></p>
            <?php endif; ?>
            <div class="hero__actions">
                <?php if ($eventSlug): ?>
                    <a class="btn btn-primary" href="<?= base_url('i/' . $eventSlug . '/rsvp') ?>">Confirmar asistencia</a>
                <?php endif; ?>
                <?php if ($rsvpDeadlineLabel): ?>
                    <span class="hero__deadline">RSVP antes del <?= esc($rsvpDeadlineLabel) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero__countdown" data-date="<?= esc($startRaw ?? '') ?>"></div>
    </header>

    <main>
        <section class="section section--overview">
            <div class="container grid grid--two">
                <div>
                    <h2>Detalles del evento</h2>
                    <ul class="detail-list">
                        <?php if (!empty($event['service_status'])): ?>
                            <li><strong>Estado del servicio:</strong> <?= safeText($event['service_status']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['visibility'])): ?>
                            <li><strong>Visibilidad:</strong> <?= safeText($event['visibility']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['access_mode'])): ?>
                            <li><strong>Acceso:</strong> <?= safeText($event['access_mode']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['primary_contact_email'])): ?>
                            <li><strong>Email de contacto:</strong> <?= safeText($event['primary_contact_email']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($event['time_zone'])): ?>
                            <li><strong>Zona horaria:</strong> <?= safeText($event['time_zone']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($venueLat) && !empty($venueLng)): ?>
                            <li><strong>Coordenadas:</strong> <?= safeText($venueLat) ?>, <?= safeText($venueLng) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card card--highlight">
                    <h3>Mensaje para los invitados</h3>
                    <p><?= $storyText ? esc($storyText) : 'Nos emociona compartir este gran día contigo. Consulta cada sección para conocer todos los detalles disponibles de nuestro evento.' ?></p>
                    <?php if ($venueNote): ?>
                        <p class="note"><?= esc($venueNote) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($galleryAssets)): ?>
            <section class="section">
                <div class="container">
                    <h2>Galería</h2>
                    <div class="gallery">
                        <?php foreach ($galleryAssets as $asset): ?>
                            <?php $img = $asset['thumb'] ?? $asset['full'] ?? ''; ?>
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
            <section class="section section--soft">
                <div class="container">
                    <h2>Nuestra historia</h2>
                    <div class="timeline">
                        <?php foreach ($timeline as $index => $item): ?>
                            <?php
                                $storyImage = $item['image_url'] ?? ($item['image'] ?? getMediaUrl($mediaByCategory, 'story', $index));
                                if (!empty($storyImage) && !preg_match('#^https?://#i', $storyImage)) {
                                    $storyImage = base_url($storyImage);
                                }
                                $storyText = $item['description'] ?? ($item['text'] ?? '');
                            ?>
                            <div class="timeline__item">
                                <?php if (!empty($storyImage)): ?>
                                    <img src="<?= esc($storyImage) ?>" alt="<?= esc($item['title'] ?? 'Momento especial') ?>">
                                <?php endif; ?>
                                <span class="timeline__date"><?= esc($item['year'] ?? ($item['date'] ?? '')) ?></span>
                                <h3><?= esc($item['title'] ?? 'Momento especial') ?></h3>
                                <p><?= esc($storyText) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($schedule)): ?>
            <section class="section">
                <div class="container">
                    <h2>Itinerario</h2>
                    <div class="cards">
                        <?php foreach ($schedule as $item): ?>
                            <div class="card">
                                <h3><?= esc($item['title'] ?? 'Actividad') ?></h3>
                                <?php if (!empty($item['time'])): ?>
                                    <p class="muted"><?= esc($item['time']) ?></p>
                                <?php endif; ?>
                                <p><?= esc($item['description'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($weddingParty)): ?>
            <section class="section section--soft">
                <div class="container">
                    <h2>Cortejo nupcial</h2>
                    <div class="cards">
                        <?php foreach ($weddingParty as $member): ?>
                            <div class="card card--compact">
                                <h3><?= esc($member['full_name'] ?? 'Integrante') ?></h3>
                                <?php if (!empty($member['role'])): ?>
                                    <p class="muted"><?= esc($member['role']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($member['description'])): ?>
                                    <p><?= esc($member['description']) ?></p>
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
                    <h2>Opciones de menú</h2>
                    <div class="cards">
                        <?php foreach ($menuOptions as $option): ?>
                            <div class="card">
                                <h3><?= esc($option['name'] ?? 'Platillo') ?></h3>
                                <?php if (!empty($option['description'])): ?>
                                    <p><?= esc($option['description']) ?></p>
                                <?php endif; ?>
                                <div class="chip-row">
                                    <?php if (!empty($option['is_vegan'])): ?><span class="chip">Vegano</span><?php endif; ?>
                                    <?php if (!empty($option['is_gluten_free'])): ?><span class="chip">Sin gluten</span><?php endif; ?>
                                    <?php if (!empty($option['is_kid_friendly'])): ?><span class="chip">Niños</span><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($registryItems)): ?>
            <section class="section section--soft">
                <div class="container">
                    <h2>Mesa de regalos</h2>
                    <div class="registry-stats">
                        <span>Total: <?= (int) ($registryStats['total'] ?? 0) ?></span>
                        <span>Disponibles: <?= (int) ($registryStats['available'] ?? 0) ?></span>
                        <span>Reservados: <?= (int) ($registryStats['claimed'] ?? 0) ?></span>
                    </div>
                    <?php if ($registryNote): ?>
                        <p class="note"><?= esc($registryNote) ?></p>
                    <?php endif; ?>
                    <div class="cards">
                        <?php foreach ($registryItems as $item): ?>
                            <div class="card">
                                <h3><?= esc($item['title'] ?? $item['name'] ?? 'Regalo') ?></h3>
                                <?php if (!empty($item['description'])): ?>
                                    <p><?= esc($item['description']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['price'])): ?>
                                    <p class="muted">$<?= number_format((float) $item['price'], 2) ?> <?= esc($item['currency_code'] ?? 'MXN') ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['external_url'])): ?>
                                    <a href="<?= esc($item['external_url']) ?>" class="link" target="_blank" rel="noopener">Ver regalo</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($accommodation)): ?>
            <section class="section">
                <div class="container">
                    <h2>Alojamiento recomendado</h2>
                    <div class="cards">
                        <?php foreach ($accommodation as $item): ?>
                            <div class="card">
                                <h3><?= esc($item['name'] ?? 'Hospedaje') ?></h3>
                                <?php if (!empty($item['address'])): ?>
                                    <p><?= esc($item['address']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['link'])): ?>
                                    <a class="link" href="<?= esc($item['link']) ?>" target="_blank" rel="noopener">Reservar</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($faqItems)): ?>
            <section class="section section--soft">
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

        <?php if (!empty($musicInfo)): ?>
            <section class="section">
                <div class="container">
                    <h2>Música</h2>
                    <?php if (!empty($musicInfo['playlist'])): ?>
                        <p><?= esc($musicInfo['playlist']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($musicInfo['note'])): ?>
                        <p class="note"><?= esc($musicInfo['note']) ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($customHtml): ?>
            <section class="section section--soft">
                <div class="container">
                    <h2>Contenido personalizado</h2>
                    <div class="custom-html">
                        <?= $customHtml ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($guestGroups) || !empty($guests) || !empty($rsvpResponses)): ?>
            <section class="section">
                <div class="container">
                    <h2>Invitados y RSVP</h2>
                    <div class="grid grid--three">
                        <div class="card">
                            <h3>Grupos</h3>
                            <p class="muted"><?= count($guestGroups) ?> grupos</p>
                            <?php if (!empty($guestGroups)): ?>
                                <ul class="list">
                                    <?php foreach ($guestGroups as $group): ?>
                                        <li>
                                            <?= esc($group['group_name'] ?? $group['name'] ?? 'Grupo') ?>
                                            <?php if (!empty($group['access_code'])): ?>
                                                <span class="muted">· <?= esc($group['access_code']) ?></span>
                                            <?php endif; ?>
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
                                                <span class="chip chip--outline"><?= esc($guest['attending_status']) ?></span>
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
            <p class="muted">13Bodas · Plantilla Gran Boda</p>
        </div>
    </footer>

    <script src="<?= $assetsBase ?>/js/main.js" defer></script>
</body>

</html>
