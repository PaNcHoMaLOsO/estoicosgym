<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\User;
use App\Services\BorradoDeDatosService;
use App\Services\ContratoDigitalService;
use App\Support\TextosLegales;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Ficha de un socio.
 *
 * Va aparte de Panel\ClienteController —que lista y da de alta— porque la ficha
 * no comparte nada con esos dos: no pagina ni valida, reune el historial de una
 * sola persona.
 */
class ClienteFichaController extends Controller
{
    private const ACTIVA = 100;

    public function __invoke(Cliente $cliente, ContratoDigitalService $contratos, BorradoDeDatosService $borrado)
    {
        $cliente->load([
            'convenio:id,nombre,tipo,descuento_porcentaje,descuento_monto',
        ]);

        $inscripciones = $cliente->inscripciones()
            ->with('membresia:id,nombre')
            // El saldo de cada inscripcion en UNA consulta: pedirlo dentro del
            // bucle serian tantas consultas como membresias tenga el socio.
            ->withSum('pagos as abonado', 'monto_abonado')
            ->orderByDesc('fecha_vencimiento')
            ->get();

        $hoy = Carbon::today();

        return Inertia::render('Clientes/Ficha', [
            'cliente' => [
                'uuid' => $cliente->uuid,
                // El id, solo para apuntarle algo fiado desde aquí: la libreta
                // del mesón trabaja con id, no con uuid.
                'id' => $cliente->id,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                'rut' => $cliente->run_pasaporte,
                // Para reconocer a quien esta delante sin preguntarle el RUT.
                'foto' => $cliente->urlDeFoto(),

                /*
                 * El contrato y los permisos.
                 *
                 * `version_vigente` viaja para poder decir «firmo la 1 y hoy va
                 * la 2»: sin ella la pantalla ensenaria un numero suelto que no
                 * significa nada para quien lo mira.
                 */
                'contrato' => [
                    'version' => $cliente->contrato_version,
                    'version_vigente' => (string) TextosLegales::vigente('contrato')->version,
                    'firmado_en' => $cliente->contrato_firmado_en?->format('d/m/Y'),
                    // Para el <input type="date"> de la pantalla.
                    'firmado_iso' => $cliente->contrato_firmado_en?->format('Y-m-d'),
                    'imagen' => (bool) $cliente->consentimiento_imagen,
                    'difusion' => (bool) $cliente->consentimiento_difusion,
                ],
                // El contrato por correo: cómo va el último que se mandó y a
                // quién le llegaría uno nuevo.
                'firma_digital' => $this->firmaDigital($cliente, $contratos),
                // Si se borraron sus datos (Ley 21.719): cuándo, quién y por qué.
                'datos_borrados' => $cliente->datos_borrados_en ? [
                    'el' => $cliente->datos_borrados_en->format('d/m/Y'),
                    'por' => $cliente->datos_borrados_por ? User::find($cliente->datos_borrados_por)?->name : null,
                    'motivo' => BorradoDeDatosService::MOTIVOS[$cliente->datos_borrados_motivo] ?? null,
                ] : null,
                // Si todavía no se pueden borrar, por qué: se dice antes de intentarlo.
                'borrar_bloqueado' => $cliente->datos_borrados_en ? null : $borrado->porQueNoSePuede($cliente),
                'email' => $cliente->email,
                'celular' => $cliente->celular,
                'direccion' => $cliente->direccion,
                'nacimiento' => $cliente->fecha_nacimiento?->format('d/m/Y'),
                'edad' => $cliente->fecha_nacimiento
                    ? (int) $cliente->fecha_nacimiento->diffInYears($hoy)
                    : null,
                'contacto_emergencia' => $cliente->contacto_emergencia,
                'telefono_emergencia' => $cliente->telefono_emergencia,
                'observaciones' => $cliente->observaciones,
                'convenio' => $cliente->convenio?->nombre,
                'activo' => (bool) $cliente->activo,
                'desde' => $cliente->created_at?->format('d/m/Y'),
                // El apoderado solo aparece si el socio es menor: en un adulto
                // serian cuatro filas vacias ocupando la mitad de la ficha.
                'menor' => (bool) $cliente->es_menor_edad,
                'apoderado' => $cliente->es_menor_edad ? [
                    'nombre' => $cliente->apoderado_nombre,
                    'rut' => $cliente->apoderado_rut,
                    'email' => $cliente->apoderado_email,
                    'telefono' => $cliente->apoderado_telefono,
                    'parentesco' => $cliente->apoderado_parentesco,
                    'consentimiento' => (bool) $cliente->consentimiento_apoderado,
                ] : null,
            ],

            'inscripciones' => $inscripciones->map(function (Inscripcion $i) use ($hoy) {
                $total = (int) ($i->precio_final ?? $i->precio_base);
                $abonado = (int) ($i->abonado ?? 0);

                return [
                    'uuid' => $i->uuid,
                    'membresia' => $i->membresia?->nombre,
                    'id_estado' => $i->id_estado,
                    'inicio' => $i->fecha_inicio?->format('d/m/Y'),
                    'vence' => $i->fecha_vencimiento?->format('d/m/Y'),
                    'dias' => $i->fecha_vencimiento
                        ? (int) $hoy->diffInDays($i->fecha_vencimiento, false)
                        : null,
                    'total' => $total,
                    'abonado' => $abonado,
                    'pendiente' => max(0, $total - $abonado),
                    'vigente' => (int) $i->id_estado === self::ACTIVA,
                    // Para decir en la cabecera hasta cuándo está pausada.
                    'pausada_hasta' => $i->fecha_pausa_fin?->format('d/m/Y'),
                ];
            }),

            'pagos' => $cliente->pagos()
                ->with('metodoPago:id,nombre')
                ->orderByDesc('fecha_pago')
                ->limit(10)
                ->get()
                ->map(fn (Pago $p) => [
                    'uuid' => $p->uuid,
                    'fecha' => $p->fecha_pago?->format('d/m/Y'),
                    'metodo' => $p->metodoPago?->nombre,
                    'id_estado' => $p->id_estado,
                    'abonado' => (int) $p->monto_abonado,
                    'pendiente' => (int) $p->monto_pendiente,
                ]),

            'resumen' => [
                'inscripciones' => $inscripciones->count(),
                'pagado' => (int) $cliente->pagos()->ingresos()->sum('monto_abonado'),
                // Lo que debe hoy, sumando todas sus inscripciones vivas.
                'debe' => (int) $inscripciones
                    ->reject(fn (Inscripcion $i) => in_array((int) $i->id_estado, [103, 105, 106], true))
                    ->sum(fn (Inscripcion $i) => max(
                        0,
                        (int) ($i->precio_final ?? $i->precio_base) - (int) ($i->abonado ?? 0),
                    )),
            ],

            /*
             * Lo que debe del meson, SEPARADO de lo de arriba.
             *
             * Son dos deudas distintas y se cobran distinto: la membresia por
             * Pagos, y esto en la libreta. Sumarlas daria una cifra que no se
             * puede cobrar de una vez y que no cuadra con ningun informe.
             *
             * Va en la ficha porque es donde se mira cuando la persona esta
             * delante: si viene a pagar su mensualidad y ademas debe tres
             * bebidas, hay que saberlo en ese momento y no dos semanas despues.
             */
            'fiado' => $this->loQueDebeDelMeson($cliente),
        ]);
    }

    /**
     * El contrato por correo de este socio.
     *
     * @return array<string,mixed>
     */
    private function firmaDigital(Cliente $cliente, ContratoDigitalService $contratos): array
    {
        $ultimo = $cliente->contratos()->latest('id')->first();
        $firmante = $contratos->firmante($cliente);

        return [
            'no_se_puede' => $contratos->porQueNoSePuedeEnviar($cliente),
            'destino' => $firmante['email'],
            'para_apoderado' => $firmante['tipo'] === 'apoderado',
            'ultimo' => $ultimo ? [
                'uuid' => $ultimo->uuid,
                'estado' => $ultimo->estado(),
                'enviado_a' => $ultimo->email_destino,
                'enviado_el' => ($ultimo->enviado_en ?? $ultimo->created_at)?->format('d/m/Y H:i'),
                'vence' => $ultimo->vence_en?->format('d/m/Y'),
                'firmado_el' => $ultimo->firmado_en?->format('d/m/Y H:i'),
                'firmante' => $ultimo->firmante_nombre,
                'error' => $ultimo->error_envio,
            ] : null,
        ];
    }

    /**
     * Lo que este socio debe de la libreta del mesón.
     *
     * Devuelve null cuando no debe nada: así la pantalla no tiene que decidir
     * si un cero se enseña o no, y un aviso que dice «debe $0» es peor que
     * ninguno.
     *
     * @return array<string,mixed>|null
     */
    private function loQueDebeDelMeson(Cliente $cliente): ?array
    {
        $lineas = Fiado::debiendo()
            ->where('id_cliente', $cliente->id)
            ->orderBy('created_at')
            ->get();

        if ($lineas->isEmpty()) {
            return null;
        }

        return [
            'total' => (int) $lineas->sum('monto'),
            'cuantas' => $lineas->count(),
            'desde' => $lineas->min('created_at')?->format('d/m/Y'),
            'lineas' => $lineas->map(fn (Fiado $f) => [
                'concepto' => $f->concepto,
                'monto' => $f->monto,
            ])->values()->all(),
        ];
    }
}
