# Agentes del Proyecto
Este documento describe los “agentes” (roles funcionales) del sistema y cómo interactúan dentro de la arquitectura actual. En este repositorio no se detectan clases explícitas con nombres tipo *agent/orchestrator*; por ello se documentan los controladores principales como agentes funcionales y sus responsabilidades observables en el código. Si se requiere confirmar si existen agentes explícitos en otro módulo, buscar clases con esos nombres en `app/` y `public/` (ver sección de convención).

## Vista general del sistema
**Arquitectura (alto nivel)**
- **Entrada HTTP** → `public/index.php` inicializa el bootstrap de CodeIgniter 4. (public/index.php)
- **Ruteo** → `app/Config/Routes.php` define rutas públicas y de administración. (app/Config/Routes.php)
- **Controladores** → clases en `app/Controllers/*` ejecutan lógica de negocio y orquestan modelos. (app/Controllers/*)
- **Modelos** → `app/Models/*` encapsulan acceso a datos y consultas. (app/Models/*)
- **Vistas** → `app/Views/*` renderiza HTML. (app/Views/*)

**Tecnologías detectadas**
- **PHP 8.1+** y **CodeIgniter 4** como framework principal. (composer.json, public/index.php)
- **Composer** para dependencias y scripts de test (`composer test`). (composer.json)

## Inventario de agentes
> **Nota**: No hay “agentes” explícitos. Se documentan roles funcionales basados en controladores y filtros observados.

### Agente: Invitación pública (render)
- **Ubicación**: `app/Controllers/Invitation.php` (método `view`).
- **Responsabilidad**: Cargar el evento por slug, recolectar módulos, media, galería, regalos, invitados, RSVP, menú, cortejo, FAQs y agenda; preparar el payload de vista y renderizar el template del evento. (app/Controllers/Invitation.php)
- **Entradas/Disparadores**: `GET /i/{slug}` desde rutas públicas. (app/Config/Routes.php)
- **Salidas**: Respuesta HTML renderizada con el template activo; 404 si no hay evento o template válido. (app/Controllers/Invitation.php)
- **Dependencias**: `EventModel`, `TemplateModel`, `ContentModuleModel`, tablas como `media_assets`, `registry_items`, `guest_groups`, `guests`, `rsvp_responses`, `menu_options`, `wedding_party_members`. (app/Controllers/Invitation.php)
- **Configuración**: Usa `event.theme_config` y `event.venue_config` (JSON) para la vista. (app/Controllers/Invitation.php)
- **Errores comunes y manejo**:
  - Evento no encontrado → `PageNotFoundException`. (app/Controllers/Invitation.php)
  - Template no activo o archivo de template inexistente → `PageNotFoundException`. (app/Controllers/Invitation.php)
  - Fallos de consulta en DB → se capturan con `try/catch` y se devuelven arrays vacíos. (app/Controllers/Invitation.php)
- **Ejemplo de uso**:
  - `GET /i/{slug}` (ruta pública). (app/Config/Routes.php)

### Agente: RSVP público (submit)
- **Ubicación**: `app/Controllers/RsvpController.php` (método `submit`).
- **Responsabilidad**: Validar solicitudes RSVP públicas, crear grupo e invitado, registrar la respuesta en `rsvp_responses` y enviar confirmación por email vía Resend. (app/Controllers/RsvpController.php, app/Libraries/RsvpSubmissionService.php, app/Libraries/RsvpMailer.php)
- **Entradas/Disparadores**: `POST /i/{slug}/rsvp`. (app/Config/Routes.php)
- **Salidas**: JSON con `success`, `message` y `data` (IDs generados) o redirect con flash message. (app/Controllers/RsvpController.php)
- **Dependencias**: `EventModel`, `RsvpSubmissionService`, acceso a tablas `guest_groups`, `guests`, `rsvp_responses` y configuración `Config\Resend`. (app/Controllers/RsvpController.php, app/Libraries/RsvpSubmissionService.php, app/Config/Resend.php)
- **Configuración**: Respeta `event.service_status` y `event.access_mode` para validar disponibilidad; requiere email válido. (app/Libraries/RsvpSubmissionService.php, app/Controllers/RsvpController.php)
- **Errores comunes y manejo**: Respuestas JSON de error para validación, evento inexistente, evento no disponible o errores de envío. (app/Controllers/RsvpController.php, app/Libraries/RsvpMailer.php)
- **Ejemplo de uso**:
  - `POST /i/{slug}/rsvp` con `name`, `email`, `phone`, `attending`, `message`, `song_request`. (app/Controllers/RsvpController.php)

### Agente: Autenticación del panel (login)
- **Ubicación**: `app/Controllers/Admin/Auth.php`.
- **Responsabilidad**: Validar credenciales, establecer sesión, recuperar roles y redirigir al dashboard. (app/Controllers/Admin/Auth.php)
- **Entradas/Disparadores**: `GET /admin/login`, `POST /admin/login`. (app/Config/Routes.php)
- **Salidas**: Redirecciones a dashboard o retorno con mensajes de error. (app/Controllers/Admin/Auth.php)
- **Dependencias**: `UserModel`, `ClientModel`, `session()`. (app/Controllers/Admin/Auth.php)
- **Configuración**: Reglas de validación de email/contraseña en el controlador. (app/Controllers/Admin/Auth.php)
- **Errores comunes y manejo**: Credenciales inválidas, usuario inactivo → redirección con mensaje. (app/Controllers/Admin/Auth.php)
- **Ejemplo de uso**:
  - `POST /admin/login` con `email` y `password`. (app/Config/Routes.php)

### Agente: Control de acceso (filtro)
- **Ubicación**: `app/Filters/AuthFilter.php` y alias en `app/Config/Filters.php`.
- **Responsabilidad**: Validar sesión activa, verificar usuario activo y roles permitidos. (app/Filters/AuthFilter.php)
- **Entradas/Disparadores**: Rutas `/admin/*` protegidas por el filtro `auth`. (app/Config/Routes.php)
- **Salidas**: Redirección al login o dashboard con mensajes. (app/Filters/AuthFilter.php)
- **Dependencias**: `UserModel`, sesión. (app/Filters/AuthFilter.php)
- **Configuración**: Alias de filtros en `app/Config/Filters.php`. (app/Config/Filters.php)
- **Errores comunes y manejo**: Sin sesión → login; usuario desactivado → login; rol no permitido → dashboard. (app/Filters/AuthFilter.php)
- **Ejemplo de uso**:
  - Rutas del grupo `admin` con `filter => auth`. (app/Config/Routes.php)

## Flujos principales
### 1) Render de invitación pública
1. `GET /i/{slug}` llega a `Invitation::view`. (app/Config/Routes.php)
2. Se busca el evento, template activo y módulos; se arman listas de galería, media y datos relacionados. (app/Controllers/Invitation.php)
3. Se renderiza la vista `templates/{code}/index`. (app/Controllers/Invitation.php)

**Observabilidad**: No se observan logs explícitos; errores se transforman en `PageNotFoundException`. (app/Controllers/Invitation.php)

### 2) Envío de RSVP público
1. `POST /i/{slug}/rsvp` llega a `RsvpController::submit`. (app/Config/Routes.php)
2. Validación de payload (`name`, `email`, `attending`) y disponibilidad del evento. (app/Controllers/RsvpController.php)
3. Inserción en `guest_groups`, `guests`, `rsvp_responses`. (app/Libraries/RsvpSubmissionService.php)
4. Envío de correo de confirmación vía Resend. (app/Libraries/RsvpMailer.php)
5. Respuesta JSON con éxito o error / redirect con flash. (app/Controllers/RsvpController.php)

**Observabilidad**: Errores capturados devuelven JSON; no hay logging explícito. (app/Controllers/RsvpController.php)

### 3) Login de administración
1. `GET /admin/login` muestra formulario de acceso. (app/Controllers/Admin/Auth.php)
2. `POST /admin/login` valida credenciales y crea sesión con roles. (app/Controllers/Admin/Auth.php)
3. Filtro `auth` protege rutas del panel y verifica sesión/roles. (app/Filters/AuthFilter.php)

**Observabilidad**: Redirecciones con mensajes flash ante errores. (app/Controllers/Admin/Auth.php)

## Herramientas / Integraciones
- **Base de datos (CodeIgniter Database)**: Acceso directo a múltiples tablas para invitaciones y RSVP. (app/Controllers/Invitation.php)
- **Sistema de archivos**: Fallback de galería leyendo `/public/uploads/events/{event_id}/gallery`. (app/Controllers/Invitation.php)
- **No determinado**: No se observan SDKs de terceros ni colas/cron explícitos en los archivos revisados; revisar `app/Config/*` para integraciones futuras. (app/Config/Routes.php)

## Convenciones y extensión
**Crear un nuevo “agente” (rol funcional)**
1. Crear un controlador en `app/Controllers/` siguiendo el estilo de `Invitation` o `Admin/*`. (app/Controllers/Invitation.php, app/Controllers/Admin/Auth.php)
2. Registrar rutas en `app/Config/Routes.php`. (app/Config/Routes.php)
3. Usar modelos de `app/Models/` para acceso a datos. (app/Models/*)
4. Proteger rutas con filtros (`auth`) si es necesario. (app/Config/Filters.php)

**Plantilla mínima sugerida**
```php
<?php
namespace App\Controllers;

class NuevoAgente extends BaseController
{
    public function index()
    {
        return view('ruta/a/vista');
    }
}
```

**Checklist de PR**
- Confirmar rutas nuevas en `app/Config/Routes.php`. (app/Config/Routes.php)
- Validar permisos con filtros si aplica. (app/Config/Filters.php)
- Añadir/actualizar vistas en `app/Views/` según el flujo. (app/Views/*)
- Ejecutar pruebas si existen (`composer test`). (composer.json)

## Referencias rápidas
| Agente | Path | Trigger | Output |
| --- | --- | --- | --- |
| Invitación pública (render) | `app/Controllers/Invitation.php` | `GET /i/{slug}` | HTML de template | 
| RSVP público (submit) | `app/Controllers/RsvpController.php` | `POST /i/{slug}/rsvp` | JSON `success/message` o redirect | 
| Auth admin | `app/Controllers/Admin/Auth.php` | `GET/POST /admin/login` | Sesión + redirección | 
| AuthFilter | `app/Filters/AuthFilter.php` | `admin/*` | Redirección/continuación | 

**Comandos útiles**
- `composer test` (ejecuta PHPUnit). (composer.json)
