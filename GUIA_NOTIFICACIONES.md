# 📧 Guía del Sistema de Notificaciones - EstoicosGym

## Acceso
**Menú:** `Administración` → `Notificaciones`

---

## 🔄 Flujo Visual del Sistema

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MÓDULO DE NOTIFICACIONES                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────┐     ┌──────────────────┐     ┌──────────────┐ │
│  │ 📋 LISTADO       │     │ ✨ NUEVA         │     │ 🎨 PLANTILLAS│ │
│  │ (índice)         │     │ NOTIFICACIÓN     │     │              │ │
│  │                  │     │ (crear)          │     │              │ │
│  │ Ver historial    │     │ Envío manual     │     │ Editar       │ │
│  │ de todas las     │     │ a grupos         │     │ emails       │ │
│  │ notificaciones   │     │ personalizados   │     │ automáticos  │ │
│  └──────────────────┘     └──────────────────┘     └──────────────┘ │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📌 Los 3 Componentes Principales

### 1️⃣ **Listado de Notificaciones** (Página Principal)
**URL:** `/admin/notificaciones`

**Qué puedes hacer:**
- Ver todas las notificaciones enviadas
- Ver el estado de cada una (Pendiente, Enviado, Fallido)
- **Botón "Ejecutar Automáticas"** → Busca y envía notificaciones según las reglas automáticas
- **Botón "Nueva Notificación"** → Ir a crear envío masivo personalizado
- Ver logs de cada notificación
- Reenviar notificaciones fallidas

---

### 2️⃣ **Nueva Notificación** (Envío Masivo Manual)
**URL:** `/admin/notificaciones/crear`

**Flujo:**
```
┌───────────────────────────────────────────────────────────────────────┐
│                      CREAR NOTIFICACIÓN MASIVA                         │
│                                                                        │
│  1. SELECCIONA GRUPO DESTINATARIO                                     │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │ ○ Clientes con Pagos Pendientes (X personas)                   │   │
│  │ ○ Inscripciones por vencer en 7 días (X personas)              │   │
│  │ ○ Inscripciones por vencer en 15 días (X personas)             │   │
│  │ ○ Inscripciones Vencidas (X personas)                          │   │
│  │ ○ Inscripciones Activas (X personas)                           │   │
│  │ ○ Por Membresía: [Seleccionar membresía ▼]                     │   │
│  │ ○ Todos los clientes con email (X personas)                    │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                                                        │
│  2. ESCRIBE TU MENSAJE                                                │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │ Asunto: _________________________________________________      │   │
│  │                                                                │   │
│  │ Mensaje:                                                       │   │
│  │ ┌────────────────────────────────────────────────────────┐     │   │
│  │ │ Hola {nombre},                                         │     │   │
│  │ │                                                        │     │   │
│  │ │ Te escribimos para...                                  │     │   │
│  │ └────────────────────────────────────────────────────────┘     │   │
│  │                                                                │   │
│  │ Variables disponibles: {nombre} {email}                       │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                                                        │
│  3. PREVISUALIZACIÓN EN TIEMPO REAL ─────────────────────────────────│
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │ De: Estoicos Gym <onboarding@resend.dev>                       │   │
│  │ Para: cliente@ejemplo.com                                      │   │
│  │ Asunto: Tu asunto aquí...                                      │   │
│  │ ─────────────────────────────────────────────────────────────  │   │
│  │ Hola Juan Pérez,                                               │   │
│  │                                                                │   │
│  │ Te escribimos para...                                          │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                                                        │
│  4. OPCIONES                                                          │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │ ☐ Programar para más tarde                                    │   │
│  │   Fecha y hora: [_____________________]                        │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                                                        │
│         ┌─────────────────────────────────────────────────┐           │
│         │  📤 ENVIAR NOTIFICACIÓN                         │           │
│         └─────────────────────────────────────────────────┘           │
└───────────────────────────────────────────────────────────────────────┘
```

---

### 3️⃣ **Plantillas** (Para Notificaciones Automáticas)
**URL:** `/admin/notificaciones/plantillas`

**Tipos de plantillas predefinidas:**

| Tipo | Descripción | Cuándo se envía |
|------|-------------|-----------------|
| 🕐 **Membresía por Vencer** | Aviso de próximo vencimiento | X días antes de vencer |
| 📅 **Membresía Vencida** | Aviso de vencimiento | El día que vence |
| 👋 **Bienvenida** | Mensaje de bienvenida | Al inscribirse |
| 💳 **Pago Pendiente** | Recordatorio de pago | Cuando hay deuda |
| ✅ **Renovación Exitosa** | Confirmación | Al renovar |

**Variables disponibles en plantillas:**
- `{nombre}` → Nombre del cliente
- `{membresia}` → Tipo de membresía
- `{fecha_vencimiento}` → Fecha de vencimiento
- `{dias_restantes}` → Días que faltan
- `{monto_pendiente}` → Monto a pagar

---

## 🔀 Diferencia: Automáticas vs Manuales

```
┌─────────────────────────────────────┐    ┌─────────────────────────────────────┐
│     NOTIFICACIONES AUTOMÁTICAS       │    │      NOTIFICACIONES MANUALES        │
├─────────────────────────────────────┤    ├─────────────────────────────────────┤
│                                      │    │                                      │
│ • Se basan en las PLANTILLAS         │    │ • Tú escribes el mensaje             │
│                                      │    │                                      │
│ • Se disparan con el botón           │    │ • Tú eliges el grupo de              │
│   "Ejecutar Automáticas"             │    │   destinatarios                      │
│                                      │    │                                      │
│ • Buscan automáticamente:            │    │ • Puedes usar desde "Nueva           │
│   - Quién está por vencer            │    │   Notificación"                      │
│   - Quién ya venció                  │    │                                      │
│   - Quién tiene pagos pendientes     │    │ • Ideal para:                        │
│                                      │    │   - Promociones                      │
│ • Usan las plantillas HTML           │    │   - Avisos especiales                │
│   prediseñadas                       │    │   - Comunicados                      │
│                                      │    │   - Eventos                          │
│                                      │    │                                      │
└─────────────────────────────────────┘    └─────────────────────────────────────┘
```

---

## 📧 Configuración de Correo

**Proveedor actual:** Resend  
**Email remitente:** onboarding@resend.dev

Para cambiar el remitente, edita el archivo `.env`:
```env
MAIL_FROM_ADDRESS="tu-email@tudominio.com"
MAIL_FROM_NAME="Estoicos Gym"
```

---

## 🚀 Cómo Usar

### Para enviar un comunicado masivo:
1. Ve a `Notificaciones` 
2. Clic en **"Nueva Notificación"** (botón verde)
3. Selecciona el grupo (ej: "Todos los clientes")
4. Escribe asunto y mensaje
5. Revisa la previsualización
6. Clic en **"Enviar Notificación"**

### Para ejecutar notificaciones automáticas:
1. Ve a `Notificaciones`
2. Clic en **"Ejecutar Automáticas"** (botón azul)
3. El sistema busca y envía según las reglas de las plantillas

### Para editar una plantilla automática:
1. Ve a `Notificaciones`
2. Clic en **"Plantillas"**
3. Busca el tipo de notificación
4. Clic en **"Editar"**
5. Modifica el HTML del email
6. Guarda los cambios

---

## 📊 Estados de Notificación

| Código | Estado | Significado |
|--------|--------|-------------|
| 600 | Pendiente | Programada, aún no enviada |
| 601 | Enviado | Email entregado correctamente |
| 602 | Fallido | Error al enviar (ver logs) |
| 603 | Cancelado | Cancelada manualmente |

---

## 🔧 Rutas Disponibles

| Acción | Ruta |
|--------|------|
| Listado | `/admin/notificaciones` |
| Nueva Notificación | `/admin/notificaciones/crear` |
| Plantillas | `/admin/notificaciones/plantillas` |
| Ver detalle | `/admin/notificaciones/{id}` |
| Ver logs | `/admin/notificaciones/{id}/logs` |

---

*Documentación actualizada - Sistema EstoicosGym*
