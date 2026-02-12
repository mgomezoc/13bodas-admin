# 🔐 CONFIGURACIÓN DE WEBHOOK STRIPE - 13BODAS.COM

## ⚠️ ACCIÓN REQUERIDA ANTES DE PRODUCCIÓN

El archivo `.env` actualmente tiene un `STRIPE_WEBHOOK_SECRET` de pruebas locales que **NO funcionará** en producción. Debes generar un nuevo secret desde el Stripe Dashboard.

---

## 📋 PASO A PASO - CONFIGURACIÓN EN STRIPE DASHBOARD

### **1. Acceder al Dashboard de Stripe**
- URL: https://dashboard.stripe.com/test/webhooks
- Login con tu cuenta de Stripe
- Asegúrate de estar en **Test mode** (interruptor en la esquina superior derecha)

### **2. Crear nuevo Webhook Endpoint**
1. Click en botón **"Add endpoint"**
2. En el campo **"Endpoint URL"** ingresar:
   ```
   https://13bodas.com/webhooks/stripe
   ```
3. En **"Description"** (opcional):
   ```
   13Bodas - Webhook de confirmación de pagos de eventos
   ```

### **3. Seleccionar Eventos**
1. Click en **"Select events to listen to"**
2. En el buscador, escribir: `checkout.session.completed`
3. Marcar ÚNICAMENTE este evento:
   - ✅ `checkout.session.completed`
4. **NO marcar** otros eventos (por ahora)

### **4. Configuración Adicional (Opcional pero Recomendado)**
- **API version**: Usar la versión más reciente disponible
- **Events delivery**: Dejar en "Latest version"

### **5. Guardar Endpoint**
- Click en **"Add endpoint"**
- Stripe generará el endpoint y te mostrará su configuración

### **6. CRÍTICO: Copiar el Signing Secret**
1. Una vez creado el endpoint, verás una sección **"Signing secret"**
2. Click en **"Reveal"** o **"Click to reveal"**
3. El secret se verá así: `whsec_XXXXXXXXXXXXXXXXXXXXXXXXXXXXX`
4. Click en el ícono de **copiar** (📋)
5. **NO COMPARTAS ESTE SECRET** - es como una contraseña

### **7. Actualizar archivo `.env` de PRODUCCIÓN**
En el servidor de producción (13bodas.com), editar el archivo `.env`:

```env
# ANTES (secret de localhost - NO FUNCIONA EN PRODUCCIÓN):
STRIPE_WEBHOOK_SECRET=whsec_9dca69355121dd14801b7ad38b2bcd891163b2162d6036288193e16e616cf9a

# DESPUÉS (secret real de Stripe Dashboard):
STRIPE_WEBHOOK_SECRET=whsec_TU_NUEVO_SECRET_AQUI
```

**⚠️ MUY IMPORTANTE:**
- El secret de producción es DIFERENTE del de desarrollo
- NUNCA subas este secret a Git
- Asegúrate de que `.env` esté en `.gitignore`

---

## ✅ VERIFICACIÓN DE CONFIGURACIÓN

### **Test 1: Verificar que el webhook está activo**
1. En Stripe Dashboard → Webhooks
2. Tu endpoint debe aparecer con:
   - URL: `https://13bodas.com/webhooks/stripe`
   - Status: **Enabled** (switch verde)
   - Events: `checkout.session.completed`

### **Test 2: Enviar evento de prueba**
1. En la página del webhook, click en **"Send test webhook"**
2. Seleccionar: `checkout.session.completed`
3. Click en **"Send test webhook"**
4. Verificar respuesta:
   - ✅ Status: **200 OK** (verde)
   - ❌ Status: **400/500** (rojo) → revisar logs del servidor

### **Test 3: Revisar logs del servidor**
En el servidor, revisar: `writable/logs/log-YYYY-MM-DD.php`

Buscar estas líneas:
```php
[info] Inicio Webhook. Payload: XXX bytes. Firma: whsec_...
[info] PaymentService::persistSuccessfulPayment processed event={...}
```

**Si aparece error:**
```php
[error] Stripe Signature Error con secreto [...XXXX]: ...
```
→ El `STRIPE_WEBHOOK_SECRET` en `.env` NO coincide con el de Stripe Dashboard

---

## 🚀 PRUEBA COMPLETA DE PAGO EN PRODUCCIÓN

Una vez configurado el webhook, ejecutar esta prueba:

### **Paso 1: Crear evento demo**
1. Login en https://13bodas.com/admin
2. Crear un evento de prueba
3. Click en "Activar evento"

### **Paso 2: Completar pago con tarjeta de prueba**
Usar estos datos de prueba de Stripe:
- **Tarjeta**: `4242 4242 4242 4242`
- **Fecha**: `12/34` (cualquier fecha futura)
- **CVC**: `123`
- **ZIP**: `12345`

### **Paso 3: Validar el flujo**
Después de pagar, verificar:

1. **Redirección**: Debes ir a `https://13bodas.com/checkout/success`
2. **Mensaje**: "Pago confirmado. Tu evento fue activado correctamente"
3. **En la base de datos**:
   ```sql
   SELECT * FROM event_payments ORDER BY created_at DESC LIMIT 1;
   ```
   Debe mostrar:
   - `payment_provider = 'stripe'`
   - `status = 'completed'`
   - `provider_event_id` con un session ID: `cs_test_...`
   - `webhook_received_at` con timestamp reciente

4. **En Stripe Dashboard** → Webhooks → Click en tu endpoint → "Logs"
   Debe mostrar:
   - Evento: `checkout.session.completed`
   - Status: **200 OK**
   - Timestamp reciente

---

## ⚠️ PROBLEMAS COMUNES Y SOLUCIONES

### ❌ Error: "Invalid signature" en logs
**Causa**: STRIPE_WEBHOOK_SECRET incorrecto
**Solución**:
1. Verificar que copiaste el secret completo (sin espacios)
2. Asegurarte que actualizaste el `.env` de PRODUCCIÓN (no local)
3. Reiniciar el servidor web después de cambiar `.env`

### ❌ Error: "Webhook endpoint returned 500"
**Causa**: Error en el código PHP
**Solución**:
1. Revisar `writable/logs/` para ver el error exacto
2. Verificar que la base de datos tenga la columna `provider_event_id`
3. Verificar que el ENUM `payment_provider` incluye 'stripe'

### ❌ El webhook no se ejecuta
**Causa**: URL incorrecta o firewall bloqueando
**Solución**:
1. Verificar que `https://13bodas.com/webhooks/stripe` es accesible públicamente
2. Probar con cURL:
   ```bash
   curl -I https://13bodas.com/webhooks/stripe
   ```
   Debe retornar: `405 Method Not Allowed` (porque espera POST, no GET)
3. Verificar que no hay reglas de firewall bloqueando IPs de Stripe

---

## 📊 TRANSICIÓN A MODO LIVE (PRODUCCIÓN REAL)

Una vez que todo funcione en **Test Mode**, para activar pagos reales:

### **1. Activar Stripe Account**
- Completar verificación de identidad en Stripe
- Configurar cuenta bancaria para recibir pagos

### **2. Crear nuevo Webhook en Live Mode**
- Cambiar switch de Stripe Dashboard a **Live mode**
- Repetir TODOS los pasos anteriores para crear el webhook
- **IMPORTANTE**: El signing secret de Live será DIFERENTE

### **3. Actualizar `.env` de producción**
```env
# Cambiar las keys de test (pk_test_...) a live (pk_live_...)
STRIPE_PUBLISHABLE_KEY=pk_live_XXXXX
STRIPE_SECRET_KEY=sk_live_XXXXX
STRIPE_WEBHOOK_SECRET=whsec_NUEVO_SECRET_DE_LIVE_MODE
```

### **4. Probar con pago real mínimo**
- Hacer un pago de $1 MXN para verificar
- Una vez confirmado, ajustar el precio en `payment_settings`

---

## 📞 SOPORTE

Si tienes problemas:
1. Revisar logs: `writable/logs/log-YYYY-MM-DD.php`
2. Consultar documentación: https://stripe.com/docs/webhooks
3. Contactar soporte de Stripe: https://support.stripe.com

---

**Última actualización**: 2026-02-11
**Versión del sistema**: 13Bodas v2.0
