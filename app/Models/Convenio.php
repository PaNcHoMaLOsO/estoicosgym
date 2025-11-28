<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $nombre Ej: INACAP, Cruz Verde, Falabella
 * @property string $tipo Tipo de convenio
 * @property string|null $descripcion
 * @property string|null $contacto_nombre
 * @property string|null $contacto_telefono
 * @property string|null $contacto_email
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cliente> $clientes
 * @property-read int|null $clientes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereContactoEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereContactoNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereContactoTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Convenio extends Model
{
    use HasFactory;
    
    protected $table = 'convenios';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'nombre',
        'tipo',
        'descripcion',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'activo',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_convenio');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
    }
}
