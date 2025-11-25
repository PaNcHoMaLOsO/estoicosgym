# Comandos Útiles - Sistema Estoicos Gym

## 🚀 Instalación Inicial

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 3. Crear tablas e insertar datos
php artisan migrate:fresh --seed

# 4. Servir la aplicación
php artisan serve
npm run dev  # En otra terminal
```

## 📊 Migraciones

```bash
# Ejecutar todas las migraciones
php artisan migrate

# Deshacer la última migración
php artisan migrate:rollback

# Deshacer todo y volver a ejecutar
php artisan migrate:fresh

# Deshacer todo, volver a ejecutar y ejecutar seeders
php artisan migrate:fresh --seed

# Ver estado de migraciones
php artisan migrate:status
```

## 🌱 Seeders (Datos Iniciales)

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar seeder específico
php artisan db:seed --class=EstadoSeeder

# Ejecutar seeders después de migrar
php artisan migrate:fresh --seed
```

## 🔧 Caché y Configuración

```bash
# Limpiar caché de aplicación
php artisan cache:clear

# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Limpiar caché de vistas
php artisan view:clear

# Limpiar todo
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

## 👤 Usuarios y Autenticación

```bash
# Crear usuario admin
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password'), 'id_rol' => 1])

# O crear con factory
>>> User::factory()->create(['id_rol' => 1])
```

## 📜 Rutas

```bash
# Ver todas las rutas
php artisan route:list

# Buscar rutas específicas
php artisan route:list | grep clientes
php artisan route:list --name=clientes
```

## 🗄️ Base de Datos

```bash
# Acceder a tinker (REPL de Laravel)
php artisan tinker

# Ejemplos dentro de tinker:
>>> App\Models\Cliente::count()
>>> App\Models\Inscripcion::with('cliente', 'membresia')->get()
>>> App\Models\Pago::where('id_estado', 302)->sum('monto_abonado')
```

## 🔍 Debugging

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log

# Modo debug activado en .env
APP_DEBUG=true
```

## 🏗️ Estructura

```bash
# Ver estructura de carpetas
tree app/Models
tree app/Http/Controllers
tree database/migrations
tree resources/views
```

## 📦 Composer

```bash
# Instalar dependencias
composer install

# Actualizar dependencias
composer update

# Autoload composer
composer dump-autoload

# Limpiar caché de composer
composer clear-cache
```

## 📝 Artisan (Comandos Personalizados)

```bash
# Crear un modelo con migración
php artisan make:model NombreModelo -m

# Crear un controlador
php artisan make:controller NombreController

# Crear una migración
php artisan make:migration nombre_migracion

# Crear un seeder
php artisan make:seeder NombreSeeder

# Listar todos los comandos
php artisan list
```

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Ejecutar test específico
php artisan test tests/Feature/ClienteTest.php

# Con coverage
php artisan test --coverage
```

## 🚢 Deploy

```bash
# Instalar dependencias de producción
composer install --optimize-autoloader --no-dev

# Compilar assets
npm run build

# Ejecutar migraciones en producción
php artisan migrate --force
```

## 🔐 Seguridad

```bash
# Verificar vulnerabilidades
composer audit

# Actualizar dependencias
composer update

# Regenerar clave de aplicación
php artisan key:generate
```

## 📊 Estadísticas

```bash
# Ver información del proyecto
php artisan about

# Información de la BD
php artisan db:show
```

## 🔗 Links Útiles

```bash
# Generar enlace simbólico para storage
php artisan storage:link

# Ver configuración
php artisan config:show
```

---

## 📋 Verificación Rápida

```bash
# 1. Verificar base de datos
php artisan tinker
>>> DB::table('clientes')->count()
>>> DB::table('inscripciones')->count()
>>> DB::table('pagos')->count()

# 2. Verificar modelos
>>> App\Models\Cliente::first()
>>> App\Models\Inscripcion::with('cliente', 'membresia')->first()

# 3. Verificar rutas
php artisan route:list | head -20
```

---

## 💡 Consejos

- Siempre hacer backup antes de `migrate:fresh`
- Usar `--seed` para recargar datos de prueba
- Revisar logs en `storage/logs/laravel.log`
- Usar `php artisan tinker` para debugging rápido
- Mantener `.env` seguro y no commitear a Git

