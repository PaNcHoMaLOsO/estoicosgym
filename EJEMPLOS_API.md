# Ejemplos de Uso - API de Modelos

## 📚 Índice
1. [Clientes](#clientes)
2. [Inscripciones](#inscripciones)
3. [Pagos](#pagos)
4. [Estadísticas](#estadísticas)
5. [Búsquedas Avanzadas](#búsquedas-avanzadas)

---

## Clientes

### Crear Cliente

```php
use App\Models\Cliente;

// Crear cliente simple
$cliente = Cliente::create([
    'nombres' => 'Juan',
    'apellido_paterno' => 'Pérez',
    'celular' => '+56912345678',
    'email' => 'juan@example.com',
]);

// Crear con convenio
$cliente = Cliente::create([
    'nombres' => 'María',
    'apellido_paterno' => 'González',
    'celular' => '+56987654321',
    'id_convenio' => 1, // INACAP
]);

// Con all fields
$cliente = Cliente::create([
    'run_pasaporte' => '12345678-9',
    'nombres' => 'Carlos',
    'apellido_paterno' => 'Rodríguez',
    'apellido_materno' => 'López',
    'celular' => '+56911111111',
    'email' => 'carlos@example.com',
    'direccion' => 'Calle Principal 123',
    'fecha_nacimiento' => '1990-01-15',
    'contacto_emergencia' => 'Ana González',
    'telefono_emergencia' => '+56922222222',
    'id_convenio' => 2,
    'observaciones' => 'Solicita horario en la noche',
]);
```

### Obtener Clientes

```php
// Todos los clientes
$clientes = Cliente::all();

// Clientes activos
$clientes = Cliente::where('activo', true)->get();

// Clientes por convenio
$clientes = Cliente::where('id_convenio', 1)->get();

// Cliente por ID
$cliente = Cliente::find(1);

// Cliente con relaciones
$cliente = Cliente::with('convenio', 'inscripciones', 'pagos')
    ->where('activo', true)
    ->first();

// Buscar por nombre
$clientes = Cliente::where('nombres', 'like', '%Juan%')
    ->orWhere('apellido_paterno', 'like', '%Juan%')
    ->get();

// Buscar por RUN
$cliente = Cliente::where('run_pasaporte', '12345678-9')->first();

// Con paginación
$clientes = Cliente::with('convenio')
    ->where('activo', true)
    ->paginate(15);
```

### Actualizar Cliente

```php
$cliente = Cliente::find(1);

$cliente->update([
    'email' => 'nuevoemail@example.com',
    'celular' => '+56999999999',
]);

// O actualización directa
$cliente->nombres = 'Juan Actualizado';
$cliente->save();

// Desactivar (soft delete)
$cliente->update(['activo' => false]);
```

### Eliminar Cliente

```php
// Soft delete
$cliente->update(['activo' => false]);

// Restaurar
$cliente->update(['activo' => true]);

// Eliminar completamente de BD
$cliente->forceDelete(); // No disponible por defecto
```

### Acceder a Relaciones

```php
$cliente = Cliente::with('convenio', 'inscripciones')->find(1);

// Convenio
$convenio = $cliente->convenio; // Si tiene
$convenio_nombre = $cliente->convenio?->nombre;

// Inscripciones del cliente
$inscripciones = $cliente->inscripciones;

// Primera inscripción activa
$inscripcion_activa = $cliente->inscripciones()
    ->where('id_estado', 201)
    ->first();

// Pagos realizados
$pagos = $cliente->pagos;
$total_pagado = $cliente->pagos->sum('monto_abonado');

// Nombre completo (accesor)
$nombre = $cliente->nombre_completo; // Juan Pérez López
```

---

## Inscripciones

### Crear Inscripción

```php
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\PrecioMembresia;
use Carbon\Carbon;

// Obtener membresía y precio
$membresia = Membresia::find(4); // Mensual
$precio = PrecioMembresia::where('id_membresia', 4)
    ->whereNull('fecha_vigencia_hasta')
    ->first();

// Crear inscripción
$inscripcion = Inscripcion::create([
    'id_cliente' => 1,
    'id_membresia' => 4,
    'id_precio_acordado' => $precio->id,
    'fecha_inscripcion' => Carbon::now()->toDateString(),
    'fecha_inicio' => Carbon::now()->toDateString(),
    'fecha_vencimiento' => Carbon::now()->addDays(30)->toDateString(),
    'precio_base' => 40000,
    'descuento_aplicado' => 5000, // $5k descuento
    'precio_final' => 35000,
    'id_motivo_descuento' => 3, // Cliente Frecuente
    'id_estado' => 201, // Activa
]);
```

### Obtener Inscripciones

```php
// Todas
$inscripciones = Inscripcion::all();

// Activas
$activas = Inscripcion::where('id_estado', 201)->get();

// Por cliente
$inscripciones_cliente = Inscripcion::where('id_cliente', 1)->get();

// Vencidas
$vencidas = Inscripcion::where('id_estado', 202)->get();

// Por vencer (próximos 7 días)
$por_vencer = Inscripcion::where('id_estado', 201)
    ->whereBetween('fecha_vencimiento', [
        Carbon::now()->startOfDay(),
        Carbon::now()->addDays(7)->endOfDay()
    ])
    ->with('cliente', 'membresia')
    ->get();

// Con todos los datos
$inscripcion = Inscripcion::with([
    'cliente',
    'membresia',
    'precioAcordado',
    'estado',
    'motivoDescuento',
    'pagos'
])->find(1);
```

### Actualizar Inscripción

```php
$inscripcion = Inscripcion::find(1);

// Aplicar descuento adicional
$nuevo_descuento = $inscripcion->descuento_aplicado + 2000;
$nuevo_precio_final = $inscripcion->precio_base - $nuevo_descuento;

$inscripcion->update([
    'descuento_aplicado' => $nuevo_descuento,
    'precio_final' => $nuevo_precio_final,
    'id_motivo_descuento' => 1,
    'observaciones' => 'Ajuste por promoción',
]);

// Cambiar estado a pausada
$inscripcion->update(['id_estado' => 203]);
```

### Cancelar Inscripción

```php
$inscripcion->update(['id_estado' => 204]); // Cancelada
```

---

## Pagos

### Registrar Pago

```php
use App\Models\Pago;
use App\Models\Inscripcion;
use Carbon\Carbon;

$inscripcion = Inscripcion::find(1);

// Pago completo
$pago = Pago::create([
    'id_inscripcion' => $inscripcion->id,
    'id_cliente' => $inscripcion->id_cliente,
    'monto_total' => $inscripcion->precio_final,
    'monto_abonado' => $inscripcion->precio_final,
    'monto_pendiente' => 0,
    'fecha_pago' => Carbon::now()->toDateString(),
    'periodo_inicio' => $inscripcion->fecha_inicio,
    'periodo_fin' => $inscripcion->fecha_vencimiento,
    'id_metodo_pago' => 1, // Efectivo
    'referencia_pago' => 'EFECTIVO-001',
    'id_estado' => 302, // Pagado
]);

// Pago parcial
$pago_parcial = Pago::create([
    'id_inscripcion' => 1,
    'id_cliente' => 1,
    'monto_total' => 40000,
    'monto_abonado' => 20000, // Mitad
    'monto_pendiente' => 20000,
    'fecha_pago' => Carbon::now()->toDateString(),
    'periodo_inicio' => $inscripcion->fecha_inicio,
    'periodo_fin' => $inscripcion->fecha_vencimiento,
    'id_metodo_pago' => 2, // Transferencia
    'referencia_pago' => 'TRF-123456',
    'id_estado' => 303, // Parcial
    'observaciones' => 'Primer abono, falta segunda cuota',
]);
```

### Obtener Pagos

```php
// Todos los pagos
$pagos = Pago::all();

// Pagos completados
$pagados = Pago::where('id_estado', 302)->get();

// Pagos pendientes
$pendientes = Pago::whereIn('id_estado', [301, 303])->get();

// Por cliente
$pagos_cliente = Pago::where('id_cliente', 1)
    ->with('inscripcion.membresia', 'metodoPago')
    ->get();

// Últimos pagos
$ultimos_pagos = Pago::latest('fecha_pago')
    ->with('cliente', 'inscripcion.membresia', 'estado')
    ->limit(10)
    ->get();

// Pagos de este mes
$pagos_mes = Pago::whereYear('fecha_pago', Carbon::now()->year)
    ->whereMonth('fecha_pago', Carbon::now()->month)
    ->get();
```

### Detalles del Pago

```php
$pago = Pago::with('cliente', 'inscripcion', 'metodoPago', 'estado')->find(1);

// Acceder a información relacionada
$cliente_nombre = $pago->cliente->nombre_completo;
$membresia_nombre = $pago->inscripcion->membresia->nombre;
$metodo = $pago->metodoPago->nombre;
$estado_pago = $pago->estado->nombre;
$saldo = $pago->monto_pendiente;
```

---

## Estadísticas

### Ingresos

```php
use Carbon\Carbon;

// Ingresos del mes actual
$ingresos_mes = Pago::whereYear('fecha_pago', Carbon::now()->year)
    ->whereMonth('fecha_pago', Carbon::now()->month)
    ->where('id_estado', 302) // Solo pagados
    ->sum('monto_abonado');

// Ingresos por método
$por_metodo = Pago::whereYear('fecha_pago', Carbon::now()->year)
    ->whereMonth('fecha_pago', Carbon::now()->month)
    ->with('metodoPago')
    ->get()
    ->groupBy('id_metodo_pago')
    ->map(function ($pagos) {
        return [
            'metodo' => $pagos->first()->metodoPago->nombre,
            'total' => $pagos->sum('monto_abonado'),
        ];
    });

// Ingresos de un rango de fechas
$desde = Carbon::parse('2024-11-01');
$hasta = Carbon::parse('2024-11-30');

$ingresos = Pago::whereBetween('fecha_pago', [$desde, $hasta])
    ->where('id_estado', 302)
    ->sum('monto_abonado');
```

### Saldo Pendiente

```php
// Total pendiente por cobrar
$total_pendiente = Pago::whereIn('id_estado', [301, 303])
    ->sum('monto_pendiente');

// Clientes con pagos pendientes
$clientes_adeudados = Pago::whereIn('id_estado', [301, 303])
    ->distinct('id_cliente')
    ->count();

// Pagos pendientes por cliente
$pendiente_cliente = Pago::where('id_cliente', 1)
    ->whereIn('id_estado', [301, 303])
    ->sum('monto_pendiente');
```

### Membresías

```php
// Total de clientes activos
$clientes_activos = Inscripcion::where('id_estado', 201)
    ->distinct('id_cliente')
    ->count();

// Membresías por tipo
$por_tipo = Inscripcion::where('id_estado', 201)
    ->with('membresia')
    ->get()
    ->groupBy('id_membresia')
    ->map(function ($insc) {
        return [
            'membresia' => $insc->first()->membresia->nombre,
            'cantidad' => $insc->count(),
        ];
    });

// Membresías vendidas este mes
$vendidas_mes = Inscripcion::whereYear('fecha_inscripcion', Carbon::now()->year)
    ->whereMonth('fecha_inscripcion', Carbon::now()->month)
    ->count();
```

---

## Búsquedas Avanzadas

### Consultas Complejas

```php
use App\Models\Cliente;
use App\Models\Inscripcion;
use Carbon\Carbon;

// Clientes con membresía activa y pagos pendientes
$deudores_activos = Cliente::whereHas('inscripciones', function ($query) {
    $query->where('id_estado', 201);
})
->whereHas('pagos', function ($query) {
    $query->whereIn('id_estado', [301, 303]);
})
->with('pagos', 'inscripciones')
->get();

// Clientes sin membresía activa hace más de 30 días
$inactivos = Cliente::whereDoesntHave('inscripciones', function ($query) {
    $query->where('id_estado', 201)
        ->where('fecha_vencimiento', '>=', Carbon::now());
})
->where('updated_at', '<', Carbon::now()->subDays(30))
->get();

// Inscripciones por vencer que aún tienen pagos pendientes
$en_riesgo = Inscripcion::where('id_estado', 201)
    ->whereBetween('fecha_vencimiento', [
        Carbon::now(),
        Carbon::now()->addDays(7)
    ])
    ->whereHas('pagos', function ($query) {
        $query->whereIn('id_estado', [301, 303]);
    })
    ->with('cliente', 'membresia')
    ->get();
```

### Ordenamiento y Límites

```php
// Últimos clientes registrados
$recientes = Cliente::latest('created_at')->limit(5)->get();

// Clientes ordenados por nombre
$alfabetico = Cliente::orderBy('apellido_paterno')
    ->orderBy('nombres')
    ->get();

// Pagos ordenados por monto (descendente)
$mayores_ingresos = Pago::orderBy('monto_abonado', 'desc')
    ->limit(10)
    ->get();

// Con offset y límite (paginación manual)
$pagina = 2;
$por_pagina = 15;
$offset = ($pagina - 1) * $por_pagina;

$clientes = Cliente::offset($offset)
    ->limit($por_pagina)
    ->get();
```

### Conteos y Agregaciones

```php
// Contar registros
$total_clientes = Cliente::count();
$activos = Cliente::where('activo', true)->count();

// Suma
$ingresos = Pago::where('id_estado', 302)->sum('monto_abonado');
$descuentos = Inscripcion::sum('descuento_aplicado');

// Promedio
$promedio_ingreso = Pago::where('id_estado', 302)->avg('monto_abonado');

// Mín/Máx
$mayor_pago = Pago::max('monto_abonado');
$menor_pago = Pago::min('monto_abonado');

// Exists
$tiene_pagos = Pago::where('id_cliente', 1)->exists();
```

---

## 💡 Ejemplo Completo: Crear Inscripción con Pago

```php
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use Carbon\Carbon;

// 1. Crear cliente
$cliente = Cliente::create([
    'nombres' => 'Pedro',
    'apellido_paterno' => 'García',
    'celular' => '+56912345678',
    'email' => 'pedro@example.com',
    'id_convenio' => null,
]);

// 2. Obtener membresía (Mensual)
$membresia = Membresia::where('nombre', 'Mensual')->first();
$precio = PrecioMembresia::where('id_membresia', $membresia->id)
    ->whereNull('fecha_vigencia_hasta')
    ->first();

// 3. Crear inscripción
$inscripcion = Inscripcion::create([
    'id_cliente' => $cliente->id,
    'id_membresia' => $membresia->id,
    'id_precio_acordado' => $precio->id,
    'fecha_inscripcion' => Carbon::now()->toDateString(),
    'fecha_inicio' => Carbon::now()->toDateString(),
    'fecha_vencimiento' => Carbon::now()->addDays(30)->toDateString(),
    'precio_base' => $precio->precio_normal,
    'descuento_aplicado' => 0,
    'precio_final' => $precio->precio_normal,
    'id_estado' => 201,
]);

// 4. Registrar pago
$pago = Pago::create([
    'id_inscripcion' => $inscripcion->id,
    'id_cliente' => $cliente->id,
    'monto_total' => $inscripcion->precio_final,
    'monto_abonado' => $inscripcion->precio_final,
    'monto_pendiente' => 0,
    'fecha_pago' => Carbon::now()->toDateString(),
    'periodo_inicio' => $inscripcion->fecha_inicio,
    'periodo_fin' => $inscripcion->fecha_vencimiento,
    'id_metodo_pago' => 1, // Efectivo
    'id_estado' => 302, // Pagado
]);

// 5. Verificar
$resultado = [
    'cliente' => $cliente->nombre_completo,
    'membresía' => $membresia->nombre,
    'monto' => $inscripcion->precio_final,
    'estado_pago' => 'Completado',
];
```

---

**Última actualización**: 25 de Noviembre de 2024

