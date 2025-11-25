# 🏋️ EstóicosGym - Sistema de Gestión

Sistema completo de gestión para gimnasios.

**Stack:** Laravel 10 + PHP 8.2 + MySQL 8.0 + HTML5 + CSS3 + JavaScript vanilla

## 🚀 Inicio Rápido

```bash
# Configurar
cp .env.example .env
# Editar .env con BD

# Instalar
composer install
php artisan key:generate

# Crear BD
php artisan migrate:fresh --seed

# Ejecutar
php artisan serve
# Acceder a http://localhost:8000
```

## 📊 Características

✅ Gestión de clientes y membresías  
✅ Sistema de pagos  
✅ Dashboard con estadísticas  
✅ Auditoría de cambios  
✅ Roles y permisos  
✅ 14 tablas de BD  
✅ 13 modelos Eloquent  
✅ 4 controladores CRUD  

## 📁 Estructura

```
app/Models/              13 modelos
app/Http/Controllers/    4 controladores
database/migrations/     14 migraciones
database/seeders/        7 seeders
resources/views/         Vistas Blade
routes/web.php          23 rutas
public/css/             Estilos
public/js/              Scripts
```

## 💻 Comandos

```bash
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan tinker
php artisan serve
```

## 🔐 Requisitos

- PHP 8.2+
- MySQL 8.0+
- Composer

## 📝 Licencia

MIT

---

**Versión:** 1.0  
**Autor:** PaNcHoMaLOsO


