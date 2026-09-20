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

    protected $fillable = ['nombre', 'zona', 'equipo', 'indicacion', 'activo', 'orden'];

    protected $casts = ['activo' => 'boolean', 'orden' => 'integer'];

    protected static function booted(): void
    {
        static::creating(fn (self $e) => $e->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
