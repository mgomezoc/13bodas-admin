<?php

declare(strict_types=1);

namespace App\Libraries;

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

            $attending = (string) ($payload['attending'] ?? 'pending');
            $attendingValue = $attending === 'maybe' ? 'pending' : $attending;
            $guestStatus = $attendingValue === 'accepted' ? 'accepted' : ($attendingValue === 'declined' ? 'declined' : 'pending');

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
}
