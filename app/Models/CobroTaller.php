<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Lo que se le cobra a una institución por un mes de taller.
 *
 * GUARDA LA CUENTA HECHA, no la forma de rehacerla: las horas, el precio que
 * tenían ese mes, el neto y el IVA. Si mañana sube la hora, la factura de julio
 * tiene que seguir diciendo lo que decía.
 */
class CobroTaller extends Model
{
    protected $table = 'cobros_taller';

    protected $fillable = [
        'uuid', 'id_taller', 'periodo', 'horas', 'precio_hora',
        'total', 'neto', 'iva', 'folio', 'emitido_en', 'pagado_en',
        'observaciones', 'id_usuario',
    ];

    protected $casts = [
        'horas' => 'float',
        'precio_hora' => 'integer',
        'total' => 'integer',
        'neto' => 'integer',
        'iva' => 'integer',
        'emitido_en' => 'date',
        'pagado_en' => 'date',
    ];

    /** El IVA chileno. Vive aquí para que la cuenta esté en un solo sitio. */
    public const IVA = 0.19;

    protected static function booted(): void
    {
        static::creating(fn (self $cobro) => $cobro->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'id_taller');
    }

    public function horas(): HasMany
    {
        return $this->hasMany(HoraTaller::class, 'id_cobro');
    }

    /**
     * El neto y el IVA de un total que ya trae el IVA dentro.
     *
     * SE PARTE DEL TOTAL y no al revés. Lo acordado es «la hora sale $30.000»,
     * y veinte horas son $600.000 exactos; calculando el neto primero y
     * sumándole el IVA, el total se iría uno o dos pesos por el redondeo y la
     * factura no cuadraría con lo que se dijo. Así sale igual que la factura
     * del SII: 20 × 30.000 = 600.000, neto 504.202, IVA 95.798.
     *
     * @return array{neto:int, iva:int}
     */
    public static function desglosar(int $total): array
    {
        $neto = (int) round($total / (1 + self::IVA));

        return ['neto' => $neto, 'iva' => $total - $neto];
    }
}
