<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Un contrato mandado a firmar por correo, y lo que pasó con él.
 *
 * El enlace del correo NO se guarda, solo su huella (token_hash): con el
 * enlace cualquiera podría firmar por el socio, y una base de datos se copia,
 * se respalda y la mira más de una persona.
 *
 * Firmado, guarda el documento TAL COMO SE FIRMÓ —con la firma dibujada
 * dentro— y la huella de ese documento. Si alguien le cambiara una coma
 * después, la huella dejaría de cuadrar: es lo que lo hace valer como
 * constancia.
 */
class Contrato extends Model
{
    protected $table = 'contratos';

    protected $fillable = [
        'id_cliente', 'id_inscripcion', 'token_hash', 'firmante_tipo', 'email_destino',
        'enviado_en', 'vence_en', 'error_envio', 'id_usuario',
        'firmado_en', 'firmante_nombre', 'firmante_rut', 'ip', 'navegador',
        'version_contrato', 'version_terminos', 'version_privacidad',
        'consentimiento_imagen', 'consentimiento_difusion',
        'contenido', 'huella', 'anulado_en', 'datos_borrados_en',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'enviado_en' => 'datetime',
        'vence_en' => 'datetime',
        'firmado_en' => 'datetime',
        'anulado_en' => 'datetime',
        'datos_borrados_en' => 'datetime',
        'consentimiento_imagen' => 'boolean',
        'consentimiento_difusion' => 'boolean',
        'version_contrato' => 'integer',
        'version_terminos' => 'integer',
        'version_privacidad' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Contrato $contrato) {
            if (empty($contrato->uuid)) {
                $contrato->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function cliente()
    {
        // Con los de la papelera: el contrato de un socio dado de baja sigue
        // siendo suyo.
        return $this->belongsTo(Cliente::class, 'id_cliente')->withTrashed();
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /** firmado, pendiente, vencido, anulado, fallido (no salió el correo) o borrado. */
    public function estado(): string
    {
        return match (true) {
            $this->datos_borrados_en !== null => 'borrado',
            $this->firmado_en !== null => 'firmado',
            $this->anulado_en !== null => $this->error_envio ? 'fallido' : 'anulado',
            $this->vence_en === null || $this->vence_en->isPast() => 'vencido',
            default => 'pendiente',
        };
    }

    public function sePuedeFirmar(): bool
    {
        return $this->estado() === 'pendiente';
    }

    /** ¿El documento guardado es el mismo que se firmó? */
    public function integro(): bool
    {
        return $this->contenido !== null
            && $this->huella !== null
            && hash_equals($this->huella, hash('sha256', $this->contenido));
    }
}
