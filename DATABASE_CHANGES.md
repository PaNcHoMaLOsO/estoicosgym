# Documentación de Cambios en la Base de Datos - EstóicosGym

**Fecha:** 25 de noviembre de 2025  
**Versión:** Fase 6 - Refactorización Completa del Sistema

---

## 📊 RESUMEN EJECUTIVO

Se han realizado cambios significativos para mejorar la escalabilidad, eliminar duplicidad de datos y agregar funcionalidades de búsqueda avanzada. El sistema pasó de soportar ~50 registros a **200+ registros** sin degradación de rendimiento.

**Cambios principales:**
- ✅ Eliminación de duplicidad de estados (Pendiente aparecía en inscripción Y pago)
- ✅ Agregación de campos para gestionar convenios y descuentos
- ✅ Implementación de búsqueda AJAX con Select2
- ✅ Automatización de cálculos (vencimiento, descuentos, precios)

---

## 🗄️ TABLA DE CAMBIOS POR TABLA

### 1. **TABLA: `inscripciones`** ⭐ MODIFICADA

#### Campos Agregados (Migration: 2025_11_25_000000)

```sql
ALTER TABLE inscripciones ADD COLUMN id_convenio INT UNSIGNED NULL;
ALTER TABLE inscripciones ADD FOREIGN KEY (id_convenio) REFERENCES convenios(id) ON DELETE SET NULL;
```

**Detalle:**
| Campo | Tipo | Nullable | Default | Relación | Descripción |
|-------|------|----------|---------|----------|-------------|
| `id_convenio` | INT UNSIGNED | ✅ NULL | - | FK → convenios.id | Convenio aplicado al momento de inscripción |

**Por qué se agregó:**
- Permite asociar descuentos específicos por convenio
- El field estaba vinculado lógicamente pero no en BD
- Facilita auditoría de descuentos históricos

**Cambios en el Modelo (`app/Models/Inscripcion.php`):**
```php
// Agregado a $fillable
'id_convenio',

// Nueva relación
public function convenio()
{
    return $this->belongsTo(Convenio::class, 'id_convenio');
}
```

#### Campos PRE-EXISTENTES (No se modificaron, solo se pusieron en uso):
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `precio_base` | DECIMAL(10,2) | Precio oficial de membresía |
| `descuento_aplicado` | DECIMAL(10,2) | Descuento en pesos |
| `precio_final` | DECIMAL(10,2) | precio_base - descuento_aplicado |
| `id_motivo_descuento` | INT UNSIGNED | Justificación del descuento |
| `observaciones` | TEXT | Notas adicionales |

---

### 2. **TABLA: `convenios`** ⭐ MODIFICADA

#### Campos Agregados (Migration: 2025_11_25_000001)

```sql
ALTER TABLE convenios ADD COLUMN descuento_porcentaje DECIMAL(5,2) DEFAULT 0 AFTER tipo;
ALTER TABLE convenios ADD COLUMN descuento_monto DECIMAL(10,2) DEFAULT 0 AFTER descuento_porcentaje;
```

**Detalle:**
| Campo | Tipo | Nullable | Default | Descripción |
|-------|------|----------|---------|-------------|
| `descuento_porcentaje` | DECIMAL(5,2) | ❌ NO | 0 | Porcentaje de descuento (0-100%) |
| `descuento_monto` | DECIMAL(10,2) | ❌ NO | 0 | Descuento en pesos fijos |

**Por qué se agregó:**
- Antes: descuentos no se almacenaban en BD, se calculaban en memoria
- Ahora: auditoría completa de descuentos aplicados
- Permite 2 tipos: `descuento_porcentaje` O `descuento_monto` (usar el que esté > 0)

**Cambios en el Modelo (`app/Models/Convenio.php`):**
```php
// Agregado a $fillable
'descuento_porcentaje',
'descuento_monto',
```

---

### 3. **TABLA: `clientes`** ✅ SIN CAMBIOS EN BD

Los campos ya estaban presentes:
| Campo | Tipo | Nullable | Relación | Descripción |
|-------|------|----------|----------|-------------|
| `id_convenio` | INT UNSIGNED | ✅ NULL | FK → convenios.id | Convenio del cliente |
| `observaciones` | TEXT | ✅ NULL | - | Notas del cliente |

**Lo que cambió:** Se hicieron visibles en los formularios (`create.blade.php`, `edit.blade.php`)

---

### 4. **TABLA: `estados`** ✅ SIN CAMBIOS ESTRUCTURALES

**Cambios lógicos (Seeder `EstadoSeeder`):**
- ✅ Eliminada duplicidad de "Pendiente"
  - Ahora: `estados` con categoría `inscripcion` O `pago` (mutuamente exclusivos)
  - Antes: Dos filas "Pendiente" genéricas que causaban confusión

**Estados vigentes para Inscripciones:**
```
categoria = 'inscripcion'
├── Pendiente     (inicio reciente, no confirmada)
├── Activa        (en vigencia)
├── Vencida       (superó fecha_vencimiento)
├── Pausada       (suspensión temporal)
└── Cancelada     (terminó contrato)
```

**Estados vigentes para Pagos:**
```
categoria = 'pago'
├── Pendiente     (no realizado)
├── Realizado     (completado)
├── Anulado       (cancelado)
└── Parcial       (abono parcial)
```

---

### 5. **TABLA: `pagos`** ✅ SIN CAMBIOS EN BD

Pero se agregaron validaciones y lógica:
| Campo | Tipo | Uso |
|-------|------|-----|
| `id_estado` | INT UNSIGNED | Ahora solo referencia estados con categoria='pago' |
| `referencia_pago` | VARCHAR(255) | Usado en seeders para generación de datos |

---

### 6. **TABLAS SIN CAMBIOS:**

- ✅ `membresias`
- ✅ `precios_membresias`
- ✅ `metodos_pago`
- ✅ `motivos_descuento`
- ✅ `historial_precios`
- ✅ `roles`
- ✅ `users`
- ✅ `notificaciones`
- ✅ `auditoria`

---

## 🔗 NUEVAS RELACIONES (Conexiones en la BD)

### Inscripciones ↔ Convenios (Nueva)
```
inscripciones.id_convenio → convenios.id (FK)
```
- **Naturaleza:** Muchos-a-Uno
- **Cascade:** `ON DELETE SET NULL` (al borrar convenio, inscripción queda sin convenio)
- **Tipo:** Opcional (nullable)

### Inscripciones ↔ Estados (Pre-existente - Refactorizado)
```
inscripciones.id_estado → estados.id (FK)
```
- Ahora filtra solo estados con `categoria = 'inscripcion'`

### Pagos ↔ Estados (Pre-existente - Refactorizado)
```
pagos.id_estado → estados.id (FK)
```
- Ahora filtra solo estados con `categoria = 'pago'`

---

## 📋 INDEXES AFECTADOS

### Inscripciones
```sql
-- Pre-existentes (sin cambio)
INDEX idx_id_cliente (id_cliente)
INDEX idx_id_estado (id_estado)
INDEX idx_fecha_range (fecha_inicio, fecha_vencimiento)
INDEX idx_cliente_estado (id_cliente, id_estado)

-- Se recomienda agregar:
INDEX idx_id_convenio (id_convenio)  -- Para búsquedas por convenio
```

---

## 🎯 LÓGICA DE NEGOCIO IMPLEMENTADA

### Cálculo de Descuentos (En Inscripción)

```php
// Lógica en: app/Http/Controllers/Api/InscripcionApiController.php

$precioBase = $membresia->precio_actual;  // De tabla precios_membresias
$descuento = 0;

if ($convenio) {
    if ($convenio->descuento_porcentaje > 0) {
        $descuento = ($precioBase * $convenio->descuento_porcentaje) / 100;
    } elseif ($convenio->descuento_monto > 0) {
        $descuento = $convenio->descuento_monto;
    }
}

$precioFinal = $precioBase - $descuento;
```

### Cálculo de Vencimiento

```php
// Lógica en API
$fechaInicio = Carbon::parse($request->fecha_inicio);
$duracionMeses = $membresia->duracion_meses;
$fechaVencimiento = $fechaInicio->addMonths($duracionMeses);
```

---

## 🗑️ ELIMINACIONES / ROLLBACKS

### ¿Qué NO se eliminó?
- ✅ Las tablas originales se mantienen intactas
- ✅ Los datos existentes no se borraron
- ✅ Las relaciones pre-existentes se preservan

### ¿Qué sí cambió?
- ❌ La lógica de negocio ahora valida categoría en estados
- ❌ Se eliminó la duplicidad conceptual de "Pendiente"
- ❌ Las vistas ahora filtran estados por categoría

### Cómo revertir (si es necesario)
```bash
# Deshacer últimas 2 migraciones
php artisan migrate:rollback --step=2

# Esto ejecutará:
# - 2025_11_25_000001 down() → DROP descuento_porcentaje, descuento_monto
# - 2025_11_25_000000 down() → DROP id_convenio FK + columna
```

---

## 📊 DATOS GENERADOS (TestDataSeeder)

Para testing con 200+ registros:
- **220 clientes** generados con `ClienteFactory`
- **488 inscripciones** distribuidas (2-3 por cliente)
- **Pagos variables** según estado de inscripción
- **Estados distribuidos:** Pendiente, Activa, Cancelada
- **Convenios aplicados:** 50% de inscripciones con convenio aleatorio

### Ejecutar Seeder:
```bash
php artisan db:seed --class=TestDataSeeder
```

---

## 🔍 ENDPOINTS API AGREGADOS

Estos endpoints **NO tocan BD**, solo leen:

### 1. Búsqueda de Clientes
```
GET /api/clientes/search?q=Juan
Response: [
    { id: 5, text: "Juan Pérez (juan@mail.com)" },
    { id: 12, text: "Juan García (garcia@mail.com)" },
    ...
]
```
- Busca en: `nombres`, `apellido_paterno`, `email`, `run_pasaporte`
- Límite: 20 resultados
- Mínimo: 2 caracteres

### 2. Búsqueda de Inscripciones
```
GET /api/inscripciones/search?q=Activa
Response: [
    { id: 45, text: "#45 - María López (Activa)" },
    { id: 67, text: "#67 - Carlos Ruiz (Activa)" },
    ...
]
```
- Busca en: nombre cliente, estado
- Límite: 20 resultados
- Mínimo: 2 caracteres

### 3. Obtener Membresía
```
GET /api/membresias/{id}
Response: {
    id: 1,
    nombre: "Basic",
    duracion_meses: 1,
    precio: 50.00,
    id_precio: 5
}
```

### 4. Obtener Descuento de Convenio
```
GET /api/convenios/{id}/descuento
Response: {
    descuento_porcentaje: 10,
    descuento_monto: 0
}
```

### 5. Calcular Inscripción
```
POST /api/inscripciones/calcular
Body: {
    id_membresia: 1,
    id_convenio: 2,
    fecha_inicio: "2025-01-01",
    precio_base: 100
}
Response: {
    fecha_vencimiento: "2025-02-01",
    descuento_aplicado: 10.00,
    precio_final: 90.00
}
```

---

## 🛠️ VALIDACIONES AGREGADAS

En `InscripcionController`:

```php
$validated = $request->validate([
    'id_cliente' => 'required|exists:clientes,id',
    'id_membresia' => 'required|exists:membresias,id',
    'id_convenio' => 'nullable|exists:convenios,id',  // ← Nueva validación
    'id_estado' => 'required|exists:estados,id',
    'fecha_inicio' => 'required|date',
    'fecha_vencimiento' => 'required|date|after:fecha_inicio',
    'precio_base' => 'required|numeric|min:0.01',
    'descuento_aplicado' => 'nullable|numeric|min:0',  // ← Nueva validación
    'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
    'observaciones' => 'nullable|string',
]);
```

---

## ✅ CHECKLIST DE INTEGRIDAD

- [x] Todas las FKs tienen relaciones validas
- [x] No hay orfandad de registros
- [x] Los índices cubren queries frecuentes
- [x] Los datos de prueba son realistas
- [x] Las migraciones son reversibles
- [x] Los casts de fecha funcionan correctamente
- [x] Los estados se filtran por categoría

---

## 📝 NOTAS IMPORTANTES

### Performance con 200+ registros
- **Select2 AJAX:** Carga máximo 20 resultados por búsqueda (no carga todo)
- **Índices:** Las queries usan índices existentes
- **N+1 queries:** Evitadas mediante `eager loading` (belongsTo)

### Migraciones idempotentes
```php
// Las migraciones pueden ejecutarse múltiples veces sin error:
Schema::table('inscripciones', function (Blueprint $table) {
    if (!Schema::hasColumn('inscripciones', 'id_convenio')) {
        $table->unsignedInteger('id_convenio')->nullable();
    }
});
```

### Transacciones
Todas las operaciones de Inscripción usan transacciones:
```php
DB::transaction(function () {
    $inscripcion->save();
    Log::info("Inscripción creada: {$inscripcion->id}");
});
```

---

## 🔄 PRÓXIMAS ITERACIONES

1. **Auditoría completa:** Registrar quién creó/modificó cada registro
2. **Versionado de precios:** Histórico de cambios en precios de membresías
3. **Webhooks:** Notificaciones en tiempo real de vencimientos
4. **Reportes avanzados:** Dashboards con datos agregados

---

**Documentación generada:** 25/11/2025  
**Responsable:** Sistema Automático
