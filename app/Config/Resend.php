<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Resend extends BaseConfig
{
    public string $apiKey = '[TU_NUEVA_KEY_AQUÍ]';
    public string $apiUrl = 'https://api.resend.com';
    public string $fromEmail = 'no-reply@verified-domain.com';
    public string $fromName = '13Bodas';
}
