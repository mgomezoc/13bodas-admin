<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Enums\GuestGroupStatus;
use App\Models\EventModel;
use App\Models\GuestGroupModel;
use App\Models\GuestModel;

class GuestInvitationService
{
    public function __construct(
        private EventModel $eventModel = new EventModel(),
        private GuestModel $guestModel = new GuestModel(),
        private GuestGroupModel $groupModel = new GuestGroupModel(),
        private InvitationLinkBuilder $linkBuilder = new InvitationLinkBuilder(),
        private InvitationMailer $mailer = new InvitationMailer()
    ) {
    }

    public function resolveContextForEvent(
        array $event,
        string $guestId,
        ?string $accessCode = null,
        bool $requireAccessCode = false
    ): array {
        if ($guestId === '') {
            return ['success' => false, 'message' => 'Invitado no válido.'];
        }

        $guest = $this->guestModel->find($guestId);
        if (!$guest) {
            return ['success' => false, 'message' => 'Invitado no encontrado.'];
        }

        $groupId = (string) ($guest['group_id'] ?? '');
        $group = $groupId !== '' ? $this->groupModel->find($groupId) : null;

        if (!$group || ($group['event_id'] ?? '') !== ($event['id'] ?? '')) {
            return ['success' => false, 'message' => 'Invitado no pertenece al evento.'];
        }

        if ($requireAccessCode) {
            $groupCode = (string) ($group['access_code'] ?? '');
            if ($accessCode === null || $accessCode === '' || $groupCode !== $accessCode) {
                return ['success' => false, 'message' => 'Código de invitación inválido.'];
            }
        }

        return [
            'success' => true,
            'event' => $event,
            'guest' => $guest,
            'group' => $group,
            'invite_url' => $this->linkBuilder->buildInviteUrl($event, $guest, $group),
        ];
    }

    public function getInviteLink(string $eventId, string $guestId): array
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Evento no encontrado.'];
        }

        return $this->resolveContextForEvent($event, $guestId);
    }

    public function sendInvitation(string $eventId, string $guestId): array
    {
        $context = $this->getInviteLink($eventId, $guestId);
        if (!($context['success'] ?? false)) {
            return $context;
        }

        $guest = $context['guest'] ?? [];
        $event = $context['event'] ?? [];
        $group = $context['group'] ?? [];
        $inviteUrl = (string) ($context['invite_url'] ?? '');

        $email = trim((string) ($guest['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Este invitado no tiene email válido.'];
        }

        $mailerResult = $this->mailer->sendInvitation($event, $guest, $inviteUrl);
        if (!($mailerResult['success'] ?? false)) {
            return $mailerResult;
        }

        $groupId = (string) ($group['id'] ?? '');
        if ($groupId !== '') {
            $this->groupModel->update($groupId, [
                'invited_at' => date('Y-m-d H:i:s'),
                'current_status' => GuestGroupStatus::Invited->value,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Invitación enviada correctamente.',
            'invite_url' => $inviteUrl,
            'event' => $event,
            'guest' => $guest,
        ];
    }
}
