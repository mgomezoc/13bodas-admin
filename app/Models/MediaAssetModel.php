<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaAssetModel extends Model
{
    protected $table            = 'media_assets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'event_id',
        'file_url_original',
        'file_url_thumbnail',
        'file_url_large',
        'mime_type',
        'alt_text',
        'aspect_ratio',
        'category_tag',
        'sort_order',
        'is_private',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Obtener assets de un evento
     */
    public function getByEvent(string $eventId, ?string $category = null): array
    {
        $builder = $this->where('event_id', $eventId);
        
        if ($category) {
            $builder->where('category_tag', $category);
        }
        
        return $builder->orderBy('sort_order', 'ASC')->findAll();
    }

    /**
     * Crear asset
     */
    public function createAsset(array $data): ?string
    {
        $assetId = UserModel::generateUUID();
        $data['id'] = $assetId;
        $data['is_private'] = $data['is_private'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Obtener siguiente orden
        if (!isset($data['sort_order'])) {
            $maxOrder = $this->where('event_id', $data['event_id'])
                ->selectMax('sort_order')
                ->first();
            $data['sort_order'] = ($maxOrder['sort_order'] ?? 0) + 1;
        }

        if ($this->insert($data)) {
            return $assetId;
        }
        
        return null;
    }

    /**
     * Actualizar orden de assets
     */
    public function updateOrder(array $assetIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($assetIds as $index => $assetId) {
            $this->update($assetId, ['sort_order' => $index + 1]);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Obtener categorías disponibles de un evento
     */
    public function getCategories(string $eventId): array
    {
        return $this->select('DISTINCT category_tag')
            ->where('event_id', $eventId)
            ->where('category_tag IS NOT NULL')
            ->findAll();
    }
}
