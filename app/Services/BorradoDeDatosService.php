<?php

namespace App\Services;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Fiado;
use App\Models\HistorialCambio;
use App\Models\HistorialTraspaso;
use App\Models\Inscripcion;
use App\Models\LogNotificacion;
use App\Models\Notificacion;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Borrar los datos personales de un socio sin tocar las cuentas.
 *
 * La ley de datos personales (Ley 21.719) da derecho a pedir que se borren.
 * Pero el socio pagó membresías, y esos pagos están en los informes, en la caja
 * de cada mes y en lo que se declara: borrar su fila con sus pagos descuadraría
 * todo hacia atrás. Así que la fila NO se borra: se le quita todo lo que dice
 * quién era. Queda «Socio Borrado #57» con sus membresías y pagos intactos
 * —montos, fechas, planes— y ya no hay forma de saber quién fue.
 *
 * Se va: nombre, RUT, contacto, dirección, nacimiento, contacto de emergencia,
 * apoderado, observaciones, foto, los correos que se le mandaron, lo escrito a
 * mano en sus membresías y pagos, y sus contratos firmados, de los que queda
 * solo la huella.
 */
class BorradoDeDatosService
{
    public const MOTIVOS = [
        'solicitud' => 'Lo pidió la persona',
        'plazo' => 'Ya no hacía falta guardarlos',
    ];

    /**
     * Lo que impide borrarlos todavía, o null.
     *
     * Son las mismas razones que impiden darlo de baja, más lo fiado: mientras
     * use el gimnasio o deba plata, el gimnasio necesita saber quién es.
     */
    public function porQueNoSePuede(Cliente $cliente): ?string
    {
        if ($cliente->datos_borrados_en) {
            return 'Sus datos ya se borraron el ' . $cliente->datos_borrados_en->format('d/m/Y') . '.';
        }

        $vigente = $cliente->inscripciones()
            ->whereIn('id_estado', EstadosCodigo::INSCRIPCION_REQUIERE_CLIENTE_ACTIVO)
            ->exists();

        if ($vigente) {
            return 'Tiene una membresía vigente o pausada. Cancélala primero: mientras la usa, el gimnasio necesita saber quién es.';
        }

        // La deuda se mira por membresía y no solo por pago: una vencida que
        // nunca se pagó no tiene ni una fila en pagos, y aun así se debe.
        $deuda = (int) Inscripcion::where('id_cliente', $cliente->id)
            ->whereIn('id_estado', Inscripcion::ESTADOS_CON_DEUDA)
            ->withSum('pagos as abonado', 'monto_abonado')
            ->get()
            ->sum(fn (Inscripcion $inscripcion) => $inscripcion->deuda);

        $pagoPendiente = $cliente->pagos()
            ->whereIn('id_estado', EstadosCodigo::PAGO_PENDIENTES_COBRO)
            ->exists();

        if ($deuda > 0 || $pagoPendiente) {
            return 'Tiene pagos pendientes' . ($deuda > 0 ? ' ($' . number_format($deuda, 0, ',', '.') . ')' : '')
                . '. Cóbralos o cancélalos antes: sin sus datos no habría a quién cobrarle.';
        }

        $fiado = (int) Fiado::debiendo()->where('id_cliente', $cliente->id)->sum('monto');

        if ($fiado > 0) {
            return 'Debe $' . number_format($fiado, 0, ',', '.') . ' del mesón. Cóbralo o sácalo de la libreta antes.';
        }

        return null;
    }

    /**
     * Los borra. No se deshace.
     *
     * @throws ValidationException si todavía no se puede
     */
    public function borrar(Cliente $cliente, ?int $idUsuario, string $motivo = 'solicitud'): void
    {
        if ($razon = $this->porQueNoSePuede($cliente)) {
            throw ValidationException::withMessages(['borrar' => $razon]);
        }

        $foto = $cliente->foto_perfil;

        DB::transaction(function () use ($cliente, $idUsuario, $motivo) {
            $id = $cliente->id;

            $cliente->forceFill([
                // El número va en el nombre para que dos fichas borradas no se
                // confundan en un informe. No dice nada de la persona.
                'nombres' => 'Socio',
                'apellido_paterno' => 'Borrado',
                'apellido_materno' => "#{$id}",
                'run_pasaporte' => null,
                'celular' => null,
                'email' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
                'contacto_emergencia' => null,
                'telefono_emergencia' => null,
                'observaciones' => null,
                'id_convenio' => null,
                'es_menor_edad' => false,
                'consentimiento_apoderado' => false,
                'apoderado_nombre' => null,
                'apoderado_rut' => null,
                'apoderado_email' => null,
                'apoderado_telefono' => null,
                'apoderado_parentesco' => null,
                'apoderado_observaciones' => null,
                'consentimiento_imagen' => false,
                'consentimiento_difusion' => false,
                'foto_perfil' => null,
                'activo' => false,
                // Si estaba en la papelera sale de ahí: ya no hay nadie que
                // restaurar, y sus pagos tienen que seguir apuntando a algo.
                'deleted_at' => null,
                'datos_borrados_en' => now(),
                'datos_borrados_por' => $idUsuario,
                'datos_borrados_motivo' => array_key_exists($motivo, self::MOTIVOS) ? $motivo : 'solicitud',
            ])->save();

            // Lo escrito a mano en sus membresías y pagos: ahí se anotan
            // lesiones, problemas y nombres. Los montos y las fechas quedan.
            Inscripcion::withTrashed()->where('id_cliente', $id)->update([
                'observaciones' => null,
                'razon_pausa' => null,
                'motivo_cambio_plan' => null,
                'motivo_traspaso' => null,
            ]);
            Pago::withTrashed()->where('id_cliente', $id)->update(['observaciones' => null]);
            HistorialCambio::where('cliente_id', $id)->update(['motivo' => null]);
            HistorialTraspaso::where(fn ($q) => $q->where('cliente_origen_id', $id)->orWhere('cliente_destino_id', $id))
                ->update(['motivo' => 'Datos borrados']);
            Fiado::where('id_cliente', $id)->update(['nombre' => null]);

            // Los correos que se le mandaron llevan su nombre, su correo y lo
            // que se le dijo. No son cuentas: se van enteros.
            $correos = Notificacion::where('id_cliente', $id)->pluck('id');
            LogNotificacion::whereIn('id_notificacion', $correos)->delete();
            Notificacion::whereIn('id', $correos)->delete();

            // Los contratos: el enlace pendiente deja de servir, y de los
            // firmados queda la huella —prueba de que existió un documento—
            // sin el documento, la firma ni la conexión.
            Contrato::where('id_cliente', $id)->whereNull('firmado_en')->whereNull('anulado_en')
                ->update(['anulado_en' => now()]);
            Contrato::where('id_cliente', $id)->update([
                'email_destino' => null,
                'firmante_nombre' => null,
                'firmante_rut' => null,
                'ip' => null,
                'navegador' => null,
                'contenido' => null,
                'error_envio' => null,
                'datos_borrados_en' => now(),
            ]);
        });

        // La foto, después: si la transacción fallara, la ficha seguiría
        // apuntando a un archivo que ya no está.
        if ($foto) {
            Storage::disk('public')->delete($foto);
        }
    }
}
