<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Ubicaciones<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Ubicaciones</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Ubicaciones del Evento</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationModal" onclick="openLocationModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Ubicación
    </button>
</div>

<?php $activeTab = 'locations'; ?>
<?= $this->include('admin/events/partials/modules_tabs') ?>

<div id="locationsList" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Orden</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                    <tr>
                        <td><?= esc($location['code']) ?></td>
                        <td><?= esc($location['name']) ?></td>
                        <td><?= esc($location['address'] ?? '-') ?></td>
                        <td><?= esc($location['sort_order'] ?? 0) ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openLocationModal(<?= json_encode($location) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLocation('<?= $location['id'] ?>')" title="Eliminar">
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

<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="locationForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-geo me-2"></i><span id="locationModalTitle">Nueva Ubicación</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="location_id" id="location_id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control" placeholder="ceremony" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitud</label>
                            <input type="text" name="geo_lat" id="geo_lat" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitud</label>
                            <input type="text" name="geo_lng" id="geo_lng" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Google Maps URL</label>
                            <input type="url" name="maps_url" id="maps_url" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waze URL</label>
                            <input type="url" name="waze_url" id="waze_url" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0">
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

function openLocationModal(location = null) {
    $('#locationForm')[0].reset();
    $('#location_id').val('');
    $('#locationModalTitle').text(location ? 'Editar Ubicación' : 'Nueva Ubicación');

    if (location) {
        $('#location_id').val(location.id);
        $('#code').val(location.code);
        $('#name').val(location.name);
        $('#address').val(location.address);
        $('#geo_lat').val(location.geo_lat);
        $('#geo_lng').val(location.geo_lng);
        $('#maps_url').val(location.maps_url);
        $('#waze_url').val(location.waze_url);
        $('#notes').val(location.notes);
        $('#sort_order').val(location.sort_order);
    }

    const modal = new bootstrap.Modal(document.getElementById('locationModal'));
    modal.show();
}

$('#locationForm').on('submit', function(e) {
    e.preventDefault();
    const locationId = $('#location_id').val();
    const url = locationId
        ? `${BASE_URL}admin/events/${eventId}/locations/update/${locationId}`
        : `${BASE_URL}admin/events/${eventId}/locations/store`;

    $.post(url, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                refreshModuleSection('#locationsList');
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar' });
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
});

function deleteLocation(locationId) {
    Swal.fire({
        title: '¿Eliminar ubicación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/locations/delete/${locationId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        refreshModuleSection('#locationsList');
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
