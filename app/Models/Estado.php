<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $codigo Rango: 01-99 membresias, 101-108 pagos, 200-299 convenios, 300-399 clientes
 * @property string $nombre
 * @property string|null $descripcion
 * @property string $categoria
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $color Color Bootstrap: primary, success, danger, warning, info, secondary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereCategoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Estado extends Model
{
    protected $table = 'estados';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria',
        'color',
        'activo',
    ];

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_estado');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_estado');
    }

    /**
     * Obtener badge HTML seguro del estado
     * @return string
     */
    public function getBadgeAttribute(): string
    {
        $color = htmlspecialchars($this->color ?? '#6c757d', ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars($this->nombre ?? 'Desconocido', ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<span class="badge" style="background-color: %s;"><i class="fas fa-info-circle fa-fw"></i> %s</span>',
            $color,
            $nombre
        );
    }
}
