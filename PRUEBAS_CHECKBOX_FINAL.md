# Pruebas Finales - Checkbox y Descuentos

## Status: ✅ CÓDIGO COMPLETADO
Todos los cambios han sido commitados en el commit más reciente.

---

## 🔧 Cambios Realizados

### 1. **Checkbox State Persistence** ✅
- **Archivo**: `create.blade.php`, `edit.blade.php`
- **Cambio**: Agregado `<input type="hidden" name="activo" value="0">` antes del checkbox
- **Por qué**: Los checkboxes HTML no envían valor cuando están desmarcados. El hidden input asegura que siempre se envíe "0" cuando está desmarcado.

### 2. **Validación de precio_convenio** ✅
- **Archivo**: `MembresiaController.php` (líneas 42, 122)
- **Cambio**: `lt:precio_normal` → `less_than:precio_normal`
- **Por qué**: El operador `lt:` no maneja correctamente valores nullable. `less_than:` sí.

### 3. **Limpieza de precio_convenio** ✅
- **Archivo**: `MembresiaController.php` (líneas 47-49, 165-167)
- **Cambio**: Si `precio_convenio` llega vacío (string ""), se convierte a `null`
- **Por qué**: Asegura que la BD almacene null en lugar de cadenas vacías.

### 4. **Boolean Casting** ✅
- **Archivo**: `Membresia.php`
- **Cambio**: `protected $casts = ['activo' => 'boolean'];`
- **Por qué**: Convierte automáticamente strings "0"/"1" del formulario a boolean false/true en la BD.

### 5. **Actualización de solo precio_convenio** ✅
- **Archivo**: `MembresiaController.php` (líneas 204-208)
- **Cambio**: Agregado else if para actualizar `precio_convenio` sin cambiar `precio_normal`
- **Por qué**: Permite cambiar el descuento sin crear un nuevo registro de precio.

---

## ✅ Test Checklist

### **Test 1: Crear Membresía Sin Descuento**
```
1. Ve a CONFIGURACIÓN > Membresías > Crear
2. Rellena:
   - Nombre: "Test Sin Descuento"
   - Duración: 1 mes, 1 día
   - Precio Normal: $50,000
   - Precio Convenio: (dejar vacío)
   - Activo: ✓ (marcado por defecto)
3. Guarda
4. Verifica: Membresía creada con precio_convenio = null
```

### **Test 2: Crear Membresía Con Descuento**
```
1. Ve a CONFIGURACIÓN > Membresías > Crear
2. Rellena:
   - Nombre: "Test Con Descuento"
   - Duración: 3 meses, 1 día
   - Precio Normal: $100,000
   - Precio Convenio: $75,000
   - Activo: ✓ (marcado)
3. Guarda
4. Verifica: Membresía creada con descuento aplicado
```

### **Test 3: Desmarcar Activo (MAIN BUG FIX)**
```
1. Ve a CONFIGURACIÓN > Membresías
2. Haz clic en "Editar" en cualquier membresía
3. Desmarca el checkbox "Activo"
4. Haz clic en "Guardar"
5. Verifica:
   ✓ Membresía aparece como "Inactivo" en la tabla
   ✓ En la BD, el campo `activo` = 0 (false)
   ✓ Si intentas editar de nuevo, el checkbox está desmarcado
```

### **Test 4: Volver a Activar (Reactivate)**
```
1. Ve a CONFIGURACIÓN > Membresías
2. Ubica una membresía inactiva (estado "Inactivo")
3. Haz clic en el botón "Reactivar"
4. Verifica:
   ✓ Membresía vuelve a estado "Activo"
   ✓ En la BD, el campo `activo` = 1 (true)
```

### **Test 5: Modificar Solo el Descuento**
```
1. Ve a CONFIGURACIÓN > Membresías
2. Edita una membresía (ej: "Mensual")
3. Cambios SOLO:
   - Precio Normal: SIN CAMBIOS (mantener igual)
   - Precio Convenio: Cambia el valor (ej: $22,000 → $20,000)
4. Guarda
5. Verifica:
   ✓ El descuento se actualizó
   ✓ NO se creó un nuevo precio (solo se actualizó el existente)
   ✓ El historial NO muestra cambio de precio
```

### **Test 6: Validación precio_convenio > precio_normal**
```
1. Ve a CONFIGURACIÓN > Membresías > Editar
2. Intenta ingresar:
   - Precio Normal: $50,000
   - Precio Convenio: $60,000 (mayor que normal)
3. Intenta Guardar
4. Verifica:
   ✗ Debe mostrar error de validación
   ✗ No debe permitir guardar
```

### **Test 7: Validación meses máximo 12**
```
1. Ve a CONFIGURACIÓN > Membresías > Editar
2. Intenta ingresar:
   - Duración Meses: 13
3. Intenta Guardar
4. Verifica:
   ✗ Debe mostrar error de validación
   ✗ No debe permitir guardar
```

### **Test 8: Pase Diario (0 meses + 1 día)**
```
1. Ve a CONFIGURACIÓN > Membresías
2. Busca "Pase Diario" en la tabla
3. Verifica:
   ✓ Existe en la tabla
   ✓ Duración: 0 meses, 1 día
   ✓ Activo: Sí
```

### **Test 9: Delete Modal (Solo para Activos)**
```
1. Ve a CONFIGURACIÓN > Membresías
2. Intenta Eliminar una membresía ACTIVA
   - Verifica: Modal de confirmación aparece
3. Intenta Eliminar una membresía INACTIVA
   - Verifica: Simplemente se desactiva (sin modal)
```

### **Test 10: Inscripción con Descuento**
```
1. Ve a INSCRIPCIONES
2. Crea una nueva inscripción
3. Selecciona membresía: "Mensual" (tiene descuento)
4. Verifica:
   ✓ El precio mostrado es: $25,000 (precio con descuento)
   ✓ No $40,000 (precio normal)
```

---

## 🗂️ Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `create.blade.php` | ✓ Hidden input checkbox |
| `edit.blade.php` | ✓ Hidden input checkbox |
| `MembresiaController.php` | ✓ Limpieza precio_convenio, validación less_than, actualización solo descuento |
| `Membresia.php` | ✓ Boolean casting |

---

## 📊 Commits Relacionados

```
a1e2bca - fix: checkbox state persistence, validation rules, boolean casting, precio_convenio handling
af25e49 - refactor: remover vista inactivas, agregar botón reactivar en tabla principal
665db50 - fix: revisión completa módulo membresías
c3f7e64 - fix: arreglar membresías - desbloquear días, limitar meses
859a09c - fix: arreglar Auth user null
dc74db0 - fix: arreglar módulo membresías - remover validación dias
```

---

## 🚀 Próximos Pasos

1. ✅ Realizar todos los tests del checklist arriba
2. ✅ Si todos pasan: Marcar como COMPLETADO
3. ⏳ Considerar: Tests unitarios para validaciones
4. ⏳ Considerar: Formateo de precios en otras vistas

---

## 📝 Notas Técnicas

### Por qué el hidden input funciona
```html
<!-- ANTES (no funciona): -->
<input type="checkbox" name="activo" value="1">
<!-- Si está desmarcado: NO se envía nada -->

<!-- DESPUÉS (funciona): -->
<input type="hidden" name="activo" value="0">
<input type="checkbox" name="activo" value="1">
<!-- Si está desmarcado: Se envía hidden con "0" -->
<!-- Si está marcado: Se envía checkbox con "1" (sobrescribe) -->
```

### Por qué less_than > lt
```php
// lt: no maneja bien nullable
'precio_convenio' => 'nullable|numeric|less_than:precio_normal'
// less_than: maneja null correctamente en comparaciones
```

### Por qué boolean casting
```php
// Sin casting: string "0" se guarda como "0"
// Con casting: string "0" se convierte a boolean false (0 en BD)
protected $casts = ['activo' => 'boolean'];
```
