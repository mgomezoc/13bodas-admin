<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;

class Users extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        return view('admin/users/index', ['pageTitle' => 'Usuarios']);
    }

    public function list()
    {
        $filters = [
            'search'    => $this->request->getGet('search'),
            'is_active' => $this->request->getGet('is_active'),
            'role'      => $this->request->getGet('role'),
        ];

        $users = $this->userModel->listWithRoles($filters);

        return $this->response->setJSON([
            'total' => count($users),
            'rows'  => $users,
        ]);
    }

    public function create()
    {
        $roles = $this->roleModel->findAll();

        return view('admin/users/form', [
            'pageTitle' => 'Nuevo Usuario',
            'user'      => null,
            'roles'     => $roles,
        ]);
    }

    public function edit(string $id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Usuario no encontrado.');
        }

        $roles     = $this->roleModel->findAll();
        $userRoles = $this->userModel->getUserRoles($id);
        $userRoleIds = array_column($userRoles, 'id');

        return view('admin/users/form', [
            'pageTitle'   => 'Editar Usuario',
            'user'        => $user,
            'roles'       => $roles,
            'userRoleIds' => $userRoleIds,
        ]);
    }

    public function save(?string $id = null)
    {
        $isUpdate = !empty($id);

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[120]',
            'email'     => $isUpdate 
                ? "required|valid_email|is_unique[users.email,id,{$id}]"
                : 'required|valid_email|is_unique[users.email]',
            'phone'     => 'permit_empty|max_length[30]',
            'is_active' => 'required|in_list[0,1]',
            'roles'     => 'required',
        ];

        if (!$isUpdate) {
            $rules['password'] = 'required|min_length[6]';
        } else {
            $rules['password'] = 'permit_empty|min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userData = [
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'phone'     => $this->request->getPost('phone'),
            'is_active' => $this->request->getPost('is_active'),
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $roleIds = $this->request->getPost('roles') ?? [];

        if ($isUpdate) {
            $updated = $this->userModel->update($id, $userData);
            if ($updated) {
                $this->userModel->syncRoles($id, $roleIds);
                return redirect()->to(base_url('admin/users'))
                    ->with('success', 'Usuario actualizado correctamente.');
            }
            return redirect()->back()->withInput()
                ->with('error', 'Error al actualizar el usuario.');
        } else {
            $userData['id'] = UserModel::generateUUID();
            $inserted = $this->userModel->insert($userData);
            if ($inserted) {
                $this->userModel->syncRoles($userData['id'], $roleIds);
                return redirect()->to(base_url('admin/users'))
                    ->with('success', 'Usuario creado correctamente.');
            }
            return redirect()->back()->withInput()
                ->with('error', 'Error al crear el usuario.');
        }
    }

    public function delete(string $id)
    {
        try {
            $user = $this->userModel->find($id);

            if (!$user) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Usuario no encontrado.'
                    ]);
                }
                return redirect()->back()->with('error', 'Usuario no encontrado.');
            }

            $deleted = $this->userModel->delete($id);

            if ($deleted) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Usuario eliminado correctamente.'
                    ]);
                }
                return redirect()->to(base_url('admin/users'))->with('success', 'Usuario eliminado correctamente.');
            }

            throw new \Exception('No se pudo eliminar el usuario.');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Error al eliminar el usuario.');
        }
    }

    public function toggleStatus(string $id)
    {
        try {
            $user = $this->userModel->find($id);

            if (!$user) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Usuario no encontrado.'
                    ]);
                }
                return redirect()->back()->with('error', 'Usuario no encontrado.');
            }

            $newStatus = $user['is_active'] ? 0 : 1;
            $updated   = $this->userModel->update($id, ['is_active' => $newStatus]);

            if ($updated) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => $newStatus ? 'Usuario activado.' : 'Usuario desactivado.',
                        'is_active' => $newStatus,
                    ]);
                }
                return redirect()->back()->with('success', $newStatus ? 'Usuario activado.' : 'Usuario desactivado.');
            }

            throw new \Exception('No se pudo cambiar el estado del usuario.');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al cambiar estado: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Error al cambiar el estado.');
        }
    }
}
