<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Editar Invitado<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>">Invitados</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Editar Invitado</h1>
        <p class="page-subtitle"><?= esc($guest['first_name'] . ' ' . $guest['last_name']) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('admin/events/' . $event['id'] . '/guests/update/' . $guest['id']) ?>" method="POST" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <h6 class="text-muted mb-3"><i class="bi bi-person me-2"></i>Datos del Invitado</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="first_name">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?= esc($guest['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="last_name">Apellido <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?= esc($guest['last_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= esc($guest['email']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="phone_number">Teléfono</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" value="<?= esc($guest['phone_number']) ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" id="is_child" name="is_child" class="form-check-input" value="1" <?= $guest['is_child'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_child">Es niño/menor de edad</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" id="is_primary_contact" name="is_primary_contact" class="form-check-input" value="1" <?= $guest['is_primary_contact'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_primary_contact">Contacto principal</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <h6 class="text-muted mb-3"><i class="bi bi-people me-2"></i>Grupo</h6>
                    
                    <div class="mb-3">
                        <label class="form-label" for="group_id">Grupo Asignado</label>
                        <select id="group_id" name="group_id" class="form-select select2" required>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>" <?= $guest['group_id'] === $group['id'] ? 'selected' : '' ?>>
                                    <?= esc($group['group_name']) ?> (<?= $group['access_code'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-light">
                        <strong>Estado RSVP:</strong>
                        <?php
                        $rsvpBadge = match($guest['rsvp_status']) {
                            'accepted' => '<span class="badge bg-success">Confirmado</span>',
                            'declined' => '<span class="badge bg-danger">No Asiste</span>',
                            default => '<span class="badge bg-warning">Pendiente</span>'
                        };
                        echo $rsvpBadge;
                        ?>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
