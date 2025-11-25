# EstóicosGym - Sistema de Gestión de Gimnasio

## 🎯 Descripción

Sistema web completo de gestión de gimnasio desarrollado con **Laravel 10** y **PHP 8.2**.

**Stack:**
- Backend: PHP + Laravel
- Frontend: HTML5 + CSS3 + JavaScript Vanilla
- Base de Datos: MySQL 8.0
- Servidor: Apache
- Entorno Local: XAMPP
- Producción: Hosting Compartido (Apache + PHP)

**⚠️ Importante:** Este proyecto NO requiere Node.js, npm ni Vite. Es una aplicación 100% PHP/Laravel.

---

## 🚀 Inicio Rápido

### 1. Requisitos
- PHP 8.2+
- MySQL 8.0+
- Composer
- XAMPP (para desarrollo local)

### 2. Instalación

```bash
# Clonar el repositorio
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym

# Instalar dependencias PHP
composer install

# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Cargar datos iniciales (seeders)
php artisan db:seed
```

### 3. Ejecutar la Aplicación

```bash
# Iniciar servidor Laravel
php artisan serve

# Acceder a http://localhost:8000
```

---

## 📁 Estructura del Proyecto

```
estoicosgym/
├── app/
│   ├── Models/              # Modelos Eloquent (13 modelos)
│   └── Http/
│       └── Controllers/     # Controladores (4 CRUD)
├── database/
│   ├── migrations/          # 14 migraciones personalizadas
│   └── seeders/            # 7 seeders con datos iniciales
├── resources/
│   ├── views/              # Vistas Blade
│   ├── css/
│   │   └── app.css         # Estilos CSS puro
│   └── js/
│       └── main.js         # JavaScript vanilla
├── routes/
│   ├── web.php             # Rutas web (23 rutas)
│   └── api.php             # Rutas API
├── public/
│   ├── css/                # Estilos compilados
│   └── js/                 # Scripts compilados
└── config/                 # Archivos de configuración
```

---

## 🗄️ Base de Datos

### Migraciones (14 tablas)
1. `users` - Usuarios del sistema
2. `estados` - Estados (activo, inactivo, suspenso)
3. `metodos_pago` - Métodos de pago (efectivo, transferencia, tarjeta)
4. `motivos_descuento` - Razones de descuentos
5. `membresias` - Tipos de membresías (básica, premium)
6. `precios_membresias` - Precios de membresías
7. `historial_precios` - Histórico de cambios de precios
8. `roles` - Roles de usuario (admin, recepción, cliente)
9. `convenios` - Convenios con terceros
10. `clientes` - Información de clientes
11. `inscripciones` - Inscripciones a membresías
12. `pagos` - Registro de pagos
13. `auditoria` - Auditoría de cambios
14. `notificaciones` - Notificaciones del sistema

### Relaciones
```
Cliente → Inscripción → Pago → MetodoPago
       ↓
    Membresía → PrecioMembresía
       ↓
    Estado
```

---

## 🎮 Modelos y Controladores

### Modelos (13)
- Cliente, Inscripcion, Pago, Membresía
- Estado, MetodoPago, MotivosDescuento
- PrecioMembresía, HistorialPrecio
- Convenio, Auditoria, Notificacion
- User

### Controladores (4)
- `DashboardController` - Panel de control (8 agregaciones)
- `ClienteController` - CRUD de clientes
- `InscripcionController` - Inscripciones con lógica
- `PagoController` - Registro de pagos

---

## 🎨 Frontend

### Assets
- **CSS:** Archivo único `app.css` con estilos base + Bootstrap 5
- **JS:** `main.js` con funcionalidad vanilla (sin dependencias)
- **CDN:** Bootstrap 5 desde jsdelivr.net

### Vistas Blade (10+)
- `welcome.blade.php` - Página de inicio
- `dashboard/index.blade.php` - Panel principal
- CRUD views para clientes, inscripciones, pagos

---

## 🔐 Autenticación

Usa Laravel Sanctum para autenticación. 

Rutas protegidas:
- `/dashboard` - Panel de control
- `/clientes` - Gestión de clientes
- `/inscripciones` - Inscripciones
- `/pagos` - Registro de pagos

---

## 📊 API Endpoints

El proyecto incluye endpoints RESTful:
- `GET /api/clientes` - Listar clientes
- `POST /api/pagos` - Crear pago
- `GET /api/inscripciones` - Listar inscripciones
- etc.

---

## 🔧 Configuración

### `.env`
```env
APP_NAME=EstóicosGym
APP_ENV=local
APP_DEBUG=true
APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbestoicos
DB_USERNAME=root
DB_PASSWORD=
```

---

## 📝 Comandos Útiles

```bash
# Migraciones
php artisan migrate               # Ejecutar todas las migraciones
php artisan migrate:fresh --seed  # Resetear BD y ejecutar seeders
php artisan migrate:status        # Ver estado de migraciones

# Seeders
php artisan db:seed               # Ejecutar seeders
php artisan db:seed --class=ClienteSeeder

# Cache
php artisan config:cache          # Cachear configuración
php artisan cache:clear           # Limpiar cache

# Rutas
php artisan route:list            # Ver todas las rutas

# Desarrollo
php artisan serve                 # Iniciar servidor
php artisan tinker                # Consola interactiva
```

---

## 🚀 Despliegue en Hosting

1. Subir archivos a hosting (excepto `/vendor`, `/storage`)
2. Ejecutar `composer install` en el servidor
3. Copiar `.env` y configurar BD
4. Ejecutar `php artisan migrate`
5. Configurar permisos: `chmod -R 775 storage bootstrap/cache`
6. Acceder a `www.tudominio.com`

---

## 📱 Responsive

- Móvil: 320px - 768px
- Tablet: 768px - 1024px
- Desktop: 1024px+

---

## 🤝 Contribuciones

Para cambios, por favor abrir un pull request o contactar al administrador.

---

## 📄 Licencia

Proyecto académico - Uso interno.

---

## 👨‍💻 Autor

**Usuario:** PaNcHoMaLOsO
**GitHub:** https://github.com/PaNcHoMaLOsO

---

## ⚠️ Notas Importantes

- **Sin Node.js:** Este proyecto no usa Node.js, npm ni Vite
- **CSS/JS directo:** Los estilos y scripts se sirven directamente desde `public/`
- **Compatible Apache:** Funciona en cualquier servidor con PHP 8.2+
- **BD XAMPP:** Configurado para usar XAMPP local (ajustar en `.env` según necesidad)

---

## 🆘 Soporte

En caso de problemas:
1. Verificar `.env` está configurado correctamente
2. Ejecutar `php artisan config:cache`
3. Limpiar cache: `php artisan cache:clear`
4. Verificar permisos de carpeta `storage/`
5. Revisar logs en `storage/logs/laravel.log`

---

**Versión:** 1.0  
**Última actualización:** 25 de Noviembre de 2025
