<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Cortejo<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Cortejo</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Cortejo Nupcial</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal" onclick="openMemberModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Miembro
    </button>
</div>

<?php $activeTab = 'party'; ?>
<?= $this->include('admin/events/partials/modules_tabs') ?>

<div id="partyList">
<?php if (empty($members)): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state py-5">
            <i class="bi bi-hearts empty-state-icon"></i>
            <h4 class="empty-state-title">Sin miembros todavía</h4>
            <p class="empty-state-text">Agrega a los miembros del cortejo para mostrarlos en la invitación.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal" onclick="openMemberModal()">
                <i class="bi bi-plus-lg me-2"></i>Agregar Miembro
            </button>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Categoría</th>
                        <th>Orden</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($member['full_name']) ?></div>
                            <?php if (!empty($member['image_url'])): ?>
                                <small class="text-muted"><?= esc($member['image_url']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($member['role'] ?? '-') ?></td>
                        <td><?= esc($categories[$member['category']] ?? $member['category']) ?></td>
                        <td><?= esc($member['display_order'] ?? '-') ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openMemberModal(<?= json_encode($member) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMember('<?= $member['id'] ?>')" title="Eliminar">
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
<?php endif; ?>
</div>

<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="memberForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-hearts me-2"></i><span id="memberModalTitle">Nuevo Miembro</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="member_id" id="member_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol</label>
                            <input type="text" name="role" id="role" class="form-control" placeholder="Ej: Dama de honor">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="category" id="category" class="form-select" required>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Orden de Visualización</label>
                            <input type="number" name="display_order" id="display_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" id="bio" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL Imagen</label>
                            <input type="url" name="image_url" id="image_url" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Social Links (JSON)</label>
                            <input type="text" name="social_links" id="social_links" class="form-control" placeholder='{"instagram":"https://..."}'>
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

function openMemberModal(member = null) {
    $('#memberForm')[0].reset();
    $('#member_id').val('');
    $('#memberModalTitle').text(member ? 'Editar Miembro' : 'Nuevo Miembro');

    if (member) {
        $('#member_id').val(member.id);
        $('#full_name').val(member.full_name);
        $('#role').val(member.role);
        $('#category').val(member.category);
        $('#display_order').val(member.display_order);
        $('#bio').val(member.bio);
        $('#image_url').val(member.image_url);
        $('#social_links').val(member.social_links);
    }

    const modal = new bootstrap.Modal(document.getElementById('memberModal'));
    modal.show();
}

$('#memberForm').on('submit', function(e) {
    e.preventDefault();
    const memberId = $('#member_id').val();
    const url = memberId
        ? `${BASE_URL}admin/events/${eventId}/party/update/${memberId}`
        : `${BASE_URL}admin/events/${eventId}/party/store`;

    $.post(url, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                refreshModuleSection('#partyList');
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar' });
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
});

function deleteMember(memberId) {
    Swal.fire({
        title: '¿Eliminar miembro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/party/delete/${memberId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        refreshModuleSection('#partyList');
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
