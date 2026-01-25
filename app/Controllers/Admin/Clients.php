<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\UserModel;
use App\Models\EventModel;

class Clients extends BaseController
{
    protected $clientModel;
    protected $userModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->userModel = new UserModel();
    }

    /**
     * Lista de clientes
     */
    public function index()
    {
        $data = [
            'pageTitle' => 'Clientes'
        ];

        return view('admin/clients/index', $data);
    }

    /**
     * API: Lista de clientes para Bootstrap Table
     */
    public function list()
    {
        $clients = $this->clientModel->listWithUsers([
            'search' => $this->request->getGet('search'),
            'is_active' => $this->request->getGet('is_active')
        ]);

        // Agregar conteo de eventos
        $eventModel = new EventModel();
        foreach ($clients as &$client) {
            $client['event_count'] = $eventModel->where('client_id', $client['id'])->countAllResults();
        }

        return $this->response->setJSON([
            'total' => count($clients),
            'rows' => $clients
        ]);
    }

    /**
     * Formulario para crear cliente
     */
    public function create()
    {
        $data = [
            'pageTitle' => 'Nuevo Cliente'
        ];

        return view('admin/clients/create', $data);
    }

    /**
     * Guardar nuevo cliente
     */
    public function store()
    {
        $rules = [
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'permit_empty|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userData = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'is_active' => 1
        ];

        $clientData = [
            'company_name' => $this->request->getPost('company_name'),
            'notes' => $this->request->getPost('notes')
        ];

        $clientId = $this->clientModel->createWithUser($userData, $clientData);

        if ($clientId) {
            return redirect()->to(base_url('admin/clients'))
                ->with('success', 'Cliente creado correctamente. Las credenciales han sido configuradas.');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Error al crear el cliente. Por favor intenta de nuevo.');
    }

    /**
     * Ver detalle del cliente
     */
    public function view(string $id)
    {
        $client = $this->clientModel->getWithEvents($id);

        if (!$client) {
            return redirect()->to(base_url('admin/clients'))
                ->with('error', 'Cliente no encontrado.');
        }

        $data = [
            'pageTitle' => 'Detalle de Cliente',
            'client' => $client
        ];

        return view('admin/clients/view', $data);
    }

    /**
     * Formulario para editar cliente
     */
    public function edit(string $id)
    {
        $client = $this->clientModel->getWithUser($id);

        if (!$client) {
            return redirect()->to(base_url('admin/clients'))
                ->with('error', 'Cliente no encontrado.');
        }

        $data = [
            'pageTitle' => 'Editar Cliente',
            'client' => $client
        ];

        return view('admin/clients/edit', $data);
    }

    /**
     * Actualizar cliente
     */
    public function update(string $id)
    {
        $client = $this->clientModel->getWithUser($id);

        if (!$client) {
            return redirect()->to(base_url('admin/clients'))
                ->with('error', 'Cliente no encontrado.');
        }

        $rules = [
            'email' => "required|valid_email|is_unique[users.email,id,{$client['user_id']}]",
            'full_name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'permit_empty|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Actualizar usuario
        $userData = [
            'email' => $this->request->getPost('email'),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
        ];

        // Si se proporciona nueva contraseña
        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                return redirect()->back()->withInput()
                    ->with('error', 'La contraseña debe tener al menos 6 caracteres.');
            }
            $userData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $this->userModel->update($client['user_id'], $userData);

        // Actualizar cliente
        $clientData = [
            'company_name' => $this->request->getPost('company_name'),
            'notes' => $this->request->getPost('notes')
        ];

        $this->clientModel->update($id, $clientData);

        return redirect()->to(base_url('admin/clients'))
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleStatus(string $id)
    {
        $client = $this->clientModel->getWithUser($id);

        if (!$client) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado.'
            ]);
        }

        $newStatus = $client['is_active'] ? 0 : 1;
        $this->userModel->update($client['user_id'], ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => $newStatus ? 'Cliente activado.' : 'Cliente desactivado.',
            'new_status' => $newStatus
        ]);
    }
}
