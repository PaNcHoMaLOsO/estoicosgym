# 🚀 Guía de Arranque - ESTOICOS GYM

Comandos y pasos necesarios para poner el proyecto en funcionamiento.

---

## 📋 Prerrequisitos

✅ **XAMPP instalado** con MySQL corriendo  
✅ **Composer** instalado  
✅ **Node.js** instalado  
✅ **.env configurado** (ya está listo)  

---

## 🔧 PASO 1: Preparar la Base de Datos

### En XAMPP Control Panel:
1. Asegúrate que **MySQL esté corriendo** ✅
2. Abre **phpMyAdmin** (http://localhost/phpmyadmin)

### En phpMyAdmin:
```sql
-- Crear base de datos
CREATE DATABASE dbestoicos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

O simplemente ejecuta (Laravel lo creará automáticamente con migraciones):
```bash
php artisan migrate:fresh --seed
```

---

## 🏗️ PASO 2: Instalar Dependencias

```bash
# Terminal 1: Instalar PHP dependencies
composer install

# Instalar JavaScript dependencies
npm install
```

**Tiempo estimado**: 3-5 minutos

---

## 🔐 PASO 3: Generar Clave de Aplicación

```bash
php artisan key:generate
```

**Resultado esperado**:
```
Application key [base64:...] set successfully.
```

---

## 💾 PASO 4: Crear Base de Datos y Tablas

```bash
php artisan migrate:fresh --seed
```

**Esto hace**:
- ✅ Crea 14 tablas
- ✅ Ejecuta 7 seeders
- ✅ Inserta datos de prueba
- ✅ Configura relaciones

**Resultado esperado**:
```
Dropped all tables successfully.
Migration table created successfully.
Migrated: 0001_create_estados_table
Migrated: 0002_create_metodos_pago_table
...
Seeding: EstadoSeeder
Seeding: MetodoPagoSeeder
...
Database seeding completed successfully.
```

---

## 🎬 PASO 5: Ejecutar el Servidor

Necesitas **2 terminales** abiertas simultáneamente:

### Terminal 1️⃣ - Servidor Laravel

```bash
php artisan serve
```

**Resultado esperado**:
```
INFO  Server running on [http://127.0.0.1:8000].
```

**La aplicación estará disponible en**: http://localhost:8000

### Terminal 2️⃣ - Build Assets (Vite)

```bash
npm run dev
```

**Resultado esperado**:
```
VITE v5.x.x build ready on 127.0.0.1:5173
```

**Esto compila**:
- CSS de Bootstrap
- JavaScript
- Recursos estáticos

---

## ✅ PASO 6: Verificar Instalación

### 1️⃣ Acceder al Dashboard

Abre en tu navegador:
```
http://localhost:8000/dashboard
```

Deberías ver:
- Header con logo "ESTOICOS GYM"
- 4 tarjetas de estadísticas
- 6 tablas con datos
- Sidebar con navegación

### 2️⃣ Verificar Base de Datos

En phpMyAdmin (http://localhost/phpmyadmin):
- Base de datos: `dbestoicos` ✅
- Tablas: 14 creadas ✅
- Registros: 30+ de seeders ✅

### 3️⃣ Listar Rutas

En terminal, ejecuta:
```bash
php artisan route:list
```

Deberías ver:
```
GET|HEAD  /dashboard .................. dashboard
GET|HEAD  /clientes ................... clientes.index
POST      /clientes ................... clientes.store
...
```

---

## 📱 Rutas Disponibles

Una vez corriendo, accede a:

| Ruta | Descripción |
|------|-------------|
| `http://localhost:8000/dashboard` | 📊 Dashboard principal |
| `http://localhost:8000/clientes` | 👥 Gestión de clientes |
| `http://localhost:8000/inscripciones` | 🏋️ Membresías |
| `http://localhost:8000/pagos` | 💰 Pagos |

---

## 🔄 Comandos Útiles Posteriores

### Limpiar Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Ver Logs
```bash
# En tiempo real
tail -f storage/logs/laravel.log

# En PowerShell
Get-Content -Path storage/logs/laravel.log -Tail 50 -Wait
```

### Resetear Base de Datos
```bash
# Borrar todo y empezar de nuevo
php artisan migrate:fresh --seed
```

### Crear Datos de Prueba
```bash
# Crear 10 clientes más
php artisan tinker
>>> Cliente::factory()->count(10)->create();
>>> exit
```

### Ver Estado
```bash
php artisan tinker

# Contar registros
>>> Cliente::count()
>>> Inscripcion::count()
>>> Pago::count()

# Ver estructura
>>> Cliente::first()

# Salir
>>> exit
```

---

## 🐛 Solución de Problemas

### ❌ Error: "SQLSTATE[HY000] [1045] Access denied"

**Causa**: Credenciales de MySQL incorrectas

**Solución**:
1. Abre `.env`
2. Verifica:
   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dbestoicos
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Crea la BD en phpMyAdmin si no existe

### ❌ Error: "The Application Key is not set"

**Solución**:
```bash
php artisan key:generate
```

### ❌ Error: "Column not found"

**Solución**:
```bash
php artisan migrate:fresh --seed
```

### ❌ Error: "npm command not found"

**Solución**:
- Instala Node.js desde https://nodejs.org/
- Reinicia la terminal

### ❌ Error: "Composer not found"

**Solución**:
- Instala Composer desde https://getcomposer.org/
- Reinicia la terminal

### ❌ Puerto 8000 ya en uso

**Solución**:
```bash
# Usar otro puerto
php artisan serve --port=8001
```

### ❌ Vite no compila

**Solución**:
```bash
npm install
npm run dev
```

---

## 📊 Datos de Prueba Creados

Después de `migrate:fresh --seed`, tienes:

### Usuarios
- **Administrador** - Email: admin@estoicos.local
- **Recepcionista** - Email: recepcionista@estoicos.local

### Membresías
- Anual: $250,000
- Semestral: $150,000
- Trimestral: $90,000
- Mensual: $40,000 (regular) / $25,000 (convenio)
- Pase Diario: $5,000

### Convenios
- INACAP (20% descuento)
- DUOC (15% descuento)
- Cruz Verde (10% descuento)
- Falabella (5% descuento)

### Métodos de Pago
- Efectivo
- Transferencia
- Tarjeta
- Mixto

---

## 🎯 Próximas Acciones

Después del startup exitoso:

1. **✅ Crear vistas de formularios**
   - `resources/views/clientes/{create, edit}.blade.php`
   - `resources/views/inscripciones/{create, edit}.blade.php`
   - `resources/views/pagos/{create, edit}.blade.php`

2. **✅ Implementar autenticación**
   - Login y registro
   - Middleware de permisos
   - Protección de rutas

3. **✅ Agregar validaciones**
   - Front-end con JavaScript
   - Mensajes de error personalizados

4. **✅ Optimizar rendimiento**
   - Caché de consultas
   - Índices de base de datos
   - Lazy loading de relaciones

---

## 📝 Resumen Rápido (TL;DR)

```bash
# 1. Abrir XAMPP y activar MySQL

# 2. Terminal 1
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve

# 3. Terminal 2
npm run dev

# 4. Abrir navegador
http://localhost:8000/dashboard

# ✅ ¡Listo!
```

**Tiempo total**: ~10 minutos

---

## 📚 Documentación Relacionada

- [README.md](README.md) - Inicio rápido general
- [INSTALACION.md](INSTALACION.md) - Instalación detallada
- [COMANDOS_UTILES.md](COMANDOS_UTILES.md) - Comandos frecuentes
- [EJEMPLOS_API.md](EJEMPLOS_API.md) - Ejemplos de código

---

**Última actualización**: 25 de Noviembre, 2025  
**Estado**: ✅ Listo para usar

