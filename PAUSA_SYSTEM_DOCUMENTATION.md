# Sistema de Pausas para Membresías - Documentación Completa

## 1. Descripción General

El sistema de pausas permite a los usuarios pausar sus membresías por períodos de 7, 14 o 30 días sin perder sus datos ni cancelar la inscripción. Durante la pausa, la membresía entra en estado **"Pausada"** y el usuario no tendrá acceso a las instalaciones.

### Características Principales:
- ✅ Pausar membresía por 7, 14 o 30 días
- ✅ Máximo 2 pausas por año (configurable)
- ✅ Seguimiento automático de fechas de pausa
- ✅ Reanudación manual o automática al vencer la pausa
- ✅ Razón de pausa registrada
- ✅ API REST completa para gestión de pausas
- ✅ UI intuitiva en el módulo de inscripciones

---

## 2. Base de Datos

### Migración 0019: Campos de Pausa en `inscripciones`

Se agregaron 7 nuevos campos a la tabla `inscripciones`:

```sql
ALTER TABLE inscripciones ADD COLUMN pausada BOOLEAN DEFAULT false;
ALTER TABLE inscripciones ADD COLUMN dias_pausa INT DEFAULT NULL;
ALTER TABLE inscripciones ADD COLUMN fecha_pausa_inicio DATE DEFAULT NULL;
ALTER TABLE inscripciones ADD COLUMN fecha_pausa_fin DATE DEFAULT NULL;
ALTER TABLE inscripciones ADD COLUMN razon_pausa TEXT DEFAULT NULL;
ALTER TABLE inscripciones ADD COLUMN pausas_realizadas INT DEFAULT 0;
ALTER TABLE inscripciones ADD COLUMN max_pausas_permitidas INT DEFAULT 2;

-- Índices
CREATE INDEX idx_inscripciones_pausada ON inscripciones(pausada);
CREATE INDEX idx_inscripciones_fecha_pausa_fin ON inscripciones(fecha_pausa_fin);
```

### Estados de Pausa (Tabla `estados`)

Se agregaron 3 nuevos estados en el rango 01-09 (membresías):

| Código | Nombre | Descripción | Color |
|--------|--------|-------------|-------|
| 2 | Pausada - 7 días | Membresía pausada por 7 días | warning |
| 3 | Pausada - 14 días | Membresía pausada por 14 días | warning |
| 4 | Pausada - 30 días | Membresía pausada por 30 días | warning |

---

## 3. Modelo Inscripcion

### Casts
```php
protected $casts = [
    'fecha_inscripcion' => 'datetime',
    'fecha_inicio' => 'datetime',
    'fecha_vencimiento' => 'datetime',
    'fecha_pausa_inicio' => 'datetime',
    'fecha_pausa_fin' => 'datetime',
    'pausada' => 'boolean',
];
```

### Fillable
```php
protected $fillable = [
    // ... campos existentes ...
    'pausada',
    'dias_pausa',
    'fecha_pausa_inicio',
    'fecha_pausa_fin',
    'razon_pausa',
    'pausas_realizadas',
    'max_pausas_permitidas',
];
```

### Métodos de Pausa

#### 1. `pausar($dias, $razon = '')`
Pausa la membresía por los días especificados.

```php
// Ejemplo
$inscripcion = Inscripcion::find(1);
$inscripcion->pausar(7, 'Vacaciones');
// Resultado: pausada = true, estado = 2, fecha_pausa_fin = hoy + 7 días
```

**Lógica:**
- Valida que pueda pausarse (no esté pausada, haya pausas disponibles)
- Establece `pausada = true`
- Cambia estado a 2, 3 o 4 según días
- Calcula `fecha_pausa_fin = hoy + días`
- Guarda la razón de pausa
- Incrementa `pausas_realizadas`

#### 2. `reanudar()`
Reanuda una membresía pausada.

```php
// Ejemplo
$inscripcion->reanudar();
// Resultado: pausada = false, estado = 1 (Activa), fecha_vencimiento extendida
```

**Lógica:**
- Valida que esté pausada
- Establece `pausada = false`
- Cambia estado a 1 (Activa)
- Extiende `fecha_vencimiento` por los días de pausa
- Limpia los campos de pausa

#### 3. `verificarPausaExpirada()`
Verifica automáticamente si la pausa ha expirado y reanuda si es necesario.

```php
// Ejemplo (ejecutar en cron job)
foreach (Inscripcion::where('pausada', true)->get() as $inscripcion) {
    $inscripcion->verificarPausaExpirada();
}
```

**Retorna:** `true` si reanuvo automáticamente, `false` si no

#### 4. `obtenerInfoPausa()`
Retorna información detallada sobre la pausa.

```php
$info = $inscripcion->obtenerInfoPausa();
// Resultado:
[
    'activa' => true,
    'dias_pausa' => 7,
    'dias_restantes' => 3,
    'fecha_inicio' => '2025-01-20',
    'fecha_fin' => '2025-01-27',
    'pausas_usadas' => 1,
    'pausas_disponibles' => 1,
    'razon' => 'Vacaciones'
]
```

#### 5. `puedepausarse()`
Valida si la membresía puede ser pausada.

```php
if ($inscripcion->puedepausarse()) {
    // Puede pausarse
} else {
    // No puede pausarse
}
```

**Condiciones:**
- No está pausada actualmente
- Pausas realizadas < max_pausas_permitidas
- Estado es Activa (id_estado = 1)

---

## 4. API REST

### Endpoints de Pausas

#### POST `/api/pausas/{id}/pausar`
Pausa una membresía.

**Request:**
```json
{
    "dias": 7,
    "razon": "Vacaciones"
}
```

**Response (Éxito):**
```json
{
    "success": true,
    "message": "Membresía pausada exitosamente",
    "data": {
        "id": 1,
        "cliente": "Juan Pérez",
        "pausada": true,
        "dias_pausa": 7,
        "fecha_pausa_inicio": "20/01/2025",
        "fecha_pausa_fin": "27/01/2025",
        "razon": "Vacaciones",
        "estado": "Pausada - 7 días",
        "pausas_usadas": 1,
        "pausas_disponibles": 1
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Esta membresía no puede ser pausada",
    "info": {
        "pausada_actualmente": false,
        "pausas_usadas": 2,
        "pausas_disponibles": 0,
        "estado": "Activa"
    }
}
```

---

#### POST `/api/pausas/{id}/reanudar`
Reanuda una membresía pausada.

**Request:** (sin body)

**Response (Éxito):**
```json
{
    "success": true,
    "message": "Membresía reanudada exitosamente",
    "data": {
        "id": 1,
        "cliente": "Juan Pérez",
        "pausada": false,
        "estado": "Activa",
        "fecha_vencimiento": "27/02/2025",
        "pausas_usadas": 1,
        "pausas_disponibles": 1
    }
}
```

---

#### GET `/api/pausas/{id}/info`
Obtiene información de pausa de una membresía.

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "cliente": "Juan Pérez",
        "puede_pausarse": true,
        "pausa_info": {
            "activa": true,
            "dias_pausa": 7,
            "dias_restantes": 3,
            "fecha_inicio": "2025-01-20",
            "fecha_fin": "2025-01-27",
            "pausas_usadas": 1,
            "pausas_disponibles": 1,
            "razon": "Vacaciones"
        },
        "estado": "Pausada - 7 días"
    }
}
```

---

#### POST `/api/pausas/verificar-expiradas`
Verifica y reanuda automáticamente pausas expiradas (para cron jobs).

**Response:**
```json
{
    "success": true,
    "message": "Verificación completada",
    "reactivadas": 5,
    "total_pausadas": 8
}
```

---

## 5. Interfaz de Usuario

### Ubicación
`resources/views/admin/inscripciones/edit.blade.php`

### Sección de Pausas en la Página de Editar Inscripción

La sección de pausas se muestra solo si el estado de la membresía es:
- 1 (Activa)
- 8 (Pendiente de Activación)
- 9 (En Revisión)

#### Cuando la Membresía está Activa:
1. Selector de duración (7, 14 o 30 días)
2. Botón "Pausar"
3. Muestra pausas disponibles
4. Modal de confirmación con razón de pausa

#### Cuando la Membresía está Pausada:
1. Información de la pausa actual
2. Días de pausa
3. Fechas de inicio y fin
4. Razón de pausa
5. Contador de pausas usadas
6. Botón "Reanudar Membresía"

### Vista de Listado (index.blade.php)
Se agregó una nueva columna "Pausa" que muestra:
- "Activo" (badge verde) si no está pausada
- "7d", "14d", "30d" (badge amarillo) con ícono de pausa si está pausada

---

## 6. Flujo Completo de Pausa

### Caso 1: Pausar una Membresía

```
1. Usuario accede a editar inscripción
   ↓
2. Selecciona duración (7, 14 o 30 días)
   ↓
3. Ingresa razón (opcional)
   ↓
4. Confirma en modal
   ↓
5. POST /api/pausas/{id}/pausar
   ↓
6. Inscripcion::pausar() es ejecutado
   ├─ pausada = true
   ├─ estado = 2/3/4 (según días)
   ├─ fecha_pausa_fin = hoy + días
   ├─ pausas_realizadas++
   └─ Se guardan todos los cambios
   ↓
7. API retorna confirmación
   ↓
8. Página se recarga automáticamente
   ↓
9. Usuario ve "Membresía pausada" con fecha de fin
```

### Caso 2: Reanudación Automática (Cron Job)

```
1. Cron ejecuta: POST /api/pausas/verificar-expiradas
   ↓
2. Obtiene todas inscripciones con pausada = true
   ↓
3. Para cada una, llama: verificarPausaExpirada()
   ├─ Si fecha_pausa_fin <= hoy
   │  └─ Llama reanudar()
   │     ├─ pausada = false
   │     ├─ estado = 1 (Activa)
   │     ├─ fecha_vencimiento += dias_pausa
   │     └─ Guarda cambios
   └─ Si no ha vencido, no hace nada
   ↓
4. Retorna cantidad de membresías reactivadas
```

### Caso 3: Reanudación Manual

```
1. Usuario accede a editar inscripción (membresía pausada)
   ↓
2. Lee la información de pausa
   ↓
3. Hace clic en "Reanudar Membresía"
   ↓
4. Confirma en diálogo
   ↓
5. POST /api/pausas/{id}/reanudar
   ↓
6. Inscripcion::reanudar() es ejecutado
   ├─ pausada = false
   ├─ estado = 1 (Activa)
   ├─ fecha_vencimiento extendida
   └─ Se limpian campos de pausa
   ↓
7. API retorna confirmación
   ↓
8. Página se recarga
   ↓
9. Usuario ve "Membresía activa" nuevamente
```

---

## 7. Configuración y Límites

### Máximo de Pausas por Año
Se puede configurar por membresía:

```php
// En el seeder o migration
$inscripcion->max_pausas_permitidas = 2;

// O cambiar durante la ejecución
$inscripcion->update(['max_pausas_permitidas' => 3]);
```

**Valor por defecto:** 2 pausas por año

### Duraciones Permitidas
- 7 días (1 semana)
- 14 días (2 semanas)
- 30 días (1 mes)

Agregar nuevas duraciones requiere:
1. Agregar nuevos estados en `EstadoSeeder`
2. Actualizar validación en `PausaApiController`
3. Actualizar selector en `edit.blade.php`

---

## 8. Cron Job Recomendado

### En `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Verificar pausas expiradas cada hora
    $schedule->call(function () {
        Http::post(url('/api/pausas/verificar-expiradas'));
    })->hourly();
}
```

### O usando Artisan Command (recomendado):

```php
// Crear comando: php artisan make:command VerificarPausasExpiradas

protected function handle()
{
    $inscripciones = Inscripcion::where('pausada', true)->get();
    $reactivadas = 0;
    
    foreach ($inscripciones as $inscripcion) {
        if ($inscripcion->verificarPausaExpirada()) {
            $reactivadas++;
        }
    }
    
    $this->info("Se reactivaron $reactivadas membresías");
}
```

---

## 9. Validaciones y Reglas

### Puede Pausarse Si:
- ✅ Estado = Activa (1) | Pendiente (8) | En Revisión (9)
- ✅ No está pausada actualmente
- ✅ pausas_realizadas < max_pausas_permitidas

### No Puede Pausarse Si:
- ❌ Ya está pausada
- ❌ pausas_realizadas >= max_pausas_permitidas
- ❌ Estado != Activa/Pendiente/Revisión
- ❌ fecha_vencimiento está en el pasado

### Reanudación:
- ✅ Extiende automáticamente fecha_vencimiento
- ✅ El tiempo de pausa se suma nuevamente
- ✅ No cuenta como nueva pausa (no incrementa contador)

---

## 10. Estados Relacionados

### Estado 1 - Activa ✅
- Verde (success)
- Usuario puede pausar o acceder normalmente

### Estado 2/3/4 - Pausada ⏸️
- Amarillo (warning)
- Usuario NO tiene acceso
- Puede ser reanudada antes de fecha_pausa_fin

### Estado 5 - Vencida ❌
- Rojo (danger)
- No puede pausarse (requiere renovación)

### Estado 7 - Suspendida por Deuda 🚫
- Rojo (danger)
- No puede pausarse (debe pagar deuda primero)

---

## 11. Pruebas

### Flujo de Prueba Manual

```
1. Crear cliente con inscripción activa
2. Acceder a editar inscripción
3. Seleccionar "Pausar por 7 días"
4. Ingresar razón "Prueba pausa"
5. Confirmar
6. Verificar que estado cambió a "Pausada - 7 días"
7. Verificar que fecha_pausa_fin = hoy + 7 días
8. Verificar que pausas_realizadas = 1
9. Reanuda la membresía
10. Verificar que estado cambió a "Activa"
11. Verificar que fecha_vencimiento se extendió 7 días
12. Intentar pausar más de 2 veces (debe fallar)
```

### Prueba de API

```bash
# Obtener info de pausa
curl -X GET http://localhost:8000/api/pausas/1/info

# Pausar membresía
curl -X POST http://localhost:8000/api/pausas/1/pausar \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker <<< 'csrf_token()')" \
  -d '{"dias": 7, "razon": "Prueba"}'

# Reanudar
curl -X POST http://localhost:8000/api/pausas/1/reanudar \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker <<< 'csrf_token()')"

# Verificar pausas expiradas
curl -X POST http://localhost:8000/api/pausas/verificar-expiradas \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker <<< 'csrf_token()')"
```

---

## 12. Logs y Auditoría

### Campos Registrados:
- `pausada` - Status actual
- `dias_pausa` - Duración
- `fecha_pausa_inicio` - Cuándo comenzó
- `fecha_pausa_fin` - Cuándo termina
- `razon_pausa` - Motivo
- `pausas_realizadas` - Contador
- `max_pausas_permitidas` - Límite

### Recomendación:
Considerar agregar registro en tabla `auditoria` para tracking completo:

```php
Auditoria::create([
    'tabla' => 'inscripciones',
    'accion' => 'pausar',
    'id_registro' => $inscripcion->id,
    'datos_antes' => json_encode($inscripcion->getOriginal()),
    'datos_despues' => json_encode($inscripcion->fresh()),
    'usuario_id' => auth()->id(),
]);
```

---

## 13. Solución de Problemas

| Problema | Causa | Solución |
|----------|-------|----------|
| No puedo pausar | pausas_realizadas >= max_pausas_permitidas | Esperar al próximo año o cambiar configuración |
| La pausa no se reanuda automáticamente | Cron job no está configurado | Ejecutar `/api/pausas/verificar-expiradas` manualmente |
| Las fechas se ven mal | Formato de fecha incorrecto | Verificar formato en `format('d/m/Y')` |
| El API retorna 404 | Rutas no registradas | Verificar `routes/web.php` tenga `/api/pausas/*` |

---

## 14. Mejoras Futuras

1. **Notificaciones:**
   - Email cuando pausa está próxima a vencer
   - Recordatorio 1 día antes de reanudación

2. **Reportes:**
   - Reporte de pausas más comunes
   - Análisis de razones de pausa

3. **Configuración:**
   - Permitir admin editar max_pausas_permitidas globalmente
   - Permitir diferentes límites por membresía tipo

4. **UI Avanzada:**
   - Timeline visual de pausas
   - Historial de pausas realizadas
   - Descuento por usar pausas vs. cancelación

---

## Última Actualización
- **Fecha:** 25 de Noviembre de 2025
- **Versión:** 1.0.0
- **Estado:** Producción ✅
