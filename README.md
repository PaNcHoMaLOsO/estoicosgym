# PRO GYM — Sistema de gestión

Gestión de socios, membresías y pagos para el gimnasio.
**Laravel 12 · PHP 8.2 · MySQL · Inertia + React 19 · Tailwind 4**

---

## Hay dos paneles, y conviven

El sistema está a mitad de una migración. Los dos funcionan y comparten la misma
base de datos y la misma lógica de negocio.

| | Dónde | Estado |
|---|---|---|
| **Panel nuevo** | `/panel` | Inertia + React. El día a día completo del gimnasio |
| **Panel antiguo** | `/admin` | Blade + AdminLTE. Se mantiene en pie, pero el panel nuevo ya no enlaza a él |

**El panel nuevo cubre el trabajo entero**: alta de socios, inscribir, cobrar,
corregir o anular un pago, renovar, pausar, reanudar, traspasar, cambiar de
plan, dar de baja, papelera, informes con constructor a medida, configuración de
planes y precios, y los correos —uno a un socio, un aviso a un grupo, y el texto
de las plantillas—.

El panel antiguo sigue sirviendo las mismas pantallas por si hiciera falta
volver a alguna, pero desde `/panel` ya no se llega a él por ningún enlace.

La lógica compartida vive en servicios (`app/Services/`) y no en los
controladores, justamente para que los dos paneles no se separen. `RegistroClienteService`
da de alta socios, `RegistroInscripcionService` inscribe y renueva,
`RegistroPagoService` cobra, `EnvioManualService` y `EnvioMasivoService` mandan
los correos, y `ConstructorInformes` arma los informes a medida.

---

## Levantarlo

Hace falta PHP 8.2+, Composer, MySQL 8 (o MariaDB 10.4) y Node 18+.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Crea la base y ponla en el `.env` (`DB_DATABASE`), luego:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Con XAMPP en Windows, MySQL se arranca así:

```bash
/c/xampp/mysql/bin/mysqld.exe --defaults-file=/c/xampp/mysql/bin/my.ini --standalone
```

### Datos de prueba

`DatabaseSeeder` solo crea los catálogos (roles, estados, planes, precios,
formas de pago). Para poblar con socios:

```bash
php artisan db:seed --class=DatosMasivosSeeder
```

Genera 100 socios con su inscripción y su pago, **coherentes entre sí**: el
estado se deduce de los montos y las fechas, los precios salen de
`precios_membresias` y ningún pago queda fechado en el futuro.

> `composer limpiar-y-cargar` hace `migrate:fresh` y **borra toda la base**.

---

## Entrar

En `http://localhost:8000/panel`:

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@progym.cl` | `password` |
| Recepcionista | `recepcion@progym.cl` | `password` |

Salen de `DatabaseSeeder`. **Cámbialas antes de poner esto en producción**:

```bash
php artisan tinker
```

```php
$u = App\Models\User::where('email', 'admin@progym.cl')->first();
$u->password = Hash::make('la-nueva');
$u->save();
```

**Los permisos por rol se aplican de verdad.** Recepción hace el trabajo de
mesón —altas, inscripciones, cobros, pausar, renovar, traspasar— y no entra a la
configuración del gimnasio ni a los informes de ingresos, ni borra nada. El
reparto se define en `RolesSeeder` y lo aplica `App\Support\Permisos`, que deduce
el permiso del nombre de la ruta.

```bash
php artisan permisos:revisar          # qué permiso exige cada ruta
php artisan permisos:revisar --rol=2  # a qué NO llega recepción
```

Falla si alguna ruta del panel quedó sin clasificar: un permiso olvidado no da
error, simplemente deja pasar.

---

## Correo

Todo el sistema envía por `App\Services\CorreoService`, que admite dos vías
—SMTP con PHPMailer, o la API de Resend— y una de respaldo por si la principal
falla. Se configura en `config/correo.php` y en el `.env`.

```bash
php artisan correo:verificar          # comprueba la configuración SIN enviar nada
php artisan correo:verificar resend   # solo una vía
```

Con Gmail hace falta la verificación en 2 pasos y una **contraseña de
aplicación** de 16 caracteres, pegada sin los espacios con que Google la muestra.
Resend solo envía desde un dominio verificado en su panel.

No se envía a los dominios reservados (`example.com`, `.test`, `.invalid`): la
base de pruebas está llena de correos así y mandarles algo solo acumula rebotes
desde la cuenta real del gimnasio.

---

## Comandos propios

```bash
php artisan inscripciones:actualizar-estados --dry-run  # marca vencidas y cierra pausas
php artisan notificaciones:generar                      # arma los avisos automáticos
php artisan notificaciones:enviar                       # los manda
php artisan correo:verificar
php artisan permisos:revisar
```

---

## Pruebas

```bash
php artisan test
```

Corren sobre SQLite en memoria, sin tocar tu base. Las de `tests/Feature/Regresiones/`
cubren fallos que ocurrieron de verdad y están escritas para fallar si alguien
los reintroduce: el pago duplicado por doble clic, el abono que reventaba, el
segundo factor que se saltaba solo, el enlace de recuperación que no caducaba,
la renovación que nunca llegó a guardarse, «no paga ahora» que dejaba
inscripciones sin ningún pago detrás, y el reenvío de un correo que disparaba
todos los demás de la cola.

`TodasLasPantallasAbrenTest` recorre TODAS las rutas GET del panel con datos
detrás. Es la más tonta y la que más veces ha servido: una pantalla que revienta
al abrirse no la detecta ninguna prueba de negocio.

---

## Cómo está montado

```
app/
  Http/Controllers/Admin/    panel antiguo (Blade)
  Http/Controllers/Panel/    panel nuevo (Inertia)
  Services/                  logica compartida por los dos paneles
  Support/Permisos.php       qué permiso exige cada ruta
resources/
  js/                        panel nuevo: paginas, componentes y tokens de diseno
  views/admin/               panel antiguo
  views/emails/              plantillas de correo
```

Los colores salen del logotipo y viven en `resources/css/tokens.css`. Se editan
a mano: el generador que los producía es del otro proyecto y aquí no existe.

---

**Licencia MIT** · [@PaNcHoMaLOsO](https://github.com/PaNcHoMaLOsO)
