# Cambios en Formulario de Inscripción - Versión 2.0

## Fecha: 26/11/2025

### 🎯 Mejoras Implementadas

#### 1. **Formulario Mejorado con 4 Pasos Claros**
- **Paso 1:** Cliente y Membresía (con select expandido)
- **Paso 2:** Fechas (Inicio y Vencimiento auto-calculado)
- **Paso 3:** Convenio y Descuentos
- **Paso 4:** Información de Pago

#### 2. **Estado Simplificado**
- ✅ Se removió selector de estados (quedó oculto con valor "Activa" = 201)
- ✅ Lógica: Al inscribirse, cliente siempre estará "Activo"
- ✅ Solo se puede cambiar estado después en edición si es necesario

#### 3. **Descuentos Automáticos por Convenio**
- ✅ Si se selecciona convenio Y membresía es MENSUAL → Descuento automático de $5.000
- ✅ Se llena automáticamente el "Motivo Descuento"
  - Ejemplo: Si convenio es "INACAP" → Auto-selecciona motivo "Estudiante"
- ✅ Se puede agregar descuento adicional si es necesario

#### 4. **Opción de Pago Pendiente**
- ✅ Nuevo checkbox: "Dejar pago pendiente"
- ✅ Si se marca:
  - Se ocultan todos los campos de pago
  - La inscripción se crea SIN registro de pago
  - Permite inscribir cliente primero y registrar pago después
- ✅ Si NO se marca:
  - Muestra sección completa de pago (Fecha, Monto, Método)
  - Campos required

#### 5. **Campos Dinámicos Condicionales**
- ✅ Cantidad de Cuotas → Solo visible si pago es PARCIAL (monto < total)
- ✅ Vencimiento de Cuota → Solo visible si hay cuotas
- ✅ Cálculo automático de monto por cuota

#### 6. **Resumen de Precios en Tiempo Real**
- ✅ Muestra dinámicamente:
  - Precio Base (de la membresía)
  - Descuento (convenio + adicional)
  - Precio Final (Base - Descuento)

#### 7. **Fecha de Vencimiento Auto-Calculada**
- ✅ Se calcula al seleccionar membresía o cambiar fecha de inicio
- ✅ Basado en `duracion_meses` de la membresía
- ✅ Campo readonly

### 📋 Validaciones

```php
// Validación en controller
- Si pago_pendiente = true:
  - monto_abonado → nullable
  - id_metodo_pago → nullable
  - fecha_pago → nullable
  
- Si pago_pendiente = false:
  - monto_abonado → required|numeric|min:0.01
  - id_metodo_pago → required|exists
  - fecha_pago → required|date
```

### 🔄 Flujos de Uso

#### Flujo 1: Inscripción con Pago Completo
1. Seleccionar cliente (filtrado a vencidos)
2. Seleccionar membresía → Precio se carga automáticamente
3. Seleccionar convenio (si aplica) → Descuento automático $5.000
4. Fecha de inicio se calcula automáticamente
5. Ingresar monto ($30.000 para mensual con descuento)
6. Elegir método de pago
7. Crear inscripción con pago (Estado: PAGADO)

#### Flujo 2: Inscripción con Pago Parcial
1. Pasos 1-4 igual al flujo 1
2. Ingresar monto parcial ($15.000)
3. Automáticamente aparece: "Cantidad de Cuotas", "Monto por Cuota"
4. Llenar cuotas y vencimiento
5. Crear inscripción con pago (Estado: PARCIAL)

#### Flujo 3: Inscripción con Pago Pendiente
1. Pasos 1-3 igual
2. Marcar checkbox "Dejar pago pendiente"
3. Sección de pago desaparece
4. Crear inscripción SIN pago
5. Después registrar pago en módulo de Pagos

### 🗄️ Base de Datos

**Tabla `inscripciones`** - Sin cambios
**Tabla `pagos`** - Campos nuevos:
- `cantidad_cuotas` (int)
- `numero_cuota` (int)
- `monto_cuota` (decimal)
- `fecha_vencimiento_cuota` (date)

### 📝 Campos del Formulario

| Campo | Tipo | Requerido | Notas |
|-------|------|-----------|-------|
| Cliente | Select | Sí | Solo vencidos, expandido |
| Membresía | Select | Sí | Carga precio AJAX |
| Fecha Inicio | Date | Sí | Editable |
| Fecha Vencimiento | Date | No | Auto-calculado, readonly |
| Convenio | Select | No | Auto-descuento si mensual |
| Descuento Adicional | Number | No | Suma al descuento convenio |
| Motivo Descuento | Select | No | Auto-llena por convenio |
| Pago Pendiente | Checkbox | No | Oculta sección pago |
| Fecha Pago | Date | Condicional | Requerido si NO pendiente |
| Monto Abonado | Number | Condicional | Requerido si NO pendiente |
| Método Pago | Select | Condicional | Requerido si NO pendiente |
| Cantidad Cuotas | Number | No | Solo si pago parcial |
| Vencimiento Cuota | Date | No | Solo si cuotas |

### 🚀 Endpoints API Utilizados

- `GET /api/membresias/{id}` → Obtener precio
- `POST /api/inscripciones/calcular` → (Deprecado) antes se usaba, ahora cálculo local

### 💻 JavaScript Events

```javascript
- idMembresia.addEventListener('change', cargarPrecioMembresia)
- idMembresia.addEventListener('change', calcularVencimiento)
- fechaInicio.addEventListener('change', calcularVencimiento)
- idConvenio.addEventListener('change', manejarCambioConvenio)
- pagoPendiente.addEventListener('change', manejarPagoPendiente)
- montoAbonado.addEventListener('input', validarPagoCompleto)
- cantidadCuotas.addEventListener('change', calcularMontoCuota)
```

### 🎨 Estilos

- Secciones con border azul izquierdo
- Animación de aparición para campos condicionales
- Resumen de precios en fondo gris
- Colores de cards: primary (cliente), info (fechas), warning (descuentos), success (pago)

### ✅ Testing Recomendado

1. [ ] Crear inscripción con convenio INACAP → Debe descontar $5.000 auto
2. [ ] Crear inscripción con pago parcial → Debe mostrar cuotas
3. [ ] Crear inscripción con pago pendiente → No debe crear Pago
4. [ ] Verificar cálculo de vencimiento según duración membresía
5. [ ] Verificar auto-llenado de motivo descuento según convenio
6. [ ] Verificar que motivo se limpia si se quita convenio

### 🔧 Próximos Pasos (Sugerencias)

- [ ] Agregar validación de cuotas mínimas según monto
- [ ] Integrar con sistema de notificaciones para pagos pendientes
- [ ] Permitir edición de inscripción para cambiar pago
- [ ] Reportes de cuotas vencidas por pagar
