<footer class="footer">
    <div class="container footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img
                    src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>"
                    alt="13Bodas"
                    width="120"
                    height="auto"
                >
                <p class="footer-tagline">
                    Invitaciones digitales y filtros AR que transforman eventos en experiencias inolvidables.
                </p>
            </div>

            <div class="footer-links-group">
                <h4>Servicios</h4>
                <ul>
                    <li><a href="<?= base_url('#servicios') ?>">Invitaciones Digitales</a></li>
                    <li><a href="<?= base_url('#magiccam') ?>">Filtros AR MagicCam</a></li>
                    <li><a href="<?= base_url('#paquetes') ?>">Paquetes</a></li>
                    <li><a href="https://magiccam.13bodas.com" target="_blank" rel="noopener">Demo MagicCam</a></li>
                </ul>
            </div>

            <div class="footer-links-group">
                <h4>Recursos</h4>
                <ul>
                    <li><a href="<?= base_url('#proceso') ?>">Cómo Trabajamos</a></li>
                    <li><a href="<?= base_url('#faq') ?>">Preguntas Frecuentes</a></li>
                    <li><a href="<?= base_url('#contacto') ?>">Contacto</a></li>
                </ul>
            </div>

            <div class="footer-links-group">
                <h4>Contacto</h4>
                <ul>
                    <li>
                        <a href="https://wa.me/528115247741" target="_blank" rel="noopener">
                            WhatsApp: +52 81 1524 7741
                        </a>
                    </li>
                    <li><a href="mailto:hola@13bodas.com">hola@13bodas.com</a></li>
                    <li>Monterrey, México</li>
                    <li>
                        <a href="https://www.facebook.com/13bodas" target="_blank" rel="noopener">Facebook</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <span id="year"></span> 13Bodas. Todos los derechos reservados.</p>
            <div class="footer-legal">
                <a href="<?= base_url('terminos') ?>">Términos y Condiciones</a>
                <a href="<?= base_url('privacidad') ?>">Aviso de Privacidad</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('year').textContent = new Date().getFullYear();
</script>
