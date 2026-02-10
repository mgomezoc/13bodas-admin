<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\PaymentSetting;
use CodeIgniter\Model;

class PaymentSettingModel extends Model
{
    protected $table            = 'payment_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = PaymentSetting::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->where('setting_key', $key)
            ->where('is_active', 1)
            ->first();

        if (!$setting) {
            return $default;
        }

        return $this->castValue((string) $setting->setting_value, (string) $setting->setting_type);
    }

    public function getEventPrice(): float
    {
        return (float) $this->getValue('event_price_mxn', (float) env('PAYMENT_EVENT_PRICE_MXN', 800.00));
    }

    public function getDemoLimits(): array
    {
        return [
            'gallery'  => (int) $this->getValue('demo_gallery_limit', 10),
            'timeline' => (int) $this->getValue('demo_timeline_limit', 3),
            'rsvp'     => (int) $this->getValue('demo_rsvp_limit', 50),
        ];
    }

    private function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'decimal' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
