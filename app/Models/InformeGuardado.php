<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Un informe del constructor, guardado para volver a pedirlo.
 *
 * Guarda la RECETA y no el resultado: qué columnas, qué filtros y en qué orden.
 * Al abrirlo se vuelve a consultar, así que trae los datos de hoy; guardar las
 * filas sería una foto que envejece sola en un cajón.
 */
class InformeGuardado extends Model
{
    protected $table = 'informes_guardados';

    protected $fillable = ['uuid', 'nombre', 'modulo', 'configuracion', 'id_usuario'];

    protected $casts = ['configuracion' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (self $informe) {
            $informe->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
