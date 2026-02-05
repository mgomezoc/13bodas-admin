<section class="majestic-schedule" id="schedule">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Itinerario del Día</h2>
        <div class="schedule-timeline">
            <?php 
            $sortedSchedule = $event['schedule_items'] ?? [];
            usort($sortedSchedule, function($a, $b) {
                return strtotime($a['time_start']) - strtotime($b['time_start']);
            });
            foreach ($sortedSchedule as $index => $item): 
            ?>
                <div class="schedule-item" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="schedule-time">
                        <i class="bi bi-clock"></i>
                        <span><?= date('H:i', strtotime($item['time_start'])) ?></span>
                    </div>
                    <div class="schedule-content">
                        <h3 class="schedule-title"><?= esc($item['title']) ?></h3>
                        <?php if (!empty($item['description'])): ?>
                            <p class="schedule-description"><?= esc($item['description']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['location'])): ?>
                            <p class="schedule-location">
                                <i class="bi bi-geo-alt"></i> <?= esc($item['location']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
