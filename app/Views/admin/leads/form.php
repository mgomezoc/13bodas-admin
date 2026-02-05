<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $pageTitle ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/leads') ?>">Leads</a></li>
        <li class="breadcrumb-item active"><?= $lead ? 'Editar' : 'Nuevo' ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-subtitle"><?= $lead ? 'Actualiza la información del lead' : 'Registra un nuevo lead manualmente' ?></p>
    </div>
    <a href="<?= base_url('admin/leads') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" class="needs-validation" action="<?= base_url('admin/leads/save' . ($lead ? '/' . $lead['id'] : '')) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                            id="full_name"
                            name="full_name"
                            value="<?= old('full_name', $lead['full_name'] ?? '') ?>"
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
                            value="<?= old('email', $lead['email'] ?? '') ?>"
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
                            value="<?= old('phone', $lead['phone'] ?? '') ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="event_date" class="form-label">Fecha del Evento</label>
                        <input
                            type="text"
                            class="form-control datepicker <?= isset($errors['event_date']) ? 'is-invalid' : '' ?>"
                            id="event_date"
                            name="event_date"
                            value="<?= old('event_date', $lead['event_date'] ?? '') ?>">
                        <?php if (isset($errors['event_date'])): ?>
                            <div class="invalid-feedback"><?= $errors['event_date'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="source" class="form-label">Origen</label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['source']) ? 'is-invalid' : '' ?>"
                                id="source"
                                name="source"
                                value="<?= old('source', $lead['source'] ?? '') ?>"
                                placeholder="website, instagram, referral">
                            <?php if (isset($errors['source'])): ?>
                                <div class="invalid-feedback"><?= $errors['source'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select id="status" name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>">
                                <?php
                                $statusOptions = [
                                    'new' => 'Nuevo',
                                    'contacted' => 'Contactado',
                                    'qualified' => 'Calificado',
                                    'converted' => 'Convertido',
                                    'lost' => 'Perdido',
                                ];
                                $selectedStatus = old('status', $lead['status'] ?? 'new');
                                ?>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $selectedStatus === $value ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['status'])): ?>
                                <div class="invalid-feedback"><?= $errors['status'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Mensaje</label>
                        <textarea
                            class="form-control"
                            id="message"
                            name="message"
                            rows="4"><?= old('message', $lead['message'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="utm_payload" class="form-label">UTM Payload</label>
                        <textarea
                            class="form-control"
                            id="utm_payload"
                            name="utm_payload"
                            rows="3"><?= old('utm_payload', $lead['utm_payload'] ?? '') ?></textarea>
                        <small class="text-muted">Guardar aquí la info UTM si se registró desde campañas.</small>
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i><?= $lead ? 'Actualizar' : 'Crear' ?> Lead
                        </button>
                        <a href="<?= base_url('admin/leads') ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($lead): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Información del Lead</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <small class="text-muted">ID:</small><br>
                        <code class="small"><?= esc($lead['id']) ?></code>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Creado:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></span>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Actualizado:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($lead['updated_at'])) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
