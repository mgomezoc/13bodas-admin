<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nuevo Invitado<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>">Invitados</a></li>
        <li class="breadcrumb-item active">Nuevo</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Nuevo Invitado</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('admin/events/' . $event['id'] . '/guests/store') ?>" method="POST" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <h6 class="text-muted mb-3"><i class="bi bi-person me-2"></i>Datos del Invitado</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="first_name">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?= old('first_name') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="last_name">Apellido <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?= old('last_name') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= old('email') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="phone_number">Teléfono</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" value="<?= old('phone_number') ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" id="is_child" name="is_child" class="form-check-input" value="1" <?= old('is_child') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_child">Es niño/menor de edad</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" id="is_primary_contact" name="is_primary_contact" class="form-check-input" value="1" <?= old('is_primary_contact', true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_primary_contact">Contacto principal del grupo</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <h6 class="text-muted mb-3"><i class="bi bi-people me-2"></i>Grupo / Familia</h6>
                    
                    <div class="mb-3">
                        <label class="form-label" for="group_id">Asignar a Grupo</label>
                        <select id="group_id" name="group_id" class="form-select select2">
                            <option value="new">+ Crear nuevo grupo</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>"><?= esc($group['group_name']) ?> (<?= $group['access_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="newGroupFields">
                        <div class="mb-3">
                            <label class="form-label" for="new_group_name">Nombre del Grupo</label>
                            <input type="text" id="new_group_name" name="new_group_name" class="form-control" placeholder="Ej: Familia Pérez">
                            <div class="form-text">Si dejas vacío, se usará el nombre del invitado</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" for="max_additional_guests">Acompañantes Permitidos</label>
                            <input type="number" id="max_additional_guests" name="max_additional_guests" class="form-control" value="0" min="0" max="10">
                            <div class="form-text">Número de "Más Uno" que puede agregar</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Los invitados del mismo grupo comparten un código de acceso para confirmar asistencia juntos.
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>Agregar Invitado
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    toggleNewGroupFields();
    
    $('#group_id').on('change', toggleNewGroupFields);
});

function toggleNewGroupFields() {
    if ($('#group_id').val() === 'new') {
        $('#newGroupFields').show();
    } else {
        $('#newGroupFields').hide();
    }
}
</script>
<?= $this->endSection() ?>
