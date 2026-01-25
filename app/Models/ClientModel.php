<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'user_id',
        'company_name',
        'notes',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtener cliente con datos de usuario
     */
    public function getWithUser(string $clientId): ?array
    {
        return $this->select('clients.*, users.email, users.full_name, users.phone, users.is_active, users.last_login_at')
            ->join('users', 'users.id = clients.user_id')
            ->where('clients.id', $clientId)
            ->first();
    }

    /**
     * Listar clientes con datos de usuario
     */
    public function listWithUsers(array $filters = []): array
    {
        $builder = $this->select('clients.*, users.email, users.full_name, users.phone, users.is_active, users.last_login_at')
            ->join('users', 'users.id = clients.user_id');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('users.email', $filters['search'])
                ->orLike('users.full_name', $filters['search'])
                ->orLike('clients.company_name', $filters['search'])
            ->groupEnd();
        }

        if (isset($filters['is_active'])) {
            $builder->where('users.is_active', $filters['is_active']);
        }

        return $builder->orderBy('clients.created_at', 'DESC')->findAll();
    }

    /**
     * Obtener cliente por user_id
     */
    public function getByUserId(string $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Obtener cliente con sus eventos
     */
    public function getWithEvents(string $clientId): ?array
    {
        $client = $this->getWithUser($clientId);
        if (!$client) {
            return null;
        }

        $eventModel = new EventModel();
        $client['events'] = $eventModel->where('client_id', $clientId)->findAll();
        
        return $client;
    }

    /**
     * Crear cliente con usuario
     */
    public function createWithUser(array $userData, array $clientData = []): ?string
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $userModel = new UserModel();
        
        // Crear usuario
        $userId = UserModel::generateUUID();
        $userData['id'] = $userId;
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);
        
        if (!$userModel->insert($userData)) {
            $db->transRollback();
            return null;
        }

        // Asignar rol de cliente (role_id = 4)
        $userModel->assignRole($userId, 4);

        // Crear registro de cliente
        $clientId = UserModel::generateUUID();
        $clientData['id'] = $clientId;
        $clientData['user_id'] = $userId;
        
        if (!$this->insert($clientData)) {
            $db->transRollback();
            return null;
        }

        $db->transComplete();
        
        return $db->transStatus() ? $clientId : null;
    }
}
