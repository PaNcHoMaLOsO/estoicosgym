<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Http\Controllers\Traits\VuelveAlSocio;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Services\RegistroInscripcionService;
use App\Support\Ajustes;
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
    use ValidatesFormToken;
    use VuelveAlSocio;

    /**
     * Con demasiado por delante no hay nada que renovar.
     *
     * Renovar antes de tiempo corta la membresía en curso: el socio pierde los
     * días que le quedaban. Cuántos son sale de Configuración: cada gimnasio lo
     * lleva distinto y antes estaba escrito aquí, donde nadie podía cambiarlo.
     */
    private function diasParaPoderRenovar(): int
    {
        return Ajustes::numero('reglas.dias_para_renovar');
    }

    public function create(Request $request, Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'membresia', 'convenio']);

        if ($aviso = $this->porQueNoSePuede($inscripcion)) {
            return redirect()
                ->route('panel.inscripciones.show', $inscripcion->uuid)
                ->with('error', $aviso);
        }

        $socio = $inscripcion->cliente;

        return Inertia::render('Inscripciones/Renovar', [
            // Se renueva desde la ficha del socio, en una ventana: al guardar
            // se vuelve a esa ficha.
            'volverA' => $request->texto('volver', ''),
            'inscripcion' => [
                'uuid' => $inscripcion->uuid,
                'socio' => trim("{$socio->nombres} {$socio->apellido_paterno} {$socio->apellido_materno}"),
                'rut' => $socio->run_pasaporte,
                // Sin celular, la pantalla lo pide: es cuando está delante.
                'celular' => $socio->celular,
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
            'convenios' => Convenio::where('activo', true)
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'tipo']),
            'preciosDeConvenio' => \App\Support\PrecioAcordado::porConvenio(),
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
        // El celular, si no tenía y se lo pidieron ahora.
        $celular = \App\Support\CelularDelSocio::validar($request);

        if (! $this->validateFormToken($request, 'inscripcion_renovar')) {
            return back()->with('error', 'Esta renovación ya se registró. Búscala en el listado antes de repetirla.');
        }

        try {
            $nueva = $registro->registrar($resultado);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ya la renovó otra pestaña: se dice eso, no «inténtalo
            // nuevamente», que invitaría a renovarla otra vez.
            $this->releaseFormToken($request, 'inscripcion_renovar');

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al renovar desde el panel: ' . $e->getMessage());
            $this->releaseFormToken($request, 'inscripcion_renovar');

            return back()->withInput()->with('error', 'No se pudo renovar. Inténtalo nuevamente.');
        }

        \App\Support\CelularDelSocio::anotar($inscripcion->cliente, $celular);

        return redirect()->to($this->volverA($request, 'panel.inscripciones.show', $nueva->uuid))->with(
            'success',
            'Membresía renovada hasta el ' . $nueva->fecha_vencimiento->format('d/m/Y') . '.'
        );
    }

    /**
     * El motivo por el que esta membresía no se puede renovar, o null.
     *
     * Público porque la ficha lo pregunta antes de ofrecer «Renovar»: el botón
     * que se ve tiene que ser el que aquí se acepta.
     */
    public function porQueNoSePuede(Inscripcion $inscripcion): ?string
    {
        if (in_array($inscripcion->id_estado, EstadosCodigo::INSCRIPCION_FINALIZADOS, true)) {
            return 'Esta membresía ya está cerrada. Crea una inscripción nueva.';
        }

        // El socio en la papelera: su membresía vencida pasaba todas las reglas,
        // la ficha ofrecía «Renovar» y la pantalla reventaba buscando el nombre
        // de un socio que la relación ya no devuelve.
        $socio = $inscripcion->cliente;

        if (! $socio || $socio->trashed()) {
            return 'El socio está en la papelera. Restáuralo antes de renovar.';
        }

        // Ya renovada: si no se comprobara, un segundo envio del formulario
        // crearia una tercera membresia encadenada a una que ya no esta vigente.
        if (Inscripcion::where('id_inscripcion_anterior', $inscripcion->id)->exists()) {
            return 'Esta membresía ya se renovó.';
        }

        // En pausa, primero se reanuda. Renovarla la cerraba como Vencida pero
        // la ficha seguía ofreciendo «Reanudar», y al pulsarlo revivía con sus
        // días guardados al lado de la nueva: dos membresías vigentes.
        //
        // Por el estado y no por la marca `pausada`: las renovadas antes de este
        // arreglo quedaron Vencidas con la marca puesta, y esas sí se tienen
        // que poder renovar (al cerrarlas se limpia la marca).
        if ((int) $inscripcion->id_estado === EstadosCodigo::INSCRIPCION_PAUSADA) {
            return 'Esta membresía está pausada. Reanúdala antes de renovar.';
        }

        // Una vieja, cuando el socio ya tiene OTRA vigente. Se podía renovar la
        // de marzo teniendo la de septiembre activa: quedaban dos activas y se
        // le cobraba dos veces el mismo mes. Lo que toca es renovar la vigente.
        $otraVigente = Inscripcion::where('id_cliente', $inscripcion->id_cliente)
            ->whereKeyNot($inscripcion->getKey())
            ->whereIn('id_estado', [EstadosCodigo::INSCRIPCION_ACTIVA, EstadosCodigo::INSCRIPCION_PAUSADA])
            ->with('membresia:id,nombre')
            ->first();

        if ($otraVigente) {
            $cual = $otraVigente->membresia?->nombre ?? 'otra membresía';
            $estado = (int) $otraVigente->id_estado === EstadosCodigo::INSCRIPCION_PAUSADA ? 'pausada' : 'vigente';

            return "Ya tiene {$cual} {$estado}: renueva esa.";
        }

        $dias = $this->diasQueQuedan($inscripcion);

        $margen = $this->diasParaPoderRenovar();

        if ($inscripcion->id_estado === EstadosCodigo::INSCRIPCION_ACTIVA && $dias > $margen) {
            return "Todavía le quedan {$dias} días. Se puede renovar cuando falten {$margen} o menos.";
        }

        return null;
    }

    private function diasQueQuedan(Inscripcion $inscripcion): int
    {
        if (! $inscripcion->fecha_vencimiento) {
            return 0;
        }

        // En dias de calendario, como el resto del sistema (Inscripcion::diasEntre).
        // diffInDays cuenta horas: con el cambio de hora de por medio un dia
        // dura 23 y salia uno menos. Ademas, startOfDay() sobre la fecha del
        // modelo la cambiaba en el propio modelo.
        return Inscripcion::diasEntre(today(), $inscripcion->fecha_vencimiento);
    }

    /** El día en que arranca la nueva. */
    private function cuandoEmpieza(Inscripcion $inscripcion): string
    {
        $vence = $inscripcion->fecha_vencimiento;

        // Si ya vencio, empieza hoy: retomar desde una fecha pasada regalaria
        // los dias que estuvo sin membresia.
        //
        // SE COMPARA DE DIA A DIA, NO POR INSTANTE. `fecha_vencimiento` se guarda
        // a medianoche, asi que `isPast()` daba «pasada» durante todo el dia del
        // vencimiento y proponia empezar hoy, encima del ultimo dia que el socio
        // ya tenia pagado: justo el solape que este metodo existe para evitar.
        // Para el resto del sistema —la tarea nocturna, los avisos y
        // `esta_vencida`— la que vence hoy sigue vigente hoy.
        if (! $vence || $vence->copy()->startOfDay()->isBefore(today())) {
            return today()->format('Y-m-d');
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
            // Del más corto al más largo, como se ofrecen en el mostrador.
            ->orderByRaw('duracion_meses * 30 + duracion_dias')
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
