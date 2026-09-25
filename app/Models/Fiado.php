<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Algo que alguien se llevó del mesón y todavía no ha pagado.
 *
 * NO es un pago de membresía. `Pago` mueve la caja del día, los informes de
 * ingresos y el saldo de cada socio; esto es la libreta del mesón y no toca
 * ninguna de esas tres cosas.
 */
class Fiado extends Model
{
    protected $table = 'fiados';

    protected $fillable = [
        'id_cliente',
        'nombre',
        'concepto',
        'monto',
        'pagado',
        'pagado_en',
        'id_usuario',
        'id_usuario_cobro',
        'id_metodo_pago',
    ];

    protected $casts = [
        'monto' => 'integer',
        'pagado' => 'boolean',
        'pagado_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $fiado) {
            $fiado->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /** Con qué se pagó. En blanco en lo cobrado antes de que se anotara. */
    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago')->withTrashed();
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function scopeDebiendo(Builder $consulta): Builder
    {
        return $consulta->where('pagado', false);
    }

    /**
     * A nombre de quién está.
     *
     * El del socio si lo es, y si no el que se escribió a mano. Se resuelve
     * aquí y no en la pantalla para que las dos formas se agrupen igual: si
     * cada pantalla lo hiciera a su manera, «Juan» y «Juan Pérez» acabarían
     * siendo dos deudores distintos.
     */
    public function aNombreDe(): string
    {
        if ($this->cliente) {
            return trim("{$this->cliente->nombres} {$this->cliente->apellido_paterno}");
        }

        return $this->nombre ?: 'Sin nombre';
    }

    /**
     * Con qué se agrupan las líneas de una misma persona.
     *
     * El socio manda: dos líneas del mismo socio son la misma cuenta aunque una
     * se apuntara con su nombre escrito a mano.
     */
    public function claveDeCuenta(): string
    {
        return $this->id_cliente
            ? 'socio:' . $this->id_cliente
            : 'nombre:' . mb_strtolower(trim((string) $this->nombre));
    }
}
