<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Una máquina o un movimiento de los que hay en la sala.
 *
 * Se escribe una vez y lo usan todas las rutinas: corregir una indicación
 * corrige la de todas, que es justo lo que no pasaría con el texto copiado en
 * cada rutina.
 */
class Ejercicio extends Model
{
    protected $table = 'ejercicios';

    /** Qué trabaja. La lista sale de cómo la gente busca en la sala. */
    public const ZONAS = [
        'pecho' => 'Pecho',
        'espalda' => 'Espalda',
        'piernas' => 'Piernas',
        'hombros' => 'Hombros',
        'brazos' => 'Brazos',
        'core' => 'Abdomen y zona media',
        'cardio' => 'Cardio',
        'cuerpo_completo' => 'Cuerpo completo',
    ];

    /** Con qué se hace. */
    public const EQUIPOS = [
        'maquina' => 'Máquina',
        'mancuernas' => 'Mancuernas',
        'barra' => 'Barra',
        'propio_peso' => 'Con su propio peso',
        'cardio' => 'Equipo de cardio',
    ];

    /**
     * Los grupos del mapa muscular (columna `musculos`: el principal y los
     * secundarios). Mientras el ejercicio no tenga foto, la rutina muestra la
     * silueta con estos grupos marcados. Ver <x-mapa-muscular>.
     */
    public const MUSCULOS = [
        'pecho' => 'Pecho',
        'espalda' => 'Espalda',
        'hombros' => 'Hombros',
        'biceps' => 'Bíceps',
        'triceps' => 'Tríceps',
        'abdomen' => 'Abdomen',
        'lumbar' => 'Lumbar',
        'gluteos' => 'Glúteos',
        'cuadriceps' => 'Cuádriceps',
        'isquios' => 'Isquiotibiales',
        'pantorrillas' => 'Pantorrillas',
    ];

    protected $fillable = ['nombre', 'zona', 'musculos', 'imagen', 'equipo', 'indicacion', 'activo', 'orden'];

    protected $casts = ['activo' => 'boolean', 'orden' => 'integer', 'musculos' => 'array'];

    /** La foto o el GIF que subió el gimnasio, o null. */
    public function urlDeImagen(): ?string
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
    }

    /** @return array{principal: ?string, secundarios: list<string>} */
    public function grupos(): array
    {
        $m = $this->musculos ?? [];

        return ['principal' => $m['principal'] ?? null, 'secundarios' => array_values($m['secundarios'] ?? [])];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $e) => $e->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
