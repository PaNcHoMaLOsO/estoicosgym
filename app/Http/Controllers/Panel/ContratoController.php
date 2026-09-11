<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Services\ContratoDigitalService;
use Illuminate\Validation\ValidationException;

/**
 * El contrato por correo, visto desde el panel: mandarlo, anular el enlace y
 * ver el que ya se firmó.
 */
class ContratoController extends Controller
{
    public function enviar(Cliente $cliente, ContratoDigitalService $contratos)
    {
        try {
            $contrato = $contratos->enviar($cliente);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with(
            'success',
            "Contrato enviado a {$contrato->email_destino}. El enlace sirve hasta el {$contrato->vence_en->format('d/m/Y')}."
        );
    }

    public function anular(Contrato $contrato, ContratoDigitalService $contratos)
    {
        try {
            $contratos->anular($contrato);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Listo: ese enlace ya no sirve para firmar.');
    }

    /**
     * El contrato de este socio con sus datos y los de su plan de hoy, para
     * leerlo o imprimirlo y firmarlo en el mesón.
     *
     * Es el mismo texto que se firma por correo, rellenado ahora. No crea
     * ningún enlace ni cambia nada.
     */
    public function ver(Cliente $cliente, ContratoDigitalService $contratos)
    {
        if ($cliente->datos_borrados_en) {
            return redirect()->route('panel.clientes.show', $cliente->uuid)
                ->with('error', 'Sus datos personales se borraron: ya no hay un contrato que mostrar.');
        }

        return view('contrato.imprimir', $contratos->borrador($cliente, now()) + [
            'cliente' => $cliente,
            'fecha' => now(),
            // Si ya firmó por correo se avisa: lo que firmó es ESE documento.
            'firmado' => $cliente->contratos()
                ->whereNotNull('firmado_en')
                ->whereNull('datos_borrados_en')
                ->latest('firmado_en')
                ->first(),
        ]);
    }

    /**
     * El contrato firmado, para leerlo o imprimirlo, con la constancia de la
     * firma.
     *
     * Es una página suelta y no una pantalla del panel: se imprime, y el menú
     * del panel no tiene nada que hacer en un contrato impreso.
     */
    public function show(Contrato $contrato)
    {
        abort_unless($contrato->firmado_en !== null, 404);

        return view('contrato.copia', [
            'contrato' => $contrato->load(['cliente', 'usuario']),
            'integro' => $contrato->integro(),
        ]);
    }
}
