<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Clientes del panel nuevo (Inertia + React).
 *
 * Convive con Admin\ClienteController, que sigue sirviendo las vistas Blade
 * mientras dure la migracion. Cuando /panel cubra todo, /admin se redirige aqui
 * y aquel se borra.
 */
class ClienteController extends Controller
{
    /**
     * La busqueda y la paginacion se hacen EN LA BASE y no en el navegador.
     *
     * La pantalla Blade traia 100 filas de golpe y filtraba con jQuery: con
     * 2.000 socios eso son 2.000 filas viajando en cada carga para enseñar 25.
     */
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $clientes = Cliente::query()
            ->where('activo', true)
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('run_pasaporte', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('celular', 'like', "%{$busqueda}%");
                });
            })
            // Solo la inscripcion mas reciente: es la unica que se pinta en la
            // fila, y traerlas todas multiplicaba las consultas por socio.
            ->with(['inscripciones' => fn ($q) => $q->latest('fecha_vencimiento')->with('membresia')->limit(1)])
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Cliente $cliente) {
                $inscripcion = $cliente->inscripciones->first();

                return [
                    'uuid' => $cliente->uuid,
                    'run_pasaporte' => $cliente->run_pasaporte,
                    'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                    'email' => $cliente->email,
                    'celular' => $cliente->celular,
                    'membresia' => $inscripcion?->membresia?->nombre,
                    'id_estado' => $inscripcion?->id_estado,
                    'vence' => $inscripcion?->fecha_vencimiento?->format('d/m/Y'),
                ];
            });

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filtros' => ['buscar' => $busqueda],
            'resumen' => $this->resumen(),
        ]);
    }

    /**
     * Cuatro cifras, no diez: son las que se miran al abrir.
     *
     * Los codigos son los de la tabla estados (100 activa, 101 pausada,
     * 102 vencida); id_estado guarda el CODIGO, no el id de la fila.
     */
    private function resumen(): array
    {
        $conEstado = fn (int $codigo) => Cliente::where('activo', true)
            ->whereHas('inscripciones', fn ($q) => $q->where('id_estado', $codigo))
            ->count();

        return [
            'total' => Cliente::where('activo', true)->count(),
            'activos' => $conEstado(100),
            'pausados' => $conEstado(101),
            'vencidos' => $conEstado(102),
        ];
    }
}
