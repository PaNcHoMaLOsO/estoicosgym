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
        'vinculo',
        'nombre',
        'slug',
        'slugs_anteriores',
        'especialidad',
        'descripcion',
        'temas',
        'modalidad',
        'dias',
        'horario',
        'lugar',
        'foto',
        'whatsapp',
        'instagram',
        'email',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'temas' => 'array',
        'slugs_anteriores' => 'array',
        'dias' => 'array',
    ];

    /**
     * Si trabaja en el gimnasio o el gimnasio lo recomienda. La página es sobre
     * todo de recomendados: profesionales de la ciudad que no son del equipo,
     * y a Google no se le dice que trabajan aquí.
     */
    public const VINCULOS = [
        'recomendado' => 'Recomendado',
        'equipo' => 'Del gimnasio',
    ];

    /** Los días en que atiende, en orden de semana. */
    public const DIAS = [
        'lun' => 'Lunes',
        'mar' => 'Martes',
        'mie' => 'Miércoles',
        'jue' => 'Jueves',
        'vie' => 'Viernes',
        'sab' => 'Sábado',
        'dom' => 'Domingo',
    ];

    /** Cómo atiende. Sin elegir, el perfil no dice nada. */
    public const MODALIDADES = [
        'presencial' => 'Presencial',
        'online' => 'Online',
        'ambas' => 'Presencial y online',
    ];

    protected static function booted(): void
    {
        static::creating(function (Especialista $especialista) {
            if (empty($especialista->uuid)) {
                $especialista->uuid = (string) Str::uuid();
            }
        });

        // La dirección de su perfil sale del nombre. Se rehace si se corrige
        // el nombre: una dirección con el nombre mal escrito no sirve a nadie.
        static::saving(function (Especialista $especialista) {
            // El embajador no tiene página propia: sin dirección. Si la tuviera,
            // le quitaría el nombre a la misma persona como especialista
            // (salía «leonardo-gutierrez-2»).
            if ($especialista->tipo === 'embajador') {
                $especialista->slug = null;

                return;
            }

            if (! $especialista->slug || $especialista->isDirty('nombre')) {
                $anterior = $especialista->getOriginal('slug');
                $especialista->slug = $especialista->slugLibre();

                // La vieja queda guardada para redirigir: ya puede estar
                // compartida.
                if ($anterior && $anterior !== $especialista->slug) {
                    $especialista->slugs_anteriores = array_values(array_unique([
                        ...($especialista->slugs_anteriores ?? []),
                        $anterior,
                    ]));
                }
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /** «Camila Rojas» → «camila-rojas»; si ya existe, «camila-rojas-2». */
    private function slugLibre(): string
    {
        $base = Str::slug($this->nombre) ?: 'especialista';
        $slug = $base;

        for ($n = 2; static::where('slug', $slug)->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }

    /**
     * Los días como se leen: «Lunes a viernes», «Martes y jueves», «Lun, mié y
     * vie». Seguidos de tres o más se dicen como rango.
     */
    public function diasComoSeLeen(): ?string
    {
        $claves = array_keys(self::DIAS);
        $dias = array_values(array_filter($claves, fn (string $d) => in_array($d, $this->dias ?? [], true)));

        if ($dias === []) {
            return null;
        }

        if (count($dias) === 7) {
            return 'Todos los días';
        }

        $posiciones = array_map(fn (string $d) => array_search($d, $claves, true), $dias);
        $seguidos = end($posiciones) - $posiciones[0] === count($posiciones) - 1;

        if ($seguidos && count($dias) >= 3) {
            return self::DIAS[$dias[0]] . ' a ' . mb_strtolower(self::DIAS[end($dias)]);
        }

        $nombres = array_map(fn (string $d) => mb_strtolower(self::DIAS[$d]), $dias);
        $ultimo = array_pop($nombres);

        return \Illuminate\Support\Str::ucfirst($nombres ? implode(', ', $nombres) . ' y ' . $ultimo : $ultimo);
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
