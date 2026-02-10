<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\EventPayment;
use CodeIgniter\Model;

class EventPaymentModel extends Model
{
    protected $table            = 'event_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = EventPayment::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'payment_provider',
        'payment_reference',
        'amount',
        'currency',
        'status',
        'customer_email',
        'customer_name',
        'payment_method',
        'paid_at',
        'expires_at',
        'webhook_received_at',
        'webhook_payload',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'event_id'          => 'required|min_length[36]|max_length[36]',
        'payment_provider'  => 'required|max_length[30]',
        'payment_reference' => 'required|max_length[120]',
        'amount'            => 'required|decimal',
        'currency'          => 'required|exact_length[3]',
    ];

    public function existsByReference(string $provider, string $reference): bool
    {
        return $this->where('payment_provider', $provider)
            ->where('payment_reference', $reference)
            ->countAllResults() > 0;
    }

    public function createFromWebhook(array $data): bool|string
    {
        if ($this->existsByReference((string) $data['payment_provider'], (string) $data['payment_reference'])) {
            log_message('info', 'Payment already processed: {reference}', ['reference' => $data['payment_reference']]);
            return false;
        }

        // FIX: Usar método local en lugar de UserModel::generateUUID()
        $data['id'] = $data['id'] ?? $this->generateUUID();

        return $this->insert($data) ? (string) $data['id'] : false;
    }

    /**
     * Genera un UUID v4
     */
    protected function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
