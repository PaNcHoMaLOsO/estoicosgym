<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNotificacion extends Model
{
    use HasFactory;

    protected $table = 'tipo_notificaciones';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'asunto_email',
        'plantilla_email',
        'dias_anticipacion',
        'activo',
        'enviar_email',
    ];

    protected $casts = [
        'dias_anticipacion' => 'integer',
        'activo' => 'boolean',
        'enviar_email' => 'boolean',
    ];

    // Códigos de tipos de notificación
    const MEMBRESIA_POR_VENCER = 'membresia_por_vencer';
    const MEMBRESIA_VENCIDA = 'membresia_vencida';
    const PAGO_PENDIENTE = 'pago_pendiente';
    const BIENVENIDA = 'bienvenida';
    const RENOVACION_EXITOSA = 'renovacion_exitosa';

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_tipo_notificacion');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Renderiza la plantilla con los datos proporcionados
     */
    public function renderizar(array $datos): array
    {
        $asunto = $this->asunto_email;
        $contenido = $this->plantilla_email;

        foreach ($datos as $key => $value) {
            $asunto = str_replace('{' . $key . '}', $value, $asunto);
            $contenido = str_replace('{' . $key . '}', $value, $contenido);
        }

        return [
            'asunto' => $asunto,
            'contenido' => $contenido,
        ];
    }
}
