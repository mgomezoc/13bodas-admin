<section class="majestic-schedule" id="schedule">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Itinerario del Día</h2>
        <div class="schedule-timeline">
            <?php
            $sortedSchedule = $scheduleItems ?? ($event['schedule_items'] ?? []);
            $locationMap = [];
            foreach ($eventLocations ?? [] as $location) {
                if (!empty($location['id'])) {
                    $locationMap[$location['id']] = $location['name'] ?? '';
                }
            }

            usort($sortedSchedule, function (array $a, array $b): int {
                $startA = $a['starts_at'] ?? ($a['time_start'] ?? null);
                $startB = $b['starts_at'] ?? ($b['time_start'] ?? null);
                $timeA = $startA ? strtotime((string) $startA) : 0;
                $timeB = $startB ? strtotime((string) $startB) : 0;
                return $timeA <=> $timeB;
            });

            foreach ($sortedSchedule as $index => $item):
                $startRaw = $item['starts_at'] ?? ($item['time_start'] ?? null);
                $endRaw = $item['ends_at'] ?? ($item['time_end'] ?? null);
                $title = $item['title'] ?? 'Actividad';
                $description = $item['description'] ?? '';
                $locationName = $item['location'] ?? ($item['location_name'] ?? '');
                if ($locationName === '' && !empty($item['location_id'])) {
                    $locationName = $locationMap[$item['location_id']] ?? '';
                }
                $timeLabel = $startRaw ? date('H:i', strtotime((string) $startRaw)) : '—';
                if ($endRaw) {
                    $timeLabel .= ' - ' . date('H:i', strtotime((string) $endRaw));
                }
            ?>
                <div class="schedule-item" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="schedule-time">
                        <i class="bi bi-clock"></i>
                        <span><?= esc($timeLabel) ?></span>
                    </div>
                    <div class="schedule-content">
                        <h3 class="schedule-title"><?= esc($title) ?></h3>
                        <?php if (!empty($description)): ?>
                            <p class="schedule-description"><?= esc($description) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($locationName)): ?>
                            <p class="schedule-location">
                                <i class="bi bi-geo-alt"></i> <?= esc($locationName) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
