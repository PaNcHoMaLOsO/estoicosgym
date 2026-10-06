<?php

namespace App\Support;

/**
 * Que permiso exige cada ruta.
 *
 * SE DEDUCE DEL NOMBRE DE LA RUTA en vez de escribirse ruta por ruta. Son
 * ochenta y tantas rutas y ponerle el middleware a mano a cada una significa
 * que la que se olvide queda abierta sin que nadie lo note: un agujero de
 * permisos no falla, simplemente deja pasar. Aqui la regla es una sola, y el
 * comando `permisos:revisar` comprueba que ninguna ruta se quede sin cubrir.
 *
 * Los nombres van como «admin.clientes.index» o «panel.pagos.store»: el modulo
 * es el trozo del medio y la accion el ultimo.
 */
class Permisos
{
    /** Consultar. */
    private const VER = [
        'index', 'show', 'trashed', 'inactive', 'logs', 'historial',
        'builder', 'generar', 'predefinido', 'campos', 'json',
        'info-cambio-plan', 'buscar-clientes-traspaso', 'buscar-cliente',
        'buscar-cliente-individual', 'contar-destinatarios', 'obtener-destinatarios',
        'buscar',
        // Buscar al socio al vender y los fiados de siempre: solo se mira.
        'buscar-socio', 'frecuentes',
        // Preguntar si alguien ya está registrado: solo se mira.
        'verificar',
        'duplicados',
        'retrato',
        'preview', 'plantillas', 'editar', 'traspaso.show',
        // Ver e imprimir el contrato de un socio: solo se mira.
        'contrato.ver',
        // Leer una cotizacion de taller y sacarla en papel: no la cambia.
        'cotizaciones.show', 'cotizaciones.imprimir',
    ];

    /** Dar de alta. */
    private const CREAR = [
        'create', 'store', 'crear', 'create-simple',
        // Anotar las clases de un taller: quien está en el mesón ve pasar las
        // clases y sabe cuál se suspendió. Si tuviera que contárselo a alguien
        // para que las escribiera, ese es el paso donde se pierden.
        'horas.store', 'horas.mes',
        // Cotizar un mes de taller: es preparar un papel para mandarlo, no
        // cobrarlo. Lo que se cobra se cierra aparte, al final del mes.
        'cotizaciones.store',
        'duplicar',
    ];

    /** Modificar lo que ya existe, incluido activar y desactivar. */
    private const EDITAR = [
        'edit', 'update', 'activate', 'deactivate', 'reactivate',
        'restore', 'restaurar', 'actualizar', 'programar', 'guardar-programada',
        // Activar y desactivar un catalogo, y tachar y destachar una nota.
        'alternar',
        // Cobrar lo fiado: se marca como pagado, no se crea nada.
        'saldar',
        'distintos',
        'resolver',
        // La foto del socio. Es editar su ficha, aunque vaya por su cuenta.
        'foto',
        // Anotar que firmo el contrato y que permisos dio. Tambien es su ficha.
        'contrato',
        // Mandarle a una cuenta del panel el enlace para poner su contraseña.
        'enlace',
        // Mandarle al socio el contrato por correo, y anular un enlace que no
        // se firmó. Es trabajo de mesón, como anotar la firma en papel.
        'contrato.enviar',
        'anular',
        // Cerrar el mes de un taller y anotarle el folio de la factura: eso ya
        // no es apuntar horas, es emitir un cobro.
        'cerrar',
        'cobros.update',
        // Corregir una cotizacion y traerle las clases del horario, y los
        // datos de facturacion de la institucion.
        'cotizaciones.update', 'cotizaciones.refrescar', 'instituciones.update',
    ];

    /** Borrar. Se separa del resto a proposito: no se deshace. */
    private const ELIMINAR = [
        'destroy', 'force-delete',
        // Quitar una clase ya anotada y reabrir un mes cerrado: las dos mueven
        // lo que se va a facturar.
        'horas.destroy', 'cobros.destroy',
        // Y tirar una cotizacion que no se mando.
        'cotizaciones.destroy',
        // Los datos personales de un socio (Ley 21.719): tampoco se deshace.
        'borrar-datos',
        // Juntar dos fichas manda una a la papelera y le mueve los pagos.
        'juntar',
    ];

    /**
     * Acciones del dia a dia sobre una membresia ya vendida. No son «editar»:
     * quien atiende el meson pausa y renueva, pero eso no le da derecho a
     * cambiarle el precio a un plan.
     */
    private const GESTION_INSCRIPCION = [
        'pausar', 'reanudar', 'renovar', 'renovar.store',
        'cambiar-plan', 'traspasar',
    ];

    /** Enviar correos a socios. */
    private const ENVIO_NOTIFICACION = [
        'enviar-cliente', 'enviar-individual', 'enviar-masivo',
        'reenviar', 'cancelar',
    ];

    /**
     * De que modulo depende cada prefijo de ruta.
     *
     * Los cuatro catalogos caen todos en `configuracion`: son las piezas que
     * definen cuanto cobra el gimnasio y se tocan una vez al mes, no algo que
     * se resuelva con el socio delante.
     */
    private const MODULOS = [
        'clientes' => 'clientes',
        'inscripciones' => 'inscripciones',
        'pagos' => 'pagos',
        'historial' => 'historial',
        'reportes' => 'reportes',
        'notificaciones' => 'notificaciones',
        'membresias' => 'configuracion',
        'convenios' => 'configuracion',
        'metodos-pago' => 'configuracion',
        'motivos-descuento' => 'configuracion',
        // Los especialistas de la web: un catalogo mas, lo toca quien
        // configura, no el meson.
        'especialistas' => 'configuracion',
        'embajadores' => 'configuracion',
        // La pagina web: lo que ven los clientes lo cambia quien configura.
        'web' => 'configuracion',
        // Las clases que salen en la web, con su precio: tambien las cambia
        // quien configura, no el meson.
        'clases' => 'configuracion',
        // Las cuentas del panel: quien entra y con que rol. Modulo PROPIO y no
        // «configuracion»: quien puede cambiar un precio no por eso puede
        // crearse otra cuenta de administrador.
        'usuarios' => 'usuarios',
        // Activar y desactivar, que valen para los cuatro a la vez.
        'catalogos' => 'configuracion',
        // La papelera cruza todos los modulos —socios, membresias, pagos,
        // catalogos— y devolver algo borrado no es tarea de meson: cae en
        // configuracion, como el resto de lo que se toca de tarde en tarde.
        'papelera' => 'configuracion',
        // La pantalla de ajustes: quien cambia el nombre del gimnasio o
        // cada cuantos dias se avisa de un vencimiento.
        'configuracion' => 'configuracion',
        'resumen' => 'clientes',
        // La caja —lo que entró, lo que se debe y cómo va el gimnasio— es
        // plata: la ve quien ve los informes, y recepción no. Por eso salió del
        // resumen, que es lo primero que abre quien atiende.
        'caja' => 'reportes',
        // El bloc de notas del meson va con el trabajo de meson: quien
        // atiende apunta lo que hay que hacer hoy.
        'notas' => 'clientes',
        // Lo fiado tambien: lo apunta y lo cobra quien esta en el meson. NO va
        // con `pagos` a proposito —una bebida de $1.500 no es el dinero de las
        // membresias— ni con `reportes`, que es lo que recepcion no ve.
        'fiados' => 'clientes',
        'canje' => 'clientes',
        // Los talleres y el arriendo de la sala: es plata del negocio, con
        // factura de por medio. Va con `pagos` —quien cobra, anota y cierra el
        // mes— y no con `reportes`, que es solo mirar.
        'talleres' => 'pagos',
        // El contrato firmado por correo es del socio: lo manda y lo mira el mesón.
        'contratos' => 'clientes',
        // Lo que se le hace firmar a todos y se publica en la web lo cambia
        // quien configura.
        'textos-legales' => 'configuracion',
        'fallas' => 'configuracion',
        'rutinas' => 'configuracion',
        'ejercicios' => 'configuracion',
    ];

    /**
     * Permisos MÁS ESTRECHOS que también dejan llegar a una ruta.
     *
     * El middleware los acepta en lugar del de siempre, pero solo abren la
     * puerta: el controlador vuelve a mirar y decide sobre QUÉ se puede
     * actuar. Corregir un pago pide `pagos.editar`, y `pagos.corregir_hoy`
     * deja entrar a la misma pantalla solo con los pagos de hoy que registró
     * esa persona (PagoEditarController::puedeCorregir).
     *
     * @var array<string,list<string>>
     */
    private const TAMBIEN = [
        'panel.pagos.edit' => ['pagos.corregir_hoy'],
        'panel.pagos.update' => ['pagos.corregir_hoy'],
        // Quien solo ve la caja del día y llega a /panel/caja —un enlace
        // guardado, el de otra pantalla— no se topa con un 403: CajaController
        // lo manda a la de hoy.
        'panel.caja' => ['caja.hoy'],
    ];

    /** @return list<string> */
    public static function tambien(?string $nombreDeRuta): array
    {
        return self::TAMBIEN[$nombreDeRuta] ?? [];
    }

    /**
     * Permiso que exige una ruta, o null si la ruta no es de un modulo
     * protegido (el panel de inicio, el cierre de sesion, la web publica).
     */
    public static function para(?string $nombreDeRuta): ?string
    {
        if (! $nombreDeRuta) {
            return null;
        }

        $partes = explode('.', $nombreDeRuta);
        $ambito = array_shift($partes);

        if (! in_array($ambito, ['admin', 'panel'], true) || $partes === []) {
            return null;
        }

        $modulo = array_shift($partes);
        $accion = implode('.', $partes);

        // «panel.resumen» no lleva accion: es la portada del panel.
        if ($accion === '') {
            return self::MODULOS[$modulo] ?? null
                ? (self::MODULOS[$modulo] . '.ver')
                : null;
        }

        $permisoBase = self::MODULOS[$modulo] ?? null;

        if (! $permisoBase) {
            return null;
        }

        /*
         * LA CAJA DEL DÍA tiene permiso propio y no cuelga de los informes: es
         * lo que necesita quien cierra el turno para cuadrar el cajón —lo que
         * entró HOY y con qué medio— sin ver cuánto factura el gimnasio en el
         * mes ni quién debe. Va antes de la regla de abajo, que mandaría
         * todo lo de la caja a `reportes.ver`.
         */
        if ($modulo === 'caja' && $accion === 'hoy') {
            return 'caja.hoy';
        }

        // El historial y los informes solo se consultan.
        if (in_array($permisoBase, ['historial', 'reportes'], true)) {
            return "{$permisoBase}.ver";
        }

        if ($modulo === 'inscripciones' && in_array($accion, self::GESTION_INSCRIPCION, true)) {
            return 'inscripciones.gestionar';
        }

        /*
         * Cancelar una membresía NO es gestión de mesón como pausar: deja al
         * socio sin el plan que pagó y no se deshace. Pide lo mismo que
         * borrarla, que recepción no tiene. Va con nombre propio porque
         * «cancelar» en el envío de correos es otra cosa (ENVIO_NOTIFICACION).
         */
        if ($modulo === 'inscripciones' && $accion === 'cancelar') {
            return 'inscripciones.eliminar';
        }

        /*
         * EL ENVÍO A GRUPOS, CON LA PANTALLA. La pantalla del envío masivo
         * pedía «editar» y recepción no la veía, pero el POST caía en «enviar»
         * y la lista de correos en «ver»: con una petición a mano se mandaba
         * un correo a todo el padrón y se bajaban todos los correos. Ahora todo
         * lo del envío masivo pide lo mismo que su pantalla, y el envío suelto
         * lo mismo que la suya.
         */
        if ($modulo === 'notificaciones' && in_array($accion, ['crear-masivo', 'enviar-masivo', 'obtener-destinatarios', 'preview-masivo'], true)) {
            return 'notificaciones.editar';
        }

        if ($modulo === 'notificaciones' && $accion === 'enviar-individual') {
            return 'notificaciones.crear';
        }

        if ($modulo === 'notificaciones' && in_array($accion, self::ENVIO_NOTIFICACION, true)) {
            return 'notificaciones.enviar';
        }

        /*
         * Lo que puede hacer cada perfil se cambia con el mismo permiso que
         * cambia las cuentas: quien reparte permisos ya podía darse otra
         * cuenta de administrador, así que pedir algo menos sería mentir.
         */
        if ($modulo === 'usuarios' && in_array($accion, ['perfiles', 'perfiles.update'], true)) {
            return 'usuarios.editar';
        }

        // Crear un taller es fijarle el precio por hora: lo mismo que
        // editarlo, no lo que hace el mesón al anotar las clases.
        if ($modulo === 'talleres' && $accion === 'store') {
            return 'pagos.editar';
        }

        // Las plantillas de correo son configuracion: cambian lo que reciben
        // TODOS los socios, no un envio suelto.
        if ($modulo === 'notificaciones' && str_starts_with($accion, 'plantillas.')) {
            return 'configuracion.editar';
        }

        return match (true) {
            in_array($accion, self::ELIMINAR, true) => "{$permisoBase}.eliminar",
            in_array($accion, self::CREAR, true) => "{$permisoBase}.crear",
            in_array($accion, self::EDITAR, true) => "{$permisoBase}.editar",
            in_array($accion, self::VER, true) => "{$permisoBase}.ver",
            // Lo que no reconoce se trata como lo mas restrictivo del modulo, y
            // `permisos:revisar` lo saca a la luz para ponerle nombre.
            default => "{$permisoBase}.editar",
        };
    }
}
