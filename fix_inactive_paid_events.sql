-- Script de Correcci\u00f3n: Activar eventos con pagos exitosos pero inactivos
-- Proyecto: 13Bodas
-- Fecha: Febrero 12, 2026

-- =======================================================
-- PASO 1: Identificar eventos con problemas
-- =======================================================

-- Eventos con pagos completados pero a\u00fan inactivos
SELECT 
    e.id,
    e.couple_title,
    e.slug,
    e.active,
    e.is_paid,
    e.service_status,
    e.paid_until,
    p.payment_reference,
    p.amount,
    p.payment_date,
    p.status as payment_status
FROM events e
INNER JOIN event_payments p ON e.id = p.event_id
WHERE p.status = 'completed'
  AND (e.active IS NULL OR e.active = 'I' OR e.active = 'N' OR e.active = 0)
ORDER BY p.payment_date DESC;

-- =======================================================
-- PASO 2: Activar eventos con pagos exitosos
-- =======================================================

-- Actualizar eventos con pagos completados
UPDATE events e
INNER JOIN event_payments p ON e.id = p.event_id
SET 
    e.active = 'Y',
    e.is_paid = 1,
    e.is_demo = 0,
    e.service_status = 'active',
    e.visibility = 'public',
    e.updated_at = NOW()
WHERE p.status = 'completed'
  AND (e.active IS NULL OR e.active = 'I' OR e.active = 'N' OR e.active = 0);

-- =======================================================
-- PASO 3: Actualizar fechas de vencimiento
-- =======================================================

-- Establecer paid_until si es null o ya venci\u00f3
UPDATE events e
INNER JOIN (
    SELECT 
        event_id,
        MAX(payment_date) as last_payment_date
    FROM event_payments
    WHERE status = 'completed'
    GROUP BY event_id
) p ON e.id = p.event_id
SET 
    e.paid_until = DATE_ADD(p.last_payment_date, INTERVAL 1 YEAR),
    e.updated_at = NOW()
WHERE e.is_paid = 1
  AND (e.paid_until IS NULL OR e.paid_until < NOW());

-- =======================================================
-- PASO 4: Caso espec\u00edfico "Manuel y Luz"
-- =======================================================

-- Identificar el evento
SELECT 
    e.id,
    e.couple_title,
    e.slug,
    e.active,
    e.is_paid,
    e.service_status,
    e.paid_until,
    e.payment_provider,
    e.payment_reference,
    p.payment_reference as payment_ref,
    p.amount,
    p.payment_date,
    p.status
FROM events e
LEFT JOIN event_payments p ON e.id = p.event_id
WHERE e.couple_title LIKE '%Manuel%Luz%'
   OR e.slug = 'manuel-y-luz'
ORDER BY p.payment_date DESC;

-- Activar el evento si tiene pago
UPDATE events e
INNER JOIN event_payments p ON e.id = p.event_id
SET 
    e.active = 'Y',
    e.is_paid = 1,
    e.is_demo = 0,
    e.service_status = 'active',
    e.visibility = 'public',
    e.paid_until = DATE_ADD(NOW(), INTERVAL 1 YEAR),
    e.updated_at = NOW()
WHERE (e.couple_title LIKE '%Manuel%Luz%' OR e.slug = 'manuel-y-luz')
  AND p.status = 'completed'
  AND (e.active IS NULL OR e.active != 'Y');

-- =======================================================
-- PASO 5: Verificaci\u00f3n final
-- =======================================================

-- Resumen de eventos por estado de activaci\u00f3n
SELECT 
    CASE 
        WHEN e.active = 'Y' THEN 'Activo'
        WHEN e.active = 'I' OR e.active = 'N' THEN 'Inactivo'
        WHEN e.active IS NULL THEN 'NULL (Verificar)'
        ELSE e.active
    END as estado_active,
    COUNT(*) as total_eventos,
    SUM(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END) as con_pagos
FROM events e
LEFT JOIN event_payments p ON e.id = p.event_id AND p.status = 'completed'
GROUP BY estado_active;

-- Verificar eventos activados correctamente
SELECT 
    e.id,
    e.couple_title,
    e.active,
    e.is_paid,
    e.service_status,
    e.paid_until,
    COUNT(p.id) as total_payments
FROM events e
LEFT JOIN event_payments p ON e.id = p.event_id AND p.status = 'completed'
WHERE e.active = 'Y'
  AND e.is_paid = 1
GROUP BY e.id, e.couple_title, e.active, e.is_paid, e.service_status, e.paid_until
HAVING total_payments > 0;

-- =======================================================
-- NOTAS DE EJECUCI\u00d3N
-- =======================================================
-- 1. Hacer backup antes de ejecutar: mysqldump -u root -p 13bodas > backup_antes_fix.sql
-- 2. Ejecutar las consultas SELECT primero para revisar qu\u00e9 se va a cambiar
-- 3. Ejecutar los UPDATE despu\u00e9s de verificar
-- 4. Verificar los resultados con las consultas finales
-- 5. Si algo sale mal, restaurar desde el backup
