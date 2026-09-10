<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Notificacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Las tres fichas que quedaban: plan, convenio y notificación.
 *
 * Van juntas por lo mismo que los catálogos: las tres responden «qué es esto y
 * a quién afecta», sin formularios ni paginación. Separarlas daría tres ficheros
 * de treinta líneas repitiendo la misma forma; cuando alguna gane lógica propia,
 * se saca.
 */
class FichasConfiguracionController extends Controller
{
    private const ACTIVA = 100;

    /** Un plan: cuánto cuesta, cuánta gente lo tiene y qué recaudó. */
    public function membresia(Membresia $membresia)
    {
        $hoy = Carbon::today();

        $precioVigente = $membresia->precios()->where('activo', true)->first();

        $inscripciones = Inscripcion::query()
            ->where('id_membresia', $membresia->id)
            ->with('cliente:id,uuid,nombres,apellido_paterno,apellido_materno')
            ->withSum('pagos as abonado', 'monto_abonado')
            ->orderByDesc('fecha_vencimiento')
            ->limit(25)
            ->get();

        return Inertia::render('Configuracion/FichaMembresia', [
            'membresia' => [
                'uuid' => $membresia->uuid,
                'nombre' => $membresia->nombre,
                'descripcion' => $membresia->descripcion,
                'duracion_dias' => (int) $membresia->duracion_dias,
                'duracion_meses' => (int) $membresia->duracion_meses,
                'max_pausas' => (int) $membresia->max_pausas,
                'activo' => (bool) $membresia->activo,
                'precio' => (int) ($precioVigente->precio_normal ?? 0),
                // null y no 0: «sin precio de convenio» y «gratis con
                // convenio» no son lo mismo, y el formulario de editar
                // necesita distinguirlos.
                'precio_convenio' => $precioVigente?->precio_convenio !== null
                    ? (int) $precioVigente->precio_convenio
                    : null,
            ],

            'cifras' => [
                'vigentes' => Inscripcion::where('id_membresia', $membresia->id)
                    ->where('id_estado', self::ACTIVA)
                    ->count(),
                'historicas' => Inscripcion::where('id_membresia', $membresia->id)->count(),
                // Lo que este plan ha traido de verdad a la caja.
                'recaudado' => (int) DB::table('pagos')
                    ->join('inscripciones', 'pagos.id_inscripcion', '=', 'inscripciones.id')
                    ->where('inscripciones.id_membresia', $membresia->id)
                    ->whereIn('pagos.id_estado', \App\Models\Pago::ESTADOS_CON_INGRESO)
                    ->sum('pagos.monto_abonado'),
            ],

            /*
             * El historial de precios importa: si un plan subio de $40.000 a
             * $45.000, las inscripciones viejas siguen cobrando lo que costaba
             * el dia que se firmaron, y sin esta tabla ese desajuste parece un
             * error de calculo.
             */
            'precios' => DB::table('historial_precios')
                ->join('precios_membresias', 'historial_precios.id_precio_membresia', '=', 'precios_membresias.id')
                ->where('precios_membresias.id_membresia', $membresia->id)
                ->orderByDesc('historial_precios.created_at')
                ->limit(10)
                ->get(['historial_precios.precio_anterior', 'historial_precios.precio_nuevo',
                    'historial_precios.razon_cambio', 'historial_precios.created_at'])
                ->map(fn ($p) => [
                    'antes' => (int) $p->precio_anterior,
                    'despues' => (int) $p->precio_nuevo,
                    'razon' => $p->razon_cambio,
                    'cuando' => $p->created_at ? Carbon::parse($p->created_at)->format('d/m/Y') : null,
                ]),

            'inscripciones' => $inscripciones->map(function (Inscripcion $i) use ($hoy) {
                $cliente = $i->cliente;
                $total = (int) ($i->precio_final ?? $i->precio_base);

                return [
                    'uuid' => $i->uuid,
                    'socio_uuid' => $cliente?->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    'id_estado' => $i->id_estado,
                    'vence' => $i->fecha_vencimiento?->format('d/m/Y'),
                    'dias' => $i->fecha_vencimiento
                        ? (int) $hoy->diffInDays($i->fecha_vencimiento, false)
                        : null,
                    'pendiente' => max(0, $total - (int) ($i->abonado ?? 0)),
                ];
            }),
        ]);
    }

    /** Un convenio: qué descuenta y a quién alcanza. */
    public function convenio(Convenio $convenio)
    {
        $socios = Cliente::query()
            ->where('id_convenio', $convenio->id)
            ->orderBy('apellido_paterno')
            ->limit(50)
            ->get(['id', 'uuid', 'nombres', 'apellido_paterno', 'apellido_materno', 'run_pasaporte', 'activo']);

        return Inertia::render('Configuracion/FichaConvenio', [
            'convenio' => [
                'uuid' => $convenio->uuid,
                'nombre' => $convenio->nombre,
                'tipo' => $convenio->tipo,
                'descripcion' => $convenio->descripcion,
                'activo' => (bool) $convenio->activo,
                // Un convenio descuenta por porcentaje O por monto, nunca por
                // los dos: se resuelve aqui para que la pantalla no decida.
                'descuento' => $convenio->descuento_porcentaje > 0
                    ? rtrim(rtrim(number_format((float) $convenio->descuento_porcentaje, 1, ',', '.'), '0'), ',') . ' %'
                    : ($convenio->descuento_monto > 0
                        ? '$' . number_format((float) $convenio->descuento_monto, 0, ',', '.')
                        : 'Sin descuento'),
                'contacto_nombre' => $convenio->contacto_nombre,
                'contacto_email' => $convenio->contacto_email,
                'contacto_telefono' => $convenio->contacto_telefono,
                // En bruto ademas del texto de arriba: «15 %» se lee bien
                // pero no se puede meter en el formulario de editar.
                'descuento_porcentaje' => (float) $convenio->descuento_porcentaje,
                'descuento_monto' => (int) $convenio->descuento_monto,
            ],

            'cifras' => [
                'socios' => $socios->count(),
                'activos' => $socios->where('activo', true)->count(),
                'vigentes' => Inscripcion::whereIn('id_cliente', $socios->pluck('id'))
                    ->where('id_estado', self::ACTIVA)
                    ->count(),
            ],

            'socios' => $socios->map(fn (Cliente $c) => [
                'uuid' => $c->uuid,
                'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                'rut' => $c->run_pasaporte,
                'activo' => (bool) $c->activo,
            ]),
        ]);
    }

    /** Una notificación: qué se mandó, a quién y qué pasó con ella. */
    public function notificacion(Notificacion $notificacion)
    {
        $notificacion->load([
            'cliente:id,uuid,nombres,apellido_paterno,apellido_materno,email',
            'tipoNotificacion:id,nombre,codigo',
        ]);

        $cliente = $notificacion->cliente;

        return Inertia::render('Notificaciones/Ficha', [
            'notificacion' => [
                'uuid' => $notificacion->uuid,
                'asunto' => $notificacion->asunto,
                'contenido' => $notificacion->contenido,
                'destino' => $notificacion->email_destino,
                'tipo' => $notificacion->tipoNotificacion?->nombre,
                'id_estado' => $notificacion->id_estado,
                'envio' => $notificacion->tipo_envio === 'automatica' ? 'Automática' : 'Manual',
                'programada' => $notificacion->fecha_programada?->format('d/m/Y'),
                'enviada' => $notificacion->fecha_envio?->format('d/m/Y H:i'),
                'intentos' => (int) $notificacion->intentos,
                'max_intentos' => (int) $notificacion->max_intentos,
                'error' => $notificacion->error_mensaje,
                'nota' => $notificacion->nota_personalizada,
            ],

            'socio' => $cliente ? [
                'uuid' => $cliente->uuid,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                'email' => $cliente->email,
            ] : null,

            // El rastro de lo que le fue pasando: es lo que se mira cuando un
            // socio dice que no le llego nada.
            'logs' => DB::table('log_notificaciones')
                ->where('id_notificacion', $notificacion->id)
                ->orderByDesc('created_at')
                ->get(['accion', 'detalle', 'created_at'])
                ->map(fn ($l) => [
                    'accion' => ucfirst((string) $l->accion),
                    'detalle' => $l->detalle,
                    'cuando' => $l->created_at ? Carbon::parse($l->created_at)->format('d/m/Y H:i') : null,
                ]),
        ]);
    }
}
