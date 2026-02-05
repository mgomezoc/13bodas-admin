<section class="majestic-party" id="party">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Nuestro Cortejo</h2>
        <div class="party-grid">
            <?php foreach ($event['party_members'] as $index => $member): ?>
                <div class="party-card" data-aos="fade-up" data-aos-delay="<?= ($index % 4) * 100 ?>">
                    <div class="party-image">
                        <?php if (!empty($member['photo_url'])): ?>
                            <img src="<?= esc($member['photo_url']) ?>" alt="<?= esc($member['name']) ?>">
                        <?php else: ?>
                            <div class="party-placeholder">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="party-info">
                        <h3 class="party-name"><?= esc($member['name']) ?></h3>
                        <p class="party-role"><?= esc($member['role']) ?></p>
                        <?php if (!empty($member['relationship'])): ?>
                            <p class="party-relationship"><?= esc($member['relationship']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
