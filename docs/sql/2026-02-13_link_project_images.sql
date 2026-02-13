-- Vincula imágenes existentes de proyectos/eventos en media_assets.
-- Basado en la estructura de la BD cargada por el full setup y usando events como entidad de proyecto.

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
    src.event_id,
    src.file_url_original,
    src.alt_text,
    'gallery',
    src.sort_order,
    0,
    NOW()
FROM (
    SELECT 'c262aeda-5f15-41a9-9c30-a25b95d79fe2' AS event_id,
           'uploads/events/c262aeda-5f15-41a9-9c30-a25b95d79fe2/gallery/1769483980_94db59e1dd37f508698c.jpg' AS file_url_original,
           'Proyecto c262aeda - imagen 1' AS alt_text,
           1 AS sort_order
    UNION ALL
    SELECT 'e1234567-89ab-cdef-0123-456789abcdef', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769384634_9d4200b7b3e0065b6071.jpg', 'Ana y Carlos - imagen 1', 1
    UNION ALL
    SELECT 'e1234567-89ab-cdef-0123-456789abcdef', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_759360d0f75b6cef1723.png', 'Ana y Carlos - imagen 2', 2
    UNION ALL
    SELECT 'e1234567-89ab-cdef-0123-456789abcdef', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_6f071744199ecfdf1f5f.jpg', 'Ana y Carlos - imagen 3', 3
    UNION ALL
    SELECT 'e1234567-89ab-cdef-0123-456789abcdef', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_bc67e19aa87a1afbe52a.webp', 'Ana y Carlos - imagen 4', 4
    UNION ALL
    SELECT 'e1234567-89ab-cdef-0123-456789abcdef', 'uploads/events/e1234567-89ab-cdef-0123-456789abcdef/gallery/1769401533_1fec19bf309bc9a73fe9.jpeg', 'Ana y Carlos - imagen 5', 5
) AS src
INNER JOIN events e ON e.id = src.event_id
LEFT JOIN media_assets m
    ON m.event_id = src.event_id
   AND m.file_url_original = src.file_url_original
WHERE m.id IS NULL;
