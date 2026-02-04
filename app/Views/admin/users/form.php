<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $pageTitle ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Usuarios</a></li>
        <li class="breadcrumb-item active"><?= $user ? 'Editar' : 'Nuevo' ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-subtitle"><?= $user ? 'Actualiza la información del usuario' : 'Crea un nuevo usuario del sistema' ?></p>
    </div>
    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= base_url('admin/users/save' . ($user ? '/' . $user['id'] : '')) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                            id="full_name" 
                            name="full_name" 
                            value="<?= old('full_name', $user['full_name'] ?? '') ?>"
                            required>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input 
                            type="email" 
                            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                            id="email" 
                            name="email" 
                            value="<?= old('email', $user['email'] ?? '') ?>"
                            required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                            id="phone" 
                            name="phone" 
                            value="<?= old('phone', $user['phone'] ?? '') ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Contraseña 
                            <?php if (!$user): ?>
                                <span class="text-danger">*</span>
                            <?php else: ?>
                                <small class="text-muted">(dejar en blanco para mantener la actual)</small>
                            <?php endif; ?>
                        </label>
                        <input 
                            type="password" 
                            class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                            id="password" 
                            name="password"
                            <?= !$user ? 'required' : '' ?>>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php else: ?>
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roles <span class="text-danger">*</span></label>
                        <?php if (isset($errors['roles'])): ?>
                            <div class="text-danger small mb-2"><?= $errors['roles'] ?></div>
                        <?php endif; ?>
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="roles[]" 
                                    value="<?= $role['id'] ?>" 
                                    id="role_<?= $role['id'] ?>"
                                    <?= in_array($role['id'], $userRoleIds ?? []) || in_array($role['id'], old('roles', [])) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                    <strong><?= esc($role['name']) ?></strong>
                                    <?php if ($role['description']): ?>
                                        <small class="text-muted d-block"><?= esc($role['description']) ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                role="switch" 
                                id="is_active_checkbox" 
                                name="is_active_checkbox"
                                <?= old('is_active', $user['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active_checkbox">Usuario activo</label>
                        </div>
                        <input type="hidden" name="is_active" id="is_active_hidden" value="<?= old('is_active', $user['is_active'] ?? 1) ?>">
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i><?= $user ? 'Actualizar' : 'Crear' ?> Usuario
                        </button>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($user): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Información del Usuario</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <small class="text-muted">ID:</small><br>
                        <code class="small"><?= esc($user['id']) ?></code>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Creado:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></span>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Actualizado:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></span>
                    </li>
                    <?php if ($user['last_login_at']): ?>
                    <li class="mb-2">
                        <small class="text-muted">Último Acceso:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($user['last_login_at'])) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($user['email_verified_at']): ?>
                    <li>
                        <small class="text-muted">Email Verificado:</small><br>
                        <span class="small text-success">
                            <i class="bi bi-check-circle-fill"></i> 
                            <?= date('d/m/Y', strtotime($user['email_verified_at'])) ?>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('is_active_checkbox').addEventListener('change', function() {
    document.getElementById('is_active_hidden').value = this.checked ? '1' : '0';
});
</script>
<?= $this->endSection() ?>
