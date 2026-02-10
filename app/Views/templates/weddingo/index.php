<?php
declare(strict_types=1);
// ===========================
// Datos base (defensivo)
// ===========================
$event    = $event ?? [];
$template = $template ?? [];
$theme    = $theme ?? [];
$modules  = $modules ?? [];
$mediaByCategory = $mediaByCategory ?? [];
$eventLocations  = $eventLocations ?? [];

// NUEVO (alineado a Invitation.php)
$galleryAssets = $galleryAssets ?? ($gallery ?? []);   // compat fallback
$registryItems = $registryItems ?? ($gifts ?? []);     // compat fallback
$registryStats = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0.0];
$faqs          = $faqs ?? [];
$scheduleItems = $scheduleItems ?? [];
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

$slug        = esc($event['slug'] ?? '');
$eventId     = esc($event['id'] ?? '');
$coupleTitle = esc($event['couple_title'] ?? 'Nuestra Boda');

$startRaw     = $event['event_date_start'] ?? null;
$endRaw       = $event['event_date_end'] ?? null;
$rsvpDeadline = $event['rsvp_deadline'] ?? null;

$primaryLocation = $eventLocations[0] ?? [];
$venueName = esc($primaryLocation['name'] ?? ($event['venue_name'] ?? ''));
$venueAddr = esc($primaryLocation['address'] ?? ($event['venue_address'] ?? ''));
$lat       = $primaryLocation['geo_lat'] ?? ($event['venue_geo_lat'] ?? '');
$lng       = $primaryLocation['geo_lng'] ?? ($event['venue_geo_lng'] ?? '');

// ===========================
// Helpers
// ===========================
function fmtDate(?string $dt, string $fmt = 'd M Y'): string
{
    if (!$dt) return '';
    return date($fmt, strtotime($dt));
}
function fmtTime(?string $dt, string $fmt = 'H:i'): string
{
    if (!$dt) return '';
    return date($fmt, strtotime($dt));
}
function fmtTimeRange(?string $start, ?string $end): string
{
    $startLabel = fmtTime($start);
    $endLabel = fmtTime($end);
    return trim($startLabel . ($endLabel ? ' - ' . $endLabel : ''));
}
function moneyFmt($amount, string $currency = 'MXN'): string
{
    $n = is_numeric($amount) ? (float)$amount : 0.0;
    $formatted = number_format($n, 2, '.', ',');
    return ($currency === 'MXN' ? '$' : '') . $formatted . ($currency !== 'MXN' ? " {$currency}" : '');
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

$eventDateLabel    = fmtDate($startRaw, 'd M Y');
$eventTimeRange    = trim(fmtTime($startRaw) . ($endRaw ? ' - ' . fmtTime($endRaw) : ''));
$eventDateISO      = $startRaw ? date('c', strtotime($startRaw)) : '';
$rsvpDeadlineLabel = fmtDate($rsvpDeadline, 'd M Y');

$moduleData = [];
foreach ($modules as $module) {
    $type = $module['module_type'] ?? 'custom';
    $moduleData[$type] = decodePayload($module['content_payload'] ?? null);
}

$storyItems = !empty($timelineItems)
    ? $timelineItems
    : ($moduleData['story']['items'] ?? ($moduleData['timeline']['items'] ?? ($moduleData['timeline']['events'] ?? [])));
$storyItems = array_values(array_filter($storyItems, 'is_array'));

// Base de assets del template (public/templates/weddingo/...)
$assetsBase = base_url('templates/weddingo');

// ===========================
// Schema y secciones (toggle)
// ===========================
$schema = [];
if (!empty($template['schema_json'])) {
    $schema = json_decode($template['schema_json'], true) ?: [];
}
$schemaTheme = $schema['theme'] ?? [];
$sections = $schema['sections'] ?? [];
$enabled = [
    'hero'      => true,
    'countdown' => true,
    'story'     => true,
    'event'     => true,
    'gallery'   => true,
    'gifts'     => true,
    'rsvp'      => true,
];
if (is_array($sections) && !empty($sections)) {
    foreach ($sections as $s) {
        $id = $s['id'] ?? null;
        if ($id) $enabled[$id] = (bool)($s['enabled'] ?? true);
    }
}

// ===========================
// Theme (evento > schema > defaults)
// ===========================
$primary   = $theme['primary']   ?? ($schemaTheme['primary']['default']   ?? '#C08CA3');
$secondary = $theme['secondary'] ?? ($schemaTheme['secondary']['default'] ?? '#2C2A2E');
$bg        = $theme['bg']        ?? ($schemaTheme['bg']['default']        ?? '#FFFFFF');
$surface   = $theme['surface']   ?? ($schemaTheme['surface']['default']   ?? '#F6F3F6');
$text      = $theme['text']      ?? ($schemaTheme['text']['default']      ?? '#1E1B1F');
$muted     = $theme['muted']     ?? ($schemaTheme['muted']['default']     ?? '#6B6670');

$fontHead  = $theme['font_head'] ?? ($schemaTheme['font_head']['default'] ?? 'Playfair Display');
$fontBody  = $theme['font_body'] ?? ($schemaTheme['font_body']['default'] ?? 'Inter');

// ===========================
// Google Maps URL
// ===========================
$mapsHref = '';
if ($lat !== '' && $lng !== '') $mapsHref = 'https://www.google.com/maps?q=' . urlencode($lat . ',' . $lng);
elseif ($venueAddr) $mapsHref = 'https://www.google.com/maps?q=' . urlencode($venueAddr);

$eventImage = $primaryLocation['image_url'] ?? getMediaUrl($mediaByCategory, 'event');
if ($eventImage !== '' && !preg_match('#^https?://#i', $eventImage)) {
    $eventImage = base_url($eventImage);
}

$heroImage = getMediaUrl($mediaByCategory, 'hero') ?: $eventImage;

// ===========================
// Normalización Galería (por si viene en formato viejo)
// Esperado: ['full','thumb','alt','caption']
// ===========================
$gallery = [];
if (!empty($galleryAssets) && is_array($galleryAssets)) {
    foreach ($galleryAssets as $img) {
        if (!is_array($img)) continue;

        // formato nuevo
        $full  = (string)($img['full'] ?? '');
        $thumb = (string)($img['thumb'] ?? '');
        $alt   = (string)($img['alt'] ?? $coupleTitle);
        $cap   = (string)($img['caption'] ?? '');

        // fallback formato viejo: ['url'=>...]
        if ($full === '' && !empty($img['url'])) {
            $full = (string)$img['url'];
            $thumb = $thumb ?: $full;
        }

        if ($full === '') continue;

        if ($full !== '' && !preg_match('#^https?://#i', $full)) {
            $full = base_url($full);
        }
        if ($thumb !== '' && !preg_match('#^https?://#i', $thumb)) {
            $thumb = base_url($thumb);
        }

        $gallery[] = [
            'full'  => $full,
            'thumb' => $thumb ?: $full,
            'alt'   => $alt,
            'caption' => $cap,
        ];
    }
}

// ===========================
// Normalización Regalos (registry_items)
// ===========================
$gifts = [];
if (!empty($registryItems) && is_array($registryItems)) {
    foreach ($registryItems as $it) {
        if (!is_array($it)) continue;

        // Si viene desde registry_items (tu caso)
        $title = (string)($it['title'] ?? $it['name'] ?? 'Regalo');
        $desc  = (string)($it['description'] ?? '');
        $img   = (string)($it['image_url'] ?? '');
        $url   = (string)($it['product_url'] ?? $it['external_url'] ?? '');
        $price = $it['price'] ?? null;
        $curr  = (string)($it['currency_code'] ?? 'MXN');

        $isFund   = (int)($it['is_fund'] ?? 0) === 1;
        $isClaimed = (int)($it['is_claimed'] ?? 0) === 1;

        // fondos (si decides usar goal/current)
        $goal    = $it['goal_amount'] ?? $it['fund_goal'] ?? null;
        $current = $it['amount_collected'] ?? $it['current_amount'] ?? null;

        $gifts[] = [
            'title' => $title,
            'description' => $desc,
            'image_url' => $img,
            'link' => $url,
            'currency' => $curr,
            'price' => $price,
            'is_fund' => $isFund,
            'goal' => $goal,
            'current' => $current,
            'status' => $isClaimed ? 'claimed' : 'available',
        ];
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $coupleTitle ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?= str_replace(' ', '+', $fontHead) ?>:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=<?= str_replace(' ', '+', $fontBody) ?>:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= $assetsBase ?>/css/style.css">

    <style>
        :root {
            --w-primary: <?= esc($primary) ?>;
            --w-secondary: <?= esc($secondary) ?>;
            --w-bg: <?= esc($bg) ?>;
            --w-surface: <?= esc($surface) ?>;
            --w-text: <?= esc($text) ?>;
            --w-muted: <?= esc($muted) ?>;
            --w-font-head: "<?= esc($fontHead) ?>", serif;
            --w-font-body: "<?= esc($fontBody) ?>", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }
    </style>
<?php if (!empty($isDemoMode)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/demo-watermark.css') ?>">
<?php endif; ?>
</head>

<body>
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-banner">🚀 Evento DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar por $800 MXN</a></div>
<?php endif; ?>


    <header class="w-topbar">
        <a class="w-brand" href="#home"><?= $coupleTitle ?></a>

        <button class="w-burger" aria-label="Abrir menú" data-w-burger>
            <span></span><span></span><span></span>
        </button>

        <nav class="w-nav" data-w-nav>
            <?php if (!empty($enabled['story'])): ?><a href="#story">Historia</a><?php endif; ?>
            <?php if (!empty($enabled['event'])): ?><a href="#event">Evento</a><?php endif; ?>
            <?php if (!empty($scheduleItems)): ?><a href="#schedule">Agenda</a><?php endif; ?>
            <?php if (!empty($enabled['gallery'])): ?><a href="#gallery">Galería</a><?php endif; ?>
            <?php if (!empty($enabled['gifts'])): ?><a href="#gifts">Regalos</a><?php endif; ?>
            <?php if (!empty($faqs)): ?><a href="#faq">FAQs</a><?php endif; ?>
            <?php if (!empty($enabled['rsvp'])): ?><a href="#rsvp" class="w-nav-cta">Confirmar</a><?php endif; ?>
        </nav>
    </header>

    <?php if (!empty($enabled['hero'])): ?>
        <!-- HERO -->
        <section id="home" class="w-hero">
            <div class="w-hero-bg" aria-hidden="true"<?= $heroImage ? ' style="background-image:url(' . esc($heroImage) . ')"' : '' ?>></div>

            <div class="w-hero-inner w-container">
                <p class="w-kicker" data-reveal>13Bodas presenta</p>
                <h1 class="w-title" data-reveal><?= $coupleTitle ?></h1>

                <p class="w-subtitle" data-reveal>
                    <?= $eventDateLabel ?: 'Fecha por confirmar' ?>
                    <?php if ($eventTimeRange): ?> · <?= esc($eventTimeRange) ?><?php endif; ?>
                </p>

                <div class="w-hero-actions" data-reveal>
                    <?php if (!empty($enabled['rsvp'])): ?>
                        <a class="w-btn" href="#rsvp">Confirmar asistencia</a>
                    <?php endif; ?>

                    <?php if ($mapsHref): ?>
                        <a class="w-btn w-btn-ghost" target="_blank" rel="noopener" href="<?= esc($mapsHref) ?>">Ver ubicación</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($enabled['countdown'])): ?>
                    <div class="w-countdown" data-reveal>
                        <div class="w-pill"><span data-cd-days>--</span><small>días</small></div>
                        <div class="w-pill"><span data-cd-hours>--</span><small>horas</small></div>
                        <div class="w-pill"><span data-cd-min>--</span><small>min</small></div>
                        <div class="w-pill"><span data-cd-sec>--</span><small>seg</small></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($enabled['story'])): ?>
        <!-- STORY -->
        <section id="story" class="w-section">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Nuestra historia</h2>
                    <p>Momentos y recuerdos que marcaron nuestro camino.</p>
                </div>

                <div class="w-story-grid">
                    <?php if (!empty($storyItems)): ?>
                        <?php foreach ($storyItems as $index => $item): ?>
                            <?php
                                $storyImage = $item['image_url'] ?? ($item['image'] ?? getMediaUrl($mediaByCategory, 'story', $index));
                                if ($storyImage !== '' && !preg_match('#^https?://#i', $storyImage)) {
                                    $storyImage = base_url($storyImage);
                                }
                                $storyTitle = $item['title'] ?? 'Momento especial';
                                $storyText = $item['description'] ?? ($item['text'] ?? '');
                            ?>
                            <article class="w-card" data-reveal>
                                <?php if (!empty($storyImage)): ?>
                                    <img src="<?= esc($storyImage) ?>" alt="<?= esc($storyTitle) ?>" class="w-story-media">
                                <?php endif; ?>
                                <h3><?= esc($storyTitle) ?></h3>
                                <?php if (!empty($item['year'] ?? $item['date'] ?? '')): ?>
                                    <p class="w-muted"><?= esc($item['year'] ?? $item['date'] ?? '') ?></p>
                                <?php endif; ?>
                                <?php if ($storyText): ?><p><?= esc($storyText) ?></p><?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <article class="w-card" data-reveal>
                            <h3>Comparte tu historia</h3>
                            <p>Muy pronto verás aquí los momentos más especiales de la pareja.</p>
                        </article>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($enabled['event'])): ?>
        <!-- EVENT -->
        <section id="event" class="w-section w-section-alt">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Detalles del evento</h2>
                    <p>Ubicación, hora y lo esencial para llegar sin fricción.</p>
                </div>

                <div class="w-event">
                    <div class="w-card w-event-card" data-reveal>
                        <div class="w-event-main">
                            <h3><?= $venueName ?: 'Lugar por confirmar' ?></h3>
                            <?php if ($venueAddr): ?><p class="w-muted"><?= $venueAddr ?></p><?php endif; ?>

                            <div class="w-event-meta">
                                <div><strong>Fecha</strong><span><?= $eventDateLabel ?: '—' ?></span></div>
                                <div><strong>Horario</strong><span><?= $eventTimeRange ?: '—' ?></span></div>
                                <div><strong>RSVP</strong><span><?= $rsvpDeadlineLabel ?: '—' ?></span></div>
                            </div>

                            <div class="w-event-actions">
                                <?php if ($mapsHref): ?>
                                    <a class="w-btn w-btn-sm" target="_blank" rel="noopener" href="<?= esc($mapsHref) ?>">Abrir en Maps</a>
                                <?php endif; ?>
                                <?php if (!empty($enabled['gallery'])): ?>
                                    <a class="w-btn w-btn-sm w-btn-ghost" href="#gallery">Ver galería</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="w-event-aside" aria-hidden="true"<?= $eventImage ? ' style="background-image:url(' . esc($eventImage) . ')"' : '' ?>></div>
                    </div>
                    <?php if (count($eventLocations) > 1): ?>
                        <div class="w-story-grid" style="margin-top:24px;">
                            <?php foreach (array_slice($eventLocations, 1) as $index => $location): ?>
                                <?php
                                $locationName = $location['name'] ?? 'Evento';
                                $locationAddress = $location['address'] ?? '';
                                $locationTime = $location['time'] ?? '';
                                $locationImage = $location['image_url'] ?? getMediaUrl($mediaByCategory, 'event', $index + 1);
                                if ($locationImage !== '' && !preg_match('#^https?://#i', $locationImage)) {
                                    $locationImage = base_url($locationImage);
                                }
                                $locationMaps = $location['maps_url'] ?? '';
                                ?>
                                <article class="w-card" data-reveal>
                                    <?php if (!empty($locationImage)): ?>
                                        <img src="<?= esc($locationImage) ?>" alt="<?= esc($locationName) ?>" class="w-story-media">
                                    <?php endif; ?>
                                    <h3><?= esc($locationName) ?></h3>
                                    <?php if (!empty($locationTime)): ?><p class="w-muted"><?= esc($locationTime) ?></p><?php endif; ?>
                                    <?php if (!empty($locationAddress)): ?><p><?= esc($locationAddress) ?></p><?php endif; ?>
                                    <?php if (!empty($locationMaps)): ?>
                                        <a class="w-btn w-btn-sm w-btn-ghost" target="_blank" rel="noopener" href="<?= esc($locationMaps) ?>">Ver mapa</a>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($enabled['gallery'])): ?>
        <!-- GALLERY -->
        <section id="gallery" class="w-section">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Galería</h2>
                    <p class="w-muted">
                        <?= !empty($gallery) ? 'Momentos especiales.' : 'Aún no hay fotos cargadas.' ?>
                    </p>
                </div>

                <div class="w-gallery" data-reveal>
                    <?php if (!empty($gallery)): ?>
                        <?php foreach ($gallery as $img): ?>
                            <?php
                            $full  = esc($img['full'] ?? '');
                            $thumb = esc($img['thumb'] ?? $full);
                            $alt   = esc($img['alt'] ?? $coupleTitle);
                            $cap   = esc($img['caption'] ?? '');
                            ?>
                            <a class="w-photo" href="<?= $full ?>" target="_blank" rel="noopener" title="<?= $cap ?>">
                                <img loading="lazy" src="<?= $thumb ?>" alt="<?= $alt ?>">
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for ($i = 0; $i < 8; $i++): ?>
                            <div class="w-photo w-photo-ph" aria-hidden="true"></div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($scheduleItems)): ?>
        <!-- SCHEDULE -->
        <section id="schedule" class="w-section">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Agenda</h2>
                    <p class="w-muted">Actividades y horarios del gran día.</p>
                </div>

                <div class="w-story-grid">
                    <?php foreach ($scheduleItems as $item): ?>
                        <?php
                        $title = esc($item['title'] ?? 'Actividad');
                        $timeLabel = fmtTimeRange($item['starts_at'] ?? null, $item['ends_at'] ?? null);
                        $description = esc($item['description'] ?? '');
                        ?>
                        <article class="w-card" data-reveal>
                            <h3><?= $title ?></h3>
                            <?php if ($timeLabel): ?>
                                <p class="w-muted"><?= esc($timeLabel) ?></p>
                            <?php endif; ?>
                            <?php if ($description): ?>
                                <p><?= $description ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($enabled['gifts'])): ?>
        <!-- GIFTS -->
        <section id="gifts" class="w-section w-section-alt">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Regalos</h2>
                    <p class="w-muted">Mesa de regalos y/o aportaciones.</p>

                    <?php if (!empty($registryStats) && (int)($registryStats['total'] ?? 0) > 0): ?>
                        <div class="w-badges" style="margin-top:10px;">
                            <span class="w-badge">Total: <?= (int)$registryStats['total'] ?></span>
                            <span class="w-badge">Disponibles: <?= (int)$registryStats['available'] ?></span>
                            <span class="w-badge is-claimed">Apartados: <?= (int)$registryStats['claimed'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="w-gifts" data-reveal>
                    <?php if (!empty($gifts)): ?>
                        <?php foreach ($gifts as $g): ?>
                            <?php
                            $title = esc($g['title'] ?? 'Regalo');
                            $desc  = esc($g['description'] ?? '');
                            $img   = esc($g['image_url'] ?? '');
                            $link  = esc($g['link'] ?? '');
                            $curr  = esc($g['currency'] ?? 'MXN');
                            $isFund = !empty($g['is_fund']);
                            $status = (string)($g['status'] ?? 'available');
                            $isClaimed = $status === 'claimed';

                            $priceLabel = '';
                            if (!$isFund && isset($g['price']) && $g['price'] !== null && $g['price'] !== '') {
                                $priceLabel = moneyFmt($g['price'], (string)$curr);
                            }

                            $goalLabel = '';
                            if ($isFund && isset($g['goal']) && $g['goal'] !== null && $g['goal'] !== '') {
                                $goalLabel = moneyFmt($g['goal'], (string)$curr);
                            }

                            $currentLabel = '';
                            if ($isFund && isset($g['current']) && $g['current'] !== null && $g['current'] !== '') {
                                $currentLabel = moneyFmt($g['current'], (string)$curr);
                            }
                            ?>
                            <article class="w-card w-gift">
                                <div class="w-gift-top">
                                    <h3><?= $title ?></h3>

                                    <span class="w-badge <?= $isClaimed ? 'is-claimed' : '' ?>">
                                        <?= $isClaimed ? 'Apartado' : ($isFund ? 'Aportación' : 'Disponible') ?>
                                    </span>
                                </div>

                                <?php if ($img): ?>
                                    <div class="w-gift-media" style="margin:10px 0;">
                                        <img loading="lazy" src="<?= $img ?>" alt="<?= $title ?>" style="width:100%; border-radius:14px; display:block;">
                                    </div>
                                <?php endif; ?>

                                <?php if ($desc): ?>
                                    <p class="w-muted"><?= $desc ?></p>
                                <?php endif; ?>

                                <div class="w-gift-bottom">
                                    <div>
                                        <?php if ($priceLabel): ?>
                                            <strong class="w-price"><?= esc($priceLabel) ?></strong>
                                        <?php elseif ($isFund): ?>
                                            <strong class="w-price">Meta: <?= esc($goalLabel ?: '—') ?></strong>
                                            <?php if ($currentLabel): ?>
                                                <div class="w-muted" style="font-size: 13px; margin-top: 4px;">Recaudado: <?= esc($currentLabel) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($link): ?>
                                        <a class="w-btn w-btn-sm" target="_blank" rel="noopener" href="<?= $link ?>">Ver</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <article class="w-card w-gift">
                            <h3>Ej. Set de copas</h3>
                            <p class="w-muted">Agrega tu mesa de regalos y enlaces.</p>
                            <div class="w-gift-bottom"><strong class="w-price">$900.00</strong></div>
                        </article>
                        <article class="w-card w-gift">
                            <h3>Ej. Fondo luna de miel</h3>
                            <p class="w-muted">Aportación libre.</p>
                            <div class="w-gift-bottom"><strong class="w-price">Meta: $10,000.00</strong></div>
                        </article>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($faqs)): ?>
        <!-- FAQ -->
        <section id="faq" class="w-section w-section-alt">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Preguntas frecuentes</h2>
                    <p class="w-muted">Resolvemos dudas comunes para que disfrutes el evento.</p>
                </div>

                <div class="w-story-grid">
                    <?php foreach ($faqs as $faq): ?>
                        <article class="w-card" data-reveal>
                            <h3><?= esc($faq['question'] ?? 'Pregunta') ?></h3>
                            <p><?= esc($faq['answer'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($enabled['rsvp'])): ?>
        <!-- RSVP -->
        <section id="rsvp" class="w-section">
            <div class="w-container">
                <div class="w-section-head" data-reveal>
                    <h2>Confirmación</h2>
                    <p><?= $rsvpDeadlineLabel ? 'Confirma antes del ' . esc($rsvpDeadlineLabel) . '.' : 'Confirma tu asistencia.' ?></p>
                </div>

                <div class="w-rsvp" data-reveal>
                    <form id="rsvp-form" class="w-card w-form" method="post" action="<?= esc(base_url(route_to('rsvp.submit', $slug))) ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($selectedGuest['id'])): ?>
                            <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                            <?php if ($selectedGuestCode !== ''): ?>
                                <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="w-field">
                            <label>Tu nombre*</label>
                            <input name="name" required autocomplete="name" placeholder="Nombre y apellido" value="<?= esc($selectedGuestName) ?>">
                        </div>

                        <div class="w-field">
                            <label>Email*</label>
                            <input type="email" name="email" autocomplete="email" placeholder="correo@ejemplo.com" required value="<?= esc($selectedGuestEmail) ?>">
                        </div>

                        <div class="w-field">
                            <label>¿Asistirás?*</label>
                            <select name="attending" required>
                                <option value="" disabled selected>Selecciona…</option>
                                <option value="accepted">Sí, asistiré</option>
                                <option value="declined">No podré asistir</option>
                            </select>
                        </div>

                        <div class="w-field">
                            <label>Mensaje (opcional)</label>
                            <textarea name="message" rows="4" placeholder="Mensaje para los novios"></textarea>
                        </div>

                        <div class="w-actions">
                            <button class="w-btn" type="submit">Enviar</button>
                            <span class="w-inline" data-rsvp-loader hidden>Enviando…</span>
                        </div>

                        <div class="w-alert w-ok" data-rsvp-ok hidden></div>
                        <div class="w-alert w-err" data-rsvp-err hidden></div>
                    </form>

                    <aside class="w-card w-side">
                        <h3>Info útil</h3>
                        <p class="w-muted">Aquí puedes agregar: dress code, hashtag, niños, estacionamiento, clima, hoteles cercanos, etc.</p>
                    </aside>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <footer class="w-footer">
        <div class="w-container">
            <p>Hecho con 13Bodas · <?= $coupleTitle ?></p>
        </div>
    </footer>

    <script>
        window.__WEDDINGO__ = {
            eventId: <?= json_encode($eventId) ?>,
            slug: <?= json_encode($slug) ?>,
            eventDateISO: <?= json_encode($eventDateISO) ?>,
            rsvpUrl: <?= json_encode(base_url(route_to('rsvp.submit', $slug))) ?>,
        };
    </script>
    <script src="<?= $assetsBase ?>/js/weddingo.js"></script>
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-watermark">DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar</a></div>
<?php endif; ?>
</body>

</html>
