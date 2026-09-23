<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A quién se le factura un taller o el arriendo de la sala.
 *
 * NO ES UN SOCIO. Es un colegio, una empresa o un club: tiene RUT de empresa,
 * giro y alguien a quien escribirle, y lo que se le cobra son horas a fin de
 * mes, no una mensualidad. Mezclarlos con los socios llenaría el mesón de
 * fichas que nadie va a atender.
 */
class Institucion extends Model
{
    use SoftDeletes;

    protected $table = 'instituciones';

    protected $fillable = [
        'uuid', 'nombre', 'rut', 'giro', 'direccion', 'comuna',
        'contacto_nombre', 'contacto_email', 'contacto_telefono',
        'observaciones', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(fn (self $institucion) => $institucion->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function talleres(): HasMany
    {
        return $this->hasMany(Taller::class, 'id_institucion');
    }
}
