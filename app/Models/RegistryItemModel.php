<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistryItemModel extends Model
{
    protected $table            = 'registry_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'event_id',
        'title',
        'description',
        'image_url',
        'product_url',
        'price',
        'currency_code',
        'is_fund',
        'goal_amount',
        'amount_collected',
        'is_claimed',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Obtener items de un evento
     */
    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Crear item de registro
     */
    public function createItem(array $data): ?string
    {
        $itemId = UserModel::generateUUID();
        $data['id'] = $itemId;
        $data['currency_code'] = $data['currency_code'] ?? 'MXN';
        $data['is_fund'] = $data['is_fund'] ?? 0;
        $data['amount_collected'] = $data['amount_collected'] ?? 0;
        $data['is_claimed'] = $data['is_claimed'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');

        if ($this->insert($data)) {
            return $itemId;
        }
        
        return null;
    }

    /**
     * Actualizar monto recaudado (para fondos)
     */
    public function addContribution(string $itemId, float $amount): bool
    {
        $item = $this->find($itemId);
        if (!$item || !$item['is_fund']) {
            return false;
        }

        $newAmount = (float)$item['amount_collected'] + $amount;
        return $this->update($itemId, ['amount_collected' => $newAmount]);
    }

    /**
     * Marcar como reclamado
     */
    public function markAsClaimed(string $itemId): bool
    {
        return $this->update($itemId, ['is_claimed' => 1]);
    }

    /**
     * Obtener resumen del registro
     */
    public function getSummary(string $eventId): array
    {
        $items = $this->getByEvent($eventId);
        
        $totalItems = count($items);
        $claimedItems = 0;
        $totalFunds = 0;
        $collectedFunds = 0;

        foreach ($items as $item) {
            if ($item['is_claimed']) {
                $claimedItems++;
            }
            if ($item['is_fund']) {
                $totalFunds += (float)$item['goal_amount'];
                $collectedFunds += (float)$item['amount_collected'];
            }
        }

        return [
            'total_items' => $totalItems,
            'claimed_items' => $claimedItems,
            'available_items' => $totalItems - $claimedItems,
            'total_funds_goal' => $totalFunds,
            'collected_funds' => $collectedFunds,
            'funds_percentage' => $totalFunds > 0 ? round(($collectedFunds / $totalFunds) * 100, 1) : 0
        ];
    }
}
