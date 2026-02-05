<section class="majestic-gallery" id="gallery">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Galería</h2>
        <div class="gallery-grid">
            <?php foreach ($event['gallery_items'] as $index => $item): ?>
                <div class="gallery-item" data-aos="zoom-in" data-aos-delay="<?= ($index % 6) * 100 ?>">
                    <img src="<?= esc($item['image_url']) ?>" 
                         alt="<?= esc($item['caption'] ?? 'Foto ' . ($index + 1)) ?>"
                         loading="lazy"
                         onclick="openLightbox(<?= $index ?>)">
                    <?php if (!empty($item['caption'])): ?>
                        <div class="gallery-caption"><?= esc($item['caption']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Lightbox -->
    <div id="galleryLightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightboxImg">
        <div class="lightbox-caption" id="lightboxCaption"></div>
        <a class="lightbox-prev" onclick="changeLightboxImage(-1); event.stopPropagation();">&#10094;</a>
        <a class="lightbox-next" onclick="changeLightboxImage(1); event.stopPropagation();">&#10095;</a>
    </div>
</section>

<script>
let currentLightboxIndex = 0;
const galleryImages = <?= json_encode(array_map(function($item) {
    return [
        'url' => $item['image_url'],
        'caption' => $item['caption'] ?? ''
    ];
}, $event['gallery_items'])) ?>;

function openLightbox(index) {
    currentLightboxIndex = index;
    document.getElementById('galleryLightbox').style.display = 'block';
    document.getElementById('lightboxImg').src = galleryImages[index].url;
    document.getElementById('lightboxCaption').textContent = galleryImages[index].caption;
}

function closeLightbox() {
    document.getElementById('galleryLightbox').style.display = 'none';
}

function changeLightboxImage(direction) {
    currentLightboxIndex += direction;
    if (currentLightboxIndex >= galleryImages.length) currentLightboxIndex = 0;
    if (currentLightboxIndex < 0) currentLightboxIndex = galleryImages.length - 1;
    
    document.getElementById('lightboxImg').src = galleryImages[currentLightboxIndex].url;
    document.getElementById('lightboxCaption').textContent = galleryImages[currentLightboxIndex].caption;
}
</script>
