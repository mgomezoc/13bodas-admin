<?php declare(strict_types=1); ?>
<?php
$helpTitle = $title ?? '¿Para qué sirve esta sección?';
$helpMessage = $message ?? '';
if ($helpMessage === '') {
    return;
}
?>
<div class="event-help-callout mb-4" role="note" aria-label="Ayuda contextual">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-info-circle"></i>
        <div>
            <div class="event-help-title"><?= esc($helpTitle) ?></div>
            <p class="event-help-text mb-0"><?= esc($helpMessage) ?></p>
        </div>
    </div>
</div>
