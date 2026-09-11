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
        'preview', 'plantillas', 'editar', 'traspaso.show',
        // Ver e imprimir el contrato de un socio: solo se mira.
        'contrato.ver',
    ];

    /** Dar de alta. */
    private const CREAR = ['create', 'store', 'crear', 'create-simple'];

    /** Modificar lo que ya existe, incluido activar y desactivar. */
    private const EDITAR = [
        'edit', 'update', 'activate', 'deactivate', 'reactivate',
        'restore', 'restaurar', 'actualizar', 'programar', 'guardar-programada',
        // Activar y desactivar un catalogo, y tachar y destachar una nota.
        'alternar',
        // Cobrar lo fiado: se marca como pagado, no se crea nada.
        'saldar',
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
    ];

    /** Borrar. Se separa del resto a proposito: no se deshace. */
    private const ELIMINAR = [
        'destroy', 'force-delete',
        // Los datos personales de un socio (Ley 21.719): tampoco se deshace.
        'borrar-datos',
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
        // La pagina web: lo que ven los clientes lo cambia quien configura.
        'web' => 'configuracion',
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
        // El bloc de notas del meson va con el trabajo de meson: quien
        // atiende apunta lo que hay que hacer hoy.
        'notas' => 'clientes',
        // Lo fiado tambien: lo apunta y lo cobra quien esta en el meson. NO va
        // con `pagos` a proposito —una bebida de $1.500 no es el dinero de las
        // membresias— ni con `reportes`, que es lo que recepcion no ve.
        'fiados' => 'clientes',
        // El contrato firmado por correo es del socio: lo manda y lo mira el mesón.
        'contratos' => 'clientes',
        // Lo que se le hace firmar a todos y se publica en la web lo cambia
        // quien configura.
        'textos-legales' => 'configuracion',
    ];

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

        // El historial y los informes solo se consultan.
        if (in_array($permisoBase, ['historial', 'reportes'], true)) {
            return "{$permisoBase}.ver";
        }

        if ($modulo === 'inscripciones' && in_array($accion, self::GESTION_INSCRIPCION, true)) {
            return 'inscripciones.gestionar';
        }

        if ($modulo === 'notificaciones' && in_array($accion, self::ENVIO_NOTIFICACION, true)) {
            return 'notificaciones.enviar';
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
