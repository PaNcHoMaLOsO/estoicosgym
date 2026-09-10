<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pone el rojo y el negro de MARCA en las plantillas de correo.
 *
 * Las trece plantillas guardadas usaban #E0001A y #101010, y el panel —y las
 * plantillas nuevas de recuperacion y contacto— usan los colores sacados del
 * logotipo: #d81f26 y #0a0a0b. Eran dos rojos distintos en correos del mismo
 * gimnasio, y se nota al abrir un aviso y entrar al panel a continuacion.
 *
 * SOLO se tocan esos dos. Los colores que SIGNIFICAN algo —el verde de
 * «pagado», el ambar de «por vencer»— se quedan como estan: fueron elegidos
 * para leerse en un cliente de correo, que no es lo mismo que una pantalla, y
 * cambiarlos por los del panel podria dejarlos ilegibles sobre fondo claro.
 */
return new class extends Migration
{
    /** viejo => nuevo */
    private const COLORES = [
        '#E0001A' => '#d81f26',
        '#e0001a' => '#d81f26',
        '#101010' => '#0a0a0b',
    ];

    public function up(): void
    {
        $this->reemplazar(self::COLORES);
    }

    public function down(): void
    {
        // El negro no se devuelve: #0a0a0b tambien podria venir de otro sitio y
        // revertirlo a ciegas pintaria de #101010 algo que nunca lo fue.
        $this->reemplazar(['#d81f26' => '#E0001A']);
    }

    private function reemplazar(array $mapa): void
    {
        $plantillas = DB::table('tipo_notificaciones')
            ->select('id', 'plantilla_email', 'asunto_email')
            ->get();

        foreach ($plantillas as $plantilla) {
            $cuerpo = (string) $plantilla->plantilla_email;
            $nuevo = str_replace(array_keys($mapa), array_values($mapa), $cuerpo);

            if ($nuevo === $cuerpo) {
                continue;
            }

            DB::table('tipo_notificaciones')
                ->where('id', $plantilla->id)
                ->update(['plantilla_email' => $nuevo, 'updated_at' => now()]);
        }
    }
};
