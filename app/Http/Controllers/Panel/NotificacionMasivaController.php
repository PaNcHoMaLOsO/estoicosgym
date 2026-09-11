<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Membresia;
use App\Services\EnvioMasivoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Un mismo aviso a un grupo de socios.
 *
 * Lo que más importa de esta pantalla es que se vea A CUÁNTA GENTE se le va a
 * escribir antes de pulsar el botón. Un correo a doscientas personas no se
 * puede recoger, y un grupo mal elegido se nota cuando ya salió.
 */
class NotificacionMasivaController extends Controller
{
    use ValidatesFormToken;

    public function create(EnvioMasivoService $masivo)
    {
        return Inertia::render('Notificaciones/Masivo', [
            'grupos' => collect(EnvioMasivoService::grupos())
                ->map(fn (array $g, string $clave) => $g + [
                    'clave' => $clave,
                    // La cuenta viaja YA HECHA: es lo primero que se mira para
                    // decidir a quién escribir, y pedirla al elegir dejaría un
                    // parpadeo justo en el dato que importa.
                    'cuantos' => $masivo->destinatarios($clave)->count(),
                ])
                ->values(),
            'membresias' => Membresia::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'variables' => EnvioMasivoService::variables(),
            'tope' => EnvioMasivoService::tope(),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    /**
     * Quiénes son, con nombre y correo.
     *
     * Se enseña la lista entera antes de mandar: «182 socios» es un número, y
     * los números no dejan ver que ahí dentro está quien se dio de baja ayer.
     */
    public function destinatarios(Request $request, EnvioMasivoService $masivo)
    {
        $datos = $request->validate([
            'grupo' => 'required|string',
            'id_membresia' => 'nullable|integer|exists:membresias,id',
        ]);

        $socios = $masivo->destinatarios($datos['grupo'], $datos['id_membresia'] ?? null);

        return response()->json([
            'cuantos' => $socios->count(),
            'socios' => $socios->map(fn ($c) => [
                'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                'email' => $c->email,
            ])->values(),
        ]);
    }

    /** Cómo le llegaría a uno de ellos, con sus datos puestos. */
    public function vistaPrevia(Request $request, EnvioMasivoService $masivo)
    {
        $datos = $request->validate([
            'grupo' => 'required|string',
            'id_membresia' => 'nullable|integer|exists:membresias,id',
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string|max:50000',
        ]);

        $uno = $masivo->destinatarios($datos['grupo'], $datos['id_membresia'] ?? null)->first();

        if (! $uno) {
            return response()->json(['error' => 'Ese grupo no tiene a nadie ahora mismo.'], 422);
        }

        return response()->json(
            $masivo->personalizar($uno, $datos['asunto'], $datos['mensaje'])
            + ['socio' => trim("{$uno->nombres} {$uno->apellido_paterno}")]
        );
    }

    public function store(Request $request, EnvioMasivoService $masivo)
    {
        $datos = $request->validate([
            'grupo' => 'required|string',
            'id_membresia' => 'nullable|integer|exists:membresias,id',
            'asunto' => 'required|string|min:5|max:255',
            'mensaje' => 'required|string|min:10|max:50000',
            // Vacio = sale ya. Con fecha, la manda el comando de ese dia.
            'cuando' => 'nullable|date|after_or_equal:today',
        ], [
            'asunto.required' => 'Ponle un asunto: es lo único que se ve en la bandeja.',
            'asunto.min' => 'El asunto se queda corto.',
            'mensaje.required' => 'Escribe el mensaje.',
            'mensaje.min' => 'El mensaje se queda corto.',
            'cuando.after_or_equal' => 'No se puede programar un correo para ayer.',
        ]);

        // El turno se reserva DESPUES de validar, y aqui importa mas que en
        // ningun otro sitio: un doble clic manda el correo dos veces a todo el
        // grupo, y eso lo ven doscientas personas.
        if (! $this->validateFormToken($request, 'notificacion_masiva')) {
            return back()->with('error', 'Ese aviso ya se mandó. Míralo en el historial antes de repetirlo.');
        }

        $resultado = $masivo->enviar(
            $datos['grupo'],
            $datos['asunto'],
            $datos['mensaje'],
            $datos['id_membresia'] ?? null,
            ! empty($datos['cuando']) ? Carbon::parse($datos['cuando']) : null
        );

        return redirect()
            ->route('panel.notificaciones.index')
            ->with('success', $this->contarLoQuePaso($resultado));
    }

    /**
     * @param array{enviados:int,fallidos:int,programados:int,para:?string,motivos:list<string>} $resultado
     */
    private function contarLoQuePaso(array $resultado): string
    {
        // Programado no es enviado, y decirlo igual seria mentir: nadie ha
        // recibido nada todavia. Se dice cuantos y para cuando.
        if ($resultado['programados'] > 0) {
            $cuantos = $resultado['programados'] === 1
                ? 'Queda 1 correo programado'
                : "Quedan {$resultado['programados']} correos programados";

            return "{$cuantos} para el {$resultado['para']}. Salen esa mañana."
                . ' Hasta entonces se pueden cancelar desde el listado.';
        }

        $aviso = $resultado['enviados'] === 1
            ? 'Se mandó 1 correo.'
            : "Se mandaron {$resultado['enviados']} correos.";

        if ($resultado['fallidos'] === 0) {
            return $aviso;
        }

        // Cuántos fallaron va SIEMPRE, aunque sea uno: un envío a medias que se
        // anuncia como completo es peor que uno que falla del todo.
        $aviso .= $resultado['fallidos'] === 1
            ? ' Uno no salió'
            : " {$resultado['fallidos']} no salieron";

        return $aviso . ': ' . implode('; ', $resultado['motivos'])
            . '. Están en el listado con su motivo.';
    }
}
