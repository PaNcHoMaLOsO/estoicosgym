# 🔌 ¿USA APIs? SÍ, Y OPCIONES PARA PAGOS

## 📊 APIs EXISTENTES EN EL PROYECTO

El proyecto **YA USA APIs** para:

```
✅ Dashboard: /api/dashboard/stats, /api/dashboard/ingresos-mes, etc.
✅ Búsqueda: /api/clientes/search, /api/inscripciones/search
✅ Clientes: /api/clientes, /api/clientes/{id}, /api/clientes/{id}/stats
✅ Membresias: /api/membresias, /api/membresias/{id}
✅ Inscripciones: /api/inscripciones/calcular
✅ Pausas: /api/pausas/{id}/pausar, /api/pausas/{id}/reanudar
```

---

## 🎯 OPCIONES PARA IMPLEMENTAR PAGOS

### **OPCIÓN A: SIN USAR API (MÁS SIMPLE)**

Mantener como está ahora: **Formularios tradicionales HTML + Blade**

```php
// Ruta web (actual)
Route::post('admin/pagos', [PagoController::class, 'store']);

// Vista Blade con lógica directa
// - Campo monto_abonado
// - Checkbox para cuotas (show/hide con JavaScript)
// - Select de métodos de pago
```

**Ventajas**:
- ✅ Más simple
- ✅ Menos latencia
- ✅ Ya está funcionando

**Desventajas**:
- ❌ No reutilizable desde móvil
- ❌ No separación frontend/backend

---

### **OPCIÓN B: CON API REST + Frontend separado (RECOMENDADO)**

Crear una **API PagoController** similar a las que existen.

```php
// routes/web.php
Route::prefix('api')->group(function () {
    // Pagos
    Route::get('/pagos', [PagoApiController::class, 'index']);
    Route::get('/pagos/{id}', [PagoApiController::class, 'show']);
    Route::post('/pagos', [PagoApiController::class, 'store']);
    Route::put('/pagos/{id}', [PagoApiController::class, 'update']);
    Route::delete('/pagos/{id}', [PagoApiController::class, 'destroy']);
    
    // Obtener saldo de inscripción
    Route::get('/inscripciones/{id}/saldo', [PagoApiController::class, 'getSaldo']);
    
    // Calcular cuotas dinámicamente
    Route::post('/pagos/calcular-cuotas', [PagoApiController::class, 'calcularCuotas']);
    
    // Procesar pago mixto
    Route::post('/pagos/mixto', [PagoApiController::class, 'procesarMixto']);
});
```

#### **PagoApiController - Ejemplo**

```php
namespace App\Http\Controllers\Api;

use App\Models\Pago;
use App\Models\Inscripcion;
use Illuminate\Http\Request;

class PagoApiController extends Controller {
    
    /**
     * POST /api/pagos
     * Registrar un pago simple o inicio de cuotas
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'es_plan_cuotas' => 'boolean',
            'cantidad_cuotas' => 'required_if:es_plan_cuotas,true|integer|min:2',
            'metodos_pago' => 'required|array', // {"efectivo": 100, "tarjeta": 50}
            'referencia_pago' => 'nullable|string|max:100',
        ]);
        
        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        
        // Validar que no esté sobrelpagado
        $saldoActual = $inscripcion->getSaldoPendiente();
        if ($validated['monto_abonado'] > $saldoActual) {
            return response()->json([
                'error' => 'Monto excede saldo pendiente',
                'saldo_pendiente' => $saldoActual,
            ], 422);
        }
        
        // Crear pago(s)
        if ($validated['es_plan_cuotas']) {
            $pagos = $this->crearCuotas($inscripcion, $validated);
            return response()->json(['pagos' => $pagos], 201);
        } else {
            $pago = Pago::create($validated);
            $pago->id_estado = $pago->calculateEstado();
            $pago->save();
            return response()->json(['pago' => $pago], 201);
        }
    }
    
    /**
     * GET /api/inscripciones/{id}/saldo
     * Obtener saldo pendiente
     */
    public function getSaldo($id) {
        $inscripcion = Inscripcion::findOrFail($id);
        return response()->json([
            'id_inscripcion' => $inscripcion->id,
            'precio_final' => $inscripcion->precio_final,
            'total_abonado' => $inscripcion->pagos()->sum('monto_abonado'),
            'saldo_pendiente' => $inscripcion->getSaldoPendiente(),
            'estado' => $inscripcion->estaPagada() ? 'pagada' : 'pendiente',
        ]);
    }
    
    /**
     * POST /api/pagos/calcular-cuotas
     * Calcular cuotas dinámicamente
     */
    public function calcularCuotas(Request $request) {
        $validated = $request->validate([
            'monto_total' => 'required|numeric|min:0.01',
            'cantidad_cuotas' => 'required|integer|min:2|max:12',
            'fecha_inicio' => 'required|date|after:today',
        ]);
        
        $montoUnaCuota = $validated['monto_total'] / $validated['cantidad_cuotas'];
        $cuotas = [];
        
        for ($i = 1; $i <= $validated['cantidad_cuotas']; $i++) {
            $cuotas[] = [
                'numero' => $i,
                'monto' => round($montoUnaCuota, 2),
                'vencimiento' => now()
                    ->addMonths($i)
                    ->format('Y-m-d'),
            ];
        }
        
        return response()->json(['cuotas' => $cuotas]);
    }
    
    /**
     * POST /api/pagos/mixto
     * Procesar pago mixto (múltiples métodos)
     */
    public function procesarMixto(Request $request) {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'metodos_pago' => 'required|array|min:2',
            'metodos_pago.*' => 'numeric|min:0.01',
        ]);
        
        $montoTotal = array_sum($validated['metodos_pago']);
        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        
        // Validar montos
        $saldo = $inscripcion->getSaldoPendiente();
        if ($montoTotal > $saldo) {
            return response()->json([
                'error' => 'Monto total excede saldo pendiente',
            ], 422);
        }
        
        // Crear UN pago con métodos múltiples en JSON
        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'monto_abonado' => $montoTotal,
            'metodos_pago_json' => json_encode($validated['metodos_pago']),
            'fecha_pago' => now()->format('Y-m-d'),
            'id_estado' => ($montoTotal >= $saldo) ? 102 : 103,
        ]);
        
        return response()->json(['pago' => $pago], 201);
    }
}
```

---

### **OPCIÓN C: USAR API EXTERNA DE PAGOS (Stripe, Mercado Pago, etc.)**

Para pagos reales con tarjetas:

```php
// Integración con Stripe
Route::post('/pagos/procesar-stripe', [PagoApiController::class, 'procesarStripe']);

// Integración con Mercado Pago
Route::post('/pagos/procesar-mercado-pago', [PagoApiController::class, 'procesarMercadoPago']);
```

**Ejemplo Mercado Pago**:
```php
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

public function procesarMercadoPago(Request $request) {
    MercadoPagoConfig::setAccessToken(env('MERCADO_PAGO_TOKEN'));
    
    $client = new PaymentClient();
    $createRequest = [
        "transaction_amount" => $request->monto,
        "description" => "Pago membresía {$request->id_inscripcion}",
        "payment_method_id" => $request->payment_method_id,
        "payer" => [
            "email" => $request->email,
        ],
        "external_reference" => "REF-{$request->id_inscripcion}",
    ];
    
    $response = $client->create($createRequest);
    
    if ($response->status === 'approved') {
        // Registrar pago
        Pago::create([
            'id_inscripcion' => $request->id_inscripcion,
            'monto_abonado' => $request->monto,
            'referencia_pago' => $response->id,
            'id_estado' => 102, // PAGADO
        ]);
    }
    
    return response()->json($response);
}
```

---

## 🎯 RECOMENDACIÓN FINAL

### **Fase 1 (Ahora): OPCIÓN B (API Simple)**
- Crear `PagoApiController` con endpoints REST
- Soportar abonos simples + cuotas + pagos mixtos
- **Ventaja**: Reutilizable, escalable, listo para móvil

### **Fase 2 (Futuro): OPCIÓN C (Stripe/Mercado Pago)**
- Integrar gateway de pagos reales
- Pagos online con tarjeta
- **Ventaja**: Pagos seguros, automatizados

### **Interfaz**:
```
┌─────────────────────────────────────────┐
│ Frontend Web/Móvil                      │
│ (Vue.js, React, o Blade + HTMX)        │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ API REST (/api/pagos)                  │
│ - POST /api/pagos (crear pago)          │
│ - GET /api/pagos/{id} (obtener)         │
│ - POST /api/pagos/calcular-cuotas       │
│ - POST /api/pagos/mixto                 │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ Backend (Models, Validators)            │
│ - Pago model                            │
│ - Inscripcion helpers                   │
│ - Validaciones                          │
└─────────────────────────────────────────┘
```

---

## 📝 COMPARATIVA

| Aspecto | Opción A | Opción B | Opción C |
|---------|----------|----------|----------|
| Complejidad | ⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Reutilización | ❌ | ✅ | ✅ |
| Móvil ready | ❌ | ✅ | ✅ |
| Pagos reales | ❌ | ❌ | ✅ |
| Implementación rápida | ✅ | ⭐⭐⭐ | ❌ |

**Mi recomendación: Comenzar con Opción B para tener base lista para futuros gatways.**

¿Te late? ¿O prefieres algo diferente?
