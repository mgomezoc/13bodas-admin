# Validación GA4 - 13Bodas One-Page

## 1) Implementación básica
- [x] `gtag.js` cargado con `async` en `<head>`.
- [x] ID de medición configurado: `G-SBKT31SXZX`.
- [x] `gtag('config', ...)` con:
  - `anonymize_ip: true`
  - `cookie_flags: 'SameSite=None;Secure'`
  - `send_page_view: true`
  - `debug_mode` habilitado fuera de producción.
- [x] Sin duplicidad de snippet GA4 en layout principal.

## 2) Enhanced Measurement + equivalentes SPA
- [x] `page_view` inicial y `page_view` por sección (`#hash`) para comportamiento one-page.
- [x] `scroll` al 90% de profundidad.
- [x] `click` de outbound links.
- [x] `file_download` para PDFs/archivos descargables.
- [ ] `video_start`, `video_progress`, `video_complete` (no hay video embebido actualmente en la landing).

## 3) Eventos personalizados
- [x] `cta_click`: botones clave con `data-track-cta`.
- [x] `form_submit`: envío de formulario de contacto.
- [x] `select_package`: selección de paquetes `essential|interactive|infinity`.
- [x] `ar_demo_interaction`: apertura de demo y soporte para `postMessage` de iframe 8thWall.
- [x] `social_click`: clics de redes sociales (WhatsApp/Facebook).
- [x] `section_navigation` + `section_view`: navegación y visibilidad de secciones.

## 4) Parámetros personalizados
- [x] `package_type`
- [x] `cta_position`
- [x] `ar_demo_interaction`
- [x] `form_type`

## 5) Privacidad y compliance
- [x] Banner de consentimiento de cookies (GDPR/CCPA orientado a analytics).
- [x] `anonymize_ip: true`.
- [x] Mecanismo de opt-out (botón Rechazar + consentimiento denegado por defecto).
- [x] Control de eliminación de datos de analytics (botón “Eliminar datos analytics”).

## 6) Performance
- [x] Script de GA4 con `async`.
- [x] Scripts pesados (GSAP y `app.js`) con `defer`.
- [x] Implementación sin bloqueo de render crítico.

## 7) Testing y debug (pasos recomendados)

### Google Tag Assistant
1. Instalar/extensión Tag Assistant.
2. Abrir `https://13bodas.com`.
3. Verificar que detecte `G-SBKT31SXZX` sin warnings de duplicidad.
4. Validar disparo de:
   - `cta_click`
   - `select_package`
   - `form_submit`
   - `section_navigation`
   - `section_view`
   - `scroll`

### GA4 Real-Time (objetivo < 30s)
1. En GA4 → Reports → Realtime.
2. Abrir la landing en incógnito.
3. Aceptar cookies analytics.
4. Interactuar con CTAs y formulario.
5. Confirmar eventos en menos de 30 segundos.

### GA4 DebugView
1. En entorno no productivo, `debug_mode` ya está activo.
2. En GA4 → Admin → DebugView.
3. Navegar por secciones y validar los parámetros personalizados.

## Notas operativas
- Si se integra un video embebido posteriormente, conectar listeners a `video_start`, `video_progress` y `video_complete`.
- Para iframe 8thWall, emitir `window.parent.postMessage({ source: '8thwall-analytics', action: 'open|close|interact' }, '*')` para trazabilidad completa.
