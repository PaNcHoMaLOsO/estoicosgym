# 📧 Configuración Completa de Emails - PROGYM

**Última actualización:** 6 de diciembre de 2025  
**Servicio:** Sender.net SMTP  
**Estado:** ✅ PRODUCCIÓN

---

## 🎯 Resumen Ejecutivo

Sistema de notificaciones por email totalmente funcional usando **Sender.net** con:
- 2,500 emails gratis/mes
- Sin restricciones de destinatarios
- Panel de estadísticas incluido
- 7 plantillas pre-configuradas

---

## ⚡ INICIO RÁPIDO (5 minutos)

### 1. Crear Cuenta en Sender.net (2 min)
```
1. Ve a: https://www.sender.net/
2. Click "Sign Up"
3. Email: estoicosgymlosangeles@gmail.com
4. Verifica tu email
```

### 2. Obtener Credenciales SMTP (1 min)
```
1. Login: https://app.sender.net/
2. Ve a: Settings → SMTP Settings
3. Click "Enable SMTP"
4. Copia las credenciales
```

### 3. Configurar Laravel (1 min)

Actualiza tu `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sender.net
MAIL_PORT=587
MAIL_USERNAME=estoicosgymlosangeles@gmail.com
MAIL_PASSWORD=SND_tu_token_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=estoicosgymlosangeles@gmail.com
MAIL_FROM_NAME="PRO GYM"
```

Limpia cache:
```bash
php artisan config:clear
```

### 4. Probar (30 seg)

```bash
php scripts/test_sender.php
```

---

## 📋 CONFIGURACIÓN COMPLETA PASO A PASO

### 📝 PASO 1: Registro y Verificación

#### 1.1. Registro en Sender.net
1. Ir a https://www.sender.net/
2. Click en **"Start Free Trial"** o **"Sign Up"**
3. Completar formulario:
   - Email: `estoicosgymlosangeles@gmail.com`
   - Password: (contraseña segura)
   - Company Name: `PRO GYM`

#### 1.2. Verificar Email
1. Revisar bandeja de entrada
2. Click en link de verificación
3. Completar perfil básico

---

### 🔑 PASO 2: Obtener Credenciales SMTP

#### 2.1. Acceder a Configuración SMTP
1. Login en https://app.sender.net/
2. Ir a **Settings** → **SMTP Settings**
3. O directamente: https://app.sender.net/settings/smtp

#### 2.2. Activar SMTP
1. Click en **"Enable SMTP"**
2. Se generarán las credenciales:

```
SMTP Server: smtp.sender.net
Port: 587 (TLS) o 465 (SSL)
Username: estoicosgymlosangeles@gmail.com
Password: SND_abc123xyz456...  (token auto-generado)
```

#### 2.3. Copiar Credenciales
⚠️ **IMPORTANTE:** Guarda estas credenciales, las necesitarás en Laravel.

---

### ⚙️ PASO 3: Configurar Laravel

#### 3.1. Actualizar .env

Abre `.env` y actualiza la sección de MAIL:

```env
# ════════════════════════════════════════════════════════
# EMAIL - SENDER.NET (ACTIVO)
# ════════════════════════════════════════════════════════
# Plan gratuito: 2,500 emails/mes
# Sin restricciones - Envía a cualquier email

MAIL_MAILER=smtp
MAIL_HOST=smtp.sender.net
MAIL_PORT=587
MAIL_USERNAME=estoicosgymlosangeles@gmail.com
MAIL_PASSWORD=SND_tu_token_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=estoicosgymlosangeles@gmail.com
MAIL_FROM_NAME="PRO GYM"
```

#### 3.2. Limpiar Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

### 🧪 PASO 4: Probar el Sistema

#### Opción 1: Script de Prueba Rápida
```bash
php scripts/test_sender.php
```

Deberías ver:
```
✅ Configuración detectada: SENDER.NET
✅ Email de prueba enviado exitosamente
```

#### Opción 2: Comando Artisan con Plantilla
```bash
php artisan test:email estoicosgymlosangeles@gmail.com bienvenida
```

---

## 🎨 PLANTILLAS DISPONIBLES

Todas pre-cargadas en la base de datos:

### 1. **Bienvenida** (`bienvenida`)
- Confirmación de inscripción
- Color: Verde #2EB872
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{fecha_inicio}`, `{fecha_vencimiento}`, `{precio}`

### 2. **Membresía por Vencer** (`membresia_por_vencer`)
- Recordatorio días antes del vencimiento
- Color: Amarillo #FFC107
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{dias_restantes}`, `{fecha_vencimiento}`

### 3. **Membresía Vencida** (`membresia_vencida`)
- Alerta de membresía expirada
- Color: Rojo #E0001A
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{fecha_vencimiento}`

### 4. **Pago Pendiente** (`pago_pendiente`)
- Recordatorio de pago parcial/pendiente
- Color: Rojo #E0001A (borde)
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{monto_pendiente}`, `{monto_total}`, `{fecha_vencimiento}`

### 5. **Pago Completado** (`pago_completado`)
- Confirmación de pago exitoso
- Color: Verde #2EB872
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{monto_pagado}`, `{saldo_pendiente}`

### 6. **Pausa de Inscripción** (`pausa_inscripcion`)
- Notificación de pausa de membresía
- Color: Amarillo #FFC107
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{fecha_pausa}`, `{motivo}`

### 7. **Activación de Inscripción** (`activacion_inscripcion`)
- Notificación de reactivación
- Color: Verde #2EB872
- Variables: `{nombre_cliente}`, `{nombre_membresia}`, `{fecha_activacion}`

---

## 🎨 Paleta de Colores PROGYM

| Elemento | Color | Hex | Uso |
|----------|-------|-----|-----|
| Header/Footer | Negro carbón | #101010 | Identidad fuerte |
| Botones CTA | rojo energía | #E0001A | Llamadas a la acción |
| Éxito | Verde | #2EB872 | Confirmaciones |
| Recordatorio | Amarillo | #FFC107 | Alertas suaves |
| Urgente | Rojo | #E0001A | Vencimientos/deudas |
| Texto principal | Negro/Gris | #101010 / #505050 | Lectura |
| Bordes | Gris acero | #C7C7C7 | Separadores |
| Fondos suaves | Gris claro | #F5F5F5 | Backgrounds |

**Documentación detallada:** Ver `COHERENCIA_COLORES_EMAILS.md`

---

## 📞 Datos de Contacto

```
Email: progymlosangeles@gmail.com
Teléfono: +56 9 5096 3143
WhatsApp: https://wa.me/56950963143
Instagram: @progym_losangeles
Google Maps: https://www.google.com/maps/place/Gimnasio+ProGym
```

---

## 🚀 Migración a Producción

### 1. Verificar Dominio (Opcional)
- Ir a https://app.sender.net/settings/domains
- Agregar dominio personalizado (ej: `progym.cl`)
- Configurar registros DNS (SPF, DKIM)
- Esperar verificación

### 2. Actualizar .env en Producción
```env
MAIL_HOST=smtp.sender.net
MAIL_FROM_ADDRESS="contacto@tudominio.cl"
MAIL_FROM_NAME="PROGYM Los Ángeles"
```

### 3. Ejecutar Migraciones
```bash
php artisan migrate --force
php artisan db:seed --class=NotificacionesSeeder --force
```

### 4. Probar en Producción
```bash
php artisan test:email tu@email.com bienvenida
```

---

## 📊 Monitoreo y Estadísticas

### Panel de Sender.net
- **URL:** https://app.sender.net/
- **Campaigns:** Ver emails enviados y estadísticas
- **SMTP Settings:** Gestionar credenciales

### Logs de Laravel
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Buscar errores de email
grep "email" storage/logs/laravel.log
```

---

## 🔧 Solución de Problemas

### Error de Conexión
```bash
# Prueba con SSL en lugar de TLS
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

### Error de Autenticación
- Verifica que el token empiece con `SND_`
- Sin espacios antes/después del token
- Regenera token en Sender.net si es necesario

### No Llegan los Emails
1. Revisar spam/correo no deseado
2. Esperar 1-2 minutos
3. Verificar en panel: https://app.sender.net/campaigns
4. Revisar logs: `storage/logs/laravel.log`

---

## ✅ Checklist de Verificación

- [ ] Cuenta en Sender.net creada y verificada
- [ ] Credenciales SMTP obtenidas
- [ ] `.env` actualizado con credenciales correctas
- [ ] Cache de Laravel limpiado
- [ ] Script de prueba ejecutado exitosamente
- [ ] Email de prueba recibido
- [ ] Plantillas cargadas en base de datos
- [ ] Notificaciones automáticas programadas (cron)

---

## ✅ Ventajas de Sender.net sobre Resend

| Característica | Sender.net | Resend |
|----------------|-----------|---------|
| Emails gratis/mes | 2,500 | 100 |
| Restricciones testing | ❌ Ninguna | ✅ Solo tu email |
| Verificación dominio | Opcional | Requerida |
| Panel estadísticas | ✅ Visual | ✅ Básico |
| Tarjeta requerida | ❌ No | ❌ No |
| Drag & Drop templates | ✅ Sí | ❌ No |

---

## 📝 Notas Técnicas

- Plantillas en tabla `tipo_notificaciones`
- Servicio `NotificacionService` maneja envíos
- Logs en `storage/logs/laravel.log`
- Límite: 2,500 emails/mes (plan gratuito)
- Rate limit: 10 emails/segundo

---

## 📚 Documentación Relacionada

- **Diseño de emails:** `COHERENCIA_COLORES_EMAILS.md`
- **Flujo automático:** `FLUJO_NOTIFICACIONES_AUTOMATICAS.md`
- **Auditorías:** `docs/auditorias/`
- **Planes futuros:** `docs/planes/`

---

**Version:** 2.0.0  
**Última actualización:** 6 de diciembre de 2025  
**Estado:** ✅ PRODUCCIÓN READY
