<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Http\Controllers\Traits\VuelveAlSocio;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Services\RegistroPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Cobro de una inscripción desde el panel nuevo.
 *
 * Va aparte de Panel\PagoController —que solo lista— porque el listado y el
 * cobro no comparten nada: uno pagina y el otro valida saldos.
 *
 * Las validaciones y los cálculos viven en RegistroPagoService.
 */
class PagoCrearController extends Controller
{
    use ValidatesFormToken;
    use VuelveAlSocio;

    /** Cuántos socios se devuelven por búsqueda. */
    private const RESULTADOS = 15;

    public function create(Request $request)
    {
        return Inertia::render('Pagos/Crear', [
            /*
             * NO se manda la lista entera de inscripciones.
             *
             * Antes iban todas las que tuvieran saldo —sesenta hoy, miles en un
             * gimnasio en marcha— dentro de un <select>, que con ese volumen no
             * sirve para encontrar a nadie. Ahora se busca, y solo viaja lo que
             * se escribe.
             */
            'preseleccionada' => $this->preseleccionada($request->query('inscripcion')),
            // Se cobra desde la ficha del socio, en una ventana: al guardar se
            // vuelve a esa ficha y no a la pantalla del pago.
            'volverA' => (string) $request->query('volver', ''),
            'metodosPago' => MetodoPago::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'requiere_comprobante']),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    /**
     * Busca a quién cobrarle.
     *
     * Devuelve solo inscripciones CON SALDO: cobrarle a quien no debe nada es
     * el error que esta pantalla tiene que hacer difícil, no fácil.
     */
    public function buscar(Request $request)
    {
        $texto = trim((string) $request->query('q', ''));

        // Con una letra saldría medio padrón y no serviría para elegir.
        if (mb_strlen($texto) < 2) {
            return response()->json(['inscripciones' => []]);
        }

        $encontradas = $this->conSaldo()
            ->whereHas('cliente', function ($q) use ($texto) {
                $q->where('nombres', 'like', "%{$texto}%")
                    ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                    ->orWhere('apellido_materno', 'like', "%{$texto}%")
                    ->orWhere('run_pasaporte', 'like', "%{$texto}%")
                    ->orWhere('email', 'like', "%{$texto}%");
            })
            ->orderByDesc('id')
            ->limit(self::RESULTADOS * 3)
            ->get()
            ->map(fn (Inscripcion $i) => $this->resumir($i))
            ->filter(fn (?array $i) => $i !== null && $i['pendiente'] > 0)
            ->take(self::RESULTADOS)
            ->values();

        return response()->json(['inscripciones' => $encontradas]);
    }

    public function store(Request $request, RegistroPagoService $registro)
    {
        // Validar PRIMERO: reservando el turno antes, un formulario rechazado lo
        // dejaría pillado y al corregirlo no se podría reenviar.
        $resultado = $registro->validar($request);

        if (! $this->validateFormToken($request, 'pago_create')) {
            return back()->with('error', 'Este pago ya se registró. Revísalo en el listado antes de repetirlo.');
        }

        try {
            $pago = $registro->registrar($resultado);
        } catch (ValidationException $e) {
            // El saldo cambió entre validar y escribir. Eso tiene explicación y
            // se arregla corrigiendo el monto, así que el aviso va al campo: como
            // un «no se pudo, inténtalo otra vez» se reintentaría igual de mal.
            $this->releaseFormToken($request, 'pago_create');

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al registrar pago desde el panel: ' . $e->getMessage());
            $this->releaseFormToken($request, 'pago_create');

            return back()->withInput()->with('error', 'No se pudo registrar el pago. Inténtalo nuevamente.');
        }

        $socio = $resultado['inscripcion']->cliente;
        $nombre = $socio ? trim("{$socio->nombres} {$socio->apellido_paterno}") : 'el socio';

        // Lo que quedó pendiente sale del pago ya escrito, no de lo que se leyó
        // al validar: si entremedio entró otro cobro, el saldo es otro.
        return redirect()->to($this->volverA($request, 'panel.pagos.show', $pago->uuid))->with(
            'success',
            (int) $pago->monto_pendiente <= 0
                ? "Pago registrado. La membresía de {$nombre} queda al día."
                : "Abono registrado a {$nombre}. Queda saldo pendiente."
        );
    }

    /**
     * La inscripción con la que se llega desde una ficha.
     *
     * El enlace trae el UUID, y antes se pasaba tal cual a un desplegable cuyas
     * opciones eran ids numéricos: no coincidía nunca, así que pulsar «Cobrar»
     * en una ficha dejaba el formulario vacío y había que buscar al socio a
     * mano, justo lo que el botón venía a evitar.
     */
    private function preseleccionada(?string $uuid): ?array
    {
        if (! $uuid) {
            return null;
        }

        $inscripcion = $this->conSaldo()->where('uuid', $uuid)->first();

        return $inscripcion ? $this->resumir($inscripcion) : null;
    }

    /** Inscripciones vivas de socios activos. El saldo se filtra al resumir. */
    private function conSaldo()
    {
        return Inscripcion::query()
            ->with(['cliente:id,nombres,apellido_paterno,apellido_materno,run_pasaporte', 'membresia:id,nombre'])
            // El saldo en UNA consulta: pedirlo por fila serían tantas como
            // inscripciones devuelva la búsqueda.
            ->withSum('pagos as abonado', 'monto_abonado')
            ->whereNotIn('id_estado', EstadosCodigo::INSCRIPCION_FINALIZADOS)
            ->whereHas('cliente', fn ($q) => $q->where('activo', true));
    }

    /** @return array<string,mixed>|null */
    private function resumir(Inscripcion $inscripcion): ?array
    {
        $cliente = $inscripcion->cliente;
        $total = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base);
        $abonado = (int) ($inscripcion->abonado ?? 0);

        return [
            'id' => $inscripcion->id,
            'uuid' => $inscripcion->uuid,
            'socio' => $cliente
                ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                : 'Socio eliminado',
            'rut' => $cliente?->run_pasaporte,
            'membresia' => $inscripcion->membresia?->nombre,
            'total' => $total,
            'abonado' => $abonado,
            'pendiente' => max(0, $total - $abonado),
            'vence' => $inscripcion->fecha_vencimiento?->format('d/m/Y'),
        ];
    }
}
