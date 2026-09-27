<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Services\JuntarFichas;
use App\Support\FichasRepetidas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deja a la vista solo a los socios con RUT.
 *
 * Las planillas traían fichas sin RUT: no se pueden buscar por RUT en el
 * mesón, no se distinguen de otro que se llame igual y casi nunca traían
 * contacto. El dueño decidió (27-sep-2026) quedarse solo con los que tienen
 * RUT; quien no lo tiene se vuelve a registrar, con su RUT, cuando venga.
 *
 * Las fichas sin RUT van a la PAPELERA, con sus membresías: salen de la
 * lista, las búsquedas y los informes, y se recuperan si alguna hacía falta.
 * Antes, si la ficha sin RUT es de alguien que también tiene ficha con RUT
 * (duplicado probable), se junta con esa para no perder su historial.
 *
 * NO toca a quien tenga movimiento hecho en el panel —pagos, fiado,
 * contratos o membresías que no vengan de las planillas—: esos se listan.
 *
 * Sin --confirmar solo cuenta lo que haría.
 */
class QuitarSociosSinRut extends Command
{
    protected $signature = 'datos:quitar-sin-rut {--confirmar : Hacerlo de verdad (sin esto solo cuenta)}';

    protected $description = 'Manda a la papelera las fichas sin RUT de las planillas (junta antes los duplicados con su ficha con RUT)';

    public function handle(JuntarFichas $juntar): int
    {
        $sinRut = fn () => Cliente::query()
            ->whereNull('datos_borrados_en')
            ->where(fn ($q) => $q->whereNull('run_pasaporte')->orWhere('run_pasaporte', ''));

        // Duplicados probables: una ficha sin RUT y otra con RUT de la misma persona.
        $pares = collect(FichasRepetidas::grupos())->where('probable', true)->map(function (array $g) {
            $fichas = collect($g['fichas']);

            return ['queda' => $fichas->first(fn ($f) => filled($f['rut'])), 'salen' => $fichas->filter(fn ($f) => blank($f['rut']))];
        })->filter(fn ($p) => $p['queda'] && $p['salen']->isNotEmpty());

        $candidatos = $sinRut()->get();
        $conMovimiento = $candidatos->filter(fn (Cliente $c) => $this->tieneMovimiento($c));

        $this->info('Socios sin RUT: ' . $candidatos->count());
        $this->line('  se juntan con su ficha con RUT: ' . $pares->sum(fn ($p) => $p['salen']->count()));
        $this->line('  van a la papelera: ' . ($candidatos->count() - $conMovimiento->count() - $pares->sum(fn ($p) => $p['salen']->count())));
        $this->line('  tienen movimiento del panel (se dejan): ' . $conMovimiento->count());
        foreach ($conMovimiento as $c) {
            $this->line("    #{$c->id} {$c->nombres} {$c->apellido_paterno}");
        }

        if (! $this->option('confirmar')) {
            $this->warn('Solo se contó. Para hacerlo: php artisan datos:quitar-sin-rut --confirmar');

            return self::SUCCESS;
        }

        foreach ($pares as $par) {
            $queda = Cliente::where('uuid', $par['queda']['uuid'])->first();

            foreach ($par['salen'] as $f) {
                $sale = Cliente::where('uuid', $f['uuid'])->first();

                if ($queda && $sale) {
                    $queda = $juntar->juntar($queda, $sale);
                    $this->line("  juntada #{$sale->id} en #{$queda->id} ({$queda->nombres} {$queda->apellido_paterno})");
                }
            }
        }

        $enviados = 0;

        DB::transaction(function () use ($sinRut, &$enviados) {
            foreach ($sinRut()->get() as $socio) {
                if ($this->tieneMovimiento($socio)) {
                    continue;
                }

                // A la papelera con sus membresías, y de baja: si se restaura,
                // vuelve como estaba y se le vende un plan.
                foreach ($socio->inscripciones()->get() as $inscripcion) {
                    $inscripcion->delete();
                }

                $socio->forceFill([
                    'activo' => false,
                    'observaciones' => trim(($socio->observaciones ? $socio->observaciones . "\n" : '') . 'A la papelera el ' . now()->format('d/m/Y') . ': ficha sin RUT de las planillas.'),
                ])->saveQuietly();
                $socio->delete();
                $enviados++;
            }
        });

        FichasRepetidas::olvidar();
        $this->info("Listo: {$enviados} fichas sin RUT en la papelera. Quedan " . Cliente::count() . ' socios a la vista.');

        return self::SUCCESS;
    }

    /** Lo hecho en el panel: eso no se toca sin mirarlo. */
    private function tieneMovimiento(Cliente $c): bool
    {
        return DB::table('pagos')->where('id_cliente', $c->id)->exists()
            || DB::table('fiados')->where('id_cliente', $c->id)->exists()
            || DB::table('contratos')->where('id_cliente', $c->id)->exists()
            || DB::table('inscripciones')->where('id_cliente', $c->id)
                ->where(fn ($q) => $q->whereNull('observaciones')->orWhere('observaciones', 'not like', 'Importado%'))
                ->exists();
    }
}
