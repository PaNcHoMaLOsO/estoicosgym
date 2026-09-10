<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Services\RegistroPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Cobro de una inscripcion desde el panel nuevo.
 *
 * Va aparte de Panel\PagoController —que solo lista— porque el listado y el
 * cobro no comparten nada: uno pagina y el otro valida saldos.
 *
 * Las validaciones y los calculos viven en RegistroPagoService, el mismo que
 * usa el panel de Blade.
 */
class PagoCrearController extends Controller
{
    use ValidatesFormToken;

    public function create(Request $request)
    {
        return Inertia::render('Pagos/Crear', [
            'inscripciones' => $this->inscripcionesConSaldo(),
            'metodosPago' => MetodoPago::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'requiere_comprobante']),
            // Se llega aqui desde el boton «Cobrar» de una inscripcion concreta.
            'preseleccion' => $request->query('inscripcion'),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, RegistroPagoService $registro)
    {
        // Validar PRIMERO: reservando el turno antes, un formulario rechazado lo
        // dejaria pillado y al corregirlo no se podria reenviar.
        $resultado = $registro->validar($request);

        if (! $this->validateFormToken($request, 'pago_create')) {
            return back()->with('error', 'Este pago ya se registró. Revísalo en el listado antes de repetirlo.');
        }

        try {
            $pago = $registro->registrar($resultado);
        } catch (\Throwable $e) {
            Log::error('Error al registrar pago desde el panel: ' . $e->getMessage());
            $this->releaseFormToken($request, 'pago_create');

            return back()->withInput()->with('error', 'No se pudo registrar el pago. Inténtalo nuevamente.');
        }

        $socio = $resultado['inscripcion']->cliente;
        $nombre = $socio ? trim("{$socio->nombres} {$socio->apellido_paterno}") : 'el socio';

        return redirect()->route('panel.pagos.index')->with(
            'success',
            $resultado['completa']
                ? "Pago registrado. La membresía de {$nombre} queda al día."
                : "Abono registrado a {$nombre}. Queda saldo pendiente."
        );
    }

    /**
     * Inscripciones que todavia deben algo.
     *
     * El saldo se calcula con withSum, en UNA consulta. La version de Blade
     * recorre las inscripciones llamando a `$insc->pagos()->sum()` dentro de un
     * filter, o sea una consulta por fila: con doscientas inscripciones son
     * doscientas consultas para pintar un desplegable.
     */
    private function inscripcionesConSaldo()
    {
        return Inscripcion::query()
            ->with(['cliente:id,nombres,apellido_paterno,apellido_materno,run_pasaporte', 'membresia:id,nombre'])
            ->withSum('pagos as abonado', 'monto_abonado')
            ->whereNotIn('id_estado', EstadosCodigo::INSCRIPCION_FINALIZADOS)
            ->whereHas('cliente', fn ($q) => $q->where('activo', true))
            ->orderByDesc('id')
            ->get()
            ->map(function (Inscripcion $inscripcion) {
                $total = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base);
                $abonado = (int) ($inscripcion->abonado ?? 0);
                $cliente = $inscripcion->cliente;

                return [
                    'id' => $inscripcion->id,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    'rut' => $cliente?->run_pasaporte,
                    'membresia' => $inscripcion->membresia?->nombre,
                    'total' => $total,
                    'abonado' => $abonado,
                    'pendiente' => $total - $abonado,
                    'vence' => $inscripcion->fecha_vencimiento?->format('d/m/Y'),
                ];
            })
            ->filter(fn (array $i) => $i['pendiente'] > 0)
            ->values();
    }
}
