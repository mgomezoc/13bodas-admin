<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Opciones de Menú<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Menú</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Opciones de Menú</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOptionModal">
        <i class="bi bi-plus-lg me-2"></i>Agregar Opción
    </button>
</div>

<?= view('admin/events/partials/_event_navigation', ['active' => 'menu', 'event_id' => $event['id']]) ?>

<div id="menuList" class="card">
    <div class="card-body">
        <?php if (empty($options)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-cup-hot empty-state-icon"></i>
                <h5 class="empty-state-title">Sin opciones de menú</h5>
                <p class="empty-state-text">Agrega las opciones de comida que tus invitados podrán elegir al confirmar.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                    <i class="bi bi-plus-lg me-2"></i>Agregar Primera Opción
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Opción</th>
                            <th>Descripción</th>
                            <th class="text-center">Características</th>
                            <?php if (!empty($hasIsActive)): ?>
                                <th class="text-center">Estado</th>
                            <?php endif; ?>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($options as $option): ?>
                        <tr>
                            <td><strong><?= esc($option['name']) ?></strong></td>
                            <td class="text-muted"><?= esc($option['description']) ?: '-' ?></td>
                            <td class="text-center">
                                <?php if ($option['is_vegan']): ?>
                                    <span class="badge bg-success me-1" title="Vegano"><i class="bi bi-leaf"></i></span>
                                <?php endif; ?>
                                <?php if ($option['is_gluten_free']): ?>
                                    <span class="badge bg-warning text-dark me-1" title="Sin Gluten">GF</span>
                                <?php endif; ?>
                                <?php if ($option['is_kid_friendly']): ?>
                                    <span class="badge bg-info me-1" title="Para Niños"><i class="bi bi-emoji-smile"></i></span>
                                <?php endif; ?>
                            </td>
                            <?php if (!empty($hasIsActive)): ?>
                                <td class="text-center">
                                    <?php if (!empty($option['is_active'])): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editOption(<?= htmlspecialchars(json_encode($option)) ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger delete-item"
                                        data-id="<?= $option['id'] ?>"
                                        data-name="<?= esc($option['name']) ?>"
                                        data-endpoint="<?= base_url('admin/events/' . $event['id'] . '/menu/delete/' . $option['id']) ?>"
                                        data-refresh-target="#menuList">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addOptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="optionForm"
                class="modal-ajax-form"
                data-refresh-target="#menuList"
                action="<?= base_url('admin/events/' . $event['id'] . '/menu/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Agregar Opción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="optionId" name="option_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="optionName" name="name" class="form-control" required placeholder="Ej: Pollo en mole">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="optionDescription" name="description" class="form-control" rows="2" placeholder="Descripción breve del platillo..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Características</label>
                        <div class="form-check">
                            <input type="checkbox" id="isVegan" name="is_vegan" class="form-check-input" value="1">
                            <label class="form-check-label" for="isVegan"><i class="bi bi-leaf text-success me-1"></i>Vegano</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="isGlutenFree" name="is_gluten_free" class="form-check-input" value="1">
                            <label class="form-check-label" for="isGlutenFree">Sin Gluten</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="isKidFriendly" name="is_kid_friendly" class="form-check-input" value="1">
                            <label class="form-check-label" for="isKidFriendly"><i class="bi bi-emoji-smile text-info me-1"></i>Para Niños</label>
                        </div>
                    </div>
                    
                    <?php if (!empty($hasIsActive)): ?>
                        <div id="activeField" class="mb-3" style="display: none;">
                            <div class="form-check">
                                <input type="checkbox" id="isActive" name="is_active" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="isActive">Activo</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
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
const modal = new bootstrap.Modal(document.getElementById('addOptionModal'));

$('#addOptionModal').on('show.bs.modal', function(e) {
    if (!$(e.relatedTarget).length) return;
    $('#optionForm')[0].reset();
    $('#optionId').val('');
    $('#modalTitle').text('Agregar Opción');
    $('#activeField').hide();
    document.getElementById('optionForm').action = `${BASE_URL}admin/events/${eventId}/menu/store`;
});

function editOption(option) {
    $('#optionId').val(option.id);
    $('#optionName').val(option.name);
    $('#optionDescription').val(option.description);
    $('#isVegan').prop('checked', option.is_vegan == 1);
    $('#isGlutenFree').prop('checked', option.is_gluten_free == 1);
    $('#isKidFriendly').prop('checked', option.is_kid_friendly == 1);
    $('#isActive').prop('checked', option.is_active == 1);
    $('#modalTitle').text('Editar Opción');
    $('#activeField').show();
    document.getElementById('optionForm').action = `${BASE_URL}admin/events/${eventId}/menu/update/${option.id}`;
    modal.show();
}
</script>
<?= $this->endSection() ?>
