# GUÍA DE TESTING - PRECIO BOX EN PASO 2

## Resumen de Cambios Realizados

### 1. **Mejora de `actualizarPrecio()` - Debugging Mejorado**
   - Añadido console logging detallado con emojis para rastrear ejecución
   - 🔍 Verifica que membresía esté seleccionada
   - 🔗 Construye URL correctamente: `/api/precio-membresia/{id}?convenio={id}`
   - 📡 Registra estado de respuesta HTTP
   - ✅ Confirma recepción de datos y actualización de elementos
   - ❌ Captura y registra errores detallados
   - 📦 Muestra/oculta precio-box según resultado

### 2. **Mejora de `actualizarPrecioFinal()` - Cálculos Completos**
   - 💵 Calcula precio final = precio_convenio - descuento_manual
   - Guarda precio final en campo oculto `precio-final-oculto` para validaciones
   - Formatea moneda con localización CLP
   - Validaciones robustas de elementos

### 3. **Estructura HTML Mejorada**
   - Campo oculto para almacenar precio final: `<input type="hidden" id="precio-final-oculto" value="0">`
   - Precio-box con estructura completa:
     - Precio Base (sin descuentos)
     - Precio Convenio (con descuento de convenio)
     - Descuento Manual (adicional)
     - Precio Final (total a pagar)
     - Fecha de Término (calculada)

### 4. **Event Listeners Activos**
   - `id_membresia` change → `actualizarPrecio()` + `actualizarResumenPaso3()`
   - `fecha_inicio` change → `actualizarPrecio()` + `actualizarResumenPaso3()`
   - `id_convenio` change → `actualizarPrecio()` + `actualizarResumenPaso3()`
   - `descuento_manual` change/input → `actualizarPrecioFinal()` + `actualizarResumenPaso3()`

## CÓMO TESTEAR

### Paso 1: Iniciar Servidor
```bash
php artisan serve --host=localhost --port=8000
```

### Paso 2: Abrir Navegador
```
http://localhost:8000/admin/clientes/create
```

### Paso 3: Ir a PASO 2
1. Completar PASO 1 (Datos del Cliente) con datos válidos
2. Click en botón "Siguiente" para ir a PASO 2

### Paso 4: Abrir Console del Navegador
- Presionar `F12` → Ir a pestaña **Console**
- Debe aparecer lista limpia al cargar

### Paso 5: Seleccionar Membresía
1. En el select "Membresía", seleccionar cualquier opción (ej: "Plan Básico")
2. **En la consola debe aparecer:**

```
🔍 Fetching precio para membresia: [ID] convenio: 
🔗 URL: /api/precio-membresia/[ID]
📡 Response status: 200
✅ Respuesta API: {precio_base: XXX, precio_final: XXX, duracion_dias: 30, nombre: "..."}
💰 Precios: {precioBase: XXX, precioConConvenio: XXX, duracionDias: 30}
📦 Mostrando precio-box
✅ Precio normal actualizado
✅ Precio convenio actualizado
✅ Fecha término actualizada: DD/MM/YYYY
💵 Calculando precio final: {precioConConvenio: XXX, descuentoManual: 0, precioTotal: XXX}
✅ Precio final guardado en campo oculto: XXX
```

3. **En la página debe aparecer:**
   - Tarjeta "Resumen de Precios" con:
     - Precio Base: $XXX
     - Convenio: $XXX
     - Descuento Manual: -$0
     - **Precio Final: $XXX** (destacado)
     - Fecha de Término: DD/MM/YYYY

### Paso 6: Pruebar Convenio
1. Seleccionar un Convenio en el select
2. **En la consola debe aparecer:**
```
🔍 Fetching precio para membresia: [ID] convenio: [CONV_ID]
🔗 URL: /api/precio-membresia/[ID]?convenio=[CONV_ID]
📡 Response status: 200
✅ Respuesta API: {precio_base: XXX, precio_final: YYYYY, duracion_dias: 30, nombre: "..."}
```
3. **El precio final debe cambiar** (generalmente menor debido al descuento)

### Paso 7: Probar Descuento Manual
1. En el campo "Descuento Manual ($)", ingresar un valor (ej: 5000)
2. **En la consola debe aparecer:**
```
💵 Calculando precio final: {precioConConvenio: XXXX, descuentoManual: 5000, precioTotal: YYYY}
✅ Precio final guardado en campo oculto: YYYY
```
3. **El precio final debe disminuir**

## CHECKLIST DE VALIDACIÓN

- [ ] Console abre sin errores críticos
- [ ] 🔍 Mensaje de fetch aparece cuando se selecciona membresía
- [ ] 🔗 URL correcta (sin `/admin/` en la ruta)
- [ ] 📡 Response status es 200
- [ ] ✅ Datos retornados del API
- [ ] 📦 Precio-box aparece en la página
- [ ] Precios se muestran correctamente en la tarjeta
- [ ] Cambiar convenio actualiza precios
- [ ] Cambiar descuento manual actualiza precio final
- [ ] Fecha de término se calcula correctamente
- [ ] Campo oculto `precio-final-oculto` tiene valor numérico

## SOLUCIÓN DE PROBLEMAS

### Si NO aparece el precio-box:
1. Abre Console (F12)
2. Busca ❌ Error
3. Verifica:
   - [ ] API retorna 404 → Base de datos no tiene precios
   - [ ] Error de fetch → Problema de red
   - [ ] Elemento precioBox NO encontrado → Problema HTML

### Si aparece pero sin precios:
1. Verifica en Console
2. Busca la sección 💰 Precios
3. Verifica que `precio_base` y `precio_final` no sean 0 o undefined

### Si el precio-box desaparece al cambiar convenio:
1. Verifica en Console si aparece error ❌
2. Puede ser que el API retorne error 404
3. Verifica que convenio_id sea válido en la BD

## API ENDPOINT

**Ruta:** `/api/precio-membresia/{membresia_id}`

**Parámetros:**
- `membresia_id`: ID de la membresía (requerido)
- `convenio`: ID del convenio (opcional)

**Respuesta exitosa (200):**
```json
{
  "precio_base": 50000,
  "precio_final": 45000,
  "duracion_dias": 30,
  "nombre": "Plan Básico"
}
```

**Respuesta error (404):**
```json
{
  "error": "Membresía no encontrada" o "Precio no encontrado"
}
```

## NOTAS IMPORTANTES

1. **Base de datos limpia**: Se ejecutó `migrate:fresh --seed`, así que solo hay datos de prueba del seeder
2. **Sin datos fake**: EnhancedTestDataSeeder fue removido
3. **Migraciones simplificadas**: Se eliminaron campos innecesarios
4. **Validaciones robustas**: Todo el flujo tiene manejo de errores

## SIGUIENTE PASO

Una vez validado el precio-box en PASO 2:
1. Ir a PASO 3
2. Verificar que el precio final se pase correctamente
3. Testear todos los tipos de pago (completo, parcial, pendiente, mixto)
4. Verificar validaciones por tipo de pago
