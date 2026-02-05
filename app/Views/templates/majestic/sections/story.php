<section class="majestic-story" id="story">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Nuestra Historia</h2>
        <div class="story-timeline">
            <?php 
            $storyModules = array_filter($event['content_modules'] ?? [], function($module) {
                return in_array($module['module_type'], ['story', 'timeline']);
            });
            foreach ($storyModules as $index => $module): 
            ?>
                <div class="story-item" data-aos="fade-<?= $index % 2 == 0 ? 'right' : 'left' ?>" data-aos-delay="<?= $index * 100 ?>">
                    <?php if (!empty($module['image_url'])): ?>
                        <div class="story-image">
                            <img src="<?= esc($module['image_url']) ?>" alt="<?= esc($module['title']) ?>">
                        </div>
                    <?php endif; ?>
                    <div class="story-content">
                        <h3 class="story-title"><?= esc($module['title']) ?></h3>
                        <?php if (!empty($module['date'])): ?>
                            <p class="story-date"><?= date('F Y', strtotime($module['date'])) ?></p>
                        <?php endif; ?>
                        <div class="story-text">
                            <?= $module['content'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
