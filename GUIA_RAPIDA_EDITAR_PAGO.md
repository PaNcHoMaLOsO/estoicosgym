# 🚀 Guía Rápida - Cómo Editar un Pago

## 5 Pasos Simples

### 1️⃣ Acceder al Pago
```
Menú → Pagos → Tabla de Pagos
Haz clic en el botón "Editar" (icono lápiz 📝)
O haz clic en "Ver Detalles" → botón "Editar"
```

### 2️⃣ Ver el Formulario
```
Verás dos columnas:
- Izquierda (grande): Formulario de edición
- Derecha (pequeña): Panel de información actual
```

### 3️⃣ Realizar Cambios
```
✏️ Modifica los campos que necesites:
   • Monto Abonado
   • Fecha del Pago
   • Método de Pago
   • Cantidad de Cuotas
   • Referencia (comprobante)
   • Observaciones
```

### 4️⃣ Revisar Alertas
```
⚠️ Si algo está mal, verás alertas rojas
   • Monto mayor que precio → ADVERTENCIA
   • Errores de validación → ERROR
```

### 5️⃣ Guardar
```
✅ Haz clic en "Guardar Cambios" (botón verde)
   El estado se asigna automáticamente
   Te redirige a la vista de detalles del pago
```

---

## 🎨 Qué Puedes Editar

| Campo | ¿Editable? | Notas |
|-------|-----------|-------|
| Monto Abonado | ✅ Sí | El estado se actualiza automáticamente |
| Fecha del Pago | ✅ Sí | No puede ser futura |
| Método de Pago | ✅ Sí | Selecciona de la lista desplegable |
| Cuotas | ✅ Sí | De 1 a 12 (afecta monto por cuota) |
| Referencia | ✅ Sí | Máximo 100 caracteres |
| Observaciones | ✅ Sí | Máximo 500 caracteres |
| Cliente | ❌ No | No puede cambiar |
| Membresía | ❌ No | No puede cambiar |
| Estado | ❌ No | Se asigna automáticamente |

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Corregir Monto Equivocado
```
Situación: Registraste $50,000 pero fueron $80,000

Pasos:
1. Editar pago → Cambiar monto a $80,000
2. Sistema detecta: $80,000 = Precio completo
3. Estado cambia automático: PAGADO ✓
4. Guardar
5. ¡Listo! El cliente aparece como completamente pagado
```

### Ejemplo 2: Cambiar Método de Pago
```
Situación: Cliente dice que pagó en efectivo, no por transferencia

Pasos:
1. Editar pago
2. Cambiar método: "Transferencia" → "Efectivo"
3. Cambiar referencia: "TRF-12345" → "EFEC-001"
4. Agregar observación: "Cliente confirmó que fue en efectivo"
5. Guardar
```

### Ejemplo 3: Agregar Información
```
Situación: El pago existe pero no hay detalles

Pasos:
1. Editar pago
2. Agregar referencia: "BOL-20251127-001"
3. Agregar observación: "Cliente pagó en oficina central"
4. Guardar
```

### Ejemplo 4: Cambiar Cuotas
```
Situación: Era 1 cuota, ahora será en 3 cuotas

Pasos:
1. Editar pago
2. Monto: $60,000
3. Cuotas: Cambiar de 1 a 3
4. Sistema calcula: $60,000 ÷ 3 = $20,000 por cuota
5. Guardar
```

---

## ⚠️ Cosas a Tener en Cuenta

❌ **NO PUEDES:**
- Cambiar el cliente
- Cambiar la membresía
- Elegir el estado (se asigna automático)
- Cambiar el ID del pago

✅ **TIENES QUE:**
- Ingresar un monto entre $1 y precio de membresía
- Seleccionar una fecha en el pasado o hoy
- Elegir un método de pago de la lista
- Usar máximo 100 caracteres en referencia

---

## 🔔 Alertas y Mensajes

### Alerta Roja (Error):
```
❌ "El monto no puede exceder $100,000 (precio de membresía)"
Solución: Reduce el monto o revisa el precio
```

### Alerta Amarilla (Advertencia):
```
⚠️ "Este pago por $120,000 excede el valor de la membresía ($100,000)"
Solución: Revisa si es intencional
```

### Mensaje Verde (Éxito):
```
✅ "Pago actualizado exitosamente. El estado se asignó automáticamente: PAGADO"
Significa: El cambio se guardó correctamente
```

---

## 🎯 Panel Derecho - Información Útil

El panel lateral te muestra:

### Estado Actual (Azul)
```
Monto: $50,000
Estado: PARCIAL
Método: Transferencia Bancaria
Fecha: 27/11/2025
Cuota: 1 de 1
(Nota: Se asignará automáticamente al guardar)
```

### Inscripción (Naranja)
```
Precio Total: $100,000
Total Pagado: $50,000
Pendiente: $50,000
Progreso: 50% (barra visual)
```

### Acciones Rápidas (Gris)
```
- Ver Detalles → Ir a la vista completa del pago
- Ver Inscripción → Ir a los datos del cliente
```

---

## 📞 Soporte Rápido

### "¿Qué pasa si cometo un error?"
Puedes editar nuevamente. Los cambios se sobrescriben.

### "¿Se puede ver el historial de cambios?"
No en este formulario, pero el `updated_at` se actualiza automáticamente.

### "¿Qué pasa si ingreso un monto incorrecto?"
- Si es mayor al precio: Te muestra alerta roja
- Si es 0 o negativo: Te muestra error
- Si está bien: Se guarda y el estado se actualiza

### "¿Las fechas pasadas están permitidas?"
Sí, puedes editar pagos con fechas antiguas.

### "¿Puedo cambiar el cliente?"
No, el cliente está vinculado a la inscripción y no es editable.

---

## ✅ Checklist antes de Guardar

Antes de hacer clic en "Guardar Cambios", verifica:

- [ ] El monto es válido (entre $1 y precio de membresía)
- [ ] La fecha no es futura
- [ ] Elegiste un método de pago
- [ ] La referencia tiene sentido (si la completaste)
- [ ] Las observaciones son claras (si las agregaste)
- [ ] No hay alertas rojas
- [ ] Los valores en el panel derecho se ven correctos

---

## 🎓 Buenas Prácticas

### ✅ Hacer:
```
✅ Agregar referencias: "TRF-12345", "EFEC-001", "BOL-001"
✅ Usar observaciones: "Cliente confirmó el pago", "Pendiente comprobante"
✅ Actualizar estados: Si el cliente pagó completo, editar el monto
✅ Documentar cambios: Quién edita, cuándo, por qué
```

### ❌ Evitar:
```
❌ Ingresar montos ficticios
❌ Cambiar fechas al presente (usar fecha real)
❌ Dejar referencias vacías si es importante
❌ Hacer cambios sin justificación (por eso está el campo observaciones)
```

---

## 🚨 Casos de Error Comunes

### Error 1: "Monto Inválido"
```
Posible causa: Intentaste ingresar un monto mayor al precio
Solución: Verifica el precio de la membresía en el panel derecho
```

### Error 2: "Fecha No Válida"
```
Posible causa: Elegiste una fecha futura
Solución: Selecciona hoy o una fecha anterior
```

### Error 3: "Método de Pago Requerido"
```
Posible causa: Dejaste vacío el campo de método
Solución: Selecciona un método de la lista desplegable
```

---

## 📊 Resumen Visual del Flujo

```
┌─────────────────────┐
│   VER PAGO          │
├─────────────────────┤
│  Botón "Editar"     │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│  FORMULARIO EDICIÓN │
├─────────────────────┤
│ • Monto             │
│ • Fecha             │
│ • Método            │
│ • Cuotas            │
│ • Referencia        │
│ • Observaciones     │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│  VALIDAR CLIENTE    │
├─────────────────────┤
│ ¿Está todo bien?    │
└────────┬────────────┘
         │
    ┌────┴───┐
    │         │
   SÍ        NO
    │         │
    ▼         ▼
 GUARDAR   MOSTRAR ERRORES
    │         │
    └────┬────┘
         │
         ▼
┌─────────────────────┐
│  ACTUALIZAR BD      │
├─────────────────────┤
│ • Actualizar campos │
│ • Calcular estado   │
│ • Guardar cambios   │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│  REDIRIGIR A SHOW   │
├─────────────────────┤
│  + Mensaje exitoso  │
└─────────────────────┘
```

---

**Guía Rápida - Edición de Pagos**  
**Versión:** 1.0  
**Última actualización:** 27 de Noviembre 2025  
**¿Preguntas?** Contacta al equipo de soporte
