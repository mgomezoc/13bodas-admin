<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EventModel;
use App\Models\MediaAssetModel;

class ProjectShowcaseService
{
    private const FALLBACK_IMAGE = 'img/demo-preview.png';

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

        return array_values(array_map(fn (array $event): array => $this->hydrateProject($event), $events));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProjectBySlug(string $slug): ?array
    {
        $event = $this->eventModel
            ->where('slug', $slug)
            ->where('service_status', 'active')
            ->first();

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

        $galleryFromFilesystem = $this->resolveFilesystemGallery(
            eventId: (string) $event['id'],
            slug: (string) $event['slug'],
            title: (string) ($event['couple_title'] ?? 'Proyecto 13Bodas')
        );

        $normalizedMediaAssets = array_map(static fn (array $asset): array => [
            'url' => (string) ($asset['file_url_original'] ?? ''),
            'alt_text' => (string) ($asset['alt_text'] ?? ''),
            'caption' => (string) ($asset['caption'] ?? ''),
        ], $galleryAssets);

        $gallery = $normalizedMediaAssets !== [] ? $normalizedMediaAssets : $galleryFromFilesystem;
        $coverAsset = $gallery[0] ?? null;

        $project = [
            'id' => (string) $event['id'],
            'slug' => (string) $event['slug'],
            'title' => (string) ($event['couple_title'] ?? 'Proyecto 13Bodas'),
            'event_date_start' => $event['event_date_start'] ?? null,
            'venue_name' => (string) ($event['venue_name'] ?? ''),
            'cover_image' => $coverAsset['url'] ?? self::FALLBACK_IMAGE,
            'cover_alt' => $coverAsset['alt_text'] ?? $event['couple_title'] ?? 'Proyecto 13Bodas',
        ];

        if ($includeGallery) {
            $project['gallery'] = $gallery;
        }

        return $project;
    }

    /**
     * @return array<int, array{url: string, alt_text: string, caption: string}>
     */
    private function resolveFilesystemGallery(string $eventId, string $slug, string $title): array
    {
        $candidates = [
            FCPATH . 'assets/images/proyectos/' . $slug,
            FCPATH . 'uploads/events/' . $eventId . '/gallery',
        ];

        foreach ($candidates as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = glob($directory . '/*.{jpg,jpeg,png,webp,gif,avif,JPG,JPEG,PNG,WEBP,GIF,AVIF}', GLOB_BRACE);
            if ($files === false || $files === []) {
                continue;
            }

            natsort($files);

            return array_map(static function (string $absolutePath) use ($title): array {
                $relativePath = str_replace('\\', '/', ltrim(str_replace(FCPATH, '', $absolutePath), '/'));
                $filename = pathinfo($absolutePath, PATHINFO_FILENAME);

                return [
                    'url' => $relativePath,
                    'alt_text' => $filename !== '' ? $filename : $title,
                    'caption' => '',
                ];
            }, array_values($files));
        }

        return [];
    }
}
