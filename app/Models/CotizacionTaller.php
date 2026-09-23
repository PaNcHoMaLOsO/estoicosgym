<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Lo que se le cotiza a una institución por un mes de taller.
 *
 * GUARDA LAS CLASES, NO LA CIFRA. Una cotización de un colegio no es un número
 * cerrado: se suspende una semana, cae un feriado, el colegio pide media hora
 * menos los viernes. Con el detalle guardado, corregirla es destildar dos
 * casillas y volver a imprimirla; con la cifra sola, hay que rehacer la cuenta
 * a mano y volver a confiar en ella.
 *
 * NO ES EL COBRO: esto dice lo que se espera cobrar, y `CobroTaller` cierra al
 * final del mes lo que de verdad se hizo.
 */
class CotizacionTaller extends Model
{
    use SoftDeletes;

    protected $table = 'cotizaciones_taller';

    protected $fillable = [
        'uuid', 'id_taller', 'numero', 'periodo', 'fecha', 'valido_hasta',
        'descripcion', 'precio_hora', 'horas', 'total', 'neto', 'iva',
        'detalle', 'estado', 'notas', 'id_usuario',
    ];

    protected $casts = [
        'numero' => 'integer',
        'fecha' => 'date',
        'valido_hasta' => 'date',
        'precio_hora' => 'integer',
        'horas' => 'float',
        'total' => 'integer',
        'neto' => 'integer',
        'iva' => 'integer',
        'detalle' => 'array',
    ];

    /** Qué puede pasarle a una cotización, y cómo se dice en pantalla. */
    public const ESTADOS = [
        'borrador' => 'Borrador',
        'enviada' => 'Enviada',
        'aceptada' => 'Aceptada',
        'rechazada' => 'Rechazada',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $cotizacion) => $cotizacion->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'id_taller');
    }

    /**
     * El siguiente número de la serie.
     *
     * LA SERIE ES DEL GIMNASIO, no del sistema: las cotizaciones en papel ya
     * iban por el 77 antes de que esto existiera. Por eso se propone el
     * siguiente al mayor que haya y se deja corregir a mano la primera vez,
     * en vez de empezar a contar desde uno y romper la numeración del colegio.
     * Cuenta también las borradas: un número usado no se reparte otra vez.
     */
    public static function siguienteNumero(): int
    {
        return (int) self::withTrashed()->max('numero') + 1;
    }

    /**
     * Las clases de un mes, listas para cotizarlas.
     *
     * Vienen todas marcadas: lo normal es que el mes se haga entero, y lo que
     * se quita son las excepciones —el feriado, la semana de pruebas—.
     *
     * @return list<array{fecha:string, detalle:string, horas:float, incluida:bool}>
     */
    public static function clasesParaCotizar(Taller $taller, Carbon $mes): array
    {
        return array_map(
            fn (array $clase) => [...$clase, 'incluida' => true],
            $taller->clasesDe($mes)
        );
    }

    /**
     * Deja las líneas ordenadas y rehace la cuenta con las que están marcadas.
     *
     * LAS QUE SE QUITAN NO SE BORRAN: se guardan destildadas. Así la cotización
     * enseña también lo que NO se cobra —«el 1 de mayo es feriado»—, que es la
     * mitad de la conversación con el colegio.
     *
     * @param  list<array<string,mixed>>  $lineas
     */
    public function rehacerLaCuenta(array $lineas): void
    {
        $limpias = [];

        foreach ($lineas as $linea) {
            $horas = round((float) ($linea['horas'] ?? 0), 2);

            if ($horas <= 0) {
                continue;
            }

            $limpias[] = [
                'fecha' => ($linea['fecha'] ?? null) ?: null,
                'detalle' => trim((string) ($linea['detalle'] ?? '')) ?: null,
                'horas' => $horas,
                'incluida' => (bool) ($linea['incluida'] ?? true),
            ];
        }

        usort($limpias, fn ($a, $b) => [$a['fecha'] ?? '', $a['detalle'] ?? ''] <=> [$b['fecha'] ?? '', $b['detalle'] ?? '']);

        $horas = round(array_sum(array_map(
            fn (array $l) => $l['incluida'] ? $l['horas'] : 0,
            $limpias
        )), 2);

        // Igual que en el cobro: se parte del total con IVA dentro, que es lo
        // acordado —«la hora sale treinta mil»—, y de ahí sale el neto. Al
        // revés, el total se iría un peso por el redondeo y el papel dejaría de
        // cuadrar con lo que se le dijo al colegio.
        $total = (int) round($horas * $this->precio_hora);
        $desglose = CobroTaller::desglosar($total);

        $this->detalle = $limpias;
        $this->horas = $horas;
        $this->total = $total;
        $this->neto = $desglose['neto'];
        $this->iva = $desglose['iva'];
    }

    /** Las clases que sí se cobran. @return list<array<string,mixed>> */
    public function lineasIncluidas(): array
    {
        return array_values(array_filter($this->detalle ?? [], fn (array $l) => $l['incluida'] ?? true));
    }

    /** Las que se quitaron, para poder decir por qué el mes sale más barato. */
    public function lineasQuitadas(): array
    {
        return array_values(array_filter($this->detalle ?? [], fn (array $l) => ! ($l['incluida'] ?? true)));
    }

    public function estaVencida(): bool
    {
        return $this->valido_hasta->isBefore(Carbon::today());
    }
}
