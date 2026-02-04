# 13BODAS - Contexto CodeIgniter 4

## 📋 Información General

- **Framework**: CodeIgniter 4
- **Entorno**: Development
- **Base URL**: `http://localhost/13bodas/public/`
- **Base de datos**: `invitaciones_ci4` (MySQL)

## 🗂️ Estructura App/

```
app/
├── Config/          # Configuraciones del framework
├── Controllers/     
│   ├── Admin/       # Panel de administración
│   │   ├── Auth.php
│   │   ├── Clients.php
│   │   ├── Dashboard.php
│   │   ├── Events.php
│   │   ├── Gallery.php
│   │   ├── Guests.php
│   │   ├── MenuOptions.php
│   │   ├── Registry.php
│   │   └── Rsvp.php
│   ├── BaseController.php
│   ├── Home.php
│   └── Invitation.php
├── Database/
│   ├── Migrations/  # 2025-01-24 CreateUsersTable
│   └── Seeds/       # UserSeeder
├── Filters/
│   ├── AuthFilter.php
│   └── ClientFilter.php
├── Models/          # 15 modelos (ver detalle abajo)
└── Views/
    ├── admin/       # Vistas administrativas
    ├── auth/
    ├── layouts/     # main.php, admin.php, legal.php
    ├── pages/       # home, gracias, términos, privacidad
    ├── partials/    # header, footer, whatsapp_float
    └── templates/   # lovelove, solene, sukun, weddingo
```

## 🛣️ Rutas Principales

### Públicas
| Ruta | Controlador | Descripción |
|------|-------------|-------------|
| `/` | `Home::index` | Landing page |
| `/terminos` | `Home::terminos` | Términos y condiciones |
| `/privacidad` | `Home::privacidad` | Aviso de privacidad |
| `/gracias` | `Home::gracias` | Página de agradecimiento |
| `/i/:slug` | `Invitation::view` | Ver invitación pública |
| `/i/:slug/rsvp` | `Invitation::rsvp` | Confirmación RSVP |

### Admin (Protegidas con filtro `auth`)
| Módulo | Base Route | Funcionalidades |
|--------|------------|-----------------|
| **Autenticación** | `/admin/login` | Login, logout |
| **Dashboard** | `/admin/dashboard` | Vista general |
| **Clientes** | `/admin/clients` | CRUD, toggle status |
| **Usuarios** | `/admin/users` | CRUD (solo admin/superadmin) |
| **Eventos** | `/admin/events` | CRUD, preview, check-slug |
| **Invitados** | `/admin/events/:id/guests` | CRUD, importar/exportar |
| **Grupos** | `/admin/events/:id/groups` | CRUD grupos de invitados |
| **RSVP** | `/admin/events/:id/rsvp` | Listado, exportar (meals, songs) |
| **Galería** | `/admin/events/:id/gallery` | Upload, reorder, delete |
| **Registro Regalos** | `/admin/events/:id/registry` | CRUD, toggle claimed |
| **Opciones Menú** | `/admin/events/:id/menu` | CRUD opciones de comida |
| **Cortejo Nupcial** | `/admin/events/:id/party` | CRUD padrinos/damas |
| **Leads** | `/admin/leads` | Listado, convertir, cambiar status |
| **Templates** | `/admin/templates` | CRUD templates |
| **Perfil** | `/admin/profile` | Actualizar perfil, cambiar password |

### API
| Ruta | Método | Descripción |
|------|--------|-------------|
| `/api/leads` | POST | Crear lead desde formulario público |

## 📊 Modelos (app/Models/)

| Modelo | Entidad | Propósito Inferido |
|--------|---------|-------------------|
| `UserModel` | Usuarios | Gestión de usuarios (admin, cliente) |
| `ClientModel` | Clientes | Datos de clientes/novios |
| `EventModel` | Eventos | Eventos/bodas |
| `EventTemplateModel` | Plantillas Evento | Relación evento-template |
| `TemplateModel` | Templates | Temas visuales (lovelove, solene, etc.) |
| `GuestModel` | Invitados | Lista de invitados por evento |
| `GuestGroupModel` | Grupos Invitados | Agrupación de invitados |
| `RsvpResponseModel` | Confirmaciones | Respuestas RSVP |
| `MediaAssetModel` | Assets Multimedia | Imágenes/videos de eventos |
| `MenuOptionModel` | Opciones Menú | Opciones de comida |
| `RegistryItemModel` | Lista Regalos | Mesa de regalos |
| `WeddingPartyMemberModel` | Cortejo Nupcial | Padrinos/damas |
| `LeadModel` | Prospectos | Contactos desde landing |
| `ContentModuleModel` | Módulos Contenido | Secciones dinámicas |
| `RoleModel` | Roles | Roles de usuario |

## 🎨 Templates Disponibles

- **lovelove**: Template completo con slider, galería, RSVP
- **solene**: Template minimalista
- **sukun**: Template moderno
- **weddingo**: Template elegante

## 🔐 Autenticación

- **Filtros**: `AuthFilter`, `ClientFilter`
- **Sesiones**: Manejadas en base de datos (`ci_sessions`)
- **Expiración**: 7200s (2 horas)
- **Roles**: Sistema multirol (admin, cliente, etc.)

## 📂 Archivos Estáticos (public/)

```
public/
├── css/             # admin.css, style.css
├── js/              # admin.js, app.js
├── img/             # Logos, assets
├── templates/       # Assets de cada template (CSS/JS/images)
└── uploads/         # Archivos subidos por eventos
    └── events/:uuid/gallery/
```

## 🧩 Lógica de Negocio

**Sistema de Invitaciones Digitales para Bodas**

1. **Landing corporativo** → Captación de leads (`LeadModel`)
2. **Panel Admin** → Gestión multicliente
3. **Gestión de Eventos**:
   - Crear evento con slug único
   - Asignar template
   - Administrar invitados (importar CSV)
   - Configurar opciones de menú
   - Subir galería
   - Crear lista de regalos
4. **Invitación Pública** (`/i/:slug`):
   - Vista personalizada por template
   - Formulario RSVP con código de invitado
   - Selección de menú
   - Petición de canciones
5. **Reportes**:
   - Exportar confirmaciones
   - Exportar opciones de comida
   - Exportar canciones solicitadas

## ⚙️ Configuraciones Clave

- **DB Charset**: utf8mb4
- **Session Driver**: Database
- **Encryption Key**: Configurado en `.env`
- **Debug Toolbar**: Habilitado en desarrollo

## 🚫 Exclusiones

- Carpeta `vendor/` (dependencias Composer)
- Carpeta `.git/` (control de versiones)
- Carpeta `tests/` (pruebas unitarias)
- Contenido de `writable/` (cache, logs, debugbar)

---

**Nota**: Este contexto está optimizado para consulta rápida en sesiones futuras de IA. Para detalles de implementación, revisar directamente los archivos de código.
