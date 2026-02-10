<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EventModel;
use App\Models\EventTemplateModel;

class ClientOnboardingService
{
    public function __construct(
        private readonly EventModel $eventModel = new EventModel(),
        private readonly EventTemplateModel $eventTemplateModel = new EventTemplateModel(),
    ) {
    }

    /**
     * Determina si el usuario cliente debe ir directo a seleccionar template.
     */
    public function resolvePostLoginRedirect(array $roleNames, ?string $clientId): ?string
    {
        if (!$this->isClientOnly($roleNames) || empty($clientId)) {
            return null;
        }

        $event = $this->eventModel->where('client_id', $clientId)->orderBy('created_at', 'DESC')->first();
        if (!$event || empty($event['id'])) {
            return null;
        }

        $activeTemplateId = $this->eventTemplateModel->getActiveTemplateId((string) $event['id']);
        if ($activeTemplateId !== null) {
            return null;
        }

        return base_url('admin/events/edit/' . $event['id'] . '?highlight=template');
    }

    private function isClientOnly(array $roleNames): bool
    {
        $isClient = in_array('client', $roleNames, true);
        $isAdmin = in_array('superadmin', $roleNames, true) || in_array('admin', $roleNames, true) || in_array('staff', $roleNames, true);

        return $isClient && !$isAdmin;
    }
}
