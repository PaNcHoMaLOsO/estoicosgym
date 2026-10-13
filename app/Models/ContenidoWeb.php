<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Un contenido de la web escrito desde el panel: un servicio, una foto, una
 * pregunta frecuente o un testimonio.
 */
class ContenidoWeb extends Model
{
    protected $table = 'contenidos_web';

    protected $fillable = [
        'uuid',
        'tipo',
        'titulo',
        'texto',
        'icono',
        'imagen',
        'enlace',
        'con_permiso',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'con_permiso' => 'boolean',
        'orden' => 'integer',
    ];

    /** Los cuatro tipos, cómo se llaman y dónde salen. */
    public const TIPOS = [
        'servicio' => [
            'titulo' => 'Servicios',
            'singular' => 'servicio',
            'descripcion' => 'Lo que se ofrece. Los tres primeros salen en Inicio y todos en El gimnasio.',
        ],
        'foto' => [
            'titulo' => 'Fotos',
            'singular' => 'foto',
            'descripcion' => 'La galería de El gimnasio. Se achican solas para que la página cargue rápido.',
        ],
        'pregunta' => [
            'titulo' => 'Preguntas frecuentes',
            'singular' => 'pregunta',
            'descripcion' => 'Salen en Contacto, y Google puede mostrarlas en sus resultados.',
        ],
        'testimonio' => [
            'titulo' => 'Testimonios',
            'singular' => 'testimonio',
            'descripcion' => 'Opiniones reales de socios, con su permiso. Salen en Inicio.',
        ],
        'instagram' => [
            'titulo' => 'Instagram',
            'singular' => 'publicación',
            'descripcion' => 'Lo último que se subió a Instagram. Salen en Inicio, la más nueva primero, y cada una abre su publicación.',
        ],
        'arriendo' => [
            'titulo' => 'Arriendo: fotos',
            'singular' => 'foto',
            'descripcion' => 'El collage de la página Arrienda horas: grupos entrenando y el espacio. La primera sale grande.',
        ],
        'institucion' => [
            'titulo' => 'Arriendo: logos',
            'singular' => 'institución',
            'descripcion' => 'Instituciones y clubes que ya hacen sus clases aquí. Salen en la página Arrienda horas: solo el nombre y el logo.',
        ],
    ];

    /** Cuántas publicaciones de Instagram se muestran en la portada. */
    public const PUBLICACIONES_EN_PORTADA = 12;

    /** Los íconos que se pueden elegir para un servicio (Font Awesome). */
    public const ICONOS = [
        'dumbbell' => 'Pesas',
        'heartbeat' => 'Corazón (cardio)',
        'user-check' => 'Persona con visto (orientación)',
        'running' => 'Persona corriendo',
        'bicycle' => 'Bicicleta',
        'fire' => 'Fuego (funcional)',
        'users' => 'Grupo (clases)',
        'apple-alt' => 'Manzana (nutrición)',
        'stopwatch' => 'Cronómetro',
        'medal' => 'Medalla',
        'child' => 'Niño',
        'hand-holding-heart' => 'Mano con corazón (bienestar)',
        'shower' => 'Ducha',
        'lock' => 'Candado (casilleros)',
        'parking' => 'Estacionamiento',
        'wifi' => 'Wifi',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContenidoWeb $contenido) {
            if (empty($contenido->uuid)) {
                $contenido->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * El enlace de una publicación, limpio; null si no lo es.
     *
     * «Copiar enlace» en el teléfono trae cola —?igsh=…, que identifica a quien
     * lo copió— y a veces el usuario por delante (/progym/p/ABC/). Se guarda
     * solo lo que abre la publicación: instagram.com/p/ABC/ o /reel/ABC/. Y
     * solo de Instagram: es un href de la web pública.
     */
    public static function enlaceDeInstagram(string $valor): ?string
    {
        if (! preg_match('#^(?:https?://)?(?:www\.|m\.)?instagram\.com/(?:[A-Za-z0-9._]+/)?(p|reel|reels|tv)/([A-Za-z0-9_-]+)#i', trim($valor), $partes)) {
            return null;
        }

        $clase = strtolower($partes[1]) === 'reels' ? 'reel' : strtolower($partes[1]);

        return "https://www.instagram.com/{$clase}/{$partes[2]}/";
    }

    /** Un reel se marca en su tarjeta: quien lo abre sabe que es un video. */
    public function esReel(): bool
    {
        return (bool) preg_match('#instagram\.com/(reel|reels|tv)/#', (string) $this->enlace);
    }

    public function urlDeImagen(): ?string
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
    }
}
