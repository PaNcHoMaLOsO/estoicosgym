<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $run_pasaporte NULL para indocumentados
 * @property string $nombres
 * @property string $apellido_paterno
 * @property string|null $apellido_materno
 * @property string $celular
 * @property string|null $email
 * @property string|null $direccion
 * @property \Illuminate\Support\Carbon|null $fecha_nacimiento
 * @property string|null $contacto_emergencia
 * @property string|null $telefono_emergencia
 * @property int|null $id_convenio Convenio asociado al cliente
 * @property string|null $observaciones
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Convenio|null $convenio
 * @property-read mixed $nombre_completo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notificacion> $notificaciones
 * @property-read int|null $notificaciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereApellidoMaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereApellidoPaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereContactoEmergencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereFechaNacimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereIdConvenio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereNombres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereRunPasaporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefonoEmergencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'run_pasaporte',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'celular',
        'email',
        'direccion',
        'fecha_nacimiento',
        'contacto_emergencia',
        'telefono_emergencia',
        'id_convenio',
        'observaciones',
        'activo',
    ];

    protected $dates = [
        'fecha_nacimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
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

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'id_convenio');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_cliente');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_cliente');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_cliente');
    }

    public function getNombreCompletoAttribute()
    {
        $nombre = $this->nombres . ' ' . $this->apellido_paterno;
        if ($this->apellido_materno) {
            $nombre .= ' ' . $this->apellido_materno;
        }
        return $nombre;
    }

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }
}
