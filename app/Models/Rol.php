<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property array<array-key, mixed>|null $permisos Array de permisos: ["crear_cliente", "editar_precio", etc.]
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $usuarios
 * @property-read int|null $usuarios_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol wherePermisos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'permisos',
        'activo',
    ];

    protected $casts = [
        'permisos' => 'json',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}
