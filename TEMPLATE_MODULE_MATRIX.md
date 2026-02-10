# Matriz de uso de módulos por template

## 1) ¿Qué hace la sección **Módulos** en Admin?

La pantalla **Admin > Eventos > Módulos** permite editar, por cada evento:

- `module_type`: el tipo de bloque semántico (ej. `couple_info`, `timeline`, `faq`).
- `css_id`: identificador visual/ancla CSS del módulo.
- `sort_order`: orden de render en frontend.
- `is_enabled`: si ese módulo participa o no en la invitación pública.
- `content_payload` (JSON): configuración/datos complementarios que algunos templates sí leen.

Esto se guarda vía `ContentModules::update()` y el frontend público carga solo módulos habilitados, ordenados por `sort_order`, desde `Invitation::view()`.

---

## 2) Matriz por familia de templates

> Leyenda:
> - ✅ = se usa de forma explícita en el template.
> - ⚠️ = uso parcial o fallback.
> - ❌ = no hay lectura explícita encontrada.

| Familia / Template | Cómo consume módulos | Tipos usados explícitamente | Dependencia fuerte de `content_payload` | `css_id` usado en render | Tipos normalmente ignorados por esa familia |
|---|---|---|---|---|---|
| `lovely`, `liebe`, `olivia`, `neela`, `feelings`, `couple-heart` | `findModule($modules, 'tipo')` | `lovely.couple`/`couple_info`, `lovely.copy`, `story`/`timeline`, `schedule`, `faq` | ✅ Alta (textos, story, schedule, faq) | ❌ | `music`, `custom_html`, `accommodation` (sin lectura directa en estas vistas) |
| `sukun` | `skFindModule(...types)` | `lovely.copy`/`sukun.copy`, `lovely.couple`/`sukun.couple`/`couple_info`, `story`/`timeline`, `countdown`, `venue`, `rsvp` | ✅ Alta | ❌ | `music`, `custom_html`, `accommodation` |
| `weddingo`, `vibranza`, `aurora`, `granboda` | Mapa `$moduleData[$module_type]` | `couple_info`, `timeline`, `schedule`, `faq`, `custom_html`, `music`, `accommodation`, `registry`, `venue` | ✅ Muy alta | ❌ | Tipos específicos `lovely.*`, `sukun.*` |
| `solene` | helper con búsqueda puntual + decode payload | `lovely.copy`, `lovely.couple`, `story`/`timeline`, `schedule`, `faq` (similar a familia finder) | ✅ Alta | ❌ | `music`, `custom_html`, `accommodation` |
| `majestic` | Uso mínimo de módulos + fuerte uso de datos legacy (`event`, `scheduleItems`, etc.) | ⚠️ Solo `story`/`timeline` en sección de historia | ⚠️ Parcial (story) | ❌ | Casi todos los demás tipos de módulo; usa más data tradicional precargada |

---

## 3) Campos que realmente impactan hoy

### Impacto alto (no remover / priorizar QA)
1. `module_type`
   - Si no coincide con lo que el template busca, el contenido no se renderiza.
2. `is_enabled`
   - `Invitation` solo carga módulos habilitados.
3. `sort_order`
   - Define el orden en que llegan al template.
4. `content_payload`
   - En la mayoría de templates modernos es la fuente de textos/configuración.

### Impacto bajo en frontend actual
- `css_id`
  - En los templates revisados no aparece lectura explícita para decidir contenido.
  - Mantenerlo es útil para anclas/estilos futuros y compatibilidad, pero no es crítico para render actual.

---

## 4) Recomendación para eliminar templates obsoletos (plan seguro)

### Fase A: Inventario y clasificación
1. En admin de templates, extraer por template:
   - `usage_count` (eventos activos hoy)
   - `usage_count_total` (histórico)
2. Clasificar:
   - **Eliminar candidato**: `usage_count = 0` y `usage_count_total = 0`.
   - **Deprecar primero**: `usage_count = 0` y `usage_count_total > 0`.
   - **No eliminar**: `usage_count > 0`.

### Fase B: Deprecación controlada (sin romper producción)
1. Marcar `is_public = 0` y `is_active = 0` para que no se vendan de nuevo.
2. Mantener archivos por 1 o 2 releases mientras monitoreas errores/visitas.
3. Preparar migración automática (si aplica) para mover eventos históricos a templates vigentes.

### Fase C: Eliminación definitiva
1. Confirmar que no existan eventos activos apuntando al template (`event_templates`).
2. Eliminar registro en tabla `templates` (el controlador ya bloquea eliminación si está en uso).
3. Eliminar carpeta física `app/Views/templates/<template_code>/` y assets asociados.
4. Ejecutar smoke test de invitaciones públicas y panel admin.

---

## 5) Recomendación práctica inmediata

Si quieres, el siguiente paso operativo más seguro es:

1. Generar una lista de templates con `usage_count / usage_count_total`.
2. Proponerte una tabla concreta de:
   - **Eliminar ahora**
   - **Deprecar**
   - **Conservar**
3. Después aplicar una limpieza por lotes (primero BD, luego archivos), con checklist de rollback.

