<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use HasFactory, SoftDeletes;
    
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
        'descuento_porcentaje',
        'descuento_monto',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'id_estado',
        'activo',
        // La pagina publica.
        'logo',
        'mostrar_en_web',
        'requisito_web',
        // Sus miembros entran sin pagar: se anotan en Entradas por canje.
        'canje',
    ];

    protected $casts = [
        'canje' => 'boolean',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'integer',
        'activo' => 'boolean',
        'mostrar_en_web' => 'boolean',
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

    /** La direccion publica del logo, o null si no tiene. */
    public function urlDeLogo(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_convenio');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
    }

    /** Los planes con precio propio de este convenio. */
    public function precios(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConvenioPrecio::class, 'id_convenio');
    }
}
