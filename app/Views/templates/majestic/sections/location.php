<section class="majestic-location" id="location">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Ubicación</h2>
        
        <?php
        $primaryLocation = $eventLocations[0] ?? [];
        $venueName = $primaryLocation['name'] ?? ($event['venue_name'] ?? '');
        $venueAddress = $primaryLocation['address'] ?? ($event['venue_address'] ?? '');
        $venueLat = $primaryLocation['geo_lat'] ?? ($event['venue_geo_lat'] ?? '');
        $venueLng = $primaryLocation['geo_lng'] ?? ($event['venue_geo_lng'] ?? '');
        $venueImage = $primaryLocation['image_url'] ?? '';
        if ($venueImage !== '' && !preg_match('#^https?://#i', $venueImage)) {
            $venueImage = base_url($venueImage);
        }
        ?>
        <div class="location-info" data-aos="fade-up" data-aos-delay="200">
            <div class="location-details">
                <h3 class="location-venue"><?= esc($venueName) ?></h3>
                <p class="location-address">
                    <i class="bi bi-geo-alt-fill"></i>
                    <?= esc($venueAddress) ?>
                    <?php if (!empty($event['venue_city'])): ?>
                        <br><?= esc($event['venue_city']) ?>, <?= esc($event['venue_state']) ?>
                    <?php endif; ?>
                </p>
                
                <?php if (!empty($event['venue_phone'])): ?>
                    <p class="location-phone">
                        <i class="bi bi-telephone-fill"></i>
                        <a href="tel:<?= esc($event['venue_phone']) ?>"><?= esc($event['venue_phone']) ?></a>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($event['venue_website'])): ?>
                    <p class="location-website">
                        <i class="bi bi-globe"></i>
                        <a href="<?= esc($event['venue_website']) ?>" target="_blank" rel="noopener">Sitio Web</a>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($venueLat) && !empty($venueLng)): ?>
                    <a href="https://www.google.com/maps?q=<?= esc($venueLat) ?>,<?= esc($venueLng) ?>" 
                       target="_blank" 
                       class="location-directions">
                        <i class="bi bi-compass"></i>
                        Cómo Llegar
                    </a>
                <?php endif; ?>
                <?php if (!empty($primaryLocation['maps_url'])): ?>
                    <a href="<?= esc($primaryLocation['maps_url']) ?>" target="_blank" rel="noopener" class="location-directions">
                        <i class="bi bi-map"></i>
                        Google Maps
                    </a>
                <?php endif; ?>
                <?php if (!empty($primaryLocation['waze_url'])): ?>
                    <a href="<?= esc($primaryLocation['waze_url']) ?>" target="_blank" rel="noopener" class="location-directions">
                        <i class="bi bi-signpost-2"></i>
                        Waze
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($venueImage) || (!empty($venueLat) && !empty($venueLng))): ?>
                <div class="location-map">
                    <?php if (!empty($venueImage)): ?>
                        <img src="<?= esc($venueImage) ?>" alt="<?= esc($venueName) ?>" style="width:100%;height:400px;object-fit:cover;border-radius:12px;">
                    <?php else: ?>
                        <div id="venueMap" style="height: 400px; border-radius: 12px; overflow: hidden;"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
