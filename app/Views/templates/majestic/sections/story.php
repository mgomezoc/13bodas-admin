<section class="majestic-story" id="story">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Nuestra Historia</h2>
        <div class="story-timeline">
            <?php
            $storyItems = [];
            if (!empty($timelineItems)) {
                $storyItems = $timelineItems;
            } else {
                foreach ($modules ?? [] as $module) {
                    if (!in_array($module['module_type'] ?? '', ['story', 'timeline'], true)) {
                        continue;
                    }
                    $payload = $module['content_payload'] ?? [];
                    if (is_string($payload)) {
                        $payload = json_decode($payload, true) ?: [];
                    }
                    if (!is_array($payload)) {
                        continue;
                    }
                    $items = $payload['items'] ?? ($payload['events'] ?? []);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (is_array($item)) {
                                $storyItems[] = $item;
                            }
                        }
                    }
                }
            }
            ?>
            <?php foreach ($storyItems as $index => $item): ?>
                <?php
                $rawImage = $item['image_url'] ?? ($item['image'] ?? '');
                $storyImage = trim((string) $rawImage);
                if ($storyImage === '') {
                    $mediaItem = $mediaByCategory['story'][$index] ?? [];
                    $storyImage = $mediaItem['file_url_large'] ?? ($mediaItem['file_url_thumbnail'] ?? ($mediaItem['file_url_original'] ?? ''));
                }
                if ($storyImage !== '' && !preg_match('#^https?://#i', $storyImage)) {
                    $storyImage = base_url($storyImage);
                }
                $storyTitle = $item['title'] ?? 'Momento especial';
                $storyDate = $item['year'] ?? ($item['date'] ?? '');
                $storyText = $item['description'] ?? ($item['text'] ?? '');
                ?>
                <div class="story-item" data-aos="fade-<?= $index % 2 == 0 ? 'right' : 'left' ?>" data-aos-delay="<?= $index * 100 ?>">
                    <?php if (!empty($storyImage)): ?>
                        <div class="story-image">
                            <img src="<?= esc($storyImage) ?>" alt="<?= esc($storyTitle) ?>">
                        </div>
                    <?php endif; ?>
                    <div class="story-content">
                        <h3 class="story-title"><?= esc($storyTitle) ?></h3>
                        <?php if (!empty($storyDate)): ?>
                            <p class="story-date"><?= esc($storyDate) ?></p>
                        <?php endif; ?>
                        <div class="story-text">
                            <?= esc($storyText) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
