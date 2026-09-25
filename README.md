# PRO GYM — Sistema de gestión

El sistema del gimnasio PRO GYM (Los Ángeles, Biobío): socios, membresías,
cobros, el fiado del mesón, los talleres que se le arriendan a colegios, la caja
y la página web pública.

**Laravel 12 · PHP 8.4 · PostgreSQL 16 · Inertia + React 19 · Tailwind 4**

---

## Qué hace

| Parte | Dónde | Qué resuelve |
|---|---|---|
| **Resumen** | `/panel` | Lo que hay que atender hoy: a quién se le vence, quién no renovó, quién debe fiado, cumpleaños, notas del mesón |
| **Socios** | `/panel/clientes` | Alta rápida, ficha del socio, contrato para firmar por correo, foto. Avisa si la persona ya está registrada mientras se escribe el RUT |
| **Membresías** | `/panel/inscripciones` | Inscribir, renovar, pausar, cambiar de plan, traspasar. A un socio de baja se le vende un plan y se reactiva solo |
| **Cobros** | `/panel/pagos` | Todo, una parte o repartido entre varios medios. «Todo» cobra el total exacto |
| **Fiado** | `/panel/fiados` | La libreta del mesón: lo que se lleva y se paga después |
| **Talleres** | `/panel/talleres` | La sala arrendada a colegios: horario, horas del mes, cotizaciones imprimibles y cobro mensual con IVA |
| **Caja** | `/panel/caja` | Lo que entró, separado en membresías, talleres y mesón, y junto. Lo que se debe |
| **Informes** | `/panel/reportes` | Ingresos del año, membresías, pendientes, cómo va el negocio, y un constructor de informes a medida |
| **Configuración** | `/panel/configuracion` | Planes y precios, convenios, correo, página web, privacidad en pantalla, usuarios, papelera |
| **Web pública** | `/` | Inicio, El gimnasio, Planes, Convenios, Especialistas, Contacto y «consulta tu membresía» |

Cómo funciona cada parte por dentro está en [docs/MODULOS.md](docs/MODULOS.md).

---

## Levantarlo en este equipo

Resumen corto; el paso a paso, en [docs/INSTALACION.md](docs/INSTALACION.md).

1. **Docker Desktop abierto.** PostgreSQL corre en el contenedor `estoicosgym-pg`.
2. **El servidor, con PHP 8.4** (el `php` de XAMPP no trae PostgreSQL):

```bash
C:/php84/php.exe artisan serve --host=127.0.0.1 --port=8000
```

3. Abrir <http://127.0.0.1:8000/panel>.

Si se cambió algo del panel (React), se recompila con `npm run build`.

---

## Subirlo al servidor

El servidor usa PostgreSQL. Después de `git pull`:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan web:aligerar-fotos --confirmar
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Y **el programador**, cada minuto (cron):

```
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Sin el cron no salen los avisos por correo. La revisión diaria —marcar las
membresías vencidas y dar de baja a quien quedó sin plan— corre igual: la hace
el primero que abre el panel cada día.

Todo lo del servidor, en [docs/INSTALACION.md](docs/INSTALACION.md#el-servidor).

---

## Comandos propios

```bash
# La revisión del día (normalmente sola: ver docs/MODULOS.md)
php artisan inscripciones:actualizar-estados --dry-run   # marca vencidas y cierra pausas
php artisan pagos:sincronizar-estados                    # recuadra saldos
php artisan clientes:desactivar-vencidos                 # baja a quien quedó sin plan

# Correo
php artisan notificaciones:generar                       # arma los avisos automáticos
php artisan notificaciones:enviar                        # los manda
php artisan correo:verificar                             # revisa la configuración sin enviar

# Datos
php artisan datos:importar-planillas                     # carga socios desde las planillas (CSV)
php artisan datos:quitar-pagos-importados                # la plata de las planillas fuera
php artisan datos:empezar-de-cero                        # borra socios de prueba, con respaldo
php artisan clientes:normalizar                          # RUT y teléfonos al formato chileno

# Web
php artisan web:aligerar-fotos --confirmar               # fotos ya subidas → WebP livianas
php artisan web:ejemplos [--quitar]                      # especialistas y testimonios de muestra

# Revisión
php artisan permisos:revisar [--rol=2]                   # qué permiso exige cada ruta
```

Los comandos que borran o cambian datos solo cuentan lo que harían si no se les
pasa `--confirmar`.

---

## Pruebas

```bash
C:/php84/php.exe artisan test
```

Corren sobre SQLite en memoria, sin tocar la base. Para correrlas contra
PostgreSQL, que es lo que usa el servidor, ver
[docs/INSTALACION.md](docs/INSTALACION.md#pruebas-contra-postgresql). Hoy pasan
las **679 en los dos**.

Las de `tests/Feature/Regresiones/` cubren fallos que ocurrieron de verdad y
están escritas para fallar si alguien los reintroduce. `TodasLasPantallasAbrenTest`
recorre todas las pantallas del panel con datos detrás.

---

## Cómo está montado

```
app/
  Http/Controllers/Panel/    el panel (Inertia)
  Http/Middleware/           permisos, privacidad del dinero, revisión del día
  Services/                  la lógica de negocio: altas, inscripciones, cobros, correo
  Support/                   piezas chicas compartidas: ajustes, búsqueda, ingresos, fotos
  Console/Commands/          los comandos de arriba
resources/
  js/pages/                  las pantallas del panel (React)
  js/components/             piezas reusables: cobro, tablas, tablero
  views/landing/             la web pública (Blade)
  views/talleres/            la cotización para imprimir
database/migrations/         el esquema; cada migración explica por qué existe
tests/Feature/Regresiones/   una prueba por cada fallo que ya pasó
docs/                        instalación, cómo funciona cada parte y lo pendiente
```

Lo pendiente y lo que se podría integrar está en [docs/MEJORAS.md](docs/MEJORAS.md).
Los documentos de antes —del panel viejo, de 2025— quedaron en
[docs/historico/](docs/historico/) y ya no describen el sistema.

---

**Licencia MIT** · [@PaNcHoMaLOsO](https://github.com/PaNcHoMaLOsO)
