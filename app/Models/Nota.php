<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Una cosa que hay que hacer, apuntada en el mesón.
 *
 * El bloc es COMPARTIDO: en el mesón se turnan varias personas y lo que deja
 * escrito la de la mañana tiene que verlo la de la tarde.
 */
class Nota extends Model
{
    protected $table = 'notas';

    protected $fillable = [
        'texto',
        'hecha',
        'hecha_en',
        'id_usuario',
        'id_usuario_hecha',
    ];

    protected $casts = [
        'hecha' => 'boolean',
        'hecha_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $nota) {
            $nota->uuid ??= (string) Str::uuid();
        });
    }

    /** Se busca por uuid en las rutas, no por id. */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function quienLaHizo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_hecha');
    }

    /**
     * Lo que se enseña en el mesón: lo pendiente, y lo tachado hoy.
     *
     * Las pendientes van TODAS aunque sean de anteayer —una tarea sin hacer no
     * caduca sola—, pero las hechas solo las de hoy: si no, el bloc se convierte
     * en un archivo histórico y deja de servir para mirar de un vistazo.
     */
    public function scopeDelDia(Builder $consulta): Builder
    {
        return $consulta->where(function (Builder $q) {
            $q->where('hecha', false)
                ->orWhere('hecha_en', '>=', today());
        });
    }
}
