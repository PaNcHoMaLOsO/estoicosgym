# Cómo funciona cada parte

Lo que hace el sistema y por qué, parte por parte. El código explica el detalle
en sus comentarios; esto es el mapa.

---

## Socios

**Dónde:** `ClienteController`, `RegistroClienteService`, `App\Support\SocioRepetido`.

- **El RUT se guarda siempre igual**: `21.410.708-2`, con la K en mayúscula,
  se escriba como se escriba. El correo, en minúsculas.
- **No se registra dos veces a la misma persona.** Mientras se escribe el RUT,
  el alta pregunta a `/panel/clientes/verificar` y avisa si ya existe —también
  si está de baja o en la papelera— con el botón que corresponde: abrir su
  ficha, venderle un plan o restaurarla. Sin RUT solo se sospecha: mismo
  celular o mismo nombre y apellido. Se avisa y no se bloquea.
- **Activo o de baja.** Un socio queda de baja cuando su última membresía venció
  y no tiene otra (ver [la revisión del día](#la-revisión-del-día)). Venderle un
  plan lo reactiva.
- **Papelera, no borrado.** Lo que se elimina va a `/panel/papelera` y se
  recupera con su historial.
- **Borrar sus datos** (Ley 21.719): se borran nombre, RUT, contacto, foto,
  correos y contratos; sus pagos quedan en las cuentas, sin nombre. Ese socio no
  se puede volver a inscribir.
- **Contrato por correo:** le llega un enlace para firmar en el celular, que
  sirve los días de Configuración → Reglas. Queda la huella del documento
  firmado para probar que no se cambió después.

## Membresías

**Dónde:** `InscripcionCrearController`, `InscripcionRenovarController`,
`RegistroInscripcionService`, `Admin\InscripcionController` (pausar, cambiar de
plan, traspasar).

| Código | Estado |
|---|---|
| 100 | Activa |
| 101 | Pausada |
| 102 | Vencida |
| 103 | Cancelada |

Los códigos se guardan en `id_estado` tal cual: no son el `id` de la tabla
`estados`.

- **Planes:** duran meses o días, con **días de regalo** que se suman al
  vencimiento (el anual con 5 dura un año y cinco días). Cada plan tiene su
  precio normal y el de convenio; un convenio puede tener precios propios.
- **Se puede vender** y **sale en la web** son cosas distintas. La Semana, la
  Quincena, los Dos meses y el Pase diario se venden en el mesón pero no se
  anuncian: sus precios se arreglan con cada persona.
- **Renovar** cierra la membresía anterior como vencida y encadena la nueva. Se
  puede renovar hasta N días antes (Configuración → Reglas).

### La revisión del día

Tres pasos, en orden:

1. `inscripciones:actualizar-estados` — marca vencidas las que pasaron su fecha
   y termina las pausas que ya cumplieron.
2. `pagos:sincronizar-estados` — recuadra saldos.
3. `clientes:desactivar-vencidos` — da de baja a quien tiene alguna vencida y
   ninguna activa ni pausada.

La corre el programador de madrugada. Si no hay programador —en este equipo la
tarea de Windows quedó apagada—, **la corre el primero que abre el panel cada
día** (`App\Http\Middleware\RevisaElDia`), después de mandarle la pantalla y una
sola vez. Sin esta revisión los números se desvían solos: membresías «activas»
con la fecha vencida, y la caja contándolas como vigentes.

## Cobros

**Dónde:** `RegistroPagoService`, `RegistroInscripcionService`, el componente
`resources/js/components/Cobro.jsx` (el mismo en alta, inscripción y renovación).

| Forma | Qué pasa |
|---|---|
| **Todo** | Se cobra el total exacto. No se lee ningún monto: antes, escribir de menos dejaba un pago «completo» debiendo |
| **Una parte** | Un abono menor que el total; el resto queda por cobrar |
| **Varios medios** | Una fila por medio. Al escribir el primero, el segundo se rellena con lo que falta |
| **Nada todavía** | Queda una fila de deuda, para que aparezca en «por cobrar» |

- **Lo que se debe es de la membresía**, no de cada pago: su precio menos todo
  lo abonado (`Inscripcion::deuda`).
- El medio **«Sin registrar»** está apagado: no se ofrece al cobrar. Es para
  pagos de los que no consta si fueron en efectivo o transferencia.

## Fiado

**Dónde:** `FiadoController`, `components/Libreta.jsx`.

La libreta del mesón: lo que alguien se lleva y paga después. Al cobrarlo se
elige **con qué pagó** y pasa a ser ingreso del **mesón** en la caja. Lo cobrado
antes del 25 de septiembre de 2026 no tiene medio y sale como «Sin anotar».

## Talleres y arriendos

**Dónde:** `TallerController`, `CotizacionTallerController`, modelos `Taller`,
`HoraTaller`, `CotizacionTaller`, `CobroTaller`.

La sala que se le arrienda a un colegio y se le factura por hora.

- **Institución**: a quién se le factura, con giro y dirección para el papel.
- **Taller**: precio por hora **con IVA incluido** y horario semanal.
- **Horas del mes**: el horario las propone; se quitan las que no hubo.
- **Cotización**: se arma sola con las clases del horario, agrupadas por semana
  para quitar una semana suspendida de un toque. Número correlativo (siguió al
  77 del papel). Se imprime o guarda como PDF con el formato del Word de
  siempre, con el detalle de horas opcional.
- **Cerrar el mes** deja el cobro con el precio de ese mes congelado. **El neto
  y el IVA salen del total**, no al revés, para cuadrar al peso con la factura
  del SII (600.000 = 504.202 + 95.798). El folio y la fecha de pago se anotan
  después; «Marcar pagada» pone la de hoy.

Esto **no emite facturas**: prepara lo que se escribe en ellas.

## Caja e informes

**Dónde:** `CajaController`, `ReporteController`, `App\Support\IngresosDelNegocio`.

Lo que entró viene de **tres fuentes**, y se ve cada una y el total:

| Fuente | Cuenta el día que… |
|---|---|
| Membresías | se pagó (`pagos.fecha_pago`) |
| Talleres | el colegio **pagó** la factura (`pagado_en`), con IVA |
| Mesón | se cobró el fiado |

Lo que se sigue debiendo no es ingreso: va en «lo que se debe» —membresías,
facturas de talleres sin pagar, fiado—.

- **Con IVA / Sin IVA** (`/panel/caja?iva=sin`): el IVA de la factura al colegio
  es del SII. Sin él, los talleres cuentan su neto; membresías y mesón no
  cambian. Arriba se ve el IVA de talleres del mes.
- **Con qué pagan** junta membresías y mesón por medio: es lo que se cuadra
  contra el cajón y la cuenta.

`/panel/reportes/negocio` mide altas, renovaciones y bajas por mes. Con los
datos de las planillas la retención no es confiable: las planillas pisaban las
renovaciones, y la pantalla lo avisa.

## Lo que se ve en pantalla

**Dónde:** Configuración → Lo que se ve en pantalla (`privacidad.*`),
`App\Http\Middleware\EscondeElDinero`, `resources/js/Privado.jsx`.

Para el mesón, donde hay gente mirando detrás: tapar importes, cerrar Caja,
esconder lo fiado, esconder quién debe y abreviar los nombres del Resumen
(«Camila R.»). No cambia permisos: solo lo que hay a la vista. El ojo de arriba
(o la tecla O) tapa las cifras en el momento.

## Permisos

**Dónde:** `App\Support\Permisos`, `RolesSeeder`.

El permiso se deduce del nombre de la ruta: `panel.pagos.store` pide
`pagos.crear`. Recepción hace el mesón —altas, inscripciones, cobros, fiado,
horas de taller, cotizar— y no entra a configuración ni a informes de dinero, ni
borra. `php artisan permisos:revisar --rol=2` lista a qué llega y a qué no.

**Cerrado por defecto:** una ruta del panel que no esté clasificada la usa solo
el administrador, hasta que se agregue a `Permisos`.

| Recepción **puede** | Recepción **no puede** |
|---|---|
| Resumen, buscar y ver socios, alta, editar la ficha, foto, celular | Eliminar socios ni borrar sus datos |
| Dar de baja y reactivar a un socio | Configuración: planes, precios, convenios, web, correo, usuarios, papelera |
| Contrato: enviarlo, verlo, anularlo | Caja e informes |
| Inscribir, renovar, pausar, reanudar, cambiar de plan, traspasar | Eliminar una membresía |
| **Corregir una membresía** (fechas, precio, descuento) | Corregir o eliminar un pago ya registrado |
| Cobrar (registrar pagos) | Cerrar el mes de un taller, corregir o borrar cobros y cotizaciones |
| Fiado: anotar, cobrar, deshacer un cobro. Notas del mesón. Canje | Borrar líneas del fiado o notas |
| Talleres: crear, anotar horas, cotizar e imprimir | Editar o eliminar un taller |
| Correos a un socio, reenviar, cancelar; **envío a un grupo** | Crear o cambiar las plantillas de correo |
| Historial de cambios | |

## Correo

**Dónde:** `App\Services\CorreoService`, Configuración → Cuenta de correo.

- Sale por **SMTP** (Gmail, con contraseña de aplicación) o por la **API de
  Resend**, con otra vía de respaldo si la primera falla.
- Las respuestas de los socios llegan al **correo de contacto** del gimnasio
  (`Reply-To`), aunque se envíe desde otra cuenta.
- Gmail gratis corta a los **500 correos al día**; pasado eso bloquea el envío
  hasta 24 horas.
- No se envía a dominios de prueba (`example.com`, `.test`).

## La web pública

**Dónde:** `LandingController`, `resources/views/landing/`.

- En Planes salen solo los que tienen «Sale en la página de planes».
- Especialistas, embajadores y testimonios se cargan en Configuración → Página
  web. `web:ejemplos` pone unos de muestra y `--quitar` los saca: un testimonio
  inventado publicado es publicidad engañosa.
- **Cada especialista tiene su perfil** (`/especialistas/su-nombre`, la dirección
  sale sola del nombre): la foto grande, cómo atiende, los temas en que se enfoca,
  su presentación y los botones para escribirle. En la lista va solo la foto, la
  especialidad y el nombre: con la presentación encima, la foto quedaba tapada.
  Los perfiles salen en el `sitemap.xml`.
- **Las fotos** se guardan livianas (`App\Support\FotoLiviana`): WebP, achicadas
  por el lado más largo. La galería se puede ordenar sola —las panorámicas
  primero y sin dos parecidas seguidas— o con flechas.
- **Consulta tu membresía**: con RUT, o con celular y primer nombre. Se bloquea
  tras varios intentos fallidos.

## Datos de las planillas

**Dónde:** `datos:importar-planillas`, `scripts/planillas_a_csv.py`.

Los socios se cargaron desde las planillas Excel del gimnasio (septiembre de
2026): 1.704 socios y 1.770 membresías con sus fechas. **Sin plata**: la
planilla traía el monto pero no con qué se pagó, así que esas membresías quedan
en $0 y sin pagos (`datos:quitar-pagos-importados`). `--con-pagos` los vuelve a
traer. El CSV con datos personales vive en `storage/app/private/importacion/`
y **no va a git**.

## PostgreSQL

El servidor usa PostgreSQL. El código nuevo tiene que respetar:

- **Buscar texto** con `->whereParecido('columna', "%texto%")`
  (`App\Support\Parecido`): sin mirar mayúsculas ni tildes. `like` a secas
  distingue las dos en PostgreSQL.
- **Columnas uuid:** comprobar `Str::isUuid()` antes de buscar algo que viene
  del navegador. En PostgreSQL, comparar una columna uuid con cualquier texto
  revienta en vez de no encontrar.
- **Sí/no:** `true`/`false`, nunca `1`/`0`.
- **Nada de `->change()` sobre un `enum`:** en PostgreSQL es una restricción
  CHECK y hay que cambiarla a mano (ver la migración de `historial_cambios`).
