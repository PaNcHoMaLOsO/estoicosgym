<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Un profesional que trabaja con el gimnasio y aparece en la web.
 *
 * El WhatsApp y el Instagram se guardan en su forma mínima —dígitos y usuario—
 * y los enlaces se arman AQUÍ. Así nunca se guarda un enlace escrito a mano:
 * lo que termina en un href de la página pública lo construye el sistema.
 */
class Especialista extends Model
{
    protected $table = 'especialistas';

    /**
     * Qué es cada persona, y con eso dónde sale en la web: el especialista en su
     * página, el embajador en la portada.
     */
    public const TIPOS = [
        'especialista' => 'Especialista',
        'embajador' => 'Embajador',
    ];

    protected $fillable = [
        'uuid',
        'tipo',
        'nombre',
        'especialidad',
        'descripcion',
        'foto',
        'whatsapp',
        'instagram',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Especialista $especialista) {
            if (empty($especialista->uuid)) {
                $especialista->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function urlDeFoto(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    /** El enlace a su WhatsApp, con un saludo ya escrito para que no tengan que pensar qué decir. */
    public function enlaceWhatsapp(string $gimnasio): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $saludo = "Hola {$this->nombre}, te escribo desde la web de {$gimnasio}.";

        return 'https://wa.me/' . $this->whatsapp . '?text=' . rawurlencode($saludo);
    }

    public function enlaceInstagram(): ?string
    {
        return $this->instagram ? 'https://www.instagram.com/' . $this->instagram . '/' : null;
    }
}
