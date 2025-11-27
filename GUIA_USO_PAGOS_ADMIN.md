# 📖 Guía de Uso - Registrar Pagos en EstóicosGym

**Para Administradores**  
*Versión 1.0 - 27 de noviembre de 2025*

---

## 🎯 Objetivo

Esta guía te enseña cómo registrar pagos de clientes de forma rápida y flexible usando el nuevo sistema de pagos de EstóicosGym.

---

## 🚀 Acceso Rápido

1. **URL:** `https://tudominio.com/admin/pagos`
2. **Botón:** "Nuevo Pago" (verde, en la esquina superior derecha)
3. **Navegación:** Admin → Módulo Pagos → Nuevo Pago

---

## 📋 Paso 1: Buscar Cliente

### 🔍 Cómo Buscar

```
┌─────────────────────────────────────┐
│ 🔍 Buscar cliente: [________]       │
│    (Mínimo 2 caracteres)            │
└─────────────────────────────────────┘
```

**Puedes buscar por:**
- ✓ **Nombre:** Juan, Pedro, María...
- ✓ **RUT:** 12.345.6, 98.765, etc
- ✓ **Email:** juan@, tu@email, etc

**Ejemplo:**
- Escribes: `12.345`
- Aparecen: Todos los clientes cuyo RUT empieza con 12.345
- Seleccionas: Juan Pérez (12.345.678-9)

### ✨ Información Visible

Al escribir, verás un dropdown con opciones. Cada opción muestra:
- **Nombre completo**
- **Membresía** (Gold, Silver, Premium)
- **Información contextual** (en algunos casos)

---

## 📊 Paso 2: Ver Información del Cliente

Después de seleccionar, aparece automáticamente:

```
╔════════════════════════════════════════════════╗
║ 👤 Juan Pérez                                  ║
╠════════════════════════════════════════════════╣
║ Membresía: Gold Premium                        ║
║ Total a Pagar: $50,000                         ║
║ Ya Abonado: $35,000                            ║
║ Saldo Pendiente: $15,000                       ║
║ Días Restantes: 45 días                        ║
║ Vencimiento: 15 de Diciembre 2025              ║
╚════════════════════════════════════════════════╝
```

### 📌 Qué Significa Cada Campo

| Campo | Significado |
|-------|------------|
| **Membresía** | Tipo de plan del cliente (Gold, Silver, etc) |
| **Total** | Precio de la membresía completa |
| **Abonado** | Dinero ya pagado acumulado |
| **Pendiente** | Dinero que aún debe |
| **Días Restantes** | Cuántos días quedan hasta vencimiento |
| **Vencimiento** | Fecha en que expira la membresía |

---

## 💳 Paso 3: Elegir Tipo de Pago

El sistema te ofrece **3 opciones**. Elige según tu necesidad:

### **A) ABONO PARCIAL** 💰
*Usar cuando: El cliente paga parte del saldo*

```
┌──────────────────────────────┐
│ ⊕ Abono Parcial              │
│   Suma al saldo anterior     │
└──────────────────────────────┘
```

**Ejemplo:**
- Cliente debe: **$15,000**
- Cliente paga: **$7,500** (solo la mitad)
- Resultado: Queda debiendo **$7,500**
- Estado: **Pendiente** (sigue en rojo)

**Cuándo usar:**
- ✓ Cliente paga en cuotas
- ✓ Cliente hace abono anticipado
- ✓ Cliente no puede pagar todo

---

### **B) PAGO COMPLETO** ✓
*Usar cuando: El cliente paga TODO el saldo*

```
┌──────────────────────────────┐
│ ✓✓ Pago Completo             │
│   Monto exacto restante      │
└──────────────────────────────┘
```

**Ejemplo:**
- Cliente debe: **$15,000**
- Cliente paga: **$15,000** (exacto)
- Resultado: Deuda **0**
- Estado: **PAGADO** (en verde)

**Cuándo usar:**
- ✓ Cliente liquida su deuda
- ✓ Cliente paga todo de una vez
- ✓ Último abono que falta

**Ventaja:** El monto se calcula automáticamente ✓ Sin errores

---

### **C) PAGO MIXTO** 🔀
*Usar cuando: El cliente paga con múltiples métodos*

```
┌──────────────────────────────┐
│ 🔀 Pago Mixto                │
│   Múltiples métodos          │
└──────────────────────────────┘
```

**Ejemplo:**
- Cliente debe: **$15,000**
- Cliente paga:
  - **$10,000** con tarjeta de crédito
  - **$5,000** en efectivo
- Total: **$15,000** ✓
- Resultado: Deuda **0**

**Cuándo usar:**
- ✓ Cliente paga parte con tarjeta, parte en efectivo
- ✓ Cliente usa debito + efectivo
- ✓ Cliente combina múltiples formas de pago

**Regla importante:** La suma de los dos métodos debe ser **exacta**

---

## 💰 Paso 4: Ingresar Datos del Pago

### **Si elegiste ABONO PARCIAL:**

```
Monto a Abonar: [_____________] $
Método de Pago: [Transferencia ▼]
```

- **Monto:** Ingresa la cantidad que el cliente paga (ej: 7500)
- **Método:** Selecciona (Transferencia, Efectivo, Débito, Crédito, etc)

**Validación en vivo:**
- Verás que se actualiza automáticamente:
  ```
  Nuevo abonado: $42,500 | Pendiente: $7,500
  ```

---

### **Si elegiste PAGO COMPLETO:**

```
Monto a Pagar: $15,000 (Automático - NO editable)
Método de Pago: [Efectivo ▼]
```

- **Monto:** Se llena automáticamente ✓ No lo toques
- **Método:** Selecciona cómo pagará

**Info:** Verás confirmación verde:
```
✓ Estado: PAGADO COMPLETAMENTE
```

---

### **Si elegiste PAGO MIXTO:**

```
💳 Tarjeta/Débito/Crédito: [_____________] $
💵 Efectivo: [_____________] $

Total: $15,000 / $15,000 ✓
```

- **Casilla 1:** Ingresa monto con tarjeta
- **Casilla 2:** Ingresa monto en efectivo
- **Validación:** Los dos campos deben sumar **exactamente** lo que debe

**Indicadores:**
- ✓ Verde: "Monto correcto"
- ❌ Rojo: "Monto incompleto" o "Monto excede"

---

## 📝 Paso 5: Campos Adicionales (Opcionales)

### **Referencia/Comprobante**
```
Referencia: [TRF-2025-001______________]
```
- Usa para registrar número de transferencia
- Útil para auditoría
- Ejemplo: `TRF-2025-001`, `REC-12345`, `CHQ-789`

### **Fecha de Pago**
```
Fecha: [2025-11-27]
```
- Por defecto: **Hoy** (se llena automático)
- Puedes cambiar si el pago es de otro día
- No puede ser **fecha futura**

### **Observaciones**
```
Observaciones: [____________________________________]
```
- Notas adicionales si es necesario
- Ej: "Cliente solicitó prórroga", "Pago en cuotas"
- Opcional

---

## 📊 Paso 6: Cuotas (OPCIONAL)

```
☐ Dividir en cuotas
```

**Usar si el cliente paga en cuotas:**

1. **Marca el checkbox:** ☑ Dividir en cuotas
2. Aparece un nuevo campo:
   ```
   Cantidad de cuotas: [3 cuotas ▼]
   ```
3. Selecciona cuántas (1 a 12)
4. Sistema muestra:
   ```
   Monto de cuota: $2,000
   ```

**Ejemplo:**
- Monto total: $6,000
- Cuotas: 3
- Resultado: $2,000 cada cuota

**Nota:** Si no necesitas cuotas, no marques. Sistema por defecto = 1 cuota.

---

## ✅ Paso 7: Registrar Pago

### Verificar Todo Está Correcto

Antes de hacer click, asegúrate:
- ✓ Cliente seleccionado
- ✓ Tipo de pago elegido
- ✓ Monto(s) ingresado(s)
- ✓ Método de pago seleccionado
- ✓ El botón está **VERDE** (no gris)

### Hacer Click

```
┌──────────────────────────────┐
│    ✓ REGISTRAR PAGO          │
└──────────────────────────────┘
```

### Qué Pasa

1. **Sistema valida** (0.5 segundos)
2. **Registra en BD** (1 segundo)
3. **Muestra confirmación** (verde):
   ```
   ¡Éxito! Pago registrado exitosamente
   ```
4. **Redirecciona** a lista de pagos

---

## 🔄 Casos de Uso Reales

### **Caso 1: Cliente paga en 3 cuotas**

```
INICIO:
Total: $30,000 | Abonado: $0 | Pendiente: $30,000

CUOTA 1 (Hoy):
Tipo: ABONO PARCIAL
Monto: $10,000
Método: Transferencia
Cuotas: 3
↓
RESULTADO: Abonado $10,000 | Pendiente $20,000

CUOTA 2 (En 1 mes):
Tipo: ABONO PARCIAL
Monto: $10,000
Método: Efectivo
↓
RESULTADO: Abonado $20,000 | Pendiente $10,000

CUOTA 3 (En 2 meses):
Tipo: PAGO COMPLETO
Método: Efectivo
Monto: $10,000 (automático)
↓
RESULTADO: Abonado $30,000 | Pendiente $0 ✓
ESTADO: PAGADO
```

---

### **Caso 2: Cliente paga con tarjeta + efectivo**

```
INICIO:
Total: $25,000 | Abonado: $0 | Pendiente: $25,000

PAGO:
Tipo: PAGO MIXTO
Casilla 1 (Tarjeta): $15,000
Casilla 2 (Efectivo): $10,000
Referencia: "TRF-VISA-456"
↓
VALIDACIÓN: 15,000 + 10,000 = 25,000 ✓
↓
RESULTADO: Abonado $25,000 | Pendiente $0 ✓
ESTADO: PAGADO
```

---

### **Caso 3: Cliente anticipa pago**

```
INICIO:
Total: $50,000 | Vencimiento en 90 días | Abonado: $0

PAGO ANTICIPADO:
Tipo: ABONO PARCIAL
Monto: $25,000
Método: Transferencia
Referencia: "ANTICIPO-001"
↓
RESULTADO: Abonado $25,000 | Pendiente $25,000
ESTADO: PENDIENTE (con 50% pagado)

[Cuando vuelve a pagar el resto, hace PAGO COMPLETO]
```

---

## 🚨 Errores Comunes y Cómo Evitarlos

### ❌ Error: "Monto excede el saldo"

**Causa:** Ingresaste más dinero del que debe

**Solución:** 
- Revisa el campo "Pendiente" en la cabecera
- Ingresa un monto igual o menor

**Ejemplo:**
- Debe: $15,000
- Ingresaste: $20,000 ❌
- Cambiar a: $15,000 ✓

---

### ❌ Error: "La suma debe ser exactamente..."

**Causa:** En pago mixto, tus dos montos no coinciden exactamente

**Solución:**
- Revisa que: Tarjeta + Efectivo = Pendiente
- Usa calculadora si es necesario

**Ejemplo:**
- Debe: $15,000
- Ingresaste: Tarjeta $10,000 + Efectivo $4,500 = $14,500 ❌
- Cambiar a: Tarjeta $10,000 + Efectivo $5,000 = $15,000 ✓

---

### ❌ Error: "Método de pago requerido"

**Causa:** Olvidaste seleccionar cómo pagó

**Solución:**
- Haz click en el dropdown "Método de Pago"
- Selecciona uno (Transferencia, Efectivo, etc)

---

### ❌ Error: "Cliente requerido"

**Causa:** No seleccionaste cliente

**Solución:**
- Escribe en el campo búsqueda
- Selecciona de la lista

---

### ❌ Botón Gris (Deshabilitado)

**Causa:** Falta algo en el formulario

**Solución:**
- Verifica: Cliente ✓, Tipo ✓, Monto ✓, Método ✓
- Cuando todo esté completo, botón se pone VERDE

---

## 💡 Tips y Trucos

### 1️⃣ Búsqueda Rápida
- Memoriza los primeros dígitos del RUT (ej: clientes que empiezan con "12")
- Busca por nombre corto (ej: "Juan" en lugar de "Juan Pablo González")
- Usa email para clientes sin RUT registrado

### 2️⃣ Validación en Vivo
- Observa el "Resumen" que se actualiza automáticamente
- Te muestra si todo está correcto (verde ✓) o hay problemas (rojo ❌)

### 3️⃣ Pago Mixto Fácil
- Si el cliente paga $15k total:
  - Tarjeta: $10k
  - Efectivo: $5k
  - Total debe ser: $15k exacto

### 4️⃣ Referencia de Pago
- Siempre ingresa número de transferencia (útil para auditoría)
- Formato: `TRF-AAAA-NNN` (ej: TRF-2025-001)

### 5️⃣ Cuotas
- Solo marca si el cliente paga en **múltiples partes**
- Si paga todo de una vez: **NO marques**

---

## 🎯 Flujo Resumen (Super Rápido)

Para registrar un pago en **5 pasos**:

```
1. Buscar cliente → "Juan Pérez"
2. Ver info → Debe $15,000
3. Elegir tipo → "Pago Completo"
4. Seleccionar método → "Efectivo"
5. Click → "REGISTRAR PAGO" ✓

LISTO EN 20 SEGUNDOS
```

---

## 📞 Soporte

### Preguntas Frecuentes

**P: ¿Puedo cambiar una pago después de registrado?**  
A: Sí, en la lista de pagos hay botón "Editar" (lápiz) → haz cambios

**P: ¿Se registra automáticamente la fecha?**  
A: Sí, por defecto es HOY. Puedes cambiarla si fue otro día.

**P: ¿Qué pasa si ingreso monto erróneo?**  
A: El sistema no permite enviar hasta que todo sea válido.

**P: ¿Cómo veo historial de pagos de un cliente?**  
A: Ve a Inscripciones → Cliente → "Ver Pagos"

**P: ¿Puedo hacer pago mixto con 3 métodos?**  
A: Ahora no, solo soporta 2 métodos. Si necesitas 3, haz 2 pagos separados.

---

## ✨ ¡Listo!

Ya sabes cómo usar el nuevo sistema de pagos. **¡Es simple, rápido y flexible!**

Cualquier duda, contacta con soporte.

---

**Última actualización:** 27 de noviembre de 2025  
**Versión:** 1.0
