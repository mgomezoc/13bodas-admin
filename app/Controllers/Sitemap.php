<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventModel;
use CodeIgniter\HTTP\ResponseInterface;

class Sitemap extends BaseController
{
    public function __construct(
        private readonly EventModel $eventModel = new EventModel(),
    ) {
    }

    /**
     * Genera el sitemap.xml dinámico para URLs indexables públicas.
     */
    public function index(): ResponseInterface
    {
        $xmlLines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        $staticUrls = [
            ['loc' => base_url('/'), 'changefreq' => 'weekly', 'priority' => '1.0', 'lastmod' => date('Y-m-d')],
            ['loc' => base_url('terminos'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => base_url('privacidad'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => base_url('llms.txt'), 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => base_url('llms-full.txt'), 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => 'https://magiccam.13bodas.com', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ];

        foreach ($staticUrls as $urlData) {
            $xmlLines[] = $this->buildUrlNode($urlData);
        }

        try {
            $publicEvents = $this->eventModel
                ->where('visibility', 'public')
                ->where('service_status', 'active')
                ->findAll();

            foreach ($publicEvents as $event) {
                $slug = (string) ($event['slug'] ?? '');

                if ($slug === '') {
                    continue;
                }

                $updatedAt = (string) ($event['updated_at'] ?? date('Y-m-d H:i:s'));

                $xmlLines[] = $this->buildUrlNode([
                    'loc' => base_url('i/' . $slug),
                    'lastmod' => date('Y-m-d', strtotime($updatedAt)),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ]);
            }
        } catch (\Throwable $throwable) {
            log_message('error', 'Error generando sitemap dinámico: {message}', ['message' => $throwable->getMessage()]);
        }

        $xmlLines[] = '</urlset>';

        return $this->response
            ->setContentType('application/xml; charset=UTF-8')
            ->setBody(implode(PHP_EOL, $xmlLines));
    }

    /**
     * @param array{loc:string, changefreq:string, priority:string, lastmod?:string} $urlData
     */
    private function buildUrlNode(array $urlData): string
    {
        $lastmodNode = '';

        if (isset($urlData['lastmod']) && $urlData['lastmod'] !== '') {
            $lastmodNode = '<lastmod>' . esc($urlData['lastmod'], 'xml') . '</lastmod>';
        }

        return '<url>'
            . '<loc>' . esc($urlData['loc'], 'xml') . '</loc>'
            . $lastmodNode
            . '<changefreq>' . esc($urlData['changefreq'], 'xml') . '</changefreq>'
            . '<priority>' . esc($urlData['priority'], 'xml') . '</priority>'
            . '</url>';
    }
}
