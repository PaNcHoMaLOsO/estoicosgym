# 🔀 FLUJO DE CLIENTE - 3 CASOS DE USO

**Implementado:** 28 de noviembre de 2025  
**Estado:** Listo para producción  
**Rama:** `feature/mejora-flujo-clientes`

---

## 📊 Resumen de los 3 Casos

El flujo de crear cliente ahora permite **3 opciones distintas** según la necesidad del dueño:

```
┌─────────────────────────────────────────────────────────┐
│            CREAR NUEVO CLIENTE - 3 OPCIONES              │
└─────────────────────────────────────────────────────────┘
                        ▼
        ┌───────────────────────────────┐
        │   PASO 1: DATOS DEL CLIENTE   │
        │   (SIEMPRE REQUERIDO)         │
        └───────────────────────────────┘
                 ▼           ▼
        ┌──────────┐  ┌──────────────┐
        │ CASO 1   │  │ CASO 2 Y 3   │
        │          │  │              │
        │ Guardar  │  │ Continuar    │
        │ CLIENTE  │  │              │
        └──────────┘  └──────────────┘
             ▼              ▼
        ┌──────────┐  ┌──────────────────────┐
        │ LISTO:   │  │ PASO 2: MEMBRESÍA    │
        │          │  │                      │
        │ CLIENTE  │  │ ┌──────────────────┐ │
        │ REGISTRADO  │ │ CASO 2:           │ │
        │ (sin)    │  │ Guardar CLIENTE +  │ │
        │ membresia│  │ MEMBRESÍA          │ │
        │ (sin pago)  │ (sin pago)        │ │
        │          │  │ └──────────────────┘ │
        └──────────┘  │        ▼              │
                      │ CASO 3: Continuar    │
                      └──────────────────────┘
                            ▼
                      ┌──────────────────────┐
                      │ PASO 3: PAGO         │
                      │                      │
                      │ ┌──────────────────┐ │
                      │ │ CASO 3:           │ │
                      │ │ Guardar CLIENTE + │ │
                      │ │ MEMBRESÍA + PAGO  │ │
                      │ │                   │ │
                      │ │ (Completo)        │ │
                      │ └──────────────────┘ │
                      └──────────────────────┘
                            ▼
                      ┌──────────────────────┐
                      │ LISTO:               │
                      │                      │
                      │ CLIENTE REGISTRADO   │
                      │ MEMBRESÍA ACTIVA     │
                      │ PAGO REGISTRADO      │
                      │                      │
                      │ (Completo)           │
                      └──────────────────────┘
```

---

## 🎯 CASO 1: SOLO CLIENTE

**Botón:** `Guardar Cliente` (en PASO 1)  
**Qué ocurre:**
- ✅ Cliente creado
- ❌ Sin membresía
- ❌ Sin pago
- **Estado:** `REGISTRADO` (sin inscripción)

**Cuándo usar:**
- Registrar cliente para luego decidir membresía
- Cliente interesado, aún sin decidir
- Primero datos, después servicios

**Base de datos:**
```
Tabla: clientes
├─ id: auto
├─ nombre, email, etc.
├─ activo: true
└─ Sin registros en inscripciones ni pagos
```

**Flujo después:**
- Cliente puede ir a módulo INSCRIPCIONES
- Seleccionar este cliente
- Asignar membresía
- Registrar pago

---

## 💼 CASO 2: CLIENTE + MEMBRESÍA (SIN PAGO)

**Botones:**
- `Siguiente` (en PASO 1) → Ir a PASO 2
- `Guardar con Membresía` (en PASO 2)

**Qué ocurre:**
- ✅ Cliente creado
- ✅ Membresía asignada
- ✅ Inscripción creada
- ❌ Sin pago registrado
- **Estado:** `INSCRITO` (pago pendiente)

**Cuándo usar:**
- Cliente confirma membresía pero paga después
- Separar registro de inscripción del pago
- Tener control administrativo del flujo

**Base de datos:**
```
Tabla: clientes
├─ Cliente creado
├─ activo: true

Tabla: inscripciones
├─ id_cliente: referencia
├─ id_membresia: membresía seleccionada
├─ fecha_inicio, fecha_vencimiento
├─ precio_base, precio_final
├─ id_estado: 100 (ACTIVA)
└─ Sin registros en pagos

Tabla: pagos
└─ (Vacía)
```

**Flujo después:**
- Cliente ve su membresía activa
- Dueño puede ver en panel "Inscritos sin pago"
- Ir a módulo PAGOS
- Seleccionar inscripción
- Registrar pago

---

## 💳 CASO 3: CLIENTE + MEMBRESÍA + PAGO (COMPLETO)

**Botones:**
- `Siguiente` (en PASO 1) → Ir a PASO 2
- `Siguiente` (en PASO 2) → Ir a PASO 3
- `Guardar Todo` (en PASO 3)

**Qué ocurre:**
- ✅ Cliente creado
- ✅ Membresía asignada
- ✅ Inscripción creada
- ✅ Pago registrado
- **Estado:** `PAGADO COMPLETAMENTE` o `ABONO REGISTRADO`

**Cuándo usar:**
- Proceso completo en una sola operación
- Cliente viene, paga, listo
- Flujo rápido y directo

**Base de datos:**
```
Tabla: clientes
├─ Cliente creado
├─ activo: true

Tabla: inscripciones
├─ id_cliente: referencia
├─ id_membresia: membresía seleccionada
├─ fecha_inicio, fecha_vencimiento
├─ precio_base, precio_final
├─ id_estado: 100 (ACTIVA)

Tabla: pagos
├─ id_inscripcion: referencia
├─ monto_total: precio final
├─ monto_abonado: lo que pagó
├─ monto_pendiente: lo que falta
├─ id_estado: 201 (PAGADO) o 200 (PENDIENTE)
│           ↓
│   Si abona completo → PAGADO (201)
│   Si abona parcial  → PENDIENTE (200)
└─ fecha_pago, id_metodo_pago, etc.
```

**Flujo después:**
- Si es pago completo (201): Todo listo
- Si es abono (200): Pago pendiente visible en panel

---

## 🔄 Comparativa de Estados

| Aspecto | CASO 1 | CASO 2 | CASO 3 |
|---------|--------|--------|--------|
| **Cliente** | ✅ Creado | ✅ Creado | ✅ Creado |
| **Membresía** | ❌ No | ✅ Activa | ✅ Activa |
| **Inscripción** | ❌ No | ✅ Creada | ✅ Creada |
| **Pago** | ❌ No | ❌ No | ✅ Registrado |
| **Estado BD** | REGISTRADO | INSCRITO | PAGADO o PENDIENTE |
| **Visible en** | Clientes | Inscritos | Pagos |
| **Siguiente paso** | Inscripciones | Pagos | Nada (listo) |

---

## 🎨 Interfaz de Usuario

### PASO 1 (DATOS DEL CLIENTE)
```
┌─────────────────────────────────────────┐
│ Botones:                                │
│                                         │
│ [Cancelar] [Guardar Cliente] [Siguiente]│
│                                         │
│ - "Guardar Cliente": Solo CASO 1        │
│ - "Siguiente": Ir a PASO 2              │
└─────────────────────────────────────────┘
```

### PASO 2 (MEMBRESÍA)
```
┌─────────────────────────────────────────┐
│ Botones:                                │
│                                         │
│ [Cancelar] [Anterior] [Guardar con     │
│            Membresía] [Siguiente]       │
│                                         │
│ - "Guardar con Membresía": CASO 2       │
│ - "Siguiente": Ir a PASO 3              │
└─────────────────────────────────────────┘
```

### PASO 3 (PAGO)
```
┌─────────────────────────────────────────┐
│ Botones:                                │
│                                         │
│ [Cancelar] [Anterior] [Guardar Todo]    │
│                                         │
│ - "Guardar Todo": CASO 3 (Completo)     │
└─────────────────────────────────────────┘
```

---

## 💾 Implementación en Código

### Vista (create.blade.php)
```php
<!-- Cada PASO muestra botones diferentes -->
<!-- PASO 1: btnGuardarSoloCliente, btnSiguiente -->
<!-- PASO 2: btnGuardarConMembresia, btnAnterior, btnSiguiente -->
<!-- PASO 3: btnGuardarCompleto, btnAnterior -->

<!-- Campo oculto indica qué tipo es -->
<input type="hidden" id="flujo_cliente" name="flujo_cliente" value="completo">
```

### Controlador (ClienteController.php)
```php
// Leer el valor del flujo
$flujoCliente = $request->input('flujo_cliente', 'completo');

// Crear cliente siempre
$cliente = Cliente::create([...]);

// CASO 1: Retornar
if ($flujoCliente === 'solo_cliente') {
    return redirect()->route('admin.clientes.show', $cliente)
        ->with('success', 'Cliente registrado...');
}

// Validar y crear membresía
$membresia = ...

// CASO 2: Retornar
if ($flujoCliente === 'con_membresia') {
    return redirect()->route('admin.clientes.show', $cliente)
        ->with('success', 'Cliente + Membresía...');
}

// CASO 3: Crear pago
Pago::create([...]);

return redirect()->route('admin.clientes.show', $cliente)
    ->with('success', 'Cliente + Membresía + Pago...');
```

---

## ✨ Ventajas de Este Diseño

1. **Flexibilidad Total**
   - ✅ Dueño elige cuándo y cómo procesar
   - ✅ No obliga a hacer todo de una vez
   - ✅ Mejor control administrativo

2. **Reutilizable en Otros Módulos**
   - ✅ Inscripciones: Cliente ya existe, solo asignar membresía + pago
   - ✅ Pagos: Cliente + Inscripción existen, solo registrar pago
   - ✅ Flujos más cortos y simples

3. **Mejor Experiencia**
   - ✅ Usuario elige su ritmo
   - ✅ No presión de hacer todo inmediato
   - ✅ Opción de salir en cualquier momento

4. **Datos Consistentes**
   - ✅ Cada paso crea estado válido en BD
   - ✅ No hay datos "huérfanos" o incompletos
   - ✅ Trazabilidad clara

---

## 🧪 Casos de Prueba

### TEST 1: Solo Cliente
```
1. Ir a Crear Cliente
2. Completar PASO 1 (datos)
3. Click en "Guardar Cliente"
4. Verificar:
   ✓ Cliente existe en BD
   ✓ Sin inscripción
   ✓ Sin pago
   ✓ Mensaje: "Cliente registrado"
   ✓ Puede editar datos
```

### TEST 2: Cliente + Membresía
```
1. Ir a Crear Cliente
2. Completar PASO 1
3. Click "Siguiente"
4. Ir a PASO 2, completar membresía
5. Click "Guardar con Membresía"
6. Verificar:
   ✓ Cliente creado
   ✓ Inscripción creada
   ✓ Sin pago
   ✓ Mensaje: "Cliente + Membresía"
   ✓ Panel muestra "Inscritos sin pago"
```

### TEST 3: Completo
```
1. Crear Cliente PASO 1
2. Ir PASO 2, completar membresía
3. Ir PASO 3, completar pago
4. Click "Guardar Todo"
5. Verificar:
   ✓ Todo creado
   ✓ Si pago completo: Estado "PAGADO"
   ✓ Si abono: Estado "PENDIENTE"
   ✓ Mensaje apropiado
```

---

## 📋 Checklist para Producción

- ✅ Vista actualizada con 3 botones
- ✅ JavaScript maneja flujo correcto
- ✅ Controlador procesa 3 casos
- ✅ Validaciones apropiadas por caso
- ✅ Mensajes claros para usuario
- ✅ Estados guardados correctamente en BD
- ✅ Panel admin muestra datos correctos
- ✅ Flujos posteriores funcionan (inscripciones, pagos)

---

## 🚀 Siguientes Pasos

Con el cliente listo, ahora se puede:

1. **Módulo INSCRIPCIONES**
   - Cliente existe → Solo buscar + membresía + pago
   - Flujo más corto

2. **Módulo PAGOS**
   - Cliente + Inscripción existen → Solo pago
   - Flujo super simplificado

3. **PANEL ADMIN**
   - "Inscritos sin pago" ← Clientes en CASO 2
   - "Pagos pendientes" ← Clientes con abono (no completo)
   - "Membresías por vencer" ← Control automático

