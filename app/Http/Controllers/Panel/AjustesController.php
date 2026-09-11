<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Ajustes;
use App\Support\EstadoDeConfiguracion;
use App\Support\Programador;
use App\Support\WebPublica;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Configuración: una portada con lo que falta, y una página por tema.
 *
 * ANTES era una sola pantalla con pestañas, y una de ellas —«Catálogos»— eran
 * tarjetas que llevaban a otras pantallas. Las pestañas no dejaban rastro en el
 * historial —«atrás» sacaba de Configuración entera— y desde un catálogo no
 * había cómo volver: el menú no marcaba nada. Quien entraba se perdía.
 *
 * AHORA cada tema tiene su dirección —/panel/configuracion/horario— y todas las
 * pantallas de Configuración, catálogos incluidos, se ven dentro del mismo
 * marco con el menú de secciones a la izquierda. «Atrás» vuelve a la sección
 * anterior, y siempre se ve dónde se está.
 */
class AjustesController extends Controller
{
    /** La portada: lo que falta configurar, con el camino a donde se arregla. */
    public function index()
    {
        return Inertia::render('Configuracion/Inicio', [
            'puntos' => EstadoDeConfiguracion::puntos(),
        ]);
    }

    /** Un tema de ajustes, en su propia página. */
    public function show(string $grupo)
    {
        return Inertia::render('Configuracion', [
            'grupo' => Ajustes::grupos()[$grupo] + [
                'clave' => $grupo,
                'ajustes' => $this->ajustesDe($grupo),
            ],
            // Lo que cada tema enseña además de sus campos.
            'extra' => match ($grupo) {
                'tareas' => [
                    'tareas' => Programador::estado(),
                    'correoConfigurado' => EstadoDeConfiguracion::correoConfigurado(),
                ],
                'web' => [
                    // Cómo sale la portada en Google, para verlo antes de guardar.
                    'vistaGoogle' => [
                        'titulo' => WebPublica::tituloDeInicio(),
                        'url' => url('/'),
                        'descripcionAutomatica' => WebPublica::descripcionAutomatica(WebPublica::precioDesde()),
                    ],
                ],
                default => null,
            },
        ]);
    }

    public function update(Request $request)
    {
        $definiciones = Ajustes::definiciones();

        $reglas = [];
        $mensajes = [];

        foreach ($definiciones as $clave => $definicion) {
            // El punto de la clave se escapa: en las reglas de Laravel separa
            // niveles de un array, y «gimnasio.nombre» se leeria como el campo
            // «nombre» dentro de «gimnasio».
            $campo = str_replace('.', '\.', $clave);

            $reglas[$campo] = match ($definicion['tipo']) {
                'numero' => ['nullable', 'integer', 'min:' . ($definicion['min'] ?? 0), 'max:' . ($definicion['max'] ?? 999999)],
                // Las fechas del aviso: llegan del selector de fecha como AAAA-MM-DD.
                'fecha' => ['nullable', 'date_format:Y-m-d'],
                'hora' => ['nullable', 'date_format:H:i'],
                'area' => ['nullable', 'string', 'max:' . ($definicion['largo'] ?? 500)],
                default => ['nullable', 'string', 'max:' . ($definicion['largo'] ?? 255)],
            };

            $mensajes["{$campo}.date_format"] = $definicion['tipo'] === 'hora'
                ? 'Escribe la hora como 08:00.'
                : 'Elige la fecha en el calendario.';
            $mensajes["{$campo}.max"] = $definicion['tipo'] === 'numero'
                ? 'Como mucho ' . ($definicion['max'] ?? 999999) . '.'
                : 'Es muy largo: hasta ' . ($definicion['largo'] ?? 255) . ' caracteres.';
            $mensajes["{$campo}.min"] = 'Como mínimo ' . ($definicion['min'] ?? 0) . '.';
        }

        $request->validate($reglas, $mensajes);

        /*
         * Se lee del array TAL CUAL, no con $request->input().
         *
         * Las claves llevan punto —«gimnasio.nombre»— y `input()` interpreta ese
         * punto como un nivel de array: busca «nombre» dentro de «gimnasio», que
         * no existe, y devuelve vacío. La fila se guardaba con el valor en
         * blanco y la pantalla decía «guardado» sin haber guardado nada.
         */
        $enviado = $request->all();
        $valores = [];

        foreach (array_keys($definiciones) as $clave) {
            if (array_key_exists($clave, $enviado)) {
                $valores[$clave] = $enviado[$clave];
            }
        }

        if ($valores === []) {
            throw ValidationException::withMessages([
                'ajustes' => 'No llegó ningún ajuste que guardar.',
            ]);
        }

        /*
         * Lo que se pinta en la pagina publica se revisa por FORMA, no solo por
         * largo. Un enlace de Instagram termina en un href: si fuera
         * «javascript:...», cualquiera con acceso a Configuracion meteria
         * codigo en la pagina que ven los clientes. Y un ID de Analytics mal
         * copiado mediria en silencio contra ninguna parte.
         */
        $errores = [];

        foreach ($valores as $clave => $valor) {
            $definicion = $definiciones[$clave];
            $valor = trim((string) $valor);

            if ($valor === '') {
                continue;
            }

            if (($definicion['formato'] ?? null) === 'url'
                && (! filter_var($valor, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $valor))) {
                $errores[$clave] = 'Tiene que ser un enlace completo, que empiece por https://';
            }

            if (isset($definicion['patron']) && ! preg_match($definicion['patron'], $valor)) {
                $errores[$clave] = $definicion['mensaje'] ?? 'El formato no es válido.';
            }
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        Ajustes::guardar($valores);

        return back()->with('success', 'Guardado.');
    }

    /**
     * Los ajustes de un tema listos para pintar, con su valor actual.
     *
     * @return list<array<string,mixed>>
     */
    private function ajustesDe(string $grupo): array
    {
        $ajustes = [];

        foreach (Ajustes::definiciones() as $clave => $definicion) {
            if ($definicion['grupo'] !== $grupo) {
                continue;
            }

            $ajustes[] = [
                'clave' => $clave,
                'etiqueta' => $definicion['etiqueta'],
                'ayuda' => $definicion['ayuda'] ?? null,
                'seccion' => $definicion['seccion'] ?? null,
                'ejemplo' => $definicion['ejemplo'] ?? null,
                'tipo' => $definicion['tipo'],
                'unidad' => $definicion['unidad'] ?? null,
                'min' => $definicion['min'] ?? null,
                'max' => $definicion['max'] ?? null,
                'largo' => $definicion['largo'] ?? null,
                'valor' => Ajustes::obtener($clave),
                // Para poder decir «lo dejaste en 10, por defecto son 3».
                'defecto' => $definicion['defecto'],
            ];
        }

        return $ajustes;
    }
}
