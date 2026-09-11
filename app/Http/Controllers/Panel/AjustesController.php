<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\Especialista;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Support\Ajustes;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Configuración: una sola puerta.
 *
 * Antes eran cinco entradas sueltas en el menú —planes, convenios, métodos,
 * motivos y papelera— sin nada que dijera que van juntas. Ahora hay una, y
 * dentro está todo lo que se toca de tarde en tarde.
 *
 * LOS CATÁLOGOS NO SE METEN AQUÍ DENTRO, se enlazan. Cada uno es una tabla con
 * su propio alta y su propia edición, y apilar cuatro tablas en una pantalla la
 * haría el doble de larga sin que nada se encuentre antes. Lo que sí vive aquí
 * son los ajustes, que son formularios cortos y no tenían sitio en ninguna
 * parte: hasta ahora estaban escritos a mano dentro del código.
 */
class AjustesController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracion', [
            'grupos' => $this->ajustesPorGrupo(),
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function update(Request $request)
    {
        $definiciones = Ajustes::definiciones();

        $reglas = [];

        foreach ($definiciones as $clave => $definicion) {
            // El punto de la clave se escapa: en las reglas de Laravel separa
            // niveles de un array, y «gimnasio.nombre» se leeria como el campo
            // «nombre» dentro de «gimnasio».
            $campo = str_replace('.', '\.', $clave);

            $reglas[$campo] = $definicion['tipo'] === 'numero'
                ? ['nullable', 'integer', 'min:' . ($definicion['min'] ?? 0), 'max:' . ($definicion['max'] ?? 999999)]
                : ['nullable', 'string', 'max:255'];
        }

        $request->validate($reglas);

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

        return back()->with('success', 'Configuración guardada.');
    }

    /**
     * Los ajustes listos para pintar, con su valor actual.
     *
     * @return list<array<string,mixed>>
     */
    private function ajustesPorGrupo(): array
    {
        $porGrupo = [];

        foreach (Ajustes::definiciones() as $clave => $definicion) {
            $porGrupo[$definicion['grupo']][] = [
                'clave' => $clave,
                'etiqueta' => $definicion['etiqueta'],
                'ayuda' => $definicion['ayuda'] ?? null,
                'tipo' => $definicion['tipo'],
                'unidad' => $definicion['unidad'] ?? null,
                'min' => $definicion['min'] ?? null,
                'max' => $definicion['max'] ?? null,
                'valor' => Ajustes::obtener($clave),
                // Para poder decir «lo dejaste en 10, por defecto son 3».
                'defecto' => $definicion['defecto'],
            ];
        }

        return collect(Ajustes::grupos())
            ->map(fn (array $g, string $clave) => $g + [
                'clave' => $clave,
                'ajustes' => $porGrupo[$clave] ?? [],
            ])
            ->values()
            ->all();
    }

    /**
     * Los cuatro catálogos, con cuántos hay activos.
     *
     * La cuenta importa: un gimnasio sin ningún plan activo no puede inscribir
     * a nadie, y eso hay que verlo desde aquí y no descubrirlo con el socio
     * delante.
     *
     * @return list<array<string,mixed>>
     */
    private function catalogos(): array
    {
        return [
            [
                'href' => '/panel/membresias',
                'titulo' => 'Planes',
                'descripcion' => 'Lo que se vende y a qué precio',
                'activos' => Membresia::where('activo', true)->count(),
                'total' => Membresia::count(),
                // Un plan sin precio vigente no se puede vender: el alta lo
                // rechaza. Se avisa aqui, que es donde se arregla.
                'aviso' => $this->planesSinPrecio(),
            ],
            [
                'href' => '/panel/convenios',
                'titulo' => 'Convenios',
                'descripcion' => 'Empresas e instituciones con descuento',
                'activos' => Convenio::where('activo', true)->count(),
                'total' => Convenio::count(),
                // Un convenio en la web sin logo sale como un recuadro con su
                // nombre: funciona, pero se ve a medio hacer.
                'aviso' => ($sinLogo = Convenio::where('activo', true)->where('mostrar_en_web', true)->whereNull('logo')->count())
                    ? ($sinLogo === 1
                        ? 'Un convenio de la web no tiene logo.'
                        : "{$sinLogo} convenios de la web no tienen logo.")
                    : null,
            ],
            [
                'href' => '/panel/metodos-pago',
                'titulo' => 'Métodos de pago',
                'descripcion' => 'Cómo se puede pagar en el mesón',
                'activos' => MetodoPago::where('activo', true)->count(),
                'total' => MetodoPago::count(),
                'aviso' => MetodoPago::where('activo', true)->count() === 0
                    ? 'Sin ningún método activo no se puede cobrar.'
                    : null,
            ],
            [
                'href' => '/panel/motivos-descuento',
                'titulo' => 'Motivos de descuento',
                'descripcion' => 'Por qué se rebaja el precio',
                'activos' => MotivoDescuento::where('activo', true)->count(),
                'total' => MotivoDescuento::count(),
                'aviso' => null,
            ],
            [
                'href' => '/panel/especialistas',
                'titulo' => 'Especialistas',
                'descripcion' => 'Los profesionales que aparecen en la web',
                'activos' => Especialista::where('activo', true)->count(),
                'total' => Especialista::count(),
                'aviso' => null,
            ],
        ];
    }

    private function planesSinPrecio(): ?string
    {
        $sinPrecio = Membresia::where('activo', true)
            ->whereDoesntHave('precios', fn ($q) => $q
                ->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now()))
            ->count();

        if ($sinPrecio === 0) {
            return null;
        }

        return $sinPrecio === 1
            ? 'Un plan activo no tiene precio: no se puede vender.'
            : "{$sinPrecio} planes activos no tienen precio: no se pueden vender.";
    }
}
