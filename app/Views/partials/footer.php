<footer class="footer">
    <div class="container footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img
                    src="<?= esc(base_url('img/13bodas-logo-blanco-transparente.png')) ?>"
                    alt="13Bodas"
                    width="120"
                    height="auto"
                >
                <p class="footer-tagline">
                    Plataforma para crear invitaciones digitales y gestionar RSVP desde cualquier país.
                </p>
            </div>

            <div class="footer-links-group">
                <h4>Producto</h4>
                <ul>
                    <li><a href="<?= site_url(route_to('home')) ?>#servicios">Funciones</a></li>
                    <li><a href="<?= site_url(route_to('home')) ?>#paquetes">Planes</a></li>
                    <li><a href="<?= site_url(route_to('register.index')) ?>">Registro gratis</a></li>
                    <li><a href="https://magiccam.13bodas.com" target="_blank" rel="noopener">Demo MagicCam</a></li>
                </ul>
            </div>

            <div class="footer-links-group">
                <h4>Recursos</h4>
                <ul>
                    <li><a href="<?= site_url(route_to('home')) ?>#proceso">Cómo empezar</a></li>
                    <li><a href="<?= site_url(route_to('home')) ?>#faq">Preguntas frecuentes</a></li>
                    <li><a href="<?= site_url(route_to('login')) ?>">Iniciar sesión</a></li>
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
                    <li>Soporte remoto internacional</li>
                    <li>
                        <a href="https://www.facebook.com/13bodas" target="_blank" rel="noopener">Facebook</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <span id="year"></span> 13Bodas. Todos los derechos reservados.</p>
            <div class="footer-legal">
                <a href="<?= site_url(route_to('legal.terms')) ?>">Términos y Condiciones</a>
                <a href="<?= site_url(route_to('legal.privacy')) ?>">Aviso de Privacidad</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('year').textContent = new Date().getFullYear();
</script>
