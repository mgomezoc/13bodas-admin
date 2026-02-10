<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dominio Personalizado - <?= esc((string) ($event['couple_title'] ?? 'Evento')) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc((string) ($event['couple_title'] ?? 'Evento')) ?></a></li>
        <li class="breadcrumb-item active">Dominio Personalizado</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('admin/events/partials/_event_navigation', ['active' => 'dominios', 'event_id' => $event['id']]) ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Dominio Personalizado</h1>
        <p class="page-subtitle">Solicita tu dominio con costo fijo de $<?= number_format((int) $fixedPrice, 0) ?> MXN</p>
    </div>
</div>

<?php if (empty($domainRequest)): ?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning mb-4">
                <h5 class="mb-2"><i class="bi bi-info-circle me-1"></i> ¿Qué incluye este servicio?</h5>
                <ul class="mb-2">
                    <li>Migración manual por el equipo de 13Bodas.</li>
                    <li>Tiempo estimado de 24-72 horas después de confirmar DNS.</li>
                    <li>Costo único de <strong>$<?= number_format((int) $fixedPrice, 0) ?> MXN</strong>.</li>
                </ul>
                <small>Ejemplo de formato correcto: <code>bodadeana.com</code>.</small>
            </div>

            <form id="domainRequestForm">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="domain">Dominio deseado <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="domain" name="domain" placeholder="ejemplo.com" required>
                    <div class="form-text">No agregues protocolo ni rutas: usa solo el dominio.</div>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" value="1" id="acceptTerms" required>
                    <label class="form-check-label" for="acceptTerms">
                        Entiendo el costo del servicio y el flujo manual de activación.
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-2"></i>Solicitar dominio
                </button>
            </form>
        </div>
    </div>
<?php else: ?>
    <?php
    $statusConfig = [
        'requested' => ['label' => 'Solicitud recibida', 'class' => 'warning', 'icon' => 'bi-clock-history'],
        'processing' => ['label' => 'En proceso', 'class' => 'info', 'icon' => 'bi-gear'],
        'completed' => ['label' => 'Dominio activo', 'class' => 'success', 'icon' => 'bi-check-circle'],
    ];
    $status = (string) ($domainRequest['status'] ?? 'requested');
    $statusItem = $statusConfig[$status] ?? $statusConfig['requested'];
    ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="alert alert-<?= esc($statusItem['class']) ?> mb-4">
                <h5 class="mb-2"><i class="bi <?= esc($statusItem['icon']) ?> me-1"></i><?= esc($statusItem['label']) ?></h5>
                <p class="mb-1"><strong>Dominio solicitado:</strong> <code><?= esc((string) ($domainRequest['domain'] ?? '')) ?></code></p>
                <p class="mb-0"><strong>Costo:</strong> $<?= number_format((int) $fixedPrice, 0) ?> MXN</p>
            </div>

            <?php if (!empty($domainRequest['admin_notes'])): ?>
                <div class="alert alert-light border">
                    <h6 class="mb-1"><i class="bi bi-chat-left-text me-1"></i>Mensaje del equipo</h6>
                    <p class="mb-0"><?= nl2br(esc((string) $domainRequest['admin_notes'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block">Solicitado el</small>
                    <strong><?= !empty($domainRequest['created_at']) ? date('d/m/Y H:i', strtotime((string) $domainRequest['created_at'])) : 'N/D' ?></strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Estado actual</small>
                    <strong><?= esc($statusItem['label']) ?></strong>
                </div>
            </div>

            <?php if ($status === 'requested' && !$isAdmin): ?>
                <div class="mt-4">
                    <button type="button" class="btn btn-outline-danger" id="cancelRequestButton">
                        <i class="bi bi-x-circle me-1"></i>Cancelar solicitud
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isAdmin): ?>
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock me-1"></i>Panel de Administración
            </div>
            <div class="card-body">
                <form id="domainAdminForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="status">Estado</label>
                        <select class="form-select" name="status" id="status">
                            <option value="requested" <?= $status === 'requested' ? 'selected' : '' ?>>Solicitado</option>
                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>En proceso</option>
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="admin_message">Mensaje para cliente</label>
                        <textarea class="form-control" id="admin_message" name="admin_message" rows="4"><?= esc((string) ($domainRequest['admin_notes'] ?? '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Actualizar estado
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const domainRequestForm = document.getElementById('domainRequestForm');
        const adminForm = document.getElementById('domainAdminForm');
        const cancelButton = document.getElementById('cancelRequestButton');

        if (domainRequestForm) {
            const domainInput = document.getElementById('domain');
            domainInput?.addEventListener('input', (event) => {
                event.target.value = event.target.value.toLowerCase().trim();
            });

            domainRequestForm.addEventListener('submit', async function(event) {
                event.preventDefault();

                const acceptTerms = document.getElementById('acceptTerms');
                if (acceptTerms && !acceptTerms.checked) {
                    Toast.fire({ icon: 'warning', title: 'Debes aceptar los términos del servicio.' });
                    return;
                }

                const formData = new FormData(domainRequestForm);
                const response = await fetch('<?= url_to('admin.events.domains.request', $event['id']) ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    Toast.fire({ icon: 'success', title: data.message });
                    setTimeout(() => window.location.reload(), 900);
                    return;
                }

                if (data.errors) {
                    Toast.fire({ icon: 'error', title: Object.values(data.errors).join(' ') });
                    return;
                }

                Toast.fire({ icon: 'error', title: data.message || 'No se pudo procesar la solicitud.' });
            });
        }

        if (adminForm) {
            adminForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                const formData = new FormData(adminForm);

                const response = await fetch('<?= url_to('admin.events.domains.update', $event['id']) ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await response.json();
                Toast.fire({ icon: data.success ? 'success' : 'error', title: data.message || 'Respuesta inválida.' });

                if (data.success) {
                    setTimeout(() => window.location.reload(), 900);
                }
            });
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', async function() {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                const response = await fetch('<?= url_to('admin.events.domains.cancel', $event['id']) ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await response.json();
                Toast.fire({ icon: data.success ? 'success' : 'error', title: data.message || 'Respuesta inválida.' });

                if (data.success) {
                    setTimeout(() => window.location.reload(), 900);
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
