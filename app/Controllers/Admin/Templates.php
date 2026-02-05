<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemplateModel;

class Templates extends BaseController
{
    protected TemplateModel $templateModel;

    public function __construct()
    {
        $this->templateModel = new TemplateModel();
    }

    public function index()
    {
        return view('admin/templates/index', [
            'pageTitle' => 'Templates',
        ]);
    }

    public function list()
    {
        $filters = [
            'search'    => $this->request->getGet('search'),
            'is_active' => $this->request->getGet('is_active'),
            'is_public' => $this->request->getGet('is_public'),
        ];

        $templates = $this->templateModel->listWithUsageCount($filters);

        return $this->response->setJSON([
            'total' => count($templates),
            'rows'  => $templates,
        ]);
    }

    public function create()
    {
        return view('admin/templates/form', [
            'pageTitle' => 'Nuevo Template',
            'template'  => null,
            'errors'    => session('errors') ?? [],
        ]);
    }

    public function edit(int $id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to(base_url('admin/templates'))
                ->with('error', 'Template no encontrado.');
        }

        return view('admin/templates/form', [
            'pageTitle' => 'Editar Template',
            'template'  => $template,
            'errors'    => session('errors') ?? [],
        ]);
    }

    public function save(?int $id = null)
    {
        $isUpdate = !empty($id);

        $rules = [
            'code'         => $isUpdate
                ? "required|max_length[80]|is_unique[templates.code,id,{$id}]"
                : 'required|max_length[80]|is_unique[templates.code]',
            'name'         => 'required|max_length[120]',
            'description'  => 'permit_empty|max_length[500]',
            'preview_url'  => 'permit_empty|valid_url',
            'thumbnail_url'=> 'permit_empty|valid_url',
            'sort_order'   => 'permit_empty|integer',
            'is_public'    => 'required|in_list[0,1]',
            'is_active'    => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schemaJson = trim((string) $this->request->getPost('schema_json'));
        $metaJson   = trim((string) $this->request->getPost('meta_json'));

        $jsonErrors = [];
        if ($schemaJson !== '' && json_decode($schemaJson, true) === null && json_last_error() !== JSON_ERROR_NONE) {
            $jsonErrors['schema_json'] = 'Schema JSON no es válido.';
        }
        if ($metaJson !== '' && json_decode($metaJson, true) === null && json_last_error() !== JSON_ERROR_NONE) {
            $jsonErrors['meta_json'] = 'Meta JSON no es válido.';
        }

        if (!empty($jsonErrors)) {
            return redirect()->back()->withInput()->with('errors', $jsonErrors);
        }

        $data = [
            'code'          => trim((string) $this->request->getPost('code')),
            'name'          => trim((string) $this->request->getPost('name')),
            'description'   => $this->request->getPost('description'),
            'preview_url'   => $this->request->getPost('preview_url'),
            'thumbnail_url' => $this->request->getPost('thumbnail_url'),
            'schema_json'   => $schemaJson !== '' ? $schemaJson : null,
            'meta_json'     => $metaJson !== '' ? $metaJson : null,
            'sort_order'    => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_public'     => (int) $this->request->getPost('is_public'),
            'is_active'     => (int) $this->request->getPost('is_active'),
        ];

        if ($isUpdate) {
            $updated = $this->templateModel->update($id, $data);

            if ($updated) {
                return redirect()->to(base_url('admin/templates'))
                    ->with('success', 'Template actualizado correctamente.');
            }

            return redirect()->back()->withInput()
                ->with('error', 'Error al actualizar el template.');
        }

        $inserted = $this->templateModel->insert($data);

        if ($inserted) {
            return redirect()->to(base_url('admin/templates'))
                ->with('success', 'Template creado correctamente.');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Error al crear el template.');
    }

    public function delete(int $id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Template no encontrado.',
                ]);
            }

            return redirect()->back()->with('error', 'Template no encontrado.');
        }

        $usage = $this->templateModel->isTemplateInUse($id);
        if ($usage['in_use'] ?? false) {
            $message = 'No se puede eliminar: este template está asignado a eventos.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $message,
                ]);
            }

            return redirect()->back()->with('error', $message);
        }

        $deleted = $this->templateModel->delete($id);

        if ($deleted) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Template eliminado correctamente.',
                ]);
            }

            return redirect()->to(base_url('admin/templates'))
                ->with('success', 'Template eliminado correctamente.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo eliminar el template.',
            ]);
        }

        return redirect()->back()->with('error', 'No se pudo eliminar el template.');
    }

    public function toggleActive(int $id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Template no encontrado.',
            ]);
        }

        $newStatus = $template['is_active'] ? 0 : 1;
        $updated = $this->templateModel->update($id, ['is_active' => $newStatus]);

        if ($updated) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => $newStatus ? 'Template activado.' : 'Template desactivado.',
                'is_active' => $newStatus,
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se pudo actualizar el estado.',
        ]);
    }

    public function togglePublic(int $id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Template no encontrado.',
            ]);
        }

        $newStatus = $template['is_public'] ? 0 : 1;
        $updated = $this->templateModel->update($id, ['is_public' => $newStatus]);

        if ($updated) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => $newStatus ? 'Template público.' : 'Template privado.',
                'is_public' => $newStatus,
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se pudo actualizar el estado.',
        ]);
    }
}
