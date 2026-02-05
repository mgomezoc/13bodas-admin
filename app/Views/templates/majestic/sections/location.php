<section class="majestic-location" id="location">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Ubicación</h2>
        
        <div class="location-info" data-aos="fade-up" data-aos-delay="200">
            <div class="location-details">
                <h3 class="location-venue"><?= esc($event['venue_name']) ?></h3>
                <p class="location-address">
                    <i class="bi bi-geo-alt-fill"></i>
                    <?= esc($event['venue_address']) ?>
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
                
                <?php if (!empty($event['venue_geo_lat']) && !empty($event['venue_geo_lng'])): ?>
                    <a href="https://www.google.com/maps?q=<?= $event['venue_geo_lat'] ?>,<?= $event['venue_geo_lng'] ?>" 
                       target="_blank" 
                       class="location-directions">
                        <i class="bi bi-compass"></i>
                        Cómo Llegar
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($event['venue_geo_lat']) && !empty($event['venue_geo_lng'])): ?>
                <div class="location-map">
                    <div id="venueMap" style="height: 400px; border-radius: 12px; overflow: hidden;"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
