# 📋 UUID vs ID - Guía de Uso

## 🎯 Resumen Rápido

| Aspecto | ID | UUID |
|--------|----|----|
| **Tipo** | `int` (1-3) | `string` (36 caracteres) |
| **Uso Interno** | Queries, relaciones, BD | URLs, rutas públicas |
| **Visibilidad** | Nunca en URLs | Siempre en URLs |
| **Seguridad** | Predecible | Impredecible, aleatorio |
| **Modelo.id** | Identificador único interno | No existe en rutas |
| **Modelo.uuid** | No existe | Identificador único público |

---

## 🏗️ Estructura de Datos

### Tablas con UUID

```
inscripciones:     id (PK), uuid (unique) ✅
pagos:             id (PK), uuid (unique) ✅
clientes:          id (PK), uuid (unique) ✅
membresias:        id (PK), uuid (unique) ✅
convenios:         id (PK), uuid (unique) ✅
```

### Ejemplo de Fila

```
id      | uuid                                 | nombre
--------|--------------------------------------|--------
1       | a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6 | Juan
2       | b2c3d4e5-f6g7-h8i9-j0k1-l2m3n4o5p6r7 | María
```

---

## 🔄 Cuándo Usar Cada Uno

### ✅ Usa `ID` para:

1. **Queries en Base de Datos**
   ```php
   // Correcto - filtrar por ID
   Inscripcion::where('id_cliente', $cliente->id)->get();
   
   // Correcto - relaciones
   Pago::whereHas('inscripcion', fn($q) => $q->where('id', 123))->get();
   ```

2. **Relaciones en Modelos**
   ```php
   // En migraciones
   $table->foreignId('id_cliente')->constrained('clientes');
   $table->foreignId('id_inscripcion')->constrained('inscripciones');
   ```

3. **API Interna (Backend to Backend)**
   ```php
   // Endpoint interno
   POST /api/internal/inscripciones/1/pagos
   ```

4. **Logs y Debugging**
   ```php
   Log::info("Inscripción ID: {$inscripcion->id}");
   ```

### ✅ Usa `UUID` para:

1. **URLs y Rutas Públicas**
   ```blade
   <!-- Correcto -->
   <a href="{{ route('admin.inscripciones.show', $inscripcion) }}">Ver</a>
   <!-- Genera: /admin/inscripciones/a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6 -->
   
   <!-- Incorrecto -->
   <a href="{{ route('admin.inscripciones.show', $inscripcion->id) }}">Ver</a>
   <!-- Genera: /admin/inscripciones/1 (predecible) -->
   ```

2. **APIs Públicas**
   ```php
   // Endpoint público
   GET /api/inscripciones/a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6
   ```

3. **Formularios y Vistas**
   ```blade
   <!-- Correcto -->
   <input type="hidden" name="inscripcion_uuid" value="{{ $inscripcion->uuid }}">
   
   <!-- Incorrecto -->
   <input type="hidden" name="inscripcion_id" value="{{ $inscripcion->id }}">
   ```

4. **URLs en Emails o Exportaciones**
   ```php
   $url = "https://app.com/inscripciones/{$inscripcion->uuid}/pdf";
   ```

---

## 🎛️ Configuración en Modelos

### Activar Resolución Automática de UUID

En cada modelo que tenga UUID:

```php
class Inscripcion extends Model
{
    // ... otros atributos ...

    /**
     * Usar UUID para resolución de rutas
     * Laravel automáticamente resolverá {inscripcion} usando uuid en las rutas
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // Generar UUID automáticamente al crear
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }
}
```

**Modelos con esta configuración:**
- ✅ Inscripcion
- ✅ Pago
- ✅ Cliente
- ✅ Membresia
- ✅ Convenio

---

## 🔗 Rutas Configuradas

```php
// routes/web.php
Route::resource('inscripciones', InscripcionController::class);
// ✅ Automáticamente usa UUID en:
// GET  /inscripciones/{inscripcion}          → show (uuid)
// PUT  /inscripciones/{inscripcion}          → update (uuid)
// DELETE /inscripciones/{inscripcion}        → destroy (uuid)
```

---

## 📝 Ejemplos Prácticos

### ✅ Correcto

```blade
<!-- Vista: resources/views/admin/inscripciones/index.blade.php -->
@foreach($inscripciones as $inscripcion)
    <tr>
        <td>{{ $inscripcion->id }}</td>  <!-- Mostrar ID si lo necesitas -->
        <td>
            <!-- Usar UUID en rutas -->
            <a href="{{ route('admin.inscripciones.show', $inscripcion) }}">
                Ver Detalles
            </a>
        </td>
    </tr>
@endforeach
```

```php
// Controlador: app/Http/Controllers/Admin/InscripcionController.php
public function show(Inscripcion $inscripcion)  // Laravel automáticamente resuelve por UUID
{
    // $inscripcion está completamente cargado y es seguro
    // $inscripcion->id está disponible para queries
    // $inscripcion->uuid está en la URL
    return view('admin.inscripciones.show', compact('inscripcion'));
}

public function update(Request $request, Inscripcion $inscripcion)  // UUID en URL
{
    // Usar ID para queries internas
    Pago::where('id_inscripcion', $inscripcion->id)->update([...]);
    
    return redirect()->route('admin.inscripciones.show', $inscripcion);  // UUID en nueva ruta
}
```

### ❌ Incorrecto

```blade
<!-- ❌ Nunca exponer ID en URLs -->
<a href="/admin/inscripciones/{{ $inscripcion->id }}">Ver</a>
<!-- Predecible: /admin/inscripciones/1, /admin/inscripciones/2 -->

<!-- ❌ Usar route() en lugar de URL hardcoded -->
<a href="/admin/inscripciones/{{ $inscripcion->uuid }}">Ver</a>
<!-- Mejor: usar route() -->
```

---

## 🔐 Ventajas de esta Configuración

| Ventaja | Beneficio |
|---------|-----------|
| **Seguridad** | No se puede adivinar IDs de otros clientes |
| **URLs Únicas** | Cada inscripción tiene una URL única y segura |
| **Auditoría** | Fácil rastrear qué se accedió |
| **Escalabilidad** | El UUID es único incluso con múltiples instancias |
| **Compatibilidad** | Funciona con UUID v4 estándar |

---

## 🐛 Debugging

### Ver ambos IDs

```php
// En tinker o en logs
$inscripcion = Inscripcion::find(1);

// Ambos están disponibles
dd($inscripcion->id);      // 1 (interno)
dd($inscripcion->uuid);    // a1b2c3d4-... (público)

// En rutas
route('admin.inscripciones.show', $inscripcion)  // Usa UUID automáticamente
```

### Verificar Configuración

```php
// Verificar que el modelo tiene getRouteKeyName configurado
$inscripcion = new Inscripcion();
echo $inscripcion->getRouteKeyName();  // Debe mostrar: uuid
```

---

## 📊 Cheat Sheet

```
┌─────────────────────┬──────────────────┬──────────────────┐
│ Contexto            │ Usa              │ Ejemplo          │
├─────────────────────┼──────────────────┼──────────────────┤
│ URLs en blade       │ UUID             │ route(..., $obj) │
│ Queries (BD)        │ ID               │ where('id_X', $) │
│ Relaciones          │ ID               │ belongsTo(...)   │
│ APIs públicas       │ UUID             │ /api/obj/{uuid}  │
│ APIs internas       │ ID               │ /api/int/{id}    │
│ Mostrar en tabla    │ ID o UUID        │ {{$obj->id/uuid}}│
│ Logs internos       │ ID               │ Log::info("ID:") │
│ Emails a clientes   │ UUID             │ URLs en email    │
└─────────────────────┴──────────────────┴──────────────────┘
```

---

## ✅ Checklist de Implementación

- [x] Todos los modelos tienen UUID en BD
- [x] getRouteKeyName() configurado en modelos
- [x] boot() genera UUID automáticamente
- [x] Rutas usan parámetro singular: `{inscripcion}` (resuelve por UUID)
- [x] Vistas usan `route()` con objeto (pasa UUID)
- [x] Controladores reciben objeto inyectado (resuelto por UUID)
- [x] Queries internas usan `->id`
- [x] URLs públicas muestran UUID

---

## 🚀 Próximos Pasos

Si necesitas:

1. **Cambiar de ID a UUID en URLs existentes**
   - Ya está hecho en Inscripcion, Pago, Cliente, Membresia, Convenio

2. **Agregar UUID a otro modelo**
   ```php
   // Crear migración
   Schema::table('tabla', function (Blueprint $table) {
       $table->uuid('uuid')->nullable()->unique()->after('id');
   });
   
   // Llenar UUIDs existentes
   Model::whereNull('uuid')->each(fn($m) => $m->update(['uuid' => Str::uuid()]));
   
   // Actualizar modelo
   public function getRouteKeyName() { return 'uuid'; }
   ```

3. **Migrar APIs existentes de ID a UUID**
   - Mantener compatibilidad dual por ahora
   - Documentar cambio
   - Deprecar endpoints con ID en el futuro

---

**Última actualización:** 26 de noviembre de 2025
