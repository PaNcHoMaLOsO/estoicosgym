# ⏸️ Sistema de Pausas - Guía Completa

## 🎯 Resumen Ejecutivo

El sistema de pausas permite que los clientes suspendan temporalmente su membresía sin perder su progreso. Se pueden pausar por 7, 14 o 30 días, y el sistema extiende automáticamente la fecha de vencimiento.

---

## 📊 Estructura de Datos

### Columnas en Tabla `inscripciones`

| Columna | Tipo | Propósito | Valores |
|---------|------|----------|--------|
| `pausada` | boolean | ¿Está pausada ahora? | true/false |
| `dias_pausa` | int | Duración de la pausa | 7, 14, 30 |
| `fecha_pausa_inicio` | datetime | Cuándo empezó | timestamp |
| `fecha_pausa_fin` | datetime | Cuándo termina | timestamp |
| `razon_pausa` | string | Motivo | texto libre |
| `pausas_realizadas` | int | Pausas usadas | 0-12 |
| `max_pausas_permitidas` | int | Máximo anual | default: 4 |
| `id_estado` | int | Estado actual | 2, 3, 4 (pausas) |

### Estados de Pausa

| Estado ID | Nombre | Duración |
|-----------|--------|----------|
| 2 | Pausada - 7 días | 7 días |
| 3 | Pausada - 14 días | 14 días |
| 4 | Pausada - 30 días | 30 días |

---

## 🔍 Cómo Funciona

### 1. Pausar una Membresía

```php
// En el controlador
$inscripcion->pausar(7, 'Viaje de negocios');
// O
$inscripcion->pausar(14, '');
// O
$inscripcion->pausar(30, 'Razón personal');
```

**Lo que hace:**
1. Valida que no exceda el máximo de pausas anuales (4)
2. Valida que los días sean 7, 14 o 30
3. Calcula fecha de fin = hoy + días
4. Actualiza campos:
   - `pausada = true`
   - `dias_pausa = 7/14/30`
   - `fecha_pausa_inicio = now()`
   - `fecha_pausa_fin = now() + días`
   - `razon_pausa = razón`
   - `pausas_realizadas += 1`
   - `id_estado = 2/3/4` (según días)
5. Retorna `true`

### 2. Verificar si Está Pausada

```php
// Método: estaPausada()
if ($inscripcion->estaPausada()) {
    // Está pausada Y la pausa no ha expirado
}

// Devuelve true si:
// - id_estado es 2, 3 o 4 (pausada) O pausada = true
// Y fecha_pausa_fin > now() (pausa vigente)
```

**Lógica:**
```
┌─────────────────────────────────┐
│ ¿estaPausada()?                 │
└─────────────────────────────────┘
         ↓
    ¿Estado pausada?               
    (id_estado = 2, 3, 4          
     O pausada = true)             
         ↓                         
    SÍ → ¿Expiró?                 
         ↓                         
         ├─ SÍ → false             
         └─ NO → true              
    NO → false                     
```

### 3. Reanudar una Membresía

```php
// En el controlador
$inscripcion->reanudar();

// Lo que hace:
// 1. Valida que esté pausada
// 2. Calcula días que faltaban para terminar la pausa
// 3. Extiende fecha_vencimiento += días_pausa
// 4. Actualiza:
//    - pausada = false
//    - id_estado = 1 (Activa)
//    - fecha_vencimiento = nueva fecha
```

**Ejemplo:**
```
Inscripción original:
- fecha_vencimiento: 31/12/2025
- pausada: false

Se pausa por 7 días:
- pausada: true
- fecha_pausa_fin: 2/12/2025

Se reanuda:
- fecha_vencimiento: 07/01/2026 (31/12 + 7 días)
- pausada: false
```

### 4. Auto-Expiración de Pausa

```php
// Método: verificarPausaExpirada()
if ($inscripcion->verificarPausaExpirada()) {
    // La pausa expiró y se reanudó automáticamente
}

// Se ejecuta automáticamente en:
// - Al cargar la inscripción
// - En el show de inscripción
// - En el index (recomendado)
```

---

## 🎨 Mostrar en UI

### En Index (Lista)

```blade
<!-- Vista: resources/views/admin/inscripciones/index.blade.php -->
@php
    $estaPausada = $inscripcion->estaPausada();
@endphp

@if($estaPausada)
    <span class="badge bg-warning">
        <i class="fas fa-pause-circle"></i> 
        @if($inscripcion->dias_pausa == 7)
            Pausada - 7d
        @elseif($inscripcion->dias_pausa == 14)
            Pausada - 14d
        @elseif($inscripcion->dias_pausa == 30)
            Pausada - 30d
        @else
            Pausada
        @endif
    </span>
@else
    <span class="badge bg-success">
        <i class="fas fa-play-circle"></i> Activo
    </span>
@endif
```

**Resultado:**
- ⏸️ Pausada - 7d (amarillo si está pausada)
- ▶️ Activo (verde si NO está pausada)

### En Show (Detalle)

```blade
<!-- Vista: resources/views/admin/inscripciones/show.blade.php -->
@php
    $info = $inscripcion->obtenerInfoPausa();
@endphp

@if($info)
    <div class="info-box bg-warning">
        <span class="info-box-icon">
            <i class="fas fa-pause-circle"></i>
        </span>
        <div class="info-box-content">
            <span class="info-box-text">Estado de Pausa</span>
            <span class="info-box-number">
                {{ $info['dias'] }} días ({{ $info['dias_restantes'] }} días restantes)
            </span>
            <p>
                Desde: {{ $info['inicio'] }}<br>
                Hasta: {{ $info['fin'] }}<br>
                Razón: {{ $info['razon'] ?? 'No especificada' }}<br>
                Pausas usadas: {{ $info['pausas_usadas'] }}/{{ $info['pausas_disponibles'] }}
            </p>
        </div>
    </div>
@endif
```

---

## 🔧 Acciones en Controlador

### Pausar (POST)

```php
// routes/api.php o routes/web.php
Route::post('/inscripciones/{inscripcion}/pausar', 
    [InscripcionController::class, 'pausar']
)->name('inscripciones.pausar');

// En controlador
public function pausar(Request $request, Inscripcion $inscripcion)
{
    try {
        $validated = $request->validate([
            'dias' => 'required|in:7,14,30',
            'razon' => 'nullable|string|max:255'
        ]);

        $inscripcion->pausar(
            $validated['dias'], 
            $validated['razon'] ?? ''
        );

        return back()->with('success', 'Membresía pausada exitosamente');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

### Reanudar (POST)

```php
// routes/api.php
Route::post('/inscripciones/{inscripcion}/reanudar', 
    [InscripcionController::class, 'reanudar']
)->name('inscripciones.reanudar');

// En controlador
public function reanudar(Request $request, Inscripcion $inscripcion)
{
    try {
        $inscripcion->reanudar();
        
        return back()->with('success', 'Membresía reanudada exitosamente');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

---

## 📋 Validaciones

### Al Pausar
- ✅ Membresía NO está pausada
- ✅ Hay pausas disponibles (pausas_realizadas < max_pausas_permitidas)
- ✅ Días son válidos (7, 14 o 30)
- ✅ Lanzar excepción si algo falla

### Al Reanudar
- ✅ Membresía ESTÁ pausada
- ✅ Calcular correctamente extensión de fecha
- ✅ Lanzar excepción si no está pausada

### Auto-Expiración
- ✅ Si fecha_pausa_fin < now() → auto-reanudar
- ✅ No requiere confirmación del usuario
- ✅ Se ejecuta transparentemente

---

## 🐛 Debugging

### Ver Estado Completo

```php
// En tinker
$inscripcion = Inscripcion::find(1);

// Estado de pausa
dump($inscripcion->estaPausada());         // true/false
dump($inscripcion->obtenerInfoPausa());    // array detallado

// Información individual
dump($inscripcion->pausada);               // true/false
dump($inscripcion->dias_pausa);            // 7, 14, 30
dump($inscripcion->fecha_pausa_inicio);    // timestamp
dump($inscripcion->fecha_pausa_fin);       // timestamp
dump($inscripcion->razon_pausa);           // string
dump($inscripcion->pausas_realizadas);     // int
dump($inscripcion->max_pausas_permitidas); // int
dump($inscripcion->id_estado);             // 1-4
```

### Resetear Estado de Pausa

```php
// Para testing/debugging (NO en producción)
$inscripcion->update([
    'pausada' => false,
    'dias_pausa' => null,
    'fecha_pausa_inicio' => null,
    'fecha_pausa_fin' => null,
    'razon_pausa' => null,
    'id_estado' => 1, // Activa
]);
```

---

## 🔄 Flujo Completo (Caso de Uso)

### Scenario: Cliente Necesita Pausa

1. **Cliente solicita pausa**
   ```
   "Quiero pausar 14 días, tengo que viajar"
   ```

2. **Admin en sistema:**
   - Click en "Pausar"
   - Selecciona 14 días
   - Ingresa razón "Viaje"
   - Click en "Confirmar"

3. **Backend ejecuta:**
   ```php
   $inscripcion->pausar(14, 'Viaje');
   ```

4. **Se actualiza a:**
   - `pausada = true`
   - `dias_pausa = 14`
   - `fecha_pausa_inicio = 26/11/2025`
   - `fecha_pausa_fin = 10/12/2025`
   - `id_estado = 3` (Pausada - 14d)
   - `pausas_realizadas = 1`
   - `fecha_vencimiento = 10/12/2025 + 14 = 24/12/2025` (se extiende)

5. **En UI:**
   - ⏸️ Pausada - 14d (amarillo)
   - "Reanuda automáticamente el 10/12/2025"

6. **Después de 14 días (auto):**
   - Sistema detecta: `now() > fecha_pausa_fin`
   - Llama: `$inscripcion->reanudar()`
   - Se actualiza a:
     - `pausada = false`
     - `id_estado = 1` (Activa)
     - `fecha_vencimiento = 24/12/2025` (sin cambios)

7. **En UI:**
   - ▶️ Activo (verde)

---

## 📈 Estadísticas

### Para Dashboard/Reports

```php
// Inscripciones actualmente pausadas
$pausadas = Inscripcion::where('pausada', true)
    ->orWhereIn('id_estado', [2, 3, 4])
    ->count();

// Por duración
$pausadas7d = Inscripcion::where('dias_pausa', 7)->where('pausada', true)->count();
$pausadas14d = Inscripcion::where('dias_pausa', 14)->where('pausada', true)->count();
$pausadas30d = Inscripcion::where('dias_pausa', 30)->where('pausada', true)->count();

// Clientes que usan pausas
$conPausas = Inscripcion::where('pausas_realizadas', '>', 0)->distinct('id_cliente')->count();

// Próximas a reanudar (en 3 días)
$proximasReanudar = Inscripcion::where('pausada', true)
    ->whereBetween('fecha_pausa_fin', [now(), now()->addDays(3)])
    ->count();
```

---

## ✅ Checklist de Verificación

- [x] Columnas de pausa existen en BD
- [x] Modelo tiene métodos de pausa implementados
- [x] `estaPausada()` verifica estado y fecha correctamente
- [x] UI muestra estado correcto (pausada vs activo)
- [x] Rutas para pausar/reanudar funcionan
- [x] Validaciones previenen estados inconsistentes
- [x] Auto-expiración funciona correctamente
- [x] Extensión de fecha es correcta
- [x] Tests de pausa existen (si hay suite de tests)

---

**Última actualización:** 26 de noviembre de 2025
**Versión:** 1.0
