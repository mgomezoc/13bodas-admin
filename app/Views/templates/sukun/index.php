<?php

/** @var array $event */
/** @var array $modules */
/** @var array $theme */
/** @var array $template */

$themeArr = $theme ?? [];
if (is_string($themeArr)) {
    $tmp = json_decode($themeArr, true);
    $themeArr = is_array($tmp) ? $tmp : [];
}

$primary   = $themeArr['primary']   ?? '#8B6F8E';
$secondary = $themeArr['secondary'] ?? '#D7C6D9';
$bg        = $themeArr['bg']        ?? '#FBF8F4';
$text      = $themeArr['text']      ?? '#2B2B2B';
$muted     = $themeArr['muted']     ?? '#6E6A6A';

$fontHead  = $themeArr['font_head'] ?? 'Cormorant Garamond';
$fontBody  = $themeArr['font_body'] ?? 'Inter';

$tz = $event['time_zone'] ?? 'America/Mexico_City';

function payload(array $m): array
{
    $p = $m['content_payload'] ?? [];
    if (is_string($p)) {
        $tmp = json_decode($p, true);
        $p = is_array($tmp) ? $tmp : [];
    }
    return is_array($p) ? $p : [];
}

$mods = $modules ?? [];
usort($mods, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

$byCss = [];
foreach ($mods as $m) {
    if (!empty($m['is_enabled']) && !empty($m['css_id'])) {
        $byCss[$m['css_id']] = $m;
    }
}

$heroM = $byCss['hero'] ?? null;
$heroP = $heroM ? payload($heroM) : [];

$eventStart = $event['event_date_start'] ?? null;
$eventStartIso = null;
if ($eventStart) {
    try {
        $dt = new DateTime($eventStart, new DateTimeZone($tz));
        $eventStartIso = $dt->format(DATE_ATOM);
    } catch (Exception $e) {
        $eventStartIso = null;
    }
}

function formatDateEs(?string $dtStr, string $tz): string
{
    if (!$dtStr) return '';
    try {
        $dt = new DateTime($dtStr, new DateTimeZone($tz));
        if (class_exists(\IntlDateFormatter::class)) {
            $fmt = new \IntlDateFormatter('es_MX', \IntlDateFormatter::LONG, \IntlDateFormatter::SHORT, $tz);
            $fmt->setPattern("d 'de' MMMM 'de' y · HH:mm");
            return (string)$fmt->format($dt);
        }
        return $dt->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $dtStr;
    }
}

$rsvpUrl = site_url('i/' . ($event['slug'] ?? '') . '/rsvp');
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= esc($event['couple_title'] ?? 'Invitación') ?> | 13Bodas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tu CSS compilado (vendor) -->
    <link rel="stylesheet" href="<?= base_url('templates/sukun/css/vendor.css') ?>">
    <!-- Estilo SUKUN controlado -->
    <link rel="stylesheet" href="<?= base_url('templates/sukun/css/style.css') ?>">

    <style>
        :root {
            --primary: <?= esc($primary) ?>;
            --secondary: <?= esc($secondary) ?>;
            --bg: <?= esc($bg) ?>;
            --text: <?= esc($text) ?>;
            --muted: <?= esc($muted) ?>;
            --font-head: "<?= esc($fontHead) ?>", serif;
            --font-body: "<?= esc($fontBody) ?>", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            --hero-image: url("<?= esc($heroP['hero_image_url'] ?? '/templates/sukun/img/hero.jpg') ?>");
        }
    </style>

    <script>
        window.SUKUN = {
            eventStartIso: <?= json_encode($eventStartIso) ?>,
            rsvpUrl: <?= json_encode($rsvpUrl) ?>
        };
    </script>
</head>

<body>

    <header class="sukun-nav">
        <div class="container-sukun sukun-nav__inner">
            <div class="brand">13BODAS</div>

            <nav class="nav-links" aria-label="Navegación">
                <?php if (isset($byCss['historia'])): ?><a href="#historia">Historia</a><?php endif; ?>
                <?php if (isset($byCss['countdown'])): ?><a href="#countdown">Cuenta regresiva</a><?php endif; ?>
                <?php if (isset($byCss['lugar'])): ?><a href="#lugar">Lugar</a><?php endif; ?>
                <a href="#rsvp">RSVP</a>
            </nav>

            <a class="nav-cta" href="#rsvp">RSVP</a>
        </div>
    </header>

    <main>

        <!-- HERO -->
        <section class="hero" id="top">
            <div class="container-sukun">
                <div class="hero__bg" aria-hidden="true"></div>

                <div class="hero__card">
                    <div class="hero__kicker"><?= esc($heroP['headline'] ?? '¡Nos casamos!') ?></div>

                    <h1 class="hero__title"><?= esc($event['couple_title'] ?? '') ?></h1>

                    <div class="hero__meta">
                        <?= esc(formatDateEs($event['event_date_start'] ?? null, $tz)) ?>
                        <?php if (!empty($event['venue_name'])): ?>
                            · <?= esc($event['venue_name']) ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($heroP['subheadline'])): ?>
                        <p style="margin:12px 0 0; color:var(--muted)"><?= esc($heroP['subheadline']) ?></p>
                    <?php endif; ?>

                    <a class="btn-primary" href="<?= esc($heroP['cta_target'] ?? '#rsvp') ?>">
                        <?= esc($heroP['cta_label'] ?? 'Confirmar asistencia') ?>
                    </a>
                </div>
            </div>
        </section>

        <!-- HISTORIA -->
        <?php if (isset($byCss['historia'])):
            $p = payload($byCss['historia']);
            $items = $p['items'] ?? [];
        ?>
            <section class="section" id="historia">
                <div class="container-sukun">
                    <h2><?= esc($byCss['historia']['title'] ?? 'Nuestra historia') ?></h2>

                    <div class="timeline">
                        <?php foreach ($items as $it): ?>
                            <div class="timeline__item">
                                <div class="timeline__dot" aria-hidden="true"></div>
                                <div class="timeline__year"><?= esc($it['year'] ?? '') ?></div>
                                <div>
                                    <p class="timeline__title"><?= esc($it['title'] ?? '') ?></p>
                                    <p class="timeline__text"><?= esc($it['text'] ?? '') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </section>
        <?php endif; ?>

        <!-- COUNTDOWN -->
        <?php if (isset($byCss['countdown'])): ?>
            <section class="section" id="countdown">
                <div class="container-sukun">
                    <h2><?= esc($byCss['countdown']['title'] ?? 'Cuenta regresiva') ?></h2>
                    <?php if (!empty($byCss['countdown']['subtitle'])): ?>
                        <p class="lead"><?= esc($byCss['countdown']['subtitle']) ?></p>
                    <?php endif; ?>

                    <div class="countdown" role="group" aria-label="Cuenta regresiva">
                        <div class="counter">
                            <p class="counter__num" id="cd-days">0</p>
                            <p class="counter__label">Días</p>
                        </div>
                        <div class="counter">
                            <p class="counter__num" id="cd-hours">00</p>
                            <p class="counter__label">Horas</p>
                        </div>
                        <div class="counter">
                            <p class="counter__num" id="cd-mins">00</p>
                            <p class="counter__label">Min</p>
                        </div>
                        <div class="counter">
                            <p class="counter__num" id="cd-secs">00</p>
                            <p class="counter__label">Seg</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- LUGAR -->
        <?php if (isset($byCss['lugar'])):
            $p = payload($byCss['lugar']);
            $mapUrl = $p['map_url'] ?? null;
            $mapLabel = $p['map_label'] ?? 'Ver mapa';
        ?>
            <section class="section" id="lugar">
                <div class="container-sukun">
                    <h2><?= esc($byCss['lugar']['title'] ?? 'Lugar') ?></h2>

                    <div class="venue">
                        <div class="card venue__meta">
                            <p style="font-weight:600; margin:0 0 6px"><?= esc($event['venue_name'] ?? '') ?></p>
                            <p><?= esc($event['venue_address'] ?? '') ?></p>

                            <?php if ($mapUrl): ?>
                                <div class="venue__actions" style="margin-top:12px">
                                    <a href="<?= esc($mapUrl) ?>" target="_blank" rel="noopener noreferrer">
                                        <?= esc($mapLabel) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card">
                            <p style="margin:0; color:var(--muted)">
                                Código de vestimenta: <strong><?= esc(($event['venue_config']['dress_code'] ?? '') ?: '—') ?></strong><br>
                                <?= esc(($event['venue_config']['notes'] ?? '') ?: '') ?>
                            </p>
                        </div>
                    </div>

                </div>
            </section>
        <?php endif; ?>

        <!-- RSVP -->
        <section class="section rsvp" id="rsvp">
            <div class="container-sukun">
                <h2>RSVP</h2>
                <p class="lead">Confirma tu asistencia para ayudarnos a organizar mejor este día.</p>

                <div class="card">
                    <form id="rsvp-form" method="post" action="<?= esc($rsvpUrl) ?>">
                        <?= csrf_field() ?>

                        <div class="input">
                            <label for="name">Nombre completo</label>
                            <input id="name" name="name" type="text" maxlength="120" required>
                        </div>

                        <div class="input">
                            <label for="email">Correo (opcional)</label>
                            <input id="email" name="email" type="email" maxlength="120">
                        </div>

                        <div class="input">
                            <label for="attending">¿Asistirás?</label>
                            <select id="attending" name="attending" required>
                                <option value="">Selecciona</option>
                                <option value="accepted">Sí, asistiré</option>
                                <option value="declined">No podré asistir</option>
                            </select>
                        </div>

                        <div class="input">
                            <label for="message">Mensaje (opcional)</label>
                            <textarea id="message" name="message" maxlength="500"></textarea>
                        </div>

                        <button class="btn-submit" id="rsvp-submit" type="submit">Enviar confirmación</button>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        © <?= date('Y') ?> 13Bodas
    </footer>

    <script src="<?= base_url('templates/sukun/js/main.js') ?>"></script>
</body>

</html>