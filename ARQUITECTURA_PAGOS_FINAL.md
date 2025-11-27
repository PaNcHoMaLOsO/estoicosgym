# 🎯 MEJOR OPCIÓN: ARQUITECTURA HÍBRIDA INTEGRAL

## 📌 TUS REQUISITOS ESPECÍFICOS

```
✅ Abonos parciales sin cuotas (se acumulan)
✅ Las cuotas OPCIONALES (solo si marca checkbox)
✅ Pagos mixtos (efectivo + tarjeta en un mismo registro)
✅ Métodos de pago simplificados
✅ Interfaz flexible para admin
✅ Preparado para API (móvil en futuro)
✅ Mantener coherencia actual
```

---

## 🏆 MEJOR OPCIÓN: ARQUITECTURA HÍBRIDA + API REST

### **3 PILARES:**

```
┌─────────────────────────────────────────────────────┐
│ 1. TABLA PAGOS (Simple pero flexible)               │
│    - Abonos simples vs cuotas (boolean)              │
│    - JSON para métodos múltiples                     │
│    - Campos opcionales para cuotas                   │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 2. MÉTODOS DE PAGO SIMPLIFICADOS                    │
│    - EFECTIVO                                       │
│    - DÉBITO/CRÉDITO (una sola opción)               │
│    - TRANSFERENCIA                                  │
│    - OTRO                                           │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 3. API REST + Interfaz Blade (Dual)                 │
│    - API para lógica pura                           │
│    - Blade para interfaz admin                      │
│    - Listo para móvil después                       │
└─────────────────────────────────────────────────────┘
```

---

## 💾 ESTRUCTURA DE BASE DE DATOS

### **Nueva tabla: metodos_pago (refactorizada)**

```sql
DROP TABLE metodos_pago; -- Limpiar anterior

CREATE TABLE metodos_pago (
    id UNSIGNED INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,  -- 'efectivo', 'tarjeta', 'transferencia', 'otro'
    nombre VARCHAR(50) NOT NULL,         -- 'Efectivo', 'Débito/Crédito', 'Transferencia', 'Otro'
    descripcion TEXT,
    requiere_referencia BOOLEAN DEFAULT FALSE,  -- Transferencia si, efectivo no
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO metodos_pago (codigo, nombre, requiere_referencia) VALUES
('efectivo', 'Efectivo', FALSE),
('tarjeta', 'Débito/Crédito', TRUE),
('transferencia', 'Transferencia', TRUE),
('otro', 'Otro', FALSE);
```

### **Tabla pagos (refactorizada final)**

```sql
CREATE TABLE pagos (
    id UNSIGNED INT PRIMARY KEY AUTO_INCREMENT,
    uuid UUID UNIQUE NOT NULL,
    id_inscripcion UNSIGNED INT NOT NULL,
    monto_abonado DECIMAL(10,2) NOT NULL COMMENT 'Lo que se pagó en este registro',
    
    -- Métodos de pago (flexible)
    id_metodo_pago_principal UNSIGNED INT,           -- Método principal
    metodos_pago_json JSON COMMENT '{"efectivo": 100, "tarjeta": 50}' DEFAULT NULL,
    referencia_pago VARCHAR(100) NULLABLE,           -- Comprobante/número transferencia
    
    -- Lógica de cuotas (OPCIONAL)
    es_plan_cuotas BOOLEAN DEFAULT FALSE,            -- ¿Es parte de un plan de cuotas?
    numero_cuota UNSIGNED TINYINT DEFAULT NULL,      -- NULL si no es cuota
    cantidad_cuotas UNSIGNED TINYINT DEFAULT NULL,   -- NULL si no es cuota
    fecha_vencimiento_cuota DATE DEFAULT NULL,       -- NULL si no es cuota
    grupo_pago UUID DEFAULT NULL,                    -- Agrupa cuotas del mismo plan
    
    -- Estado y Control
    id_estado UNSIGNED INT NOT NULL,  -- 101, 102, 103, 104
    fecha_pago DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_metodo_pago_principal) REFERENCES metodos_pago(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_estado) REFERENCES estados(id) ON DELETE RESTRICT,
    
    -- Índices
    INDEX idx_id_inscripcion (id_inscripcion),
    INDEX idx_fecha_pago (fecha_pago),
    INDEX idx_id_estado (id_estado),
    INDEX idx_es_plan_cuotas (es_plan_cuotas),
    INDEX idx_grupo_pago (grupo_pago)
);
```

---

## 🏗️ LÓGICA EN MODELS

### **Pago Model (Híbrido)**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pago extends Model {
    
    protected $table = 'pagos';
    
    protected $fillable = [
        'uuid',
        'id_inscripcion',
        'monto_abonado',
        'id_metodo_pago_principal',
        'metodos_pago_json',
        'referencia_pago',
        'es_plan_cuotas',
        'numero_cuota',
        'cantidad_cuotas',
        'fecha_vencimiento_cuota',
        'grupo_pago',
        'id_estado',
        'fecha_pago',
        'observaciones',
    ];
    
    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_vencimiento_cuota' => 'date',
        'es_plan_cuotas' => 'boolean',
        'metodos_pago_json' => 'array',
    ];
    
    protected static function boot() {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }
    
    // RELACIONES
    public function inscripcion() {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }
    
    public function metodoPagoPrincipal() {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago_principal');
    }
    
    public function estado() {
        return $this->belongsTo(Estado::class, 'id_estado');
    }
    
    // ===== MÉTODOS PARA ABONOS SIMPLES =====
    
    /**
     * Obtener saldo pendiente de la inscripción
     */
    public function getSaldoPendiente() {
        $totalAbonado = $this->inscripcion->pagos()
            ->whereIn('id_estado', [102, 103]) // Pagado o Parcial
            ->sum('monto_abonado');
        
        return max(0, $this->inscripcion->precio_final - $totalAbonado);
    }
    
    /**
     * Obtener total abonado hasta ahora
     */
    public function getTotalAbonado() {
        return $this->inscripcion->pagos()
            ->whereIn('id_estado', [102, 103])
            ->sum('monto_abonado');
    }
    
    // ===== MÉTODOS PARA CUOTAS =====
    
    /**
     * ¿Este pago es parte de un plan de cuotas?
     */
    public function esParteDeCuotas() {
        return $this->es_plan_cuotas;
    }
    
    /**
     * Obtener todas las cuotas relacionadas
     */
    public function cuotasRelacionadas() {
        if (!$this->grupo_pago) {
            return collect([]);
        }
        
        return self::where('grupo_pago', $this->grupo_pago)
            ->orderBy('numero_cuota')
            ->get();
    }
    
    /**
     * ¿Es la última cuota?
     */
    public function esUltimaCuota() {
        if (!$this->esParteDeCuotas()) return false;
        return $this->numero_cuota >= $this->cantidad_cuotas;
    }
    
    /**
     * ¿Es una cuota válida?
     */
    public function esNumeroCuotaValido() {
        if (!$this->esParteDeCuotas()) return true;
        return $this->numero_cuota > 0 && $this->numero_cuota <= $this->cantidad_cuotas;
    }
    
    // ===== MÉTODOS PARA PAGOS MIXTOS =====
    
    /**
     * ¿Es pago mixto (múltiples métodos)?
     */
    public function esPagoMixto() {
        return $this->metodos_pago_json && count($this->metodos_pago_json) > 1;
    }
    
    /**
     * Obtener desglose de métodos de pago
     */
    public function obtenerDesglose() {
        if (!$this->metodos_pago_json) {
            return [
                $this->metodoPagoPrincipal->codigo => $this->monto_abonado,
            ];
        }
        return $this->metodos_pago_json;
    }
    
    // ===== CÁLCULO DE ESTADO =====
    
    /**
     * Calcular estado dinámico
     * 101: PENDIENTE
     * 102: PAGADO
     * 103: PARCIAL
     * 104: VENCIDO
     */
    public function calcularEstadoDinamico() {
        $saldoPendiente = $this->getSaldoPendiente();
        
        // Si todo está pagado
        if ($saldoPendiente <= 0) {
            return 102; // PAGADO
        }
        
        // Si es cuota vencida
        if ($this->esParteDeCuotas() && 
            $this->fecha_vencimiento_cuota && 
            $this->fecha_vencimiento_cuota->isPast()) {
            return 104; // VENCIDO
        }
        
        // Si hay algo abonado (parcial) o si es primer abono
        if ($this->monto_abonado > 0 || $this->getTotalAbonado() > 0) {
            return 103; // PARCIAL
        }
        
        return 101; // PENDIENTE
    }
}
```

### **Inscripcion Model (Helpers)**

```php
public function getSaldoPendiente() {
    $totalAbonado = $this->pagos()
        ->whereIn('id_estado', [102, 103])
        ->sum('monto_abonado');
    
    return max(0, $this->precio_final - $totalAbonado);
}

public function estaPagada() {
    return $this->getSaldoPendiente() <= 0;
}

public function getTotalAbonado() {
    return $this->pagos()
        ->whereIn('id_estado', [102, 103])
        ->sum('monto_abonado');
}

public function getDetalleAbonos() {
    return [
        'precio_final' => $this->precio_final,
        'total_abonado' => $this->getTotalAbonado(),
        'saldo_pendiente' => $this->getSaldoPendiente(),
        'porcentaje_pagado' => ($this->precio_final > 0) 
            ? round(($this->getTotalAbonado() / $this->precio_final) * 100, 2) 
            : 0,
        'estado' => $this->estaPagada() ? 'Pagada' : 'Pendiente',
    ];
}
```

---

## 🔌 API REST (PagoApiController)

```php
namespace App\Http\Controllers\Api;

use App\Models\Pago;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagoApiController extends Controller {
    
    /**
     * POST /api/pagos
     * Crear un pago simple o marcar inicio de cuotas
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            'referencia_pago' => 'nullable|string|max:100',
            
            // Métodos múltiples (para pagos mixtos)
            'metodos_pago_json' => 'nullable|array',
            'metodos_pago_json.*' => 'numeric|min:0',
            
            // Cuotas (opcionales)
            'es_plan_cuotas' => 'boolean|default:false',
            'cantidad_cuotas' => 'required_if:es_plan_cuotas,true|integer|min:2|max:12',
            'fecha_vencimiento_cuota' => 'nullable|date|after:today',
        ]);
        
        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        
        // VALIDACIÓN: No sobrepasar saldo
        $saldo = $inscripcion->getSaldoPendiente();
        if ($validated['monto_abonado'] > $saldo) {
            return response()->json([
                'error' => 'Monto excede saldo pendiente',
                'saldo_pendiente' => $saldo,
                'monto_solicitado' => $validated['monto_abonado'],
            ], 422);
        }
        
        // VALIDACIÓN: Inscripción activa
        if ($inscripcion->id_estado != 1) {
            return response()->json([
                'error' => 'Inscripción no está activa',
            ], 422);
        }
        
        try {
            if ($validated['es_plan_cuotas']) {
                // Crear plan de cuotas
                $pagos = $this->crearPlanCuotas($inscripcion, $validated);
                return response()->json([
                    'mensaje' => 'Plan de cuotas creado exitosamente',
                    'cuotas' => $pagos,
                ], 201);
            } else {
                // Crear pago simple
                $pago = $this->crearPagoSimple($inscripcion, $validated);
                return response()->json([
                    'mensaje' => 'Pago registrado exitosamente',
                    'pago' => $pago,
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Crear pago simple (abono sin cuotas)
     */
    private function crearPagoSimple(Inscripcion $inscripcion, array $validated) {
        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'monto_abonado' => $validated['monto_abonado'],
            'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
            'metodos_pago_json' => $validated['metodos_pago_json'] ?? null,
            'referencia_pago' => $validated['referencia_pago'] ?? null,
            'es_plan_cuotas' => false,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
        
        // Calcular estado
        $pago->id_estado = $pago->calcularEstadoDinamico();
        $pago->save();
        
        return $pago;
    }
    
    /**
     * Crear plan de cuotas
     */
    private function crearPlanCuotas(Inscripcion $inscripcion, array $validated) {
        $grupoPago = Str::uuid();
        $cantidadCuotas = $validated['cantidad_cuotas'];
        $montoTotal = $validated['monto_abonado'];
        $montoPorCuota = round($montoTotal / $cantidadCuotas, 2);
        
        $pagos = [];
        
        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            $fechaVencimiento = now()
                ->addMonths($i)
                ->format('Y-m-d');
            
            $pago = Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'monto_abonado' => ($i == $cantidadCuotas) 
                    ? ($montoTotal - ($montoPorCuota * ($i - 1)))  // Última cuota
                    : $montoPorCuota,
                'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
                'referencia_pago' => $validated['referencia_pago'] ?? null,
                'es_plan_cuotas' => true,
                'numero_cuota' => $i,
                'cantidad_cuotas' => $cantidadCuotas,
                'fecha_vencimiento_cuota' => $fechaVencimiento,
                'grupo_pago' => $grupoPago,
                'fecha_pago' => now()->format('Y-m-d'),
            ]);
            
            $pago->id_estado = 101; // PENDIENTE para cuotas nuevas
            $pago->save();
            
            $pagos[] = $pago;
        }
        
        return $pagos;
    }
    
    /**
     * GET /api/inscripciones/{id}/saldo
     */
    public function getSaldo($id) {
        $inscripcion = Inscripcion::findOrFail($id);
        
        return response()->json($inscripcion->getDetalleAbonos());
    }
    
    /**
     * POST /api/pagos/calcular-cuotas
     * Simular cuotas sin crear
     */
    public function calcularCuotas(Request $request) {
        $validated = $request->validate([
            'monto_total' => 'required|numeric|min:0.01',
            'cantidad_cuotas' => 'required|integer|min:2|max:12',
        ]);
        
        $montoPorCuota = round($validated['monto_total'] / $validated['cantidad_cuotas'], 2);
        $cuotas = [];
        
        for ($i = 1; $i <= $validated['cantidad_cuotas']; $i++) {
            $cuotas[] = [
                'numero' => $i,
                'monto' => ($i == $validated['cantidad_cuotas']) 
                    ? ($validated['monto_total'] - ($montoPorCuota * ($i - 1)))
                    : $montoPorCuota,
                'vencimiento' => now()->addMonths($i)->format('Y-m-d'),
            ];
        }
        
        return response()->json([
            'cantidad_cuotas' => $validated['cantidad_cuotas'],
            'monto_total' => $validated['monto_total'],
            'cuotas' => $cuotas,
        ]);
    }
}
```

---

## 🎨 RUTAS (API + Web)

```php
// routes/web.php

Route::prefix('api')->group(function () {
    // Pagos
    Route::post('/pagos', [PagoApiController::class, 'store']);
    Route::get('/pagos/{id}', [PagoApiController::class, 'show']);
    Route::put('/pagos/{id}', [PagoApiController::class, 'update']);
    
    // Saldo
    Route::get('/inscripciones/{id}/saldo', [PagoApiController::class, 'getSaldo']);
    
    // Simulador de cuotas
    Route::post('/pagos/calcular-cuotas', [PagoApiController::class, 'calcularCuotas']);
});

// Mantener rutas web tradicionales
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('pagos', PagoController::class);
});
```

---

## 🎯 INTERFAZ BLADE (Formulario Inteligente)

```blade
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Registrar Pago</h5>
    </div>
    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf
        
        <!-- Inscripción -->
        <div class="form-group mb-3">
            <label>Inscripción *</label>
            <select name="id_inscripcion" class="form-control" id="inscripcionSelect" required>
                <option value="">Seleccionar...</option>
                @foreach($inscripciones as $insc)
                    <option value="{{ $insc->id }}" data-saldo="{{ $insc->getSaldoPendiente() }}">
                        {{ $insc->cliente->nombres }} - ${{ $insc->precio_final }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Saldo actual -->
        <div class="alert alert-info" id="saldoAlert" style="display:none;">
            <strong>Saldo pendiente:</strong> <span id="saldoMonto">$0.00</span>
        </div>
        
        <!-- Monto a pagar -->
        <div class="form-group mb-3">
            <label>Monto a Pagar *</label>
            <input type="number" name="monto_abonado" class="form-control" step="0.01" required>
        </div>
        
        <!-- Método de pago principal -->
        <div class="form-group mb-3">
            <label>Método de Pago Principal *</label>
            <select name="id_metodo_pago_principal" class="form-control" required>
                <option value="1">Efectivo</option>
                <option value="2">Débito/Crédito</option>
                <option value="3">Transferencia</option>
                <option value="4">Otro</option>
            </select>
        </div>
        
        <!-- Referencia de pago -->
        <div class="form-group mb-3">
            <label>Referencia (Comprobante/N° Transferencia)</label>
            <input type="text" name="referencia_pago" class="form-control" maxlength="100">
        </div>
        
        <!-- ===== CHECKBOX DINÁMICO PARA CUOTAS ===== -->
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="planCuotasCheckbox" name="es_plan_cuotas">
            <label class="form-check-label" for="planCuotasCheckbox">
                ¿Pagar en CUOTAS?
            </label>
        </div>
        
        <!-- Apartado de cuotas (oculto hasta marcar checkbox) -->
        <div id="apartadoCuotas" style="display:none;" class="border-start border-3 border-info ps-3 mb-3">
            <div class="form-group mb-3">
                <label>Cantidad de Cuotas *</label>
                <input type="number" name="cantidad_cuotas" class="form-control" min="2" max="12" id="cantidadCuotas">
            </div>
            
            <div id="previewCuotas"></div>
        </div>
        
        <!-- Botón guardar -->
        <button type="submit" class="btn btn-success">Guardar Pago</button>
    </form>
</div>

<script>
// Mostrar/ocultar apartado de cuotas
document.getElementById('planCuotasCheckbox').addEventListener('change', function(e) {
    const apartado = document.getElementById('apartadoCuotas');
    apartado.style.display = e.target.checked ? 'block' : 'none';
    
    if (e.target.checked) {
        document.getElementById('cantidadCuotas').required = true;
    } else {
        document.getElementById('cantidadCuotas').required = false;
    }
});

// Mostrar saldo cuando se selecciona inscripción
document.getElementById('inscripcionSelect').addEventListener('change', function() {
    const saldo = this.options[this.selectedIndex].dataset.saldo;
    if (saldo) {
        document.getElementById('saldoMonto').textContent = '$' + parseFloat(saldo).toFixed(2);
        document.getElementById('saldoAlert').style.display = 'block';
    }
});

// Simular cuotas en tiempo real
document.getElementById('cantidadCuotas').addEventListener('change', function() {
    const monto = document.querySelector('input[name="monto_abonado"]').value;
    if (!monto || this.value < 2) return;
    
    fetch('/api/pagos/calcular-cuotas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            monto_total: parseFloat(monto),
            cantidad_cuotas: parseInt(this.value),
        }),
    })
    .then(res => res.json())
    .then(data => {
        let html = '<strong>Preview de Cuotas:</strong><ul>';
        data.cuotas.forEach(c => {
            html += `<li>Cuota ${c.numero}/${data.cantidad_cuotas}: $${c.monto.toFixed(2)} - Vence: ${c.vencimiento}</li>`;
        });
        html += '</ul>';
        document.getElementById('previewCuotas').innerHTML = html;
    });
});
</script>
@endsection
```

---

## ✅ VENTAJAS DE ESTA ARQUITECTURA

| Aspecto | Beneficio |
|--------|-----------|
| **Simplicidad** | Una tabla, campos opcionales, todo flexible |
| **Acumulación** | Abonos se suman automáticamente sin cuotas fijas |
| **Cuotas opcionales** | Solo si marca checkbox |
| **Pagos mixtos** | Múltiples métodos en JSON |
| **Métodos simples** | 4 opciones claras (Efectivo, Tarjeta, Transferencia, Otro) |
| **API ready** | Listo para móvil y futuros gateways |
| **Admin flexible** | Interfaz adaptada a requisitos |
| **Escalable** | Fácil agregar Stripe/Mercado Pago |

---

## 🚀 PLAN DE IMPLEMENTACIÓN

### **Fase 1: Base (Hoy)**
1. Crear nueva migración refactorizando `pagos` y `metodos_pago`
2. Crear/actualizar Models con métodos helpers
3. Crear `PagoApiController`
4. Actualizar rutas

### **Fase 2: Interfaz (Mañana)**
5. Actualizar formulario Blade con checkbox dinámico
6. Agregar validaciones frontend con JavaScript
7. Pruebas manuales

### **Fase 3: Futuro**
8. Agregar Stripe/Mercado Pago
9. Dashboard de pagos avanzado
10. Notificaciones automáticas

---

**¿Implementamos ahora? ¿O ajustamos algo?**
