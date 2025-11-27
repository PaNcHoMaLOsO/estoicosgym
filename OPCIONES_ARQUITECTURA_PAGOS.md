# 🎯 OPCIONES DE ARQUITECTURA PARA SISTEMA DE PAGOS

## 📌 ENTENDIMIENTO DE TU PROPUESTA

```
"La persona deja solo abonado, no le colamos fecha del siguiente pago.
Después quiero abonar el restante pero aún no paga la totalidad.
Lo abonado se le suma a lo anteriormente abonado.
El pago aún no está completado.
Ahora sí pagó la totalidad y siempre al abonado se le va ir restando 
al precio de la membresía fijado."
```

---

## 🔄 OPCIÓN 1: TABLA ÚNICA "pagos" (SIMPLISTA)

**Idea**: Un solo registro por pago, acumulando montos.

### Estructura:
```php
pagos table {
    id
    id_inscripcion
    monto_abonado          // Lo que se pagó EN ESTE PAGO
    monto_acumulado        // Total pagado hasta ahora (denormalizado)
    monto_pendiente        // Cálculo: precio_final - monto_acumulado
    fecha_pago
    metodos_pago_json      // {"efectivo": 100, "tarjeta": 50}
    id_estado              // 101, 102, 103, 104
    observaciones
}
```

### Ejemplo:
```
Membresía: $300

PAGO 1:
├─ monto_abonado: $100
├─ monto_acumulado: $100 (helper calc)
├─ monto_pendiente: $200
└─ id_estado: 103 (PARCIAL)

PAGO 2:
├─ monto_abonado: $150
├─ monto_acumulado: $250
├─ monto_pendiente: $50
└─ id_estado: 103 (PARCIAL)

PAGO 3:
├─ monto_abonado: $50
├─ monto_acumulado: $300
├─ monto_pendiente: $0
└─ id_estado: 102 (PAGADO)
```

### Código:
```php
class Inscripcion {
    public function getSaldoPendiente() {
        $totalAbonado = $this->pagos()->sum('monto_abonado');
        return max(0, $this->precio_final - $totalAbonado);
    }
    
    public function estaPagada() {
        return $this->getSaldoPendiente() <= 0;
    }
}

class Pago {
    public function calculateEstado() {
        $saldo = $this->inscripcion->getSaldoPendiente();
        
        if ($saldo <= 0) return 102;      // PAGADO
        if ($this->monto_abonado > 0) return 103; // PARCIAL
        return 101;                        // PENDIENTE
    }
}
```

**Ventajas**:
- ✅ Simple, sin cuotas fijas
- ✅ Fácil acumulación
- ✅ Flexibilidad total

**Desventajas**:
- ❌ Si quieres CUOTAS después, hay que rediseñar
- ❌ Sin histórico de "planes de cuotas"
- ❌ Difícil diferenciar pago único vs cuotas

---

## 🎁 OPCIÓN 2: TABLA SEPARADA "planes_pago" + "pagos"

**Idea**: Separar el PLAN del pago individual.

### Estructura:
```php
planes_pago table {
    id
    id_inscripcion
    tipo_plan           // 'abono_simple' o 'cuotas'
    cantidad_cuotas
    monto_total
    monto_abonado
    estado_plan         // 'activo', 'completado', 'vencido'
    created_at
}

pagos table {
    id
    id_plan_pago        // FK a planes_pago
    id_inscripcion
    monto_abonado
    metodos_pago_json   // {"efectivo": 100, "tarjeta": 50}
    fecha_pago
    numero_pago         // 1, 2, 3... en secuencia del plan
    id_estado
}
```

### Ejemplo:
```
PLAN DE CUOTAS (planes_pago.id = 5)
├─ tipo_plan: 'cuotas'
├─ cantidad_cuotas: 3
├─ monto_total: $300

  PAGO 1 (pagos.id = 101)
  ├─ numero_pago: 1
  ├─ monto_abonado: $100
  └─ fecha_pago: 2025-11-27

  PAGO 2 (pagos.id = 102)
  ├─ numero_pago: 2
  ├─ monto_abonado: $100
  └─ fecha_pago: 2025-12-15

  PAGO 3 (pagos.id = 103)
  ├─ numero_pago: 3
  ├─ monto_abonado: $100
  └─ fecha_pago: 2026-01-10
```

### Código:
```php
class PlanPago {
    public function obtenerSaldo() {
        $totalAbonado = $this->pagos()->sum('monto_abonado');
        return $this->monto_total - $totalAbonado;
    }
    
    public function getPagosRestantes() {
        return $this->cantidad_cuotas - $this->pagos()->count();
    }
}

class Pago {
    public function calcularEstadoDinamico() {
        $plan = $this->planPago;
        $saldo = $plan->obtenerSaldo();
        
        if ($saldo <= 0) return 102;      // PAGADO
        if ($plan->pagos()->count() > 0) return 103; // PARCIAL
        return 101;                        // PENDIENTE
    }
}
```

**Ventajas**:
- ✅ Separación clara: PLAN vs PAGO
- ✅ Fácil implementar cuotas fijas con fechas
- ✅ Historial completo del plan
- ✅ Diferencia cuotas de abonos simples

**Desventajas**:
- ❌ Más complejo (2 tablas)
- ❌ Más queries si no hay eager loading
- ❌ Más migraciones

---

## 💾 OPCIÓN 3: TABLA ÚNICA "pagos" CON CAMPOS FLEXIBLES (HÍBRIDA)

**Idea**: Una sola tabla pero con campos opcionales para cuotas.

### Estructura:
```php
pagos table {
    id
    id_inscripcion
    monto_abonado
    metodos_pago_json           // {"efectivo": 100, "tarjeta": 50}
    fecha_pago
    id_estado
    
    // ===== Campos opcionales para CUOTAS =====
    es_plan_cuotas              // boolean: ¿Este pago es parte de cuotas?
    numero_cuota                // null si es abono simple
    cantidad_cuotas             // null si es abono simple
    fecha_vencimiento_cuota     // null si es abono simple
    grupo_pago_uuid             // null si es abono simple (agrupa cuotas)
    
    observaciones
    created_at
}
```

### Ejemplo:
```
PAGO 1 (Abono simple)
├─ monto_abonado: $100
├─ es_plan_cuotas: false
├─ numero_cuota: null
├─ cantidad_cuotas: null
└─ grupo_pago_uuid: null

PAGO 2 (Cuota 1/3)
├─ monto_abonado: $100
├─ es_plan_cuotas: true
├─ numero_cuota: 1
├─ cantidad_cuotas: 3
├─ fecha_vencimiento_cuota: 2025-12-31
└─ grupo_pago_uuid: 'abc123'

PAGO 3 (Cuota 2/3)
├─ monto_abonado: $100
├─ es_plan_cuotas: true
├─ numero_cuota: 2
├─ cantidad_cuotas: 3
├─ fecha_vencimiento_cuota: 2026-01-31
└─ grupo_pago_uuid: 'abc123' (MISMO)

PAGO 4 (Cuota 3/3)
├─ monto_abonado: $100
├─ es_plan_cuotas: true
├─ numero_cuota: 3
├─ cantidad_cuotas: 3
├─ fecha_vencimiento_cuota: 2026-02-28
└─ grupo_pago_uuid: 'abc123' (MISMO)
```

### Código:
```php
class Pago {
    public function esParteDeCuotas() {
        return $this->es_plan_cuotas;
    }
    
    public function cuotasRelacionadas() {
        if (!$this->grupo_pago_uuid) return [];
        return self::where('grupo_pago_uuid', $this->grupo_pago_uuid)
            ->orderBy('numero_cuota')
            ->get();
    }
    
    public function calcularEstado() {
        $saldo = $this->inscripcion->getSaldoPendiente();
        
        if ($saldo <= 0) return 102;
        if ($saldo < $this->inscripcion->precio_final) return 103;
        
        // Si es cuota vencida
        if ($this->es_plan_cuotas && $this->fecha_vencimiento_cuota < now()) {
            return 104;
        }
        
        return 101;
    }
}
```

**Ventajas**:
- ✅ Una sola tabla (simplicidad)
- ✅ Flexible: cuotas opcionales
- ✅ Mantiene compatibilidad con refactor anterior
- ✅ Acumulación automática

**Desventajas**:
- ❌ Muchos campos null (denormalización leve)
- ❌ Lógica condicional más compleja

---

## 🛒 OPCIÓN 4: EVENTOS CON SAGA PATTERN (AVANZADO)

**Idea**: Cada pago genera eventos que se procesan.

### Estructura:
```php
// Tabla simple de pagos
pagos table {
    id
    id_inscripcion
    monto_abonado
    metodos_pago_json
    fecha_pago
    id_estado
}

// Tabla de eventos (audit)
payment_events table {
    id
    id_pago
    evento_tipo         // 'pago_registrado', 'cuota_vencida', 'pagado_completo'
    datos_evento        // JSON con detalles
    procesado           // boolean
    created_at
}
```

### Flujo:
```
1. Crear Pago → Evento: 'pago_registrado'
2. Event Listener calcula saldo
3. Si saldo <= 0 → Evento: 'pagado_completo' 
   └─ Actualiza inscripción a estado PAGADA
4. Si fecha_vencimiento < hoy → Evento: 'cuota_vencida'
   └─ Notifica al admin
```

**Ventajas**:
- ✅ Muy escalable
- ✅ Historial completo de eventos
- ✅ Fácil agregar lógica futura

**Desventajas**:
- ❌ Complejo para empezar
- ❌ Requiere Laravel Events bien configurado
- ❌ Overkill para el caso actual

---

## 📊 COMPARATIVA RÁPIDA

| Aspecto | Opción 1 | Opción 2 | Opción 3 | Opción 4 |
|---------|----------|----------|----------|----------|
| Simplicidad | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐ |
| Flexibilidad | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Performance | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| Fácil mantener | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐ |
| Escalable | ⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

---

## 🎯 MI RECOMENDACIÓN

### **Usar OPCIÓN 3 (Híbrida)** porque:

1. ✅ Mantiene tu refactoring actual (no rompe nada)
2. ✅ Es simple pero flexible
3. ✅ Los campos null son pocos
4. ✅ Fácil agregar cuotas después con checkbox
5. ✅ Acumulación automática de abonos
6. ✅ Soporta pagos mixtos sin problema

### Flujo específico con Opción 3:

```
USUARIO: "Pagar $100 de $300"
↓
PAGO 1:
├─ monto_abonado: 100
├─ es_plan_cuotas: FALSE
├─ estado: 103 (PARCIAL)
└─ Saldo pendiente: 200

─────────────────────────

USUARIO: "Pagar otros $100"
↓
PAGO 2:
├─ monto_abonado: 100
├─ es_plan_cuotas: FALSE
├─ estado: 103 (PARCIAL)
└─ Saldo pendiente: 100

─────────────────────────

USUARIO: "Pagar últimos $100 en 2 cuotas"
↓ (Marca checkbox "Pagar en cuotas")

PAGO 3 (Cuota 1/2):
├─ monto_abonado: 50
├─ es_plan_cuotas: TRUE
├─ numero_cuota: 1
├─ cantidad_cuotas: 2
├─ grupo_pago_uuid: 'xyz789'
└─ estado: 103 (PARCIAL)

PAGO 4 (Cuota 2/2):
├─ monto_abonado: 50
├─ es_plan_cuotas: TRUE
├─ numero_cuota: 2
├─ cantidad_cuotas: 2
├─ grupo_pago_uuid: 'xyz789'
└─ estado: 102 (PAGADO) ✅
```

---

## 🔨 IMPLEMENTACIÓN RECOMENDADA

1. **Migración**: Modificar tabla `pagos` para agregar campos opcionales
2. **Modelo**: Agregar métodos para detectar si es cuota
3. **Controller**: Lógica de acumulación automática
4. **Vista**: Checkbox dinámico para cuotas
5. **JSON**: Campo `metodos_pago_json` para mixtos

¿Cuál opción te late más? ¿O combinamos algo de varias?
