<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Services\RegistroInscripcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Renovación de una membresía que termina.
 *
 * Es un alta encadenada con la anterior: mismo socio, se elige plan otra vez y
 * se cobra otra vez. Los cálculos son los mismos, así que sale del mismo
 * servicio; lo único propio es de dónde vienen los valores sugeridos.
 */
class InscripcionRenovarController extends Controller
{
    /**
     * Con más de un mes por delante no hay nada que renovar.
     *
     * Renovar antes de tiempo corta la membresía en curso: el socio pierde los
     * días que le quedaban.
     */
    private const DIAS_PARA_PODER_RENOVAR = 30;

    use ValidatesFormToken;

    public function create(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'membresia', 'convenio']);

        if ($aviso = $this->porQueNoSePuede($inscripcion)) {
            return redirect()
                ->route('panel.inscripciones.show', $inscripcion->uuid)
                ->with('error', $aviso);
        }

        $socio = $inscripcion->cliente;

        return Inertia::render('Inscripciones/Renovar', [
            'inscripcion' => [
                'uuid' => $inscripcion->uuid,
                'socio' => trim("{$socio->nombres} {$socio->apellido_paterno} {$socio->apellido_materno}"),
                'rut' => $socio->run_pasaporte,
                'plan' => $inscripcion->membresia?->nombre,
                'id_membresia' => $inscripcion->id_membresia,
                'id_convenio' => $inscripcion->id_convenio,
                'convenio' => $inscripcion->convenio?->nombre,
                'precio_anterior' => (int) $inscripcion->precio_final,
                'vence' => $inscripcion->fecha_vencimiento?->format('d/m/Y'),
                'dias' => $this->diasQueQuedan($inscripcion),
                /*
                 * La nueva empieza el dia DESPUES de que venza la anterior, no
                 * hoy: si empezara hoy se solaparian y el socio pagaria dos
                 * veces los dias que le quedaban.
                 */
                'empieza_sugerido' => $this->cuandoEmpieza($inscripcion),
            ],
            'membresias' => $this->planesCobrables(),
            'convenios' => Convenio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'motivos' => MotivoDescuento::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'metodosPago' => MetodoPago::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'requiere_comprobante']),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, Inscripcion $inscripcion, RegistroInscripcionService $registro)
    {
        if ($aviso = $this->porQueNoSePuede($inscripcion)) {
            return back()->with('error', $aviso);
        }

        // Validar PRIMERO: reservando el turno antes, un formulario rechazado lo
        // dejaria pillado y al corregirlo no se podria reenviar.
        $resultado = $registro->validarRenovacion($request, $inscripcion);

        if (! $this->validateFormToken($request, 'inscripcion_renovar')) {
            return back()->with('error', 'Esta renovación ya se registró. Búscala en el listado antes de repetirla.');
        }

        try {
            $nueva = $registro->registrar($resultado);
        } catch (\Throwable $e) {
            Log::error('Error al renovar desde el panel: ' . $e->getMessage());
            $this->releaseFormToken($request, 'inscripcion_renovar');

            return back()->withInput()->with('error', 'No se pudo renovar. Inténtalo nuevamente.');
        }

        return redirect()->route('panel.inscripciones.show', $nueva->uuid)->with(
            'success',
            'Membresía renovada hasta el ' . $nueva->fecha_vencimiento->format('d/m/Y') . '.'
        );
    }

    /** El motivo por el que esta membresía no se puede renovar, o null. */
    private function porQueNoSePuede(Inscripcion $inscripcion): ?string
    {
        if (in_array($inscripcion->id_estado, EstadosCodigo::INSCRIPCION_FINALIZADOS, true)) {
            return 'Esta membresía ya está cerrada. Crea una inscripción nueva.';
        }

        // Ya renovada: si no se comprobara, un segundo envio del formulario
        // crearia una tercera membresia encadenada a una que ya no esta vigente.
        if (Inscripcion::where('id_inscripcion_anterior', $inscripcion->id)->exists()) {
            return 'Esta membresía ya se renovó.';
        }

        $dias = $this->diasQueQuedan($inscripcion);

        if ($inscripcion->id_estado === EstadosCodigo::INSCRIPCION_ACTIVA && $dias > self::DIAS_PARA_PODER_RENOVAR) {
            return "Todavía le quedan {$dias} días. Se puede renovar cuando falten " . self::DIAS_PARA_PODER_RENOVAR . ' o menos.';
        }

        return null;
    }

    private function diasQueQuedan(Inscripcion $inscripcion): int
    {
        if (! $inscripcion->fecha_vencimiento) {
            return 0;
        }

        // En dias enteros y desde el principio del dia: comparar con la hora
        // actual haria que una membresia que vence hoy dijera «-1 dias».
        return (int) now()->startOfDay()->diffInDays($inscripcion->fecha_vencimiento->startOfDay(), false);
    }

    /** El día en que arranca la nueva. */
    private function cuandoEmpieza(Inscripcion $inscripcion): string
    {
        $vence = $inscripcion->fecha_vencimiento;

        // Si ya vencio, empieza hoy: retomar desde una fecha pasada regalaria
        // los dias que estuvo sin membresia.
        if (! $vence || $vence->isPast()) {
            return now()->format('Y-m-d');
        }

        return $vence->copy()->addDay()->format('Y-m-d');
    }

    /**
     * Los planes vendibles hoy, con su precio ya resuelto.
     *
     * Se dejan fuera los que no tienen precio vigente: el servicio los rechaza,
     * así que ofrecerlos solo sirve para que el formulario falle al final.
     *
     * @return list<array<string,mixed>>
     */
    private function planesCobrables(): array
    {
        return Membresia::with(['precios' => fn ($q) => $q
            ->where('activo', true)
            ->where('fecha_vigencia_desde', '<=', now())
            ->orderByDesc('fecha_vigencia_desde')])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Membresia $m) {
                $precio = $m->precios->first();

                if (! $precio) {
                    return null;
                }

                return [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'duracion' => $m->duracion_dias > 0
                        ? ($m->duracion_dias === 1 ? '1 día' : "{$m->duracion_dias} días")
                        : (($m->duracion_meses ?? 1) === 1 ? '1 mes' : ($m->duracion_meses . ' meses')),
                    'precio' => (int) round($precio->precio_normal),
                    'precio_convenio' => $precio->precio_convenio
                        ? (int) round($precio->precio_convenio)
                        : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
