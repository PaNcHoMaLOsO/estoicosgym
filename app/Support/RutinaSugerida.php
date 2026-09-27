<?php

namespace App\Support;

use App\Models\Rutina;

/**
 * Qué rutina enseñarle a quien leyó el QR de la sala.
 *
 * Responde tres cosas —qué busca, cuánto lleva entrenando y cuántos días puede
 * venir— y de ahí sale una de las rutinas que el gimnasio tiene cargadas.
 *
 * SIEMPRE SALE ALGUNA mientras haya rutinas. Quien contestó y recibe «no hay
 * nada para ti» queda tan perdido como antes de escanear: si no hay una exacta
 * se afloja primero el nivel, después los días, y la pantalla dice de qué es la
 * que le tocó.
 */
class RutinaSugerida
{
    /** Los días por semana que alguna rutina cargada ofrece. */
    public static function diasPosibles(): array
    {
        $dias = Rutina::where('activa', true)
            ->distinct()
            ->orderBy('dias_por_semana')
            ->pluck('dias_por_semana')
            ->map(fn ($d) => (int) $d)
            ->all();

        return $dias ?: [2, 3, 4];
    }

    public static function buscar(string $objetivo, string $nivel, int $dias): ?Rutina
    {
        $con = fn (array $filtros) => Rutina::where('activa', true)
            ->where($filtros)
            ->with(['dias.ejercicios.ejercicio', 'dias.ejercicios.alternativa'])
            ->orderBy('orden')
            ->first();

        return $con(['objetivo' => $objetivo, 'nivel' => $nivel, 'dias_por_semana' => $dias])
            ?? $con(['objetivo' => $objetivo, 'dias_por_semana' => $dias])
            ?? $con(['objetivo' => $objetivo, 'nivel' => $nivel])
            ?? $con(['objetivo' => $objetivo])
            ?? $con(['nivel' => $nivel, 'dias_por_semana' => $dias])
            ?? Rutina::where('activa', true)->with(['dias.ejercicios.ejercicio', 'dias.ejercicios.alternativa'])->orderBy('orden')->first();
    }

    /**
     * Las otras rutinas del mismo objetivo: la misma idea con más o menos
     * días, o para otro nivel. Quien puede venir un día más, o ya se le quedó
     * corta la suya, pasa a la otra desde abajo de la rutina.
     *
     * @return list<array{nombre:string, nivel:string, dias:int, url:string}>
     */
    public static function variantes(Rutina $actual): array
    {
        return Rutina::where('activa', true)
            ->where('objetivo', $actual->objetivo)
            ->whereKeyNot($actual->getKey())
            ->orderBy('dias_por_semana')
            ->orderBy('orden')
            ->get()
            ->map(fn (Rutina $r) => [
                'nombre' => $r->nombre,
                'nivel' => Rutina::NIVELES[$r->nivel] ?? $r->nivel,
                'dias' => $r->dias_por_semana,
                'url' => route('landing.rutina', ['objetivo' => $r->objetivo, 'nivel' => $r->nivel, 'dias' => $r->dias_por_semana]),
            ])
            ->all();
    }

    /** Si lo que respondió tiene sentido: si no, se le vuelven a hacer las preguntas. */
    public static function respondido(string $objetivo, string $nivel, int $dias): bool
    {
        return isset(Rutina::OBJETIVOS[$objetivo])
            && isset(Rutina::NIVELES[$nivel])
            && $dias >= 2
            && $dias <= 6;
    }
}
