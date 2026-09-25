# Revisión de la lógica: qué se arregló y qué falta

Revisión del 25 de septiembre de 2026, con el sistema ya en PostgreSQL y los
datos reales cargados.

---

## Arreglado en esta revisión

| Qué | Por qué importaba |
|---|---|
| **La revisión del día corre sin programador** (`RevisaElDia`) | La tarea de Windows estaba apagada y no corría nunca: había membresías «activas» con la fecha vencida y 319 socios figurando activos sin nada vigente. La caja contaba como vigente lo que no lo era |
| **Renovar una membresía importada** ya no compara contra $0 | Decía «paga $40.000 más que la vez pasada»: esas membresías quedaron en $0 porque la plata de las planillas se sacó |
| **El correo del socio, sin mayúsculas** | En PostgreSQL «Juan@Gmail.com» y «juan@gmail.com» contaban como distintos y pasaban como dos socios |

Antes de esto, al pasar a PostgreSQL: las búsquedas sin tildes, el login con
mayúsculas, los enlaces con identificadores rotos, dos migraciones que no
corrían y el tipo «Club deportivo» que no se podía guardar. Ver el historial
de git del 24 de septiembre.

---

## Pendiente, por orden de importancia

### 1. Juntar los datos de contacto — lo que más rinde

De **1.704 socios, 1 tiene correo y 23 tienen celular**: las planillas casi no
traían contacto. Los avisos de vencimiento por correo, el contrato por correo y
la lista de «a quién llamar» del Resumen **hoy no le llegan a casi nadie**.

**Propuesta:** que renovar e inscribir pidan el celular —y el correo, opcional—
cuando el socio no lo tiene, en el mismo formulario. El socio que vuelve es el
momento de pedirlo; en unos meses la base tendría contacto de todos los que
siguen viniendo.

### 2. Tope diario de correos

Gmail gratis corta a los 500 correos al día y bloquea el envío hasta 24 horas:
ese día tampoco salen contratos ni recuperación de clave. Hoy hay tope por
envío a un grupo (150), pero no por día.

**Propuesta:** un contador que frene a ~400, dejando margen para lo urgente, y
que Configuración diga cuántos van hoy.

### 3. Fusionar fichas repetidas

Hay **27 nombres repetidos (57 fichas)** y **214 socios sin RUT**, que vienen de
las planillas. Algunos son personas distintas con el mismo nombre; otros, la
misma persona con una ficha con RUT y otra sin él.

**Propuesta:** una pantalla «Posibles duplicados» que junte dos fichas en una:
pasa membresías, pagos, fiado y contratos a la buena y manda la otra a la
papelera.

### 4. Respaldos automáticos en el servidor

Hoy los respaldos son a mano. **Propuesta:** `pg_dump` diario en el cron del
servidor, guardando los últimos 14.

### 5. La web con dominio propio

Mientras `APP_URL` sea `localhost`, los enlaces de los correos —firmar el
contrato, recuperar la clave— solo abren en el computador del gimnasio. Con
dominio y hosting se arregla eso, se puede conectar Google Search Console (el
espacio ya está en Configuración → Google y redes) y el correo podría salir como
`contacto@progym.cl`.

---

## Integraciones posibles

| Integración | Qué juntaría |
|---|---|
| **Cotización → horas del mes** | La cotización aceptada ya tiene las clases del mes: podría dejarlas anotadas de una vez, y al cerrar el mes mostrar lo cotizado contra lo cobrado |
| **Venta del mesón con Estoicos Suplementos** | El espacio está reservado en el Resumen. Venta al contado de barritas y bebidas con el catálogo de la tienda, no solo fiado. En pausa mientras se termina la tienda |

---

## Limpieza técnica

- **Comandos del sistema viejo** que probablemente ya no sirven: `test:email`,
  `test:email-visual`, `test:enviar-plantillas`, `test:notificacion-bienvenida`,
  `test:notificacion-tutor`, `test:plantillas-automaticas`,
  `simular:notificaciones`, `verificar:notificaciones`, `limpiar:clientes-test`.
  Revisarlos y borrar los que no se usen.
- **`Admin\InscripcionController`**: lo que queda del panel viejo (pausar,
  cambiar de plan, traspasar). Pasarlo a `Panel\` con el resto.
- **Imágenes sin uso** en `public/images/`: `estoicos_gym_logo.png` (256 KB),
  `estoicos_splementos_logo.png`, y `progym_logo.svg` (328 KB), que solo usa
  `test:email`.
- **Talleres, lista:** consulta la base una vez por taller para saber si su mes
  está cerrado. Con uno o dos talleres no se nota.
