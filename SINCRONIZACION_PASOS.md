# SINCRONIZACIÓN DE DATOS ENTRE PASOS - Documentación de Cambios

## Problemas Solucionados

| Problema | Causa | Solución |
|----------|-------|----------|
| **PASO 3 muestra datos viejos** | `goToStep()` no actualizaba datos | Agregado: `actualizarPrecio()` + `actualizarResumenPaso3()` |
| **Cliente muestra "-"** | No se actualiza al cambiar de paso | Agregado: `actualizarNombreCliente()` en PASO 2 |
| **Precio incorrecto ($99.000)** | Resumen leía datos desactualizados | Resumen ahora lee de múltiples fuentes (visible + oculto) |
| **Updates recursivos** | `actualizarNombreCliente()` llamaba siempre al resumen | Agregada validación: `if (currentStep === 3)` |
| **Loops de actualización** | Event listeners actualizaban siempre el resumen | Agregada validación: `if (currentStep === 3)` en listeners |

## Cambios Implementados

### 1. **Mejorada función `goToStep(step)`**

**Antes:**
```javascript
function goToStep(step) {
    // Solo cambiar paso y botones
    updateButtons();
    updateStepButtons();
}
```

**Después:**
```javascript
function goToStep(step) {
    // ... código de cambio de paso ...
    
    // Actualizar datos según el paso
    if (step === 2) {
        actualizarNombreCliente();  // Actualizar header con nombre
    } else if (step === 3) {
        actualizarPrecio();          // Recalcular precios
        setTimeout(() => {
            actualizarResumenPaso3(); // Actualizar resumen (después de precios)
        }, 100);
    }
}
```

**Beneficio:** Garantiza que los datos estén actualizados al entrar a cada paso.

---

### 2. **Mejorada función `actualizarResumenPaso3()`**

**Cambios clave:**

- ✅ Lectura robusta de elementos con validación nula
- ✅ Lectura múltiple de precio final: primero del elemento visible, luego del campo oculto
- ✅ Manejo seguro de valores vacíos (mostrar "-" en lugar de undefined)
- ✅ Console logging detallado para debugging
- ✅ Mantiene formato CLP consistente

**Ejemplo de lectura múltiple:**
```javascript
const precioTotalEl = document.getElementById('precio-total');
let precioFinal = '$0';

if (precioTotalEl?.textContent) {
    precioFinal = precioTotalEl.textContent;  // Del elemento visible
} else {
    const precioFinalOculto = document.getElementById('precio-final-oculto');
    if (precioFinalOculto?.value) {
        precioFinal = '$' + parseInt(precioFinalOculto.value).toLocaleString('es-CL');  // Del campo oculto
    }
}
```

---

### 3. **Mejorada función `actualizarNombreCliente()`**

**Antes:**
```javascript
function actualizarNombreCliente() {
    // Actualizar nombre
    // Siempre llamar actualizarResumenPaso3() → Causa loops
}
```

**Después:**
```javascript
function actualizarNombreCliente() {
    // Actualizar nombre
    
    // Solo actualizar resumen si estamos en PASO 3
    if (currentStep === 3) {
        actualizarResumenPaso3();
    }
}
```

**Beneficio:** Evita loops de actualización recursivos.

---

### 4. **Event Listeners Optimizados**

**Antes:**
```javascript
membresiaSelect.addEventListener('change', function() {
    actualizarPrecio();
    actualizarResumenPaso3();  // Siempre se ejecuta
});
```

**Después:**
```javascript
membresiaSelect.addEventListener('change', function() {
    actualizarPrecio();
    if (currentStep === 3) {           // Solo si estamos en PASO 3
        actualizarResumenPaso3();
    }
});
```

**Beneficio:** Evita actualizaciones innecesarias cuando no estamos viendo el resumen.

---

## Flujo de Actualización

### Cuando el usuario va a PASO 2:
```
goToStep(2)
    ↓
actualizarNombreCliente()
    ↓
Actualiza: #cliente-nombre (header del paso 2)
```

### Cuando el usuario va a PASO 3:
```
goToStep(3)
    ↓
actualizarPrecio()         [Recalcula precios de PASO 2]
    ↓
wait 100ms                 [Esperar cálculos]
    ↓
actualizarResumenPaso3()   [Llenar resumen con datos actualizados]
    ↓
Lee de:
  - #nombres (PASO 1)
  - #apellido_paterno (PASO 1)
  - #id_membresia (PASO 2)
  - #id_convenio (PASO 2)
  - #id_motivo_descuento (PASO 2)
  - #descuento_manual (PASO 2)
  - #precio-total (PASO 2)
    ↓
Actualiza:
  - #resumen-cliente
  - #resumen-membresia
  - #resumen-convenio
  - #resumen-motivo
  - #resumen-desc-manual
  - #resumen-precio-final
```

### Cuando el usuario cambia datos en PASO 1 mientras mira PASO 3:
```
Cambiar: #nombres
    ↓
actualizarNombreCliente()
    ↓
if (currentStep === 3)  ← ¿Estamos viendo PASO 3?
    ↓ Sí
actualizarResumenPaso3()
    ↓
#resumen-cliente se actualiza
```

### Cuando el usuario cambia membresía (cualquier paso):
```
Cambiar: #id_membresia
    ↓
actualizarPrecio()       [Siempre]
    ↓
if (currentStep === 3)   [¿Vemos resumen?]
    ↓ Sí
actualizarResumenPaso3()
```

---

## Testing Checklist

### Prueba 1: Navegación PASO 1 → PASO 2
- [ ] Completar PASO 1 con datos
- [ ] Hacer click "Siguiente"
- [ ] ✅ El nombre debe aparecer en el header del PASO 2 (#cliente-nombre)
- [ ] ✅ Debe mostrar: "Juan Pérez" (no "-")

### Prueba 2: Navegación PASO 2 → PASO 3
- [ ] Completar PASO 2 (seleccionar membresía)
- [ ] Hacer click "Siguiente"
- [ ] ✅ El resumen debe tener datos correctos:
  - [ ] Cliente: "Juan Pérez"
  - [ ] Membresía: "Plan Básico" (no "-")
  - [ ] Precio Final: $50.000 (no $0 ni $99.000)

### Prueba 3: Cambiar datos en PASO 1 y ver en PASO 3
- [ ] Ir a PASO 3
- [ ] Volver a PASO 1
- [ ] Cambiar "Nombres" a "Carlos"
- [ ] Volver a PASO 3
- [ ] ✅ El resumen debe actualizar: "Carlos Pérez"

### Prueba 4: Cambiar membresía en PASO 2 desde PASO 3
- [ ] Ir a PASO 3 (verá resumen con Plan Básico: $50.000)
- [ ] Volver a PASO 2
- [ ] Cambiar membresía a "Plan Premium"
- [ ] Volver a PASO 3
- [ ] ✅ El resumen debe actualizar:
  - [ ] Membresía: "Plan Premium"
  - [ ] Precio Final: $80.000 (diferente)

### Prueba 5: Cambiar convenio y ver precio actualizado
- [ ] En PASO 2, seleccionar membresía
- [ ] ✅ Precio-box debe mostrar: $50.000
- [ ] Cambiar a convenio con 10% descuento
- [ ] ✅ Precio-box debe actualizar: $45.000
- [ ] Ir a PASO 3
- [ ] ✅ Resumen debe mostrar: $45.000 (no $50.000)

### Prueba 6: Descuento manual
- [ ] En PASO 2, ingresar Descuento Manual: $5.000
- [ ] ✅ Precio-box debe actualizar: $40.000
- [ ] Ir a PASO 3
- [ ] ✅ Resumen debe mostrar:
  - [ ] Descuento Manual: -$5.000
  - [ ] Precio Final: $40.000

### Prueba 7: Volver atrás y adelante
- [ ] Estar en PASO 3
- [ ] Ir a PASO 2 (cambiar membresía)
- [ ] Volver a PASO 3
- [ ] ✅ El resumen debe reflejar el cambio (no datos viejos)

### Prueba 8: Console logging
- [ ] Abrir Console (F12)
- [ ] Ir a PASO 3
- [ ] Deben aparecer logs:
  ```
  🔄 Actualizando resumen PASO 3...
  🔍 Fetching precio para membresia: 1 convenio: 
  ✅ Cliente: Juan Pérez
  ✅ Membresía: Plan Básico
  ✅ Precio Final: $50.000
  ✅ Resumen PASO 3 actualizado
  ```

---

## Comportamiento Esperado

### En PASO 1:
- Usuario llena: Nombres, Apellido, Email, Celular
- No debe haber actualizaciones de resumen (no visible)

### En PASO 2:
- El header debe mostrar el nombre completado
- Al seleccionar membresía, debe mostrar precio-box
- Los cambios actualizan precio-box
- El resumen NO se actualiza (está en PASO 3)

### En PASO 3:
- Al entrar, se llena automáticamente el resumen
- Si cambia datos en PASO 1: resumen se actualiza
- Si cambia datos en PASO 2: resumen se actualiza
- Los datos mostrados son siempre los más recientes

---

## Debugging

### Si el nombre no aparece en PASO 2:
1. Abre Console (F12)
2. Ejecuta: `console.log(document.getElementById('cliente-nombre').textContent)`
3. Debe mostrar el nombre completo

### Si el resumen está vacío en PASO 3:
1. Abre Console
2. Ejecuta: `actualizarResumenPaso3()`
3. Mira los logs para ver qué datos se leen
4. Verifica que los inputs tengan valores

### Si el precio es incorrecto:
1. Abre Console
2. Busca logs de 💵 Calculando precio final
3. Verifica que el descuento manual sea correcto
4. Verifica que el precio_convenio se aplique correctamente

---

## Commits Relacionados

- `5064d44` - fix: Sincronizar datos entre pasos - actualizar resumen en PASO 3

