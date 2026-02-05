-- ================================================
-- Template Majestic - Inserción en Base de Datos
-- ================================================
-- Ejecuta este SQL en la base de datos invitaciones_ci4

USE invitaciones_ci4;

INSERT INTO templates (
    code, 
    name, 
    description, 
    preview_url, 
    thumbnail_url, 
    is_public, 
    is_active, 
    sort_order, 
    schema_json, 
    meta_json,
    created_at,
    updated_at
) VALUES (
    'majestic',
    'Majestic - Full Feature',
    'Template premium con todas las características: hero fullscreen, countdown animado, historia de pareja, itinerario interactivo, mapa Leaflet, galería con lightbox, registro de regalos, cortejo nupcial, formulario RSVP y FAQ. Diseño elegante, mobile-first con animaciones AOS. Ideal para bodas de lujo que requieren mostrar toda la información del evento.',
    'https://via.placeholder.com/1200x800/8B7355/ffffff?text=Majestic+Preview',
    'https://via.placeholder.com/400x300/8B7355/ffffff?text=Majestic',
    1,
    1,
    1,
    '{
  "colors": {
    "primary": "#8B7355",
    "secondary": "#D4AF37",
    "accent": "#C9A97E",
    "text_dark": "#2C2C2C",
    "text_light": "#FFFFFF",
    "background": "#FAF9F6",
    "background_secondary": "#F5F3EE"
  },
  "fonts": {
    "heading": "Cormorant Garamond",
    "body": "Montserrat",
    "accent": "Great Vibes"
  },
  "settings": {
    "animation_duration": "800",
    "enable_aos": true,
    "enable_countdown": true,
    "enable_gallery_lightbox": true,
    "map_zoom": 15
  }
}',
    '{
  "sections": [
    "hero",
    "countdown",
    "story",
    "schedule",
    "location",
    "gallery",
    "registry",
    "party",
    "rsvp",
    "faq"
  ],
  "features": {
    "responsive": true,
    "aos_animations": true,
    "interactive_map": true,
    "image_gallery": true,
    "countdown_timer": true,
    "rsvp_form": true,
    "wedding_party": true,
    "timeline_story": true
  },
  "libraries": {
    "aos": "2.3.1",
    "leaflet": "1.9.4",
    "bootstrap_icons": "1.11.3"
  }
}',
    NOW(),
    NOW()
);

-- Verificar inserción
SELECT * FROM templates WHERE code = 'majestic';
