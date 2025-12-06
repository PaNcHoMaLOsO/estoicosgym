<?php

namespace App\Models;

use App\Enums\EstadosCodigo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use HasFactory, SoftDeletes;

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
        'id_estado',
        'observaciones',
        'activo',
        // Campos para menores de edad
        'es_menor_edad',
        'consentimiento_apoderado',
        'apoderado_nombre',
        'apoderado_rut',
        'apoderado_email',
        'apoderado_telefono',
        'apoderado_parentesco',
        'apoderado_observaciones',
    ];

    protected $dates = [
        'fecha_nacimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
        'es_menor_edad' => 'boolean',
        'consentimiento_apoderado' => 'boolean',
    ];

    // ===== MUTATORS PARA SANITIZACIÓN DE DATOS =====

    /**
     * Sanitizar y capitalizar nombres
     */
    public function setNombresAttribute($value)
    {
        // Eliminar espacios extras y capitalizar cada palabra
        $this->attributes['nombres'] = $value 
            ? ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($value)), 'UTF-8')) 
            : null;
    }

    /**
     * Sanitizar y capitalizar apellido paterno
     */
    public function setApellidoPaternoAttribute($value)
    {
        $this->attributes['apellido_paterno'] = $value 
            ? ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($value)), 'UTF-8')) 
            : null;
    }

    /**
     * Sanitizar y capitalizar apellido materno
     */
    public function setApellidoMaternoAttribute($value)
    {
        $this->attributes['apellido_materno'] = $value 
            ? ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($value)), 'UTF-8')) 
            : null;
    }

    /**
     * Normalizar email a minúsculas
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    /**
     * Limpiar y normalizar celular (formato chileno)
     */
    public function setCelularAttribute($value)
    {
        if (!$value) {
            $this->attributes['celular'] = null;
            return;
        }
        
        // Eliminar todo excepto dígitos
        $celular = preg_replace('/[^0-9]/', '', $value);
        
        // Si empieza con 56 (código Chile), quitarlo
        if (strlen($celular) === 11 && substr($celular, 0, 2) === '56') {
            $celular = substr($celular, 2);
        }
        
        // Guardar solo los 9 dígitos
        $this->attributes['celular'] = $celular;
    }

    /**
     * Sanitizar nombre de apoderado
     */
    public function setApoderadoNombreAttribute($value)
    {
        $this->attributes['apoderado_nombre'] = $value 
            ? ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($value)), 'UTF-8')) 
            : null;
    }

    /**
     * Sanitizar contacto de emergencia
     */
    public function setContactoEmergenciaAttribute($value)
    {
        $this->attributes['contacto_emergencia'] = $value 
            ? ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($value)), 'UTF-8')) 
            : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
            // Sincronizar id_estado con activo al crear
            if (empty($model->id_estado)) {
                $model->id_estado = $model->activo ? EstadosCodigo::CLIENTE_ACTIVO : EstadosCodigo::CLIENTE_CANCELADO;
            }
        });

        // Sincronizar id_estado cuando cambia activo
        static::updating(function ($model) {
            if ($model->isDirty('activo')) {
                $model->id_estado = $model->activo ? EstadosCodigo::CLIENTE_ACTIVO : EstadosCodigo::CLIENTE_CANCELADO;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
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

    // ===== MÉTODOS PARA MANEJO DE MENORES DE EDAD =====

    /**
     * Calcular la edad del cliente
     */
    public function getEdadAttribute(): ?int
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }
        return $this->fecha_nacimiento->age;
    }

    /**
     * Verificar si el cliente es menor de edad (< 18 años)
     */
    public function getEsMenorAttribute(): bool
    {
        $edad = $this->edad;
        return $edad !== null && $edad < 18;
    }

    /**
     * Verificar si tiene datos de apoderado completos
     */
    public function getTieneApoderadoCompletoAttribute(): bool
    {
        return $this->es_menor_edad 
            && $this->consentimiento_apoderado
            && !empty($this->apoderado_nombre)
            && !empty($this->apoderado_rut)
            && !empty($this->apoderado_telefono)
            && !empty($this->apoderado_parentesco);
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

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
    }
}
