<?php

declare(strict_types=1);

$selectedGuest = $selectedGuest ?? null;
$selectedGuestName = '';
$selectedGuestEmail = '';
$selectedGuestPhone = '';
$selectedGuestCode = '';

if (!empty($selectedGuest)) {
    $selectedGuestName = trim((string) ($selectedGuest['first_name'] ?? '') . ' ' . (string) ($selectedGuest['last_name'] ?? ''));
    $selectedGuestEmail = (string) ($selectedGuest['email'] ?? '');
    $selectedGuestPhone = (string) ($selectedGuest['phone_number'] ?? '');
    $selectedGuestCode = (string) ($selectedGuest['access_code'] ?? '');
}
?>
<section class="majestic-rsvp" id="rsvp">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Confirma tu Asistencia</h2>
        <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
            Por favor confirma tu asistencia antes del <?= date('d/m/Y', strtotime($event['rsvp_deadline'] ?? '-1 month', strtotime($event['event_date_start']))) ?>
        </p>
        
        <div class="rsvp-form-container" data-aos="fade-up" data-aos-delay="200">
            <form id="rsvpForm" class="rsvp-form" method="POST" action="<?= esc(route_to('rsvp.submit', $event['slug'])) ?>">
                <?= csrf_field() ?>
                <?php if (!empty($selectedGuest['id'])): ?>
                    <input type="hidden" name="guest_id" value="<?= esc((string) $selectedGuest['id']) ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="guest_code">Código de Invitación</label>
                    <input type="text" 
                           id="guest_code" 
                           name="guest_code" 
                           class="form-control"
                           placeholder="Ingresa tu código"
                           value="<?= esc($selectedGuestCode) ?>"
                           <?= $selectedGuestCode !== '' ? 'readonly' : '' ?>
                           required>
                    <small class="form-text">Revisa tu invitación para encontrar tu código único</small>
                </div>
                
                <div class="form-group">
                    <label for="guest_name">Nombre Completo</label>
                    <input type="text" 
                           id="guest_name" 
                           name="name" 
                           class="form-control"
                           placeholder="Tu nombre"
                           value="<?= esc($selectedGuestName) ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="guest_email">Email</label>
                    <input type="email" 
                           id="guest_email" 
                           name="email" 
                           class="form-control"
                           placeholder="tu@email.com"
                           value="<?= esc($selectedGuestEmail) ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="guest_phone">Teléfono</label>
                    <input type="tel" 
                           id="guest_phone" 
                           name="phone" 
                           class="form-control"
                           placeholder="(555) 123-4567"
                           value="<?= esc($selectedGuestPhone) ?>">
                </div>
                
                <div class="form-group">
                    <label>¿Confirmas tu asistencia?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="attending" value="accepted" required>
                            <span><i class="bi bi-check-circle"></i> Sí, asistiré</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="attending" value="declined">
                            <span><i class="bi bi-x-circle"></i> No podré asistir</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="attending" value="maybe">
                            <span><i class="bi bi-question-circle"></i> Aún no estoy seguro</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="guest_count">Número de Acompañantes</label>
                    <select id="guest_count" name="guests" class="form-control">
                        <option value="0">Solo yo</option>
                        <option value="1">1 acompañante</option>
                        <option value="2">2 acompañantes</option>
                        <option value="3">3 acompañantes</option>
                        <option value="4">4 acompañantes</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message">Mensaje para los Novios</label>
                    <textarea id="message" 
                              name="message" 
                              class="form-control" 
                              rows="4"
                              placeholder="Deja un mensaje especial..."></textarea>
                </div>
                
                <button type="submit" class="rsvp-submit">
                    <i class="bi bi-send"></i>
                    Enviar Confirmación
                </button>
            </form>
        </div>
    </div>
</section>
