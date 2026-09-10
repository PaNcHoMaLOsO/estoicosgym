<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fiado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * La libreta de lo fiado en el mesón.
 *
 * Se apunta lo que alguien se lleva, se le va sumando cada vez, y cuando paga
 * se salda su cuenta entera de un golpe: nadie paga una bebida de la semana
 * pasada y deja a deber la de ayer.
 */
class FiadoController extends Controller
{
    /** Anota una cosa más en la cuenta de alguien. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'nombre' => 'nullable|string|max:100',
            'concepto' => 'required|string|max:120',
            'monto' => 'required|integer|min:1|max:9999999',
        ], [
            'concepto.required' => 'Apunta qué se llevó.',
            'monto.required' => 'Apunta cuánto es.',
            'monto.min' => 'El monto tiene que ser mayor que cero.',
        ]);

        // Uno de los dos, o no se sabe de quién es la cuenta.
        if (empty($datos['id_cliente']) && trim((string) ($datos['nombre'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'nombre' => 'Di de quién es: elige al socio o escribe un nombre.',
            ]);
        }

        Fiado::create([
            'id_cliente' => $datos['id_cliente'] ?? null,
            // El nombre a mano solo se guarda cuando NO hay socio: con socio, el
            // nombre sale de su ficha y una copia aquí se quedaría vieja el día
            // que se corrija.
            'nombre' => empty($datos['id_cliente']) ? trim($datos['nombre']) : null,
            'concepto' => trim($datos['concepto']),
            'monto' => $datos['monto'],
            'id_usuario' => $request->user()->id,
        ]);

        return back();
    }

    /**
     * Salda la cuenta ENTERA de una persona.
     *
     * De golpe y no línea a línea: quien paga en el mesón paga lo que debe, no
     * la bebida del martes. Marcarlas de una en una es la forma de dejarse una
     * sin querer y que esa persona arrastre $1.500 para siempre.
     */
    public function saldar(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'nombre' => 'nullable|string|max:100',
        ]);

        $pendientes = Fiado::debiendo()
            ->when(
                ! empty($datos['id_cliente']),
                fn ($q) => $q->where('id_cliente', $datos['id_cliente']),
                fn ($q) => $q->whereNull('id_cliente')->where('nombre', $datos['nombre'] ?? '')
            )
            ->get();

        if ($pendientes->isEmpty()) {
            return back()->with('error', 'Esa cuenta ya estaba saldada.');
        }

        $total = $pendientes->sum('monto');

        DB::transaction(function () use ($pendientes, $request) {
            foreach ($pendientes as $fiado) {
                $fiado->update([
                    'pagado' => true,
                    'pagado_en' => now(),
                    'id_usuario_cobro' => $request->user()->id,
                ]);
            }
        });

        $quien = $pendientes->first()->aNombreDe();

        return back()->with(
            'success',
            sprintf('%s pagó $%s. Cuenta saldada.', $quien, number_format($total, 0, ',', '.'))
        );
    }

    /**
     * Quita una línea apuntada por error.
     *
     * Se borra de verdad: un «bebida $1.500» que nunca ocurrió no es un dato
     * que recuperar, es un error de tecleo. Lo que se cobró de verdad se queda
     * marcado como pagado y ahí sigue.
     */
    public function destroy(Fiado $fiado)
    {
        if ($fiado->pagado) {
            return back()->with('error', 'Eso ya se cobró: no se borra.');
        }

        $fiado->delete();

        return back();
    }

    /**
     * Busca a quién apuntarle.
     *
     * Salen TODOS los socios, incluidos los dados de baja: quien se llevó una
     * bebida ayer y hoy ya no está de alta sigue debiendo esa bebida.
     */
    public function buscar(Request $request)
    {
        $texto = trim((string) $request->query('q', ''));

        if (mb_strlen($texto) < 2) {
            return response()->json(['clientes' => []]);
        }

        $clientes = Cliente::query()
            ->where(function ($q) use ($texto) {
                $q->where('nombres', 'like', "%{$texto}%")
                    ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                    ->orWhere('apellido_materno', 'like', "%{$texto}%")
                    ->orWhere('run_pasaporte', 'like', "%{$texto}%");
            })
            ->orderBy('apellido_paterno')
            ->limit(10)
            ->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno', 'run_pasaporte', 'activo']);

        return response()->json([
            'clientes' => $clientes->map(fn (Cliente $c) => [
                'id' => $c->id,
                'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                'rut' => $c->run_pasaporte,
                'activo' => (bool) $c->activo,
            ]),
        ]);
    }
}
