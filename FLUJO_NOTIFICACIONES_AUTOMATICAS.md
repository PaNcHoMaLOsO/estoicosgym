# 📧 Flujo de Notificaciones Automáticas - EstóicosGym

**Última actualización:** 6 de diciembre de 2025

---

## 🎯 Resumen Ejecutivo

El sistema de notificaciones automáticas funciona mediante **tareas programadas (cron jobs)** que se ejecutan diariamente en horarios específicos. Estas tareas:

1. **Programan** notificaciones basadas en criterios de clientes/inscripciones
2. **Envían** los emails a través de Resend API
3. **Reintentan** envíos fallidos automáticamente

---

## 📅 Horario de Ejecución Automática

### **Tareas Programadas en `routes/console.php`**

| Hora | Comando | Descripción |
|------|---------|-------------|
| **01:00 AM** | `inscripciones:actualizar-estados` | Actualiza estados de inscripciones (marca como vencidas) |
| **02:00 AM** | `pagos:sincronizar-estados` | Sincroniza estados de pagos |
| **03:00 AM** | `clientes:desactivar-vencidos` | Desactiva clientes con membresías vencidas |
| **08:00 AM** | `notificaciones:enviar --todo` | 🔔 **PROGRAMA Y ENVÍA NOTIFICACIONES** |
| **02:00 PM** | `notificaciones:enviar --reintentar` | 🔄 Reintenta notificaciones fallidas |

---

## 🔔 Tipos de Notificaciones Automáticas

### 1. **Membresía por Vencer** (`membresia_por_vencer`)

**¿Cuándo se envía?**
- **X días ANTES** de que venza la membresía (configurable en BD)
- Por defecto: **7 días de anticipación**

**Criterios para envío:**
```php
// Se buscan inscripciones que cumplan:
1. Estado: 100 (Activa)
2. fecha_vencimiento = HOY + dias_anticipacion (ej: hoy + 7 días)
3. Cliente activo: cliente.activo = true
4. Email válido: cliente.email != null AND != ''
5. No existe notificación previa enviada/pendiente del mismo tipo
```

**Ejemplo práctico:**
```
Hoy: 6 de diciembre de 2025
dias_anticipacion: 7 días
Fecha objetivo: 13 de diciembre de 2025

▸ Se envía notificación a clientes cuya membresía vence el 13/12/2025
```

**Contenido del email:**
- Saludo con nombre del cliente
- Nombre de la membresía (Mensual, Trimestral, etc.)
- Fecha exacta de vencimiento
- Días restantes en grande: "Vence en 7 días"
- Botón con teléfono de contacto
- Color: 🟡 Amarillo (advertencia)

---

### 2. **Membresía Vencida** (`membresia_vencida`)

**¿Cuándo se envía?**
- El **mismo día que vence** la membresía
- Se ejecuta a las **8:00 AM**

**Criterios para envío:**
```php
// Se buscan inscripciones que cumplan:
1. Estado: 100 (Activa - aún no marcada como vencida)
2. fecha_vencimiento = HOY
3. Cliente activo: cliente.activo = true
4. Email válido: cliente.email != null AND != ''
5. No existe notificación previa enviada/pendiente del mismo tipo
```

**Ejemplo práctico:**
```
Hoy: 6 de diciembre de 2025
Inscripción vence: 6 de diciembre de 2025

▸ A las 8:00 AM se programa notificación
▸ A las 1:00 AM del día siguiente se actualiza estado a 102 (Vencida)
```

**Contenido del email:**
- Aviso de membresía vencida
- Fecha de vencimiento
- Llamado urgente a renovar
- Color: 🔴 Rojo (urgente)

---

### 3. **Bienvenida** (`bienvenida`)

**¿Cuándo se envía?**
- **Cuando se crea una nueva inscripción**
- Se dispara desde el código al registrar cliente

**Criterios:**
```php
// Se envía al momento de crear la inscripción
1. Nueva inscripción creada
2. Cliente tiene email válido
3. Tipo de notificación "bienvenida" está activo
```

**Contenido del email:**
- Bienvenida personalizada con nombre
- Información de la membresía adquirida
- Fecha de inicio y vencimiento
- Información de contacto del gym
- Color: 🟢 Verde (positivo)

---

### 4. **Pago Pendiente** (`pago_pendiente`)

**¿Cuándo se envía?**
- Actualmente en desarrollo
- Se programaría para clientes con deudas

**Criterios propuestos:**
```php
1. Inscripción con monto_pendiente > 0
2. Cliente activo
3. X días después de la inscripción sin pago completo
```

---

### 5. **Pausa de Membresía** (`pausa`)

**¿Cuándo se envía?**
- Cuando un admin pausa una inscripción
- Se envía inmediatamente

**Contenido:**
- Confirmación de pausa
- Fecha de inicio de pausa
- Fecha de reactivación automática

---

### 6. **Activación de Membresía** (`activacion`)

**¿Cuándo se envía?**
- Cuando se reactiva una membresía pausada
- Se envía inmediatamente

**Contenido:**
- Confirmación de reactivación
- Nueva fecha de vencimiento ajustada
- Color: 🟢 Verde (positivo)

---

### 7. **Pago Completado** (`pago_completado`)

**¿Cuándo se envía?**
- Cuando un cliente completa el pago de su membresía
- Se envía al momento del registro del pago final

**Contenido:**
- Confirmación de pago
- Monto pagado
- Membresía vigente
- Color: 🟢 Verde (positivo)

---

## 🔄 Flujo Técnico Completo

### **Paso 1: Programación de Notificaciones (8:00 AM)**

```
┌─────────────────────────────────────────────────────────┐
│ Comando: notificaciones:enviar --todo                  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ 1. programarNotificacionesPorVencer()                  │
│    ├─ Lee dias_anticipacion de BD (ej: 7 días)        │
│    ├─ Calcula fecha_objetivo = HOY + 7                │
│    ├─ Busca inscripciones que vencen ese día          │
│    ├─ Filtra: activas, con email, cliente activo      │
│    ├─ Verifica que no exista notificación previa      │
│    └─ Crea registros en tabla notificaciones          │
│                                                         │
│ 2. programarNotificacionesVencidas()                   │
│    ├─ Busca inscripciones que vencen HOY              │
│    ├─ Filtra: activas, con email                      │
│    ├─ Verifica que no exista notificación previa      │
│    └─ Crea registros en tabla notificaciones          │
│                                                         │
│ 3. enviarPendientes()                                  │
│    ├─ Lee notificaciones con:                         │
│    │  • id_estado = 600 (Pendiente)                   │
│    │  • fecha_programada <= HOY                       │
│    │  • intentos < max_intentos                       │
│    ├─ Por cada notificación:                          │
│    │  ├─ Registra log "enviando"                      │
│    │  ├─ Envía email via Mail::html() → Resend       │
│    │  ├─ Si éxito: marcarComoEnviada()               │
│    │  │  • id_estado = 601 (Enviado)                 │
│    │  │  • fecha_envio = now()                       │
│    │  └─ Si falla: marcarComoFallida()               │
│    │     • id_estado = 602 (Fallido)                 │
│    │     • intentos++                                │
│    │     • error_mensaje = exception                 │
│    └─ Retorna estadísticas                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### **Paso 2: Reintento de Fallidas (2:00 PM)**

```
┌─────────────────────────────────────────────────────────┐
│ Comando: notificaciones:enviar --reintentar            │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ reintentarFallidas()                                   │
│    ├─ Lee notificaciones con:                         │
│    │  • id_estado = 602 (Fallido)                     │
│    │  • intentos < max_intentos (3)                   │
│    ├─ Registra log "reintentando"                     │
│    ├─ Cambia estado a 600 (Pendiente)                 │
│    ├─ Intenta enviar nuevamente                       │
│    └─ Actualiza según resultado                       │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🗄️ Estructura en Base de Datos

### **Tabla: `tipo_notificaciones`**

```sql
id | codigo                 | nombre                          | dias_anticipacion | activo
---|------------------------|---------------------------------|-------------------|-------
1  | membresia_por_vencer  | Membresía por Vencer           | 7                 | true
2  | membresia_vencida     | Membresía Vencida              | 0                 | true
3  | bienvenida            | Bienvenida                     | 0                 | true
4  | pago_pendiente        | Pago Pendiente                 | 3                 | true
5  | pausa                 | Pausa de Inscripción           | 0                 | true
6  | activacion            | Activación de Inscripción      | 0                 | true
7  | pago_completado       | Pago Completado                | 0                 | true
```

### **Tabla: `notificaciones`**

```sql
Columnas principales:
- id
- uuid
- id_tipo_notificacion → FK a tipo_notificaciones
- id_cliente → FK a clientes
- id_inscripcion → FK a inscripciones
- email_destino
- asunto
- contenido (HTML renderizado)
- id_estado (600=Pendiente, 601=Enviado, 602=Fallido, 603=Cancelado)
- fecha_programada
- fecha_envio
- intentos (contador)
- max_intentos (límite: 3)
- error_mensaje
- tipo_envio ('automatica' | 'manual') ← NUEVO
- enviado_por_user_id ← NUEVO (para manuales)
- nota_personalizada ← NUEVO (para manuales)
```

### **Tabla: `log_notificaciones`**

```sql
Registra cada evento:
- id_notificacion
- accion: 'programada', 'enviando', 'enviada', 'fallida', 'reintentando', 'cancelada'
- detalle
- ip_servidor
- created_at
```

---

## 📊 Estados de Notificaciones

| Código | Nombre | Descripción |
|--------|--------|-------------|
| **600** | Pendiente | Programada, esperando envío |
| **601** | Enviado | Enviada exitosamente |
| **602** | Fallido | Error al enviar (reintentable si intentos < 3) |
| **603** | Cancelado | Cancelada manualmente por admin |

---

## 🎨 Variables Disponibles en Plantillas

Las plantillas de email pueden usar estas variables que se reemplazan automáticamente:

```
{nombre}              → Nombre completo del cliente
{nombres}             → Solo nombres
{apellido}            → Apellido paterno
{email}               → Email del cliente
{celular}             → Celular del cliente
{membresia}           → Nombre de la membresía (Mensual, Trimestral, etc.)
{fecha_inicio}        → Fecha de inicio de inscripción
{fecha_vencimiento}   → Fecha de vencimiento
{dias_restantes}      → Días hasta el vencimiento
{monto_total}         → Precio total de la membresía
{monto_pagado}        → Total pagado hasta ahora
{monto_pendiente}     → Saldo pendiente
{fecha_pausa}         → Fecha de inicio de pausa (si aplica)
{fecha_reactivacion}  → Fecha de reactivación (si aplica)
{fecha_pago}          → Fecha del último pago
{monto_ultimo_pago}   → Monto del último pago
```

---

## 🔧 Comandos Manuales Disponibles

### **1. Programar y enviar todo**
```bash
php artisan notificaciones:enviar --todo
```

### **2. Solo programar (sin enviar)**
```bash
php artisan notificaciones:enviar --programar
```

### **3. Solo enviar pendientes**
```bash
php artisan notificaciones:enviar --enviar
```

### **4. Solo reintentar fallidas**
```bash
php artisan notificaciones:enviar --reintentar
```

### **5. Verificar sin enviar (testing)**
```bash
php artisan verificar:notificaciones
```

### **6. Ver estadísticas**
```bash
php artisan notificaciones:procesar --todo
# Al final muestra:
# - Pendientes
# - Enviadas hoy
# - Enviadas este mes
# - Fallidas
# - Total histórico
```

---

## ⚙️ Configuración del Sistema

### **Cambiar días de anticipación**

1. Ir a: **Admin → Notificaciones → Plantillas**
2. Editar: "Membresía por Vencer"
3. Cambiar campo: `dias_anticipacion`
4. Valores recomendados: 3, 5, 7, 10 días

### **Activar/Desactivar tipo de notificación**

1. Ir a: **Admin → Notificaciones → Plantillas**
2. Editar la plantilla
3. Toggle: `Activo` (ON/OFF)
4. Si está desactivado, no se programarán notificaciones de ese tipo

### **Cambiar horarios de ejecución**

Editar archivo: `routes/console.php`

```php
// Cambiar hora de envío (actualmente 08:00)
Schedule::command('notificaciones:enviar --todo')
    ->dailyAt('09:00') // ← Cambiar aquí
    ->withoutOverlapping();
```

---

## 🧪 Testing y Debugging

### **Ver notificaciones programadas hoy**
```sql
SELECT 
    n.id,
    c.nombres,
    c.apellido_paterno,
    tn.nombre AS tipo,
    n.id_estado,
    n.fecha_programada,
    n.intentos
FROM notificaciones n
JOIN clientes c ON n.id_cliente = c.id
JOIN tipo_notificaciones tn ON n.id_tipo_notificacion = tn.id
WHERE DATE(n.fecha_programada) = CURDATE()
ORDER BY n.created_at DESC;
```

### **Ver fallidas pendientes de reintento**
```sql
SELECT 
    n.id,
    c.email,
    n.intentos,
    n.error_mensaje,
    n.created_at
FROM notificaciones n
JOIN clientes c ON n.id_cliente = c.id
WHERE n.id_estado = 602
  AND n.intentos < n.max_intentos
ORDER BY n.created_at DESC;
```

### **Ver logs de una notificación específica**
```sql
SELECT * FROM log_notificaciones
WHERE id_notificacion = 123
ORDER BY created_at DESC;
```

### **Estadísticas rápidas**
```sql
SELECT 
    id_estado,
    COUNT(*) as total
FROM notificaciones
WHERE DATE(created_at) = CURDATE()
GROUP BY id_estado;
```

---

## 🚨 Casos Especiales

### **1. Cliente con múltiples inscripciones**
- Se envía UNA notificación por CADA inscripción activa
- Cada inscripción tiene su propia fecha de vencimiento

### **2. Cliente sin email**
- No se programa notificación
- Se registra en logs: "Cliente sin email válido"

### **3. Notificación ya enviada**
- No se duplica
- Se verifica en BD antes de crear

### **4. Inscripción pausada**
- NO se envían notificaciones de "por vencer" o "vencida"
- Solo se envía notificación de "pausa" al momento de pausar

### **5. Error al enviar**
- Se marca como fallida (estado 602)
- Se reintenta máximo 3 veces
- Después de 3 intentos, queda como fallida permanente

---

## 📈 Métricas del Sistema

El sistema registra automáticamente:

- ✅ Total de notificaciones enviadas (por día/mes/año)
- ⏳ Notificaciones pendientes
- ❌ Tasa de fallos
- 🔄 Reintentos exitosos
- ⏱️ Tiempo de procesamiento
- 📧 Emails por tipo de notificación

Estas métricas se pueden ver en:
- Panel de administración: `/admin/notificaciones`
- Logs del sistema: `storage/logs/laravel.log`
- Ejecutando comando: `php artisan notificaciones:procesar --todo`

---

## 🔐 Seguridad y Privacidad

- Los emails se envían desde: `estoicosgymlosangeles@gmail.com`
- API utilizada: **Resend** (https://resend.com)
- Los logs NO guardan contenido sensible de pagos
- Solo se almacenan: email destino, fecha, estado
- Cumple con buenas prácticas de GDPR

---

## 📞 Soporte

Si las notificaciones no se están enviando:

1. ✅ Verificar que el cron esté configurado en el servidor
2. ✅ Revisar logs: `storage/logs/laravel.log`
3. ✅ Ejecutar manualmente: `php artisan notificaciones:enviar --todo`
4. ✅ Verificar configuración de Resend en `.env`
5. ✅ Comprobar que las plantillas estén activas

---

**Documento actualizado:** 6 de diciembre de 2025  
**Versión del sistema:** 1.0  
**Laravel:** 10.x
