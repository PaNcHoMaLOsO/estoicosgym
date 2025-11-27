# 📋 Guía de Validaciones - Edición de Pagos

## Validaciones Implementadas

### 1. **MONTO ABONADO**

#### Validaciones:
✅ Requerido (no puede estar vacío)  
✅ Numérico (solo números y punto)  
✅ Mínimo: $1  
✅ Máximo: $999,999,999  
✅ No puede exceder precio de membresía  

#### Ejemplos de Rechazo:
```
❌ Vacío → "El campo monto abonado es requerido"
❌ Texto "abc" → "El campo monto debe ser numérico"
❌ $0 → "El monto debe ser mayor a 0"
❌ $1,000,000 (si membresía es $500k) → "El monto no puede exceder $500,000 (precio de membresía)"
```

#### Ejemplos de Aceptación:
```
✅ $50,000 (pago parcial de membresía $100k) → Estado: PARCIAL
✅ $100,000 (pago completo de membresía $100k) → Estado: PAGADO
✅ $1 (pago mínimo) → Estado: PARCIAL
```

---

### 2. **FECHA DE PAGO**

#### Validaciones:
✅ Requerido  
✅ Formato válido (YYYY-MM-DD)  
✅ No puede ser fecha futura  
✅ Máximo = hoy  

#### Ejemplos de Rechazo:
```
❌ Vacío → "El campo fecha pago es requerido"
❌ "32/13/2025" → Formato inválido
❌ "01/01/2026" (futuro) → "La fecha de pago no puede ser futura"
```

#### Ejemplos de Aceptación:
```
✅ 01/01/2025
✅ 27/11/2025
✅ Hoy
```

---

### 3. **MÉTODO DE PAGO**

#### Validaciones:
✅ Requerido  
✅ Debe existir en tabla metodos_pago  
✅ Foreign key válido  

#### Opciones Disponibles:
```
- Efectivo
- Transferencia Bancaria
- Tarjeta de Crédito
- Tarjeta de Débito
- Cheque
(Depende de tu configuración)
```

#### Ejemplos de Rechazo:
```
❌ ID 999 (no existe) → "El id de método pago seleccionado es inválido"
❌ Vacío → "El campo método pago es requerido"
```

---

### 4. **CANTIDAD DE CUOTAS**

#### Validaciones:
✅ Opcional (default = 1)  
✅ Entero positivo  
✅ Mínimo: 1  
✅ Máximo: 12  

#### Ejemplos:
```
✅ Vacío → Se usa 1 (pago único)
✅ 3 → Se divide el monto en 3 cuotas
✅ 12 → Se divide en 12 cuotas (máximo permitido)
```

#### Cálculo Automático:
```
Monto abonado: $60,000
Cantidad de cuotas: 3
Monto por cuota: $60,000 ÷ 3 = $20,000
```

---

### 5. **REFERENCIA DE PAGO**

#### Validaciones:
✅ Opcional  
✅ String de máximo 100 caracteres  
✅ Alfanumérico (A-Z, 0-9, símbolos)  

#### Ejemplos Válidos:
```
✅ TRF-2025-001-001
✅ BOL-EFEC-001
✅ CHEQUENRO1234567
✅ TVN123456789ABC
✅ PAYPAL-TRANS-XYZ
```

#### Ejemplos Inválidos:
```
❌ Más de 100 caracteres → Se trunca automáticamente
```

---

### 6. **OBSERVACIONES**

#### Validaciones:
✅ Opcional  
✅ Máximo 500 caracteres  
✅ Cualquier texto  

#### Ejemplos Válidos:
```
✅ "Cliente pagará el resto el viernes"
✅ "Pago parcial, pendiente de confirmar"
✅ "Factura enviada a correo del cliente"
✅ Vacío (sin observaciones)
```

#### Contador en Tiempo Real:
```
Mientras escribes ves: [n]/500 caracteres
Ejemplo: "Cliente pagará mañana" = 22/500
```

---

### 7. **INSCRIPCIÓN** (Solo Lectura)

#### Validaciones:
✅ Debe existir  
✅ Cliente visible  
✅ Membresía visible  

❌ **No es editable** desde este formulario

---

## 🎯 Flujo de Validación Completo

### Paso 1: Cliente (JavaScript)
```javascript
function validar() {
    if (monto <= 0) alert("⚠️ Monto debe ser > 0");
    if (monto > montoTotal) alert("⚠️ Monto excede precio");
    if (fecha > hoy) alert("⚠️ Fecha no puede ser futura");
    if (cuotas > 12) alert("⚠️ Máximo 12 cuotas");
}
```

### Paso 2: Servidor (Laravel Validation)
```php
$validated = $request->validate([
    'monto_abonado' => 'required|numeric|min:1|max:999999999',
    'fecha_pago' => 'required|date|before_or_equal:today',
    'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
    'referencia_pago' => 'nullable|string|max:100',
    'observaciones' => 'nullable|string|max:500',
]);
```

### Paso 3: Lógica (Controller)
```php
// Validaciones adicionales
if ($montoAbonado > $montoTotal) {
    return back()->withErrors([...]);
}

// Cálculo automático del estado
$estadoId = ($montoAbonado >= $montoTotal) ? 102 : 103;
```

### Paso 4: Base de Datos
```sql
-- Foreign keys verifican integridad
UPDATE pagos SET 
    monto_abonado = 50000,
    id_estado = 103,  -- Foreign key valida
    updated_at = NOW()
WHERE id = 1;
```

---

## 🔄 Cambio Automático de Estado

| Acción | Antes | Después | Nuevo Estado |
|--------|-------|---------|--------------|
| Editar $50k → $100k | PARCIAL | PAGADO | 102 |
| Editar $100k → $50k | PAGADO | PARCIAL | 103 |
| Editar $0 → $20k | PENDIENTE | PARCIAL | 103 |

**El estado se asigna automáticamente. No necesitas seleccionarlo.**

---

## ⚠️ Mensajes de Error Detallados

### Error 1: Monto Inválido
```
Monto abonado: $1,000,000
Precio membresía: $500,000
Error: "El monto no puede exceder $500,000 (precio de membresía)"
```

### Error 2: Fecha Futura
```
Fecha ingresada: 01/12/2025
Hoy: 27/11/2025
Error: "La fecha de pago no puede ser futura"
```

### Error 3: Método Inexistente
```
ID de método: 999
Error: "El id de método pago seleccionado es inválido"
```

---

## 📊 Ejemplo Completo de Edición

### Datos Iniciales del Pago:
```
ID Pago: 5
Cliente: Juan García
Membresía: Premium (Precio: $100,000)
Monto Abonado: $50,000 (PARCIAL)
Fecha: 20/11/2025
Método: Transferencia Bancaria
Cuotas: 1
Referencia: TRF-2025-0123
Observaciones: Pago inicial
```

### Usuario edita:
```
Monto: $100,000 (cambio de $50k a $100k)
Fecha: (sin cambio)
Método: Efectivo (cambio de Transferencia)
Cuotas: 1
Referencia: EFEC-001
Observaciones: "Cliente pagó en efectivo en caja"
```

### Resultado después de guardar:
```
✅ Monto: $100,000
✅ Estado: PAGADO (cambio automático de PARCIAL)
✅ Método: Efectivo
✅ Fecha: 27/11/2025 (automático al guardar)
✅ Referencia: EFEC-001
✅ Observaciones: "Cliente pagó en efectivo en caja"
✅ Mensaje: "Pago actualizado exitosamente. El estado se asignó automáticamente: PAGADO"
```

---

## 🧪 Pruebas Recomendadas

### Test 1: Monto Válido
- [ ] Editar monto parcial → Verificar estado PARCIAL
- [ ] Editar monto completo → Verificar estado PAGADO

### Test 2: Validaciones de Rango
- [ ] Intentar monto negativo → Debe rechazar
- [ ] Intentar monto 0 → Debe rechazar
- [ ] Intentar monto mayor a precio → Debe rechazar

### Test 3: Fechas
- [ ] Editar con fecha hoy → Debe aceptar
- [ ] Editar con fecha futura → Debe rechazar
- [ ] Editar con fecha pasada → Debe aceptar

### Test 4: Observaciones
- [ ] 100 caracteres → Debe aceptar
- [ ] 500 caracteres → Debe aceptar
- [ ] 501 caracteres → Debe rechazar

### Test 5: Integridad
- [ ] Verificar que el cliente no cambia
- [ ] Verificar que la membresía no cambia
- [ ] Verificar que se actualizan timestamps

---

**Documentación de Validaciones**  
**Versión:** 1.0  
**Última actualización:** 27 de Noviembre 2025
