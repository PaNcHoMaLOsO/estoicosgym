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
        'preview', 'plantillas', 'editar', 'traspaso.show',
    ];

    /** Dar de alta. */
    private const CREAR = ['create', 'store', 'crear', 'create-simple'];

    /** Modificar lo que ya existe, incluido activar y desactivar. */
    private const EDITAR = [
        'edit', 'update', 'activate', 'deactivate', 'reactivate',
        'restore', 'actualizar', 'programar', 'guardar-programada',
        // Activar y desactivar un catalogo desde su listado.
        'alternar',
    ];

    /** Borrar. Se separa del resto a proposito: no se deshace. */
    private const ELIMINAR = ['destroy', 'force-delete'];

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
        // Activar y desactivar, que valen para los cuatro a la vez.
        'catalogos' => 'configuracion',
        'resumen' => 'clientes',
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
