-- ============================================================================
-- 13Bodas - Vincular imágenes de proyectos a media_assets
-- Fuente esperada principal: public/assets/images/proyectos/{slug}/*
-- Fallback histórico:        public/uploads/events/{event_uuid}/gallery/*
--
-- Este script usa una tabla temporal con slug + ruta para evitar hardcodear UUIDs.
-- ============================================================================

START TRANSACTION;

CREATE TEMPORARY TABLE tmp_project_images (
    event_slug VARCHAR(100) NOT NULL,
    file_url_original VARCHAR(600) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 1
);

-- --------------------------------------------------------------------------
-- REGISTROS ENCONTRADOS EN ESTE REPO (fallback histórico en uploads/events)
-- --------------------------------------------------------------------------
INSERT INTO tmp_project_images (event_slug, file_url_original, alt_text, sort_order) VALUES
('ana-y-carlos', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769384634_9d4200b7b3e0065b6071.jpg', 'ana-y-carlos-01', 1),
('ana-y-carlos', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_759360d0f75b6cef1723.png', 'ana-y-carlos-02', 2),
('ana-y-carlos', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_6f071744199ecfdf1f5f.jpg', 'ana-y-carlos-03', 3),
('ana-y-carlos', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_bc67e19aa87a1afbe52a.webp', 'ana-y-carlos-04', 4),
('ana-y-carlos', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_1fec19bf309bc9a73fe9.jpeg', 'ana-y-carlos-05', 5);

-- --------------------------------------------------------------------------
-- Si ya tienes carpetas en public/assets/images/proyectos/{slug}/,
-- agrega aquí más filas con event_slug y file_url_original:
-- ('mi-slug', 'assets/images/proyectos/mi-slug/foto-01.jpg', 'mi-slug-01', 1)
-- --------------------------------------------------------------------------

INSERT INTO media_assets (
    id,
    event_id,
    file_url_original,
    alt_text,
    category,
    sort_order,
    is_private,
    created_at
)
SELECT
    UUID(),
    e.id,
    t.file_url_original,
    t.alt_text,
    'gallery',
    t.sort_order,
    0,
    NOW()
FROM tmp_project_images t
INNER JOIN events e
    ON e.slug = t.event_slug
LEFT JOIN media_assets m
    ON m.event_id = e.id
   AND m.file_url_original = t.file_url_original
WHERE m.id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_project_images;

COMMIT;
