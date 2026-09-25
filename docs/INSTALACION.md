# Instalación

El sistema corre sobre **PostgreSQL**, que es lo que usa el servidor. En este
equipo PostgreSQL va en Docker; en el servidor, el que tenga instalado.

---

## En este equipo (Windows)

### Lo que tiene que haber

- **Docker Desktop**, abierto. La base es el contenedor `estoicosgym-pg`
  (PostgreSQL 16, volumen `estoicosgym-pg-datos`, puerto 5432). Arranca solo con
  Docker Desktop (`--restart unless-stopped`).
- **PHP 8.4** en `C:\php84`, que trae `pdo_pgsql`. El `php` que está en el PATH
  es el de XAMPP 8.2 y **no sirve**: no sabe hablar con PostgreSQL.
- **Node 18+** para compilar el panel.

### Arrancarlo

```bash
C:/php84/php.exe artisan serve --host=127.0.0.1 --port=8000
```

<http://127.0.0.1:8000/panel>

### Si Docker Desktop no arranca

Si se abre y se cierra solo, y el registro
(`%LOCALAPPDATA%\Docker\log\host\com.docker.backend.exe.log`) dice
`dockerInference … The file cannot be accessed by the system`, quedó trabado un
archivo de una sesión anterior. Se renombra la carpeta y se vuelve a abrir:

```powershell
Rename-Item "$env:LOCALAPPDATA\Docker\run" "run-viejo"
```

### La base, desde cero

Solo si hay que crear el contenedor otra vez (la clave va en el `.env`):

```bash
docker run -d --name estoicosgym-pg --restart unless-stopped \
  -e POSTGRES_USER=estoicos -e POSTGRES_PASSWORD=la-clave -e POSTGRES_DB=dbestoicos \
  -p 127.0.0.1:5432:5432 -v estoicosgym-pg-datos:/var/lib/postgresql/data postgres:16

C:/php84/php.exe artisan migrate --seed
```

`--seed` carga los catálogos —roles, estados, planes, precios y medios de
pago— y, si no hay ninguna cuenta, crea `admin@progym.cl` y
`recepcion@progym.cl` con una **clave al azar que muestra una sola vez**:
anótala y cámbiala al entrar.

El puerto de la base queda solo para este equipo (`127.0.0.1:5432`): con
`-p 5432:5432` cualquiera en la misma red podía intentar entrar. Los socios reales se cargan con `datos:importar-planillas` (ver
[MODULOS.md](MODULOS.md#datos-de-las-planillas)).

### Respaldos

```bash
docker exec estoicosgym-pg pg_dump -U estoicos dbestoicos > respaldo.sql
docker exec -i estoicosgym-pg psql -U estoicos dbestoicos < respaldo.sql
```

Los de la mudanza desde MySQL (septiembre de 2026) están en
`D:\Projects\respaldos_estoicosgym\`.

---

## El servidor

### Primera vez

```bash
git clone -b migracion-react https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed --force
```

En el `.env` del servidor:

| Variable | Valor |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` — con `true` cualquier error enseña código y claves |
| `APP_URL` | la dirección pública, con `https://`. Los enlaces de los correos (contrato, recuperar clave) salen de aquí, y **solo se aceptan peticiones a esa dirección** |
| `SESSION_SECURE_COOKIE` | `true`: la sesión viaja solo por https |
| `DB_*` | los del PostgreSQL del servidor |
| `MAIL_*` | la cuenta de correo. También se puede poner desde el panel: Configuración → Cuenta de correo |

El usuario de PostgreSQL necesita poder crear la extensión `unaccent` (la
instala una migración). Si no puede, el buscador funciona igual pero distingue
tildes: «hernandez» no encontraría «Hernández».

### Cada vez que se actualiza

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan web:aligerar-fotos --confirmar
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

`web:aligerar-fotos` pasa a WebP las fotos que ya estaban subidas. Deja las
originales a propósito: si se borraran en el repositorio, al hacer `git pull`
desaparecerían antes de alcanzar a correr el comando. Cuando todo esté bien:
`php artisan web:aligerar-fotos --confirmar --borrar-viejas`.

### El programador

```
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Es lo que manda los avisos por correo a su hora (Configuración → Avisos
automáticos). La revisión del día —vencidas, pagos, bajas— corre también sin
él, la primera vez que alguien abre el panel cada día; con el cron corre de
madrugada y el panel ya no tiene nada que hacer.

Configuración → Avisos automáticos dice si el programador está corriendo de
verdad: cada vuelta deja un latido.

---

## Pruebas contra PostgreSQL

`phpunit.xml` usa SQLite en memoria. Para correrlas contra PostgreSQL se crea
una base aparte —**nunca la real**: las pruebas la borran— y se pasan las
variables delante:

```bash
docker exec estoicosgym-pg psql -U estoicos -d dbestoicos -c "CREATE DATABASE estoicosgym_pruebas"

DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 DB_DATABASE=estoicosgym_pruebas \
DB_USERNAME=estoicos DB_PASSWORD=la-clave C:/php84/php.exe artisan test
```

En PostgreSQL la numeración de las tablas no vuelve atrás con la transacción
de cada prueba; `Tests\CasoConCatalogos` la reinicia antes de sembrar.
