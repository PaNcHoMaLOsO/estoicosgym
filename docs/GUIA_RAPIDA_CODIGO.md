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
// Línea ~15-30
class Cliente extends Model
{
    protected $fillable = [
        'rut', 'nombre', 'apellido_paterno', 'apellido_materno',
        'fecha_nacimiento', 'genero', 'direccion', 'telefono',
        'email', 'es_menor_edad', 'estado_id'
    ];
}
```
**Qué hace:** Define qué campos tiene un cliente en la BD.

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
        'rut' => 'required|unique:clientes',
        'nombre' => 'required|string|max:100',
        'email' => 'required|email|unique:clientes',
        // ... más validaciones
    ]);
    
    // 2. VALIDAR RUT específicamente
    if (!$this->validarRut($request->rut)) {
        return back()->with('error', 'RUT inválido');
    }
    
    // 3. GUARDAR en base de datos
    $cliente = Cliente::create($validated);
    
    // 4. SI ES MENOR → Crear registro de tutor
    if ($request->es_menor_edad) {
        TutorLegal::create([
            'cliente_id' => $cliente->id,
            'rut_tutor' => $request->rut_tutor,
            'nombre_tutor' => $request->nombre_tutor,
            // ...
        ]);
    }
    
    // 5. REDIRIGIR con mensaje
    return redirect()->route('admin.clientes.index')
                    ->with('success', 'Cliente creado');
}
```
**Si te preguntan:** "Primero validamos todos los datos, luego verificamos el RUT con el algoritmo, guardamos el cliente, y si es menor creamos automáticamente el registro del tutor legal."

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

#### 1. **MODELO Membresia.php** (Doble precio)
```php
// Línea ~20-40
protected $fillable = [
    'nombre',
    'descripcion',
    'duracion_dias',
    'precio_normal',      // ← PRECIO 1
    'precio_convenio',    // ← PRECIO 2
    'estado_id'
];

// Relación con historial
public function historialPrecios()
{
    return $this->hasMany(HistorialPrecioMembresia::class);
}
```

#### 2. **HISTORIAL DE PRECIOS** (Lo diferenciador)
```php
// MembresiaController.php método update() línea ~180-220
public function update(Request $request, $id)
{
    $membresia = Membresia::findOrFail($id);
    
    // SI CAMBIÓ EL PRECIO → Guardar en historial
    if ($request->precio_normal != $membresia->precio_normal ||
        $request->precio_convenio != $membresia->precio_convenio) {
        
        HistorialPrecioMembresia::create([
            'membresia_id' => $membresia->id,
            'precio_normal_anterior' => $membresia->precio_normal,
            'precio_convenio_anterior' => $membresia->precio_convenio,
            'precio_normal_nuevo' => $request->precio_normal,
            'precio_convenio_nuevo' => $request->precio_convenio,
            'fecha_cambio' => now(),
            'usuario_id' => auth()->id()
        ]);
    }
    
    // Actualizar membresía
    $membresia->update($request->all());
    
    return redirect()->route('admin.membresias.index')
                    ->with('success', 'Membresía actualizada');
}
```
**Si te preguntan:** "Cada vez que cambia el precio, guardamos el historial: precio anterior, nuevo, fecha y quién lo cambió. Así tenemos trazabilidad completa para auditoría."

#### 3. **DOBLE PRECIO EN VISTA**
```php
// create.blade.php línea ~40-60
<div class="row">
    <div class="col-md-6">
        <label>Precio Normal</label>
        <input type="number" name="precio_normal" 
               class="form-control" required>
    </div>
    
    <div class="col-md-6">
        <label>Precio Convenio</label>
        <input type="number" name="precio_convenio" 
               class="form-control" required>
    </div>
</div>
```

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

#### 1. **ESTADOS con ENUM** (Lo más importante)
```php
// app/Enums/EstadoInscripcion.php
namespace App\Enums;

enum EstadoInscripcion: int
{
    case PENDIENTE = 100;
    case ACTIVA = 101;
    case PAUSADA = 102;
    case VENCIDA = 103;
    case CANCELADA = 104;
    
    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::ACTIVA => 'Activa',
            self::PAUSADA => 'Pausada',
            self::VENCIDA => 'Vencida',
            self::CANCELADA => 'Cancelada',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::PENDIENTE => 'warning',
            self::ACTIVA => 'success',
            self::PAUSADA => 'info',
            self::VENCIDA => 'danger',
            self::CANCELADA => 'dark',
        };
    }
}
```
**Si te preguntan:** "Usamos Enums de PHP 8.1 para los estados. Cada número tiene un significado (100=Pendiente, 101=Activa, etc.). Es más seguro que usar strings y evita errores de tipeo."

#### 2. **CREAR INSCRIPCIÓN** (Flujo completo)
```php
// InscripcionController.php método store() línea ~200-280
public function store(Request $request)
{
    DB::beginTransaction();
    
    try {
        // 1. CREAR INSCRIPCIÓN
        $inscripcion = Inscripcion::create([
            'codigo' => $this->generarCodigo(),
            'cliente_id' => $request->cliente_id,
            'membresia_id' => $request->membresia_id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_vencimiento' => $this->calcularVencimiento(
                $request->fecha_inicio, 
                $request->duracion_dias
            ),
            'precio_membresia' => $request->precio,
            'descuento' => $request->descuento ?? 0,
            'total_pagar' => $request->precio - ($request->descuento ?? 0),
            'estado_id' => EstadoInscripcion::PENDIENTE->value
        ]);
        
        // 2. CREAR PAGO (si paga algo)
        if ($request->monto_pagado > 0) {
            Pago::create([
                'inscripcion_id' => $inscripcion->id,
                'monto' => $request->monto_pagado,
                'metodo_pago_id' => $request->metodo_pago_id,
                'fecha_pago' => now(),
                'estado_id' => EstadoPago::COMPLETADO->value
            ]);
            
            // SI PAGÓ TODO → Activar inscripción
            if ($request->monto_pagado >= $inscripcion->total_pagar) {
                $inscripcion->update([
                    'estado_id' => EstadoInscripcion::ACTIVA->value
                ]);
            }
        }
        
        // 3. ENVIAR NOTIFICACIÓN
        event(new InscripcionCreada($inscripcion));
        
        DB::commit();
        
        return redirect()->route('admin.inscripciones.show', $inscripcion)
                        ->with('success', 'Inscripción creada');
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}
```
**Si te preguntan:** "Usamos transacciones de base de datos. Si algo falla, todo se revierte automáticamente. Creamos la inscripción, el pago, y si pagó todo la activamos. Al final disparamos un evento para la notificación automática."

#### 3. **PAUSAR INSCRIPCIÓN** (Lógica de negocio)
```php
// InscripcionController.php método pausar() línea ~400-450
public function pausar(Request $request, $id)
{
    $inscripcion = Inscripcion::findOrFail($id);
    
    // VALIDAR: Solo se puede pausar si está Activa
    if ($inscripcion->estado_id != EstadoInscripcion::ACTIVA->value) {
        return back()->with('error', 'Solo se pueden pausar inscripciones activas');
    }
    
    // VALIDAR: Debe pagar multa (10% del total)
    $multa = $inscripcion->total_pagar * 0.10;
    
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
        'estado_id' => EstadoInscripcion::PAUSADA->value,
        'fecha_pausa' => now(),
        'dias_pausa' => $request->dias_pausa,
        'fecha_vencimiento' => $nueva_fecha_vencimiento
    ]);
    
    // REGISTRAR PAGO DE MULTA
    Pago::create([
        'inscripcion_id' => $inscripcion->id,
        'monto' => $request->pago_multa,
        'metodo_pago_id' => $request->metodo_pago_id,
        'tipo' => 'multa_pausa',
        'fecha_pago' => now()
    ]);
    
    return back()->with('success', 'Inscripción pausada');
}
```
**Si te preguntan:** "La pausa requiere pagar una multa del 10%. Calculamos cuántos días le quedan, sumamos los días de pausa, y actualizamos la fecha de vencimiento. Todo queda registrado en el historial."

#### 4. **PAGOS PARCIALES** (Lo complejo)
```php
// PagoController.php método registrarParcial() línea ~150-200
public function registrarParcial(Request $request)
{
    $inscripcion = Inscripcion::findOrFail($request->inscripcion_id);
    
    // CALCULAR saldo pendiente
    $total_pagado = $inscripcion->pagos()->sum('monto');
    $saldo_pendiente = $inscripcion->total_pagar - $total_pagado;
    
    // VALIDAR monto
    if ($request->monto > $saldo_pendiente) {
        return back()->with('error', 'Monto excede saldo pendiente');
    }
    
    // CREAR pago parcial
    $pago = PagoParcial::create([
        'inscripcion_id' => $inscripcion->id,
        'monto' => $request->monto,
        'metodo_pago_id' => $request->metodo_pago_id,
        'fecha_pago' => now(),
        'numero_cuota' => $this->siguienteNumeroCuota($inscripcion),
        'estado_id' => EstadoPago::COMPLETADO->value
    ]);
    
    // VERIFICAR si completó el pago
    $nuevo_total_pagado = $total_pagado + $request->monto;
    
    if ($nuevo_total_pagado >= $inscripcion->total_pagar) {
        // ACTIVAR inscripción
        $inscripcion->update([
            'estado_id' => EstadoInscripcion::ACTIVA->value
        ]);
        
        // Crear pago completo en tabla pagos
        Pago::create([
            'inscripcion_id' => $inscripcion->id,
            'monto' => $inscripcion->total_pagar,
            'estado_id' => EstadoPago::COMPLETADO->value,
            'completado_con_parciales' => true
        ]);
    }
    
    return back()->with('success', "Pago parcial registrado. Saldo: " . 
                        ($saldo_pendiente - $request->monto));
}
```
**Si te preguntan:** "Permitimos pagar en cuotas. Cada pago se registra con su número de cuota. Cuando la suma de todos los pagos parciales alcanza el total, activamos automáticamente la inscripción."

#### 5. **TRASPASOS** (Lo más complejo)
```php
// InscripcionController.php método traspaso() línea ~550-650
public function traspaso(Request $request)
{
    DB::beginTransaction();
    
    try {
        $inscripcion_origen = Inscripcion::findOrFail($request->inscripcion_id);
        
        // VALIDAR: Debe estar activa y paga
        if ($inscripcion_origen->estado_id != EstadoInscripcion::ACTIVA->value) {
            throw new \Exception('Inscripción debe estar activa');
        }
        
        if ($inscripcion_origen->saldo_pendiente > 0) {
            throw new \Exception('Debe estar completamente pagada');
        }
        
        // CALCULAR días restantes
        $dias_restantes = now()->diffInDays($inscripcion_origen->fecha_vencimiento);
        
        // CALCULAR proporcional a pagar
        $membresia_nueva = Membresia::findOrFail($request->membresia_nueva_id);
        $precio_dia_anterior = $inscripcion_origen->total_pagar / 
                               $inscripcion_origen->membresia->duracion_dias;
        $valor_dias_restantes = $precio_dia_anterior * $dias_restantes;
        $diferencia_pagar = $membresia_nueva->precio_normal - $valor_dias_restantes;
        
        // CANCELAR inscripción anterior
        $inscripcion_origen->update([
            'estado_id' => EstadoInscripcion::CANCELADA->value,
            'motivo_cancelacion' => 'Traspaso a nueva membresía',
            'fecha_cancelacion' => now()
        ]);
        
        // CREAR nueva inscripción
        $inscripcion_nueva = Inscripcion::create([
            'codigo' => $this->generarCodigo(),
            'cliente_id' => $inscripcion_origen->cliente_id,
            'membresia_id' => $request->membresia_nueva_id,
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays($membresia_nueva->duracion_dias),
            'precio_membresia' => $membresia_nueva->precio_normal,
            'descuento' => $valor_dias_restantes,
            'total_pagar' => $diferencia_pagar,
            'estado_id' => EstadoInscripcion::PENDIENTE->value,
            'inscripcion_origen_id' => $inscripcion_origen->id // ← Referencia
        ]);
        
        // SI DEBE PAGAR DIFERENCIA
        if ($diferencia_pagar > 0) {
            // Usuario debe pagar
            DB::commit();
            return redirect()->route('admin.inscripciones.show', $inscripcion_nueva)
                            ->with('warning', "Debe pagar diferencia: $$diferencia_pagar");
        } else {
            // No debe pagar o le sobra
            $inscripcion_nueva->update([
                'estado_id' => EstadoInscripcion::ACTIVA->value
            ]);
            
            DB::commit();
            return redirect()->route('admin.inscripciones.show', $inscripcion_nueva)
                            ->with('success', 'Traspaso exitoso');
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
├── rut (único)
├── nombre
├── apellido_paterno
├── apellido_materno
├── email (único)
├── telefono
├── fecha_nacimiento
├── es_menor_edad
├── estado_id
├── deleted_at (soft delete)
└── timestamps

TUTORES:
2024_xx_create_tutores_legales_table.php
├── id
├── cliente_id (FK)
├── rut_tutor
├── nombre_tutor
└── timestamps

MEMBRESÍAS:
2024_xx_create_membresias_table.php
├── id
├── nombre
├── duracion_dias
├── precio_normal      ← IMPORTANTE
├── precio_convenio    ← IMPORTANTE
├── estado_id
└── timestamps

HISTORIAL PRECIOS:
2024_xx_create_historial_precio_membresias_table.php
├── id
├── membresia_id (FK)
├── precio_normal_anterior
├── precio_convenio_anterior
├── precio_normal_nuevo
├── precio_convenio_nuevo
├── fecha_cambio
├── usuario_id
└── timestamps

INSCRIPCIONES:
2024_xx_create_inscripciones_table.php
├── id
├── codigo (único, ej: 0001234)
├── cliente_id (FK)
├── membresia_id (FK)
├── fecha_inicio
├── fecha_vencimiento
├── precio_membresia
├── descuento
├── total_pagar
├── estado_id (100-104)
├── inscripcion_origen_id (para traspasos)
└── timestamps

PAGOS:
2024_xx_create_pagos_table.php
├── id
├── inscripcion_id (FK)
├── monto
├── metodo_pago_id
├── fecha_pago
├── estado_id (200-202)
└── timestamps

PAGOS PARCIALES:
2024_xx_create_pagos_parciales_table.php
├── id
├── inscripcion_id (FK)
├── monto
├── numero_cuota
├── metodo_pago_id
├── fecha_pago
└── timestamps

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

public function tutorLegal()
{
    return $this->hasOne(TutorLegal::class);
}

public function notificaciones()
{
    return $this->hasMany(Notificacion::class);
}

// Inscripcion.php
public function cliente()
{
    return $this->belongsTo(Cliente::class);
}

public function membresia()
{
    return $this->belongsTo(Membresia::class);
}

public function pagos()
{
    return $this->hasMany(Pago::class);
}

public function pagosParciales()
{
    return $this->hasMany(PagoParcial::class);
}

public function notificaciones()
{
    return $this->hasMany(Notificacion::class);
}

// Membresia.php
public function inscripciones()
{
    return $this->hasMany(Inscripcion::class);
}

public function historialPrecios()
{
    return $this->hasMany(HistorialPrecioMembresia::class);
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
