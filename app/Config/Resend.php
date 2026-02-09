<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Resend extends BaseConfig
{
    public string $apiKey = '';
    public string $apiUrl = 'https://api.resend.com';
    public string $fromEmail = 'no-reply@verified-domain.com';
    public string $fromName = '13Bodas';

    public function __construct()
    {
        $this->apiKey = (string) (env('RESEND_API_KEY') ?? '');
        $this->apiUrl = (string) (env('RESEND_API_URL') ?? $this->apiUrl);
        $this->fromEmail = (string) (env('RESEND_FROM_EMAIL') ?? $this->fromEmail);
        $this->fromName = (string) (env('RESEND_FROM_NAME') ?? $this->fromName);
    }
}
