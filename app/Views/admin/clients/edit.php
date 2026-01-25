<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Editar Cliente<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/clients') ?>">Clientes</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Editar Cliente</h1>
        <p class="page-subtitle"><?= esc($client['full_name']) ?></p>
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
        <form action="<?= base_url('admin/clients/update/' . $client['id']) ?>" method="POST" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-person me-2"></i>Información del Usuario
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label" for="full_name">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               class="form-control" 
                               value="<?= old('full_name', $client['full_name']) ?>"
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?= old('email', $client['email']) ?>"
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="password">Nueva Contraseña</label>
                        <input type="text" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Dejar vacío para mantener la actual">
                        <div class="form-text">Solo completa este campo si deseas cambiar la contraseña.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-control" 
                               value="<?= old('phone', $client['phone']) ?>">
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-building me-2"></i>Información Adicional
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label" for="company_name">Empresa / Referencia</label>
                        <input type="text" 
                               id="company_name" 
                               name="company_name" 
                               class="form-control" 
                               value="<?= old('company_name', $client['company_name']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notas Internas</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  class="form-control" 
                                  rows="4"><?= old('notes', $client['notes']) ?></textarea>
                    </div>
                    
                    <div class="alert alert-light">
                        <small class="text-muted">
                            <strong>Último acceso:</strong> 
                            <?= $client['last_login_at'] ? date('d/m/Y H:i', strtotime($client['last_login_at'])) : 'Nunca' ?>
                            <br>
                            <strong>Creado:</strong> <?= date('d/m/Y', strtotime($client['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('admin/clients') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
