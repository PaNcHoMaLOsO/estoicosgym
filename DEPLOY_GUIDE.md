# Guía de Despliegue - EstoicosGym

## Arquitectura del Sistema

```
tudominio.com/                    → WordPress (Landing Page)
tudominio.com/sistema-admin/      → Laravel (Sistema de Gestión)
```

---

## PARTE 1: Desplegar Laravel

### 1.1 Preparar Archivos Locales

```bash
# Optimizar para producción
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.2 Subir Archivos a cPanel

1. Accede a cPanel → File Manager
2. Crea carpeta `sistema-admin` en `public_html`
3. Sube todos los archivos excepto:
   - `.env` (crear nuevo en servidor)
   - `node_modules/`
   - `.git/`
   - `storage/logs/*`

### 1.3 Configurar Base de Datos

1. cPanel → MySQL Databases
2. Crear base de datos y usuario
3. Asignar permisos

### 1.4 Configurar `.env` en Servidor

Crear archivo `.env` en el servidor con:

```env
APP_NAME="EstoicosGym"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com/sistema-admin

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<NOMBRE_BD>
DB_USERNAME=<USUARIO_BD>
DB_PASSWORD=<PASSWORD_BD>

MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=465
MAIL_USERNAME=<EMAIL>
MAIL_PASSWORD=<PASSWORD>
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=<EMAIL>
MAIL_FROM_NAME="Estoicos Gym"
```

> ⚠️ **IMPORTANTE**: Reemplaza los valores entre `< >` con tus credenciales reales.

### 1.5 Ejecutar Migraciones

```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## PARTE 2: Desplegar WordPress (Opcional)

### 2.1 Instalar WordPress en cPanel

1. cPanel → Softaculous → WordPress
2. Instalar en raíz del dominio
3. Subir tema `estoicos-theme`

---

## Verificación Final

- [ ] Sistema Laravel accesible en `/sistema-admin`
- [ ] Login funcionando
- [ ] Emails enviándose correctamente
- [ ] Base de datos conectada
- [ ] SSL activo (HTTPS)

---

## Soporte

En caso de errores, revisar:
- `storage/logs/laravel.log`
- Logs de error en cPanel
