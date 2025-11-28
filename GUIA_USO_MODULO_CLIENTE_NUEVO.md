# Módulo Nuevo: Creación de Clientes - Guía de Uso

## 📋 Overview

El nuevo módulo de creación de clientes permite registrar clientes en **3 pasos opcionales**:

1. **PASO 1**: Datos personales del cliente
2. **PASO 2**: Membresía e inscripción (OPCIONAL)
3. **PASO 3**: Información de pago (OPCIONAL)

Cada paso tiene sus propios botones de acción para mayor flexibilidad.

---

## 🎯 Los 3 Flujos Disponibles

### **Flujo 1: Solo Cliente** (PASO 1)
```
Completa → Click "Guardar Cliente" 
Resultado: Cliente registrado SIN membresía
Estado en BD: Cliente con activo=true
```

### **Flujo 2: Cliente + Membresía** (PASO 1 + PASO 2)
```
Paso 1 completo → Click "Siguiente" → Paso 2 
Paso 2 completo → Click "Guardar con Membresía"
Resultado: Cliente + Inscripción creados
Estado en BD: 
  - Cliente: activo=true
  - Inscripción: id_estado=100 (Activa)
  - NO crea Pago
```

### **Flujo 3: Completo** (PASO 1 + PASO 2 + PASO 3)
```
Paso 1 completo → "Siguiente" → Paso 2
Paso 2 completo → "Siguiente" → Paso 3
Paso 3 completo → Click "Guardar Todo"
Resultado: Cliente + Inscripción + Pago creados
Estado en BD:
  - Cliente: activo=true
  - Inscripción: id_estado=100 (Activa)
  - Pago: id_estado=201 (Pagado) o 200 (Pendiente)
```

---

## ✅ Validaciones por Paso

### PASO 1: Datos del Cliente
| Campo | Requerido | Validación |
|-------|-----------|-----------|
| Nombres | ✅ | No puede estar vacío |
| Apellido Paterno | ✅ | No puede estar vacío |
| Apellido Materno | ❌ | Opcional |
| Email | ✅ | Formato válido + único en BD |
| Celular | ✅ | Mínimo 9 dígitos |
| RUT/Pasaporte | ❌ | Algoritmo módulo 11 (si se ingresa) |
| Otros campos | ❌ | Opcionales |

**Validación**: Al hacer "Siguiente" o "Guardar"

### PASO 2: Membresía
| Campo | Requerido | Validación |
|-------|-----------|-----------|
| Membresía | ✅ | Debe seleccionar una |
| Fecha Inicio | ✅ | Hoy o posterior |
| Convenio | ❌ | Opcional (aplica descuento si existe) |

**Validación**: Al hacer "Siguiente" o "Guardar con Membresía"

### PASO 3: Pago
| Campo | Requerido | Validación |
|-------|-----------|-----------|
| Monto Abonado | ✅ | Mayor a $0 |
| Método Pago | ✅ | Debe seleccionar uno |
| Fecha Pago | ✅ | Hoy o anterior |

**Validación**: Al hacer "Guardar Todo"

---

## 🔄 Navegación

### Botones de Paso (Arriba)
- **Paso 1**: Siempre habilitado (inicio)
- **Paso 2**: Se habilita cuando completas Paso 1
- **Paso 3**: Se habilita cuando llegas a Paso 2

### Botones de Acción (Abajo)
- **← Anterior**: Volver al paso anterior
- **Siguiente →**: Ir al siguiente paso (valida actual)
- **Guardar [opción]**: Guardar según flujo elegido

---

## 💾 Opciones de Guardado

### En PASO 1
```
[Cancelar] [← Anterior (oculto)] [Siguiente →] [Guardar Cliente]
```
- **Guardar Cliente**: Registra solo cliente, sin membresía

### En PASO 2
```
[Cancelar] [← Anterior] [Siguiente →] [Guardar con Membresía]
```
- **Guardar con Membresía**: Cliente + Inscripción, sin pago

### En PASO 3
```
[Cancelar] [← Anterior] [Guardar Todo]
```
- **Guardar Todo**: Cliente + Inscripción + Pago completo

---

## 🔐 Seguridad

### Anti-Duplicados
- Cada formulario tiene token único (uniqid)
- Si se intenta reenviar: error "Formulario duplicado"
- Si falla guardado de token: cliente se elimina automáticamente

### Orden de Validación
1. Validar datos
2. Crear cliente
3. Validar token
4. Si token falla → eliminar cliente y mostrar error

---

## 📊 Datos en Base de Datos

### Cliente Creado
```
clientes table:
  - id: auto
  - run_pasaporte: string (nullable)
  - nombres: string
  - apellido_paterno: string
  - email: string (UNIQUE)
  - celular: string
  - activo: boolean (true)
  - created_at / updated_at
```

### Inscripción Creada (si PASO 2 completado)
```
inscripciones table:
  - id: auto
  - id_cliente: FK clientes.id
  - id_membresia: FK membresias.id
  - id_precio_acordado: FK precios_membresias.id
  - id_convenio: FK convenios.id (nullable)
  - fecha_inicio: date
  - fecha_vencimiento: date (fecha_inicio + duracion_dias)
  - precio_base: decimal
  - precio_final: decimal (con descuento si aplica)
  - id_estado: 100 (Activa)
```

### Pago Creado (si PASO 3 completado)
```
pagos table:
  - id: auto
  - id_inscripcion: FK inscripciones.id
  - id_cliente: FK clientes.id
  - monto_total: decimal (precio final)
  - monto_abonado: decimal (lo que pagó)
  - monto_pendiente: decimal (total - abonado)
  - fecha_pago: date
  - id_metodo_pago: FK metodos_pago.id
  - id_estado: 201 (Pagado) o 200 (Pendiente)
```

---

## ⚙️ Lógica del Controlador

```php
store() {
  1. Validar datos cliente (PASO 1)
  2. Crear Cliente
  3. Validar token anti-duplicado
  4. Si flujo='solo_cliente': retornar
  5. Si flujo='con_membresia' o 'completo':
     - Validar membresía (PASO 2)
     - Crear Inscripción
     - Calcular precio (con descuento si hay convenio)
  6. Si flujo='completo':
     - Validar pago (PASO 3)
     - Crear Pago
  7. Retornar con mensaje de éxito
}
```

---

## 🐛 Errores Comunes

### "Email ya existe"
- Otro cliente tiene ese email
- **Solución**: Usar email diferente o verificar si cliente ya existe

### "RUT inválido"
- Formato incorrecto o dígito verificador calculado mal
- **Solución**: Verificar formato XX.XXX.XXX-X o usar pasaporte

### "Formulario duplicado"
- Se intentó reenviar el formulario 2 veces con el mismo token
- **Solución**: Cargar página de nuevo, llenar formulario y guardar

### "Membresía requerida"
- No seleccionó membresía en PASO 2
- **Solución**: Seleccionar membresía antes de "Siguiente"

---

## 🎨 UI/UX

### Visual Feedback
- **Step Buttons**: Muestran progreso (azul=actual, gris=completado, deshabilitado=bloqueado)
- **Error Messages**: Muestran en rojo bajo cada campo inválido
- **Loader**: Spinner en botón durante guardado
- **Confirmación**: SweetAlert2 antes de guardar

### Responsive
- ✅ Desktop: 2 columnas
- ✅ Tablet: 1 columna
- ✅ Mobile: Full width

---

## 📋 Resumen Rápido

**¿Cómo registrar un cliente?**

1. Ir a Admin → Clientes → Nuevo Cliente
2. Rellenar PASO 1 (datos personales)
3. Elegir opción:
   - **Solo cliente**: Click "Guardar Cliente"
   - **Con membresía**: "Siguiente" → PASO 2 → "Guardar con Membresía"
   - **Completo**: "Siguiente" → PASO 2 → "Siguiente" → PASO 3 → "Guardar Todo"

**¡Listo!** Cliente registrado en sistema.

