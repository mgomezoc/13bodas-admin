<section class="majestic-hero" id="hero" style="background-image: url('<?= esc($event['hero_image'] ?? base_url('assets/images/default-hero.jpg')) ?>');">
    <div class="hero-overlay"></div>
    <div class="hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1 class="hero-names"><?= esc($event['couple_title']) ?></h1>
        <div class="hero-divider"></div>
        <p class="hero-date">
            <?= strftime('%d de %B de %Y', strtotime($event['event_date_start'])) ?>
        </p>
        <?php if (!empty($event['venue_name'])): ?>
            <p class="hero-venue">
                <i class="bi bi-geo-alt"></i> <?= esc($event['venue_name']) ?>
            </p>
        <?php endif; ?>
        <a href="#rsvp" class="hero-cta">
            Confirmar Asistencia
            <i class="bi bi-arrow-down"></i>
        </a>
    </div>
    <div class="hero-scroll-indicator">
        <i class="bi bi-chevron-down"></i>
    </div>
</section>
