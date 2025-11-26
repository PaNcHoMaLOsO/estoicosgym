# 🚀 Inicio Rápido - EstóicosGym (5 minutos)

Guía simplificada para instalar y ejecutar EstóicosGym en tu máquina.

---

## 1. Requisitos Previos

Instalar en tu sistema:

- **PHP 8.2+** (descargar desde [php.net](https://www.php.net))
- **Composer** (descargar desde [getcomposer.org](https://getcomposer.org))
- **MySQL 8.0+** (descargar desde [mysql.com](https://www.mysql.com))
- **Git** (descargar desde [git-scm.com](https://git-scm.com))

Verificar que están instalados:
```bash
php --version
composer --version
mysql --version
```

---

## 2. Descargar e Instalar

Clonar repositorio e instalar dependencias:

```bash
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym
composer install
```

---

## 3. Configurar Base de Datos

Crear archivo `.env`:

```bash
cp .env.example .env
```

Editar `.env` y configurar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=
```

Crear la base de datos:

```bash
mysql -u root -p
CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

## 4. Inicializar Sistema

Generar clave, migrar BD y cargar datos de prueba:

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```

---

## 5. Ejecutar y Acceder

Iniciar servidor:

```bash
php artisan serve
```

Abrir en navegador:

```
http://localhost:8000/dashboard
```

✨ **¡Listo! El sistema está corriendo.**

---

## 📞 Ayuda Rápida

| Problema | Solución |
|----------|----------|
| MySQL no inicia | Abrir XAMPP y iniciar MySQL |
| "Class not found" | Ejecutar `composer install` |
| "Tabla no encontrada" | Ejecutar `php artisan migrate` |
| Error 500 | Ver `storage/logs/laravel.log` |

---

## 📖 Más Información

- **Documentación completa:** Ver [README.md](README.md)
- **API:** Ver [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Base de datos:** Ver [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)

---

**¡Disfruta del sistema EstóicosGym!** 💪
