# 🔍 CONTEXTO DEL PROYECTO - EstóicosGym

**Documento de Contexto Técnico para IAs**  
**Fecha:** 25/11/2025  
**Versión:** 1.0.0

---

## 📋 RESUMEN EJECUTIVO

Sistema de gestión de membresías para gimnasios construido con **Laravel 12 + PHP 8.2 + MySQL 8.0 + AdminLTE 3**.

**Estado:** ✅ Funcional y Listo  
**Stack:** Pure PHP (sin Node.js)  
**Usuarios:** 3 módulos CRUD (Clientes, Inscripciones, Pagos)

---

## 🏗️ STACK TECNOLÓGICO

| Componente | Versión | Rol |
|-----------|---------|-----|
| Laravel | 12.0 | Framework backend |
| PHP | 8.2+ | Lenguaje |
| MySQL | 8.0+ | Base de datos |
| AdminLTE | 3.15 (Composer) | UI framework |
| Bootstrap | 5.3 | CSS |
| Composer | 2.x | Gestor dependencias |

**Instalación:** `composer require jeroennoten/laravel-adminlte:^3.15`

---

## 📁 ESTRUCTURA ARCHIVOS

```
app/
├── Http/Controllers/
│   ├── DashboardController.php (estadísticas)
│   └── Admin/
│       ├── ClienteController.php (CRUD clientes)
│       ├── InscripcionController.php (CRUD membresías)
│       └── PagoController.php (CRUD pagos)
├── Models/ (14 modelos)
│   ├── Cliente.php
│   ├── Inscripcion.php
│   ├── Pago.php
│   ├── Membresia.php
│   ├── Estado.php
│   ├── MetodoPago.php
│   └── ... (+ 8 más)
└── Rules/
    └── RutValido.php (validador RUT chileno)

database/
├── migrations/ (14 migraciones)
├── seeders/ (7 seeders con datos prueba)
└── factories/

resources/views/
├── admin/
│   ├── clientes/ (4 vistas: index, create, edit, show)
│   ├── inscripciones/ (4 vistas)
│   └── pagos/ (4 vistas)
├── dashboard/ (dashboard con KPIs)
└── layouts/

routes/
└── web.php (23 rutas RESTful)
```

---

## 🗄️ BASE DE DATOS (14 tablas)

### Tablas Principales:
- **clientes** (id, run_pasaporte UNIQUE, nombres, apellido_paterno, email UNIQUE, celular, direccion, fecha_nacimiento, id_convenio FK, activo BOOL)
- **inscripciones** (id, id_cliente FK, id_membresia FK, fecha_inicio, fecha_vencimiento, precio_final, id_estado FK)
- **pagos** (id, id_inscripcion FK, id_cliente FK, monto_abonado DECIMAL, fecha_pago, id_metodo_pago FK, referencia_pago)

### Tablas Auxiliares:
- **estados** (id, nombre: Activa, Vencida, Pausada, Cancelada, Pendiente)
- **metodos_pago** (id, nombre: Efectivo, Transferencia, Tarjeta, Cheque, Otro)
- **membresias** (id, nombre, duracion_meses, duracion_dias)
- **precios_membresias** (id, id_membresia FK, precio, vigencia_desde)
- **usuarios** (id, name, email UNIQUE, password)
- Y otras 6 tablas (convenios, roles, motivos_descuento, historial_precios, auditoria, notificaciones)

**Datos de Prueba:** 10 clientes, 20 inscripciones, 60 pagos

---

## 🎯 MÓDULOS IMPLEMENTADOS

### 1. CLIENTES
**Ruta:** `/admin/clientes`  
**Métodos:** index (pagina 15), create, store, show, edit, update, destroy  
**Validaciones:**
- `run_pasaporte`: unique, RUT chileno válido (algoritmo módulo 11)
- `email`: unique, email
- `nombres`: required, string, max 255
- `apellido_paterno`: required
- `celular`: required, string, max 20

**Relaciones:** inscripciones (1:N), pagos (1:N), notificaciones (1:N)

### 2. INSCRIPCIONES
**Ruta:** `/admin/inscripciones`  
**Métodos:** index (pagina 15), create, store, show, edit, update, destroy  
**Validaciones:**
- `id_cliente`: required, exists:clientes
- `id_estado`: required, exists:estados
- `fecha_vencimiento`: required, date, after:fecha_inicio

**Relaciones:** cliente (N:1), estado (N:1), pagos (1:N), membresia (N:1)

### 3. PAGOS
**Ruta:** `/admin/pagos`  
**Métodos:** index (pagina 15), create, store, show, edit, update, destroy  
**Validaciones:**
- `id_inscripcion`: required, exists:inscripciones
- `monto_abonado`: required, numeric, min:0.01
- `id_metodo_pago`: required, exists:metodo_pagos

**Relaciones:** inscripcion (N:1), cliente (N:1), metodoPago (N:1)

---

## 🎨 VISTAS (12 Blade)

| Módulo | index | create | edit | show |
|--------|-------|--------|------|------|
| Clientes | ✅ | ✅ | ✅ | ✅ |
| Inscripciones | ✅ | ✅ | ✅ | ✅ |
| Pagos | ✅ | ✅ | ✅ | ✅ |

**Características:**
- Paginación automática (15 registros)
- Filtros en index (por estado, cliente, método pago)
- Validación de errores con `@error` y `old()`
- Bootstrap 5 responsive
- Iconos Font Awesome 6
- Método DELETE con confirmación

---

## 🔄 RUTAS (23 RESTful)

```php
// Públicas
GET  / → redirect dashboard
GET  /dashboard → DashboardController@index

// Admin (prefix: /admin, name: admin.*)
GET    /admin/clientes → ClienteController@index
GET    /admin/clientes/create → ClienteController@create
POST   /admin/clientes → ClienteController@store
GET    /admin/clientes/{cliente} → ClienteController@show
GET    /admin/clientes/{cliente}/edit → ClienteController@edit
PUT    /admin/clientes/{cliente} → ClienteController@update
DELETE /admin/clientes/{cliente} → ClienteController@destroy
[Similar para inscripciones y pagos]
```

---

## 🧮 MODELOS (Campos Principales)

### Cliente
```php
$fillable = ['run_pasaporte', 'nombres', 'apellido_paterno', 'apellido_materno', 
             'celular', 'email', 'direccion', 'fecha_nacimiento', 'id_convenio', 
             'observaciones', 'activo']
scopeActive() // where activo = true
```

### Inscripcion
```php
$fillable = ['id_cliente', 'id_membresia', 'fecha_inscripcion', 'fecha_inicio', 
             'fecha_vencimiento', 'precio_final', 'id_estado', 'observaciones']
$casts = ['fecha_inscripcion' => 'date', 'fecha_inicio' => 'date', 'fecha_vencimiento' => 'date']
```

### Pago
```php
$fillable = ['id_inscripcion', 'id_cliente', 'monto_abonado', 'fecha_pago', 
             'id_metodo_pago', 'referencia_pago', 'observaciones']
$casts = ['fecha_pago' => 'date']
```

---

## 📊 DASHBOARD

**Ubicación:** `DashboardController@index` → `resources/views/dashboard/index.blade.php`

**KPIs (4 estadísticas):**
1. Total Clientes Activos: `Cliente::where('activo', true)->count()`
2. Inscripciones Activas: `Inscripcion::where('id_estado', $idEstadoActiva)->count()`
3. Pagos del Mes: `Pago::whereYear/Month->sum('monto_abonado')`
4. Ingresos Totales: `Pago::sum('monto_abonado')`

**Tablas (3):**
1. Últimos 5 pagos con cliente y método
2. Últimas 5 inscripciones con cliente y estado
3. Membresías más vendidas

---

## ⚙️ CONFIGURACIÓN ADMINLTE

**Archivo:** `config/adminlte.php`

```php
'title' => 'EstóicosGym',
'sidebar' => [
    ['text' => 'Dashboard', 'url' => 'dashboard', 'icon' => 'fas fa-fw fa-home'],
    ['header' => 'MÓDULOS'],
    ['text' => 'Clientes', 'url' => 'admin/clientes', 'icon' => 'fas fa-fw fa-users'],
    ['text' => 'Inscripciones', 'url' => 'admin/inscripciones', 'icon' => 'fas fa-fw fa-credit-card'],
    ['text' => 'Pagos', 'url' => 'admin/pagos', 'icon' => 'fas fa-fw fa-dollar-sign'],
]
```

---

## 🔐 VALIDACIONES ESPECIALES

### RutValido (app/Rules/RutValido.php)
```php
// Algoritmo módulo 11
// Formatos aceptados: XX.XXX.XXX-X, XXXXXXXX-X, XXXXXXXX-K
// Calcula check digit y valida contra RUT
// Error: "El RUT ingresado no es válido. Formato: XX.XXX.XXX-X o XXXXXXXX-X"
```

---

## 📥 INSTALACIÓN

```bash
# 1. Clonar
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym

# 2. Instalar
composer install

# 3. Configurar
cp .env.example .env
# Editar .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Generar clave
php artisan key:generate

# 5. Crear BD
mysql -u root
CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Migrar
php artisan migrate
php artisan db:seed

# 7. Servidor
php artisan serve
```

**Acceso:** `http://localhost:8000/dashboard`

---

## 📚 DOCUMENTACIÓN INCLUIDA

- **README.md** - Guía completa para usuarios
- **INICIO_RAPIDO.md** - 5 pasos en 5 minutos
- **ESTADO_FINAL.md** - Resumen del proyecto
- **COMO_COMPARTIR.md** - Cómo distribuir
- **INDICE_DOCUMENTACION.md** - Índice completo
- **INSTALL.bat / INSTALL.sh** - Scripts automáticos

---

## ✅ ESTADO DEL CÓDIGO

| Componente | Estado | Notas |
|-----------|--------|-------|
| Controllers | ✅ COMPLETO | 3 controllers, 21 métodos |
| Models | ✅ COMPLETO | 14 modelos con relaciones |
| Migrations | ✅ COMPLETO | 14 migraciones, BD relacional |
| Seeders | ✅ COMPLETO | Datos de prueba incluidos |
| Vistas | ✅ COMPLETO | 12 vistas Blade |
| Validaciones | ✅ COMPLETO | RUT chileno incluido |
| Rutas | ✅ COMPLETO | 23 rutas RESTful |
| Dashboard | ✅ COMPLETO | 4 KPIs + 3 tablas |

---

## 🔍 ARCHIVOS CRÍTICOS

| Archivo | Propósito | Estado |
|---------|-----------|--------|
| `app/Rules/RutValido.php` | Validador RUT | ✅ |
| `config/adminlte.php` | Configuración UI | ✅ |
| `routes/web.php` | Rutas del sistema | ✅ |
| `database/seeders/ClientesInscripcionesPagosSeeder.php` | Datos prueba | ✅ |
| `.env.example` | Configuración | ✅ |

---

## 🚀 COMANDOS ESENCIALES

```bash
php artisan serve              # Iniciar servidor
php artisan migrate            # Ejecutar migraciones
php artisan db:seed            # Cargar seeders
php artisan cache:clear        # Limpiar cache
php artisan tinker             # Consola interactiva
php artisan route:list         # Listar rutas
```

---

## 📊 MÉTRICAS DEL PROYECTO

- **Controllers:** 3 (Dashboard, Cliente, Inscripcion, Pago)
- **Models:** 14
- **Migrations:** 14
- **Vistas:** 12
- **Rutas:** 23
- **Validaciones Personalizadas:** 1 (RutValido)
- **Líneas de Código:** ~2000+
- **Documentación:** 5 archivos .md + 2 scripts

---

## 🎯 PRÓXIMAS MEJORAS (Futuro)

- [ ] Autenticación con Laravel Breeze
- [ ] Sistema de notificaciones
- [ ] Reportes PDF/Excel
- [ ] Gráficos con Chart.js
- [ ] API REST
- [ ] Auditoría de cambios

---

## 📞 SOPORTE TÉCNICO

**Problemas Comunes:**
1. MySQL no inicia → `net start MySQL80` (Windows)
2. Tabla no encontrada → `php artisan migrate`
3. Clase no existe → `composer dump-autoload`
4. Error 500 → Ver `storage/logs/laravel.log`

**Logs:** `storage/logs/laravel.log`  
**Cache:** `php artisan cache:clear`  
**Config:** `php artisan config:clear`

---

## 📄 INFORMACIÓN FINAL

**Licencia:** MIT  
**Autor:** PaNcHoMaLOsO  
**GitHub:** https://github.com/PaNcHoMaLOsO/estoicosgym  
**Versión:** 1.0.0  
**Estado:** ✅ PRODUCCIÓN  

---

**Documento creado para facilitar la transferencia de contexto entre IAs**  
**Última actualización:** 25/11/2025
