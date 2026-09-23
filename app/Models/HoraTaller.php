<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Una clase que se hizo: qué día, cuántas horas y de qué taller. */
class HoraTaller extends Model
{
    protected $table = 'horas_taller';

    protected $fillable = ['uuid', 'id_taller', 'fecha', 'horas', 'detalle', 'id_cobro', 'id_usuario'];

    protected $casts = ['fecha' => 'date', 'horas' => 'float'];

    protected static function booted(): void
    {
        static::creating(fn (self $hora) => $hora->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'id_taller');
    }

    /** Ya cobrada: tocarla movería una factura que ya salió. */
    public function estaCobrada(): bool
    {
        return $this->id_cobro !== null;
    }
}
