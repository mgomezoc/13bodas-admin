<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\UserModel;

class Leads extends BaseController
{
    protected LeadModel $leadModel;

    public function __construct()
    {
        $this->leadModel = new LeadModel();
    }

    public function index()
    {
        $statusOptions = [
            'new' => 'Nuevo',
            'contacted' => 'Contactado',
            'qualified' => 'Calificado',
            'converted' => 'Convertido',
            'lost' => 'Perdido',
        ];

        $sources = $this->leadModel->select('source')
            ->where('source IS NOT NULL', null, false)
            ->where('source !=', '')
            ->groupBy('source')
            ->orderBy('source', 'ASC')
            ->findAll();

        $sourceOptions = array_values(array_filter(array_map(
            fn(array $row) => $row['source'] ?? null,
            $sources
        )));

        return view('admin/leads/index', [
            'pageTitle' => 'Leads',
            'statusOptions' => $statusOptions,
            'sourceOptions' => $sourceOptions,
        ]);
    }

    public function list()
    {
        $allowedSorts = ['created_at', 'full_name', 'email', 'status', 'source', 'event_date'];
        $sort = $this->request->getGet('sort');
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $order = strtolower((string) $this->request->getGet('order')) === 'asc' ? 'ASC' : 'DESC';

        $limit = (int) ($this->request->getGet('limit') ?? 15);
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        if ($limit <= 0 || $limit > 100) {
            $limit = 15;
        }

        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
            'source' => $this->request->getGet('source'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'sort' => $sort,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $total = $this->leadModel->countWithFilters($filters);
        $leads = $this->leadModel->listWithFilters($filters);

        return $this->response->setJSON([
            'total' => $total,
            'rows' => $leads,
        ]);
    }

    public function create()
    {
        return view('admin/leads/form', [
            'pageTitle' => 'Nuevo Lead',
            'lead' => null,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function edit(string $id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to(base_url('admin/leads'))->with('error', 'Lead no encontrado.');
        }

        return view('admin/leads/form', [
            'pageTitle' => 'Editar Lead',
            'lead' => $lead,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function view(string $id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Lead no encontrado.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'lead' => $lead,
        ]);
    }

    public function save(?string $id = null)
    {
        $isUpdate = !empty($id);

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email',
            'phone' => 'permit_empty|max_length[30]',
            'event_date' => 'permit_empty|valid_date',
            'source' => 'permit_empty|max_length[50]',
            'status' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'phone' => trim((string) $this->request->getPost('phone')),
            'event_date' => $this->request->getPost('event_date') ?: null,
            'message' => $this->request->getPost('message'),
            'source' => trim((string) $this->request->getPost('source')),
            'status' => trim((string) $this->request->getPost('status')),
            'utm_payload' => $this->request->getPost('utm_payload'),
        ];

        $existingLead = null;
        if ($isUpdate) {
            $existingLead = $this->leadModel->find($id);
            if (!$existingLead) {
                return redirect()->to(base_url('admin/leads'))
                    ->with('error', 'Lead no encontrado.');
            }
        }

        if ($data['status'] === '') {
            $data['status'] = $isUpdate ? ($existingLead['status'] ?? 'new') : 'new';
        }

        if ($data['source'] === '') {
            $data['source'] = $isUpdate ? ($existingLead['source'] ?? 'website') : 'website';
        }

        if ($isUpdate) {
            $updated = $this->leadModel->update($id, $data);

            if ($updated) {
                return redirect()->to(base_url('admin/leads'))
                    ->with('success', 'Lead actualizado correctamente.');
            }

            return redirect()->back()->withInput()
                ->with('error', 'Error al actualizar el lead.');
        }

        $data['id'] = UserModel::generateUUID();
        $inserted = $this->leadModel->insert($data);

        if ($inserted) {
            return redirect()->to(base_url('admin/leads'))
                ->with('success', 'Lead creado correctamente.');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Error al crear el lead.');
    }

    public function updateStatus(string $id)
    {
        $status = trim((string) $this->request->getPost('status'));

        if ($status === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El estado es obligatorio.',
            ]);
        }

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Lead no encontrado.',
            ]);
        }

        $updated = $this->leadModel->updateStatus($id, $status);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Estado actualizado.',
                'status' => $status,
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo actualizar el estado.',
        ]);
    }

    public function convert(string $id)
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 6) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres.',
            ]);
        }

        $clientId = $this->leadModel->convertToClient($id, $password);

        if ($clientId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lead convertido a cliente.',
                'client_id' => $clientId,
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'No se pudo convertir el lead.',
        ]);
    }

    public function delete(string $id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Lead no encontrado.',
            ]);
        }

        $deleted = $this->leadModel->delete($id);

        if ($deleted) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lead eliminado correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo eliminar el lead.',
        ]);
    }
}
