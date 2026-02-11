<section id="contacto" class="contact section-padding">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-content" data-aos="fade-right">
                <span class="section-tag">Contacto</span>
                <h2 class="section-title">
                    Hagamos realidad<br>
                    <span class="gradient-text">tu evento soñado</span>
                </h2>
                <p class="contact-description">
                    Cuéntanos de tu evento y recibe una cotización personalizada en menos de 24 horas.
                    Toda la comunicación se gestiona por formularios para dar mejor seguimiento a cada solicitud.
                </p>

                <div class="contact-info">
                    <div class="contact-info-item">
                        <div class="info-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div>
                            <strong>WhatsApp</strong>
                            <a href="https://wa.me/528115247741" target="_blank" rel="noopener">+52 81 1524 7741</a>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="info-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <strong>Ubicación</strong>
                            <span>Monterrey, México (Servicio Global)</span>
                        </div>
                    </div>
                </div>

                <div class="contact-social">
                    <strong>Síguenos:</strong>
                    <div class="social-links">
                        <a href="https://www.facebook.com/13bodas" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="contact-form-wrapper" data-aos="fade-left">
                <?php if (session()->getFlashdata('contact_error')): ?>
                    <div class="alert alert-danger" role="alert" style="margin-bottom: 16px;">
                        <?= esc((string) session()->getFlashdata('contact_error')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('contact_success')): ?>
                    <div class="alert alert-success" role="alert" style="margin-bottom: 16px;">
                        <?= esc((string) session()->getFlashdata('contact_success')) ?>
                    </div>
                <?php endif; ?>

                <form
                    action="<?= site_url(route_to('api.leads.store')) ?>"
                    method="POST"
                    class="contact-form"
                    id="contactForm"
                >
                    <?= csrf_field() ?>
                    <input type="hidden" name="_subject" value="Nueva solicitud desde 13Bodas.com">
                    <input type="hidden" name="_language" value="es">
                    <input type="hidden" name="_next" value="<?= esc(base_url('gracias')) ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre completo <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Ana y Carlos">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="tu@email.com">
                        </div>

                        <div class="form-group">
                            <label for="telefono">WhatsApp</label>
                            <input type="tel" id="telefono" name="telefono" placeholder="+52 81 1234 5678">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="evento">Tipo de evento <span class="required">*</span></label>
                            <select id="evento" name="tipo_evento" required>
                                <option value="">Selecciona...</option>
                                <option value="boda">💒 Boda</option>
                                <option value="xv">👑 XV Años</option>
                                <option value="bautizo">👶 Bautizo</option>
                                <option value="aniversario">💍 Aniversario</option>
                                <option value="corporativo">🏢 Evento Corporativo</option>
                                <option value="otro">🎉 Otro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha">Fecha aproximada</label>
                            <input type="date" id="fecha" name="fecha_evento">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="paquete">Paquete de interés</label>
                        <select id="paquete" name="paquete_interes">
                            <option value="">Selecciona...</option>
                            <option value="essential">Essential - Invitación Web</option>
                            <option value="interactive">Interactive - Invitación + Filtro AR</option>
                            <option value="infinity">Infinity - Solución Completa VIP</option>
                            <option value="custom">A la medida / Personalizado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Cuéntanos más sobre tu evento</label>
                        <textarea
                            id="mensaje"
                            name="mensaje"
                            rows="4"
                            placeholder="Ej: Boda en la playa con temática tropical, me interesa filtro AR con elementos marinos y palmeras..."
                        ></textarea>
                    </div>

                    <div class="form-footer">
                        <p class="form-note">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 0a8 8 0 100 16A8 8 0 008 0zm.93 12H7.07V7h1.86v5zm0-6H7.07V4h1.86v2z"/>
                            </svg>
                            Te responderemos en menos de 24 horas
                        </p>
                        <button type="submit" class="btn btn-primary btn-submit">
                            Solicitar asesoría
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3l16 7-16 7V3zm2 11.5l9-4.5-9-4.5v9z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
