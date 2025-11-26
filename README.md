# 💪 EstóicosGym - Sistema de Gestión de Membresías

![Laravel](https://img.shields.io/badge/Laravel-12.0-red?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?style=flat-square)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.15-darkblue?style=flat-square)
![Estado](https://img.shields.io/badge/Estado-Producción-brightgreen?style=flat-square)

**Sistema profesional y robusto** de gestión de clientes, membresías y pagos para gimnasios.  
Construido con **Laravel 12**, **AdminLTE 3**, **MySQL 8** y **PHP 8.2+**

---

## 📋 Tabla de Contenidos

1. [Requisitos](#requisitos)
2. [Instalación](#instalación)
3. [Configuración](#configuración)
4. [Características](#características)
5. [Uso del Sistema](#uso-del-sistema)
6. [Estructura del Proyecto](#estructura-del-proyecto)
7. [Problemas Comunes](#problemas-comunes)
8. [Soporte](#soporte)

---

## ✅ Requisitos

Asegúrate de tener instalado:

- **PHP 8.2 o superior** - [Descargar PHP](https://www.php.net/downloads)
- **Composer 2.x** - [Descargar Composer](https://getcomposer.org/download/)
- **MySQL 8.0 o superior** - [Descargar MySQL](https://www.mysql.com/downloads/)
- **Git** - [Descargar Git](https://git-scm.com/download/)

Verificar instalación:
```bash
php --version
composer --version
mysql --version
git --version
```

---

## 🚀 Instalación Completa

### 1. Clonar el Repositorio

```bash
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym
```

### 2. Instalar Dependencias PHP

```bash
composer install
```

### 3. Configurar Archivo .env

```bash
cp .env.example .env
```

Editar `.env` con tu configuración:

```env
APP_NAME=EstóicosGym
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 5. Crear Base de Datos

```bash
mysql -u root -p
CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 6. Ejecutar Migraciones

```bash
php artisan migrate
php artisan db:seed
```

### 7. Iniciar Servidor

```bash
php artisan serve
```

✨ **Acceder en:** `http://localhost:8000/dashboard`

---

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
APP_NAME=EstóicosGym
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=
```

Para **producción**, cambiar:
```env
APP_ENV=production
APP_DEBUG=false
```

---

## ✨ Características del Sistema

### ✅ Implementadas

- **Gestión de Clientes**: CRUD completo con validación RUT chileno
- **Inscripciones/Membresías**: Crear, editar, ver, eliminar
- **Sistema de Pagos**: Registro y seguimiento de pagos
- **Sistema de Pausa**: Pausar y reanudar membresías por días (7, 14, 30)
- **Dashboard**: Estadísticas en tiempo real
- **Interfaz AdminLTE 3**: Diseño profesional y responsivo
- **Base de Datos**: 14 tablas relacionales
- **Estados de Pago**: Corrección de cálculos de estados (Pagado, Parcial, Pendiente)
- **Filtros y Búsqueda**: En todos los listados
- **Datos de Prueba**: Seeders listos para usar

### 📊 Datos que se Crean al Inicializar

- 5 Estados (Activa, Vencida, Pausada, Cancelada, Pendiente)
- 5 Métodos de Pago (Efectivo, Transferencia, Tarjeta, Cheque, Otro)
- 10 Clientes de prueba
- 20 Inscripciones de ejemplo
- 60 Pagos de ejemplo

---

## 🎯 Uso del Sistema

### Módulos Principales

#### 👥 **Clientes**
- **URL:** `http://localhost:8000/admin/clientes`
- Crear, listar, editar y eliminar clientes
- Validación automática de RUT chileno
- Campos: RUT, Nombres, Apellidos, Email, Celular, Dirección

#### 📝 **Inscripciones (Membresías)**
- **URL:** `http://localhost:8000/admin/inscripciones`
- Gestionar membresías activas, vencidas, pausadas
- Ver estado de pagos
- Pausar/Reanudar membresías
- Campos: Cliente, Fecha Inicio, Fecha Vencimiento, Estado

#### 💰 **Pagos**
- **URL:** `http://localhost:8000/admin/pagos`
- Registrar pagos de membresías
- Filtrar por estado (Pagado, Pendiente, Parcial)
- Método de pago registrado
- Campos: Inscripción, Monto, Fecha, Método, Estado

---

## 📁 Estructura del Proyecto

```
estoicosgym/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php
│   │   └── Admin/
│   │       ├── ClienteController.php
│   │       ├── InscripcionController.php
│   │       └── PagoController.php
│   ├── Models/
│   │   ├── Cliente.php
│   │   ├── Inscripcion.php (con métodos pausar, reanudar)
│   │   ├── Pago.php
│   │   ├── Estado.php
│   │   └── (+ 10 modelos más)
│   └── Rules/
│       └── RutValido.php
├── database/
│   ├── migrations/       (20 migraciones)
│   └── seeders/          (7 seeders)
├── resources/views/
│   ├── admin/            (CRUD views)
│   ├── dashboard/
│   └── layouts/
├── routes/
│   └── web.php           (23 rutas)
├── config/
├── public/
└── storage/
```

---

## 🔧 Comandos Útiles

```bash
# Servidor
php artisan serve                 # Iniciar servidor (puerto 8000)

# Base de datos
php artisan migrate               # Ejecutar migraciones
php artisan db:seed               # Cargar datos de prueba
php artisan migrate:reset          # Revertir y reiniciar

# Cache
php artisan cache:clear           # Limpiar cache
php artisan config:clear          # Limpiar configuración
php artisan view:clear            # Limpiar vistas

# Debugging
php artisan tinker                # Consola interactiva
tail -f storage/logs/laravel.log  # Ver logs en tiempo real
```

---

## 🐛 Problemas Comunes

### MySQL no está iniciado

**Windows (XAMPP):**
```bash
# Abrir XAMPP y hacer clic en "Start"
```

**Linux:**
```bash
sudo systemctl start mysql
```

### "SQLSTATE[HY000]" - Tabla no encontrada

```bash
php artisan migrate
php artisan db:seed
```

### "Class not found" - Dependencias incompletas

```bash
composer install
composer dump-autoload
```

### Error 500 - Página en blanco

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
tail -f storage/logs/laravel.log
```

---

## 📊 Stack Tecnológico

| Componente | Versión | Propósito |
|-----------|---------|----------|
| Laravel | 12.0 | Framework PHP |
| PHP | 8.2+ | Lenguaje backend |
| MySQL | 8.0+ | Base de datos |
| AdminLTE | 3.15 | Tema UI |
| Bootstrap | 5.3 | CSS Framework |
| Composer | 2.x | Gestor dependencias |

---

## 🎉 Inicio Rápido (5 minutos)

Ver el archivo **[INICIO_RAPIDO.md](INICIO_RAPIDO.md)** para una instalación step-by-step más simple.

---

## 📞 Soporte y Ayuda

1. **Revisar logs:** `storage/logs/laravel.log`
2. **Consola del navegador:** F12 en el navegador
3. **Terminal:** El servidor muestra errores en tiempo real

---

## 📄 Información Adicional

- **Licencia:** MIT
- **Autor:** PaNcHoMaLOsO
- **GitHub:** [@PaNcHoMaLOsO](https://github.com/PaNcHoMaLOsO)
- **Versión:** 1.0.0 - Estado Final
- **Última actualización:** 2025

**Sistema en producción y completamente funcional. ¡Listo para usar!**


