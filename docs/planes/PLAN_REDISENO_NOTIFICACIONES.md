# 📋 Plan de Rediseño - Módulo de Notificaciones

**Fecha:** 6 de diciembre de 2025  
**Objetivo:** Separar notificaciones automáticas y manuales con flujo coherente

---

## 🎯 Visión General

### Estado Actual
El módulo actual mezcla:
- ✅ Notificaciones automáticas (programadas por el sistema)
- ✅ Notificaciones manuales (enviadas por el admin)
- ⚠️ Todo en la misma vista y controlador

### Objetivo del Rediseño
Separar claramente en dos módulos:

#### 1. **Notificaciones Automáticas** (Sistema)
- Ejecutadas por cron jobs o comandos programados
- Basadas en estados del cliente (vencimiento, pago, pausa, etc.)
- El admin solo **monitorea** y **revisa logs**
- No requiere intervención manual

#### 2. **Notificaciones Manuales** (Admin)
- Enviadas manualmente por el administrador
- Buscar cliente específico
- Seleccionar plantilla predefinida (bienvenida, por_vencer, vencida, etc.)
- Completar variables automáticamente con datos del cliente
- Personalizar mensaje si es necesario
- Enviar de inmediato

---

## 📊 Estructura Propuesta

### Menú de Navegación
```
📧 Notificaciones
├── 🤖 Automáticas (Sistema)
│   ├── Dashboard / Estadísticas
│   ├── Historial de Envíos
│   ├── Logs / Errores
│   └── Configuración de Plantillas
│
└── ✉️ Manuales (Administrador)
    ├── Enviar Nueva Notificación
    ├── Buscar Cliente
    ├── Seleccionar Plantilla
    └── Historial de Envíos Manuales
```

---

## 🔧 Implementación por Fases

### **FASE 1: Reorganizar Estructura de Archivos**

#### Controladores
```
app/Http/Controllers/Admin/
├── Notificaciones/
│   ├── NotificacionAutomaticaController.php  // Monitoreo, logs, stats
│   ├── NotificacionManualController.php       // Envío manual
│   └── PlantillaController.php                // Gestión de plantillas
```

#### Vistas
```
resources/views/admin/notificaciones/
├── automaticas/
│   ├── dashboard.blade.php         // Estadísticas y estado
│   ├── historial.blade.php         // Historial de automáticas
│   ├── logs.blade.php              // Logs y errores
│   └── configuracion.blade.php     // Config de triggers
│
├── manuales/
│   ├── crear.blade.php             // Formulario envío manual
│   ├── seleccionar-cliente.blade.php
│   ├── seleccionar-plantilla.blade.php
│   ├── preview.blade.php           // Vista previa antes de enviar
│   └── historial.blade.php         // Historial de manuales
│
├── plantillas/
│   ├── index.blade.php             // Listado de plantillas
│   ├── editar.blade.php            // Editar plantilla
│   └── preview.blade.php           // Vista previa de plantilla
│
└── shared/
    └── components/                 // Componentes compartidos
```

---

### **FASE 2: Notificaciones Automáticas**

#### Funcionalidades

**Dashboard de Automáticas**
- 📊 Estadísticas de envíos (hoy, semana, mes)
- 📈 Gráficas de rendimiento
- ⚠️ Alertas de fallos
- 🔔 Próximas notificaciones programadas
- 🎯 Tipos más enviados

**Historial de Automáticas**
- 📋 Tabla con filtros:
  - Por tipo (bienvenida, por_vencer, vencida, etc.)
  - Por estado (enviado, fallido, pendiente)
  - Por fecha
  - Por cliente
- 🔍 Ver detalle de cada envío
- 📧 Ver contenido HTML enviado
- 🔄 Reintentar fallidas

**Logs y Errores**
- 📝 Log detallado de cada intento
- ❌ Lista de errores con causa
- 🔧 Sugerencias de corrección
- 📊 Estadísticas de tasa de éxito

**Configuración**
- ⏰ Horarios de ejecución automática
- 📅 Días de anticipación para "por_vencer"
- 🔄 Frecuencia de recordatorios
- ✅ Activar/desactivar tipos de notificaciones

#### Comandos Artisan
```bash
# Ya existentes (mejorar)
php artisan notificaciones:procesar
php artisan verificar:notificaciones --solo-test

# Nuevos
php artisan notificaciones:estadisticas
php artisan notificaciones:limpiar-antiguos --dias=90
```

---

### **FASE 3: Notificaciones Manuales**

#### Flujo de Envío Manual

**Paso 1: Buscar Cliente**
```
┌─────────────────────────────────────┐
│  🔍 Buscar Cliente                  │
├─────────────────────────────────────┤
│  Buscar por:                        │
│  • Nombre                           │
│  • RUT/Pasaporte                    │
│  • Email                            │
│  • Celular                          │
│                                     │
│  [Buscar]                           │
│                                     │
│  Resultados:                        │
│  ┌─────────────────────────────┐   │
│  │ Carlos González             │   │
│  │ 20.111.222-3                │   │
│  │ Membresía: Mensual (Activa) │   │
│  │ [Seleccionar] ────────────► │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

**Paso 2: Seleccionar Plantilla**
```
┌─────────────────────────────────────┐
│  📧 Seleccionar Plantilla           │
├─────────────────────────────────────┤
│  Cliente: Carlos González           │
│                                     │
│  Plantillas Disponibles:            │
│                                     │
│  🎉 Bienvenida                      │
│  └─ Mensaje de bienvenida al gym   │
│     [Usar esta plantilla]           │
│                                     │
│  ⏰ Membresía por Vencer            │
│  └─ Recordatorio de vencimiento    │
│     [Usar esta plantilla]           │
│                                     │
│  ⚠️ Membresía Vencida               │
│  └─ Aviso de membresía vencida     │
│     [Usar esta plantilla]           │
│                                     │
│  💳 Pago Pendiente                  │
│  └─ Recordatorio de deuda          │
│     [Usar esta plantilla]           │
│                                     │
│  [+ Ver todas las plantillas]       │
└─────────────────────────────────────┘
```

**Paso 3: Completar y Personalizar**
```
┌─────────────────────────────────────┐
│  ✏️ Completar Notificación          │
├─────────────────────────────────────┤
│  Cliente: Carlos González           │
│  Email: carlos@example.com          │
│  Plantilla: Bienvenida              │
│                                     │
│  Variables Cargadas:                │
│  ✅ {nombre} = Carlos               │
│  ✅ {apellido} = González           │
│  ✅ {membresia} = Mensual           │
│  ✅ {precio} = $25.000              │
│  ✅ {fecha_vencimiento} = 06/01/26  │
│                                     │
│  Asunto:                            │
│  [Bienvenido a PROGYM, Carlos!]     │
│                                     │
│  Contenido:                         │
│  [Vista previa del email HTML]      │
│                                     │
│  Personalizar Mensaje (opcional):   │
│  ┌─────────────────────────────┐   │
│  │ Agregar nota personal...    │   │
│  └─────────────────────────────┘   │
│                                     │
│  [👁️ Vista Previa] [📧 Enviar]      │
└─────────────────────────────────────┘
```

**Paso 4: Confirmación**
```
┌─────────────────────────────────────┐
│  ✅ Notificación Enviada            │
├─────────────────────────────────────┤
│  Se envió correctamente a:          │
│  📧 carlos@example.com              │
│                                     │
│  Tipo: Bienvenida                   │
│  Fecha: 06/12/2025 15:30            │
│                                     │
│  [Ver en Historial]                 │
│  [Enviar otra notificación]         │
└─────────────────────────────────────┘
```

#### Funcionalidades del Módulo Manual

**Características Clave:**
- ✅ Autocompletar variables desde la BD
- 🔍 Búsqueda inteligente de cliente
- 👁️ Vista previa antes de enviar
- 📋 Historial de manuales separado
- 📝 Agregar notas personalizadas
- ⚡ Envío inmediato (no programado)
- 📊 Registro en logs separado

---

### **FASE 4: Gestión de Plantillas**

#### Funcionalidades

**Listado de Plantillas**
- 📋 7 plantillas predefinidas:
  1. 🎉 Bienvenida
  2. ⏰ Membresía por Vencer
  3. ⚠️ Membresía Vencida
  4. 💳 Pago Pendiente
  5. ⏸️ Pausa de Inscripción
  6. ▶️ Activación de Inscripción
  7. ✅ Pago Completado

**Editar Plantilla**
- 📝 Editar HTML
- 🎨 Editor visual (opcional)
- 🔤 Lista de variables disponibles
- 👁️ Vista previa en tiempo real
- 🧪 Enviar email de prueba
- 📋 Historial de cambios

**Vista Previa de Plantilla**
- 📱 Vista móvil y desktop
- 🧪 Datos de prueba
- 📊 Análisis de variables

---

## 🗄️ Base de Datos

### Tabla: `notificaciones`
**Agregar columnas:**
```sql
ALTER TABLE notificaciones ADD COLUMN tipo_envio ENUM('automatica', 'manual') DEFAULT 'automatica';
ALTER TABLE notificaciones ADD COLUMN enviado_por_user_id INT NULL;
ALTER TABLE notificaciones ADD COLUMN nota_personalizada TEXT NULL;
```

### Tabla: `log_notificaciones`
**Ya existe** - mantener estructura actual

---

## 🎨 Diseño UI/UX

### Paleta de Colores

**Notificaciones Automáticas:**
- 🤖 Color principal: `#4361ee` (azul tech)
- ✅ Éxito: `#2EB872` (verde)
- ⚠️ Advertencia: `#FFC107` (amarillo)
- ❌ Error: `#E0001A` (rojo)

**Notificaciones Manuales:**
- ✉️ Color principal: `#e94560` (magenta)
- 📧 Acento: `#ff6b6b` (coral)

### Componentes Compartidos
- 📊 Cards de estadísticas
- 📋 Tablas con filtros
- 🔍 Buscador de clientes
- 📧 Vista previa de email
- 🎨 Editor de plantillas

---

## 📝 Rutas Propuestas

### Notificaciones Automáticas
```php
Route::prefix('notificaciones/automaticas')->name('admin.notificaciones.automaticas.')->group(function () {
    Route::get('/', [NotificacionAutomaticaController::class, 'dashboard'])->name('dashboard');
    Route::get('/historial', [NotificacionAutomaticaController::class, 'historial'])->name('historial');
    Route::get('/logs', [NotificacionAutomaticaController::class, 'logs'])->name('logs');
    Route::get('/configuracion', [NotificacionAutomaticaController::class, 'configuracion'])->name('configuracion');
    Route::post('/configuracion', [NotificacionAutomaticaController::class, 'guardarConfiguracion'])->name('guardar-configuracion');
    Route::post('/reintentar/{notificacion}', [NotificacionAutomaticaController::class, 'reintentar'])->name('reintentar');
});
```

### Notificaciones Manuales
```php
Route::prefix('notificaciones/manuales')->name('admin.notificaciones.manuales.')->group(function () {
    Route::get('/', [NotificacionManualController::class, 'index'])->name('index');
    Route::get('/crear', [NotificacionManualController::class, 'crear'])->name('crear');
    Route::post('/buscar-cliente', [NotificacionManualController::class, 'buscarCliente'])->name('buscar-cliente');
    Route::get('/seleccionar-plantilla/{cliente}', [NotificacionManualController::class, 'seleccionarPlantilla'])->name('seleccionar-plantilla');
    Route::post('/preview', [NotificacionManualController::class, 'preview'])->name('preview');
    Route::post('/enviar', [NotificacionManualController::class, 'enviar'])->name('enviar');
    Route::get('/historial', [NotificacionManualController::class, 'historial'])->name('historial');
});
```

### Plantillas
```php
Route::prefix('notificaciones/plantillas')->name('admin.notificaciones.plantillas.')->group(function () {
    Route::get('/', [PlantillaController::class, 'index'])->name('index');
    Route::get('/{tipo}/editar', [PlantillaController::class, 'editar'])->name('editar');
    Route::post('/{tipo}/actualizar', [PlantillaController::class, 'actualizar'])->name('actualizar');
    Route::get('/{tipo}/preview', [PlantillaController::class, 'preview'])->name('preview');
    Route::post('/{tipo}/test', [PlantillaController::class, 'enviarPrueba'])->name('enviar-prueba');
});
```

---

## ✅ Checklist de Implementación

### Fase 1: Preparación
- [ ] Crear backup de archivos actuales
- [ ] Crear nuevos controladores
- [ ] Crear estructura de carpetas de vistas
- [ ] Migración para agregar columnas a BD

### Fase 2: Notificaciones Automáticas
- [ ] Dashboard con estadísticas
- [ ] Historial de automáticas
- [ ] Logs y errores
- [ ] Configuración de triggers
- [ ] Mejorar comandos artisan

### Fase 3: Notificaciones Manuales
- [ ] Buscador de clientes
- [ ] Selector de plantillas
- [ ] Autocompletar variables
- [ ] Vista previa
- [ ] Envío manual
- [ ] Historial separado

### Fase 4: Gestión de Plantillas
- [ ] Listado de plantillas
- [ ] Editor de plantillas
- [ ] Vista previa
- [ ] Email de prueba

### Fase 5: Testing y Deploy
- [ ] Tests unitarios
- [ ] Tests de integración
- [ ] Validar con datos reales
- [ ] Documentar uso
- [ ] Deploy a producción

---

## 🚀 Próximos Pasos Inmediatos

1. **Crear migración** para nuevas columnas
2. **Crear controladores** separados
3. **Reorganizar vistas** en carpetas
4. **Implementar buscador** de clientes
5. **Crear flujo** de envío manual paso a paso

---

**Estado:** 📋 Planificación completa  
**Prioridad:** 🔥 Alta  
**Estimación:** 3-4 días de desarrollo
