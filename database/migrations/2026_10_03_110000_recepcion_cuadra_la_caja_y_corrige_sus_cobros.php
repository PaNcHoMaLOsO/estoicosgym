<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lo nuevo que puede hacer la recepción que ya existe.
 *
 *  · Ver la caja del día (`caja.hoy`): para cuadrar el cajón al cerrar el
 *    turno sin ver cuánto factura el gimnasio en el mes.
 *  · Corregir sus cobros de hoy (`pagos.corregir_hoy`): el monto o el medio
 *    mal puesto, solo en lo que registró esa persona hoy.
 *  · Escribirle un correo a un socio (`notificaciones.crear`).
 *
 * El seeder solo vale para una base nueva; esta es para la que ya tiene sus
 * roles. Se busca el rol POR NOMBRE —el id 2 es el de la siembra, pero quien
 * haya recreado los roles a mano puede tener otro— y SOLO SE AGREGA: si el
 * dueño ya le quitó o le dio algo desde Perfiles, eso se respeta. Correrla
 * dos veces deja lo mismo.
 */
return new class extends Migration
{
    private const NUEVOS = [
        'notificaciones.crear',
        'caja.hoy',
        'pagos.corregir_hoy',
    ];

    public function up(): void
    {
        $rol = DB::table('roles')->whereRaw('LOWER(nombre) = ?', ['recepcionista'])->first();

        if (! $rol) {
            return;
        }

        $actuales = json_decode((string) $rol->permisos, true);
        $actuales = is_array($actuales) ? $actuales : [];

        // Un rol que ya lo puede todo no necesita nada más.
        if (in_array('*', $actuales, true)) {
            return;
        }

        $faltan = array_values(array_diff(self::NUEVOS, $actuales));

        if ($faltan === []) {
            return;
        }

        DB::table('roles')->where('id', $rol->id)->update([
            'permisos' => json_encode(array_values(array_merge($actuales, $faltan))),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $rol = DB::table('roles')->whereRaw('LOWER(nombre) = ?', ['recepcionista'])->first();

        if (! $rol) {
            return;
        }

        $actuales = json_decode((string) $rol->permisos, true) ?: [];

        DB::table('roles')->where('id', $rol->id)->update([
            'permisos' => json_encode(array_values(array_diff($actuales, self::NUEVOS))),
            'updated_at' => now(),
        ]);
    }
};
