<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\TextoLegal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * El contrato, los términos y condiciones y la política de privacidad.
 *
 * Los escribe el gimnasio en Configuración, con {variables} que se rellenan
 * con sus datos —y, en el contrato, con los del socio y su plan—. El formato
 * es Markdown, lo más simple que hay: ## para un título, **negrita**, un guion
 * al principio de cada punto de una lista.
 *
 * VERSIONES: lo que alguien ya firmó no se toca. Si se edita un texto que
 * nadie ha firmado, se corrige ahí mismo; si ya lo firmó alguien, lo nuevo se
 * guarda como la versión siguiente y la anterior queda como estaba, porque es
 * lo que esa persona aceptó.
 */
class TextosLegales
{
    public const TIPOS = [
        'contrato' => [
            'titulo' => 'Contrato',
            'descripcion' => 'Lo que firma cada socio: por correo en su celular, o impreso en el mesón.',
        ],
        'terminos' => [
            'titulo' => 'Términos y condiciones',
            'descripcion' => 'Las reglas del gimnasio. Se publican en la página web y se aceptan al firmar el contrato.',
        ],
        'privacidad' => [
            'titulo' => 'Política de privacidad',
            'descripcion' => 'Qué datos se guardan, para qué y cómo pedir que se borren. La ley pide tenerla publicada.',
        ],
    ];

    /** Lo que se puede escribir entre llaves en los tres textos. */
    public const VARIABLES_DEL_GIMNASIO = [
        'gimnasio' => 'Nombre del gimnasio',
        'direccion_gimnasio' => 'Su dirección',
        'email_gimnasio' => 'Su correo de contacto',
        'telefono_gimnasio' => 'Su teléfono',
        'sitio' => 'La dirección de la página web',
    ];

    /** Y además, en el contrato: los datos del socio y de su plan. */
    public const VARIABLES_DEL_CONTRATO = [
        'socio' => 'Nombre completo del socio',
        'rut_socio' => 'Su RUT o pasaporte',
        'email_socio' => 'Su correo',
        'celular_socio' => 'Su celular',
        'firmante' => 'Quién firma: el socio, o su apoderado si es menor',
        'rut_firmante' => 'El RUT de quien firma',
        'plan' => 'El plan contratado',
        'precio' => 'Lo que paga por el plan',
        'inicio' => 'Desde cuándo corre',
        'vencimiento' => 'Cuándo vence',
        'pausas' => 'Cuántas pausas incluye',
        'fecha' => 'La fecha de la firma',
        'version' => 'El número de versión del contrato',
    ];

    /**
     * Lo que sale en un texto cuando falta un dato del gimnasio.
     *
     * Se ve a propósito —en el contrato y en la web— para que alguien vaya a
     * completarlo; un hueco en blanco pasaría sin que nadie lo note.
     */
    public const POR_COMPLETAR = '(por completar)';

    /** Qué columna de un contrato firmado guarda la versión de cada texto. */
    private const COLUMNA_EN_EL_CONTRATO = [
        'contrato' => 'version_contrato',
        'terminos' => 'version_terminos',
        'privacidad' => 'version_privacidad',
    ];

    /** @return array<string,string> */
    public static function variables(string $tipo): array
    {
        self::exigir($tipo);

        return $tipo === 'contrato'
            ? self::VARIABLES_DEL_GIMNASIO + self::VARIABLES_DEL_CONTRATO
            : self::VARIABLES_DEL_GIMNASIO;
    }

    /** La versión que rige hoy. */
    public static function vigente(string $tipo): TextoLegal
    {
        self::exigir($tipo);

        return TextoLegal::where('tipo', $tipo)->orderByDesc('version')->first()
            // La primera vez, el texto base: así hay algo que publicar y que
            // firmar desde el primer día, aunque nadie lo haya escrito todavía.
            ?? TextoLegal::createOrFirst(
                ['tipo' => $tipo, 'version' => 1],
                ['contenido' => self::predeterminado($tipo)]
            );
    }

    /** El texto base que trae el sistema, en resources/legal. */
    public static function predeterminado(string $tipo): string
    {
        self::exigir($tipo);

        return (string) file_get_contents(resource_path("legal/{$tipo}.md"));
    }

    /** ¿Nadie del gimnasio lo ha revisado todavía? */
    public static function esElTextoBase(TextoLegal $texto): bool
    {
        return $texto->id_usuario === null;
    }

    /**
     * Cuántas personas firmaron esta versión.
     *
     * Del contrato cuentan también las firmas en papel que se anotaron en la
     * ficha con su número de versión.
     */
    public static function firmas(TextoLegal $texto): int
    {
        $socios = Contrato::whereNotNull('firmado_en')
            ->where(self::COLUMNA_EN_EL_CONTRATO[$texto->tipo], $texto->version)
            ->pluck('id_cliente');

        if ($texto->tipo === 'contrato') {
            $socios = $socios->merge(
                Cliente::withTrashed()->where('contrato_version', (string) $texto->version)->pluck('id')
            );
        }

        return $socios->unique()->count();
    }

    /**
     * Guarda lo escrito en el panel.
     *
     * @return array{0: TextoLegal, 1: string} el texto y cómo quedó: «nueva»
     *     (se creó la versión siguiente), «misma» (se corrigió sin cambiar de
     *     versión, porque nadie la había firmado) o «igual» (no había cambios)
     */
    public static function guardar(string $tipo, string $contenido, int $idUsuario): array
    {
        $contenido = str_replace("\r\n", "\n", $contenido);

        return DB::transaction(function () use ($tipo, $contenido, $idUsuario) {
            $actual = self::vigente($tipo);

            if (trim($contenido) === trim(str_replace("\r\n", "\n", $actual->contenido))) {
                // Sin cambios, pero alguien lo leyó y lo dio por bueno: deja de
                // ser «el texto base sin revisar».
                $actual->update(['id_usuario' => $actual->id_usuario ?? $idUsuario]);

                return [$actual, 'igual'];
            }

            if (self::firmas($actual) === 0) {
                $actual->update(['contenido' => $contenido, 'id_usuario' => $idUsuario]);

                return [$actual, 'misma'];
            }

            $nueva = TextoLegal::create([
                'tipo' => $tipo,
                'version' => $actual->version + 1,
                'contenido' => $contenido,
                'id_usuario' => $idUsuario,
            ]);

            return [$nueva, 'nueva'];
        });
    }

    /**
     * Las {variables} que usa el texto y el sistema no sabe rellenar.
     *
     * @return list<string>
     */
    public static function variablesDesconocidas(string $tipo, string $texto): array
    {
        preg_match_all('/\{([a-z_]+)\}/i', $texto, $encontradas);

        return array_values(array_diff(
            array_unique($encontradas[1]),
            array_keys(self::variables($tipo))
        ));
    }

    /**
     * El Markdown, con sus variables rellenas, convertido en HTML seguro.
     *
     * @param array<string,string> $variables
     */
    public static function html(string $markdown, array $variables = []): string
    {
        $reemplazos = [];

        foreach ($variables as $clave => $valor) {
            $reemplazos['{' . $clave . '}'] = self::literal((string) $valor);
        }

        return Str::markdown(strtr($markdown, $reemplazos), [
            // El HTML que alguien escriba se quita: el texto lo edita quien
            // tenga acceso a Configuración y se publica en la web.
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Los datos del gimnasio, para rellenar los tres textos.
     *
     * @return array<string,string>
     */
    public static function datosDelGimnasio(): array
    {
        return [
            'gimnasio' => Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM',
            'direccion_gimnasio' => Ajustes::obtener('gimnasio.direccion') ?: self::POR_COMPLETAR,
            'email_gimnasio' => Ajustes::obtener('gimnasio.email') ?: self::POR_COMPLETAR,
            'telefono_gimnasio' => Ajustes::obtener('gimnasio.telefono') ?: self::POR_COMPLETAR,
            'sitio' => rtrim(url('/'), '/'),
        ];
    }

    /**
     * Un socio inventado, para ver cómo queda el contrato antes de guardarlo.
     *
     * @return array<string,string>
     */
    public static function ejemploDelContrato(): array
    {
        $hoy = now();

        return [
            'socio' => 'Camila Rojas Soto',
            'rut_socio' => '12.345.678-5',
            'email_socio' => 'camila@correo.cl',
            'celular_socio' => '912345678',
            'firmante' => 'Camila Rojas Soto',
            'rut_firmante' => '12.345.678-5',
            'plan' => 'Mensual',
            'precio' => '$25.000',
            'inicio' => $hoy->format('d/m/Y'),
            'vencimiento' => $hoy->copy()->addMonth()->format('d/m/Y'),
            'pausas' => '1 pausa',
            'fecha' => $hoy->format('d/m/Y'),
            'version' => (string) self::vigente('contrato')->version,
        ];
    }

    /**
     * Lo que se publica en la web: el texto vigente, ya rellenado.
     *
     * @return array{html:string, version:int, fecha:?string}
     */
    public static function publicado(string $tipo): array
    {
        $texto = self::vigente($tipo);

        return [
            'html' => self::sinTituloPrincipal(self::html($texto->contenido, self::datosDelGimnasio())),
            'version' => $texto->version,
            'fecha' => $texto->updated_at?->format('d/m/Y'),
        ];
    }

    /**
     * El HTML sin su título principal, para ponerlo en una página que ya
     * tiene el suyo.
     *
     * Dos títulos principales en una página le enredan a Google de qué trata.
     * El primero se quita —la cabecera de la página ya lo dice— y cualquier
     * otro que alguien escriba con un solo # baja un nivel.
     */
    public static function sinTituloPrincipal(string $html): string
    {
        $html = (string) preg_replace('#^\s*<h1>.*?</h1>\s*#s', '', $html, 1);

        return str_replace(['<h1>', '</h1>'], ['<h2>', '</h2>'], $html);
    }

    /**
     * Un dato para que se lea tal cual.
     *
     * Un apellido con un asterisco o un guion bajo no puede poner negrita a
     * medio contrato, ni un «<» colar algo en la página. Los saltos de línea
     * de una dirección tampoco pueden partir un párrafo en dos.
     */
    private static function literal(string $valor): string
    {
        return addcslashes((string) preg_replace('/\s+/u', ' ', trim($valor)), '\\`*_[]<>#+!|~');
    }

    private static function exigir(string $tipo): void
    {
        if (! isset(self::TIPOS[$tipo])) {
            throw new InvalidArgumentException("No hay un texto legal «{$tipo}».");
        }
    }
}
