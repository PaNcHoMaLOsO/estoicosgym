# 📋 LISTADO COMPLETO DE CAMPOS DEL FORMULARIO DE CLIENTE

**Versión:** 2.0 (Mejorada con tipos de pago)
**Última actualización:** 28 de noviembre de 2025
**Rama:** feature/mejora-flujo-clientes

---

## 🔹 PASO 1: DATOS DEL CLIENTE (11 campos)

### Sección: Identificación
- **`run_pasaporte`** - RUT/Pasaporte (opcional)
  - Formato: Ej: 7.882.382-4
  - Validación: opcional

### Sección: Datos Personales
- **`nombres`** ⭐ - Nombres (REQUERIDO)
- **`apellido_paterno`** ⭐ - Apellido Paterno (REQUERIDO)
- **`apellido_materno`** - Apellido Materno (opcional)
- **`fecha_nacimiento`** - Fecha de Nacimiento (opcional)
  - Tipo: date

### Sección: Contacto
- **`email`** ⭐ - Email (REQUERIDO)
  - Tipo: email
  - Validación: formato válido de email
- **`celular`** ⭐ - Celular (REQUERIDO)
  - Tipo: tel

### Sección: Contacto de Emergencia
- **`contacto_emergencia`** - Nombre del Contacto (opcional)
- **`telefono_emergencia`** - Teléfono del Contacto (opcional)
  - Tipo: tel

### Sección: Domicilio
- **`direccion`** - Dirección (opcional)

### Sección: Observaciones
- **`observaciones`** - Notas Adicionales (opcional)
  - Tipo: textarea
  - Filas: 3

### Botones PASO 1:
- ✅ **Guardar Cliente** - Crea cliente sin membresía ni pago
- ➡️ **Siguiente** - Avanza a PASO 2

---

## 🔹 PASO 2: MEMBRESÍA (7 campos + Display)

### Display (Lectura):
- **📌 Cliente** - Nombre del cliente (actualiza en tiempo real desde PASO 1)

### Sección: Seleccionar Membresía
- **`id_membresia`** ⭐ - Membresía (REQUERIDO)
  - Tipo: select
  - Opciones: Se cargan desde base de datos
  - Evento: onChange dispara `actualizarPrecio()`

- **`fecha_inicio`** ⭐ - Fecha de Inicio (REQUERIDO)
  - Tipo: date
  - Default: Hoy
  - Evento: onChange dispara `actualizarPrecio()`

### Sección: Convenio / Descuento
- **`id_convenio`** - ¿Tiene Convenio? (opcional)
  - Tipo: select
  - Opciones: Sin Convenio + Lista de convenios con %descuento
  - Evento: onChange dispara `actualizarPrecio()`

- **`id_motivo_descuento`** - Motivo del Descuento (opcional)
  - Tipo: select
  - Opciones: Sin Motivo + Motivos activos de BD

- **`descuento_manual`** - Descuento Manual ($) (opcional)
  - Tipo: number
  - Min: 0
  - Step: 1 (números enteros)
  - Default: 0
  - Evento: onChange/oninput dispara `actualizarPrecioFinal()`

- **`observaciones_inscripcion`** - Observaciones (opcional)
  - Tipo: text
  - Placeholder: Notas sobre la inscripción

### Display Automático (Lectura):
- **💰 Resumen de Precios** (aparece después de seleccionar membresía):
  - Precio Base: $XXX.XXX
  - Convenio: $XXX.XXX
  - Descuento Manual: -$XXX.XXX
  - **PRECIO FINAL: $XXX.XXX**
  - Fecha de Término: DD-MM-YYYY (calculada automáticamente)

### Botones PASO 2:
- ⬅️ **Anterior** - Vuelve a PASO 1
- ➡️ **Siguiente** - Avanza a PASO 3
- ✅ **Guardar con Membresía** - Crea cliente + inscripción (sin pago)

---

## 🔹 PASO 3: PAGO (5 campos + Resumen)

### Display: Resumen de Inscripción (Lectura)
Muestra información consolidada de PASO 1 y PASO 2:
- **Cliente:** Nombre del cliente (se actualiza en tiempo real)
- **Membresía:** Nombre de la membresía seleccionada
- **Convenio:** Sí/No + nombre del convenio
- **Descuento Motivo:** Motivo seleccionado (o "-")
- **Descuento Manual:** -$XXX.XXX
- **PRECIO FINAL A PAGAR:** $XXX.XXX (destacado)

### Sección: Información de Pago

#### 1. **`tipo_pago`** ⭐ - Tipo de Pago (REQUERIDO)
   - Tipo: select
   - Opciones:
     - **Pago Completo** (Todo de una)
       - Monto se bloquea automáticamente = Precio Final
       - No se puede editar
       - Mensaje: "Se pagará el monto total de una sola vez"
     
     - **Pago Parcial / Abono**
       - Permite ingresar monto
       - Validación: Número > 0
       - Mensaje: "El cliente puede abonar una parte. El saldo restante quedará pendiente"
     
     - **Pago Pendiente** (Sin pagar)
       - No muestra campos de monto
       - Se crea inscripción sin registrar pago
       - Mensaje: "No se registrará pago. La inscripción se crea sin abonar"
     
     - **Pago Mixto** (Combinado)
       - Permite ingresar monto de la 1ª parte
       - Se pueden usar múltiples métodos o cuotas
       - Mensaje: "Se pueden combinar múltiples pagos o métodos"

#### 2. **`fecha_pago`** ⭐ - Fecha de Pago (REQUERIDO)
   - Tipo: date
   - Default: Hoy
   - **MUY IMPORTANTE:** Se registra siempre la fecha del pago

#### 3. **`monto_abonado`** (CONDICIONAL, según tipo_pago)
   - Tipo: number
   - Step: 1 (números enteros)
   - Min: 0
   - Visible: Si tipo_pago = "completo" | "parcial" | "mixto"
   - NO visible: Si tipo_pago = "pendiente" o no seleccionado
   - **Si Pago Completo:**
     - Readonly: true
     - Value: Automático = Precio Final
     - Label: "Monto Total (Pago Completo)"
   
   - **Si Pago Parcial:**
     - Readonly: false
     - Value: Editable por usuario
     - Label: "Monto a Abonar"
     - Hint: "Ingrese el monto que desea abonar. Quedarán pendientes: $XXX.XXX"
   
   - **Si Pago Mixto:**
     - Readonly: false
     - Value: Editable por usuario
     - Label: "Monto Abonado (Parte 1)"
     - Hint: "Ingrese el monto de la primera parte. Puede usar múltiples métodos"

#### 4. **`id_metodo_pago`** ⭐ (CONDICIONAL, según tipo_pago)
   - Tipo: select
   - Visible: Si tipo_pago = "completo" | "parcial" | "mixto"
   - NO visible: Si tipo_pago = "pendiente" o no seleccionado
   - Opciones: Se cargan desde MetodoPago (Efectivo, Tarjeta, Transferencia, etc.)

### Display: Información Adicional (Dinámica)
- **Alerta de Tipo de Pago:** Cambia según tipo_pago seleccionado
  - Color y icono según tipo
  - Resumen del tipo de pago seleccionado
  - Monto total a cubrir

### Botones PASO 3:
- ⬅️ **Anterior** - Vuelve a PASO 2
- ✅ **Guardar Todo** - Crea cliente + inscripción + pago (o pendiente de pago)

---

## 📊 RESUMEN GRÁFICO DEL FLUJO

```
┌─────────────────────────────────────────────────────────────┐
│              PASO 1: DATOS CLIENTE (11 campos)              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ 📋 Identificación:                                           │
│  └─ RUT/Pasaporte (opcional)                               │
│                                                              │
│ 👤 Datos Personales:                                        │
│  ├─ Nombres ⭐                                               │
│  ├─ Apellido Paterno ⭐                                      │
│  ├─ Apellido Materno (opcional)                            │
│  └─ Fecha Nacimiento (opcional)                            │
│                                                              │
│ 📞 Contacto:                                                │
│  ├─ Email ⭐                                                 │
│  └─ Celular ⭐                                               │
│                                                              │
│ 🚨 Emergencia:                                              │
│  ├─ Contacto (opcional)                                    │
│  └─ Teléfono (opcional)                                    │
│                                                              │
│ 🏠 Domicilio:                                               │
│  └─ Dirección (opcional)                                   │
│                                                              │
│ 📝 Observaciones:                                           │
│  └─ Notas Adicionales (opcional)                           │
│                                                              │
└─────────────────────────────────────────────────────────────┘
           ✅ Guardar Cliente  |  ➡️ Siguiente

┌─────────────────────────────────────────────────────────────┐
│         PASO 2: MEMBRESÍA (7 campos + Display)             │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ 📌 Cliente: Juan García (se actualiza en tiempo real)      │
│                                                              │
│ 💪 Membresía:                                               │
│  ├─ Membresía ⭐                                             │
│  └─ Fecha Inicio ⭐                                          │
│                                                              │
│ 🤝 Convenio/Descuento:                                     │
│  ├─ Convenio (opcional)                                    │
│  ├─ Motivo Descuento (opcional)                           │
│  ├─ Descuento Manual ($) (opcional)                        │
│  └─ Observaciones (opcional)                              │
│                                                              │
│ 💰 RESUMEN PRECIOS:                                         │
│  ├─ Precio Base: $299.000                                  │
│  ├─ Convenio: $299.000                                     │
│  ├─ Descuento: -$50.000                                    │
│  ├─ ━━━━━━━━━━━━━━━━━━━━━━━━                               │
│  ├─ 📊 Precio Final: $249.000                              │
│  └─ 📅 Fecha Término: 27-12-2025                           │
│                                                              │
└─────────────────────────────────────────────────────────────┘
  ⬅️ Anterior  |  ➡️ Siguiente  |  ✅ Guardar con Membresía

┌─────────────────────────────────────────────────────────────┐
│          PASO 3: PAGO (5 campos + Resumen)                 │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ 📋 RESUMEN INSCRIPCIÓN:                                    │
│  ├─ Cliente: Juan García                                  │
│  ├─ Membresía: Básica                                      │
│  ├─ Convenio: Sí (FONASA)                                  │
│  ├─ Descuento Motivo: Promoción Black Friday              │
│  ├─ Descuento Manual: -$50.000                            │
│  └─ 💰 PRECIO FINAL: $249.000                              │
│                                                              │
│ 💳 INFORMACIÓN DE PAGO:                                    │
│  ├─ Tipo de Pago ⭐                                         │
│  │  ├─ Pago Completo (Todo de una)                         │
│  │  ├─ Pago Parcial / Abono                                │
│  │  ├─ Pago Pendiente (Sin pagar)                          │
│  │  └─ Pago Mixto (Combinado)                              │
│  │                                                          │
│  ├─ Fecha de Pago ⭐ (28-11-2025)                          │
│  │                                                          │
│  ├─ Monto Abonado (si aplica)                              │
│  │  └─ Editable según tipo de pago                        │
│  │                                                          │
│  └─ Método de Pago ⭐ (si aplica)                           │
│     └─ Efectivo, Tarjeta, Transferencia, etc.             │
│                                                              │
│ ⚠️ INFORMACIÓN DINÁMICA:                                   │
│  └─ Alerta según tipo de pago seleccionado                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
              ⬅️ Anterior  |  ✅ Guardar Todo
```

---

## 🔐 CAMPOS OBLIGATORIOS (⭐)

### PASO 1 (4 campos):
- `nombres`
- `apellido_paterno`
- `email`
- `celular`

### PASO 2 (2 campos):
- `id_membresia`
- `fecha_inicio`

### PASO 3 (4 campos):
- `tipo_pago`
- `fecha_pago`
- `monto_abonado` (condicional: si tipo_pago ≠ pendiente)
- `id_metodo_pago` (condicional: si tipo_pago ≠ pendiente)

**Total de campos obligatorios: 10**

---

## 📱 VALIDACIONES Y COMPORTAMIENTO

### PASO 1:
- Email: Validación de formato
- Celular: Validación de teléfono
- Nombres + Apellido: Obligatorios

### PASO 2:
- Al seleccionar Membresía: Se cargan precios automáticamente
- Al cambiar Convenio: Se actualiza precio final
- Al cambiar Descuento Manual: Se actualiza precio final en tiempo real
- Fecha Término: Se calcula automáticamente (fecha_inicio + duracion_dias)

### PASO 3:
- Resumen se actualiza en tiempo real al cambiar datos de PASO 1 y PASO 2
- Si tipo_pago = "completo": monto_abonado se bloquea automáticamente
- Si tipo_pago = "pendiente": no se muestra monto ni método de pago
- Fecha Pago: Siempre visible y requerida

---

## 🎯 TIPOS DE PAGO DETALLADOS

### 1️⃣ PAGO COMPLETO
- **Monto:** Bloqueado = Precio Final
- **Método:** Requerido
- **Resultado:** Se crea cliente + inscripción + pago registrado como "completo"

### 2️⃣ PAGO PARCIAL / ABONO
- **Monto:** Editable, debe ser > 0
- **Método:** Requerido
- **Resultado:** Se crea cliente + inscripción + pago registrado como "parcial" (saldo pendiente)

### 3️⃣ PAGO PENDIENTE
- **Monto:** No se muestra
- **Método:** No se muestra
- **Resultado:** Se crea cliente + inscripción, SIN registrar pago

### 4️⃣ PAGO MIXTO
- **Monto:** Editable (primera parte)
- **Método:** Requerido (primera parte)
- **Resultado:** Se crea cliente + inscripción + pago parcial (puede continuarse después)

---

## 💾 CAMPOS ALMACENADOS EN BD

### Tabla: clientes
- run_pasaporte
- nombres
- apellido_paterno
- apellido_materno
- fecha_nacimiento
- email
- celular
- contacto_emergencia
- telefono_emergencia
- direccion
- observaciones

### Tabla: inscripciones
- id_cliente (FK)
- id_membresia (FK)
- id_convenio (FK)
- id_motivo_descuento (FK)
- fecha_inicio
- fecha_vencimiento (calculada)
- precio_base
- descuento_aplicado
- precio_final
- observaciones_inscripcion
- tipo_pago

### Tabla: pagos
- id_cliente (FK)
- id_inscripcion (FK)
- monto_abonado
- id_metodo_pago (FK)
- fecha_pago
- tipo_pago (completo | parcial | pendiente | mixto)
- estado (completado | pendiente | parcial)

---

## ✅ ESTADO FINAL

**Versión:** 2.0 - Completa y lista para usar
**Última revisión:** 28 de noviembre de 2025
**Rama:** feature/mejora-flujo-clientes
**Commit:** 1389ce8
