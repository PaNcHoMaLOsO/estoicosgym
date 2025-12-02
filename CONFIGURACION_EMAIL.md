# Configuración de Email para Producción

## Configuración en cPanel

### 1. Crear cuenta de email en cPanel
1. Entra a cPanel → Email Accounts
2. Crea una cuenta: `notificaciones@tudominio.com`
3. Guarda la contraseña de forma segura

### 2. Configurar en `.env`
Edita el archivo `.env` en el servidor:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=465
MAIL_USERNAME=notificaciones@tudominio.com
MAIL_PASSWORD=<TU_PASSWORD_AQUI>
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notificaciones@tudominio.com
MAIL_FROM_NAME="Estoicos Gym"
```

> ⚠️ **IMPORTANTE**: Nunca subas el archivo `.env` al repositorio. Usa variables de entorno en el servidor.

### 3. Probar la configuración
```bash
php artisan email:test correo@ejemplo.com
```

---

## Alternativa: Gmail con App Password

1. Ve a tu cuenta Google → Seguridad
2. Activa verificación en 2 pasos
3. Genera una "Contraseña de aplicación" para Mail
4. Configura en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<TU_EMAIL>@gmail.com
MAIL_PASSWORD=<APP_PASSWORD>
MAIL_ENCRYPTION=tls
```

⚠️ Gmail tiene límite de 500 emails/día para cuentas gratuitas.

---

## Estado actual: Modo desarrollo
En desarrollo los emails se guardan en: `storage/logs/laravel.log`
