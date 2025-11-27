# 🧪 GUÍA INTERACTIVA - TESTING DE BOTONES MÓDULO PAGOS

**Fecha:** 27 de noviembre de 2025

---

## 📌 INTRODUCCIÓN

Esta guía proporciona instrucciones paso a paso para verificar que cada botón funciona correctamente en el módulo de pagos.

**Requisitos:**
- ✅ Sistema ejecutándose en `http://127.0.0.1:8000`
- ✅ Usuario autenticado con permisos de admin
- ✅ Datos de prueba disponibles (clientes, inscripciones, métodos de pago)
- ✅ JavaScript habilitado en navegador

---

## 🎯 TESTING SECUENCIAL

### PASO 1: VERIFICAR LISTADO DE PAGOS

**URL:** `http://127.0.0.1:8000/admin/pagos`

#### ✅ Test 1.1: Botón "Nuevo Pago"
```
Acción: Hacer click en botón "Nuevo Pago" (verde, arriba a la derecha)
Esperado: Navegar a formulario de crear pago
Verificar:
  ☐ URL cambia a /admin/pagos/create
  ☐ Formulario muestra 3 secciones (Paso 1, 2, 3)
  ☐ Campo de inscripción está vacío
  ☐ Botón "Registrar Pago" está deshabilitado (gris)
```

#### ✅ Test 1.2: Botón "Buscar" (Filtros)
```
Acción: 
  1. Hacer click en encabezado "Filtros de Búsqueda" para expandir
  2. Ingresar nombre de cliente en campo "Cliente"
  3. Hacer click en botón "Buscar"
Esperado: Tabla se filtra mostrando solo pagos del cliente buscado
Verificar:
  ☐ Tabla muestra solo registros coincidentes
  ☐ URL contiene parámetro ?cliente=...
  ☐ Cantidad de pagos se reduce
```

#### ✅ Test 1.3: Botón "Limpiar"
```
Acción:
  1. Estar en página con filtros aplicados (del test anterior)
  2. Hacer click en botón "Limpiar"
Esperado: Todos los filtros se resetean, tabla muestra todos los pagos
Verificar:
  ☐ URL vuelve a /admin/pagos (sin parámetros)
  ☐ Tabla muestra todos los registros nuevamente
  ☐ Campos de filtro están vacíos
```

#### ✅ Test 1.4: Botón "Ver" (Ojo)
```
Acción: En cualquier fila de la tabla, hacer click en botón "Ver" (ojo azul)
Esperado: Navegar a página de detalles del pago
Verificar:
  ☐ URL cambia a /admin/pagos/{id}
  ☐ Se muestra información completa del pago
  ☐ Se muestran detalles de inscripción/cliente
  ☐ Se muestra resumen de pagos
```

#### ✅ Test 1.5: Botón "Editar" (Lápiz)
```
Acción: En cualquier fila, hacer click en botón "Editar" (lápiz amarillo)
Esperado: Abrir formulario de edición con datos precargados
Verificar:
  ☐ URL cambia a /admin/pagos/{id}/edit
  ☐ Campos muestran valores actuales del pago
  ☐ Información de inscripción es de solo lectura
  ☐ Botón "Guardar Cambios" está habilitado
```

#### ✅ Test 1.6: Botón "Eliminar" (Papelera)
```
Acción: En cualquier fila, hacer click en botón "Eliminar" (papelera roja)
Esperado: Mostrar confirmación antes de eliminar
Verificar:
  ☐ Aparece diálogo confirm() con mensaje "¿Eliminar este pago?"
  ☐ Si cancela: pago NO se elimina, permanece en tabla
  ☐ Si confirma: pago se elimina, página recarga sin registro
```

---

### PASO 2: CREAR NUEVO PAGO

**URL:** `http://127.0.0.1:8000/admin/pagos/create`

#### ✅ Test 2.1: Seleccionar Inscripción
```
Acción:
  1. Hacer click en campo "Buscar Inscripción"
  2. Escribir al menos 2 caracteres (nombre cliente o membresía)
  3. Seleccionar una inscripción de las sugerencias
Esperado: Cargar información de saldo y mostrar pasos 2 y 3
Verificar:
  ☐ Aparece lista dropdown con inscripciones coincidentes
  ☐ Cada opción muestra saldo disponible
  ☐ Al seleccionar, se cargan datos del saldo
  ☐ Aparecen cajas de "Total a Pagar", "Ya Abonado", "Saldo Pendiente"
  ☐ Paso 2 y Paso 3 se hacen visibles
```

#### ✅ Test 2.2: Radio "Pago Simple"
```
Acción:
  1. Asegurar que "Pago Simple o Abono" está seleccionado (por defecto)
  2. Verificar sección de cuotas
Esperado: Sección de cuotas está oculta
Verificar:
  ☐ No aparece campo "Cantidad de Cuotas"
  ☐ No aparece "Vista Previa de Cuotas"
  ☐ Cantidad de cuotas input está vacío
```

#### ✅ Test 2.3: Radio "Plan de Cuotas"
```
Acción:
  1. Hacer click en radio "Plan de Cuotas"
Esperado: Mostrar sección de cuotas con configuración
Verificar:
  ☐ Aparece campo "Cantidad de Cuotas" con valor 2 (por defecto)
  ☐ Aparece campo "Monto por Cuota" (solo lectura)
  ☐ Aparece campo "Vencimiento 1ª Cuota"
  ☐ Aparece "Vista Previa de Cuotas"
```

#### ✅ Test 2.4: Validación de Monto
```
Acción:
  1. Ingresar monto mayor al saldo pendiente
  2. Observar botón "Registrar Pago"
Esperado: Botón permanece deshabilitado, campo se marca como inválido
Verificar:
  ☐ Monto input tiene color rojo (borde/fondo)
  ☐ Botón "Registrar Pago" sigue deshabilitado (gris)
  ☐ Mensaje de error aparece si aplica

Acción alternativa:
  1. Ingresar monto dentro del rango válido
Esperado: Botón se habilita
Verificar:
  ☐ Monto input vuelve a color normal
  ☐ Botón "Registrar Pago" se habilita (azul)
```

#### ✅ Test 2.5: Preview de Cuotas
```
Acción:
  1. Tener seleccionado "Plan de Cuotas"
  2. Ingresar monto: 100000
  3. Cantidad de cuotas: 4
  4. Verificar preview
Esperado: Se muestra tabla con 4 cuotas de 25000 cada una
Verificar:
  ☐ Preview muestra "Cuota #1", "#2", "#3", "#4"
  ☐ Cada cuota muestra monto: $25.000
  ☐ Cada cuota muestra fecha de vencimiento incrementada por mes
  ☐ Cambiar cantidad recalcula preview en tiempo real
```

#### ✅ Test 2.6: Botón "Limpiar"
```
Acción:
  1. Rellenar todos los campos del formulario
  2. Hacer click en botón "Limpiar"
Esperado: Todos los campos se vacían, formulario vuelve a estado inicial
Verificar:
  ☐ Inscripción se borra
  ☐ Monto se borra
  ☐ Método de pago se borra
  ☐ Fechas se limpian
  ☐ Observaciones se vacían
  ☐ Pasos 2 y 3 se ocultan
  ☐ Botón "Registrar Pago" se deshabilita nuevamente
```

#### ✅ Test 2.7: Botón "Registrar Pago"
```
Acción:
  1. Completar formulario correctamente:
     - Inscripción: seleccionada
     - Monto: válido (entre 0.01 y saldo disponible)
     - Método de pago: seleccionado
     - Fecha: hoy o anterior
  2. Hacer click en botón "Registrar Pago" (ahora debe estar azul/habilitado)
Esperado: Pago se crea y redirige a listado con mensaje de éxito
Verificar:
  ☐ URL cambia a /admin/pagos
  ☐ Aparece mensaje verde "¡Éxito! Pago registrado correctamente"
  ☐ Nuevo pago aparece en tabla
  ☐ Saldo de la inscripción se actualiza
```

#### ✅ Test 2.8: Botón "Cancelar"
```
Acción:
  1. Rellenar parcialmente el formulario
  2. Hacer click en botón "Cancelar"
Esperado: Regresar a listado SIN guardar datos
Verificar:
  ☐ URL cambia a /admin/pagos
  ☐ Formulario se abandona (datos no se guardan)
  ☐ Pago no aparece en tabla
```

---

### PASO 3: EDITAR PAGO

**URL:** `http://127.0.0.1:8000/admin/pagos/{id}/edit`

#### ✅ Test 3.1: Formulario Prerellenado
```
Acción: Navegar a edit de un pago existente
Esperado: Campos muestran valores actuales
Verificar:
  ☐ Fecha de pago muestra fecha actual del pago
  ☐ Método de pago muestra método seleccionado
  ☐ Monto abonado muestra monto actual
  ☐ Referencia de pago muestra valor actual (si existe)
  ☐ Observaciones muestran contenido actual (si existen)
```

#### ✅ Test 3.2: Botón "Guardar Cambios"
```
Acción:
  1. Cambiar monto abonado a un valor diferente
  2. Hacer click en "Guardar Cambios"
Esperado: Cambios se guardan y se redirige a detalles
Verificar:
  ☐ URL cambia a /admin/pagos/{id}
  ☐ Mensaje de éxito aparece
  ☐ Monto se ha actualizado en la vista
  ☐ Campo "Actualizado" refleja timestamp nuevo
```

#### ✅ Test 3.3: Botón "Ver Detalles"
```
Acción: Hacer click en botón "Ver Detalles" (azul con ojo)
Esperado: Navegar a página de detalles del pago
Verificar:
  ☐ URL cambia a /admin/pagos/{id}
  ☐ Se muestra información completa del pago
  ☐ Botones de acción están disponibles
```

#### ✅ Test 3.4: Botón "Volver"
```
Acción: Hacer click en botón "Volver" (esquina superior derecha)
Esperado: Regresar a listado sin guardar cambios
Verificar:
  ☐ URL cambia a /admin/pagos
  ☐ Cambios realizados NO se guardan
```

---

### PASO 4: VER DETALLES DE PAGO

**URL:** `http://127.0.0.1:8000/admin/pagos/{id}`

#### ✅ Test 4.1: Información Mostrada
```
Acción: Navegar a página de detalles
Esperado: Se muestra información completa del pago
Verificar:
  ☐ Resumen con 3 cajas: Monto Abonado, Fecha, % Pagado
  ☐ Método de pago con icono apropiado
  ☐ Información de inscripción (cliente, membresía)
  ☐ Resumen de pagos (total, abonado, pendiente, cantidad)
  ☐ Si es plan de cuotas: tabla de cuotas relacionadas
  ☐ Historial de todos los pagos de la inscripción
```

#### ✅ Test 4.2: Botón "Editar"
```
Acción: Hacer click en botón "Editar" (amarillo con lápiz)
Esperado: Navegar a formulario de edición
Verificar:
  ☐ URL cambia a /admin/pagos/{id}/edit
  ☐ Formulario prerellenado con datos actuales
```

#### ✅ Test 4.3: Botón "Eliminar Pago"
```
Acción: Hacer click en botón "Eliminar Pago" (rojo con papelera)
Esperado: Confirmar antes de eliminar
Verificar:
  ☐ Diálogo confirm() muestra: "¿Estás seguro? Esta acción no puede revertirse."
  ☐ Si cancela: pago NO se elimina
  ☐ Si confirma: pago se elimina y redirige a listado
```

#### ✅ Test 4.4: Botón "Ver Inscripción"
```
Acción: Hacer click en botón "Ver Inscripción" (en sección de inscripción)
Esperado: Navegar a página de detalles de inscripción
Verificar:
  ☐ URL cambia a /admin/inscripciones/{id}
  ☐ Se muestra información de la inscripción relacionada
  ☐ Se puede volver al pago desde allí
```

#### ✅ Test 4.5: Link de Cliente
```
Acción: Hacer click en nombre del cliente (link azul)
Esperado: Navegar a página de cliente
Verificar:
  ☐ URL cambia a /admin/clientes/{id}
  ☐ Se muestra información del cliente
```

---

## 🔍 VALIDACIONES A VERIFICAR

### Validaciones HTML5 (Frontend)
```javascript
☐ Campo fecha_pago: No permite futuro
☐ Campo monto_abonado: Solo números decimales
☐ Campo cantidad_cuotas: Solo números enteros 2-12
☐ Campos requeridos: Impiden envío si están vacíos
☐ Select2: Busca con AJAX mientras escribe
```

### Mensajes de Error
```
☐ "Inscripción no existe" - Si intentas crear con inscripción inválida
☐ "Monto debe ser mayor a 0" - Si ingresas 0 o negativo
☐ "Monto no puede exceder saldo pendiente" - Si excede saldo
☐ "Método de pago requerido" - Si no seleccionas método
☐ "Cantidad de cuotas debe estar entre 2 y 12" - Si intentas cuota inválida
☐ "Esta referencia ya existe para este método" - Si referencia es duplicada
```

---

## 📊 MATRIZ DE TESTING RÁPIDO

```
Botón                      | Ubicación  | Estado | Notas
---------------------------|------------|--------|-------------------
Nuevo Pago                 | INDEX Top  | ✅     | Verde, navega
Buscar                     | INDEX Filt | ✅     | Aplica filtros
Limpiar                    | INDEX Filt | ✅     | Limpia filtros
Ver (Ojo)                  | INDEX Tbl  | ✅     | Azul, por fila
Editar (Lápiz)             | INDEX Tbl  | ✅     | Amarillo, por fila
Eliminar (Papelera)        | INDEX Tbl  | ✅     | Rojo, con confirm
Volver (CREATE Top)        | CREATE Top | ✅     | Gris, navega
Cancelar (CREATE Bot)      | CREATE Bot | ✅     | Gris, navega
Limpiar (CREATE Bot)       | CREATE Bot | ✅     | Naranja, reset
Registrar Pago             | CREATE Bot | ✅     | Azul, submit
Pago Simple (Radio)        | CREATE P2  | ✅     | Oculta cuotas
Plan de Cuotas (Radio)     | CREATE P2  | ✅     | Muestra cuotas
Ver Detalles (EDIT Top)    | EDIT Top   | ✅     | Azul, navega
Volver (EDIT Top)          | EDIT Top   | ✅     | Gris, navega
Guardar Cambios (EDIT Bot) | EDIT Bot   | ✅     | Azul, submit
Editar (SHOW Top)          | SHOW Top   | ✅     | Amarillo, navega
Volver (SHOW Top)          | SHOW Top   | ✅     | Gris, navega
Ver Inscripción            | SHOW Info  | ✅     | Azul, navega
Eliminar Pago (SHOW Bot)   | SHOW Bot   | ✅     | Rojo, con confirm
```

---

## 🎯 CASOS ESPECIALES

### Test: Crear Pago con Cuotas
```
1. Seleccionar inscripción
2. Ingresar monto: 300000
3. Cambiar a "Plan de Cuotas"
4. Cantidad de cuotas: 6
5. Establecer vencimiento 1ª cuota: 01/01/2026
6. Registrar

Verificar:
  ☐ Se crea un pago con es_plan_cuotas = true
  ☐ numero_cuota = 1
  ☐ cantidad_cuotas = 6
  ☐ monto_abonado = 50000 (300000/6)
  ☐ fecha_vencimiento_cuota = 01/01/2026
  ☐ En vista SHOW aparece tabla de "Plan de Cuotas"
```

### Test: Referencia Única por Método
```
1. Crear pago con método "Efectivo" y referencia "REF001"
2. Intentar crear otro pago con mismo método y referencia
3. Debe fallar con error "Esta referencia ya existe"

Pero:
1. Crear pago con método "Efectivo" y referencia "REF001"
2. Crear otro pago con método "Transferencia" y referencia "REF001"
3. Debe ser permitido (referencias únicas por método)
```

### Test: Validación de Fecha
```
1. Intentar crear pago con fecha futura
2. Campo debe rechazar o formulario no debe enviar
3. Intenta hoy o fecha anterior - debe funcionar
```

---

## 📋 REPORTE FINAL

Después de completar todos los tests, crear reporte:

```
TESTING COMPLETADO: [FECHA]
Total de Tests: 40+
Todos Pasados: ✅ SÍ / ❌ NO

Botones Funcionales: 23/23
Checkboxes Funcionales: 2/2
Rutas Confirmadas: 7/7
APIs Verificadas: 2/2

Problemas Encontrados:
  - [Lista aquí si los hay]

Notas Adicionales:
  - [Agregar observaciones]

Firma: ________________
Fecha: ________________
```

---

**Guía de Testing Generada:** 27/11/2025  
**Versión:** 1.0  
**Estado:** Listo para usar

