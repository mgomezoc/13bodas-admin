<?php

declare(strict_types=1);

namespace App\Libraries;

class InvitationLinkBuilder
{
    public function buildInviteUrl(array $event, array $guest, array $group): string
    {
        $slug = (string) ($event['slug'] ?? '');
        $guestId = (string) ($guest['id'] ?? '');
        $accessMode = (string) ($event['access_mode'] ?? 'open');
        $accessCode = (string) ($group['access_code'] ?? '');

        $baseUrl = rtrim(base_url('i/' . $slug), '/');
        $query = ['guest' => $guestId];

        if ($accessMode === 'invite_code' && $accessCode !== '') {
            $query['code'] = $accessCode;
        }

        $queryString = http_build_query($query);

        return $baseUrl . '?' . $queryString . '#rsvp';
    }
}
