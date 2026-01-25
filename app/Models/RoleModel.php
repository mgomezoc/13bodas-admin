<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description'];

    /**
     * Obtener rol por nombre
     */
    public function findByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Obtener permisos de un rol
     */
    public function getPermissions(int $roleId): array
    {
        $db = \Config\Database::connect();
        return $db->table('role_permissions')
            ->select('permissions.*')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->get()
            ->getResultArray();
    }

    /**
     * Asignar permiso a rol
     */
    public function assignPermission(int $roleId, int $permissionId): bool
    {
        $db = \Config\Database::connect();
        
        // Verificar si ya existe
        $exists = $db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->countAllResults() > 0;

        if ($exists) {
            return true;
        }

        return $db->table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }

    /**
     * Remover todos los permisos de un rol
     */
    public function removeAllPermissions(int $roleId): bool
    {
        $db = \Config\Database::connect();
        return $db->table('role_permissions')->where('role_id', $roleId)->delete();
    }

    /**
     * Obtener todos los roles con conteo de usuarios
     */
    public function listWithUserCount(): array
    {
        return $this->select('roles.*, COUNT(user_roles.user_id) as user_count')
            ->join('user_roles', 'user_roles.role_id = roles.id', 'left')
            ->groupBy('roles.id')
            ->findAll();
    }
}
