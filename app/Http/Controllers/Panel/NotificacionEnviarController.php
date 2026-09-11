<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Services\EnvioManualService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Escribirle a un socio a mano.
 *
 * Va aparte de Panel\NotificacionController —que solo lista lo enviado— porque
 * no comparten nada: uno pagina y este compone y manda.
 */
class NotificacionEnviarController extends Controller
{
    use ValidatesFormToken;

    public function create(Request $request, EnvioManualService $envio)
    {
        return Inertia::render('Notificaciones/Enviar', [
            /*
             * NO va la lista de socios: se busca. Y solo aparece quien tiene
             * correo, porque a los demas no se les puede escribir por aqui.
             */
            'preseleccionado' => $this->preseleccionado($request->query('cliente'), $envio),
            'plantillas' => TipoNotificacion::where('activo', true)
                // Las del contrato llevan un enlace que solo se crea al mandarlo
                // desde la ficha: elegidas aquí saldrían sin dónde firmar.
                ->whereNotIn('codigo', \App\Services\ContratoDigitalService::PLANTILLAS)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion'])
                ->map(fn (TipoNotificacion $t) => [
                    'id' => $t->id,
                    'nombre' => $t->nombre,
                    'descripcion' => $t->descripcion,
                ]),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    public function buscar(Request $request, EnvioManualService $envio)
    {
        $texto = trim((string) $request->query('q', ''));

        // Con una letra saldria medio padron y no serviria para elegir.
        if (mb_strlen($texto) < 2) {
            return response()->json(['clientes' => []]);
        }

        return response()->json(['clientes' => $envio->buscar($texto)]);
    }

    /**
     * El correo ya compuesto, para leerlo antes de mandarlo.
     *
     * Se mira SIEMPRE antes de enviar: un correo sale una sola vez y no se
     * puede recoger.
     */
    public function vistaPrevia(Request $request, EnvioManualService $envio)
    {
        $datos = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plantilla_id' => 'required|exists:tipo_notificaciones,id',
            'nota' => 'nullable|string|max:1000',
        ]);

        $correo = $envio->componer(
            Cliente::findOrFail($datos['cliente_id']),
            TipoNotificacion::findOrFail($datos['plantilla_id']),
            $datos['nota'] ?? null
        );

        return response()->json($correo);
    }

    public function store(Request $request, EnvioManualService $envio)
    {
        $datos = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plantilla_id' => 'required|exists:tipo_notificaciones,id',
            'nota' => 'nullable|string|max:1000',
        ], [
            'cliente_id.required' => 'Elige a quién se le escribe.',
            'plantilla_id.required' => 'Elige qué se le manda.',
        ]);

        $cliente = Cliente::findOrFail($datos['cliente_id']);

        // El turno se reserva DESPUES de validar: un formulario rechazado lo
        // dejaria pillado y al corregirlo no se podria reenviar. Y aqui importa
        // mas que en otros sitios, porque un correo repetido lo recibe el socio.
        if (! $this->validateFormToken($request, 'notificacion_manual')) {
            return back()->with('error', 'Este correo ya se envió. Búscalo en el historial antes de repetirlo.');
        }

        $notificacion = $envio->enviar(
            $cliente,
            TipoNotificacion::findOrFail($datos['plantilla_id']),
            $datos['nota'] ?? null
        );

        return redirect()
            ->route('panel.notificaciones.show', $notificacion->uuid)
            ->with('success', "Correo enviado a {$notificacion->email_destino}.");
    }

    /**
     * Vuelve a intentar uno que no salió.
     *
     * SOLO ESE. El del panel viejo llamaba a «enviar todas las pendientes», así
     * que reintentar un correo fallido disparaba de golpe todos los demás que
     * hubiera en cola.
     */
    public function reenviar(Notificacion $notificacion, EnvioManualService $envio)
    {
        if ((int) $notificacion->id_estado === EstadosCodigo::NOTIFICACION_ENVIADA) {
            return back()->with('error', 'Ese correo ya se envió.');
        }

        $envio->reenviar($notificacion);

        return back()->with('success', "Correo enviado a {$notificacion->email_destino}.");
    }

    /** Uno que todavía no ha salido se puede parar. */
    public function cancelar(Notificacion $notificacion)
    {
        if ((int) $notificacion->id_estado !== EstadosCodigo::NOTIFICACION_PENDIENTE) {
            return back()->with('error', 'Solo se puede cancelar un correo que todavía no ha salido.');
        }

        $notificacion->cancelar('Cancelada a mano desde el panel');

        return back()->with('success', 'El correo no se enviará.');
    }

    /** @return array<string,mixed>|null */
    private function preseleccionado(?string $uuid, EnvioManualService $envio): ?array
    {
        if (! $uuid) {
            return null;
        }

        $cliente = Cliente::where('uuid', $uuid)->first();

        // Sin correo no se puede: mejor abrir vacio y que se busque a otro,
        // que abrir con alguien a quien el formulario va a rechazar al final.
        if (! $cliente || empty($cliente->email)) {
            return null;
        }

        return $envio->buscar($cliente->email, 1)->first();
    }
}
