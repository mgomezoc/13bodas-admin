<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nuevo Cliente<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/clients') ?>">Clientes</a></li>
        <li class="breadcrumb-item active">Nuevo Cliente</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Nuevo Cliente</h1>
        <p class="page-subtitle">Crea una cuenta de cliente para entregar a tu comprador</p>
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
        <form action="<?= base_url('admin/clients/store') ?>" method="POST" class="needs-validation" novalidate>
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
                               value="<?= old('full_name') ?>"
                               placeholder="Ej: Juan Pérez García"
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?= old('email') ?>"
                               placeholder="cliente@email.com"
                               required>
                        <div class="form-text">Este será el usuario para iniciar sesión</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   value="<?= old('password') ?: substr(md5(time()), 0, 8) ?>"
                                   minlength="6"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <div class="form-text">Mínimo 6 caracteres. Puedes generar una aleatoria.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-control" 
                               value="<?= old('phone') ?>"
                               placeholder="81 1234 5678">
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
                               value="<?= old('company_name') ?>"
                               placeholder="Opcional">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notas Internas</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  class="form-control" 
                                  rows="4"
                                  placeholder="Notas privadas sobre este cliente (no visibles para el cliente)"><?= old('notes') ?></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Importante:</strong> Una vez creado el cliente, deberás crear un <strong>Evento</strong> y asignárselo para que pueda comenzar a configurar su invitación.
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('admin/clients') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>Crear Cliente
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function generatePassword() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let password = '';
    for (let i = 0; i < 8; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('password').value = password;
}
</script>
<?= $this->endSection() ?>
