<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Services\ContratoDigitalService;
use App\Support\TextosLegales;
use Illuminate\Http\Request;

/**
 * La página que abre el socio desde el correo para leer y firmar su contrato.
 *
 * No pide cuenta ni clave: la llave es el enlace, que es largo, no se puede
 * adivinar y solo le llegó a esa persona. Por eso la página no sale en Google,
 * no le pasa su dirección a nadie y deja de servir al vencer o al mandarse
 * otra.
 */
class ContratoPublicoController extends Controller
{
    public function mostrar(string $token, ContratoDigitalService $contratos)
    {
        $contrato = $contratos->buscar($token);
        $motivo = $this->porQueNo($contrato);
        $gimnasio = TextosLegales::datosDelGimnasio();

        if ($motivo === 'firmado') {
            return response()->view('contrato.firmado', [
                'contrato' => $contrato,
                'gimnasio' => $gimnasio,
                'recien' => (bool) session('firmado'),
            ]);
        }

        if ($motivo !== null) {
            return response()->view('contrato.no-disponible', [
                'motivo' => $motivo,
                'contrato' => $contrato,
                'gimnasio' => $gimnasio,
            ], $motivo === 'vencido' ? 410 : 404);
        }

        return response()->view('contrato.firmar', $contratos->documento($contrato, now()) + [
            'contrato' => $contrato,
            'token' => $token,
        ]);
    }

    public function firmar(Request $request, string $token, ContratoDigitalService $contratos)
    {
        $contrato = $contratos->buscar($token);

        if ($this->porQueNo($contrato) !== null) {
            return redirect()->route('contrato.mostrar', $token);
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'rut' => ['required', 'string', 'max:20'],
            'firma' => ['required', 'string', 'max:600000'],
            'acepto_contrato' => ['accepted'],
            'acepto_terminos' => ['accepted'],
            'leido_privacidad' => ['accepted'],
            'version_contrato' => ['required', 'integer'],
            'version_terminos' => ['required', 'integer'],
            'version_privacidad' => ['required', 'integer'],
        ], [
            'nombre.required' => 'Escribe tu nombre completo.',
            'rut.required' => 'Escribe tu RUT o pasaporte.',
            'firma.required' => 'Falta tu firma: dibújala en el recuadro.',
            'acepto_contrato.accepted' => 'Marca que leíste y aceptas el contrato.',
            'acepto_terminos.accepted' => 'Marca que aceptas los términos y condiciones.',
            'leido_privacidad.accepted' => 'Marca que leíste la política de privacidad.',
        ]);

        $contratos->firmar($contrato, $token, [
            'nombre' => $datos['nombre'],
            'rut' => $datos['rut'],
            'firma' => $datos['firma'],
            'consentimiento_imagen' => $request->boolean('consentimiento_imagen'),
            'consentimiento_difusion' => $request->boolean('consentimiento_difusion'),
            'version_contrato' => (int) $datos['version_contrato'],
            'version_terminos' => (int) $datos['version_terminos'],
            'version_privacidad' => (int) $datos['version_privacidad'],
        ], $request->ip(), $request->userAgent());

        return redirect()->route('contrato.mostrar', $token)->with('firmado', true);
    }

    /** «firmado», «vencido», «anulado», o null si se puede firmar. */
    private function porQueNo(?Contrato $contrato): ?string
    {
        if (! $contrato || $contrato->datos_borrados_en || ! $contrato->cliente || $contrato->cliente->trashed()) {
            return 'anulado';
        }

        return match ($contrato->estado()) {
            'firmado' => 'firmado',
            'pendiente' => null,
            'vencido' => 'vencido',
            default => 'anulado',
        };
    }
}
