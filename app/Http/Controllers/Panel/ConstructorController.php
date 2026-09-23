<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ConstructorInformes;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\InformeGuardado;

/**
 * Constructor de informes del panel nuevo.
 *
 * Se elige de qué (socios, pagos, membresías…), qué columnas y con qué filtros.
 * Todo el peso está en ConstructorInformes; aquí solo se recibe la petición y
 * se devuelve la tabla o el CSV.
 */
class ConstructorController extends Controller
{
    public function __construct(private readonly ConstructorInformes $informes)
    {
    }

    public function index(Request $request)
    {
        return Inertia::render('Reportes/Constructor', [
            'catalogo' => $this->informes->catalogoParaPantalla(),
            'limites' => ConstructorInformes::LIMITES,
            'tope' => ConstructorInformes::TOPE,
            'guardados' => $this->guardadosDe($request),
        ]);
    }

    /**
     * Guarda la receta del informe que se acaba de armar.
     *
     * LA RECETA, NO EL RESULTADO: columnas, filtros y orden. Al abrirlo se
     * vuelve a consultar, así que trae los datos de hoy. Guardar las filas
     * sería una foto que envejece sola.
     *
     * Con el mismo nombre se pisa el anterior en vez de crear otro: «Socios de
     * Santo Tomás» dos veces en la lista, con distinto contenido, no se
     * distinguen.
     */
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:80',
            'modulo' => 'required|string|max:40',
            'configuracion' => 'required|array',
        ], [
            'nombre.required' => 'Ponle un nombre para poder encontrarlo después.',
        ]);

        abort_unless($this->informes->existe($datos['modulo']), 404);

        InformeGuardado::updateOrCreate(
            ['id_usuario' => $request->user()->id, 'nombre' => trim($datos['nombre'])],
            ['modulo' => $datos['modulo'], 'configuracion' => $datos['configuracion']]
        );

        return back()->with('success', "Informe «{$datos['nombre']}» guardado.");
    }

    /** Quita uno de la lista. Solo los propios: la de cada uno es suya. */
    public function olvidar(Request $request, InformeGuardado $informe)
    {
        abort_unless($informe->id_usuario === $request->user()->id, 403);

        $informe->delete();

        return back()->with('success', 'Informe quitado de tu lista.');
    }

    /**
     * Los informes guardados de quien mira.
     *
     * Cada uno ve los suyos: la lista de informes de alguien es su forma de
     * trabajar, y mezclarlas convertiría el menú en un cajón común.
     *
     * @return list<array<string,mixed>>
     */
    private function guardadosDe(Request $request): array
    {
        return InformeGuardado::where('id_usuario', $request->user()->id)
            ->orderBy('nombre')
            ->get()
            ->map(fn (InformeGuardado $i) => [
                'uuid' => $i->uuid,
                'nombre' => $i->nombre,
                'modulo' => $i->modulo,
                'configuracion' => $i->configuracion,
            ])
            ->all();
    }

    /**
     * Devuelve la tabla.
     *
     * Va por JSON y no por Inertia a propósito: se pulsa «Ver» muchas veces
     * seguidas cambiando columnas, y recargar la página entera en cada intento
     * perdería la configuración que se acaba de armar.
     */
    public function generar(Request $request, string $modulo)
    {
        if (! $this->informes->existe($modulo)) {
            return response()->json(['error' => 'Ese informe no existe.'], 404);
        }

        return response()->json(
            $this->informes->ejecutar($modulo, $this->peticion($request))
        );
    }

    /**
     * El mismo informe, en un archivo que abre Excel.
     *
     * Se escribe A MEDIDA QUE SALE, sin armar el archivo entero en memoria: son
     * hasta 5.000 filas y guardarlas dos veces —una en la consulta y otra en el
     * texto del CSV— es el doble de memoria por nada.
     */
    public function exportar(Request $request, string $modulo): StreamedResponse
    {
        abort_unless($this->informes->existe($modulo), 404);

        $informe = $this->informes->ejecutar($modulo, $this->peticion($request));
        $nombre = 'informe-' . $modulo . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($informe) {
            $salida = fopen('php://output', 'w');

            // Sin esta marca al principio, Excel abre el archivo en su propia
            // codificación y los acentos salen rotos.
            fwrite($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($salida, array_column($informe['columnas'], 'titulo'), ';');

            foreach ($informe['filas'] as $fila) {
                fputcsv($salida, array_map($this->paraCsv(...), array_values($fila)), ';');
            }

            fclose($salida);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** Lo que el navegador mandó, sin tocar: el servicio decide qué acepta. */
    private function peticion(Request $request): array
    {
        return [
            'columnas' => (array) $request->input('columnas', []),
            'filtros' => (array) $request->input('filtros', []),
            'orden' => $request->input('orden'),
            'direccion' => (string) $request->input('direccion', 'desc'),
            'limite' => $request->input('limite'),
        ];
    }

    private function paraCsv(mixed $valor): string
    {
        return match (true) {
            $valor === null => '',
            is_bool($valor) => $valor ? 'Sí' : 'No',
            // Los decimales van con coma: en un Excel en español, «40000.5»
            // entra como texto y no se puede sumar.
            is_float($valor) => number_format($valor, $valor == (int) $valor ? 0 : 2, ',', ''),
            is_int($valor) => (string) $valor,
            default => $this->sinFormula((string) $valor),
        };
    }

    /**
     * Un texto que empieza por = + - @ Excel lo abre como FÓRMULA: un socio
     * anotado como «=HIPERVINCULO(...)» se ejecutaría al abrir el informe en el
     * computador del gimnasio. Con un apóstrofo delante se lee tal cual.
     */
    private function sinFormula(string $texto): string
    {
        return preg_match('/^[=+\-@\t\r]/', $texto) && ! is_numeric($texto) ? "'" . $texto : $texto;
    }
}
