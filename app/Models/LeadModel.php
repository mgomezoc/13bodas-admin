<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadModel extends Model
{
    protected $table            = 'leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'full_name',
        'email',
        'phone',
        'event_date',
        'message',
        'source',
        'status',
        'utm_payload',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Crear nuevo lead
     */
    public function createLead(array $data): ?string
    {
        $leadId = UserModel::generateUUID();
        $data['id'] = $leadId;
        $data['status'] = $data['status'] ?? 'new';
        $data['source'] = $data['source'] ?? 'website';

        if ($this->insert($data)) {
            return $leadId;
        }
        
        return null;
    }

    /**
     * Listar leads con filtros
     */
    public function listWithFilters(array $filters = []): array
    {
        $builder = $this;

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('full_name', $filters['search'])
                ->orLike('email', $filters['search'])
                ->orLike('phone', $filters['search'])
            ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $builder->where('source', $filters['source']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'DESC';

        return $builder->orderBy($sortField, $sortOrder)->findAll();
    }

    /**
     * Actualizar estado del lead
     */
    public function updateStatus(string $leadId, string $status): bool
    {
        return $this->update($leadId, ['status' => $status]);
    }

    /**
     * Obtener estadísticas de leads
     */
    public function getStats(): array
    {
        $db = \Config\Database::connect();
        
        // Por estado
        $byStatus = $db->table('leads')
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        // Por fuente
        $bySource = $db->table('leads')
            ->select('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->getResultArray();

        // Últimos 30 días
        $last30Days = $db->table('leads')
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->countAllResults();

        // Este mes
        $thisMonth = $db->table('leads')
            ->where('created_at >=', date('Y-m-01'))
            ->countAllResults();

        return [
            'by_status' => $byStatus,
            'by_source' => $bySource,
            'last_30_days' => $last30Days,
            'this_month' => $thisMonth,
            'total' => $this->countAllResults()
        ];
    }

    /**
     * Convertir lead a cliente
     */
    public function convertToClient(string $leadId, string $password): ?string
    {
        $lead = $this->find($leadId);
        if (!$lead) {
            return null;
        }

        $clientModel = new ClientModel();
        
        $clientId = $clientModel->createWithUser([
            'email' => $lead['email'],
            'password' => $password,
            'full_name' => $lead['full_name'],
            'phone' => $lead['phone'],
            'is_active' => 1
        ], [
            'notes' => "Convertido desde lead. Mensaje original: " . ($lead['message'] ?? 'N/A')
        ]);

        if ($clientId) {
            // Actualizar estado del lead
            $this->update($leadId, ['status' => 'converted']);
        }

        return $clientId;
    }
}
