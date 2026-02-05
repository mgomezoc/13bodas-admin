<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Agenda<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Agenda</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Agenda del Evento</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="openScheduleModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Actividad
    </button>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><i class="bi bi-info-circle me-1"></i>Información</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/locations') ?>"><i class="bi bi-geo me-1"></i>Ubicaciones</a></li>
    <li class="nav-item"><button class="nav-link active" type="button"><i class="bi bi-clock me-1"></i>Agenda</button></li>
</ul>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Actividad</th>
                        <th>Horario</th>
                        <th>Ubicación</th>
                        <th>Visible</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($item['title']) ?></div>
                            <small class="text-muted"><?= esc($item['description'] ?? '') ?></small>
                        </td>
                        <td>
                            <?= esc($item['starts_at']) ?>
                            <?php if (!empty($item['ends_at'])): ?>
                                <br><small class="text-muted">hasta <?= esc($item['ends_at']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($locationMap[$item['location_id']] ?? '-') ?></td>
                        <td><?= $item['is_visible'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openScheduleModal(<?= json_encode($item) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteScheduleItem('<?= $item['id'] ?>')" title="Eliminar">
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

<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="scheduleForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-clock me-2"></i><span id="scheduleModalTitle">Nueva Actividad</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Icono</label>
                            <input type="text" name="icon" id="icon" class="form-control" placeholder="bi-alarm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Inicio <span class="text-danger">*</span></label>
                            <input type="text" name="starts_at" id="starts_at" class="form-control datetimepicker" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fin</label>
                            <input type="text" name="ends_at" id="ends_at" class="form-control datetimepicker">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ubicación</label>
                            <select name="location_id" id="location_id" class="form-select">
                                <option value="">Sin ubicación</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Visible</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible" checked>
                                <label class="form-check-label" for="is_visible">Mostrar</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
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

function openScheduleModal(item = null) {
    $('#scheduleForm')[0].reset();
    $('#item_id').val('');
    $('#scheduleModalTitle').text(item ? 'Editar Actividad' : 'Nueva Actividad');
    $('#is_visible').prop('checked', true);

    if (item) {
        $('#item_id').val(item.id);
        $('#title').val(item.title);
        $('#icon').val(item.icon);
        $('#starts_at').val(item.starts_at);
        $('#ends_at').val(item.ends_at);
        $('#location_id').val(item.location_id);
        $('#sort_order').val(item.sort_order);
        $('#is_visible').prop('checked', parseInt(item.is_visible) === 1);
        $('#description').val(item.description);
    }

    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
}

$('#scheduleForm').on('submit', function(e) {
    e.preventDefault();
    const itemId = $('#item_id').val();
    const url = itemId
        ? `${BASE_URL}admin/events/${eventId}/schedule/update/${itemId}`
        : `${BASE_URL}admin/events/${eventId}/schedule/store`;

    $.post(url, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                setTimeout(() => location.reload(), 600);
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar' });
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
});

function deleteScheduleItem(itemId) {
    Swal.fire({
        title: '¿Eliminar actividad?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/schedule/delete/${itemId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        setTimeout(() => location.reload(), 600);
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
