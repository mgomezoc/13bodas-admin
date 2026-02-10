<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\EventPayment;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class EventPaymentModel extends Model
{
    /** @var array<string, true>|null */
    private ?array $tableColumns = null;

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
        'provider_event_id',
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
        return $this->select('id')
            ->where('payment_provider', $provider)
            ->where('payment_reference', $reference)
            ->first() !== null;
    }

    public function findIdByReference(string $provider, string $reference): ?string
    {
        $payment = $this->select('id')
            ->where('payment_provider', $provider)
            ->where('payment_reference', $reference)
            ->first();

        if ($payment instanceof EventPayment) {
            return (string) ($payment->id ?? '');
        }

        return null;
    }

    public function createFromWebhook(array $data): string
    {
        $provider = (string) ($data['payment_provider'] ?? '');
        $reference = (string) ($data['payment_reference'] ?? '');

        if ($provider === '' || $reference === '') {
            throw new \InvalidArgumentException('payment_provider y payment_reference son obligatorios.');
        }

        if ($this->existsByReference($provider, $reference)) {
            $existingId = $this->findIdByReference($provider, $reference);
            if ($existingId !== null && $existingId !== '') {
                return $existingId;
            }

            throw new \RuntimeException('Duplicate payment reference without retrievable ID.');
        }

        $data['id'] = $data['id'] ?? $this->generateUUIDv4();
        $data = $this->sanitizeDataForCurrentSchema($data);

        $missingRequired = array_diff(['id', 'event_id', 'payment_provider', 'payment_reference', 'amount', 'currency', 'status'], array_keys($data));
        if ($missingRequired !== []) {
            throw new \RuntimeException('event_payments schema incompleto. Faltan columnas requeridas: ' . implode(', ', $missingRequired));
        }

        try {
            if (!$this->insert($data)) {
                throw new \RuntimeException('Failed to insert payment: ' . json_encode($this->errors(), JSON_UNESCAPED_UNICODE));
            }

            return (string) $data['id'];
        } catch (DatabaseException $exception) {
            if ($this->isDuplicateKeyException($exception)) {
                $existingId = $this->findIdByReference($provider, $reference);
                if ($existingId !== null && $existingId !== '') {
                    return $existingId;
                }

                throw new \RuntimeException('Duplicate payment detected.', previous: $exception);
            }

            throw new \RuntimeException('Database error inserting payment.', previous: $exception);
        }
    }

    private function generateUUIDv4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    private function isDuplicateKeyException(DatabaseException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'duplicate')
            || str_contains((string) $exception->getCode(), '1062');
    }

    private function sanitizeDataForCurrentSchema(array $data): array
    {
        $columns = $this->loadTableColumns();
        if ($columns === []) {
            return $data;
        }

        $sanitized = [];
        $droppedKeys = [];

        foreach ($data as $key => $value) {
            $column = (string) $key;
            if (isset($columns[$column])) {
                $sanitized[$column] = $value;
                continue;
            }

            $droppedKeys[] = $column;
        }

        if ($droppedKeys !== []) {
            log_message('warning', 'EventPaymentModel dropped non-existing columns: {columns}', [
                'columns' => implode(',', $droppedKeys),
            ]);
        }

        return $sanitized;
    }

    /**
     * @return array<string, true>
     */
    private function loadTableColumns(): array
    {
        if ($this->tableColumns !== null) {
            return $this->tableColumns;
        }

        $fields = $this->db->getFieldData($this->table);
        $columns = [];

        foreach ($fields as $field) {
            $name = (string) ($field->name ?? '');
            if ($name !== '') {
                $columns[$name] = true;
            }
        }

        $this->tableColumns = $columns;

        return $columns;
    }
}
