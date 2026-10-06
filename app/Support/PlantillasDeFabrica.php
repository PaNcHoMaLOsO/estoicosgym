<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Las trece plantillas de correo con las que viene el sistema.
 *
 * VIVEN EN EL REPOSITORIO (database/seeders/plantillas). Antes el seeder las
 * leía de storage/app/test_emails, que no se sube: en el servidor esa carpeta
 * no existía y las plantillas no se podían cargar.
 *
 * Y YA NO LLEVAN TEXTO DE MUESTRA. Las viejas decían «Juan Pérez»,
 * «Trimestral», «$$25.000» y el teléfono del gimnasio escritos a mano, y el
 * envío automático los cambiaba buscando ese texto. Las de ahora llevan
 * {variables}, las mismas que rellena el envío manual.
 *
 * ACTUALIZAR SIN PISAR LO EDITADO. Las que ya están en la base se cambian por
 * las nuevas SOLO si siguen idénticas a la versión vieja de fábrica. Cómo era
 * esa versión se guarda aquí como huella (sha256), porque los archivos viejos
 * no existen en el servidor. Si alguien la retocó en Configuración, se deja
 * como está y se avisa.
 */
class PlantillasDeFabrica
{
    /**
     * codigo => archivo, nombre, descripción, asunto nuevo, asunto viejo, días, manual, huella vieja.
     *
     * La huella es la del archivo viejo normalizado (ver normalizar()).
     */
    public const PLANTILLAS = [
        'bienvenida' => [
            'archivo' => '01_bienvenida.html',
            'nombre' => 'Bienvenida',
            'descripcion' => 'Email de bienvenida al inscribirse (incluye detalles de pago)',
            'asunto' => '🎉 Bienvenido/a {nombre} a {gimnasio}',
            'asunto_viejo' => '🎉 Bienvenido/a {nombre} a PROGYM - ¡Comienza tu transformación!',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => '581994165a1f2beef3b6f2ff7460094bf28237432dbac688be7fce19cd61272d',
        ],
        'pago_completado' => [
            'archivo' => '02_pago_completado.html',
            'nombre' => 'Pago Completado',
            'descripcion' => 'Confirmación cuando se completa el pago de la membresía',
            'asunto' => '✅ {nombre}, tu pago quedó registrado - {gimnasio}',
            'asunto_viejo' => '✅ {nombre}, tu pago ha sido registrado - PROGYM',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => 'bb484aeeec5b6e6f0fcaa9af212938d35c1d7c254c16929789eec29587d6dc4b',
        ],
        'membresia_por_vencer' => [
            'archivo' => '03_membresia_por_vencer.html',
            'nombre' => 'Membresía por Vencer',
            'descripcion' => 'Recordatorio X días antes del vencimiento (soporte apoderados)',
            'asunto' => '⏰ {nombre}, tu membresía {membresia} vence en {dias_restantes} días',
            'asunto_viejo' => '⏰ {nombre}, la membresía de {nombre_cliente} vence en {dias_restantes} días',
            'dias' => 5,
            'manual' => false,
            'huella_vieja' => 'a243c771c9b3bfe7f66dde4c8133014974ecb1c5a6392037c34101df8a269315',
        ],
        'membresia_vencida' => [
            'archivo' => '04_membresia_vencida.html',
            'nombre' => 'Membresía Vencida',
            'descripcion' => 'Notificación cuando la membresía ha vencido (soporte apoderados)',
            'asunto' => '❗ {nombre}, tu membresía {membresia} en {gimnasio} venció',
            'asunto_viejo' => '❗ {nombre}, la membresía de {nombre_cliente} en PROGYM ha vencido',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => 'ad72ed6e61c354db43fc2fd4d7c17143dc3e1e4a4dd974a47cd8d3299c85c52b',
        ],
        'pausa_inscripcion' => [
            'archivo' => '05_pausa_inscripcion.html',
            'nombre' => 'Pausa de Inscripción',
            'descripcion' => 'Confirmación cuando el cliente pausa su membresía',
            'asunto' => '⏸️ {nombre}, tu membresía en {gimnasio} quedó pausada',
            'asunto_viejo' => '⏸️ {nombre}, tu membresía en PROGYM ha sido pausada',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => '45bdb07d23f8dad16e6cf7e94431ae77c6f42bf0f200d95638745b5302bd709b',
        ],
        'activacion_inscripcion' => [
            'archivo' => '06_activacion_inscripcion.html',
            'nombre' => 'Activación de Inscripción',
            'descripcion' => 'Confirmación cuando se reactiva la membresía pausada',
            'asunto' => '▶️ {nombre}, ¡bienvenido/a de vuelta a {gimnasio}!',
            'asunto_viejo' => '▶️ {nombre}, ¡Bienvenido de vuelta a PROGYM!',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => '8d79f1436810196308628bd3111a6fb2daf68e01230fd68b0e60f14ff2606298',
        ],
        'pago_pendiente' => [
            'archivo' => '07_pago_pendiente.html',
            'nombre' => 'Pago Pendiente',
            'descripcion' => 'Recordatorio de saldo pendiente',
            'asunto' => '💳 {nombre}, tienes un saldo pendiente en {gimnasio}',
            'asunto_viejo' => '💳 {nombre}, tienes un saldo pendiente en PROGYM',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => 'dacb382acda3568e7a79a5643626554f046019f086af2fabe6c0e2ac4987e2e3',
        ],
        'renovacion' => [
            'archivo' => '08_renovacion.html',
            'nombre' => 'Renovación Exitosa',
            'descripcion' => 'Confirmación de renovación de membresía',
            'asunto' => '🎊 {nombre}, tu membresía en {gimnasio} quedó renovada',
            'asunto_viejo' => '🎊 {nombre}, tu membresía en PROGYM ha sido renovada',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => 'e6ea51cf16da6256010110fe1c5fe3c360676027eae4a349493c54dcba071cd5',
        ],
        'confirmacion_tutor_legal' => [
            'archivo' => '09_confirmacion_tutor_legal.html',
            'nombre' => 'Confirmación de Tutor Legal',
            'descripcion' => 'Constancia legal enviada al apoderado cuando inscribe a un menor',
            'asunto' => '📋 {nombre_apoderado}, confirmación de registro como tutor legal - {gimnasio}',
            'asunto_viejo' => '📋 {nombre_apoderado}, confirmación de registro como Tutor Legal - PROGYM',
            'dias' => 0,
            'manual' => false,
            'huella_vieja' => 'daf84073892c5455805f2cf6cfe8ac01a2575a593465daba76568d0a69ce0238',
        ],
        'horario_especial' => [
            'archivo' => '10_horario_especial.html',
            'nombre' => 'Horario Especial',
            'descripcion' => 'Plantilla manual para anunciar cambios en horarios',
            'asunto' => '📅 Horario especial - {gimnasio}',
            'asunto_viejo' => '📅 Horario Especial - PROGYM',
            'dias' => 0,
            'manual' => true,
            'huella_vieja' => '06c392f717039675438465dd5f32ed878c986a87f7af40ae480e5dacc77c6b40',
        ],
        'promocion' => [
            'archivo' => '11_promocion.html',
            'nombre' => 'Promoción Especial',
            'descripcion' => 'Plantilla manual para enviar promociones y ofertas',
            'asunto' => '🎁 Promoción especial - {gimnasio}',
            'asunto_viejo' => '🎁 Promoción Especial - PROGYM',
            'dias' => 0,
            'manual' => true,
            'huella_vieja' => '954fda51fd44dc64af959c83fa237c4aca8791a03b85a4780fc7999a5a603a6f',
        ],
        'anuncio' => [
            'archivo' => '12_anuncio.html',
            'nombre' => 'Anuncio Importante',
            'descripcion' => 'Plantilla manual para comunicados importantes',
            'asunto' => '📢 Anuncio importante - {gimnasio}',
            'asunto_viejo' => '📢 Anuncio Importante - PROGYM',
            'dias' => 0,
            'manual' => true,
            'huella_vieja' => '6631e6c77a2665441d8e66e08a1612e79968bef62480e6180ae6c1344abe524a',
        ],
        'evento' => [
            'archivo' => '13_evento.html',
            'nombre' => 'Evento Especial',
            'descripcion' => 'Plantilla manual para invitaciones a eventos',
            'asunto' => '🎉 No te pierdas nuestro evento - {gimnasio}',
            'asunto_viejo' => '🎉 No te pierdas nuestro evento - PROGYM',
            'dias' => 0,
            'manual' => true,
            'huella_vieja' => '7e3b301536e8c97ba699c75ebeaabde81e696834d2277b8f1b5bc2221105c357',
        ],
    ];

    /**
     * Texto de muestra de las plantillas viejas. Si una plantilla editada aún
     * lo lleva, el correo le llega al socio con un nombre o un monto inventado.
     */
    public const MUESTRAS = [
        'Juan Pérez', 'Juanito Pérez', 'María González', '$$25.000', '$$65.000',
        '06/03/2026', '06/12/2025', '25.555.666-7', '11.222.333-4', 'Viaje por trabajo',
        'progymlosangeles@gmail.com', '+56 9 5096 3143',
    ];

    /** El HTML nuevo de una plantilla, desde el repositorio. */
    public static function contenido(string $codigo): string
    {
        $ruta = database_path('seeders/plantillas/' . self::PLANTILLAS[$codigo]['archivo']);

        if (! is_file($ruta)) {
            throw new \RuntimeException("Falta la plantilla {$ruta}: va en el repositorio.");
        }

        return (string) file_get_contents($ruta);
    }

    /**
     * La forma de comparar dos versiones sin que cuenten los espacios, los
     * saltos de línea (CRLF o LF) ni los dos colores que la migración del
     * 10-sep-2026 cambió en todas las plantillas guardadas.
     */
    public static function normalizar(?string $html): string
    {
        $html = str_replace(['#E0001A', '#e0001a', '#101010'], ['#d81f26', '#d81f26', '#0a0a0b'], (string) $html);

        return trim((string) preg_replace('/\s+/u', ' ', $html));
    }

    public static function huella(?string $html): string
    {
        return hash('sha256', self::normalizar($html));
    }

    /**
     * Qué pasaría (o pasó) con cada plantilla.
     *
     * Sin `$aplicar` solo mira y no escribe nada. Se puede repetir: una que ya
     * está al día sale como «al día» y no se vuelve a escribir.
     *
     * @return list<array{codigo:string, nombre:string, resultado:string, asunto:string, muestras:list<string>}>
     *   resultado: creada | actualizada | al_dia | editada
     *   asunto: actualizado | al_dia | editado (solo si la fila ya existía)
     */
    public static function actualizar(bool $aplicar): array
    {
        $informe = [];

        foreach (self::PLANTILLAS as $codigo => $p) {
            $fila = DB::table('tipo_notificaciones')->where('codigo', $codigo)->first();
            $nuevo = self::contenido($codigo);

            if (! $fila) {
                if ($aplicar) {
                    DB::table('tipo_notificaciones')->insert([
                        'codigo' => $codigo,
                        'nombre' => self::nombreLibre($p['nombre'], $codigo),
                        'descripcion' => $p['descripcion'],
                        'asunto_email' => $p['asunto'],
                        'plantilla_email' => $nuevo,
                        'dias_anticipacion' => $p['dias'],
                        'activo' => true,
                        'enviar_email' => true,
                        'es_manual' => $p['manual'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $informe[] = ['codigo' => $codigo, 'nombre' => $p['nombre'], 'resultado' => 'creada', 'asunto' => 'actualizado', 'muestras' => []];

                continue;
            }

            $cambios = [];
            $huella = self::huella($fila->plantilla_email);

            if ($huella === self::huella($nuevo)) {
                $resultado = 'al_dia';
            } elseif ($huella === $p['huella_vieja']) {
                $resultado = 'actualizada';
                $cambios['plantilla_email'] = $nuevo;
            } else {
                $resultado = 'editada';
            }

            // El asunto va aparte: se pudo editar uno y no el otro.
            $asuntoActual = trim((string) $fila->asunto_email);

            if ($asuntoActual === $p['asunto']) {
                $asunto = 'al_dia';
            } elseif ($asuntoActual === $p['asunto_viejo']) {
                $asunto = 'actualizado';
                $cambios['asunto_email'] = $p['asunto'];
            } else {
                $asunto = 'editado';
            }

            if ($aplicar && $cambios !== []) {
                DB::table('tipo_notificaciones')->where('id', $fila->id)->update($cambios + ['updated_at' => now()]);
            }

            $informe[] = [
                'codigo' => $codigo,
                'nombre' => $fila->nombre,
                'resultado' => $resultado,
                'asunto' => $asunto,
                // Lo que se deja como está por editado, ¿sigue llevando texto
                // de muestra? Entonces hay que arreglarlo a mano.
                'muestras' => $resultado === 'editada'
                    ? array_values(array_filter(self::MUESTRAS, fn ($m) => str_contains($fila->asunto_email . ' ' . $fila->plantilla_email, $m)))
                    : [],
            ];
        }

        return $informe;
    }

    /** El nombre es único: si alguien ya usó «Bienvenida» para otra, se distingue. */
    private static function nombreLibre(string $nombre, string $codigo): string
    {
        return DB::table('tipo_notificaciones')->where('nombre', $nombre)->exists()
            ? "{$nombre} ({$codigo})"
            : $nombre;
    }
}
