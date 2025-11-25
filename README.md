# 💪 EstóicosGym - Sistema de Gestión de Membresías

![Laravel](https://img.shields.io/badge/Laravel-12.0-red?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?style=flat-square)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.15-darkblue?style=flat-square)

Sistema profesional de gestión de clientes, membresías y pagos para gimnasios construido con Laravel 12 y AdminLTE 3.

---

## 📋 Tabla de Contenidos

1. [Requisitos](#requisitos)
2. [Instalación](#instalación)
3. [Configuración](#configuración)
4. [Uso](#uso)
5. [Características](#características)
6. [Problemas Comunes](#problemas-comunes)

---

## ✅ Requisitos

Asegúrate de tener instalado:

- **PHP 8.2 o superior** - [Descargar PHP](https://www.php.net/downloads)
- **Composer 2.x** - [Descargar Composer](https://getcomposer.org/download/)
- **MySQL 8.0 o superior** - [Descargar MySQL](https://www.mysql.com/downloads/)
- **Git** - [Descargar Git](https://git-scm.com/download/)

### Verificar Instalación

```bash
php --version
composer --version
mysql --version
git --version
```

---

## 🚀 Instalación Paso a Paso

### Paso 1: Clonar el Repositorio

```bash
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym
```

### Paso 2: Instalar Dependencias PHP

```bash
composer install
```

**Esto instalará:**
- Laravel 12
- AdminLTE 3.15 (vía Composer)
- Todas las librerías necesarias

### Paso 3: Configurar Archivo .env

```bash
cp .env.example .env
```

Editar `.env` y configurar:

```env
APP_NAME=EstóicosGym
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos - IMPORTANTE: Cambiar según tu configuración
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 4: Generar Clave de Aplicación

```bash
php artisan key:generate
```

### Paso 5: Crear Base de Datos

Abrir línea de comandos MySQL:

```bash
mysql -u root -p
```

Ejecutar:

```sql
CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Paso 6: Ejecutar Migraciones

```bash
php artisan migrate
```

### Paso 7: Cargar Datos de Prueba

```bash
php artisan db:seed
```

Se crearán automáticamente:
- 5 Estados (Activa, Vencida, Pausada, Cancelada, Pendiente)
- 5 Métodos de Pago (Efectivo, Transferencia, Tarjeta, Cheque, Otro)
- 10 Clientes de prueba
- 20 Inscripciones
- 60 Pagos de ejemplo

### Paso 8: Iniciar Servidor

```bash
php artisan serve
```

✨ **Acceder en:** `http://localhost:8000/dashboard`

---

## ⚙️ Configuración de Base de Datos

### Usuarios Soportados

- **Windows (XAMPP):** Usuario `root`, sin contraseña
- **Linux (MariaDB):** Usuario `root`, sin contraseña  
- **Linux (MySQL):** Ajustar `DB_USERNAME` y `DB_PASSWORD` según configuración

### Ejemplo de .env para Windows (XAMPP)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=
```

### Ejemplo de .env para Linux

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

---

## 🎯 Uso del Sistema

### Acceder al Sistema

1. Iniciar servidor: `php artisan serve`
2. Abrir navegador: `http://localhost:8000/dashboard`

### Módulos Disponibles

#### 👥 **Clientes**
- URL: `http://localhost:8000/admin/clientes`
- Crear, listar, ver, editar y eliminar clientes
- Validación automática de RUT chileno
- Campos: RUT, Nombres, Apellidos, Email, Celular, Dirección, Fecha Nacimiento

#### 📝 **Inscripciones (Membresías)**
- URL: `http://localhost:8000/admin/inscripciones`
- Gestionar membresías de clientes
- Filtrar por estado (Activa, Vencida, Pausada, etc.)
- Campos: Cliente, Fecha Inicio, Fecha Vencimiento, Estado

#### 💰 **Pagos**
- URL: `http://localhost:8000/admin/pagos`
- Registrar y seguir pagos de membresías
- Filtrar por inscripción y método de pago
- Campos: Inscripción, Monto, Fecha, Método de Pago

---

## ✨ Características

### ✅ Completadas

- Gestión CRUD de Clientes
- Validación de RUT chileno
- Gestión de Inscripciones/Membresías
- Gestión de Pagos
- Dashboard con estadísticas
- Interfaz AdminLTE 3 (profesional)
- Base de datos relacional (14 tablas)
- Paginación automática (15 registros)
- Filtros en listados
- Datos de prueba incluidos

### 🔄 Próximas Fases

- Autenticación y control de roles
- Sistema de notificaciones
- Reportes y gráficos
- Exportación a Excel
- Panel de control mejorado

---

## 🐛 Problemas Comunes

### "Connection refused" - MySQL no está iniciado

**Windows (XAMPP):**
```bash
# Abrir XAMPP y hacer clic en "Start" en Apache y MySQL
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

Revisar logs:
```bash
tail -f storage/logs/laravel.log
```

Limpiar caché:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 🔧 Comandos Útiles

```bash
# Servidor
php artisan serve                 # Iniciar servidor (puerto 8000)

# Base de datos
php artisan migrate               # Ejecutar migraciones
php artisan db:seed               # Cargar datos de prueba
php artisan migrate:reset          # Revertir todo y reiniciar

# Cache
php artisan cache:clear           # Limpiar cache
php artisan config:clear          # Limpiar configuración

# Debugging
php artisan tinker                # Consola interactiva
tail -f storage/logs/laravel.log  # Ver logs en tiempo real
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
| Composer | 2.x | Gestor de dependencias |

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
│   └── Models/
│       ├── Cliente.php
│       ├── Inscripcion.php
│       ├── Pago.php
│       └── (+ 11 modelos más)
├── database/
│   ├── migrations/    (14 migraciones)
│   └── seeders/       (7 seeders)
├── resources/views/
│   ├── admin/         (12 vistas CRUD)
│   └── dashboard/
├── routes/
│   └── web.php        (23 rutas)
└── README.md          (este archivo)
```

---

## 📞 Soporte y Ayuda

1. **Revisar logs:** `storage/logs/laravel.log`
2. **Consola del navegador:** Presionar F12
3. **Terminal:** El servidor muestra errores en tiempo real

---

## 🎉 Resumen Rápido (5 minutos)

```bash
# 1. Clonar
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym

# 2. Instalar
composer install

# 3. Configurar
cp .env.example .env
# Editar .env si es necesario

# 4. Generar clave
php artisan key:generate

# 5. Base de datos
mysql -u root -p
CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 6. Migraciones
php artisan migrate
php artisan db:seed

# 7. Ejecutar
php artisan serve
```

**Resultado:** Sistema listo en `http://localhost:8000/dashboard` ✨

---

## 📄 Información Adicional

- **Licencia:** MIT
- **Autor:** PaNcHoMaLOsO
- **GitHub:** [@PaNcHoMaLOsO](https://github.com/PaNcHoMaLOsO)
- **Versión:** 1.0.0
- **Última actualización:** 25 de noviembre de 2025

---

**¡Listo para usar! Cualquier duda, revisa la sección de "Problemas Comunes".**


