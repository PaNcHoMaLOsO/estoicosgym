<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Alguien que entró por un convenio de canje, sin pagar.
 *
 * NO es un socio ni un pago: el huésped del hotel está de paso. Se guarda a
 * quién se dejó entrar, con qué tarjeta y quién lo anotó.
 */
class EntradaCanje extends Model
{
    protected $table = 'entradas_canje';

    protected $fillable = ['id_convenio', 'nombre', 'tarjeta', 'id_usuario'];

    protected static function booted(): void
    {
        static::creating(function (self $entrada) {
            $entrada->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class, 'id_convenio');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
