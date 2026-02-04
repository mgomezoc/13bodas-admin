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
        return view('admin/templates/index', ['pageTitle' => 'Templates']);
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
        ]);
    }

    public function edit(int $id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to(base_url('admin/templates'))->with('error', 'Template no encontrado.');
        }

        return view('admin/templates/form', [
            'pageTitle' => 'Editar Template',
            'template'  => $template,
        ]);
    }

    public function save(?int $id = null)
    {
        $isUpdate = !empty($id);

        $rules = [
            'code'        => $isUpdate ? "required|max_length[50]|is_unique[templates.code,id,{$id}]" : 'required|max_length[50]|is_unique[templates.code]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
            'preview_url' => 'permit_empty|max_length[255]',
            'thumbnail_url' => 'permit_empty|max_length[255]',
            'sort_order'  => 'permit_empty|integer',
            'is_public'   => 'required|in_list[0,1]',
            'is_active'   => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'code'          => $this->request->getPost('code'),
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'preview_url'   => $this->request->getPost('preview_url'),
            'thumbnail_url' => $this->request->getPost('thumbnail_url'),
            'sort_order'    => $this->request->getPost('sort_order') ?: 0,
            'is_public'     => $this->request->getPost('is_public'),
            'is_active'     => $this->request->getPost('is_active'),
        ];

        $schemaJson = trim($this->request->getPost('schema_json'));
        if (!empty($schemaJson)) {
            json_decode($schemaJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->withInput()->with('errors', ['schema_json' => 'El Schema JSON no es válido: ' . json_last_error_msg()]);
            }
            $data['schema_json'] = $schemaJson;
        }

        $metaJson = trim($this->request->getPost('meta_json'));
        if (!empty($metaJson)) {
            json_decode($metaJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->withInput()->with('errors', ['meta_json' => 'El Meta JSON no es válido: ' . json_last_error_msg()]);
            }
            $data['meta_json'] = $metaJson;
        }

        if ($isUpdate) {
            $updated = $this->templateModel->update($id, $data);
            if ($updated) {
                return redirect()->to(base_url('admin/templates'))->with('success', 'Template actualizado correctamente.');
            }
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el template.');
        } else {
            $inserted = $this->templateModel->insert($data);
            if ($inserted) {
                return redirect()->to(base_url('admin/templates'))->with('success', 'Template creado correctamente.');
            }
            return redirect()->back()->withInput()->with('error', 'Error al crear el template.');
        }
    }

    public function delete(int $id)
    {
        try {
            $template = $this->templateModel->find($id);

            if (!$template) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Template no encontrado.'
                    ]);
                }
                return redirect()->back()->with('error', 'Template no encontrado.');
            }

            $usage = $this->templateModel->isTemplateInUse($id);

            if ($usage['in_use']) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "No se puede eliminar: Este template está siendo usado por {$usage['count']} evento(s)."
                    ]);
                }
                return redirect()->back()->with('error', "No se puede eliminar: Este template está siendo usado por {$usage['count']} evento(s).");
            }

            $deleted = $this->templateModel->delete($id);

            if ($deleted) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Template eliminado correctamente.'
                    ]);
                }
                return redirect()->to(base_url('admin/templates'))->with('success', 'Template eliminado correctamente.');
            }

            throw new \Exception('No se pudo eliminar el template.');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Error al eliminar el template.');
        }
    }

    public function toggleActive(int $id)
    {
        try {
            $template = $this->templateModel->find($id);

            if (!$template) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Template no encontrado.'
                ]);
            }

            $newStatus = $template['is_active'] ? 0 : 1;
            $updated   = $this->templateModel->update($id, ['is_active' => $newStatus]);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $newStatus ? 'Template activado.' : 'Template desactivado.',
                    'is_active' => $newStatus,
                ]);
            }

            throw new \Exception('No se pudo cambiar el estado.');

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function togglePublic(int $id)
    {
        try {
            $template = $this->templateModel->find($id);

            if (!$template) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Template no encontrado.'
                ]);
            }

            $newStatus = $template['is_public'] ? 0 : 1;
            $updated   = $this->templateModel->update($id, ['is_public' => $newStatus]);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $newStatus ? 'Template público.' : 'Template privado.',
                    'is_public' => $newStatus,
                ]);
            }

            throw new \Exception('No se pudo cambiar la visibilidad.');

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
