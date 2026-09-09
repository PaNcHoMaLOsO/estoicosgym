<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use Inertia\Inertia;

/**
 * Los cuatro catalogos de configuracion del panel nuevo.
 *
 * Van juntos y no en cuatro controladores porque son cuatro tablas pequeñas
 * —entre 3 y 11 filas— que solo se listan: no hay busqueda, ni paginacion, ni
 * filtros. Separarlos habria dado cuatro ficheros de quince lineas cada uno
 * repitiendo la misma forma. Cuando alguno gane logica propia, se saca.
 */
class ConfiguracionController extends Controller
{
    public function membresias()
    {
        $membresias = Membresia::query()
            // El precio vigente vive en otra tabla; sin esto la pantalla haria
            // una consulta por fila para pintar la columna de precio.
            ->with(['precios' => fn ($q) => $q->where('activo', true)])
            ->withCount('inscripciones')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Membresia $m) => [
                'uuid' => $m->uuid,
                'nombre' => $m->nombre,
                'descripcion' => $m->descripcion,
                'duracion' => $this->duracion($m),
                'max_pausas' => $m->max_pausas,
                'precio' => (int) ($m->precios->first()->precio_normal ?? 0),
                'inscripciones' => $m->inscripciones_count,
                'activo' => (bool) $m->activo,
            ]);

        return Inertia::render('Configuracion/Membresias', ['membresias' => $membresias]);
    }

    public function convenios()
    {
        $convenios = Convenio::query()
            ->withCount('clientes')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Convenio $c) => [
                'uuid' => $c->uuid,
                'nombre' => $c->nombre,
                'tipo' => $c->tipo,
                // Un convenio descuenta por porcentaje O por monto fijo, nunca
                // por los dos: se resuelve aqui para que la fila no decida.
                'descuento' => $c->descuento_porcentaje > 0
                    ? rtrim(rtrim(number_format((float) $c->descuento_porcentaje, 1, ',', '.'), '0'), ',') . ' %'
                    : ($c->descuento_monto > 0 ? '$' . number_format((float) $c->descuento_monto, 0, ',', '.') : '—'),
                'contacto' => $c->contacto_nombre,
                'clientes' => $c->clientes_count,
                'activo' => (bool) $c->activo,
            ]);

        return Inertia::render('Configuracion/Convenios', ['convenios' => $convenios]);
    }

    public function metodosPago()
    {
        $metodos = MetodoPago::query()
            ->withCount('pagos')
            ->orderBy('nombre')
            ->get()
            ->map(fn (MetodoPago $m) => [
                'id' => $m->id,
                'nombre' => $m->nombre,
                'descripcion' => $m->descripcion,
                'requiere_comprobante' => (bool) $m->requiere_comprobante,
                'pagos' => $m->pagos_count,
                'activo' => (bool) $m->activo,
            ]);

        return Inertia::render('Configuracion/MetodosPago', ['metodos' => $metodos]);
    }

    public function motivosDescuento()
    {
        $motivos = MotivoDescuento::query()
            ->orderBy('nombre')
            ->get()
            ->map(fn (MotivoDescuento $m) => [
                'id' => $m->id,
                'nombre' => $m->nombre,
                'descripcion' => $m->descripcion,
                'activo' => (bool) $m->activo,
            ]);

        return Inertia::render('Configuracion/MotivosDescuento', ['motivos' => $motivos]);
    }

    /** «Anual», «3 meses», «1 día»: lo que se lee, no dos columnas de numeros. */
    private function duracion(Membresia $membresia): string
    {
        if ($membresia->duracion_meses > 0) {
            return $membresia->duracion_meses === 12
                ? 'Anual'
                : ($membresia->duracion_meses === 1 ? '1 mes' : "{$membresia->duracion_meses} meses");
        }

        if ($membresia->duracion_dias > 0) {
            return $membresia->duracion_dias === 1 ? '1 día' : "{$membresia->duracion_dias} días";
        }

        return '—';
    }
}
