<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EventModel;
use App\Models\MediaAssetModel;

class ProjectShowcaseService
{
    public function __construct(
        private EventModel $eventModel = new EventModel(),
        private MediaAssetModel $mediaAssetModel = new MediaAssetModel()
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProjects(int $limit = 12): array
    {
        $events = $this->eventModel
            ->where('service_status', 'active')
            ->orderBy('event_date_start', 'DESC')
            ->findAll($limit);

        return array_map(fn (array $event): array => $this->hydrateProject($event), $events);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProjectBySlug(string $slug): ?array
    {
        $event = $this->eventModel->findBySlug($slug);
        if ($event === null) {
            return null;
        }

        return $this->hydrateProject($event, includeGallery: true);
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    private function hydrateProject(array $event, bool $includeGallery = false): array
    {
        $galleryAssets = $this->mediaAssetModel
            ->where('event_id', (string) $event['id'])
            ->where('category', 'gallery')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();

        $coverAsset = $galleryAssets[0] ?? null;
        $fallbackImage = 'img/demo-preview.png';

        $project = [
            'id' => (string) $event['id'],
            'slug' => (string) $event['slug'],
            'title' => (string) ($event['couple_title'] ?? 'Proyecto 13Bodas'),
            'event_date_start' => $event['event_date_start'] ?? null,
            'venue_name' => (string) ($event['venue_name'] ?? ''),
            'cover_image' => $coverAsset['file_url_original'] ?? $fallbackImage,
            'cover_alt' => $coverAsset['alt_text'] ?? $event['couple_title'] ?? 'Proyecto 13Bodas',
        ];

        if ($includeGallery) {
            $project['gallery'] = array_map(static fn (array $asset): array => [
                'url' => (string) ($asset['file_url_original'] ?? ''),
                'alt_text' => (string) ($asset['alt_text'] ?? ''),
                'caption' => (string) ($asset['caption'] ?? ''),
            ], $galleryAssets);
        }

        return $project;
    }
}
