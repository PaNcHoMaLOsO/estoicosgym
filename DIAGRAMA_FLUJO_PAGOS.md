# Diagrama del Flujo de Pagos - EstóicosGym

## 📊 Diagrama de Flujo General

```
┌─────────────────────────────────────────────────────────────────┐
│                  FLUJO DE REGISTRO DE PAGOS                     │
└─────────────────────────────────────────────────────────────────┘

                        START: Click "Nuevo Pago"
                              │
                              ▼
                    ┌──────────────────────┐
                    │  Mostrar Formulario  │
                    │   (PASO 1: BUSCAR)   │
                    └──────────────────────┘
                              │
                              ▼
                    ┌──────────────────────┐
                    │ Select2: Buscar      │
                    │ - Nombre             │
                    │ - RUT                │
                    │ - Email              │
                    └──────────────────────┘
                              │
                    ¿Cliente Seleccionado?
                      /              \
                    NO              YES
                    │                │
                    ▼                ▼
            [Ocultar form]   ┌───────────────────┐
                             │ MOSTRAR INFO      │
                             │ - Membresía       │
                             │ - Total           │
                             │ - Abonado         │
                             │ - Pendiente       │
                             │ - Días restantes  │
                             │ - Vencimiento     │
                             └───────────────────┘
                                      │
                                      ▼
                    ┌──────────────────────────────┐
                    │ PASO 2: ELEGIR TIPO DE PAGO  │
                    ├──────────────────────────────┤
                    │ ○ Abono Parcial              │
                    │ ○ Pago Completo              │
                    │ ○ Pago Mixto                 │
                    └──────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
   ABONO PARCIAL        PAGO COMPLETO         PAGO MIXTO
   ┌──────────────┐    ┌──────────────┐     ┌───────────────┐
   │ Monto libre  │    │ Monto AUTO   │     │ 2 Casillas:   │
   │ Método: req  │    │ Método: req  │     │ - Tarjeta/DB  │
   │              │    │              │     │ - Efectivo    │
   └──────────────┘    └──────────────┘     └───────────────┘
        │                     │                     │
        ▼                     ▼                     ▼
   Validar:            Validar:               Validar:
   - Monto > 0        - Método             - Suma = pendiente
   - Monto <= tot     - Estado             - Ambos > 0
   - Método           - Fecha              - Fecha
        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              │
                              ▼
                    ┌──────────────────────┐
                    │ PASO 3: COMÚN        │
                    ├──────────────────────┤
                    │ Referencia (opt)     │
                    │ Fecha (auto hoy)     │
                    │ Observaciones (opt)  │
                    │ Cuotas (checkbox)    │
                    └──────────────────────┘
                              │
                    ¿Botón habilitado?
                      /              \
                    NO              YES
                    │                │
                    ▼                ▼
            [Esperar datos]   ┌───────────────┐
                              │ Click SUBMIT  │
                              └───────────────┘
                                      │
                              ▼
                    Backend Valida TODO
                              │
                ┌─────────────┴──────────────┐
                │                            │
                ▼                            ▼
            VÁLIDO                       INVÁLIDO
              │                              │
              ▼                              ▼
        Registra en BD              Muestra Errores
              │                              │
              ▼                              ▼
        Retorna SUCCESS          Retorna a Form
              │
              ▼
        ┌──────────────────┐
        │ Redirect Index   │
        │ Success Message  │
        │ "Pago registrado │
        │  exitosamente"   │
        └──────────────────┘
              │
              ▼
            END
```

---

## 🔀 Árbol de Decisión: Tipo de Pago

```
¿Cuál es la situación del cliente?

├─ ¿Tiene deuda pendiente?
│  ├─ NO → ¿Quiere anticipar pago?
│  │       ├─ SÍ → ABONO PARCIAL (anticipado)
│  │       └─ NO → [No hacer nada]
│  │
│  └─ SÍ → ¿Pagará todo ahora?
│         ├─ SÍ (monto exacto) → PAGO COMPLETO
│         ├─ NO (parte) → ABONO PARCIAL
│         └─ Múltiples métodos → PAGO MIXTO

Ejemplos:
┌────────────────────────────────────────┐
│ Total: $50k                            │
│ Abonado: $0                            │
│ Pendiente: $50k                        │
├────────────────────────────────────────┤
│ Escenario 1: Paga $10k en efectivo     │
│ → ABONO PARCIAL + efectivo              │
│                                        │
│ Escenario 2: Paga $50k por transferencia│
│ → PAGO COMPLETO + transferencia        │
│                                        │
│ Escenario 3: Paga $30k tarjeta + $20k  │
│ → PAGO MIXTO (tarjeta + efectivo)      │
└────────────────────────────────────────┘
```

---

## 💾 Base de Datos - Campos Clave

```
TABLA: pagos
├── id (PK)
├── id_inscripcion (FK)
├── id_cliente (FK)
├── id_membresia (FK)
│
├── MONTOS:
│  ├── monto_total          [50000]
│  ├── monto_abonado        [35000]  ← Suma de todos los abonos
│  ├── monto_pendiente      [15000]  ← total - abonado
│  │
│  ├── cantidad_cuotas       [3]      ← Opcional
│  ├── numero_cuota          [1]
│  └── monto_cuota           [16666]  ← abonado / cuotas
│
├── PAGO:
│  ├── fecha_pago            [2025-11-27]
│  ├── id_metodo_pago        [1] Transferencia
│  ├── referencia_pago       ["TRF-001"] ← Opcional
│  │
│  └── observaciones         [Nota... [Tipo: abono]]
│
└── ESTADO:
   ├── id_estado             [202] ← 201=Pagado, 202=Parcial
   ├── created_at
   └── updated_at

ÍNDICES:
├── idx_inscripcion
├── idx_cliente
├── idx_estado
├── idx_fecha_pago
└── idx_monto_abonado
```

---

## 🧮 Lógica de Cálculo - Pseudocódigo

```javascript
// CUANDO CLIENTE ES SELECCIONADO
function onClienteChange(clienteId) {
    const cliente = getCliente(clienteId);
    
    total = cliente.inscripcion.precio_final || cliente.inscripcion.precio_base;
    abonado = 0;  // Nueva inscripción = $0 abonado
    pendiente = total - abonado;
    
    // Mostrar info
    updateClienteHeader({
        nombre: cliente.nombres,
        membresia: cliente.membresia.nombre,
        total: total,
        abonado: abonado,
        pendiente: pendiente,
        diasRestantes: daysUntil(cliente.fecha_vencimiento),
        vencimiento: cliente.fecha_vencimiento
    });
    
    // Habilitar tipo de pago
    showTipoPagoSection();
}

// CUANDO CAMBIA TIPO DE PAGO
function onTipoPagoChange(tipo) {
    if (tipo === 'abono') {
        // Mostrar input monto libre
        showMontoAbono();
        maxMonto = pendiente;  // No puede exceder pendiente
        
        // En tiempo real
        onMontoInput(monto) {
            nuevoAbonado = abonado + monto;
            nuevoPendiente = total - nuevoAbonado;
            updateResumen(`Nuevo: $${nuevoAbonado} | Pendiente: $${nuevoPendiente}`);
        }
    } 
    else if (tipo === 'completo') {
        // Mostrar monto automático (NO editable)
        montoAutomatico = pendiente;
        displayMonto(montoAutomatico, disabled=true);
        updateResumen(`✓ PAGADO COMPLETAMENTE`);
    } 
    else if (tipo === 'mixto') {
        // Mostrar dos inputs
        showMetodo1Input('Tarjeta');
        showMetodo2Input('Efectivo');
        target = pendiente;
        
        // En tiempo real
        onMontoMixtoInput() {
            total_ingresado = monto1 + monto2;
            if (total_ingresado === target) {
                estado = '✓ Correcto';
                enableSubmit();
            } else if (total_ingresado > target) {
                estado = '❌ Excede';
                disableSubmit();
            } else {
                estado = '❌ Incompleto';
                disableSubmit();
            }
            updateResumen(`Total: $${total_ingresado} / $${target} ${estado}`);
        }
    }
}

// AL ENVIAR FORMULARIO
function onFormSubmit() {
    // Frontend valida
    if (!isValid()) {
        showErrors();
        return;
    }
    
    // Backend valida
    tipoPago = getSelectedType();
    
    if (tipoPago === 'abono') {
        monto = getMontoAbono();
        if (monto <= 0 || monto > pendiente) {
            return error("Monto inválido");
        }
        montoAbonado = monto;
    }
    else if (tipoPago === 'completo') {
        montoAbonado = pendiente;
    }
    else if (tipoPago === 'mixto') {
        monto1 = getMontoMetodo1();
        monto2 = getMontoMetodo2();
        if (monto1 + monto2 !== pendiente) {
            return error("Suma debe ser exacta");
        }
        montoAbonado = monto1 + monto2;
    }
    
    // Calcular pendiente nuevo
    montoPendiente = total - montoAbonado;
    
    // Guardar en BD
    pago = create({
        monto_total: total,
        monto_abonado: montoAbonado,
        monto_pendiente: montoPendiente,
        tipo_pago: tipoPago,  // En observaciones
        estado: montoAbonado >= total ? 102 : 103
    });
    
    return redirect('/pagos', success="Pago registrado");
}
```

---

## 🎨 Estado de UI por Tipo de Pago

### **ABONO PARCIAL**
```
┌─────────────────────────────────────┐
│ 📦 Abono Parcial                    │
├─────────────────────────────────────┤
│ Monto a Abonar: [_______] $         │◄─ EDITABLE
│ Método: [Transferencia ▼]           │
│                                     │
│ Resumen: $45,000 | Pendiente: $5k   │◄─ DINÁMICO
└─────────────────────────────────────┘
```

### **PAGO COMPLETO**
```
┌─────────────────────────────────────┐
│ ✓ Pago Completo                     │
├─────────────────────────────────────┤
│ Monto: $15,000 (Automático)         │◄─ NO EDITABLE
│ Método: [Efectivo ▼]                │
│                                     │
│ ✓ Estado: PAGADO COMPLETAMENTE      │◄─ INFO
└─────────────────────────────────────┘
```

### **PAGO MIXTO**
```
┌─────────────────────────────────────┐
│ 🔀 Pago Mixto                       │
├─────────────────────────────────────┤
│ 💳 Tarjeta/Débito/Crédito:         │
│    [_______] $                      │◄─ EDITABLE
│                                     │
│ 💵 Efectivo:                        │
│    [_______] $                      │◄─ EDITABLE
│                                     │
│ Total: $15,000 / $15,000 ✓          │◄─ DINÁMICO
└─────────────────────────────────────┘
```

---

## 📈 Estados del Pago en BD

```
Estado ACTUAL:
┌──────────────────────────┐
│ Estado: 103 (Parcial)    │
├──────────────────────────┤
│ Total: $50,000           │
│ Abonado: $35,000 (70%)   │
│ Pendiente: $15,000 (30%) │
└──────────────────────────┘

DESPUÉS ABONO $10k:
┌──────────────────────────┐
│ Estado: 103 (Parcial)    │
├──────────────────────────┤
│ Total: $50,000           │
│ Abonado: $45,000 (90%)   │
│ Pendiente: $5,000 (10%)  │
└──────────────────────────┘

DESPUÉS PAGO FINAL $5k:
┌──────────────────────────┐
│ Estado: 102 (Pagado)     │
├──────────────────────────┤
│ Total: $50,000           │
│ Abonado: $50,000 (100%)  │
│ Pendiente: $0 (0%)       │
└──────────────────────────┘
```

---

## 🔍 Búsqueda - Algoritmo de Matching

```
INPUT: "juan p"

SELECT * FROM inscripciones i
JOIN clientes c ON i.id_cliente = c.id
WHERE 
    c.nombres LIKE '%juan%' 
    OR c.apellido_paterno LIKE '%p%'
    OR c.rut LIKE '%juan p%'
    OR c.email LIKE '%juan p%'
LIMIT 10;

RESULTADOS:
├─ 12.345.678-9 | Juan Pérez (Gold)
├─ 98.765.432-1 | Juan Pablo (Silver)
└─ 11.111.111-1 | Pablo Juan (Premium)

[Si coincide SELECT2 mostrará info preview]
```

---

## ⚡ Validaciones en Cascada

```
NIVEL 1: FRONTEND (JavaScript)
├─ Cliente seleccionado?
├─ Tipo de pago seleccionado?
├─ Campos requeridos completos?
├─ Monto en rango válido?
└─ Suma correcta (si mixto)?
   └─ Si todo OK → Botón HABILITADO
   └─ Si alguno falla → Botón DESHABILITADO

NIVEL 2: BACKEND (Laravel)
├─ Validar cliente existe
├─ Validar inscripción activa
├─ Validar método pago existe
├─ Validar fecha no es futura
├─ Según tipo:
│  ├─ ABONO: 0 < monto <= pendiente
│  ├─ COMPLETO: monto == pendiente
│  └─ MIXTO: m1 + m2 == pendiente (exacto)
└─ Si alguno falla → Error 422, volver a form

NIVEL 3: BD
└─ Check constraints si existen
```

---

## 🎯 KPIs de Éxito

```
✓ Tiempo promedio registro: < 30 segundos
✓ Tasa de errores validación: < 5%
✓ Usuarios sin confusión: > 95%
✓ Tipos de pago usados:
  - Abono Parcial: 40%
  - Pago Completo: 45%
  - Pago Mixto: 15%
```

---

## 🚨 Manejo de Errores Comunes

```
❌ "El monto no puede exceder $15,000"
   → Usuario trató de abonar más que pendiente
   → Solución: Mostrar máximo permitido

❌ "Método de pago requerido"
   → Usuario no seleccionó método
   → Solución: Marcar campo en rojo + tooltip

❌ "La suma debe ser exactamente $15,000"
   → Usuario en pago mixto ingresó mal montos
   → Solución: Mostrar suma actual vs esperada

❌ "La inscripción no está activa"
   → Sistema validación backend
   → Solución: Mostrar estado cliente en UI

❌ "Cliente requerido"
   → Usuario no seleccionó cliente
   → Solución: Botón submit disabled hasta seleccionar
```

---

## 📋 Checklist: Funcionalidades Implementadas

- [✓] Búsqueda Select2 con 3 criterios
- [✓] Información cliente dinámica
- [✓] Tres tipos de pago con radio buttons
- [✓] Abono parcial con cálculo en tiempo real
- [✓] Pago completo con monto automático
- [✓] Pago mixto con validación suma exacta
- [✓] Campos comunes (referencia, fecha, observaciones)
- [✓] Checkbox cuotas opcional
- [✓] Resumen dinámico por tipo
- [✓] Validaciones frontend
- [✓] Validaciones backend
- [✓] Manejo de errores
- [✓] Estados visuales (botón disabled/enabled)
- [✓] Responsive design
- [✓] Documentación completa
