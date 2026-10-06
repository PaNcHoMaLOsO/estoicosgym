<?php

namespace App\Support;

/**
 * Las páginas de la web que se encienden y se apagan desde Configuración →
 * Páginas de la web.
 *
 * No todas salen al mismo tiempo: una sección se prepara (fotos, textos,
 * horarios) y se publica cuando está lista. Apagada, sale del menú, del pie,
 * de la portada y del mapa del sitio, y su dirección responde «no existe».
 * Quien entra al panel la sigue viendo, con una franja que avisa que está
 * apagada, para poder revisarla antes de encenderla.
 *
 * La portada, Contacto y los textos legales no se apagan: son por donde la
 * gente encuentra y escribe al gimnasio.
 */
class PaginasWeb
{
    /** clave => [nombre en el panel, lo que hay en ella, sus rutas] */
    public const PAGINAS = [
        'gimnasio' => ['El gimnasio', 'Las fotos y los servicios de la sala.', ['landing.gimnasio']],
        'planes' => ['Planes', 'Los planes con sus precios.', ['landing.planes']],
        'clases' => ['Clases', 'El horario de judo, lucha y las demás clases, y la página de cada una.', ['landing.clases', 'landing.clase']],
        'especialistas' => ['Especialistas', 'Los especialistas, su perfil y las páginas por especialidad.', ['landing.especialistas', 'landing.especialista', 'landing.especialidad']],
        'convenios' => ['Convenios', 'Los convenios con sus logos y precios.', ['landing.convenios']],
        'arriendo' => ['Arrienda horas', 'Para instituciones, clubes y entrenadores que quieren dar clases aquí.', ['landing.arriendo']],
        'rutinas' => ['Rutinas y ejercicios', '«Qué entrenar hoy», las rutinas de la semana y los ejercicios de la sala.', ['landing.rutina', 'landing.rutinas', 'landing.rutina.ver', 'landing.ejercicios']],
        'membresia' => ['Mi membresía', 'Donde el socio consulta con su RUT cuándo vence y si debe algo.', ['landing.membresia', 'landing.consultar-membresia']],
    ];

    public static function encendida(string $clave): bool
    {
        return ! isset(self::PAGINAS[$clave]) || Ajustes::activo("paginas.{$clave}");
    }

    /** A qué página pertenece una ruta (null: la portada, Contacto, los legales…). */
    public static function deLaRuta(string $ruta): ?string
    {
        foreach (self::PAGINAS as $clave => [, , $rutas]) {
            if (in_array($ruta, $rutas, true)) {
                return $clave;
            }
        }

        return null;
    }

    /** Si la ruta se ve: las que no son de ninguna página, siempre. */
    public static function rutaVisible(string $ruta): bool
    {
        $pagina = self::deLaRuta($ruta);

        return $pagina === null || self::encendida($pagina);
    }

    /**
     * Todas a la vez, para el menú y el pie.
     *
     * @return array<string,bool>
     */
    public static function estado(): array
    {
        return array_map(fn (string $clave) => self::encendida($clave), array_combine(array_keys(self::PAGINAS), array_keys(self::PAGINAS)));
    }
}
