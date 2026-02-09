<?php
declare(strict_types=1);
/**
 * Template: Solene (Announcement Home)
 * Regla: no consultar DB aquí. Consumir $event, $modules, $theme, $template.
 */

$event = $event ?? [];
$modules = $modules ?? [];
$templateMeta = $templateMeta ?? [];
$mediaByCategory = $mediaByCategory ?? [];
$galleryAssets = $galleryAssets ?? [];
$registryItems = $registryItems ?? [];
$registryStats = $registryStats ?? ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0];
$weddingParty = $weddingParty ?? [];
$menuOptions = $menuOptions ?? [];
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

$tz = $event['time_zone'] ?? 'America/Mexico_City';

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

function soleneGetText(array $copyPayload, array $defaults, string $key, string $hardcoded = ''): string
{
    return esc($copyPayload[$key] ?? ($defaults[$key] ?? $hardcoded));
}

function soleneGetMediaUrl(array $mediaByCategory, string $category, int $index = 0): string
{
    $items = $mediaByCategory[$category] ?? [];
    if (empty($items) || !isset($items[$index])) return '';
    $m = $items[$index];
    $url = $m['file_url_large'] ?? ($m['file_url_original'] ?? '');
    if ($url !== '' && !preg_match('#^https?://#i', $url)) $url = base_url($url);
    return $url;
}

function soleneGetAllMediaUrls(array $mediaByCategory, string $category): array
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

function soleneDateEs(?string $datetime, string $tz): string
{
    if (!$datetime) return '';
    try {
        $dt = new DateTime($datetime, new DateTimeZone($tz));
    } catch (Exception $e) {
        return $datetime;
    }

    $months = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    $day = (int)$dt->format('d');
    $month = $months[(int)$dt->format('n')] ?? $dt->format('m');
    $year = $dt->format('Y');

    return "{$day} de {$month} de {$year}";
}

$rawDefaults = $templateMeta['defaults'] ?? [];
if (isset($rawDefaults['copy']) && is_array($rawDefaults['copy'])) {
    $defaults = $rawDefaults['copy'];
    $tplAssets = $rawDefaults['assets'] ?? [];
} else {
    $defaults = $rawDefaults;
    $tplAssets = $templateMeta['assets'] ?? [];
}

$theme = $theme ?? [];
$themeColors = $theme['colors'] ?? $theme;
$themeFonts = $theme['fonts'] ?? $theme;

$primary = $themeColors['primary'] ?? '#C9927E';
$accent = $themeColors['accent'] ?? '#E8DDD3';
$bg = $themeColors['bg'] ?? '#FFFFFF';
$bgAlt = $themeColors['surface'] ?? '#EEF3F6';
$text = $themeColors['text'] ?? '#2D2D2D';
$muted = $themeColors['muted'] ?? '#8B8B8B';

$fontHeading = $themeFonts['heading'] ?? ($themeFonts['font_heading'] ?? 'Cormorant Garamond');
$fontBody = $themeFonts['body'] ?? ($themeFonts['font_body'] ?? 'Manrope');
$fontScript = $themeFonts['script'] ?? 'Great Vibes';

$copyPayload = solenePayload(soleneFindModule($modules, 'lovely.copy'));
$couplePayload = solenePayload(soleneFindModule($modules, 'lovely.couple'));
$coupleInfoPayload = solenePayload(soleneFindModule($modules, 'couple_info'));
$timelinePayload = solenePayload(soleneFindModule($modules, 'timeline'));
$storyPayload = solenePayload(soleneFindModule($modules, 'story'));
$countdownPayload = solenePayload(soleneFindModule($modules, 'countdown'));
$venuePayload = solenePayload(soleneFindModule($modules, 'venue'));
$rsvpPayload = solenePayload(soleneFindModule($modules, 'rsvp'));

$heroTitle = $event['couple_title'] ?? ($coupleInfoPayload['headline'] ?? 'Nuestra boda');
$heroTagline = soleneGetText($copyPayload, $defaults, 'hero_tagline', 'Nos casamos');
$heroSubtitle = $coupleInfoPayload['subheadline'] ?? soleneGetText($copyPayload, $defaults, 'hero_subtitle', '¡Acompáñanos en este día especial!');

$brideName = $event['bride_name'] ?? ($couplePayload['bride']['name'] ?? '');
$groomName = $event['groom_name'] ?? ($couplePayload['groom']['name'] ?? '');
$brideBio = $couplePayload['bride']['bio'] ?? '';
$groomBio = $couplePayload['groom']['bio'] ?? '';
$brideSocial = $couplePayload['bride']['social_links'] ?? [];
$groomSocial = $couplePayload['groom']['social_links'] ?? [];

$heroImages = soleneGetAllMediaUrls($mediaByCategory, 'hero');
if (empty($heroImages)) {
    $sliderDefaults = $tplAssets['slider_images'] ?? ['images/slider/slide-1.jpg'];
    foreach ($sliderDefaults as $img) {
        $heroImages[] = base_url('templates/solene/' . ltrim($img, '/'));
    }
}
$heroImage = $heroImages[0] ?? '';

$bridePhoto = soleneGetMediaUrl($mediaByCategory, 'bride');
$groomPhoto = soleneGetMediaUrl($mediaByCategory, 'groom');

$eventDateStart = $event['event_date_start'] ?? null;
$eventDateISO = '';
if ($eventDateStart) {
    try {
        $eventDateISO = (new DateTime($eventDateStart, new DateTimeZone($tz)))->format('c');
    } catch (Exception $e) {
        $eventDateISO = '';
    }
}
$eventDateHuman = soleneDateEs($eventDateStart, $tz);

$storyItems = !empty($timelineItems)
    ? $timelineItems
    : ($timelinePayload['items'] ?? ($storyPayload['items'] ?? []));
$galleryList = $galleryAssets;

$venueLocations = $eventLocations;
$venueName = $event['venue_name'] ?? ($venueLocations[0]['name'] ?? ($venuePayload['title'] ?? ''));
$venueAddress = $event['venue_address'] ?? ($venueLocations[0]['address'] ?? '');

$rsvpTitle = $rsvpPayload['title'] ?? soleneGetText($copyPayload, $defaults, 'rsvp_heading', '¿Confirmas asistencia?');
$rsvpSubtitle = $rsvpPayload['subtitle'] ?? '';

$hasGallery = !empty($galleryList);
$hasStory = !empty($storyItems);
$hasVenue = !empty($venueName) || !empty($venueAddress) || !empty($venueLocations);
$hasRegistry = !empty($registryItems);
$hasMenu = !empty($menuOptions);
$hasWeddingParty = !empty($weddingParty);

$saveDateBg = soleneGetMediaUrl($mediaByCategory, 'cta_bg') ?: soleneGetMediaUrl($mediaByCategory, 'countdown_bg');
$saveDateBg = $saveDateBg ?: base_url('templates/solene/images/save-date.jpg');

$slug = $event['slug'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($heroTitle) ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontScript) ?>:wght@400&family=<?= urlencode($fontHeading) ?>:wght@400;500;600;700&family=<?= urlencode($fontBody) ?>:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('templates/solene/css/style.css') ?>">

    <style>
        :root {
            --solene-primary: <?= esc($primary) ?>;
            --solene-accent: <?= esc($accent) ?>;
            --solene-bg: <?= esc($bg) ?>;
            --solene-bg-alt: <?= esc($bgAlt) ?>;
            --solene-text: <?= esc($text) ?>;
            --solene-muted: <?= esc($muted) ?>;
            --solene-heading-font: "<?= esc($fontHeading) ?>", serif;
            --solene-body-font: "<?= esc($fontBody) ?>", sans-serif;
            --solene-script-font: "<?= esc($fontScript) ?>", cursive;
        }
    </style>
</head>
<body class="solene">
    <header class="solene-header" data-header>
        <nav class="solene-nav" aria-label="Navegación principal">
            <a class="solene-brand" href="#hero">13Bodas</a>
            <button class="solene-nav-toggle" type="button" data-nav-toggle aria-label="Abrir menú" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <div class="solene-nav-links" data-nav>
                <a href="#hero">Inicio</a>
                <a href="#pareja">Novios</a>
                <?php if ($hasGallery): ?><a href="#galeria">Galería</a><?php endif; ?>
                <a href="#rsvp">RSVP</a>
                <?php if ($hasWeddingParty): ?><a href="#cortejo">Partners</a><?php endif; ?>
                <a href="#savedate">Save the Date</a>
                <?php if ($hasVenue): ?><a href="#lugar">Cuándo y dónde</a><?php endif; ?>
                <?php if ($hasStory): ?><a href="#historia">Artículos</a><?php endif; ?>
                <?php if ($hasRegistry): ?><a href="#regalos">Regalos</a><?php endif; ?>
                <?php if ($hasMenu): ?><a href="#menu">Menú</a><?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section id="hero" class="solene-hero" data-parallax style="background-image:url('<?= esc($heroImage) ?>')">
            <div class="solene-hero-overlay"></div>
            <div class="solene-hero-content">
                <h1 class="solene-hero-title"><?= esc($heroTitle) ?></h1>
                <p class="solene-hero-subtitle"><?= esc($heroTagline) ?></p>
                <p class="solene-hero-note"><?= esc($heroSubtitle) ?></p>
                <div class="solene-countdown" data-countdown="<?= esc($eventDateISO) ?>">
                    <div><span data-count="months">00</span><small>Meses</small></div>
                    <div><span data-count="days">00</span><small>Días</small></div>
                    <div><span data-count="hours">00</span><small>Horas</small></div>
                    <div><span data-count="minutes">00</span><small>Min</small></div>
                    <div><span data-count="seconds">00</span><small>Seg</small></div>
                </div>
            </div>
        </section>

        <section id="pareja" class="solene-section solene-section--alt">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">BRIDE · & · GROOM</span>
                    <h2>Novios</h2>
                    <p class="solene-script">Nuestro gran día</p>
                </header>

                <div class="solene-couple-grid">
                    <article class="solene-couple-card">
                        <div class="solene-couple-photo" style="background-image:url('<?= esc($bridePhoto) ?>')"></div>
                        <h3 class="solene-script"><?= esc($brideName) ?></h3>
                        <p><?= esc($brideBio) ?></p>
                        <?php if (!empty($brideSocial)): ?>
                            <div class="solene-social">
                                <?php foreach ((array)$brideSocial as $link): ?>
                                    <?php $url = is_array($link) ? ($link['url'] ?? '') : $link; ?>
                                    <?php if ($url): ?><a href="<?= esc($url) ?>" target="_blank" rel="noopener">•</a><?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                    <div class="solene-couple-center">
                        <p><?= esc($heroSubtitle) ?></p>
                        <span class="solene-script">The wedding day</span>
                    </div>
                    <article class="solene-couple-card">
                        <div class="solene-couple-photo" style="background-image:url('<?= esc($groomPhoto) ?>')"></div>
                        <h3 class="solene-script"><?= esc($groomName) ?></h3>
                        <p><?= esc($groomBio) ?></p>
                        <?php if (!empty($groomSocial)): ?>
                            <div class="solene-social">
                                <?php foreach ((array)$groomSocial as $link): ?>
                                    <?php $url = is_array($link) ? ($link['url'] ?? '') : $link; ?>
                                    <?php if ($url): ?><a href="<?= esc($url) ?>" target="_blank" rel="noopener">•</a><?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            </div>
        </section>

        <?php if ($hasGallery): ?>
        <section id="galeria" class="solene-section">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">OUR · WEDDING · GALLERY</span>
                    <h2>Nuestra galería</h2>
                </header>
                <div class="solene-gallery">
                    <?php foreach ($galleryList as $index => $asset): ?>
                        <?php
                        $full = $asset['full'] ?? ($asset['url'] ?? '');
                        $thumb = $asset['thumb'] ?? $full;
                        $alt = $asset['alt'] ?? $heroTitle;
                        $class = 'solene-gallery-item';
                        if ($index % 4 === 0) $class .= ' is-large';
                        if ($index % 7 === 0) $class .= ' is-tall';
                        ?>
                        <?php if ($thumb): ?>
                            <figure class="<?= $class ?>" data-reveal>
                                <img data-src="<?= esc($thumb) ?>" src="<?= esc($thumb) ?>" alt="<?= esc($alt) ?>">
                            </figure>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section id="rsvp" class="solene-section solene-section--alt">
            <div class="solene-container solene-rsvp">
                <form class="solene-rsvp-form" method="post" action="<?= esc(base_url(route_to('rsvp.submit', $slug))) ?>" data-rsvp-form>
                    <?= csrf_field() ?>
                    <?php if (!empty($selectedGuest['id'])): ?>
                        <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                        <?php if ($selectedGuestCode !== ''): ?>
                            <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    <h3><?= esc($rsvpTitle) ?></h3>
                    <?php if ($rsvpSubtitle): ?><p><?= esc($rsvpSubtitle) ?></p><?php endif; ?>
                    <input type="text" name="name" placeholder="Nombre*" required value="<?= esc($selectedGuestName) ?>">
                    <input type="email" name="email" placeholder="Email*" required value="<?= esc($selectedGuestEmail) ?>">
                    <input type="text" name="phone" placeholder="Teléfono" value="<?= esc($selectedGuestPhone) ?>">
                    <select name="attending" required>
                        <option value="" disabled selected>¿Asistirás?</option>
                        <option value="accepted">Sí, asistiré</option>
                        <option value="declined">No podré asistir</option>
                    </select>
                    <input type="number" name="guests" min="1" max="10" placeholder="Número de invitados">
                    <?php if (!empty($menuOptions)): ?>
                        <select name="meal_option">
                            <option value="" disabled selected>Preferencia de menú</option>
                            <?php foreach ($menuOptions as $option): ?>
                                <option value="<?= esc($option['id'] ?? $option['name'] ?? '') ?>"><?= esc($option['name'] ?? 'Opción') ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <textarea name="message" rows="3" placeholder="Mensaje para los novios"></textarea>
                    <textarea name="song_request" rows="2" placeholder="¿Qué canción no puede faltar?"></textarea>
                    <button type="submit">Enviar</button>
                    <div class="solene-rsvp-status" data-rsvp-status></div>
                </form>
                <div class="solene-rsvp-copy">
                    <h3 class="solene-script">¡Nos casamos!</h3>
                    <p><?= esc($heroSubtitle) ?></p>
                    <?php if ($rsvpSubtitle): ?><p class="solene-muted"><?= esc($rsvpSubtitle) ?></p><?php endif; ?>
                    <a class="solene-btn" href="#rsvp">RSVP</a>
                </div>
            </div>
        </section>

        <?php if ($hasWeddingParty): ?>
        <section id="cortejo" class="solene-section solene-section--light">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">PARTNERS</span>
                    <h2>Partners</h2>
                    <p class="solene-muted">Personas especiales que nos acompañan.</p>
                </header>
                <div class="solene-partners">
                    <?php foreach ($weddingParty as $member): ?>
                        <div class="solene-partner-card">
                            <?php if (!empty($member['image_url'])): ?>
                                <img data-src="<?= esc($member['image_url']) ?>" src="<?= esc($member['image_url']) ?>" alt="<?= esc($member['full_name'] ?? 'Partner') ?>">
                            <?php endif; ?>
                            <span><?= esc($member['full_name'] ?? '') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section id="savedate" class="solene-save-date" data-parallax style="background-image:url('<?= esc($saveDateBg) ?>')">
            <div class="solene-save-date-overlay"></div>
            <div class="solene-save-date-content">
                <h2 class="solene-script"><?= esc($eventDateHuman) ?></h2>
                <a class="solene-btn" href="#lugar">VER MAPA</a>
            </div>
        </section>

        <?php if ($hasVenue): ?>
        <section id="lugar" class="solene-section">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">WHEN · & · WHERE</span>
                    <h2>Cuándo y dónde</h2>
                </header>
                <div class="solene-venue-grid">
                    <?php if (!empty($venueLocations)): ?>
                        <?php foreach ($venueLocations as $location): ?>
                            <div class="solene-venue-card">
                                <?php
                                    $locImage = $location['image_url'] ?? '';
                                    if ($locImage !== '' && !preg_match('#^https?://#i', $locImage)) {
                                        $locImage = base_url($locImage);
                                    }
                                ?>
                                <?php if (!empty($locImage)): ?>
                                    <img data-src="<?= esc($locImage) ?>" src="<?= esc($locImage) ?>" alt="<?= esc($location['name'] ?? '') ?>">
                                <?php endif; ?>
                                <h3><?= esc($location['name'] ?? '') ?></h3>
                                <?php if (!empty($location['address'])): ?><p><?= esc($location['address']) ?></p><?php endif; ?>
                                <?php if (!empty($location['time'])): ?><p class="solene-muted"><?= esc($location['time']) ?></p><?php endif; ?>
                                <div class="solene-venue-links">
                                    <?php if (!empty($location['maps_url'])): ?><a href="<?= esc($location['maps_url']) ?>" target="_blank" rel="noopener">Maps</a><?php endif; ?>
                                    <?php if (!empty($location['waze_url'])): ?><a href="<?= esc($location['waze_url']) ?>" target="_blank" rel="noopener">Waze</a><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="solene-venue-card">
                            <h3><?= esc($venueName) ?></h3>
                            <p><?= esc($venueAddress) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $mapSrc = '';
            $mapLat = $venueLocations[0]['geo_lat'] ?? ($event['venue_geo_lat'] ?? '');
            $mapLng = $venueLocations[0]['geo_lng'] ?? ($event['venue_geo_lng'] ?? '');
            if (!empty($mapLat) && !empty($mapLng)) {
                $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($mapLat . ',' . $mapLng) . '&z=15&output=embed';
            } elseif ($venueAddress) {
                $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($venueAddress) . '&z=15&output=embed';
            }
            ?>
            <?php if ($mapSrc): ?>
                <div class="solene-map">
                    <iframe title="Mapa" src="<?= esc($mapSrc) ?>" loading="lazy"></iframe>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($hasStory): ?>
        <section id="historia" class="solene-section solene-section--alt">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">ARTICLES · TO · INSPIRE · YOU</span>
                    <h2>Artículos para inspirarte</h2>
                </header>
                <div class="solene-articles">
                    <?php foreach ($storyItems as $index => $item): ?>
                        <?php if (!is_array($item)) continue; ?>
                        <article class="solene-article-card">
                            <?php
                                $fallbackImage = soleneGetMediaUrl($mediaByCategory, 'story', $index);
                                $itemImage = trim((string)($item['image_url'] ?? ($item['image'] ?? '')));
                                $storyImage = $itemImage !== '' ? $itemImage : $fallbackImage;
                                if (!empty($storyImage) && !preg_match('#^https?://#i', $storyImage)) {
                                    $storyImage = base_url($storyImage);
                                }
                            ?>
                            <?php if (!empty($storyImage)): ?>
                                <img data-src="<?= esc($storyImage) ?>" src="<?= esc($storyImage) ?>" alt="<?= esc($item['title'] ?? '') ?>">
                            <?php endif; ?>
                            <span class="solene-muted"><?= esc($item['year'] ?? ($item['date'] ?? '')) ?></span>
                            <h3><?= esc($item['title'] ?? '') ?></h3>
                            <p><?= esc($item['description'] ?? ($item['text'] ?? '')) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasRegistry): ?>
        <section id="regalos" class="solene-section">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">REGALOS</span>
                    <h2>Mesa de regalos</h2>
                </header>
                <div class="solene-registry-grid">
                    <?php foreach ($registryItems as $item): ?>
                        <div class="solene-registry-card">
                            <?php if (!empty($item['image_url'])): ?>
                                <img data-src="<?= esc($item['image_url']) ?>" src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title'] ?? $item['name'] ?? '') ?>">
                            <?php endif; ?>
                            <h3><?= esc($item['title'] ?? $item['name'] ?? 'Regalo') ?></h3>
                            <?php if (!empty($item['description'])): ?><p><?= esc($item['description']) ?></p><?php endif; ?>
                            <?php if (!empty($item['price'])): ?><p class="solene-muted">$<?= number_format((float)$item['price'], 2) ?> <?= esc($item['currency_code'] ?? 'MXN') ?></p><?php endif; ?>
                            <?php if (!empty($item['external_url'])): ?><a href="<?= esc($item['external_url']) ?>" target="_blank" rel="noopener">Ver regalo</a><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($hasMenu): ?>
        <section id="menu" class="solene-section solene-section--alt">
            <div class="solene-container">
                <header class="solene-section-head">
                    <span class="solene-kicker">MENÚ</span>
                    <h2>Opciones de menú</h2>
                </header>
                <div class="solene-menu-grid">
                    <?php foreach ($menuOptions as $option): ?>
                        <div class="solene-menu-card">
                            <h3><?= esc($option['name'] ?? 'Platillo') ?></h3>
                            <?php if (!empty($option['description'])): ?><p><?= esc($option['description']) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer class="solene-footer">
        <div class="solene-container solene-footer-grid">
            <div>
                <h3><?= esc($heroTitle) ?></h3>
                <p><?= esc($heroSubtitle) ?></p>
            </div>
            <div>
                <h4>Información</h4>
                <a href="#hero">Inicio</a>
                <a href="#rsvp">RSVP</a>
                <?php if ($hasGallery): ?><a href="#galeria">Galería</a><?php endif; ?>
            </div>
            <div>
                <h4>Detalles</h4>
                <?php if ($venueName): ?><span><?= esc($venueName) ?></span><?php endif; ?>
                <?php if ($venueAddress): ?><span><?= esc($venueAddress) ?></span><?php endif; ?>
            </div>
            <div>
                <h4>Instagram</h4>
                <div class="solene-footer-instagram">
                    <?php foreach (array_slice($galleryList, 0, 4) as $asset): ?>
                        <?php $thumb = $asset['thumb'] ?? ($asset['full'] ?? ''); ?>
                        <?php if ($thumb): ?><img data-src="<?= esc($thumb) ?>" src="<?= esc($thumb) ?>" alt="Galería"><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="solene-footer-bar">© <?= date('Y') ?> 13Bodas</div>
    </footer>

    <script src="<?= base_url('templates/solene/js/main.js') ?>" defer></script>
</body>
</html>
