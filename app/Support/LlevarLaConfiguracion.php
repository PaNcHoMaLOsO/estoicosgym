<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Llevar la configuración de un equipo a otro: del PC del dueño al servidor.
 *
 * EL PROBLEMA: todo lo que el dueño armó en Configuración —planes y precios,
 * convenios con sus logos, plantillas de correo, la página web con sus fotos,
 * especialistas, rutinas, textos legales, ajustes— vive en la base de SU
 * equipo. Volver a escribirlo a mano en el servidor es una tarde perdida y una
 * fuente segura de diferencias.
 *
 * QUÉ VIAJA Y QUÉ NO: solo lo que es configuración, tabla por tabla y columna
 * por columna, en una lista cerrada. Una tabla nueva de socios que aparezca
 * mañana no se cuela en el archivo porque nadie la agrega aquí. No viajan los
 * socios ni nada que cuelgue de ellos, ni los usuarios del panel, ni la cuenta
 * de correo con su contraseña (el zip se copia con un pendrive o por correo y
 * no debe llevar una llave que abra la cuenta del gimnasio), ni el contacto
 * personal de cada convenio.
 *
 * CÓMO SE RECONOCE CADA FILA EN EL OTRO LADO: los ids no sirven —el servidor
 * numeró sus filas en otro orden—, así que se busca por uuid y, si no aparece,
 * por la clave natural (el nombre del plan, el código de la plantilla). Lo
 * segundo importa: el servidor ya tiene «Mensual» sembrado con OTRO uuid, y
 * buscar solo por uuid crearía un segundo «Mensual» que la base rechaza. Las
 * claves foráneas viajan como la referencia del padre (su uuid) y se traducen
 * al id que ese padre tenga en el servidor.
 */
class LlevarLaConfiguracion
{
    /** Versión del formato del zip: si cambia, el importador lo sabrá decir. */
    public const FORMATO = 1;

    /** Lo que tienen al principio las filas de prueba que no deben viajar. */
    private const PREFIJO_DE_PRUEBA = 'PRUEBA ·';

    /**
     * Las tablas que viajan, en el orden en que se importan: primero los
     * padres, para que al llegar a un hijo su padre ya tenga id en el servidor.
     *
     *  · columnas: las que viajan. Ni id ni fechas: el id es del equipo y las
     *    fechas de creación se ponen al escribir.
     *  · identidad: con qué columnas se busca la fila en el servidor, en orden;
     *    la primera que encuentre gana.
     *  · padres: columna => tabla de la que es clave foránea.
     *  · referencia: cómo la nombran sus hijos en el archivo.
     *  · imagenes: columnas que apuntan a un archivo del disco public.
     *  · papelera: la tabla borra suave; en el archivo va solo lo vigente.
     */
    private static function tablas(): array
    {
        return [
            'metodos_pago' => [
                'columnas' => ['nombre', 'descripcion', 'requiere_comprobante', 'activo'],
                'identidad' => [['nombre']],
                'papelera' => true,
            ],
            'motivos_descuento' => [
                'columnas' => ['nombre', 'descripcion', 'activo'],
                'identidad' => [['nombre']],
                'papelera' => true,
            ],
            'membresias' => [
                'columnas' => ['uuid', 'nombre', 'duracion_meses', 'duracion_dias', 'dias_regalo', 'max_pausas', 'descripcion', 'activo', 'en_la_web'],
                'identidad' => [['uuid'], ['nombre']],
                'referencia' => ['uuid'],
                'papelera' => true,
            ],
            /*
             * Del convenio NO viajan contacto_nombre, contacto_telefono ni
             * contacto_email: son datos de una persona, no de la configuración.
             * Como no van en el archivo, el importador tampoco los pisa: el
             * servidor conserva los que tenga.
             *
             * id_estado apunta al CÓDIGO del estado (100, 200…), que es igual
             * en todas las bases porque lo siembra el mismo seeder: viaja tal
             * cual.
             */
            'convenios' => [
                'columnas' => ['uuid', 'nombre', 'tipo', 'descuento_porcentaje', 'descuento_monto', 'descripcion', 'id_estado', 'activo', 'logo', 'mostrar_en_web', 'requisito_web', 'canje'],
                'identidad' => [['uuid'], ['nombre']],
                'referencia' => ['uuid'],
                'imagenes' => ['logo'],
                'papelera' => true,
            ],
            'convenio_precios' => [
                'columnas' => ['id_convenio', 'id_membresia', 'precio', 'condicion'],
                'identidad' => [['id_convenio', 'id_membresia']],
                'padres' => ['id_convenio' => 'convenios', 'id_membresia' => 'membresias'],
            ],
            // Las plantillas de correo: el código es lo que busca el sistema
            // («bienvenida», «vencimiento»), así que es su identidad.
            'tipo_notificaciones' => [
                'columnas' => ['codigo', 'nombre', 'descripcion', 'asunto_email', 'plantilla_email', 'dias_anticipacion', 'activo', 'enviar_email', 'es_manual'],
                'identidad' => [['codigo']],
            ],
            // Las migraciones siembran textos de la web con uuid al azar: en el
            // servidor son otros, así que se reconocen también por tipo y título.
            'contenidos_web' => [
                'columnas' => ['uuid', 'tipo', 'titulo', 'texto', 'icono', 'imagen', 'con_permiso', 'orden', 'activo'],
                'identidad' => [['uuid'], ['tipo', 'titulo']],
                'imagenes' => ['imagen'],
            ],
            // Especialistas y embajadores: su WhatsApp, Instagram y correo SÍ
            // viajan porque son su ficha pública en la web, no datos de socios.
            'especialistas' => [
                'columnas' => ['uuid', 'tipo', 'nombre', 'slug', 'slugs_anteriores', 'especialidad', 'descripcion', 'temas', 'modalidad', 'foto', 'whatsapp', 'instagram', 'email', 'orden', 'activo'],
                'identidad' => [['uuid'], ['slug'], ['tipo', 'nombre']],
                'imagenes' => ['foto'],
            ],
            'clases' => [
                'columnas' => ['uuid', 'nombre', 'descripcion', 'profesor', 'para_quien', 'precio_mensual', 'imagen', 'fotos', 'horario', 'color', 'activo', 'orden'],
                'identidad' => [['uuid'], ['nombre']],
                'imagenes' => ['imagen'],
                'galerias' => ['fotos'],
            ],
            'ejercicios' => [
                'columnas' => ['uuid', 'nombre', 'zona', 'equipo', 'indicacion', 'activo', 'orden'],
                'identidad' => [['uuid'], ['nombre']],
                'referencia' => ['uuid'],
            ],
            'rutinas' => [
                'columnas' => ['uuid', 'nombre', 'objetivo', 'nivel', 'dias_por_semana', 'descripcion', 'activa', 'orden'],
                'identidad' => [['uuid'], ['nombre']],
                'referencia' => ['uuid'],
            ],
            'rutina_dias' => [
                'columnas' => ['id_rutina', 'numero', 'titulo', 'foco'],
                'identidad' => [['id_rutina', 'numero']],
                'padres' => ['id_rutina' => 'rutinas'],
                'referencia' => ['id_rutina', 'numero'],
            ],
            'rutina_ejercicios' => [
                'columnas' => ['id_dia', 'orden', 'id_ejercicio', 'id_alternativa', 'series', 'repeticiones', 'descanso_seg', 'nota'],
                'identidad' => [['id_dia', 'orden']],
                'padres' => ['id_dia' => 'rutina_dias', 'id_ejercicio' => 'ejercicios', 'id_alternativa' => 'ejercicios'],
            ],
        ];
    }

    /**
     * Los ajustes que viajan: los que el sistema conoce, menos la cuenta de
     * correo entera y cualquier secreto.
     *
     * Lista blanca y no negra: una clave vieja que quedó en la tabla sin que
     * nadie la lea no se arrastra al servidor, y un secreto nuevo que se
     * agregue a Ajustes queda fuera solo por ser de tipo «secreto».
     *
     * @return list<string>
     */
    public static function ajustesQueViajan(): array
    {
        return array_keys(array_filter(
            Ajustes::definiciones(),
            fn (array $definicion) => ($definicion['grupo'] ?? null) !== 'correo'
                && ($definicion['tipo'] ?? null) !== 'secreto'
        ));
    }

    /* ======================= EXPORTAR ======================= */

    /**
     * Arma el zip y devuelve cuántas filas y fotos lleva.
     *
     * @return array{tablas: array<string,int>, imagenes: int, faltantes: list<string>}
     */
    public static function exportar(string $destino): array
    {
        $datos = ['ajustes' => self::exportarAjustes()];
        $referencias = [];
        $imagenes = [];

        foreach (self::tablas() as $tabla => $definicion) {
            if (! Schema::hasTable($tabla)) {
                continue;
            }

            $datos[$tabla] = self::exportarTabla($tabla, $definicion, $referencias, $imagenes);

            // El precio va justo después del plan que lo tiene.
            if ($tabla === 'membresias') {
                $datos['precios_membresias'] = self::exportarPrecios($referencias['membresias'] ?? []);
            }
        }

        $datos['textos_legales'] = self::exportarTextosLegales();

        if (! is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0775, true);
        }

        $zip = new \ZipArchive;

        if ($zip->open($destino, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("No se pudo crear el archivo {$destino}.");
        }

        $publico = Storage::disk('public');
        $faltantes = [];
        $guardadas = 0;

        foreach (array_keys($imagenes) as $ruta) {
            if ($publico->exists($ruta)) {
                $zip->addFromString("imagenes/{$ruta}", $publico->get($ruta));
                $guardadas++;
            } else {
                // La fila apunta a una foto que ya no está: se avisa, y la
                // fila viaja igual —en el servidor se verá como en el PC—.
                $faltantes[] = $ruta;
            }
        }

        $resumen = array_map('count', $datos);

        foreach ($datos as $tabla => $filas) {
            $zip->addFromString(
                "tablas/{$tabla}.json",
                json_encode($filas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        $zip->addFromString('configuracion.json', json_encode([
            'formato' => self::FORMATO,
            'creado' => now()->toIso8601String(),
            'desde' => config('app.url'),
            'tablas' => $resumen,
            'imagenes' => $guardadas,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $zip->close();

        return ['tablas' => $resumen, 'imagenes' => $guardadas, 'faltantes' => $faltantes];
    }

    /** @return list<array{clave:string, valor:mixed}> */
    private static function exportarAjustes(): array
    {
        return DB::table('ajustes')
            ->whereIn('clave', self::ajustesQueViajan())
            ->orderBy('clave')
            ->get(['clave', 'valor'])
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    /**
     * Las filas de una tabla, con sus claves foráneas ya cambiadas por la
     * referencia del padre.
     *
     * @param array<string,array<int,string>> $referencias id del equipo => referencia, por tabla (se va llenando)
     * @param array<string,true> $imagenes las rutas de fotos que hay que meter al zip (se va llenando)
     */
    private static function exportarTabla(string $tabla, array $definicion, array &$referencias, array &$imagenes): array
    {
        $existentes = Schema::getColumnListing($tabla);
        $columnas = array_values(array_intersect($definicion['columnas'], $existentes));

        $consulta = DB::table($tabla)->orderBy('id');

        if (($definicion['papelera'] ?? false) && in_array('deleted_at', $existentes, true)) {
            $consulta->whereNull('deleted_at');
        }

        $filas = [];

        foreach ($consulta->get() as $fila) {
            $fila = (array) $fila;

            if (self::esDePrueba($fila)) {
                continue;
            }

            $salida = array_intersect_key($fila, array_flip($columnas));

            // Un hijo cuyo padre no viaja (porque era de prueba) tampoco viaja:
            // en el servidor no tendría a quién apuntar.
            foreach ($definicion['padres'] ?? [] as $columna => $padre) {
                if ($salida[$columna] === null) {
                    continue;
                }

                $referencia = $referencias[$padre][$salida[$columna]] ?? null;

                if ($referencia === null) {
                    continue 2;
                }

                $salida[$columna] = $referencia;
            }

            // Las galerías: una lista de rutas en JSON. Viajan las que se
            // pueden llevar; las de prueba y las raras se quedan.
            foreach ($definicion['galerias'] ?? [] as $columna) {
                $rutas = json_decode((string) ($salida[$columna] ?? ''), true);
                $rutas = array_values(array_filter(is_array($rutas) ? $rutas : [], fn ($r) => is_string($r)
                    && ! str_starts_with(basename($r), 'prueba-') && self::rutaSegura($r)));

                foreach ($rutas as $r) {
                    $imagenes[$r] = true;
                }

                $salida[$columna] = $rutas ? json_encode($rutas) : null;
            }

            foreach ($definicion['imagenes'] ?? [] as $columna) {
                $ruta = $salida[$columna] ?? null;

                if (! $ruta) {
                    continue;
                }

                // Las fotos de prueba se quedan, y la fila viaja sin foto antes
                // que apuntando a un archivo que en el servidor no existirá.
                if (str_starts_with(basename($ruta), 'prueba-') || ! self::rutaSegura($ruta)) {
                    $salida[$columna] = null;

                    continue;
                }

                $imagenes[$ruta] = true;
            }

            if (isset($definicion['referencia'])) {
                $referencias[$tabla][$fila['id']] = self::referenciaDe($definicion['referencia'], $salida);
            }

            $filas[] = $salida;
        }

        return $filas;
    }

    /**
     * El precio que rige hoy de cada plan, y solo ese.
     *
     * El historial de precios NO viaja: cuenta lo que se cobró en este equipo,
     * y en el servidor el historial es el suyo. Al importar, si el precio es
     * otro se cierra el vigente y se abre uno nuevo, igual que en la pantalla.
     *
     * @param array<int,string> $planes id del plan en este equipo => su referencia
     */
    private static function exportarPrecios(array $planes): array
    {
        $filas = [];

        foreach ($planes as $id => $referencia) {
            $vigente = DB::table('precios_membresias')
                ->where('id_membresia', $id)
                ->where('activo', true)
                ->orderByDesc('fecha_vigencia_desde')
                ->orderByDesc('id')
                ->first();

            if ($vigente) {
                $filas[] = [
                    'id_membresia' => $referencia,
                    'precio_normal' => $vigente->precio_normal,
                    'precio_convenio' => $vigente->precio_convenio,
                ];
            }
        }

        return $filas;
    }

    /**
     * La versión vigente de cada texto legal.
     *
     * Las anteriores no viajan: son lo que firmó alguien EN ESTE equipo, y en
     * el servidor lo firmado es otra cosa. «revisado» dice si el dueño ya lo
     * dio por bueno o si sigue siendo el texto base.
     */
    private static function exportarTextosLegales(): array
    {
        $filas = [];

        foreach (array_keys(TextosLegales::TIPOS) as $tipo) {
            $vigente = DB::table('textos_legales')->where('tipo', $tipo)->orderByDesc('version')->first();

            if ($vigente) {
                $filas[] = [
                    'tipo' => $tipo,
                    'contenido' => $vigente->contenido,
                    'revisado' => $vigente->id_usuario !== null,
                ];
            }
        }

        return $filas;
    }

    /** ¿Es una fila de prueba que se cargó para ver cómo quedaba la web? */
    private static function esDePrueba(array $fila): bool
    {
        foreach (['nombre', 'titulo'] as $columna) {
            if (isset($fila[$columna]) && str_starts_with(trim((string) $fila[$columna]), self::PREFIJO_DE_PRUEBA)) {
                return true;
            }
        }

        return isset($fila['profesor']) && str_contains(mb_strtolower((string) $fila['profesor']), '(prueba)');
    }

    /**
     * Una ruta del disco public que se puede leer y escribir sin salirse de él
     * ni meterse en las fotos de los socios.
     */
    private static function rutaSegura(string $ruta): bool
    {
        return ! str_contains($ruta, '..')
            && ! str_starts_with($ruta, '/')
            && ! str_contains($ruta, '\\')
            && ! str_starts_with($ruta, 'clientes/');
    }

    private static function referenciaDe(array $columnas, array $fila): string
    {
        return implode('#', array_map(fn ($c) => (string) $fila[$c], $columnas));
    }

    /* ======================= IMPORTAR ======================= */

    /**
     * Lee el zip y lo deja en memoria: las tablas y las fotos.
     *
     * @return array{tablas: array<string,list<array>>, imagenes: array<string,string>, manifiesto: array}
     */
    public static function leer(string $archivo): array
    {
        $zip = new \ZipArchive;

        if (! is_file($archivo) || $zip->open($archivo) !== true) {
            throw new RuntimeException("No se pudo abrir {$archivo}: ¿es el zip que sacó configuracion:exportar?");
        }

        $manifiesto = json_decode((string) $zip->getFromName('configuracion.json'), true);

        if (! is_array($manifiesto) || ($manifiesto['formato'] ?? null) !== self::FORMATO) {
            $zip->close();

            throw new RuntimeException('El zip no es una configuración exportada, o la sacó una versión del sistema que esta no entiende.');
        }

        $tablas = [];
        $imagenes = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);

            if (preg_match('#^tablas/([a-z_]+)\.json$#', $nombre, $m)) {
                $tablas[$m[1]] = json_decode((string) $zip->getFromIndex($i), true) ?? [];
            } elseif (str_starts_with($nombre, 'imagenes/') && ! str_ends_with($nombre, '/')) {
                $ruta = substr($nombre, strlen('imagenes/'));

                // Un zip armado a mano podría traer «../../.env»: solo se
                // aceptan rutas que quedan dentro del disco public.
                if (self::rutaSegura($ruta)) {
                    $imagenes[$ruta] = (string) $zip->getFromIndex($i);
                }
            }
        }

        $zip->close();

        return ['tablas' => $tablas, 'imagenes' => $imagenes, 'manifiesto' => $manifiesto];
    }

    /**
     * Recorre el archivo contra la base y, si $escribir, aplica los cambios.
     *
     * Es UNA sola función para mirar y para escribir a propósito: si fueran
     * dos, el resumen de «qué cambiaría» podría decir una cosa y la
     * importación hacer otra.
     *
     * @return array{tablas: array<string,array{nuevas:int, actualiza:int, iguales:int, omitidas:int}>, imagenes: array{nuevas:int, ya_estaban:int}}
     */
    public static function importar(array $contenido, bool $escribir): array
    {
        $tablas = $contenido['tablas'];
        $resumen = [];
        // Referencia del archivo => id en el servidor, por tabla.
        $ids = [];

        $resumen['ajustes'] = self::importarAjustes($tablas['ajustes'] ?? [], $escribir);

        foreach (self::tablas() as $tabla => $definicion) {
            if (! Schema::hasTable($tabla) || ! isset($tablas[$tabla])) {
                continue;
            }

            $resumen[$tabla] = self::importarTabla($tabla, $definicion, $tablas[$tabla], $ids, $escribir);

            if ($tabla === 'membresias' && isset($tablas['precios_membresias'])) {
                $resumen['precios_membresias'] = self::importarPrecios($tablas['precios_membresias'], $ids['membresias'] ?? [], $escribir);
            }
        }

        if (isset($tablas['textos_legales'])) {
            $resumen['textos_legales'] = self::importarTextosLegales($tablas['textos_legales'], $escribir);
        }

        return ['tablas' => $resumen, 'imagenes' => self::importarImagenes($contenido['imagenes'], $escribir)];
    }

    private static function cuenta(): array
    {
        return ['nuevas' => 0, 'actualiza' => 0, 'iguales' => 0, 'omitidas' => 0];
    }

    private static function importarAjustes(array $filas, bool $escribir): array
    {
        $cuenta = self::cuenta();
        // Se vuelve a filtrar AQUÍ: un zip editado a mano no puede meter la
        // contraseña del correo ni una clave que el sistema no conoce.
        $permitidos = array_flip(self::ajustesQueViajan());
        $actuales = DB::table('ajustes')->pluck('valor', 'clave')->all();

        foreach ($filas as $fila) {
            $clave = $fila['clave'] ?? null;

            if (! is_string($clave) || ! isset($permitidos[$clave])) {
                $cuenta['omitidas']++;

                continue;
            }

            $valor = $fila['valor'] ?? null;

            if (array_key_exists($clave, $actuales)) {
                if (self::igual($actuales[$clave], $valor)) {
                    $cuenta['iguales']++;

                    continue;
                }

                $cuenta['actualiza']++;
            } else {
                $cuenta['nuevas']++;
            }

            if (! $escribir) {
                continue;
            }

            if (array_key_exists($clave, $actuales)) {
                DB::table('ajustes')->where('clave', $clave)->update(['valor' => $valor, 'updated_at' => now()]);
            } else {
                DB::table('ajustes')->insert(['clave' => $clave, 'valor' => $valor, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        return $cuenta;
    }

    /**
     * @param array<string,array<string,int>> $ids referencia del archivo => id en el servidor, por tabla
     */
    private static function importarTabla(string $tabla, array $definicion, array $filas, array &$ids, bool $escribir): array
    {
        $cuenta = self::cuenta();
        $columnasDelServidor = Schema::getColumns($tabla);
        $existentes = array_column($columnasDelServidor, 'name');
        // Las columnas booleanas: el zip de un SQLite trae 1 y 0, y
        // PostgreSQL no acepta un entero en una columna boolean.
        $booleanas = array_column(array_filter(
            $columnasDelServidor,
            fn ($c) => str_contains(strtolower((string) $c['type_name']), 'bool')
        ), 'name');
        $conPapelera = ($definicion['papelera'] ?? false) && in_array('deleted_at', $existentes, true);
        // Una fila del servidor no la reclaman dos del archivo.
        $tomadas = [];

        foreach ($filas as $original) {
            $fila = array_intersect_key($original, array_flip(array_intersect($definicion['columnas'], $existentes)));
            $referencia = isset($definicion['referencia']) ? self::referenciaDe($definicion['referencia'], $original) : null;

            // Las claves foráneas: de la referencia del archivo al id de aquí.
            // Si el padre todavía no existe —es nuevo y no se está
            // escribiendo— la fila también es nueva.
            $sinPadre = false;

            foreach ($definicion['padres'] ?? [] as $columna => $padre) {
                if (! isset($fila[$columna])) {
                    continue;
                }

                $id = $ids[$padre][(string) $fila[$columna]] ?? null;

                if ($id === null) {
                    $sinPadre = true;

                    break;
                }

                $fila[$columna] = $id;
            }

            if ($sinPadre) {
                $cuenta[$escribir ? 'omitidas' : 'nuevas']++;

                continue;
            }

            foreach ($booleanas as $columna) {
                if (array_key_exists($columna, $fila) && $fila[$columna] !== null) {
                    $fila[$columna] = (bool) $fila[$columna];
                }
            }

            [$actual, $porUuid] = self::buscar($tabla, $definicion['identidad'], $fila, $tomadas);

            if ($actual) {
                $tomadas[$actual->id] = true;

                // Encontrada por el nombre: se queda con su uuid, que es el que
                // ya usan las direcciones y los enlaces del servidor.
                $cambios = $porUuid ? $fila : array_diff_key($fila, ['uuid' => true]);
                $distinta = array_filter(
                    $cambios,
                    fn ($valor, $columna) => ! self::igual($actual->{$columna} ?? null, $valor),
                    ARRAY_FILTER_USE_BOTH
                );

                // Estaba en la papelera del servidor y en el PC sigue en uso:
                // vuelve, porque es lo que el dueño tiene configurado.
                if ($conPapelera && $actual->deleted_at !== null) {
                    $distinta['deleted_at'] = null;
                }

                if ($distinta === []) {
                    $cuenta['iguales']++;
                } else {
                    $cuenta['actualiza']++;

                    if ($escribir) {
                        DB::table($tabla)->where('id', $actual->id)->update($distinta + ['updated_at' => now()]);
                    }
                }

                $id = $actual->id;
            } else {
                $cuenta['nuevas']++;
                $id = $escribir
                    ? DB::table($tabla)->insertGetId($fila + ['created_at' => now(), 'updated_at' => now()])
                    : null;
            }

            if ($referencia !== null && $id !== null) {
                $ids[$tabla][$referencia] = $id;
            }
        }

        return $cuenta;
    }

    /**
     * La fila del servidor que corresponde, y si se la encontró por uuid.
     *
     * @return array{0: ?object, 1: bool}
     */
    private static function buscar(string $tabla, array $identidades, array $fila, array $tomadas): array
    {
        foreach ($identidades as $columnas) {
            $valores = array_intersect_key($fila, array_flip($columnas));

            // Sin todos los valores (un especialista sin slug) esa identidad no sirve.
            if (count($valores) !== count($columnas) || in_array(null, $valores, true)) {
                continue;
            }

            $encontrada = DB::table($tabla)
                ->where($valores)
                ->whereNotIn('id', array_keys($tomadas))
                ->orderBy('id')
                ->first();

            if ($encontrada) {
                return [$encontrada, $columnas === ['uuid']];
            }
        }

        return [null, false];
    }

    /**
     * El precio de cada plan, como lo haría la pantalla de Planes y precios.
     *
     * Los precios NO se pisan: si el del archivo es otro, se cierra el vigente
     * hoy y se abre uno nuevo. Un socio que pagó el precio viejo tiene que
     * seguir viéndolo en su historial.
     *
     * @param array<string,int> $planes referencia del plan => id en el servidor
     */
    private static function importarPrecios(array $filas, array $planes, bool $escribir): array
    {
        $cuenta = self::cuenta();

        foreach ($filas as $fila) {
            $idPlan = $planes[(string) ($fila['id_membresia'] ?? '')] ?? null;

            if ($idPlan === null) {
                // El plan es nuevo y no se está escribiendo: su precio también.
                $cuenta[$escribir ? 'omitidas' : 'nuevas']++;

                continue;
            }

            $vigente = DB::table('precios_membresias')
                ->where('id_membresia', $idPlan)
                ->where('activo', true)
                ->orderByDesc('fecha_vigencia_desde')
                ->orderByDesc('id')
                ->first();

            if ($vigente
                && self::igual($vigente->precio_normal, $fila['precio_normal'])
                && self::igual($vigente->precio_convenio, $fila['precio_convenio'] ?? null)) {
                $cuenta['iguales']++;

                continue;
            }

            $cuenta[$vigente ? 'actualiza' : 'nuevas']++;

            if (! $escribir) {
                continue;
            }

            if ($vigente) {
                DB::table('precios_membresias')->where('id', $vigente->id)->update([
                    'activo' => false,
                    'fecha_vigencia_hasta' => today()->format('Y-m-d'),
                    'updated_at' => now(),
                ]);
            }

            DB::table('precios_membresias')->insert([
                'id_membresia' => $idPlan,
                'precio_normal' => $fila['precio_normal'],
                'precio_convenio' => $fila['precio_convenio'] ?? null,
                'fecha_vigencia_desde' => today()->format('Y-m-d'),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $cuenta;
    }

    /**
     * Los textos legales, con la misma regla que TextosLegales::guardar: lo
     * que alguien ya firmó en el servidor no se toca.
     *
     * Si el texto es el mismo, nada. Si es otro y la versión vigente del
     * servidor no la firmó nadie, se corrige ahí mismo; si ya tiene firmas, el
     * texto del archivo entra como la versión siguiente.
     *
     * No se llama a guardar() porque escribe siempre y porque vigente() crea
     * la versión 1 si falta: mirar sin --confirmar no puede escribir nada.
     */
    private static function importarTextosLegales(array $filas, bool $escribir): array
    {
        $cuenta = self::cuenta();

        // Quién queda como el que lo revisó: el primer administrador del
        // servidor. Si no hay ninguno, queda sin nombre (= texto base).
        $revisor = DB::table('users')->where('id_rol', 1)->orderBy('id')->value('id');

        foreach ($filas as $fila) {
            $tipo = $fila['tipo'] ?? null;

            if (! isset(TextosLegales::TIPOS[$tipo])) {
                $cuenta['omitidas']++;

                continue;
            }

            $contenido = str_replace("\r\n", "\n", (string) ($fila['contenido'] ?? ''));
            $usuario = ($fila['revisado'] ?? false) ? $revisor : null;
            $actual = \App\Models\TextoLegal::where('tipo', $tipo)->orderByDesc('version')->first();

            if (! $actual) {
                $cuenta['nuevas']++;

                if ($escribir) {
                    \App\Models\TextoLegal::create(['tipo' => $tipo, 'version' => 1, 'contenido' => $contenido, 'id_usuario' => $usuario]);
                }

                continue;
            }

            if (trim($contenido) === trim(str_replace("\r\n", "\n", $actual->contenido))) {
                $cuenta['iguales']++;

                continue;
            }

            $cuenta['actualiza']++;

            if (! $escribir) {
                continue;
            }

            if (TextosLegales::firmas($actual) === 0) {
                $actual->update(['contenido' => $contenido, 'id_usuario' => $usuario ?? $actual->id_usuario]);
            } else {
                \App\Models\TextoLegal::create([
                    'tipo' => $tipo,
                    'version' => $actual->version + 1,
                    'contenido' => $contenido,
                    'id_usuario' => $usuario,
                ]);
            }
        }

        return $cuenta;
    }

    /**
     * Las fotos: se copian solo las que el servidor no tiene.
     *
     * Los nombres son al azar y cada foto subida lleva uno nuevo, así que un
     * archivo con el mismo nombre es la misma foto: pisarlo no ganaría nada.
     */
    private static function importarImagenes(array $imagenes, bool $escribir): array
    {
        $publico = Storage::disk('public');
        $cuenta = ['nuevas' => 0, 'ya_estaban' => 0];

        foreach ($imagenes as $ruta => $bytes) {
            if ($publico->exists($ruta)) {
                $cuenta['ya_estaban']++;

                continue;
            }

            $cuenta['nuevas']++;

            if ($escribir) {
                $publico->put($ruta, $bytes);
            }
        }

        return $cuenta;
    }

    /**
     * ¿Es el mismo valor, venga de PostgreSQL o de SQLite?
     *
     * La base devuelve «15000.00» o 15000, true o 1, según el motor y la
     * columna: compararlos tal cual daría «cambió» en filas idénticas.
     */
    private static function igual(mixed $a, mixed $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b;
        }

        if (is_bool($a) || is_bool($b)) {
            return (bool) $a === (bool) $b;
        }

        if (is_numeric($a) && is_numeric($b)) {
            return (float) $a === (float) $b;
        }

        return str_replace("\r\n", "\n", (string) $a) === str_replace("\r\n", "\n", (string) $b);
    }
}
