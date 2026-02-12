<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\EventModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EventPaymentFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ResponseInterface|string|null
    {
        $eventId = $arguments[0] ?? $request->getGet('event_id');
        if (!$eventId) {
            return null;
        }

        $event = (new EventModel())->find((string) $eventId);
        if (!$event) {
            return redirect()->route('admin.events.index')->with('error', 'Evento no encontrado.');
        }

        $isPaid = (int) ($event['is_paid'] ?? 0) === 1;
        $isValid = empty($event['paid_until']) || strtotime((string) $event['paid_until']) > time();
        
        // También verificar campo 'active' si existe
        $isActive = !isset($event['active']) || $event['active'] === 'Y' || $event['active'] === 1;

        if ($isPaid && $isValid && $isActive) {
            return null;
        }

        $path = $request->getUri()->getPath();
        foreach (['registry', 'custom-domain'] as $blocked) {
            if (str_contains($path, $blocked)) {
                return redirect()->route('checkout.index', [$eventId])->with('warning', 'Esta funcionalidad requiere activar tu evento.');
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
