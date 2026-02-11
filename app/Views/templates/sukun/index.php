<?php
declare(strict_types=1);
/**
 * Template: Sukun (Estilo Feelings)
 * NO consulta BD. Consume: $event, $modules, $theme, $template,
 * $templateMeta, $mediaByCategory, $galleryAssets,
 * $registryItems, $registryStats, $weddingParty, $menuOptions
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
$slug = $event['slug'] ?? '';

function skFindModule(array $modules, string ...$types): ?array
{
    foreach ($modules as $m) {
        if (in_array($m['module_type'] ?? '', $types, true)) {
            return $m;
        }
    }
    return null;
}

function skPayload(?array $module): array
{
    if (!$module) return [];
    $p = $module['content_payload'] ?? [];
    if (is_string($p)) {
        $decoded = json_decode($p, true);
        $p = is_array($decoded) ? $decoded : [];
    }
    return is_array($p) ? $p : [];
}

function skText(array $copy, array $defaults, string $key, string $fallback = ''): string
{
    return esc($copy[$key] ?? ($defaults[$key] ?? $fallback));
}

function skMediaUrl(array $mbc, string $cat, int $idx = 0, string $field = 'file_url_original'): string
{
    $item = $mbc[$cat][$idx][$field] ?? ($mbc[$cat][$idx]['file_url_large'] ?? ($mbc[$cat][$idx]['file_url_thumbnail'] ?? ''));
    if (!$item) return '';
    if (!preg_match('#^https?://#i', $item)) return base_url($item);
    return $item;
}

function skAllMedia(array $mbc, string $cat): array
{
    return $mbc[$cat] ?? [];
}

function skDateEs(?string $dt, string $tz, string $pattern = "d 'de' MMMM, yyyy"): string
{
    if (!$dt) return '';
    try {
        $d = new DateTime($dt, new DateTimeZone($tz));
        if (class_exists(IntlDateFormatter::class)) {
            $fmt = new IntlDateFormatter('es_MX', IntlDateFormatter::LONG, IntlDateFormatter::NONE, $tz);
            $fmt->setPattern($pattern);
            return (string)$fmt->format($d);
        }
        return $d->format('d/m/Y');
    } catch (Exception $e) {
        return $dt;
    }
}

function skDateIso(?string $dt, string $tz): string
{
    if (!$dt) return '';
    try {
        return (new DateTime($dt, new DateTimeZone($tz)))->format(DATE_ATOM);
    } catch (Exception $e) {
        return '';
    }
}

$themeArr = $theme ?? [];
if (is_string($themeArr)) {
    $themeArr = json_decode($themeArr, true) ?: [];
}
$sectionVisibility = $themeArr['sections'] ?? ($templateMeta['section_visibility'] ?? []);

$primary = $themeArr['colors']['primary'] ?? ($themeArr['primary'] ?? '#C9A98C');
$accent = $themeArr['colors']['accent'] ?? ($themeArr['secondary'] ?? '#8BA888');
$bgColor = $themeArr['colors']['bg'] ?? ($themeArr['bg'] ?? '#FFF9F5');
$textColor = $themeArr['colors']['text'] ?? ($themeArr['text'] ?? '#2B2B2B');
$mutedColor = $themeArr['colors']['muted'] ?? ($themeArr['muted'] ?? '#8B8680');
$fontScript = 'Great Vibes';
$fontSerif = $themeArr['fonts']['heading'] ?? ($themeArr['font_head'] ?? 'Cormorant Garamond');
$fontBody = $themeArr['fonts']['body'] ?? ($themeArr['font_body'] ?? 'Jost');

$rawDefaults = $templateMeta['defaults'] ?? [];
if (isset($rawDefaults['copy']) && is_array($rawDefaults['copy'])) {
    $defaults = $rawDefaults['copy'];
    $tplAssets = $rawDefaults['assets'] ?? [];
} else {
    $defaults = $rawDefaults;
    $tplAssets = $templateMeta['assets'] ?? [];
}

$copyPayload = skPayload(skFindModule($modules, 'lovely.copy', 'sukun.copy'));
$couplePayload = skPayload(skFindModule($modules, 'lovely.couple', 'sukun.couple', 'couple_info'));
$storyPayload = skPayload(skFindModule($modules, 'story', 'timeline'));
$countdownPayload = skPayload(skFindModule($modules, 'countdown'));
$venuePayload = skPayload(skFindModule($modules, 'venue'));
$rsvpPayload = skPayload(skFindModule($modules, 'rsvp'));

$brideName = $event['bride_name'] ?? ($couplePayload['bride_name'] ?? ($couplePayload['bride']['name'] ?? ''));
$groomName = $event['groom_name'] ?? ($couplePayload['groom_name'] ?? ($couplePayload['groom']['name'] ?? ''));
$coupleTitle = $event['couple_title'] ?? ($brideName && $groomName ? "$brideName & $groomName" : 'Nuestra Boda');
$heroTagline = skText($copyPayload, $defaults, 'hero_tagline', 'WE ARE GETTING MARRIED');
$heroSubtitle = $couplePayload['subheadline'] ?? skText($copyPayload, $defaults, 'hero_subtitle', '¡Acompáñanos en este día especial!');

$brideBio = $couplePayload['bride']['bio'] ?? ($couplePayload['bride_bio'] ?? ($defaults['bride_bio'] ?? ''));
$groomBio = $couplePayload['groom']['bio'] ?? ($couplePayload['groom_bio'] ?? ($defaults['groom_bio'] ?? ''));
$brideSocial = $couplePayload['bride']['social_links'] ?? [];
$groomSocial = $couplePayload['groom']['social_links'] ?? [];

$heroImg = skMediaUrl($mediaByCategory, 'hero');
if (!$heroImg) $heroImg = $couplePayload['hero_image_url'] ?? '';
if (!$heroImg) {
    $heroImg = base_url('templates/sukun/images/hero.jpg');
}

$coupleImg = skMediaUrl($mediaByCategory, 'event');
if (!$coupleImg && !empty($galleryAssets[0]['full'])) $coupleImg = $galleryAssets[0]['full'];

$storyItems = !empty($timelineItems)
    ? $timelineItems
    : ($storyPayload['items'] ?? ($storyPayload['events'] ?? []));
$storyItems = array_values(array_filter($storyItems, 'is_array'));

$galleryList = $galleryAssets;
if (empty($galleryList)) {
    foreach (skAllMedia($mediaByCategory, 'gallery') as $asset) {
        $full = $asset['file_url_original'] ?? ($asset['file_url_large'] ?? '');
        $thumb = $asset['file_url_thumbnail'] ?? $full;
        if ($full && !preg_match('#^https?://#i', $full)) $full = base_url($full);
        if ($thumb && !preg_match('#^https?://#i', $thumb)) $thumb = base_url($thumb);
        $galleryList[] = [
            'full' => $full,
            'thumb' => $thumb,
            'alt' => $asset['alt_text'] ?? $coupleTitle
        ];
    }
}

$venueLocations = $eventLocations;
if (empty($venueLocations) && (!empty($event['venue_name']) || !empty($event['venue_address']))) {
    $venueLocations = [[
        'name' => $event['venue_name'] ?? '',
        'address' => $event['venue_address'] ?? '',
        'maps_url' => '',
        'waze_url' => '',
        'time' => $event['event_date_start'] ? skDateEs($event['event_date_start'], $tz, "d 'de' MMMM, yyyy") : ''
    ]];
}

$parallaxBg = skMediaUrl($mediaByCategory, 'cta_bg') ?: skMediaUrl($mediaByCategory, 'countdown_bg');

$eventDateIso = skDateIso($event['event_date_start'] ?? null, $tz);
$eventDateHuman = skDateEs($event['event_date_start'] ?? null, $tz);
$rsvpUrl = base_url(route_to('rsvp.submit', $slug));

$hasGallery = ($sectionVisibility['gallery'] ?? true) && !empty($galleryList);
$hasStory = ($sectionVisibility['story'] ?? true) && !empty($storyItems);
$hasParty = ($sectionVisibility['party'] ?? true) && !empty($weddingParty);
$hasRegistry = ($sectionVisibility['gifts'] ?? ($sectionVisibility['registry'] ?? true)) && !empty($registryItems);
$hasMenu = ($sectionVisibility['menu'] ?? true) && !empty($menuOptions);
$hasVenues = ($sectionVisibility['event'] ?? ($sectionVisibility['events'] ?? true)) && !empty($venueLocations);
$hasNews = ($sectionVisibility['news'] ?? true) && count($storyItems) >= 3;
$showHero = $sectionVisibility['hero'] ?? true;
$showCouple = $sectionVisibility['couple'] ?? true;
$showRsvp = $sectionVisibility['rsvp'] ?? true;
$showCountdown = $sectionVisibility['countdown'] ?? true;

$sectionLinks = [];
if ($showHero) {
    $sectionLinks['hero'] = 'Inicio';
}
if ($showCouple) {
    $sectionLinks['pareja'] = 'Pareja';
}
if ($hasStory) {
    $sectionLinks['historia'] = 'Historia';
}
if ($hasGallery) {
    $sectionLinks['galeria'] = 'Galería';
}
if ($showRsvp) {
    $sectionLinks['rsvp'] = 'RSVP';
}
if ($hasVenues) {
    $sectionLinks['eventos'] = 'Eventos';
}
if ($hasParty) {
    $sectionLinks['cortejo'] = 'Cortejo';
}
if ($hasRegistry) {
    $sectionLinks['regalos'] = 'Regalos';
}
if ($hasNews) {
    $sectionLinks['noticias'] = 'Noticias';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($coupleTitle) ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('templates/sukun/css/style.css') ?>">

    <style>
        :root {
            --sk-primary: <?= esc($primary) ?>;
            --sk-accent: <?= esc($accent) ?>;
            --sk-bg: <?= esc($bgColor) ?>;
            --sk-bg-alt: #F5F0EB;
            --sk-text: <?= esc($textColor) ?>;
            --sk-muted: <?= esc($mutedColor) ?>;
            --sk-dark-wood: #3D2B1F;
            --sk-card: rgba(255, 255, 255, 0.92);
            --sk-font-script: "<?= esc($fontScript) ?>", cursive;
            --sk-font-serif: "<?= esc($fontSerif) ?>", serif;
            --sk-font-body: "<?= esc($fontBody) ?>", sans-serif;
        }
    </style>

    <script>
        window.SUKUN = {
            eventStartIso: <?= json_encode($eventDateIso) ?>,
            rsvpUrl: <?= json_encode($rsvpUrl) ?>
        };
    </script>
<?php if (!empty($isDemoMode)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/demo-watermark.css') ?>">
<?php endif; ?>
<?= $jsonLdEvent ?? '' ?>
</head>
<body class="sukun">
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-banner">🚀 Evento DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar por $800 MXN</a></div>
<?php endif; ?>

<header class="sk-header" data-header>
    <div class="sk-container sk-header__inner">
        <a class="sk-brand" href="#hero">Feelings</a>
        <nav class="sk-nav" data-nav>
            <?php foreach ($sectionLinks as $id => $label): ?>
                <a href="#<?= esc($id) ?>"><?= esc($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <button class="sk-nav-toggle" type="button" aria-label="Abrir menú" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>
        <div class="sk-cart">
            <span class="sk-cart__icon">🛒</span>
        </div>
    </div>
</header>

<main>
    <?php if ($showHero): ?>
    <section id="hero" class="sk-hero">
        <div class="sk-container sk-hero__grid">
            <div class="sk-hero__content sk-reveal">
                <p class="sk-hero__kicker"><?= esc($heroTagline) ?></p>
                <h1 class="sk-hero__title"><?= esc($coupleTitle) ?></h1>
                <p class="sk-hero__subtitle"><?= esc($heroSubtitle) ?></p>
                <p class="sk-hero__date"><?= esc($eventDateHuman) ?></p>
                <?php if ($showCountdown): ?>
                <div class="sk-countdown" data-countdown="<?= esc($eventDateIso) ?>">
                    <div class="sk-countdown__item">
                        <span data-count="days">00</span>
                        <small>Días</small>
                    </div>
                    <div class="sk-countdown__leaf"></div>
                    <div class="sk-countdown__item">
                        <span data-count="hours">00</span>
                        <small>Horas</small>
                    </div>
                    <div class="sk-countdown__leaf"></div>
                    <div class="sk-countdown__item">
                        <span data-count="minutes">00</span>
                        <small>Min</small>
                    </div>
                    <div class="sk-countdown__leaf"></div>
                    <div class="sk-countdown__item">
                        <span data-count="seconds">00</span>
                        <small>Seg</small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="sk-hero__media sk-reveal">
                <div class="sk-hero__decor sk-hero__decor--tl"></div>
                <div class="sk-hero__decor sk-hero__decor--br"></div>
                <img src="<?= esc($heroImg) ?>" alt="<?= esc($coupleTitle) ?>" class="sk-hero__image">
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($showCouple): ?>
    <section id="pareja" class="sk-section">
        <div class="sk-container">
            <div class="sk-section__header sk-reveal">
                <span class="sk-section__kicker">Bride &amp; Groom</span>
                <h2 class="sk-section__title">Nuestra historia comienza</h2>
                <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                    <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                    <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                    <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                </svg>
            </div>
            <div class="sk-couple">
                <div class="sk-couple__card sk-reveal">
                    <span class="sk-couple__label">Bride</span>
                    <h3 class="sk-couple__name"><?= esc($brideName) ?></h3>
                    <p><?= esc($brideBio) ?></p>
                    <div class="sk-social">
                        <?php foreach ((array)$brideSocial as $link): ?>
                            <?php $url = is_array($link) ? ($link['url'] ?? '') : $link; ?>
                            <?php if ($url): ?><a href="<?= esc($url) ?>" target="_blank" rel="noopener">◎</a><?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="sk-couple__photo sk-reveal">
                    <?php if ($coupleImg): ?>
                        <img src="<?= esc($coupleImg) ?>" alt="<?= esc($coupleTitle) ?>">
                    <?php else: ?>
                        <div class="sk-couple__placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="sk-couple__card sk-reveal">
                    <span class="sk-couple__label">Groom</span>
                    <h3 class="sk-couple__name"><?= esc($groomName) ?></h3>
                    <p><?= esc($groomBio) ?></p>
                    <div class="sk-social">
                        <?php foreach ((array)$groomSocial as $link): ?>
                            <?php $url = is_array($link) ? ($link['url'] ?? '') : $link; ?>
                            <?php if ($url): ?><a href="<?= esc($url) ?>" target="_blank" rel="noopener">◎</a><?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($hasStory): ?>
    <section id="historia" class="sk-section">
        <div class="sk-container">
            <div class="sk-section__header sk-reveal">
                <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                    <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                    <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                    <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                </svg>
                <h2 class="sk-section__title sk-title-script">Our love story</h2>
            </div>

            <?php if ($hasStory): ?>
                <div class="sk-tabs" data-tabs>
                    <div class="sk-tabs__nav" role="tablist">
                        <?php foreach ($storyItems as $index => $item): ?>
                            <?php $tabId = 'story-' . $index; ?>
                            <button class="sk-tab<?= $index === 0 ? ' sk-tab--active' : '' ?>" data-tab="<?= esc($tabId) ?>" role="tab">
                                <?= esc($item['title'] ?? ('Historia ' . ($index + 1))) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="sk-tabs__content">
                        <?php foreach ($storyItems as $index => $item): ?>
                            <?php
                            $tabId = 'story-' . $index;
                            $rawStoryImg = $item['image_url'] ?? ($item['image'] ?? '');
                            $storyImg = trim((string) $rawStoryImg) !== '' ? $rawStoryImg : skMediaUrl($mediaByCategory, 'story', $index);
                            if ($storyImg !== '' && !preg_match('#^https?://#i', $storyImg)) {
                                $storyImg = base_url($storyImg);
                            }
                            $storyTitle = $item['title'] ?? 'Nuestra historia';
                            $storyDate = $item['year'] ?? ($item['date'] ?? '');
                            $storyText = $item['description'] ?? ($item['text'] ?? '');
                            ?>
                            <article class="sk-tab-content<?= $index === 0 ? ' sk-tab-content--active' : '' ?>" data-tab-content="<?= esc($tabId) ?>">
                                <div class="sk-story-card">
                                    <div class="sk-story-card__image">
                                        <?php if ($storyImg): ?>
                                            <img data-src="<?= esc($storyImg) ?>" src="<?= esc($storyImg) ?>" alt="<?= esc($storyTitle) ?>">
                                        <?php else: ?>
                                            <div class="sk-image-placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sk-story-card__body">
                                        <span class="sk-story-icon">✿</span>
                                        <h3><?= esc($storyTitle) ?></h3>
                                        <?php if ($storyDate): ?><p class="sk-muted"><?= esc($storyDate) ?></p><?php endif; ?>
                                        <p><?= esc($storyText) ?></p>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="sk-muted">Próximamente compartiremos nuestra historia.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($sectionVisibility['banner'] ?? ($hasGallery || $hasStory)): ?>
        <div id="banner" class="sk-parallax" style="background-image: <?= $parallaxBg ? "url('" . esc($parallaxBg) . "')" : 'linear-gradient(120deg, #f5efe9, #f0e5da)' ?>;"></div>
    <?php endif; ?>

    <?php if ($hasGallery): ?>
    <section id="galeria" class="sk-section sk-section--alt">
        <div class="sk-container">
            <div class="sk-section__header sk-reveal">
                <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                    <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                    <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                    <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                </svg>
                <h2 class="sk-section__title sk-title-script">Captured Moments</h2>
            </div>
            <?php if ($hasGallery): ?>
                <div class="sk-gallery" data-lightbox>
                    <?php foreach ($galleryList as $index => $asset): ?>
                        <?php
                        $full = $asset['full'] ?? ($asset['url'] ?? '');
                        $thumb = $asset['thumb'] ?? $full;
                        $alt = $asset['alt'] ?? $coupleTitle;
                        $class = 'sk-gallery__item';
                        if ($index === 2) $class .= ' is-tall';
                        if ($index === 4) $class .= ' is-wide';
                        ?>
                        <?php if ($thumb): ?>
                            <figure class="<?= $class ?> sk-reveal" data-lightbox-item data-full="<?= esc($full ?: $thumb) ?>">
                                <img data-src="<?= esc($thumb) ?>" src="<?= esc($thumb) ?>" alt="<?= esc($alt) ?>">
                                <div class="sk-gallery__overlay"></div>
                            </figure>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="sk-muted">Pronto compartiremos nuestras fotos favoritas.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($showRsvp): ?>
    <section id="rsvp" class="sk-section">
        <div class="sk-container">
            <div class="sk-section__header sk-reveal">
                <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                    <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                    <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                    <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                </svg>
                <h2 class="sk-section__title sk-title-script"><?= esc($rsvpPayload['title'] ?? 'Are you attending?') ?></h2>
                <?php if (!empty($rsvpPayload['subtitle'])): ?><p class="sk-muted"><?= esc($rsvpPayload['subtitle']) ?></p><?php endif; ?>
            </div>

            <div class="sk-rsvp-frame sk-reveal">
                <div class="sk-rsvp-flower sk-rsvp-flower--tl"></div>
                <div class="sk-rsvp-flower sk-rsvp-flower--br"></div>
                <form id="rsvp-form" class="sk-rsvp-form" method="post" action="<?= esc($rsvpUrl) ?>">
                    <?= csrf_field() ?>
                    <?php if (!empty($selectedGuest['id'])): ?>
                        <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                        <?php if ($selectedGuestCode !== ''): ?>
                            <input type="hidden" name="guest_code" value="<?= esc($selectedGuestCode) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="sk-form-grid">
                        <input type="text" name="name" placeholder="Nombre*" required value="<?= esc($selectedGuestName) ?>">
                        <input type="email" name="email" placeholder="Email*" required value="<?= esc($selectedGuestEmail) ?>">
                        <select name="attending" required>
                            <option value="" disabled selected>¿Asistirás?</option>
                            <option value="accepted">Sí, asistiré</option>
                            <option value="declined">No podré asistir</option>
                        </select>
                        <select name="guests">
                            <option value="" disabled selected>Número de invitados</option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <?php if ($hasMenu): ?>
                            <select name="meal_option">
                                <option value="" disabled selected>Preferencia de menú</option>
                                <?php foreach ($menuOptions as $option): ?>
                                    <option value="<?= esc($option['id'] ?? $option['name'] ?? '') ?>"><?= esc($option['name'] ?? 'Opción') ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <textarea name="message" rows="3" placeholder="Mensaje para los novios"></textarea>
                    </div>
                    <button type="submit" class="sk-btn" data-submit>Submit now</button>
                    <p class="sk-form-status" data-status></p>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($hasVenues): ?>
    <section id="eventos" class="sk-section sk-section--dark">
        <div class="sk-container">
            <div class="sk-section__header sk-reveal">
                <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                    <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                    <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                    <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                    <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                </svg>
                <h2 class="sk-section__title sk-title-script"><?= esc($venuePayload['title'] ?? 'Wedding Events') ?></h2>
            </div>

            <div class="sk-events">
                <?php foreach ($venueLocations as $index => $location): ?>
                    <?php
                    $locName = $location['name'] ?? ($event['venue_name'] ?? 'Evento');
                    $locAddress = $location['address'] ?? ($event['venue_address'] ?? '');
                    $locTime = $location['time'] ?? $eventDateHuman;
                    $locMap = $location['maps_url'] ?? '';
                    $rawLocImage = $location['image_url'] ?? '';
                    $locImage = trim((string) $rawLocImage) !== '' ? $rawLocImage : skMediaUrl($mediaByCategory, 'event', $index);
                    if ($locImage !== '' && !preg_match('#^https?://#i', $locImage)) {
                        $locImage = base_url($locImage);
                    }
                    ?>
                    <article class="sk-event-card sk-reveal">
                        <div class="sk-event-card__image"<?= $locImage ? ' style="background-image:url(' . esc($locImage) . ')"' : '' ?>></div>
                        <div class="sk-event-card__body">
                            <h3><?= esc($locName) ?></h3>
                            <?php if ($locTime): ?><p><span>📅</span><?= esc($locTime) ?></p><?php endif; ?>
                            <?php if ($locAddress): ?><p><span>📍</span><?= esc($locAddress) ?></p><?php endif; ?>
                            <?php if ($locMap): ?>
                                <a href="<?= esc($locMap) ?>" target="_blank" rel="noopener">See Location</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="sk-logos">
                <?php foreach (array_slice($weddingParty, 0, 5) as $member): ?>
                    <?php
                    $initials = '';
                    $name = $member['full_name'] ?? '';
                    foreach (explode(' ', $name) as $part) {
                        $initials .= mb_substr($part, 0, 1);
                    }
                    ?>
                    <div class="sk-logo">
                        <?= esc($initials ?: '13') ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($hasParty): ?>
        <?php if ($hasParty): ?>
        <section id="cortejo" class="sk-section">
            <div class="sk-container">
                <div class="sk-section__header sk-reveal">
                    <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                        <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                        <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                        <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                        <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                        <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                    </svg>
                    <h2 class="sk-section__title sk-title-script">Wedding Party</h2>
                </div>
                <div class="sk-party">
                    <?php foreach ($weddingParty as $member): ?>
                        <div class="sk-party__card sk-reveal">
                            <?php if (!empty($member['image_url'])): ?>
                                <img data-src="<?= esc($member['image_url']) ?>" src="<?= esc($member['image_url']) ?>" alt="<?= esc($member['full_name'] ?? '') ?>">
                            <?php else: ?>
                                <div class="sk-party__placeholder"></div>
                            <?php endif; ?>
                            <h3><?= esc($member['full_name'] ?? '') ?></h3>
                            <?php if (!empty($member['role'])): ?><p class="sk-muted"><?= esc($member['role']) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasRegistry): ?>
        <?php if ($hasRegistry): ?>
        <section id="regalos" class="sk-section sk-section--alt">
            <div class="sk-container">
                <div class="sk-section__header sk-reveal">
                    <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                        <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                        <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                        <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                        <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                        <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                    </svg>
                    <h2 class="sk-section__title sk-title-script">Mesa de regalos</h2>
                </div>
                <div class="sk-registry">
                    <?php foreach ($registryItems as $item): ?>
                        <?php
                        $price = $item['price'] ?? null;
                        $goal = $item['goal_amount'] ?? null;
                        $current = $item['current_amount'] ?? null;
                        $progress = 0;
                        if ($goal && $current) {
                            $progress = min(100, ($current / $goal) * 100);
                        }
                        ?>
                        <div class="sk-registry__card sk-reveal">
                            <?php if (!empty($item['image_url'])): ?>
                                <img data-src="<?= esc($item['image_url']) ?>" src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title'] ?? $item['name'] ?? '') ?>">
                            <?php else: ?>
                                <div class="sk-registry__placeholder"></div>
                            <?php endif; ?>
                            <h3><?= esc($item['title'] ?? $item['name'] ?? 'Regalo') ?></h3>
                            <?php if (!empty($item['description'])): ?><p><?= esc($item['description']) ?></p><?php endif; ?>
                            <?php if ($price): ?><p class="sk-muted">$<?= number_format((float)$price, 2) ?> <?= esc($item['currency_code'] ?? 'MXN') ?></p><?php endif; ?>
                            <?php if ($goal): ?>
                                <div class="sk-progress">
                                    <span style="width: <?= esc((string)$progress) ?>%"></span>
                                </div>
                                <small class="sk-muted"><?= esc(number_format((float)$current, 2)) ?> / <?= esc(number_format((float)$goal, 2)) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($item['external_url'])): ?>
                                <a href="<?= esc($item['external_url']) ?>" target="_blank" rel="noopener" class="sk-link">Ver regalo</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasNews): ?>
        <?php if ($hasNews): ?>
        <section id="noticias" class="sk-section">
            <div class="sk-container">
                <div class="sk-section__header sk-reveal">
                    <svg class="sk-floral-icon" width="40" height="30" viewBox="0 0 40 30" aria-hidden="true">
                        <path d="M20 28 C20 20, 10 15, 5 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                        <ellipse cx="5" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(-30 5 4)"/>
                        <path d="M20 28 C20 20, 30 15, 35 5" stroke="currentColor" fill="none" stroke-width="1.2"/>
                        <ellipse cx="35" cy="4" rx="4" ry="7" fill="currentColor" opacity="0.3" transform="rotate(30 35 4)"/>
                        <ellipse cx="20" cy="12" rx="3" ry="5" fill="currentColor" opacity="0.4"/>
                    </svg>
                    <h2 class="sk-section__title sk-title-script">Latest News</h2>
                </div>
                <div class="sk-news">
                    <?php foreach (array_slice($storyItems, 0, 3) as $item): ?>
                        <?php
                        $newsImg = $item['image_url'] ?? ($item['image'] ?? '');
                        if ($newsImg !== '' && !preg_match('#^https?://#i', $newsImg)) {
                            $newsImg = base_url($newsImg);
                        }
                        $newsTitle = $item['title'] ?? 'Historia';
                        $newsDate = $item['year'] ?? ($item['date'] ?? '');
                        $newsText = $item['description'] ?? ($item['text'] ?? '');
                        ?>
                        <article class="sk-news__card sk-reveal">
                            <div class="sk-news__image">
                                <?php if ($newsImg): ?>
                                    <img data-src="<?= esc($newsImg) ?>" src="<?= esc($newsImg) ?>" alt="<?= esc($newsTitle) ?>">
                                    <span class="sk-news__tag">Historia</span>
                                <?php else: ?>
                                    <div class="sk-news__placeholder"></div>
                                <?php endif; ?>
                            </div>
                            <div class="sk-news__body">
                                <p class="sk-muted">By 13Bodas<?= $newsDate ? ' · ' . esc($newsDate) : '' ?></p>
                                <h3><?= esc($newsTitle) ?></h3>
                                <p><?= esc($newsText) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer class="sk-footer">
    <div class="sk-container sk-footer__grid">
        <div>
            <h3 class="sk-footer__logo"><?= esc($coupleTitle) ?></h3>
            <p><?= esc($heroSubtitle) ?></p>
        </div>
        <div>
            <h4>Información</h4>
            <?php foreach ($sectionLinks as $id => $label): ?>
                <?php if ($id === 'historia' && !$hasStory) continue; ?>
                <?php if ($id === 'galeria' && !$hasGallery) continue; ?>
                <?php if ($id === 'eventos' && !$hasVenues) continue; ?>
                <?php if ($id === 'cortejo' && !$hasParty) continue; ?>
                <?php if ($id === 'regalos' && !$hasRegistry) continue; ?>
                <?php if ($id === 'noticias' && !$hasNews) continue; ?>
                <a href="#<?= esc($id) ?>"><?= esc($label) ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h4>Contacto</h4>
            <?php if (!empty($event['venue_name'])): ?><span><?= esc($event['venue_name']) ?></span><?php endif; ?>
            <?php if (!empty($event['venue_address'])): ?><span><?= esc($event['venue_address']) ?></span><?php endif; ?>
            <?php if (!empty($event['rsvp_deadline'])): ?><span>RSVP: <?= esc(skDateEs($event['rsvp_deadline'], $tz)) ?></span><?php endif; ?>
        </div>
        <div>
            <h4>Galería</h4>
            <div class="sk-footer__gallery">
                <?php foreach (array_slice($galleryList, 0, 6) as $asset): ?>
                    <?php $thumb = $asset['thumb'] ?? ($asset['full'] ?? ''); ?>
                    <?php if ($thumb): ?><img data-src="<?= esc($thumb) ?>" src="<?= esc($thumb) ?>" alt="Galería"><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="sk-footer__bar">© <?= date('Y') ?> 13Bodas</div>
</footer>

<div class="sk-lightbox" data-lightbox-overlay aria-hidden="true">
    <button class="sk-lightbox__close" type="button" data-lightbox-close aria-label="Cerrar">×</button>
    <button class="sk-lightbox__nav sk-lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Anterior">‹</button>
    <img src="" alt="" class="sk-lightbox__image" data-lightbox-image>
    <button class="sk-lightbox__nav sk-lightbox__nav--next" type="button" data-lightbox-next aria-label="Siguiente">›</button>
</div>

<script src="<?= base_url('templates/sukun/js/main.js') ?>" defer></script>
<?php if (!empty($isDemoMode)): ?>
    <div class="demo-watermark">DEMO · <a class="text-warning" href="<?= base_url('checkout/' . ($event['id'] ?? '')) ?>">Activar</a></div>
<?php endif; ?>
</body>
</html>
