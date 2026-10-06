<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Roles y sus permisos.
 *
 * Los nombres son «modulo.accion». Ademas de un permiso suelto valen el
 * comodin total «*» y el de modulo «clientes.*»; los resuelve User::puede().
 *
 * QUE PUEDE LA RECEPCION Y QUE NO. Puede todo el trabajo de meson: dar de alta
 * socios, inscribirlos, cobrarles y gestionar la membresia del dia a dia
 * (pausar, reanudar, renovar). No puede BORRAR nada, no toca la configuracion
 * del gimnasio —planes, precios, convenios, formas de pago— y no ve los
 * informes de ingresos. La regla que separa las dos listas es simple: lo que se
 * hace con el socio delante es de recepcion; lo que decide cuanto cobra el
 * gimnasio es del dueño.
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Control total del sistema',
                'permisos' => json_encode(['*']),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Recepcionista',
                'descripcion' => 'Atención de socios: altas, inscripciones y cobros',
                'permisos' => json_encode(self::PERMISOS_RECEPCION),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /** Publico para que la migracion que actualiza los roles existentes no lo repita. */
    public const PERMISOS_RECEPCION = [
        'clientes.ver',
        'clientes.crear',
        'clientes.editar',

        'inscripciones.ver',
        'inscripciones.crear',
        'inscripciones.editar',
        // Pausar, reanudar, renovar, cambiar de plan y traspasar: es lo que se
        // resuelve con el socio en el mostrador.
        'inscripciones.gestionar',

        'pagos.ver',
        'pagos.crear',
        // El monto o el medio mal puesto, solo en lo que cobró esa persona
        // hoy. Anular no: eso saca plata de la caja y queda para el dueño.
        'pagos.corregir_hoy',

        // Lo que entró hoy y con qué medio, para cuadrar el cajón al cerrar
        // el turno. El mes, las deudas y los informes siguen siendo del dueño.
        'caja.hoy',

        'historial.ver',

        'notificaciones.ver',
        // Escribirle a un socio: es atención de mesón, como llamarlo.
        'notificaciones.crear',
        'notificaciones.enviar',
    ];
}
