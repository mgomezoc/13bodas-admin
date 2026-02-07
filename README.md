# 13Bodas Admin (CodeIgniter 4)

Panel administrativo y frontend de invitaciones para eventos (boda) construido con CodeIgniter 4. El sistema expone rutas públicas para ver invitaciones y RSVP, además de un panel protegido para administrar eventos, invitados, galerías, regalos, menú, ubicaciones, agenda y contenido del template.

## Tecnologías
- **PHP 8.1+**
- **CodeIgniter 4**
- **Composer** (dependencias y tests)

## Requisitos
- PHP 8.1 o superior.
- Extensiones PHP recomendadas por CI4: `intl`, `mbstring`, `json`, `curl` (si se usan peticiones HTTP) y `mysqlnd` si se usa MySQL.

## Configuración
1. Copia el archivo `env` a `.env` y ajusta `app.baseURL` y la conexión a base de datos.
2. Instala dependencias:
   ```bash
   composer install
   ```

## Ejecución local
CodeIgniter sirve desde la carpeta `public`.
```bash
php -S 0.0.0.0:8000 -t public
```

## Rutas principales
### Públicas
- `GET /` → Home del sitio.
- `GET /i/{slug}` → Render de invitación pública.
- `POST /i/{slug}/rsvp` → Envío de RSVP público (JSON).

### Admin (protegidas por login)
- `GET /admin/login` → Formulario de login.
- `GET /admin` → Dashboard.
- Módulos de eventos: invitados, grupos, RSVP, galería, regalos, menú, cortejo, ubicaciones, agenda, FAQ, recomendaciones, módulos y dominios personalizados.

Las rutas completas están en `app/Config/Routes.php`.

## Estructura del proyecto
- `public/` → Front controller y assets públicos.
- `app/Controllers/` → Controladores (públicos y admin).
- `app/Models/` → Modelos de acceso a datos.
- `app/Views/` → Vistas y templates de invitaciones.
- `app/Filters/` → Filtros de autenticación y cliente.
- `tests/` → Pruebas (PHPUnit).

## RSVP público
El endpoint `POST /i/{slug}/rsvp` crea grupos e invitados, y registra la respuesta en `rsvp_responses`. Requiere que el evento esté activo (`service_status=active`) y que `access_mode` sea `open`.

## Documentación adicional
- `agentes.md` contiene el inventario de roles funcionales y flujos principales.

## Pruebas
```bash
composer test
```

## Notas
- El front controller de CI4 está en `public/index.php`. Configura tu servidor para apuntar a la carpeta `public`.
