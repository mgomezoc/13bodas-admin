<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Enums\RsvpStatus;
use Config\Database;

class RsvpSubmissionService
{
    public function __construct(private RsvpMailer $mailer = new RsvpMailer())
    {
    }

    public function submit(array $event, array $payload): array
    {
        if (($event['service_status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'Evento no disponible.'];
        }

        $guestId = trim((string) ($payload['guest_id'] ?? ''));
        if ($guestId !== '') {
            return $this->submitForGuest($event, $payload, $guestId);
        }

        if (($event['access_mode'] ?? 'open') !== 'open') {
            return ['success' => false, 'message' => 'Este evento requiere código de invitación.'];
        }

        $persisted = $this->persistRsvp($event, $payload);
        if (!$persisted['success']) {
            return $persisted;
        }

        $emailResult = $this->mailer->sendConfirmation($event, $payload);
        if (!$emailResult['success']) {
            return ['success' => false, 'message' => $emailResult['message']];
        }

        return ['success' => true, 'message' => 'Confirmación registrada y correo enviado.', 'data' => $persisted['data']];
    }

    private function persistRsvp(array $event, array $payload): array
    {
        $db = Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        try {
            $accessCode = substr(bin2hex(random_bytes(8)), 0, 12);
            $guestName = (string) ($payload['name'] ?? '');

            $db->table('guest_groups')->insert([
                'event_id'              => $event['id'],
                'group_name'            => $guestName,
                'access_code'           => $accessCode,
                'max_additional_guests' => 0,
                'is_vip'                => 0,
                'current_status'        => 'responded',
                'responded_at'          => $now,
                'invited_at'            => null,
                'first_viewed_at'       => $now,
                'last_viewed_at'        => $now,
            ]);

            $group = $db->table('guest_groups')
                ->select('id')
                ->where('event_id', $event['id'])
                ->where('access_code', $accessCode)
                ->get()
                ->getRowArray();

            if (!$group || empty($group['id'])) {
                throw new \RuntimeException('No se pudo crear el grupo.');
            }

            $groupId = $group['id'];

            $parts = preg_split('/\s+/', $guestName, 2);
            $first = $parts[0] ?? $guestName;
            $last = $parts[1] ?? '';

            $attending = (string) ($payload['attending'] ?? RsvpStatus::Pending->value);
            $attendingValue = $attending === 'maybe' ? RsvpStatus::Pending->value : $attending;
            $guestStatus = match ($attendingValue) {
                RsvpStatus::Accepted->value => RsvpStatus::Accepted->value,
                RsvpStatus::Declined->value => RsvpStatus::Declined->value,
                default => RsvpStatus::Pending->value,
            };

            $db->table('guests')->insert([
                'group_id'           => $groupId,
                'first_name'         => $first,
                'last_name'          => $last,
                'email'              => $payload['email'] ?? null,
                'phone_number'       => $payload['phone'] ?? null,
                'is_child'           => 0,
                'is_primary_contact' => 1,
                'rsvp_status'        => $guestStatus,
            ]);

            $guest = $db->table('guests')
                ->select('id')
                ->where('group_id', $groupId)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$guest || empty($guest['id'])) {
                throw new \RuntimeException('No se pudo crear el invitado.');
            }

            $guestId = $guest['id'];

            $db->table('rsvp_responses')->insert([
                'event_id'      => $event['id'],
                'group_id'      => $groupId,
                'guest_id'      => $guestId,
                'attending'     => $attendingValue,
                'message'       => $payload['message'] ?? null,
                'song_request'  => $payload['song_request'] ?? null,
                'responded_at'  => $now,
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('No se pudo guardar la confirmación.');
            }

            return [
                'success' => true,
                'message' => 'Confirmación registrada.',
                'data' => [
                    'group_id' => $groupId,
                    'guest_id' => $guestId,
                ],
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => 'No se pudo registrar la confirmación.'];
        }
    }

    private function submitForGuest(array $event, array $payload, string $guestId): array
    {
        $db = Database::connect();
        $now = date('Y-m-d H:i:s');

        $guestRow = $db->table('guests')
            ->select('guests.id, guests.group_id, guest_groups.event_id, guest_groups.access_code')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guests.id', $guestId)
            ->get()
            ->getRowArray();

        if (!$guestRow || ($guestRow['event_id'] ?? '') !== ($event['id'] ?? '')) {
            return ['success' => false, 'message' => 'Invitado no válido para este evento.'];
        }

        if (($event['access_mode'] ?? 'open') === 'invite_code') {
            $providedCode = trim((string) ($payload['guest_code'] ?? ''));
            if ($providedCode === '' || $providedCode !== ($guestRow['access_code'] ?? '')) {
                return ['success' => false, 'message' => 'Código de invitación inválido.'];
            }
        }

        $attending = (string) ($payload['attending'] ?? RsvpStatus::Pending->value);
        $attendingValue = $attending === 'maybe' ? RsvpStatus::Pending->value : $attending;
        $guestStatus = match ($attendingValue) {
            RsvpStatus::Accepted->value => RsvpStatus::Accepted->value,
            RsvpStatus::Declined->value => RsvpStatus::Declined->value,
            default => RsvpStatus::Pending->value,
        };

        $db->transStart();

        try {
            $db->table('guests')
                ->where('id', $guestId)
                ->update([
                    'rsvp_status' => $guestStatus,
                    'updated_at' => $now,
                ]);

            $responseTable = $db->table('rsvp_responses');
            $existing = $responseTable->where('guest_id', $guestId)->get()->getRowArray();

            $payloadData = [
                'guest_id' => $guestId,
                'event_id' => $event['id'],
                'group_id' => $guestRow['group_id'],
                'attending' => $attendingValue,
                'message' => $payload['message'] ?? null,
                'song_request' => $payload['song_request'] ?? null,
                'responded_at' => $now,
                'response_method' => 'public_guest_link',
            ];

            if ($existing) {
                $responseTable->where('id', $existing['id'])->update($payloadData);
            } else {
                $responseTable->insert($payloadData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('No se pudo guardar la confirmación.');
            }

            return [
                'success' => true,
                'message' => 'Confirmación registrada.',
                'data' => [
                    'guest_id' => $guestId,
                    'group_id' => $guestRow['group_id'],
                ],
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => 'No se pudo registrar la confirmación.'];
        }
    }
}
