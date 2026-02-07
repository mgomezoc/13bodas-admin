<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Recomendaciones<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Recomendaciones</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Recomendaciones</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recModal" onclick="openRecModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Recomendación
    </button>
</div>

<?php $activeTab = 'recommendations'; ?>
<?= $this->include('admin/events/partials/modules_tabs') ?>

<div id="recommendationsList" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Título</th>
                        <th>URL</th>
                        <th>Visible</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= esc($item['type']) ?></td>
                        <td>
                            <div class="fw-semibold"><?= esc($item['title']) ?></div>
                            <small class="text-muted"><?= esc($item['description'] ?? '') ?></small>
                        </td>
                        <td><?= esc($item['url'] ?? '-') ?></td>
                        <td><?= $item['is_visible'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openRecModal(<?= json_encode($item) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRec('<?= $item['id'] ?>')" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="recModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="recForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-star me-2"></i><span id="recModalTitle">Nueva Recomendación</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="hotel">Hotel</option>
                                <option value="transport">Transporte</option>
                                <option value="restaurant">Restaurante</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL</label>
                            <input type="url" name="url" id="url" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL Imagen</label>
                            <input type="url" name="image_url" id="image_url" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Visible</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible" checked>
                                <label class="form-check-label" for="is_visible">Mostrar</label>
                            </div>
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
<script>
const eventId = '<?= $event['id'] ?>';

function openRecModal(item = null) {
    $('#recForm')[0].reset();
    $('#item_id').val('');
    $('#recModalTitle').text(item ? 'Editar Recomendación' : 'Nueva Recomendación');
    $('#is_visible').prop('checked', true);

    if (item) {
        $('#item_id').val(item.id);
        $('#type').val(item.type);
        $('#title').val(item.title);
        $('#description').val(item.description);
        $('#url').val(item.url);
        $('#image_url').val(item.image_url);
        $('#sort_order').val(item.sort_order);
        $('#is_visible').prop('checked', parseInt(item.is_visible) === 1);
    }

    const modal = new bootstrap.Modal(document.getElementById('recModal'));
    modal.show();
}

$('#recForm').on('submit', function(e) {
    e.preventDefault();
    const itemId = $('#item_id').val();
    const url = itemId
        ? `${BASE_URL}admin/events/${eventId}/recommendations/update/${itemId}`
        : `${BASE_URL}admin/events/${eventId}/recommendations/store`;

    $.post(url, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                refreshModuleSection('#recommendationsList');
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar' });
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
});

function deleteRec(itemId) {
    Swal.fire({
        title: '¿Eliminar recomendación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/recommendations/delete/${itemId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        refreshModuleSection('#recommendationsList');
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
