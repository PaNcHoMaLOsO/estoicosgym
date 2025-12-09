# 🚀 GUÍA RÁPIDA DE CÓDIGO - 4 HORAS PARA ESTUDIAR
## Dónde está cada cosa y qué hace

**Fecha:** 09/12/2025  
**Para:** Presentación del Prototipo  
**Tiempo de estudio:** 4 horas

---

## 📁 ESTRUCTURA LARAVEL (Lo Básico)

```
app/
├── Models/           → Los DATOS (tablas de la BD)
├── Http/Controllers/ → La LÓGICA (qué hace cada pantalla)
├── Services/         → SERVICIOS (lógica compleja reutilizable)
└── Enums/            → ESTADOS (números que significan algo)

resources/views/      → Las PANTALLAS (HTML que ve el usuario)
routes/web.php        → Las RUTAS (URLs del sistema)
database/migrations/  → La BASE DE DATOS (estructura de tablas)
```

### 🎯 Patrón MVC:
```
Usuario hace clic → RUTA (web.php) 
                  → CONTROLADOR (lógica)
                  → MODELO (datos)
                  → VISTA (pantalla)
                  → Usuario ve resultado
```

---

## 🔴 RF-02: GESTIÓN DE CLIENTES

### 📂 Archivos Importantes:

```
MODELO:
app/Models/Cliente.php (líneas clave: 20-50)

CONTROLADOR:
app/Http/Controllers/Admin/ClienteController.php

VISTAS:
resources/views/admin/clientes/
├── index.blade.php   → Listado
├── create.blade.php  → Formulario crear
├── edit.blade.php    → Formulario editar
└── show.blade.php    → Ver detalle

RUTA:
routes/web.php (buscar "clientes")
```

### 🔍 Código Clave:

#### 1. **MODELO Cliente.php** (Lo que representa)
```php
// Línea ~66-92
class Cliente extends Model
{
    protected $fillable = [
        'uuid',
        'run_pasaporte',      // RUT o pasaporte
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'celular',
        'email',
        'direccion',
        'fecha_nacimiento',
        'contacto_emergencia',
        'telefono_emergencia',
        'id_convenio',
        'observaciones',
        'activo',             // boolean (en lugar de estado_id)
        // Campos para menores de edad
        'es_menor_edad',
        'consentimiento_apoderado',
        'apoderado_nombre',
        'apoderado_rut',
        'apoderado_email',
        'apoderado_telefono',
    ];
}
```
**Qué hace:** Define qué campos tiene un cliente en la BD. El sistema maneja clientes con RUT/pasaporte, datos de contacto de emergencia, y si es menor de edad registra los datos del apoderado.

#### 2. **VALIDACIÓN RUT** (Lo más técnico)
```php
// ClienteController.php línea ~80-120
protected function validarRut($rut)
{
    // Elimina puntos y guión
    $rut = preg_replace('/[^0-9kK]/', '', $rut);
    
    // Separa número y dígito verificador
    $numero = substr($rut, 0, -1);
    $dv = strtoupper(substr($rut, -1));
    
    // Algoritmo módulo 11
    $suma = 0;
    $multiplicador = 2;
    
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += $numero[$i] * $multiplicador;
        $multiplicador = $multiplicador == 7 ? 2 : $multiplicador + 1;
    }
    
    $dvCalculado = 11 - ($suma % 11);
    if ($dvCalculado == 11) $dvCalculado = 0;
    if ($dvCalculado == 10) $dvCalculado = 'K';
    
    return $dvCalculado == $dv;
}
```
**Si te preguntan:** "Validamos el RUT con algoritmo módulo 11, es el estándar chileno. Multiplicamos cada dígito por 2,3,4,5,6,7,2,3... y verificamos el dígito verificador."

#### 3. **CREAR CLIENTE** (Flujo completo)
```php
// ClienteController.php método store() línea ~150-200
public function store(Request $request)
{
    // 1. VALIDAR datos del formulario
    $validated = $request->validate([
        'run_pasaporte' => 'required|unique:clientes',
        'nombres' => 'required|string|max:100',
        'apellido_paterno' => 'required|string|max:100',
        'celular' => 'required|string',
        'email' => 'nullable|email|unique:clientes',
        // ... más validaciones
    ]);
    
    // 2. VALIDAR RUT específicamente (si es RUT chileno)
    if ($this->esRutChileno($request->run_pasaporte)) {
        if (!$this->validarRut($request->run_pasaporte)) {
            return back()->with('error', 'RUT inválido');
        }
    }
    
    // 3. GUARDAR en base de datos
    $cliente = Cliente::create($validated);
    
    // 4. SI ES MENOR → Los datos del apoderado ya están en el mismo registro
    // El modelo Cliente incluye todos los campos del apoderado:
    // - apoderado_nombre, apoderado_rut, apoderado_email
    // - apoderado_telefono, consentimiento_apoderado
    
    // 5. REDIRIGIR con mensaje
    return redirect()->route('admin.clientes.index')
                    ->with('success', 'Cliente creado');
}
```
**Si te preguntan:** "Primero validamos todos los datos, si es RUT chileno lo verificamos con el algoritmo módulo 11, guardamos el cliente con todos sus datos. Si es menor de edad, los datos del apoderado se guardan en el mismo registro del cliente."

#### 4. **SOFT DELETE** (Borrado lógico)
```php
// Cliente.php línea ~25
use SoftDeletes;

protected $dates = ['deleted_at'];

// ClienteController.php método destroy()
public function destroy($id)
{
    $cliente = Cliente::findOrFail($id);
    
    // No borra físicamente, solo marca deleted_at
    $cliente->delete();
    
    return redirect()->route('admin.clientes.index')
                    ->with('success', 'Cliente eliminado');
}

// Para recuperar
public function restore($id)
{
    $cliente = Cliente::withTrashed()->findOrFail($id);
    $cliente->restore();
    
    return redirect()->back()
                    ->with('success', 'Cliente restaurado');
}
```
**Si te preguntan:** "Usamos soft delete de Laravel. No borramos físicamente, solo marcamos una fecha de eliminación. Así mantenemos el historial y podemos recuperar."

---

## 🟢 RF-03: GESTIÓN DE MEMBRESÍAS

### 📂 Archivos Importantes:

```
MODELO:
app/Models/Membresia.php

CONTROLADOR:
app/Http/Controllers/Admin/MembresiaController.php

VISTAS:
resources/views/admin/membresias/
├── index.blade.php
├── create.blade.php
└── edit.blade.php
```

### 🔍 Código Clave:

#### 1. **MODELO Membresia.php** (Sistema de precios separado)
```php
// Línea ~47-55
protected $fillable = [
    'uuid',
    'nombre',
    'duracion_meses',    // Meses de duración
    'duracion_dias',     // Días (para pase diario o anual)
    'max_pausas',        // Máximo de pausas permitidas
    'descripcion',
    'activo',            // boolean
];

// Relación con precios (tabla separada)
public function precios()
{
    return $this->hasMany(PrecioMembresia::class, 'id_membresia');
}
```
**IMPORTANTE:** Los precios NO están en la tabla membresías, están en `precios_membresias` con vigencias.

#### 2. **SISTEMA DE PRECIOS CON VIGENCIAS** (Lo diferenciador)
```php
// TABLA: precios_membresias
// Modelo: PrecioMembresia.php

protected $fillable = [
    'id_membresia',
    'precio_normal',              // Precio para clientes sin convenio
    'precio_convenio',            // Precio con descuento (NULL = sin descuento)
    'fecha_vigencia_desde',       // Desde cuándo es válido este precio
    'fecha_vigencia_hasta',       // Hasta cuándo (NULL = vigente actualmente)
    'activo',                     // boolean
];

// Ejemplo de cambio de precio:
// 1. El precio actual tiene fecha_vigencia_hasta = NULL (vigente)
// 2. Al crear nuevo precio:

$precio_actual = PrecioMembresia::where('id_membresia', $membresia_id)
    ->whereNull('fecha_vigencia_hasta')
    ->first();

// Cerrar precio actual
$precio_actual->update([
    'fecha_vigencia_hasta' => now()->subDay(),
    'activo' => false
]);

// Crear nuevo precio
PrecioMembresia::create([
    'id_membresia' => $membresia_id,
    'precio_normal' => $nuevo_precio_normal,
    'precio_convenio' => $nuevo_precio_convenio,
    'fecha_vigencia_desde' => now(),
    'fecha_vigencia_hasta' => null,  // Vigente
    'activo' => true
]);

// Historial automático en tabla historial_precios
HistorialPrecio::create([
    'id_precio_membresia' => $precio_actual->id,
    'precio_normal_anterior' => $precio_actual->precio_normal,
    'precio_normal_nuevo' => $nuevo_precio_normal,
    'fecha_cambio' => now(),
    'id_usuario' => auth()->id()
]);
```
**Si te preguntan:** "Usamos un sistema de precios con vigencias. Cada precio tiene fecha_desde y fecha_hasta. Al cambiar precios, cerramos el anterior y creamos uno nuevo. El historial se genera automáticamente en la tabla `historial_precios`. Esto permite trazabilidad completa y saber qué precio tenía una inscripción en cualquier fecha."

#### 3. **OBTENER PRECIO VIGENTE**
```php
// Obtener precio actual de una membresía
$precio_vigente = PrecioMembresia::where('id_membresia', $membresia_id)
    ->where('activo', true)
    ->whereNull('fecha_vigencia_hasta')  // Vigente actualmente
    ->first();

// O con relación:
$membresia = Membresia::find($id);
$precio_actual = $membresia->precios()
    ->where('activo', true)
    ->whereNull('fecha_vigencia_hasta')
    ->first();

// Al crear inscripción, guardar el ID del precio:
Inscripcion::create([
    'id_cliente' => $cliente_id,
    'id_membresia' => $membresia_id,
    'id_precio_acordado' => $precio_actual->id,  // ← Referencia al precio
    'precio_base' => $precio_actual->precio_normal,
    'precio_final' => $precio_actual->precio_convenio ?? $precio_actual->precio_normal,
    // ...
]);
```
**Ventaja:** Cada inscripción queda ligada al precio específico que tenía la membresía en ese momento, incluso si después cambia el precio.

---

## 🟡 RF-04: INSCRIPCIONES Y PAGOS

### 📂 Archivos Importantes:

```
MODELOS:
app/Models/Inscripcion.php       → La inscripción
app/Models/Pago.php              → Los pagos
app/Models/PagoParcial.php       → Cuotas/pagos en partes

CONTROLADORES:
app/Http/Controllers/Admin/InscripcionController.php
app/Http/Controllers/Admin/PagoController.php

VISTAS:
resources/views/admin/inscripciones/
├── index.blade.php
├── create.blade.php
└── show.blade.php    → AQUÍ SE VE TODO
```

### 🔍 Código Clave:

#### 1. **ESTADOS con CONSTANTES** (Lo más importante)
```php
// app/Enums/EstadosCodigo.php
// NO usamos enum, usamos clase con constantes

class EstadosCodigo
{
    // ESTADOS DE INSCRIPCIÓN/MEMBRESÍA (100-106)
    public const INSCRIPCION_ACTIVA = 100;
    public const INSCRIPCION_PAUSADA = 101;
    public const INSCRIPCION_VENCIDA = 102;
    public const INSCRIPCION_CANCELADA = 103;
    public const INSCRIPCION_SUSPENDIDA = 104;
    public const INSCRIPCION_CAMBIADA = 105;
    public const INSCRIPCION_TRASPASADA = 106;
    
    // ESTADOS DE PAGO (200-205)
    public const PAGO_PENDIENTE = 200;
    public const PAGO_PAGADO = 201;
    public const PAGO_PARCIAL = 202;
    public const PAGO_VENCIDO = 203;
    public const PAGO_CANCELADO = 204;
    public const PAGO_TRASPASADO = 205;
    
    // ESTADOS DE CLIENTE (400-402)
    public const CLIENTE_ACTIVO = 400;
    public const CLIENTE_SUSPENDIDO = 401;
    public const CLIENTE_CANCELADO = 402;
    
    // ESTADOS DE NOTIFICACIÓN (600-603)
    public const NOTIFICACION_PENDIENTE = 600;
    public const NOTIFICACION_ENVIADA = 601;
    public const NOTIFICACION_FALLIDA = 602;
    public const NOTIFICACION_CANCELADA = 603;
    
    // GRUPOS para validaciones
    public const INSCRIPCION_ACCESO_PERMITIDO = [
        self::INSCRIPCION_ACTIVA,
    ];
    
    public const INSCRIPCION_FINALIZADOS = [
        self::INSCRIPCION_CANCELADA,
        self::INSCRIPCION_CAMBIADA,
        self::INSCRIPCION_TRASPASADA,
    ];
}

// Uso:
use App\Enums\EstadosCodigo;

$inscripcion->id_estado = EstadosCodigo::INSCRIPCION_ACTIVA; // 100
```
**Si te preguntan:** "Usamos una clase con constantes públicas para centralizar todos los códigos de estado. Cada grupo tiene un rango numérico (100-106 inscripciones, 200-205 pagos, 600-603 notificaciones). Es más mantenible que hardcodear números y evita errores."

#### 2. **CREAR INSCRIPCIÓN** (Flujo completo)
```php
// InscripcionController.php método store() línea ~200-280
public function store(Request $request)
{
    DB::beginTransaction();
    
    try {
        // 1. OBTENER PRECIO VIGENTE
        $precio_vigente = PrecioMembresia::where('id_membresia', $request->membresia_id)
            ->whereNull('fecha_vigencia_hasta')
            ->first();
        
        // 2. CREAR INSCRIPCIÓN
        $inscripcion = Inscripcion::create([
            'uuid' => Str::uuid(),
            'id_cliente' => $request->cliente_id,
            'id_membresia' => $request->membresia_id,
            'id_precio_acordado' => $precio_vigente->id,  // Precio en ese momento
            'fecha_inscripcion' => now(),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_vencimiento' => $this->calcularVencimiento(
                $request->fecha_inicio, 
                $membresia->duracion_dias
            ),
            'precio_base' => $precio_vigente->precio_normal,
            'descuento_aplicado' => $request->descuento ?? 0,
            'precio_final' => $precio_vigente->precio_normal - ($request->descuento ?? 0),
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA  // No ::value
        ]);
        
        // 3. CREAR PAGO (si paga algo)
        if ($request->monto_abonado > 0) {
            $monto_pendiente = $inscripcion->precio_final - $request->monto_abonado;
            
            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
                'monto_abonado' => $request->monto_abonado,
                'monto_pendiente' => $monto_pendiente,
                'id_metodo_pago' => $request->metodo_pago_id,
                'fecha_pago' => now(),
                'cantidad_cuotas' => $request->cantidad_cuotas ?? 1,
                'numero_cuota' => 1,
                'id_estado' => $monto_pendiente > 0 
                    ? EstadosCodigo::PAGO_PARCIAL 
                    : EstadosCodigo::PAGO_PAGADO
            ]);
            
            // SI PAGÓ TODO → Ya está activa
            // Si no, queda ACTIVA pero con saldo pendiente
        }
        
        // 4. ENVIAR NOTIFICACIÓN (si existe evento)
        // event(new InscripcionCreada($inscripcion));
        
        DB::commit();
        
        return redirect()->route('admin.inscripciones.show', $inscripcion)
                        ->with('success', 'Inscripción creada');
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}
```
**Si te preguntan:** "Usamos transacciones de base de datos. Si algo falla, todo se revierte automáticamente. Primero obtenemos el precio vigente actual de la membresía, creamos la inscripción guardando el ID del precio (para trazabilidad histórica), creamos el pago inicial que puede ser parcial o completo. La inscripción se crea directamente activa si no hay restricciones."

#### 3. **PAUSAR INSCRIPCIÓN** (Lógica de negocio)
```php
// InscripcionController.php método pausar() línea ~400-450
public function pausar(Request $request, $id)
{
    $inscripcion = Inscripcion::findOrFail($id);
    
    // VALIDAR: Solo se puede pausar si está Activa
    if ($inscripcion->id_estado != EstadosCodigo::INSCRIPCION_ACTIVA) {
        return back()->with('error', 'Solo se pueden pausar inscripciones activas');
    }
    
    // VALIDAR: Debe pagar multa (10% del total)
    $multa = $inscripcion->precio_final * 0.10;
    
    if ($request->pago_multa < $multa) {
        return back()->with('error', "Debe pagar multa de $multa");
    }
    
    // CALCULAR nueva fecha de vencimiento
    $dias_transcurridos = now()->diffInDays($inscripcion->fecha_inicio);
    $dias_restantes = $inscripcion->membresia->duracion_dias - $dias_transcurridos;
    
    $nueva_fecha_vencimiento = now()
        ->addDays($request->dias_pausa)
        ->addDays($dias_restantes);
    
    // ACTUALIZAR
    $inscripcion->update([
        'id_estado' => EstadosCodigo::INSCRIPCION_PAUSADA,
        'pausada' => true,
        'fecha_pausa_inicio' => now(),
        'fecha_pausa_fin' => now()->addDays($request->dias_pausa),
        'dias_pausa' => $request->dias_pausa,
        'fecha_vencimiento' => $nueva_fecha_vencimiento,
        'pausas_realizadas' => $inscripcion->pausas_realizadas + 1
    ]);
    
    // REGISTRAR PAGO DE MULTA
    Pago::create([
        'uuid' => Str::uuid(),
        'id_inscripcion' => $inscripcion->id,
        'id_cliente' => $inscripcion->id_cliente,
        'monto_abonado' => $request->pago_multa,
        'monto_pendiente' => 0,
        'id_metodo_pago' => $request->metodo_pago_id,
        'observaciones' => 'Multa por pausa de membresía',
        'fecha_pago' => now(),
        'id_estado' => EstadosCodigo::PAGO_PAGADO
    ]);
    
    return back()->with('success', 'Inscripción pausada');
}
```
**Si te preguntan:** "La pausa requiere pagar una multa del 10%. Calculamos cuántos días le quedan, sumamos los días de pausa, y actualizamos la fecha de vencimiento. Todo queda registrado en el historial."

#### 4. **PAGOS PARCIALES / EN CUOTAS** (Lo complejo)
```php
// PagoController.php método registrarParcial() línea ~150-200
public function registrarParcial(Request $request)
{
    $inscripcion = Inscripcion::findOrFail($request->inscripcion_id);
    
    // CALCULAR saldo pendiente
    $total_pagado = $inscripcion->pagos()->sum('monto_abonado');
    $saldo_pendiente = $inscripcion->precio_final - $total_pagado;
    
    // VALIDAR monto
    if ($request->monto_abonado > $saldo_pendiente) {
        return back()->with('error', 'Monto excede saldo pendiente');
    }
    
    // OBTENER número de cuota (contar cuotas anteriores)
    $numero_cuota = $inscripcion->pagos()->count() + 1;
    
    // CREAR pago parcial (en la MISMA tabla pagos)
    $pago = Pago::create([
        'uuid' => Str::uuid(),
        'grupo_pago' => $inscripcion->uuid,  // Agrupar cuotas
        'id_inscripcion' => $inscripcion->id,
        'id_cliente' => $inscripcion->id_cliente,
        'monto_abonado' => $request->monto_abonado,
        'monto_pendiente' => $saldo_pendiente - $request->monto_abonado,
        'id_metodo_pago' => $request->metodo_pago_id,
        'fecha_pago' => now(),
        'cantidad_cuotas' => $request->cantidad_cuotas_total,
        'numero_cuota' => $numero_cuota,
        'monto_cuota' => $request->monto_abonado,
        'id_estado' => ($saldo_pendiente - $request->monto_abonado) > 0
            ? EstadosCodigo::PAGO_PARCIAL
            : EstadosCodigo::PAGO_PAGADO
    ]);
    
    // VERIFICAR si completó el pago
    $nuevo_total_pagado = $total_pagado + $request->monto_abonado;
    
    if ($nuevo_total_pagado >= $inscripcion->precio_final) {
        // Actualizar último pago a PAGADO
        $pago->update(['id_estado' => EstadosCodigo::PAGO_PAGADO]);
    }
    
    return back()->with('success', "Cuota #{$numero_cuota} registrada. Saldo: $" . 
                        number_format($saldo_pendiente - $request->monto_abonado, 0));
}
```
**Si te preguntan:** "NO usamos tabla `pagos_parciales` separada. Los pagos en cuotas se manejan en la misma tabla `pagos` con los campos `cantidad_cuotas`, `numero_cuota` y `grupo_pago` (UUID). Cada cuota es un registro independiente. Cuando la suma de `monto_abonado` de todos los pagos alcanza el `precio_final`, el estado cambia a PAGADO."

#### 5. **TRASPASOS** (Lo más complejo)
```php
// InscripcionController.php método traspaso() línea ~550-650
public function traspaso(Request $request)
{
    DB::beginTransaction();
    
    try {
        $inscripcion_origen = Inscripcion::findOrFail($request->inscripcion_id);
        
        // VALIDAR: Debe estar activa y paga
        if ($inscripcion_origen->id_estado != EstadosCodigo::INSCRIPCION_ACTIVA) {
            throw new \Exception('Inscripción debe estar activa');
        }
        
        $saldo_pendiente = $inscripcion_origen->precio_final - 
                           $inscripcion_origen->pagos()->sum('monto_abonado');
        
        if ($saldo_pendiente > 0) {
            throw new \Exception('Debe estar completamente pagada');
        }
        
        // CALCULAR días restantes
        $dias_restantes = now()->diffInDays($inscripcion_origen->fecha_vencimiento);
        
        // CALCULAR proporcional a pagar
        $membresia_nueva = Membresia::findOrFail($request->membresia_nueva_id);
        $precio_nuevo = $membresia_nueva->precios()
            ->whereNull('fecha_vigencia_hasta')->first();
        
        $precio_dia_anterior = $inscripcion_origen->precio_final / 
                               $inscripcion_origen->membresia->duracion_dias;
        $valor_dias_restantes = $precio_dia_anterior * $dias_restantes;
        $diferencia_pagar = $precio_nuevo->precio_normal - $valor_dias_restantes;
        
        // MARCAR inscripción anterior como traspasada
        $inscripcion_origen->update([
            'id_estado' => EstadosCodigo::INSCRIPCION_TRASPASADA,
            'observaciones' => 'Traspasada a nueva membresía el ' . now()->format('d/m/Y')
        ]);
        
        // CREAR nueva inscripción
        $inscripcion_nueva = Inscripcion::create([
            'uuid' => Str::uuid(),
            'id_cliente' => $inscripcion_origen->id_cliente,
            'id_membresia' => $request->membresia_nueva_id,
            'id_precio_acordado' => $precio_nuevo->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays($membresia_nueva->duracion_dias),
            'precio_base' => $precio_nuevo->precio_normal,
            'descuento_aplicado' => $valor_dias_restantes,  // Crédito de días anteriores
            'precio_final' => $diferencia_pagar,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'es_cambio_plan' => true,
            'id_inscripcion_anterior' => $inscripcion_origen->id, // ← Referencia
            'tipo_cambio' => $diferencia_pagar > 0 ? 'upgrade' : 'downgrade',
            'credito_plan_anterior' => $valor_dias_restantes
        ]);
        
        // SI DEBE PAGAR DIFERENCIA
        if ($diferencia_pagar > 0) {
            // Usuario debe pagar
            DB::commit();
            return redirect()->route('admin.inscripciones.show', $inscripcion_nueva)
                            ->with('warning', "Debe pagar diferencia: $$diferencia_pagar");
        } else {
            // No debe pagar (downgrade) o le sobra crédito
            // Ya está activa desde la creación
            
            DB::commit();
            return redirect()->route('admin.inscripciones.show', $inscripcion_nueva)
                            ->with('success', 'Traspaso exitoso. Crédito aplicado: $' . 
                                   number_format($valor_dias_restantes, 0));
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}
```
**Si te preguntan:** "El traspaso es lo más complejo. Calculamos cuántos días le quedan, su valor proporcional, la diferencia con la nueva membresía, cancelamos la antigua, creamos la nueva con descuento por los días que ya pagó. Todo en una transacción para que sea atómico."

---

## 🔵 RF-07: NOTIFICACIONES AUTOMÁTICAS

### 📂 Archivos Importantes:

```
MODELOS:
app/Models/Notificacion.php
app/Models/TipoNotificacion.php
app/Models/LogNotificacion.php

CONTROLADOR:
app/Http/Controllers/Admin/NotificacionController.php

SERVICIO:
app/Services/NotificacionService.php    ← AQUÍ ESTÁ LA MAGIA

COMANDO:
app/Console/Commands/EnviarNotificaciones.php

PLANTILLAS:
storage/app/test_emails/preview/*.html
```

### 🔍 Código Clave:

#### 1. **SERVICIO NotificacionService.php** (Lo más importante)
```php
// Línea ~50-120
public function programarNotificacionesPorVencer()
{
    // BUSCAR inscripciones que vencen en 7 días
    $dias_anticipacion = 7;
    $fecha_objetivo = now()->addDays($dias_anticipacion)->toDateString();
    
    $inscripciones = Inscripcion::where('estado_id', EstadoInscripcion::ACTIVA->value)
        ->whereDate('fecha_vencimiento', $fecha_objetivo)
        ->with('cliente', 'membresia')
        ->get();
    
    $programadas = 0;
    
    foreach ($inscripciones as $inscripcion) {
        // VERIFICAR que no exista ya
        $existe = Notificacion::where('cliente_id', $inscripcion->cliente_id)
            ->where('tipo_notificacion_id', 6) // membresia_por_vencer
            ->where('created_at', '>=', now()->subDay())
            ->exists();
        
        if (!$existe) {
            // CREAR notificación
            Notificacion::create([
                'cliente_id' => $inscripcion->cliente_id,
                'inscripcion_id' => $inscripcion->id,
                'tipo_notificacion_id' => 6,
                'asunto' => 'Tu membresía vence pronto',
                'programado_para' => now()->addHours(2),
                'estado_id' => 600, // Pendiente
                'intentos' => 0
            ]);
            
            $programadas++;
        }
    }
    
    return $programadas;
}

// Línea ~130-200
public function enviarPendientes()
{
    // BUSCAR notificaciones pendientes programadas para hoy o antes
    $pendientes = Notificacion::where('estado_id', 600)
        ->where('programado_para', '<=', now())
        ->with('cliente', 'tipoNotificacion')
        ->get();
    
    $enviadas = 0;
    $fallidas = 0;
    
    foreach ($pendientes as $notificacion) {
        // VALIDAR límites anti-spam
        if (!$this->puedeEnviar($notificacion->cliente_id)) {
            continue;
        }
        
        try {
            // OBTENER plantilla
            $plantilla = $notificacion->tipoNotificacion;
            
            // RENDERIZAR con datos
            $contenido = $this->renderizarPlantilla(
                $plantilla->html_template,
                $notificacion->cliente,
                $notificacion->inscripcion
            );
            
            // ENVIAR vía Resend
            $resultado = Resend::emails()->send([
                'from' => 'PROGYM <onboarding@resend.dev>',
                'to' => [$notificacion->cliente->email],
                'subject' => $notificacion->asunto,
                'html' => $contenido
            ]);
            
            // MARCAR como enviada
            $notificacion->update([
                'estado_id' => 601, // Enviada
                'fecha_enviado' => now(),
                'resend_id' => $resultado['id']
            ]);
            
            // LOG
            LogNotificacion::create([
                'notificacion_id' => $notificacion->id,
                'accion' => 'enviada',
                'resultado' => 'exitoso',
                'resend_id' => $resultado['id']
            ]);
            
            $enviadas++;
            
        } catch (\Exception $e) {
            // MARCAR como fallida
            $notificacion->increment('intentos');
            
            if ($notificacion->intentos >= 3) {
                $notificacion->update(['estado_id' => 602]); // Fallida
            }
            
            // LOG
            LogNotificacion::create([
                'notificacion_id' => $notificacion->id,
                'accion' => 'intento_envio',
                'resultado' => 'fallido',
                'error' => $e->getMessage()
            ]);
            
            $fallidas++;
        }
    }
    
    return ['enviadas' => $enviadas, 'fallidas' => $fallidas];
}

// Línea ~250-290
protected function puedeEnviar($cliente_id)
{
    // LÍMITE 1: Máximo 3 notificaciones por día
    $hoy = Notificacion::where('cliente_id', $cliente_id)
        ->where('estado_id', 601)
        ->whereDate('fecha_enviado', today())
        ->count();
    
    if ($hoy >= 3) {
        return false;
    }
    
    // LÍMITE 2: Intervalo mínimo 2 horas
    $ultima = Notificacion::where('cliente_id', $cliente_id)
        ->where('estado_id', 601)
        ->orderBy('fecha_enviado', 'desc')
        ->first();
    
    if ($ultima && $ultima->fecha_enviado->diffInHours(now()) < 2) {
        return false;
    }
    
    // LÍMITE 3: No duplicar misma notificación en 24 horas
    $duplicada = Notificacion::where('cliente_id', $cliente_id)
        ->where('tipo_notificacion_id', $this->tipo_id)
        ->where('created_at', '>=', now()->subDay())
        ->exists();
    
    if ($duplicada) {
        return false;
    }
    
    return true;
}
```
**Si te preguntan:** "El servicio es el corazón del sistema. Busca inscripciones que vencen en 7 días, crea notificaciones pendientes, las envía respetando límites anti-spam (3 por día, 2 horas de intervalo, sin duplicar), y registra todo en logs."

#### 2. **COMANDO Artisan** (Automatización)
```php
// app/Console/Commands/EnviarNotificaciones.php línea ~40-80
public function handle()
{
    $this->info('🚀 Iniciando proceso de notificaciones...');
    
    // PROGRAMAR NUEVAS
    if ($this->option('programar') || $this->option('todo')) {
        $this->info('📋 Programando notificaciones...');
        
        $por_vencer = $this->notificacionService->programarNotificacionesPorVencer();
        $this->info("   ✅ Por vencer: {$por_vencer}");
        
        $vencidas = $this->notificacionService->programarNotificacionesVencidas();
        $this->info("   ✅ Vencidas: {$vencidas}");
    }
    
    // ENVIAR PENDIENTES
    if ($this->option('enviar') || $this->option('todo')) {
        $this->info('📧 Enviando notificaciones pendientes...');
        
        $resultado = $this->notificacionService->enviarPendientes();
        $this->info("   ✅ Enviadas: {$resultado['enviadas']}");
        $this->info("   ❌ Fallidas: {$resultado['fallidas']}");
    }
    
    // REINTENTAR FALLIDAS
    if ($this->option('reintentar')) {
        $this->info('🔄 Reintentando fallidas...');
        
        $reintentadas = $this->notificacionService->reintentarFallidas();
        $this->info("   ✅ Reintentadas: {$reintentadas}");
    }
    
    $this->info('✅ Proceso completado');
    
    return 0;
}
```
**Si te preguntan:** "Tenemos un comando Artisan que se ejecuta diariamente con CRON. Programa nuevas notificaciones, envía las pendientes y reintenta las fallidas. Todo automatizado."

#### 3. **CONTROLADOR** (Envío Manual)
```php
// NotificacionController.php método store() línea ~200-280
public function store(Request $request)
{
    // VALIDAR
    $validated = $request->validate([
        'tipo_notificacion_id' => 'required|exists:tipos_notificacion,id',
        'tipo_envio' => 'required|in:individual,por_membresia,por_estado,todos',
        'asunto' => 'nullable|string',
        'mensaje_adicional' => 'nullable|string'
    ]);
    
    // OBTENER DESTINATARIOS según filtro
    $destinatarios = $this->obtenerDestinatarios($request);
    
    if ($destinatarios->isEmpty()) {
        return back()->with('error', 'No hay destinatarios válidos');
    }
    
    // VALIDAR límite diario (500)
    $enviadas_hoy = Notificacion::whereDate('created_at', today())->count();
    
    if ($enviadas_hoy + $destinatarios->count() > 500) {
        return back()->with('error', 'Límite diario excedido');
    }
    
    DB::beginTransaction();
    
    try {
        $creadas = 0;
        
        foreach ($destinatarios as $cliente) {
            // CREAR notificación
            $notificacion = Notificacion::create([
                'cliente_id' => $cliente->id,
                'tipo_notificacion_id' => $request->tipo_notificacion_id,
                'asunto' => $request->asunto ?? $this->obtenerAsuntoDefault($request->tipo_notificacion_id),
                'mensaje_adicional' => $request->mensaje_adicional,
                'programado_para' => $request->enviar_ahora ? now() : $request->programar_fecha,
                'estado_id' => 600, // Pendiente
                'intentos' => 0
            ]);
            
            // SI ES INMEDIATO → Enviar ahora
            if ($request->enviar_ahora) {
                $resultado = $this->notificacionService->enviarNotificacion($notificacion);
                
                if ($resultado['exito']) {
                    $creadas++;
                }
            } else {
                $creadas++;
            }
        }
        
        DB::commit();
        
        if ($request->enviar_ahora) {
            return redirect()->route('admin.notificaciones.index')
                            ->with('success', "{$creadas} de {$destinatarios->count()} enviadas");
        } else {
            return redirect()->route('admin.notificaciones.index')
                            ->with('success', "{$creadas} notificaciones programadas");
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

// Método auxiliar línea ~320-360
protected function obtenerDestinatarios(Request $request)
{
    switch ($request->tipo_envio) {
        case 'individual':
            return Cliente::where('id', $request->cliente_id)
                         ->where('estado_id', 1) // Activo
                         ->get();
        
        case 'por_membresia':
            return Cliente::whereHas('inscripciones', function($q) use ($request) {
                $q->where('membresia_id', $request->membresia_id)
                  ->where('estado_id', EstadoInscripcion::ACTIVA->value);
            })->where('estado_id', 1)->get();
        
        case 'por_estado':
            return Cliente::whereHas('inscripciones', function($q) use ($request) {
                $q->where('estado_id', $request->estado_inscripcion_id);
            })->where('estado_id', 1)->get();
        
        case 'todos':
            return Cliente::where('estado_id', 1)
                         ->whereHas('inscripciones', function($q) {
                             $q->where('estado_id', EstadoInscripcion::ACTIVA->value);
                         })
                         ->get();
        
        default:
            return collect();
    }
}
```
**Si te preguntan:** "El envío manual permite seleccionar destinatarios por varios filtros. Creamos notificaciones para todos los seleccionados y las enviamos inmediatamente o programadas. Todo con transacciones y validaciones de límites."

#### 4. **PLANTILLAS HTML** (Renderizado)
```php
// NotificacionService.php método renderizarPlantilla() línea ~300-350
protected function renderizarPlantilla($html, $cliente, $inscripcion = null)
{
    // REEMPLAZAR variables
    $variables = [
        '{{nombre}}' => $cliente->nombre . ' ' . $cliente->apellido_paterno,
        '{{email}}' => $cliente->email,
        '{{fecha_actual}}' => now()->format('d/m/Y'),
    ];
    
    if ($inscripcion) {
        $variables = array_merge($variables, [
            '{{membresia}}' => $inscripcion->membresia->nombre,
            '{{codigo}}' => $inscripcion->codigo,
            '{{fecha_inicio}}' => $inscripcion->fecha_inicio->format('d/m/Y'),
            '{{fecha_vencimiento}}' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
            '{{dias_restantes}}' => now()->diffInDays($inscripcion->fecha_vencimiento),
            '{{total_pagar}}' => number_format($inscripcion->total_pagar, 0),
            '{{saldo_pendiente}}' => number_format($inscripcion->saldo_pendiente, 0),
        ]);
    }
    
    // APLICAR reemplazos
    $contenido = $html;
    foreach ($variables as $variable => $valor) {
        $contenido = str_replace($variable, $valor, $contenido);
    }
    
    return $contenido;
}
```
**Si te preguntan:** "Las plantillas son HTML con variables {{nombre}}, {{membresia}}, etc. Al enviar, reemplazamos cada variable con los datos reales del cliente e inscripción."

---

## 🗄️ BASE DE DATOS

### Migraciones Importantes:

```
database/migrations/

CLIENTES:
2024_xx_create_clientes_table.php
├── id
├── uuid
├── run_pasaporte (único, acepta RUT o pasaporte)
├── nombres
├── apellido_paterno
├── apellido_materno (nullable)
├── celular
├── email (nullable, único)
├── direccion
├── fecha_nacimiento
├── contacto_emergencia
├── telefono_emergencia
├── id_convenio (FK nullable)
├── observaciones
├── activo (boolean)
├── deleted_at (soft delete)
├── es_menor_edad (boolean)
├── consentimiento_apoderado
├── apoderado_nombre
├── apodeado_rut
├── apoderado_email
├── apoderado_telefono
├── apoderado_parentesco
└── timestamps

NOTA: NO existe tabla tutores_legales separada, 
los datos del apoderado están en la misma tabla clientes

MEMBRESÍAS:
2024_xx_create_membresias_table.php
├── id
├── uuid
├── nombre
├── duracion_meses
├── duracion_dias
├── max_pausas
├── descripcion
├── activo (boolean)
├── deleted_at
└── timestamps

PRECIOS DE MEMBRESÍAS (tabla separada):
2024_xx_create_precios_membresias_table.php
├── id
├── id_membresia (FK)
├── precio_normal
├── precio_convenio (nullable)
├── fecha_vigencia_desde
├── fecha_vigencia_hasta (nullable = vigente)
├── activo (boolean)
└── timestamps

HISTORIAL DE PRECIOS:
2024_xx_create_historial_precios_table.php
├── id
├── id_precio_membresia (FK)
├── precio_normal_anterior
├── precio_normal_nuevo
├── precio_convenio_anterior
├── precio_convenio_nuevo
├── fecha_cambio
├── id_usuario
└── timestamps

INSCRIPCIONES:
2024_xx_create_inscripciones_table.php
├── id
├── uuid
├── id_cliente (FK)
├── id_membresia (FK)
├── id_convenio (FK nullable)
├── id_precio_acordado (FK a precios_membresias)
├── fecha_inscripcion
├── fecha_inicio
├── fecha_vencimiento
├── precio_base
├── descuento_aplicado
├── precio_final
├── id_motivo_descuento (FK nullable)
├── id_estado (100-106) referencia a tabla estados
├── observaciones
├── deleted_at
├── pausada (boolean)
├── dias_pausa
├── fecha_pausa_inicio
├── fecha_pausa_fin
├── pausas_realizadas
├── max_pausas_permitidas
├── es_cambio_plan (boolean)
├── id_inscripcion_anterior (FK para traspasos)
├── tipo_cambio (upgrade/downgrade)
├── credito_plan_anterior
└── timestamps

PAGOS (incluye pagos parciales/cuotas):
2024_xx_create_pagos_table.php
├── id
├── uuid
├── grupo_pago (UUID para agrupar cuotas)
├── id_inscripcion (FK)
├── id_cliente (FK)
├── monto_abonado
├── monto_pendiente
├── id_motivo_descuento (FK nullable)
├── fecha_pago
├── id_metodo_pago (FK)
├── id_metodo_pago2 (FK nullable, para pagos combinados)
├── monto_metodo1
├── monto_metodo2
├── referencia_pago
├── id_estado (200-205)
├── cantidad_cuotas (total de cuotas)
├── numero_cuota (1, 2, 3...)
├── monto_cuota
├── fecha_vencimiento_cuota
├── observaciones
├── deleted_at
└── timestamps

NOTA: NO existe tabla pagos_parciales separada.
Los pagos en cuotas se manejan aquí con cantidad_cuotas y numero_cuota.

NOTIFICACIONES:
2024_xx_create_notificaciones_table.php
├── id
├── cliente_id (FK)
├── inscripcion_id (FK, nullable)
├── tipo_notificacion_id (FK)
├── asunto
├── mensaje_adicional
├── programado_para
├── fecha_enviado
├── estado_id (600-602)
├── intentos
├── resend_id
└── timestamps
```

### Relaciones Importantes:

```php
// Cliente.php
public function inscripciones()
{
    return $this->hasMany(Inscripcion::class);
}

public function convenio()
{
    return $this->belongsTo(Convenio::class, 'id_convenio');
}

public function notificaciones()
{
    return $this->hasMany(Notificacion::class);
}

// Inscripcion.php
public function cliente()
{
    return $this->belongsTo(Cliente::class, 'id_cliente');
}

public function membresia()
{
    return $this->belongsTo(Membresia::class, 'id_membresia');
}

public function precioAcordado()
{
    return $this->belongsTo(PrecioMembresia::class, 'id_precio_acordado');
}

public function convenio()
{
    return $this->belongsTo(Convenio::class, 'id_convenio');
}

public function pagos()
{
    return $this->hasMany(Pago::class, 'id_inscripcion');
}

public function notificaciones()
{
    return $this->hasMany(Notificacion::class);
}

// Membresia.php
public function inscripciones()
{
    return $this->hasMany(Inscripcion::class, 'id_membresia');
}

public function precios()
{
    return $this->hasMany(PrecioMembresia::class, 'id_membresia');
}

// PrecioMembresia.php
public function membresia()
{
    return $this->belongsTo(Membresia::class, 'id_membresia');
}

public function historialPrecios()
{
    return $this->hasMany(HistorialPrecio::class, 'id_precio_membresia');
}
```

---

## 🎯 CONCEPTOS CLAVE PARA DEFENDER

### 1. **¿Por qué Laravel?**
```
✅ Framework PHP moderno
✅ MVC bien estructurado
✅ ORM (Eloquent) potente
✅ Migraciones para BD
✅ Sistema de autenticación incluido
✅ Gran comunidad y documentación
```

### 2. **¿Qué es MVC?**
```
MODELO → Representa los datos (BD)
VISTA → Lo que ve el usuario (HTML)
CONTROLADOR → Lógica de negocio

Ejemplo:
Usuario → Ruta → Controlador → Modelo → Base de Datos
                                       ↓
Usuario ← Vista ← Controlador ← Modelo ← Base de Datos
```

### 3. **¿Qué es un ORM?**
```
Object-Relational Mapping

En lugar de:
SELECT * FROM clientes WHERE id = 1

Usamos:
$cliente = Cliente::find(1);

Ventajas:
✅ Más legible
✅ Menos errores
✅ Independiente de BD
✅ Validaciones automáticas
```

### 4. **¿Qué son las Migraciones?**
```
Control de versiones para la base de datos

En lugar de ejecutar SQL manualmente:
php artisan migrate

Ventajas:
✅ Historial de cambios
✅ Rollback fácil
✅ Entorno consistente
✅ Trabajo en equipo
```

### 5. **¿Qué es Soft Delete?**
```
Borrado lógico en lugar de físico

Registro NO se elimina de la BD
Solo se marca con deleted_at = fecha

Ventajas:
✅ Recuperación fácil
✅ Mantiene historial
✅ Auditoría completa
✅ Integridad referencial
```

### 6. **¿Qué son los Enums?**
```
Constantes con tipo fuerte

En lugar de:
$estado = 'activo'; // puede tener typos

Usamos:
$estado = EstadoInscripcion::ACTIVA->value; // 101

Ventajas:
✅ Sin errores de tipeo
✅ Autocompletado IDE
✅ Documentación implícita
✅ Validación de tipos
```

### 7. **¿Qué son las Transacciones?**
```
Todo o nada en la base de datos

DB::beginTransaction();
try {
    // Operación 1
    // Operación 2
    // Operación 3
    DB::commit(); // ✅ Todo bien
} catch {
    DB::rollBack(); // ❌ Deshacer todo
}

Ejemplo práctico:
- Crear inscripción
- Crear pago
- Enviar notificación

Si falla cualquiera → Se deshace TODO
```

---

## ⚡ RESPUESTAS RÁPIDAS A PREGUNTAS COMUNES

### "¿Cómo validaste el RUT?"
> "Usamos el algoritmo módulo 11, que es el estándar chileno. Multiplicamos cada dígito por 2,3,4,5,6,7 repetidamente, sumamos, dividimos por 11 y verificamos el resto contra el dígito verificador."

### "¿Cómo manejan los estados?"
> "Usamos Enums de PHP 8.1. Cada estado tiene un número (100, 101, etc.) y métodos para obtener su label y color. Es más seguro que usar strings y evita errores de tipeo."

### "¿Cómo funciona el doble precio?"
> "La membresía tiene precio_normal y precio_convenio. Al inscribir, el usuario elige cuál aplica. Cada cambio de precio se guarda en historial_precio_membresias para auditoría."

### "¿Cómo funciona el soft delete?"
> "Usamos el trait SoftDeletes de Laravel. No borramos el registro, solo marcamos deleted_at. Podemos recuperarlo después con restore() y mantiene la integridad referencial."

### "¿Cómo calculan el traspaso?"
> "Calculamos días restantes × precio por día = valor proporcional. Si la nueva membresía es más cara, paga la diferencia. Si es más barata, ese valor se descuenta de la nueva."

### "¿Cómo evitan spam?"
> "Tres límites: máximo 3 notificaciones por cliente al día, intervalo mínimo de 2 horas, y no duplicar la misma notificación en 24 horas. Todo validado antes de enviar."

### "¿Cómo garantizan que todo funcione?"
> "Usamos transacciones de base de datos. Si algo falla, todo se revierte automáticamente. Por ejemplo, al crear inscripción + pago, si el pago falla, la inscripción tampoco se crea."

### "¿Por qué eligieron Resend?"
> "Es una API moderna, simple de usar, con buen free tier, y específicamente diseñada para notificaciones transaccionales. Tiene mejor deliverability que Gmail."

### "¿Tienen APIs REST implementadas?"
> "Sí, tenemos APIs REST internas para búsquedas AJAX, dashboard en tiempo real, validación de RUT, cálculos de precios, y operaciones sobre inscripciones y pagos. Retornan JSON para consumo desde JavaScript."

---

## 🌐 APIs REST INTERNAS

El sistema tiene **APIs REST** para operaciones AJAX y datos en tiempo real:

### 📍 Rutas API (routes/web.php)
```
Prefijo: /api/ (requiere autenticación)

CONTROLADORES:
├── app/Http/Controllers/Api/ClienteApiController.php
├── app/Http/Controllers/Api/SearchApiController.php
├── app/Http/Controllers/Api/DashboardApiController.php
├── app/Http/Controllers/Api/MembresiaApiController.php
├── app/Http/Controllers/Api/InscripcionApiController.php
├── app/Http/Controllers/Api/PausaApiController.php
└── app/Http/Controllers/Api/PagoApiController.php
```

### 🔍 1. **APIs de Búsqueda** (SearchApiController)

```php
// Buscar clientes (autocomplete)
GET /api/clientes/search?q=juan

Response:
[
    {
        "id": 1,
        "text": "Juan Pérez (juan@email.com)"
    }
]

// Buscar inscripciones
GET /api/inscripciones/search?q=0001234
```

**Uso:** Campos con autocomplete tipo-ahead en formularios

---

### 👤 2. **APIs de Clientes** (ClienteApiController)

```php
// Listar clientes activos
GET /api/clientes

Response:
[
    {
        "id": 1,
        "nombre_completo": "Juan Pérez",
        "run": "12345678-9",
        "email": "juan@email.com",
        "celular": "987654321",
        "inscripciones_activas": 1
    }
]

// Ver cliente específico
GET /api/clientes/{id}

// Estadísticas del cliente
GET /api/clientes/{id}/stats

// Validar RUT
POST /api/clientes/validar-rut
Body: { "rut": "12345678-9" }

Response:
{
    "valido": true,
    "mensaje": "RUT válido"
}
```

**Uso:** Lazy loading de tabla clientes, validación en tiempo real

---

### 💪 3. **APIs de Membresías** (MembresiaApiController)

```php
// Listar membresías activas
GET /api/membresias

// Buscar membresías
GET /api/membresias/search?q=mensual

// Ver membresía específica
GET /api/membresias/{id}

Response:
{
    "id": 1,
    "nombre": "Mensual",
    "duracion_dias": 30,
    "precio_normal": 25000,
    "precio_convenio": 20000
}

// Obtener descuento de convenio
GET /api/convenios/{id}/descuento
```

**Uso:** Cargar precios dinámicamente en formularios de inscripción

---

### 📝 4. **APIs de Inscripciones** (InscripcionApiController)

```php
// Calcular precio final y fecha vencimiento
POST /api/inscripciones/calcular

Body:
{
    "membresia_id": 1,
    "fecha_inicio": "2025-12-09",
    "aplica_convenio": true,
    "convenio_id": 5,
    "descuento_manual": 0
}

Response:
{
    "precio_base": 25000,
    "descuento_convenio": 5000,
    "descuento_manual": 0,
    "precio_final": 20000,
    "fecha_vencimiento": "2026-01-08",
    "duracion_dias": 30
}
```

**Uso:** Calcular en tiempo real mientras usuario llena formulario

---

### ⏸️ 5. **APIs de Pausas** (PausaApiController)

```php
// Pausar inscripción
POST /api/pausas/{id}/pausar
Body: { "dias_pausa": 14 }

// Reanudar inscripción
POST /api/pausas/{id}/reanudar

// Ver info de pausa
GET /api/pausas/{id}/info

// Verificar pausas expiradas (CRON)
POST /api/pausas/verificar-expiradas
```

**Uso:** Operaciones AJAX sin recargar página

---

### 💳 6. **APIs de Pagos** (PagoApiController)

```php
// Crear pago
POST /api/pagos
Body: {
    "inscripcion_id": 1,
    "monto_abonado": 25000,
    "metodo_pago_id": 1
}

// Ver pago específico
GET /api/pagos/{id}

// Actualizar pago
PUT /api/pagos/{id}

// Eliminar pago
DELETE /api/pagos/{id}

// Obtener saldo pendiente
GET /api/inscripciones/{id}/saldo

Response:
{
    "total_pagar": 25000,
    "total_pagado": 10000,
    "saldo_pendiente": 15000,
    "porcentaje_pagado": 40
}

// Calcular cuotas
POST /api/pagos/calcular-cuotas
Body: {
    "monto_total": 25000,
    "numero_cuotas": 5
}

Response:
{
    "numero_cuotas": 5,
    "monto_por_cuota": 5000,
    "total": 25000
}
```

**Uso:** Gestión de pagos parciales y cálculos en formularios

---

### 📊 7. **APIs de Dashboard** (DashboardApiController)

```php
// Estadísticas generales
GET /api/dashboard/stats

Response:
{
    "clientes_activos": 150,
    "inscripciones_activas": 120,
    "ingresos_mes": 3500000,
    "pagos_pendientes": 450000
}

// Ingresos por mes
GET /api/dashboard/ingresos-mes

// Inscripciones por estado
GET /api/dashboard/inscripciones-estado

// Membresías populares
GET /api/dashboard/membresias-populares

// Métodos de pago más usados
GET /api/dashboard/metodos-pago

// Últimos pagos
GET /api/dashboard/ultimos-pagos

// Próximas a vencer
GET /api/dashboard/proximas-vencer

// Resumen de clientes
GET /api/dashboard/resumen-clientes
```

**Uso:** Dashboard con gráficos en tiempo real

---

### 🔐 Autenticación

Todas las APIs requieren:
```
✅ Usuario autenticado (middleware: auth)
✅ Sesión activa
✅ CSRF token (para POST/PUT/DELETE)
```

### 📤 Formato de Respuesta

Todas las APIs retornan JSON:
```php
// Éxito
{
    "data": { ... },
    "message": "Operación exitosa"
}

// Error
{
    "error": "Mensaje de error",
    "code": 400
}
```

### 💡 Ejemplo de Uso en JavaScript

```javascript
// Buscar cliente (autocomplete)
fetch('/api/clientes/search?q=juan')
    .then(res => res.json())
    .then(data => {
        console.log(data); // Array de clientes
    });

// Calcular precio inscripción
fetch('/api/inscripciones/calcular', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        membresia_id: 1,
        fecha_inicio: '2025-12-09',
        aplica_convenio: true
    })
})
.then(res => res.json())
.then(data => {
    document.getElementById('precio').value = data.precio_final;
    document.getElementById('fecha_vencimiento').value = data.fecha_vencimiento;
});

// Validar RUT en tiempo real
document.getElementById('rut').addEventListener('blur', async function() {
    const response = await fetch('/api/clientes/validar-rut', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ rut: this.value })
    });
    
    const data = await response.json();
    
    if (!data.valido) {
        alert('RUT inválido');
    }
});
```

---

## 📧 API EXTERNA: RESEND

### ¿Qué es Resend?

**Resend** es el servicio externo que usamos para enviar emails transaccionales (notificaciones, bienvenida, recordatorios).

```
Sitio: https://resend.com
Librería: resend/resend-php
Config: config/mail.php
```

### 🔑 Configuración

```php
// .env
RESEND_API_KEY=re_xxxxxxxxxxxxx
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="PROGYM"

// config/mail.php
'mailers' => [
    'resend' => [
        'transport' => 'resend',
    ],
],
```

### 📤 Uso en NotificacionService.php

```php
use Resend\Laravel\Facades\Resend;

// Enviar email
$resultado = Resend::emails()->send([
    'from' => 'PROGYM <onboarding@resend.dev>',
    'to' => ['cliente@email.com'],
    'subject' => 'Bienvenido a PROGYM',
    'html' => $contenido_html
]);

// Respuesta
{
    "id": "re_xxxxx",  // ID único de Resend
    "status": "sent"
}
```

### 🔍 Tracking de Emails

```php
// Guardamos el ID de Resend en BD
Notificacion::update([
    'resend_id' => $resultado['id'],
    'estado_id' => 601 // Enviada
]);

// Logs completos
LogNotificacion::create([
    'notificacion_id' => $notificacion->id,
    'accion' => 'enviada',
    'resultado' => 'exitoso',
    'resend_id' => $resultado['id'],
    'fecha' => now()
]);
```

### ⚠️ Manejo de Errores

```php
try {
    $resultado = Resend::emails()->send([...]);
    
} catch (\Resend\Exceptions\ErrorException $e) {
    // Errores de Resend
    // - API key inválida
    // - Rate limit excedido
    // - Email inválido
    // - Dominio no verificado
    
    LogNotificacion::create([
        'notificacion_id' => $notificacion->id,
        'accion' => 'intento_envio',
        'resultado' => 'fallido',
        'error' => $e->getMessage(),
        'codigo_error' => $e->getCode()
    ]);
    
    // Reintentar hasta 3 veces
    if ($notificacion->intentos < 3) {
        $notificacion->increment('intentos');
    } else {
        $notificacion->update(['estado_id' => 602]); // Fallida
    }
}
```

### 📊 Límites de Resend (Plan Gratuito)

```
✅ 100 emails/día
✅ 3,000 emails/mes
✅ 1 dominio verificado
✅ API completa
✅ Logs básicos

Plan Pagado:
💰 $20/mes → 50,000 emails
💰 $80/mes → 100,000 emails
```

### 🔐 Seguridad

```php
// NUNCA exponer la API key
// ✅ Usar .env
RESEND_API_KEY=re_xxxxx

// ❌ NO hardcodear
$api_key = "re_xxxxx"; // MAL

// ✅ Validar emails antes de enviar
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new \Exception('Email inválido');
}

// ✅ Rate limiting interno
if ($emails_hoy >= 100) {
    throw new \Exception('Límite diario alcanzado');
}
```

### 🎯 Ventajas de Resend vs Otros

```
✅ API simple y moderna
✅ Mejor deliverability que Gmail/SMTP
✅ SDK oficial para Laravel
✅ Logs detallados en dashboard
✅ Webhooks para eventos (bounce, delivered, opened)
✅ Testing con emails reales
✅ Dominio de prueba incluido (onboarding@resend.dev)
```

### 📝 Ejemplo Completo de Envío

```php
// NotificacionService.php método enviarNotificacion()
public function enviarNotificacion($notificacion)
{
    try {
        // 1. VALIDAR cliente activo
        if ($notificacion->cliente->estado_id != 1) {
            throw new \Exception('Cliente inactivo');
        }
        
        // 2. VALIDAR límites anti-spam
        if (!$this->puedeEnviar($notificacion->cliente_id)) {
            throw new \Exception('Límite de envíos alcanzado');
        }
        
        // 3. OBTENER plantilla HTML
        $plantilla = $notificacion->tipoNotificacion;
        
        // 4. RENDERIZAR con datos del cliente
        $contenido = $this->renderizarPlantilla(
            $plantilla->html_template,
            $notificacion->cliente,
            $notificacion->inscripcion
        );
        
        // 5. ENVIAR vía Resend
        $resultado = Resend::emails()->send([
            'from' => config('mail.from.address'),
            'to' => [$notificacion->cliente->email],
            'subject' => $notificacion->asunto,
            'html' => $contenido,
            'tags' => [
                'tipo' => $notificacion->tipoNotificacion->nombre,
                'cliente_id' => $notificacion->cliente_id
            ]
        ]);
        
        // 6. ACTUALIZAR notificación
        $notificacion->update([
            'estado_id' => 601, // Enviada
            'fecha_enviado' => now(),
            'resend_id' => $resultado['id']
        ]);
        
        // 7. LOG exitoso
        LogNotificacion::create([
            'notificacion_id' => $notificacion->id,
            'accion' => 'enviada',
            'resultado' => 'exitoso',
            'resend_id' => $resultado['id'],
            'detalles' => json_encode($resultado)
        ]);
        
        return ['exito' => true, 'resend_id' => $resultado['id']];
        
    } catch (\Resend\Exceptions\ErrorException $e) {
        // Error de Resend
        $this->logError($notificacion, $e);
        return ['exito' => false, 'error' => $e->getMessage()];
        
    } catch (\Exception $e) {
        // Error general
        $this->logError($notificacion, $e);
        return ['exito' => false, 'error' => $e->getMessage()];
    }
}
```

### 🧪 Testing sin Consumir Cuota

```php
// Usar dominio de prueba
'to' => ['delivered@resend.dev'],  // ✅ Siempre exitoso
'to' => ['bounced@resend.dev'],    // ❌ Siempre falla
'to' => ['complained@resend.dev'], // ⚠️ Marca como spam
```

### 🔄 Webhooks (Futuro)

```php
// Resend puede notificar eventos:
POST /api/webhooks/resend

{
    "type": "email.delivered",
    "data": {
        "email_id": "re_xxxxx",
        "to": "cliente@email.com",
        "subject": "Bienvenido",
        "created_at": "2025-12-09T10:00:00Z"
    }
}

// Actualizar estado en BD
public function webhook(Request $request)
{
    $evento = $request->input('type');
    $resend_id = $request->input('data.email_id');
    
    $notificacion = Notificacion::where('resend_id', $resend_id)->first();
    
    match($evento) {
        'email.delivered' => $notificacion->update(['entregado' => true]),
        'email.bounced' => $notificacion->update(['rebotado' => true]),
        'email.opened' => $notificacion->increment('aperturas'),
        'email.clicked' => $notificacion->increment('clics'),
        default => null
    };
}
```

### 💡 Preguntas Frecuentes

**¿Por qué Resend y no Gmail?**
> Gmail tiene límites muy bajos (500/día) y puede marcar como spam. Resend está diseñado específicamente para emails transaccionales con mejor deliverability.

**¿Qué pasa si se acaba la cuota?**
> Las notificaciones quedan pendientes en BD. Cuando renueva la cuota (nuevo mes o upgrade), el CRON las envía automáticamente.

**¿Se pueden enviar adjuntos?**
> Sí, Resend soporta attachments, pero por ahora solo usamos HTML para notificaciones simples.

**¿Cómo verificar que llegó el email?**
> En el dashboard de Resend podemos ver logs completos: enviados, entregados, abiertos, clics. Cada email tiene su `resend_id` único.

---

## 📚 ARCHIVOS A REVISAR (Orden de prioridad)

### ⭐⭐⭐ CRÍTICOS (Estudiar sí o sí):
1. `app/Http/Controllers/Admin/ClienteController.php` → CRUD completo
2. `app/Http/Controllers/Admin/InscripcionController.php` → Lógica compleja
3. `app/Services/NotificacionService.php` → Automatización
4. `app/Models/Inscripcion.php` → Relaciones

### ⭐⭐ IMPORTANTES (Si tienes tiempo):
5. `app/Enums/EstadoInscripcion.php` → Estados
6. `app/Models/Cliente.php` → Modelo base
7. `app/Console/Commands/EnviarNotificaciones.php` → CRON
8. `routes/web.php` → Rutas del sistema

### ⭐ OPCIONALES (Si sobra tiempo):
9. `resources/views/admin/inscripciones/show.blade.php` → Vista completa
10. `database/migrations/*` → Estructura BD
11. `app/Http/Controllers/Admin/PagoController.php` → Pagos
12. `config/mail.php` → Configuración emails

---

## 🎓 PLAN DE ESTUDIO (4 HORAS)

### HORA 1: RF-02 Clientes (Más simple)
```
✅ Leer ClienteController.php (métodos: index, create, store, edit, update, destroy)
✅ Ver validación de RUT
✅ Entender soft delete
✅ Ver manejo de tutores
```

### HORA 2: RF-03 Membresías + RF-04 Inscripciones (Intermedio)
```
✅ Leer MembresiaController.php (doble precio, historial)
✅ Leer InscripcionController.php (crear, pausar, reactivar)
✅ Entender Enums de estados
✅ Ver cálculo de fechas de vencimiento
```

### HORA 3: RF-04 Inscripciones (Complejo)
```
✅ Leer método traspaso() en InscripcionController
✅ Ver PagoController.php (pagos parciales)
✅ Entender transacciones DB
✅ Ver relaciones entre modelos
```

### HORA 4: RF-07 Notificaciones (Automatización)
```
✅ Leer NotificacionService.php completo
✅ Ver EnviarNotificaciones.php (comando)
✅ Entender sistema anti-spam
✅ Ver integración con Resend
```

---

## 💡 TIPS FINALES

### Si te preguntan algo que no sabes:
```
✅ "Es una funcionalidad que tenemos planificada para la siguiente fase"
✅ "Usamos el estándar de Laravel para eso"
✅ "Está implementado en el servicio X, línea Y"
✅ "Déjame verificar el código exacto" (y buscas rápido)
```

### Si te piden ver código en vivo:
```
✅ Abre el controlador principal (InscripcionController o ClienteController)
✅ Muestra un método completo (store o update)
✅ Explica línea por línea con confianza
✅ Muestra la vista correspondiente
```

### Palabras clave que suenan profesional:
```
✅ "Transacción atómica"
✅ "Integridad referencial"
✅ "Auditoría completa"
✅ "Validación server-side"
✅ "Separación de responsabilidades"
✅ "Inyección de dependencias"
✅ "ORM Eloquent"
✅ "Soft delete"
✅ "Migraciones versionadas"
```

---

**✅ Con esta guía + 4 horas de estudio → Defiendes el código con confianza**

¡Mucha suerte en tu presentación! 🚀
