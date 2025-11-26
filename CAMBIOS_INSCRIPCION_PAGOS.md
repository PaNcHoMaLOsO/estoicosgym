# Cambios en Sistema de Inscripciones y Pagos

**Fecha:** 26 de Noviembre de 2025  
**Estado:** Completado ✅

## Resumen de Cambios

Se han implementado mejoras significativas en el sistema de inscripciones y pagos para permitir:

1. **Fecha de Inicio Editable** - Ya no está bloqueada
2. **Filtrado de Clientes** - Solo se muestran clientes sin inscripciones activas
3. **Sistema de Cuotas** - Posibilidad de pagar en múltiples cuotas
4. **Pagos Parciales** - Registrar pagos incompletos con seguimiento de cuotas

---

## 1. Cambios en Base de Datos

### Migración: `add_cuotas_to_pagos_table`

Se agregaron 4 nuevos campos a la tabla `pagos`:

```sql
- cantidad_cuotas (INT, default: 1)
  Descripción: Total de cuotas en que se pagará la membresía
  
- numero_cuota (INT, default: 1)
  Descripción: Número de cuota actual (ej: 1 de 3)
  
- monto_cuota (DECIMAL 10,2, nullable)
  Descripción: Monto de cada cuota individual
  
- fecha_vencimiento_cuota (DATE, nullable)
  Descripción: Fecha de vencimiento para esta cuota específica
```

**Ejecutar:** `php artisan migrate`

---

## 2. Cambios en Modelos

### `app/Models/Pago.php`

**Cambios:**
- Agregados nuevos campos a `$fillable`
- Agregados casts para `monto_cuota` (decimal:2) y `fecha_vencimiento_cuota` (date)
- Actualizada documentación PHPDoc

**Nuevas propiedades:**
```php
protected $fillable = [
    // ... campos existentes
    'cantidad_cuotas',
    'numero_cuota',
    'monto_cuota',
    'fecha_vencimiento_cuota',
];

protected $casts = [
    // ... casts existentes
    'fecha_vencimiento_cuota' => 'date',
    'monto_cuota' => 'decimal:2',
];
```

### `app/Models/Inscripcion.php`

**Sin cambios directos** - Se mantiene el método `obtenerEstadoPago()` que ahora calcula correctamente los pagos parciales.

---

## 3. Cambios en Controllers

### `app/Http/Controllers/InscripcionController.php`

**Métodos modificados:**

#### `create()`
```php
// Nuevas importaciones
use App\Models\Estado;
use App\Models\Convenio;

// Filtrar clientes activos sin inscripciones activas
$clientesConInscripcion = Inscripcion::whereIn('id_estado', [201, 202, 203, 205, 206])
    ->pluck('id_cliente')
    ->unique();

$clientes = Cliente::where('activo', true)
    ->whereNotIn('id', $clientesConInscripcion)
    ->orderBy('nombres')
    ->get();

// Se agregan estados y convenios
$estados = Estado::whereIn('id', [201, 202, 203, 205, 206])->get();
$convenios = Convenio::where('activo', true)->get();
```

#### `store()`
```php
// Nuevas validaciones
$validated = $request->validate([
    'id_cliente' => 'required|integer|exists:clientes,id',
    'id_membresia' => 'required|integer|exists:membresias,id',
    'id_estado' => 'required|integer|exists:estados,id',
    'fecha_inicio' => 'required|date|after_or_equal:today', // ✅ AHORA EDITABLE
    'cantidad_cuotas' => 'required|integer|min:1|max:12', // ✅ NUEVO
    'id_motivo_descuento' => 'nullable|integer|exists:motivos_descuento,id',
    'descuento_aplicado' => 'nullable|numeric|min:0',
    'observaciones' => 'nullable|string',
]);

// Cálculo de monto de cuota
$montoCuota = $precioFinal / $validated['cantidad_cuotas'];
```

#### `show()`
```php
// Se carga estado de pago
$estadoPago = $inscripcion->obtenerEstadoPago();
return view('admin.inscripciones.show', compact('inscripcion', 'estadoPago'));
```

#### `update()`
```php
// Validaciones mejoradas con id_estado
$validated = $request->validate([
    'id_estado' => 'required|integer|exists:estados,id',
    'id_motivo_descuento' => 'nullable|integer|exists:motivos_descuento,id',
    'descuento_aplicado' => 'nullable|numeric|min:0',
    'observaciones' => 'nullable|string',
]);
```

### `app/Http/Controllers/Admin/PagoController.php`

**Métodos modificados:**

#### `create()`
```php
// Se puede precarga una inscripción específica
public function create(Request $request)
{
    $inscripcion = null;
    
    if ($request->filled('id_inscripcion')) {
        $inscripcion = Inscripcion::with('cliente', 'membresia')
            ->find($request->id_inscripcion);
    } else {
        $inscripcion = Inscripcion::with('cliente', 'membresia')->latest()->first();
    }
    
    $metodos_pago = MetodoPago::all();
    return view('admin.pagos.create', compact('inscripcion', 'metodos_pago'));
}
```

#### `store()`
```php
// Nuevas validaciones para cuotas
$validated = $request->validate([
    'id_inscripcion' => 'required|exists:inscripciones,id',
    'monto_abonado' => 'required|numeric|min:0.01',
    'fecha_pago' => 'required|date',
    'id_metodo_pago' => 'required|exists:metodo_pagos,id',
    'cantidad_cuotas' => 'required|integer|min:1|max:12', // ✅ NUEVO
    'numero_cuota' => 'required|integer|min:1', // ✅ NUEVO
    'fecha_vencimiento_cuota' => 'nullable|date', // ✅ NUEVO
    'observaciones' => 'nullable|string|max:500',
]);

// Guardado con soporte para cuotas
Pago::create([
    // ... campos base
    'cantidad_cuotas' => $validated['cantidad_cuotas'],
    'numero_cuota' => $validated['numero_cuota'],
    'monto_cuota' => $montoCuota,
    'fecha_vencimiento_cuota' => $validated['fecha_vencimiento_cuota'],
]);
```

#### `update()`
```php
// Similar a store() - soporte para cuotas
```

---

## 4. Cambios en Vistas

### `resources/views/admin/inscripciones/create.blade.php`

**Cambios principales:**

1. **Fecha Inicio**: Removido `readonly`, ahora es editable
   ```html
   <!-- Antes: readonly -->
   <!-- Después: editable -->
   <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
   ```

2. **Cantidad de Cuotas**: Nuevo campo
   ```html
   <input type="number" id="cantidad_cuotas" name="cantidad_cuotas" 
          min="1" max="12" value="1" required>
   ```

3. **Monto por Cuota**: Campo calculado automáticamente
   ```html
   <input type="number" id="monto_cuota" readonly>
   ```

4. **JavaScript mejorado**:
   - Calcula automáticamente el monto por cuota
   - Valida que número_cuota no exceda cantidad_cuotas
   - Sincroniza cambios en tiempo real

### `resources/views/admin/inscripciones/show.blade.php`

**Cambios principales:**

1. **Resumen de Pagos Mejorado**:
   - Total a Pagar
   - Total Abonado
   - Pendiente
   - Porcentaje Pagado (con barra de progreso)

2. **Tabla de Pagos Actualizada**:
   - Agregar columna "Cuota" mostrando `numero_cuota/cantidad_cuotas`
   - Agregar columna "Vencimiento Cuota"
   - Mejor formato visual

3. **Botón para Registrar Pago**:
   ```html
   <a href="{{ route('admin.pagos.create', ['id_inscripcion' => $inscripcion->id]) }}" 
      class="btn btn-success">
       <i class="fas fa-plus-circle"></i> Registrar Pago
   </a>
   ```

### `resources/views/admin/pagos/create.blade.php`

**Cambios principales:**

1. **Sección de Inscripción Mejorada**:
   - Si se precarga una inscripción, mostrarla destacada
   - Mostrar Cliente, Membresía, Vencimiento
   - Mostrar Precio Base, Descuento, Total

2. **Nuevos Campos de Cuotas**:
   - Cantidad Cuotas
   - Número Cuota (actual)
   - Monto por Cuota (calculado)
   - Fecha Vencimiento Cuota

3. **JavaScript Actualizado**:
   - Calcula automáticamente monto_cuota = monto_abonado / cantidad_cuotas
   - Valida número_cuota contra cantidad_cuotas
   - Select2 para mejora de UX

---

## 5. Flujo de Uso

### Crear Inscripción con Múltiples Cuotas

1. **Ir a:** Inscripciones → Crear Nueva
2. **Llenar:**
   - Cliente (solo sin inscripción activa)
   - Membresía
   - Fecha Inicio (EDITABLE)
   - Cantidad Cuotas: 3 (ej: pago en 3 cuotas)
   - Descuento (si aplica)
3. **Guardar** inscripción

### Registrar Pago Parcial

1. **Desde inscripción.show:** Click en "Registrar Pago"
2. **Llenar:**
   - Fecha Pago
   - Cantidad Cuotas: 3 (mismo que inscripción)
   - Número Cuota: 1 (primera cuota)
   - Monto Abonado: (calcula monto_cuota automáticamente)
   - Fecha Vencimiento Cuota: (fecha de vencimiento para esta cuota)
   - Método Pago
3. **Guardar** pago

### Registrar Siguiente Cuota

1. **Repetir proceso** desde inscripción.show
2. **Cambiar:**
   - Número Cuota: 2
   - Fecha Pago: (fecha actual)
   - Fecha Vencimiento Cuota: (nueva fecha)
3. **Guardar** - Sistema acumula pagos automáticamente

---

## 6. Cálculos Automáticos

### Monto de Cuota
```
monto_cuota = monto_abonado / cantidad_cuotas
```

### Estado de Pago (en obtenerEstadoPago())
```
porcentaje_pagado = (total_abonado / monto_total) * 100

Estado:
- "pagado" si pendiente <= 0
- "parcial" si total_abonado > 0 y pendiente > 0
- "pendiente" si total_abonado == 0
```

---

## 7. Validaciones

### En Inscripción:
- Fecha Inicio: `after_or_equal:today`
- Cliente: Debe no tener inscripción activa
- Cantidad Cuotas: Entre 1 y 12

### En Pago:
- Número Cuota: Entre 1 y cantidad_cuotas
- Monto Abonado: Mínimo 0.01
- Fecha Pago: Date válida
- Método Pago: Existe en base de datos

---

## 8. Estados Filtrarados

Las inscripciones se filtran considerando estados activos:
- 201: Activa
- 202: Pausada
- 203: Pausada (otra)
- 205: Pausada (otra)
- 206: Pausada (otra)

**Nota:** Se pueden ajustar estos IDs según la configuración específica del sistema.

---

## 9. Base de Datos - Ejemplo

```sql
-- Inscripción con 3 cuotas
INSERT INTO inscripciones (
    id_cliente, id_membresia, id_precio_acordado,
    fecha_inscripcion, fecha_inicio, fecha_vencimiento,
    precio_base, descuento_aplicado, id_estado
) VALUES (1, 1, 1, NOW(), '2025-11-26', '2025-12-26', 100.00, 0, 201);

-- Cuota 1
INSERT INTO pagos (
    id_inscripcion, id_cliente, monto_total, monto_abonado,
    fecha_pago, id_metodo_pago, id_estado,
    cantidad_cuotas, numero_cuota, monto_cuota, fecha_vencimiento_cuota
) VALUES (1, 1, 100.00, 33.33, '2025-11-26', 1, 102,
          3, 1, 33.33, '2025-11-26');

-- Cuota 2
INSERT INTO pagos (
    id_inscripcion, id_cliente, monto_total, monto_abonado,
    fecha_pago, id_metodo_pago, id_estado,
    cantidad_cuotas, numero_cuota, monto_cuota, fecha_vencimiento_cuota
) VALUES (1, 1, 100.00, 33.33, '2025-12-10', 1, 102,
          3, 2, 33.33, '2025-12-10');

-- Cuota 3
INSERT INTO pagos (
    id_inscripcion, id_cliente, monto_total, monto_abonado,
    fecha_pago, id_metodo_pago, id_estado,
    cantidad_cuotas, numero_cuota, monto_cuota, fecha_vencimiento_cuota
) VALUES (1, 1, 100.00, 33.34, '2025-12-24', 1, 102,
          3, 3, 33.34, '2025-12-24');
```

---

## 10. Commits Realizados

1. **`4ff00a0`** - feat: enhance inscripcion and pago models - editable start date, cuotas system, exclude active clients
2. **`7767613`** - feat: update payment and inscription views with cuotas tracking and better UI

---

## 11. Próximos Pasos (Opcional)

- [ ] Agregar API endpoint para inscripciones y pagos
- [ ] Crear reportes de pagos por cuota
- [ ] Implementar notificaciones de vencimiento de cuota
- [ ] Dashboard para monitoreo de cuotas pendientes
- [ ] Exportar estado de pagos a CSV/PDF

---

## 12. Testing

### Verificar:
1. ✅ Crear inscripción con fecha futura editable
2. ✅ Solo mostrarse clientes sin inscripción activa
3. ✅ Registrar pago parcial (cuota 1 de 3)
4. ✅ Registrar siguiente cuota (cuota 2 de 3)
5. ✅ Verificar estado de pago (% pagado)
6. ✅ Editar inscripción existente

---

**Documento generado:** 26 de Noviembre de 2025  
**Versión:** 1.0
