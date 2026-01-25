<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'email',
        'password_hash',
        'full_name',
        'phone',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'email'         => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password_hash' => 'permit_empty|min_length[60]',
        'full_name'     => 'permit_empty|max_length[255]',
        'phone'         => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Este correo electrónico ya está registrado.'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Genera un UUID v4
     */
    public static function generateUUID(): string
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

    /**
     * Busca un usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Verifica credenciales y retorna el usuario si son válidas
     */
    public function validateCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return null;
    }

    /**
     * Obtiene los roles de un usuario
     */
    public function getUserRoles(string $userId): array
    {
        $db = \Config\Database::connect();
        return $db->table('user_roles')
            ->select('roles.id, roles.name, roles.description')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene los permisos de un usuario basándose en sus roles
     */
    public function getUserPermissions(string $userId): array
    {
        $db = \Config\Database::connect();
        return $db->table('user_roles')
            ->select('DISTINCT permissions.name')
            ->join('role_permissions', 'role_permissions.role_id = user_roles.role_id')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('user_roles.user_id', $userId)
            ->get()
            ->getResultArray();
    }

    /**
     * Verifica si un usuario tiene un rol específico
     */
    public function hasRole(string $userId, string $roleName): bool
    {
        $roles = $this->getUserRoles($userId);
        foreach ($roles as $role) {
            if ($role['name'] === $roleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si un usuario tiene un permiso específico
     */
    public function hasPermission(string $userId, string $permissionName): bool
    {
        $permissions = $this->getUserPermissions($userId);
        foreach ($permissions as $permission) {
            if ($permission['name'] === $permissionName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Asigna un rol a un usuario
     */
    public function assignRole(string $userId, int $roleId): bool
    {
        $db = \Config\Database::connect();
        
        // Verificar si ya tiene el rol
        $existing = $db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->get()
            ->getRow();
            
        if ($existing) {
            return true;
        }
        
        return $db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }

    /**
     * Remueve todos los roles de un usuario
     */
    public function removeAllRoles(string $userId): bool
    {
        $db = \Config\Database::connect();
        return $db->table('user_roles')->where('user_id', $userId)->delete();
    }

    /**
     * Actualiza el último login
     */
    public function updateLastLogin(string $userId): bool
    {
        return $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Lista usuarios con sus roles (para el admin)
     */
    public function listWithRoles(array $filters = []): array
    {
        $builder = $this->select('users.*, GROUP_CONCAT(roles.name) as role_names')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->groupBy('users.id');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('users.email', $filters['search'])
                ->orLike('users.full_name', $filters['search'])
            ->groupEnd();
        }

        if (isset($filters['is_active'])) {
            $builder->where('users.is_active', $filters['is_active']);
        }

        if (!empty($filters['role'])) {
            $builder->where('roles.name', $filters['role']);
        }

        return $builder->orderBy('users.created_at', 'DESC')->findAll();
    }

    /**
     * Crea un nuevo usuario con rol
     */
    public function createWithRole(array $data, int $roleId): ?string
    {
        $userId = self::generateUUID();
        $data['id'] = $userId;
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        if ($this->insert($data)) {
            $this->assignRole($userId, $roleId);
            return $userId;
        }
        
        return null;
    }
}
