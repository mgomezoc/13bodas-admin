<?php

declare(strict_types=1);

namespace App\Libraries;

final class StructuredDataBuilder
{
    /**
     * @param array<int, array<string, mixed>> $nodes
     */
    public function renderScripts(array $nodes): string
    {
        $scripts = [];

        foreach ($nodes as $node) {
            $scripts[] = $this->renderScript($node);
        }

        return implode("\n", $scripts);
    }

    /**
     * @param array<string, mixed> $node
     */
    public function renderScript(array $node): string
    {
        $payload = json_encode(
            $node,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        if ($payload === false) {
            return '';
        }

        return '<script type="application/ld+json">' . $payload . '</script>';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function homeSchemas(string $baseUrl): array
    {
        $baseUrl = rtrim($baseUrl, '/');

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => '13Bodas',
                'url' => $baseUrl . '/',
                'logo' => $baseUrl . '/img/13bodas-logo-invitaciones-digitales.png',
                'description' => 'Plataforma para crear invitaciones digitales y administrar RSVP para eventos.',
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'contactType' => 'sales',
                    'telephone' => '+52-81-1524-7741',
                    'availableLanguage' => ['es', 'en'],
                    'areaServed' => 'Worldwide',
                ],
                'sameAs' => [
                    'https://www.facebook.com/13bodas',
                    'https://magiccam.13bodas.com',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => '13Bodas Platform',
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'inLanguage' => 'es-MX',
                'url' => $baseUrl . '/',
                'description' => 'Plataforma web para crear invitaciones digitales con RSVP y gestión de invitados.',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                    'description' => 'Registro gratuito con demo inicial',
                ],
                'provider' => [
                    '@type' => 'Organization',
                    'name' => '13Bodas',
                    'url' => $baseUrl . '/',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => 'Plataforma de Invitaciones Digitales con RSVP',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => '13Bodas',
                ],
                'areaServed' => 'Worldwide',
                'serviceType' => 'Event invitation and RSVP management software',
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => '¿Puedo usar 13Bodas fuera de México?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Sí. La plataforma funciona online y puedes administrar tu evento desde cualquier país.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => '¿Qué significa RSVP en una invitación?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'RSVP significa por favor confirma tu asistencia para organizar mejor la logística del evento.',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    public function eventSchema(array $event, string $eventUrl): array
    {
        $name = (string) ($event['couple_title'] ?? 'Evento 13Bodas');
        $startDate = $this->normalizeDate($event['event_date_start'] ?? null);
        $endDate = $this->normalizeDate($event['event_date_end'] ?? null);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $name,
            'url' => $eventUrl,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'organizer' => [
                '@type' => 'Organization',
                'name' => '13Bodas',
                'url' => base_url('/'),
            ],
        ];

        if ($startDate !== null) {
            $schema['startDate'] = $startDate;
        }

        if ($endDate !== null) {
            $schema['endDate'] = $endDate;
        }

        $venueName = trim((string) ($event['venue_name'] ?? ''));
        $venueAddress = trim((string) ($event['venue_address'] ?? ''));
        if ($venueName !== '' || $venueAddress !== '') {
            $schema['location'] = [
                '@type' => 'Place',
                'name' => $venueName !== '' ? $venueName : $name,
                'address' => $venueAddress,
            ];
        }

        return $schema;
    }

    private function normalizeDate(mixed $rawDate): ?string
    {
        if (!is_string($rawDate) || trim($rawDate) === '') {
            return null;
        }

        $timestamp = strtotime($rawDate);

        if ($timestamp === false) {
            return null;
        }

        return date(DATE_ATOM, $timestamp);
    }
}
