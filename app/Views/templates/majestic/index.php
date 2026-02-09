<?php

declare(strict_types=1);

$event = $event ?? [];
$modules = $modules ?? [];
$templateMeta = $templateMeta ?? [];
$theme = $theme ?? [];
$mediaByCategory = $mediaByCategory ?? [];
$eventLocations = $eventLocations ?? [];
$scheduleItems = $scheduleItems ?? ($event['schedule_items'] ?? []);
$galleryAssets = $galleryAssets ?? ($event['gallery_items'] ?? []);
$registryItems = $registryItems ?? ($event['registry_items'] ?? []);
$weddingParty = $weddingParty ?? ($event['party_members'] ?? []);
$sectionVisibility = $theme['sections'] ?? ($templateMeta['section_visibility'] ?? []);
$showEventSection = $sectionVisibility['event'] ?? ($sectionVisibility['events'] ?? true);
$showGiftsSection = $sectionVisibility['gifts'] ?? ($sectionVisibility['registry'] ?? true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc($event['meta_description'] ?? 'Invitación de boda - ' . $event['couple_title']) ?>">
    <meta name="keywords" content="boda, invitación, <?= esc($event['couple_title']) ?>">
    <meta property="og:title" content="<?= esc($event['couple_title']) ?>">
    <meta property="og:description" content="<?= esc($event['meta_description'] ?? 'Te invitamos a nuestra boda') ?>">
    <meta property="og:image" content="<?= esc($event['hero_image'] ?? '') ?>">
    <meta property="og:type" content="website">
    
    <title><?= esc($event['couple_title']) ?> | <?= date('d/m/Y', strtotime($event['event_date_start'])) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="<?= base_url('templates/majestic/css/style.css') ?>">
    
    <!-- Dynamic Theme Variables -->
    <style>
        :root {
            <?php 
            $colors = $theme['colors'] ?? [];
            $fonts = $theme['fonts'] ?? [];
            ?>
            --primary-color: <?= $colors['primary'] ?? '#8B7355' ?>;
            --secondary-color: <?= $colors['secondary'] ?? '#D4AF37' ?>;
            --accent-color: <?= $colors['accent'] ?? '#C9A97E' ?>;
            --text-dark: <?= $colors['text_dark'] ?? '#2C2C2C' ?>;
            --text-light: <?= $colors['text_light'] ?? '#FFFFFF' ?>;
            --background: <?= $colors['background'] ?? '#FAF9F6' ?>;
            --background-secondary: <?= $colors['background_secondary'] ?? '#F5F3EE' ?>;
            
            --font-heading: '<?= $fonts['heading'] ?? 'Cormorant Garamond' ?>', serif;
            --font-body: '<?= $fonts['body'] ?? 'Montserrat' ?>', sans-serif;
            --font-accent: '<?= $fonts['accent'] ?? 'Great Vibes' ?>', cursive;
        }
</style>
</head>
<body>
    <!-- Hero Section -->
    <?php if ($sectionVisibility['hero'] ?? true): ?>
        <?= $this->include('templates/majestic/sections/hero') ?>
    <?php endif; ?>
    
    <!-- Countdown Section -->
    <?php if ($sectionVisibility['countdown'] ?? true): ?>
        <?= $this->include('templates/majestic/sections/countdown') ?>
    <?php endif; ?>
    
    <!-- Story Section -->
    <?php if (($sectionVisibility['story'] ?? true) && !empty($modules)): ?>
        <?= $this->include('templates/majestic/sections/story') ?>
    <?php endif; ?>
    
    <!-- Schedule Section -->
    <?php if ($showEventSection && !empty($scheduleItems)): ?>
        <?= $this->include('templates/majestic/sections/schedule') ?>
    <?php endif; ?>
    
    <!-- Location Section -->
    <?php if ($sectionVisibility['location'] ?? true): ?>
        <?= $this->include('templates/majestic/sections/location') ?>
    <?php endif; ?>
    
    <!-- Gallery Section -->
    <?php if (($sectionVisibility['gallery'] ?? true) && !empty($galleryAssets)): ?>
        <?= $this->include('templates/majestic/sections/gallery') ?>
    <?php endif; ?>
    
    <!-- Registry Section -->
    <?php if ($showGiftsSection && !empty($registryItems)): ?>
        <?= $this->include('templates/majestic/sections/registry') ?>
    <?php endif; ?>
    
    <!-- Party Section -->
    <?php if (($sectionVisibility['party'] ?? true) && !empty($weddingParty)): ?>
        <?= $this->include('templates/majestic/sections/party') ?>
    <?php endif; ?>
    
    <!-- RSVP Section -->
    <?php if ($sectionVisibility['rsvp'] ?? true): ?>
        <?= $this->include('templates/majestic/sections/rsvp') ?>
    <?php endif; ?>
    
    <!-- FAQ Section -->
    <?php if (($sectionVisibility['faq'] ?? true) && !empty($event['faqs'])): ?>
        <?= $this->include('templates/majestic/sections/faq') ?>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="majestic-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= esc($event['couple_title']) ?>. Creado con 13Bodas.com</p>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php
    $primaryLocation = $eventLocations[0] ?? [];
    $venueLat = $primaryLocation['geo_lat'] ?? ($event['venue_geo_lat'] ?? 0);
    $venueLng = $primaryLocation['geo_lng'] ?? ($event['venue_geo_lng'] ?? 0);
    ?>
    <script>
        const EVENT_DATA = {
            eventDate: '<?= $event['event_date_start'] ?>',
            venueLat: <?= $venueLat ?: 0 ?>,
            venueLng: <?= $venueLng ?: 0 ?>,
            venueName: '<?= esc($primaryLocation['name'] ?? ($event['venue_name'] ?? '')) ?>',
            eventId: '<?= $event['id'] ?>'
        };
    </script>
    <script src="<?= base_url('templates/majestic/js/main.js') ?>"></script>
</body>
</html>
