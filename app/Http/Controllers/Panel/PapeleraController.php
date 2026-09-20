<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Lo que se borró y todavía se puede recuperar.
 *
 * UNA SOLA PANTALLA para los siete tipos, y no siete papeleras separadas: quien
 * la abre no viene a mirar «la papelera de convenios», viene a buscar algo que
 * borró hace un rato y muchas veces ni se acuerda de qué era exactamente.
 *
 * Aquí NO se borra del todo. Un socio con inscripciones, un plan con
 * membresías vendidas o un pago cuadrado en una caja de hace tres años están
 * referenciados por otras filas, y quitarlos de verdad deja huecos en sitios
 * que nadie va a mirar hasta que cuadren mal las cuentas. Se restaura, o se
 * deja donde está.
 */
class PapeleraController extends Controller
{
    /**
     * Qué se guarda en la papelera y cómo se lee cada cosa.
     *
     * @return array<string,array<string,mixed>>
     */
    private function tipos(): array
    {
        return [
            'clientes' => [
                'titulo' => 'Socios',
                'modelo' => Cliente::class,
                'ruta' => '/panel/clientes',
                'describir' => fn (Cliente $c) => [
                    'que' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                    'detalle' => $c->run_pasaporte ?: $c->email,
                ],
            ],
            'inscripciones' => [
                'titulo' => 'Membresías vendidas',
                'modelo' => Inscripcion::class,
                'ruta' => '/panel/inscripciones',
                'con' => ['cliente', 'membresia'],
                'describir' => fn (Inscripcion $i) => [
                    'que' => $i->cliente
                        ? trim("{$i->cliente->nombres} {$i->cliente->apellido_paterno}")
                        : 'Socio eliminado',
                    'detalle' => trim(($i->membresia?->nombre ?? 'Sin plan')
                        . ' · vencía ' . ($i->fecha_vencimiento?->format('d/m/Y') ?? '-')),
                ],
            ],
            'pagos' => [
                'titulo' => 'Pagos',
                'modelo' => Pago::class,
                'ruta' => '/panel/pagos',
                'con' => ['cliente'],
                'describir' => fn (Pago $p) => [
                    'que' => $p->cliente
                        ? trim("{$p->cliente->nombres} {$p->cliente->apellido_paterno}")
                        : 'Socio eliminado',
                    'detalle' => '$' . number_format((float) $p->monto_abonado, 0, ',', '.')
                        . ' · ' . ($p->fecha_pago?->format('d/m/Y') ?? '-'),
                ],
            ],
            'membresias' => [
                'titulo' => 'Planes',
                'modelo' => Membresia::class,
                'ruta' => '/panel/membresias',
                'describir' => fn (Membresia $m) => [
                    'que' => $m->nombre,
                    'detalle' => $m->descripcion,
                ],
            ],
            'convenios' => [
                'titulo' => 'Convenios',
                'modelo' => Convenio::class,
                'ruta' => '/panel/convenios',
                'describir' => fn (Convenio $c) => [
                    'que' => $c->nombre,
                    'detalle' => $c->descripcion,
                ],
            ],
            'metodos-pago' => [
                'titulo' => 'Métodos de pago',
                'modelo' => MetodoPago::class,
                'ruta' => '/panel/metodos-pago',
                'describir' => fn (MetodoPago $m) => [
                    'que' => $m->nombre,
                    'detalle' => $m->descripcion,
                ],
            ],
            'motivos-descuento' => [
                'titulo' => 'Motivos de descuento',
                'modelo' => MotivoDescuento::class,
                'ruta' => '/panel/motivos-descuento',
                'describir' => fn (MotivoDescuento $m) => [
                    'que' => $m->nombre,
                    'detalle' => $m->descripcion,
                ],
            ],
        ];
    }

    public function index()
    {
        $grupos = [];

        foreach ($this->tipos() as $clave => $tipo) {
            $consulta = $tipo['modelo']::onlyTrashed();

            if (isset($tipo['con'])) {
                // Los nombres del socio y del plan salen de otras tablas, y
                // pedirlos por fila serian tantas consultas como filas.
                $consulta->with($tipo['con']);
            }

            $filas = $consulta
                ->orderByDesc('deleted_at')
                // Con mas de cien no se busca a ojo, se busca en el listado de
                // cada cosa. Aqui se viene a por lo de esta semana.
                ->limit(100)
                ->get()
                ->map(function (Model $fila) use ($clave, $tipo) {
                    $como = ($tipo['describir'])($fila);

                    return [
                        'id' => $fila->getKey(),
                        'tipo' => $clave,
                        /*
                         * BORRAR SUS DATOS DESDE AQUÍ.
                         *
                         * Un socio en la papelera no tiene ficha que abrir: su
                         * dirección no responde. Sin esto, la única forma de
                         * atender un «bórrenme mis datos» de alguien ya dado de
                         * baja era restaurarlo, borrarlo y volver a borrarlo.
                         *
                         * Lo que se borra son SUS DATOS —nombre, RUT, contacto,
                         * foto, correos, contratos—; sus pagos y membresías se
                         * quedan en las cuentas, sin nombre.
                         */
                        'datos_borrables' => $fila instanceof Cliente && ! $fila->datos_borrados_en,
                        'por_que_no' => $fila instanceof Cliente
                            ? app(\App\Services\BorradoDeDatosService::class)->porQueNoSePuede($fila)
                            : null,
                        'que' => $como['que'],
                        'detalle' => $como['detalle'] ?: null,
                        'borrado' => $fila->deleted_at?->format('d/m/Y H:i'),
                        'hace' => $fila->deleted_at?->diffForHumans(),
                    ];
                });

            if ($filas->isNotEmpty()) {
                $grupos[] = [
                    'clave' => $clave,
                    'titulo' => $tipo['titulo'],
                    'cuantos' => $filas->count(),
                    'filas' => $filas->values(),
                ];
            }
        }

        return Inertia::render('Papelera', ['grupos' => $grupos]);
    }

    /**
     * Lo devuelve a su sitio.
     *
     * Ojo con las membresías: restaurar una de un socio que sigue borrado deja
     * una membresía sin dueño. Se avisa, pero se hace igual —la alternativa es
     * dejar algo que se borró por error donde nadie puede tocarlo—.
     */
    public function restaurar(Request $request, string $tipo, string $id)
    {
        $config = $this->tipos()[$tipo] ?? abort(404);

        $fila = $config['modelo']::onlyTrashed()->findOrFail($id);
        $fila->restore();

        // Un pago que vuelve cambia el saldo de todos los de su membresía:
        // sin recalcular, los demás seguían diciendo lo que se debía sin él.
        if ($fila instanceof \App\Models\Pago) {
            $fila->inscripcion?->recalcularSusPagos();
        }

        $como = ($config['describir'])($fila);

        return back()->with('success', "«{$como['que']}» vuelve a estar disponible.");
    }

    /**
     * Borra los datos personales de un socio que está en la papelera.
     *
     * Sus pagos y membresías NO se tocan: siguen en las cuentas a nombre de
     * «Socio Borrado», porque el gimnasio tiene que poder cuadrar sus ingresos
     * de años anteriores aunque la persona ya no exista para el sistema.
     *
     * Es lo mismo que hace el botón de su ficha, pero aquí llega quien ya fue
     * dado de baja: su ficha no se puede abrir, y sin esto había que
     * restaurarlo, borrarle los datos y volver a darlo de baja.
     */
    public function borrarDatos(Request $request, string $id, \App\Services\BorradoDeDatosService $borrado)
    {
        // Borrar los datos de una persona es cosa de quien puede eliminar
        // socios, no de cualquiera que entre a la papelera.
        abort_unless($request->user()?->puede('clientes.eliminar'), 403);

        $request->merge(['confirmacion' => mb_strtoupper(trim((string) $request->input('confirmacion')))]);

        $datos = $request->validate([
            'motivo' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Services\BorradoDeDatosService::MOTIVOS))],
            'confirmacion' => ['required', 'in:BORRAR'],
        ], [
            'motivo.required' => 'Elige por qué se borran.',
            'confirmacion.required' => 'Escribe BORRAR para confirmar.',
            'confirmacion.in' => 'Escribe BORRAR para confirmar.',
        ]);

        $cliente = Cliente::onlyTrashed()->findOrFail($id);

        try {
            $borrado->borrar($cliente, $request->user()?->id, $datos['motivo']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Se borraron sus datos personales. Sus membresías y pagos siguen en las cuentas, sin nombre.');
    }
}
