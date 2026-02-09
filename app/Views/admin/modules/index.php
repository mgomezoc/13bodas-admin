<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Módulos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Módulos</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Módulos de Contenido</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
</div>

<?= view('admin/events/partials/_event_navigation', ['active' => 'modulos', 'event_id' => $event['id']]) ?>

<div id="modulesList" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>CSS ID</th>
                        <th>Orden</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?= esc($moduleTypes[$module['module_type']] ?? $module['module_type']) ?></td>
                        <td><?= esc($module['css_id'] ?? '-') ?></td>
                        <td><?= esc($module['sort_order']) ?></td>
                        <td><?= $module['is_enabled'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openModuleModal(<?= json_encode($module) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="moduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="moduleForm"
                class="modal-ajax-form"
                data-refresh-target="#modulesList"
                action="<?= base_url('admin/events/' . $event['id'] . '/modules/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-grid me-2"></i><span id="moduleModalTitle">Editar Módulo</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="module_id" id="module_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CSS ID</label>
                            <input type="text" name="css_id" id="css_id" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Activo</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_enabled" id="is_enabled">
                                <label class="form-check-label" for="is_enabled">Sí</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Payload (JSON)</label>
                            <textarea name="content_payload" id="content_payload" class="form-control" rows="6" placeholder='{"key":"value"}'></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<script>
const eventId = '<?= $event['id'] ?>';

function openModuleModal(module) {
    $('#moduleForm')[0].reset();
    $('#module_id').val(module.id);
    $('#css_id').val(module.css_id);
    $('#sort_order').val(module.sort_order);
    $('#is_enabled').prop('checked', parseInt(module.is_enabled) === 1);
    $('#content_payload').val(module.content_payload || '');
    document.getElementById('moduleForm').action = `${BASE_URL}admin/events/${eventId}/modules/update/${module.id}`;

    const modal = new bootstrap.Modal(document.getElementById('moduleModal'));
    modal.show();
}

</script>
<?= $this->endSection() ?>
