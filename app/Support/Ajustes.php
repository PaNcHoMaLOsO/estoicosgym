<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Los ajustes del gimnasio, con sus valores por defecto.
 *
 * LO QUE ESTABA ESCRITO A MANO Y AHORA SE PUEDE CAMBIAR: el nombre del gimnasio
 * —que iba dentro de las plantillas de correo—, «se puede renovar 30 días
 * antes», «una nota lleva 3 días sin hacerse», «un fiado lleva 14 días sin
 * cobrarse» y el tope del envío masivo. Ninguno de los cinco lo podía tocar
 * nadie del gimnasio sin abrir el código.
 *
 * SE LEEN MUCHÍSIMO —el nombre del gimnasio sale en cada correo— así que van a
 * caché. Se olvida entera al guardar cualquiera: son diez filas, y mantener una
 * entrada por clave sería más código del que ahorra.
 */
class Ajustes
{
    private const CACHE = 'ajustes:todos';

    /**
     * Qué se puede ajustar, qué significa y qué vale si nadie lo tocó.
     *
     * Los valores por defecto son los que estaban escritos a mano en el código,
     * para que estrenar esto no cambie el comportamiento de nada.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function definiciones(): array
    {
        return [
            // ---- Quién es el gimnasio ----
            'gimnasio.nombre' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Nombre',
                'ayuda' => 'Sale en los correos que reciben los socios.',
                'tipo' => 'texto',
                'defecto' => 'PRO GYM',
            ],
            'gimnasio.direccion' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Dirección',
                'ayuda' => 'Para los correos y los comprobantes.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.telefono' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Teléfono',
                'ayuda' => 'El que se le da al socio para llamar.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.email' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Correo de contacto',
                'ayuda' => 'A dónde responde el socio si contesta un aviso.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.horario' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Horario',
                'ayuda' => 'Tal cual se quiere que se lea. Ej: «Lun a Vie 7:00–22:00 · Sáb 9:00–14:00».',
                'tipo' => 'texto',
                'defecto' => '',
            ],

            // ---- Cómo funciona el gimnasio ----
            'reglas.dias_para_renovar' => [
                'grupo' => 'reglas',
                'etiqueta' => 'Renovar como mucho antes de',
                'ayuda' => 'Días antes del vencimiento en que ya se puede renovar. Antes de eso, renovar cortaría la membresía en curso y el socio perdería los días que le quedan.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 120,
                'defecto' => 30,
                'unidad' => 'días',
            ],
            'reglas.dias_aviso_vencimiento' => [
                'grupo' => 'reglas',
                'etiqueta' => 'Avisar del vencimiento',
                'ayuda' => 'Con cuántos días de antelación se le manda el aviso al socio.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 60,
                'defecto' => 7,
                'unidad' => 'días antes',
            ],

            // ---- El mesón ----
            'meson.dias_nota_vieja' => [
                'grupo' => 'meson',
                'etiqueta' => 'Marcar una nota sin hacer',
                'ayuda' => 'A los cuántos días una nota pendiente se marca para que alguien decida. Las notas NO se borran solas.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 30,
                'defecto' => 3,
                'unidad' => 'días',
            ],
            'meson.dias_fiado_viejo' => [
                'grupo' => 'meson',
                'etiqueta' => 'Marcar un fiado sin cobrar',
                'ayuda' => 'A los cuántos días una cuenta del mesón sale marcada. Una cuenta vieja no se cobra sola.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 90,
                'defecto' => 14,
                'unidad' => 'días',
            ],

            // ---- Correo ----
            'correo.tope_masivo' => [
                'grupo' => 'correo',
                'etiqueta' => 'Máximo por envío a un grupo',
                'ayuda' => 'Los correos salen uno a uno dentro de la petición: pasado cierto número el servidor corta a mitad de la lista y nadie sabe a quién le llegó. Súbelo solo si el servidor aguanta.',
                'tipo' => 'numero',
                'min' => 10,
                'max' => 500,
                'defecto' => 150,
                'unidad' => 'socios',
            ],
        ];
    }

    /** Cómo se llama cada grupo en la pantalla. */
    public static function grupos(): array
    {
        return [
            'gimnasio' => ['titulo' => 'El gimnasio', 'descripcion' => 'Lo que ve el socio en los correos'],
            'reglas' => ['titulo' => 'Reglas', 'descripcion' => 'Cómo se comportan las membresías'],
            'meson' => ['titulo' => 'Mesón', 'descripcion' => 'Las notas y lo fiado'],
            'correo' => ['titulo' => 'Correo', 'descripcion' => 'Límites de los envíos'],
        ];
    }

    /** El valor de un ajuste, o su defecto si nadie lo tocó. */
    public static function obtener(string $clave): mixed
    {
        $definicion = self::definiciones()[$clave] ?? null;

        if (! $definicion) {
            return null;
        }

        $guardado = self::todos()[$clave] ?? null;

        if ($guardado === null || $guardado === '') {
            return $definicion['defecto'];
        }

        return $definicion['tipo'] === 'numero' ? (int) $guardado : $guardado;
    }

    /** Atajo para los que son números: siempre devuelve un entero usable. */
    public static function numero(string $clave): int
    {
        return (int) self::obtener($clave);
    }

    /**
     * Guarda unos cuantos de golpe.
     *
     * Lo que no esté en las definiciones SE DESCARTA: los valores llegan de un
     * formulario, y una clave inventada acabaría en la tabla ocupando sitio y
     * sin que nada la lea nunca.
     *
     * @param array<string,mixed> $valores
     */
    public static function guardar(array $valores): void
    {
        $conocidos = self::definiciones();

        foreach ($valores as $clave => $valor) {
            if (! isset($conocidos[$clave])) {
                continue;
            }

            DB::table('ajustes')->updateOrInsert(
                ['clave' => $clave],
                ['valor' => (string) $valor, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Cache::forget(self::CACHE);
    }

    /**
     * Todo lo guardado, de una vez.
     *
     * @return array<string,string>
     */
    private static function todos(): array
    {
        return Cache::rememberForever(
            self::CACHE,
            fn () => DB::table('ajustes')->pluck('valor', 'clave')->all()
        );
    }

    /** Para las pruebas y para después de guardar. */
    public static function olvidar(): void
    {
        Cache::forget(self::CACHE);
    }
}
