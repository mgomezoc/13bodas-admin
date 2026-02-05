# Template Majestic - Documentación

## 📋 Descripción

**Majestic** es un template premium de invitación digital que incluye TODAS las características disponibles en el sistema de invitaciones. Diseñado con una estética elegante, moderna y completamente responsive.

## ✨ Características

### Secciones Incluidas:
1. **Hero** - Pantalla completa con imagen de fondo, nombres y CTA
2. **Countdown** - Contador regresivo animado hasta el día del evento
3. **Story** - Timeline de la historia de la pareja con imágenes
4. **Schedule** - Itinerario visual del día con horarios
5. **Location** - Mapa interactivo con Leaflet + información del venue
6. **Gallery** - Grid de imágenes con lightbox
7. **Registry** - Mesa de regalos con tarjetas elegantes
8. **Party** - Cortejo nupcial con fotos circulares
9. **RSVP** - Formulario completo de confirmación
10. **FAQ** - Acordeón de preguntas frecuentes

### Tecnologías:
- **HTML5 Semántico**
- **CSS Variables** para theming dinámico
- **AOS 2.3.1** para animaciones on scroll
- **Leaflet 1.9.4** para mapas interactivos
- **Bootstrap Icons 1.11.3**
- **Vanilla JavaScript** (sin dependencias jQuery)

### Diseño:
- **Mobile-First** totalmente responsive
- **Animaciones suaves** con AOS
- **Parallax effect** en hero
- **Lightbox personalizado** para galería
- **Theming dinámico** mediante CSS variables

## 🎨 Schema de Colores Default

```json
{
  "primary": "#8B7355",      // Marrón elegante
  "secondary": "#D4AF37",    // Dorado
  "accent": "#C9A97E",       // Beige/Champagne
  "text_dark": "#2C2C2C",
  "text_light": "#FFFFFF",
  "background": "#FAF9F6",   // Crema
  "background_secondary": "#F5F3EE"
}
```

## 📦 Estructura de Archivos

```
app/Views/templates/majestic/
├── layout.php                 # Layout principal con head y scripts
├── sections/
│   ├── hero.php              # Hero fullscreen
│   ├── countdown.php         # Contador regresivo
│   ├── story.php             # Historia de la pareja
│   ├── schedule.php          # Itinerario
│   ├── location.php          # Mapa y ubicación
│   ├── gallery.php           # Galería con lightbox
│   ├── registry.php          # Mesa de regalos
│   ├── party.php             # Cortejo nupcial
│   ├── rsvp.php              # Formulario RSVP
│   └── faq.php               # Preguntas frecuentes

public/templates/majestic/
├── css/
│   └── style.css             # Estilos principales (12KB)
└── js/
    └── main.js               # JavaScript principal (5KB)
```

## 🔧 Configuración

### Variables del Evento Requeridas:

```php
$event = [
    // Básico
    'couple_title' => 'María & Juan',
    'event_date_start' => '2025-06-15 18:00:00',
    'hero_image' => 'url_de_imagen',
    
    // Venue
    'venue_name' => 'Nombre del lugar',
    'venue_address' => 'Dirección completa',
    'venue_city' => 'Ciudad',
    'venue_state' => 'Estado',
    'venue_geo_lat' => 25.6866,
    'venue_geo_lng' => -100.3161,
    
    // Theme
    'theme_config' => '{"colors":{...},"fonts":{...}}',
    
    // Módulos opcionales
    'content_modules' => [...],  // Historia
    'schedule_items' => [...],   // Itinerario
    'gallery_items' => [...],    // Fotos
    'registry_items' => [...],   // Regalos
    'party_members' => [...],    // Cortejo
    'faqs' => [...]              // Preguntas
];
```

## 🚀 Uso en el Controlador

```php
public function view($slug)
{
    $event = $this->eventModel
        ->where('slug', $slug)
        ->first();
    
    // Cargar relaciones
    $event['content_modules'] = $this->contentModuleModel
        ->where('event_id', $event['id'])
        ->orderBy('sort_order')
        ->findAll();
    
    $event['schedule_items'] = $this->scheduleModel
        ->where('event_id', $event['id'])
        ->orderBy('time_start')
        ->findAll();
    
    // ... más relaciones
    
    // Determinar template
    $templateCode = $event['template_code'] ?? 'majestic';
    
    return view("templates/{$templateCode}/layout", ['event' => $event]);
}
```

## 📱 Responsive Breakpoints

- **Desktop**: > 768px (diseño completo)
- **Tablet**: 768px - 480px (ajustes grid)
- **Mobile**: < 480px (stack vertical)

## 🎯 Funcionalidades JavaScript

1. **Countdown Timer** - Actualiza cada segundo
2. **Leaflet Map** - Mapa interactivo con marcador custom
3. **Smooth Scroll** - Navegación suave entre secciones
4. **Gallery Lightbox** - Navegación de imágenes
5. **FAQ Accordion** - Expandir/colapsar
6. **RSVP Form** - Envío AJAX con validación
7. **Parallax Hero** - Efecto parallax al scroll
8. **AOS Init** - Animaciones on scroll

## 🌐 Dependencias CDN

```html
<!-- Fonts -->
Google Fonts: Cormorant Garamond, Montserrat, Great Vibes

<!-- Librerías -->
AOS: unpkg.com/aos@2.3.1
Leaflet: unpkg.com/leaflet@1.9.4
Bootstrap Icons: cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3
```

## ⚡ Performance

- **CSS**: ~12KB (minificado)
- **JS**: ~5KB (minificado)
- **Total Asset Size**: ~17KB + CDN
- **Lazy Loading**: Implementado en imágenes
- **AOS**: Load on demand

## 🎨 Personalización

### Cambiar Colores:
Edita el `schema_json` del template en la base de datos o sobrescribe en `theme_config` del evento.

### Agregar Sección:
1. Crear archivo en `sections/nueva_seccion.php`
2. Agregar include en `layout.php`
3. Agregar estilos en `style.css`

### Modificar Fuentes:
Actualiza `schema_json`:
```json
"fonts": {
  "heading": "Tu Fuente Heading",
  "body": "Tu Fuente Body",
  "accent": "Tu Fuente Cursiva"
}
```

## 📄 Licencia

© 2025 13Bodas.com - Template Premium

---

**Versión:** 1.0
**Fecha:** 2025-02-04
**Autor:** 13Bodas Development Team
