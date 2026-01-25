<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Invitados<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Invitados</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Invitados</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests/import') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-upload me-2"></i>Importar
        </a>
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests/export') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-download me-2"></i>Exportar
        </a>
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Invitado
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['accepted'] ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['declined'] ?></div>
                <div class="stat-label">No Asisten</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><i class="bi bi-info-circle me-1"></i>Información</a></li>
    <li class="nav-item"><button class="nav-link active" type="button"><i class="bi bi-people me-1"></i>Invitados</button></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>"><i class="bi bi-check2-square me-1"></i>RSVPs</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>"><i class="bi bi-images me-1"></i>Galería</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/registry') ?>"><i class="bi bi-gift me-1"></i>Regalos</a></li>
</ul>

<div class="card">
    <div class="card-body">
        <table 
            id="guestsTable"
            data-toggle="table"
            data-url="<?= base_url('admin/events/' . $event['id'] . '/guests/list') ?>"
            data-pagination="true"
            data-page-size="25"
            data-search="true"
            data-search-align="left"
            data-show-refresh="true"
            data-sort-name="group_name"
            data-sort-order="asc"
            data-locale="es-MX"
            data-response-handler="responseHandler"
            class="table table-hover">
            <thead>
                <tr>
                    <th data-field="first_name" data-sortable="true" data-formatter="nameFormatter">Nombre</th>
                    <th data-field="group_name" data-sortable="true">Grupo</th>
                    <th data-field="email">Email</th>
                    <th data-field="phone_number">Teléfono</th>
                    <th data-field="rsvp_status" data-formatter="rsvpFormatter" data-align="center">RSVP</th>
                    <th data-field="is_child" data-formatter="childFormatter" data-align="center">Tipo</th>
                    <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = '<?= $event['id'] ?>';

function nameFormatter(value, row) {
    let name = `${row.first_name} ${row.last_name}`;
    if (row.is_primary_contact == 1) {
        name += ' <i class="bi bi-star-fill text-warning" title="Contacto principal"></i>';
    }
    return name;
}

function rsvpFormatter(value, row) {
    const statusMap = {
        'pending': { class: 'bg-warning', label: 'Pendiente' },
        'accepted': { class: 'bg-success', label: 'Confirmado' },
        'declined': { class: 'bg-danger', label: 'No Asiste' }
    };
    const status = statusMap[value] || { class: 'bg-secondary', label: value };
    return `<span class="badge ${status.class}">${status.label}</span>`;
}

function childFormatter(value, row) {
    return value == 1 ? '<span class="badge bg-info">Niño</span>' : '<span class="badge bg-light text-dark">Adulto</span>';
}

function actionsFormatter(value, row) {
    return `
        <div class="action-buttons">
            <a href="${BASE_URL}admin/events/${eventId}/guests/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteGuest('${row.id}')" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

function deleteGuest(guestId) {
    Swal.fire({
        title: '¿Eliminar invitado?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/guests/delete/${guestId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        $('#guestsTable').bootstrapTable('refresh');
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
