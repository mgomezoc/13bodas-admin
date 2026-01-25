<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Clientes<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Clientes</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Clientes</h1>
        <p class="page-subtitle">Gestiona los clientes de la plataforma</p>
    </div>
    <a href="<?= base_url('admin/clients/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Cliente
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="btable-wrap btable-clients">
            <table
                id="clientsTable"
                data-toggle="table"
                data-url="<?= base_url('admin/clients/list') ?>"
                data-pagination="true"
                data-page-size="15"
                data-page-list="[15, 30, 50, 100]"
                data-search="true"
                data-search-align="left"
                data-show-refresh="true"
                data-show-columns="true"
                data-sort-name="created_at"
                data-sort-order="desc"
                data-locale="es-MX"
                data-response-handler="responseHandler"
                data-classes="table table-sm table-striped table-hover align-middle"
                class="w-100">
                <thead>
                    <tr>
                        <th data-field="full_name" data-sortable="true">Nombre</th>
                        <th data-field="email" data-sortable="true">Email</th>
                        <th data-field="phone">Teléfono</th>
                        <th data-field="event_count" data-sortable="true" data-align="center">Eventos</th>
                        <th data-field="is_active" data-formatter="statusFormatter" data-align="center">Estado</th>
                        <th data-field="last_login_at" data-formatter="dateTimeFormatter" data-sortable="true">Último Acceso</th>
                        <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function actionsFormatter(value, row) {
        return `
        <div class="action-buttons">
            <a href="${BASE_URL}admin/clients/view/${row.id}" class="btn btn-sm btn-outline-secondary" title="Ver">
                <i class="bi bi-eye"></i>
            </a>
            <a href="${BASE_URL}admin/clients/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-${row.is_active ? 'warning' : 'success'}" 
                    onclick="toggleStatus('${row.id}')" 
                    title="${row.is_active ? 'Desactivar' : 'Activar'}">
                <i class="bi bi-${row.is_active ? 'pause' : 'play'}"></i>
            </button>
        </div>
    `;
    }

    function toggleStatus(clientId) {
        Swal.fire({
            title: '¿Cambiar estado?',
            text: '¿Estás seguro de cambiar el estado de este cliente?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`${BASE_URL}admin/clients/toggle-status/${clientId}`)
                    .done(function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            $('#clientsTable').bootstrapTable('refresh');
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message
                            });
                        }
                    })
                    .fail(function() {
                        Toast.fire({
                            icon: 'error',
                            title: 'Error de conexión'
                        });
                    });
            }
        });
    }
</script>
<?= $this->endSection() ?>