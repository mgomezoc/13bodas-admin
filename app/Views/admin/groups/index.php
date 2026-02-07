<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Grupos<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Grupos</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Grupos de Invitados</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#groupModal" onclick="openGroupModal()">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Grupo
    </button>
</div>

<?php $activeTab = 'groups'; ?>
<?= $this->include('admin/events/partials/modules_tabs') ?>

<div class="card">
    <div class="card-body">
        <table
            id="groupsTable"
            data-toggle="table"
            data-url="<?= base_url('admin/events/' . $event['id'] . '/groups/list') ?>"
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
                    <th data-field="group_name" data-sortable="true">Grupo</th>
                    <th data-field="access_code">Código</th>
                    <th data-field="guest_count" data-align="center">Invitados</th>
                    <th data-field="max_additional_guests" data-align="center">Adicionales</th>
                    <th data-field="is_vip" data-align="center" data-formatter="vipFormatter">VIP</th>
                    <th data-field="current_status" data-align="center" data-formatter="statusFormatter">Estado</th>
                    <th data-field="id" data-align="right" data-formatter="actionsFormatter">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="groupForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-collection me-2"></i><span id="groupModalTitle">Nuevo Grupo</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="group_id" id="group_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Grupo <span class="text-danger">*</span></label>
                            <input type="text" name="group_name" id="group_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código de Acceso</label>
                            <input type="text" name="access_code" id="access_code" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Máx. Invitados Adicionales</label>
                            <input type="number" name="max_additional_guests" id="max_additional_guests" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="current_status" id="current_status" class="form-select">
                                <option value="invited">Invitado</option>
                                <option value="viewed">Visto</option>
                                <option value="partial">Parcial</option>
                                <option value="responded">Respondido</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">VIP</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_vip" id="is_vip">
                                <label class="form-check-label" for="is_vip">Grupo VIP</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invitado en</label>
                            <input type="text" name="invited_at" id="invited_at" class="form-control datetimepicker">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primer Visto</label>
                            <input type="text" name="first_viewed_at" id="first_viewed_at" class="form-control datetimepicker">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Último Visto</label>
                            <input type="text" name="last_viewed_at" id="last_viewed_at" class="form-control datetimepicker">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Respondido en</label>
                            <input type="text" name="responded_at" id="responded_at" class="form-control datetimepicker">
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

function vipFormatter(value) {
    return parseInt(value) === 1 ? '<span class="badge bg-warning text-dark">VIP</span>' : '-';
}

function statusFormatter(value) {
    const map = {
        invited: { label: 'Invitado', class: 'bg-secondary' },
        viewed: { label: 'Visto', class: 'bg-info' },
        partial: { label: 'Parcial', class: 'bg-warning text-dark' },
        responded: { label: 'Respondido', class: 'bg-success' }
    };
    const info = map[value] || { label: value, class: 'bg-secondary' };
    return `<span class="badge ${info.class}">${info.label}</span>`;
}

function actionsFormatter(value, row) {
    return `
        <div class="action-buttons">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openGroupModal(${JSON.stringify(row)})' title="Editar">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteGroup('${row.id}')" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

function openGroupModal(group = null) {
    $('#groupForm')[0].reset();
    $('#group_id').val('');
    $('#groupModalTitle').text(group ? 'Editar Grupo' : 'Nuevo Grupo');

    if (group) {
        $('#group_id').val(group.id);
        $('#group_name').val(group.group_name);
        $('#access_code').val(group.access_code);
        $('#max_additional_guests').val(group.max_additional_guests);
        $('#current_status').val(group.current_status);
        $('#is_vip').prop('checked', parseInt(group.is_vip) === 1);
        $('#invited_at').val(group.invited_at || '');
        $('#first_viewed_at').val(group.first_viewed_at || '');
        $('#last_viewed_at').val(group.last_viewed_at || '');
        $('#responded_at').val(group.responded_at || '');
    }

    const modal = new bootstrap.Modal(document.getElementById('groupModal'));
    modal.show();
}

$('#groupForm').on('submit', function(e) {
    e.preventDefault();
    const groupId = $('#group_id').val();
    const url = groupId
        ? `${BASE_URL}admin/events/${eventId}/groups/update/${groupId}`
        : `${BASE_URL}admin/events/${eventId}/groups/store`;

    $.post(url, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                $('#groupsTable').bootstrapTable('refresh');
                bootstrap.Modal.getInstance(document.getElementById('groupModal')).hide();
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar' });
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
});

function deleteGroup(groupId) {
    Swal.fire({
        title: '¿Eliminar grupo?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/groups/delete/${groupId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        $('#groupsTable').bootstrapTable('refresh');
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
