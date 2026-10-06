<?php

namespace App\Support;

/**
 * Todo lo que se le puede dar a un perfil, con nombre de persona.
 *
 * Los permisos son cadenas como «pagos.editar» y salen del nombre de cada ruta
 * (ver Permisos). Esa cadena no le dice nada al dueño: «pagos.editar» incluye
 * crear un taller y cerrarle el mes al colegio, que nadie adivinaría. Aquí cada
 * una lleva lo que de verdad abre, agrupada por la parte del gimnasio a la que
 * pertenece, para que la pantalla de perfiles se pueda leer sin programador.
 *
 * NO SE PUEDE QUEDAR ATRÁS. La prueba PerfilesTest recorre todas las rutas del
 * panel y exige que cada permiso que piden esté aquí: una ruta nueva con un
 * permiso nuevo no aparecería en la pantalla y nadie podría dárselo.
 *
 * El comodín «*» no está a propósito: es el Administrador, y no se reparte
 * desde una casilla.
 */
class CatalogoDePermisos
{
    /**
     * Las áreas en el orden en que se leen, con sus permisos.
     *
     * `necesita`: lo que tiene que estar encendido para que esto sirva. Ver el
     * módulo se agrega solo (abajo, en necesita()): editar un socio sin poder
     * abrir su ficha es un botón que no lleva a ninguna parte.
     *
     * `cuidado`: se pinta con aviso en la pantalla.
     */
    public const AREAS = [
        'socios' => [
            'titulo' => 'Socios y mesón',
            'descripcion' => 'Las fichas de los socios y lo suelto del mostrador: fiado, canje y notas del día.',
            'permisos' => [
                'clientes.ver' => [
                    'etiqueta' => 'Ver socios y el mesón',
                    'explicacion' => 'El resumen, la lista de socios con sus fichas, el fiado, el canje y las notas.',
                ],
                'clientes.crear' => [
                    'etiqueta' => 'Dar de alta',
                    'explicacion' => 'Socios nuevos, y anotar un fiado, una entrada por canje o una nota.',
                ],
                'clientes.editar' => [
                    'etiqueta' => 'Cambiar fichas y cobrar el fiado',
                    'explicacion' => 'Corregir datos y foto, anotar el contrato, desactivar o reactivar, cobrar lo fiado.',
                ],
                'clientes.eliminar' => [
                    'etiqueta' => 'Borrar socios',
                    'explicacion' => 'Mandar a la papelera, juntar fichas repetidas, borrar datos personales, fiados y notas.',
                ],
            ],
        ],
        'membresias' => [
            'titulo' => 'Membresías',
            'descripcion' => 'Los planes que ya se vendieron a cada socio.',
            'permisos' => [
                'inscripciones.ver' => [
                    'etiqueta' => 'Ver membresías',
                    'explicacion' => 'La lista y la ficha de cada membresía.',
                ],
                'inscripciones.crear' => [
                    'etiqueta' => 'Inscribir',
                    'explicacion' => 'Venderle un plan a un socio.',
                ],
                'inscripciones.gestionar' => [
                    'etiqueta' => 'Pausar, renovar y cambiar de plan',
                    'explicacion' => 'Lo del día a día con el socio delante: pausar, reanudar, renovar, cambiar de plan y traspasar.',
                ],
                'inscripciones.editar' => [
                    'etiqueta' => 'Corregir una membresía',
                    'explicacion' => 'Cambiarle fechas o precio a una membresía ya vendida.',
                ],
                'inscripciones.eliminar' => [
                    'etiqueta' => 'Cancelar o borrar membresías',
                    'explicacion' => 'Deja al socio sin el plan que pagó. No se deshace.',
                ],
            ],
        ],
        'pagos' => [
            'titulo' => 'Pagos y talleres',
            'descripcion' => 'Los cobros de membresías y lo que se le factura a colegios y empresas.',
            'permisos' => [
                'pagos.ver' => [
                    'etiqueta' => 'Ver pagos y talleres',
                    'explicacion' => 'La lista de pagos con su comprobante, los talleres y sus cotizaciones.',
                ],
                'pagos.crear' => [
                    'etiqueta' => 'Cobrar',
                    'explicacion' => 'Registrar cobros, anotar las clases de un taller y cotizar el mes.',
                ],
                'pagos.corregir_hoy' => [
                    'etiqueta' => 'Corregir sus cobros de hoy',
                    'explicacion' => 'Arreglar un monto o un medio mal puesto, solo en pagos que registró esa persona y con fecha de hoy. No puede anularlos.',
                    'necesita' => ['pagos.crear'],
                ],
                'pagos.editar' => [
                    'etiqueta' => 'Corregir cualquier pago y manejar talleres',
                    'explicacion' => 'Cambiar pagos de cualquier día, crear talleres, cerrar el mes y marcar facturas pagadas.',
                ],
                'pagos.eliminar' => [
                    'etiqueta' => 'Anular pagos',
                    'explicacion' => 'Sacar un pago de la caja, y borrar talleres, clases anotadas y cotizaciones.',
                ],
            ],
        ],
        'caja' => [
            'titulo' => 'Caja e informes',
            'descripcion' => 'Cuánta plata entró. Lo de hoy sirve para cuadrar el turno; lo demás es cómo va el negocio.',
            'permisos' => [
                'caja.hoy' => [
                    'etiqueta' => 'Ver la caja del día',
                    'explicacion' => 'Lo que entró hoy, de dónde vino y con qué medio, para cuadrar el turno. Nada del mes ni de deudas.',
                ],
                'reportes.ver' => [
                    'etiqueta' => 'Ver la caja completa y los informes',
                    'explicacion' => 'Lo que entró en el mes y en los anteriores, lo que se debe y todos los informes.',
                ],
            ],
        ],
        'correos' => [
            'titulo' => 'Correos a socios',
            'descripcion' => 'Lo que se les manda a los socios desde el panel.',
            'permisos' => [
                'notificaciones.ver' => [
                    'etiqueta' => 'Ver los correos',
                    'explicacion' => 'Qué se mandó, a quién y si llegó.',
                ],
                'notificaciones.crear' => [
                    'etiqueta' => 'Escribir un correo a un socio',
                    'explicacion' => 'Mandarle a una persona un aviso con una de las plantillas.',
                ],
                'notificaciones.enviar' => [
                    'etiqueta' => 'Reintentar o cancelar un envío',
                    'explicacion' => 'Volver a mandar uno que no salió, o frenar uno que todavía no sale.',
                ],
                'notificaciones.editar' => [
                    'etiqueta' => 'Escribir a un grupo',
                    'explicacion' => 'Mandar el mismo correo a muchos socios a la vez.',
                ],
            ],
        ],
        'historial' => [
            'titulo' => 'Historial',
            'descripcion' => 'Qué se cambió y quién lo hizo.',
            'permisos' => [
                'historial.ver' => [
                    'etiqueta' => 'Ver el historial',
                    'explicacion' => 'Pausas, cambios de plan, renovaciones y bajas, con su fecha.',
                ],
            ],
        ],
        'configuracion' => [
            'titulo' => 'Configuración',
            'descripcion' => 'Lo que decide cuánto cobra el gimnasio y lo que se ve en la página web.',
            'permisos' => [
                'configuracion.ver' => [
                    'etiqueta' => 'Ver la configuración',
                    'explicacion' => 'Planes, precios, convenios, métodos de pago, la web, la papelera y los ajustes.',
                ],
                'configuracion.crear' => [
                    'etiqueta' => 'Agregar a la configuración',
                    'explicacion' => 'Planes, convenios, métodos de pago, clases, rutinas y contenido de la web nuevos.',
                ],
                'configuracion.editar' => [
                    'etiqueta' => 'Cambiar la configuración',
                    'explicacion' => 'Precios, plantillas de correo, textos legales, ajustes del gimnasio y sacar cosas de la papelera.',
                ],
                'configuracion.eliminar' => [
                    'etiqueta' => 'Borrar de la configuración',
                    'explicacion' => 'Quitar clases, rutinas, especialistas o contenido de la web, y vaciar la papelera.',
                ],
            ],
        ],
        'usuarios' => [
            'titulo' => 'Usuarios del panel',
            'descripcion' => 'Quién entra al panel y con qué perfil.',
            'cuidado' => 'Quien puede crear o cambiar cuentas puede crearse una de Administrador, y desde ahí todo lo demás.',
            'permisos' => [
                'usuarios.ver' => [
                    'etiqueta' => 'Ver las cuentas',
                    'explicacion' => 'Quién tiene cuenta, con qué perfil y cuándo entró por última vez.',
                ],
                'usuarios.crear' => [
                    'etiqueta' => 'Crear cuentas',
                    'explicacion' => 'Darle entrada a alguien nuevo, con el perfil que se elija.',
                    'cuidado' => true,
                ],
                'usuarios.editar' => [
                    'etiqueta' => 'Cambiar cuentas y perfiles',
                    'explicacion' => 'Cambiar el perfil de una cuenta, su contraseña, y lo que puede hacer cada perfil.',
                    'cuidado' => true,
                ],
            ],
        ],
    ];

    /** Todos los permisos que se pueden dar, en el orden de la pantalla. */
    public static function todos(): array
    {
        return array_merge(...array_map(fn (array $area) => array_keys($area['permisos']), array_values(self::AREAS)));
    }

    public static function existe(string $permiso): bool
    {
        return in_array($permiso, self::todos(), true);
    }

    /**
     * Lo que tiene que estar encendido antes que esto, sin repetir.
     *
     * Ver el módulo va siempre, salvo para el propio «ver»: así no hay que
     * escribirlo en cada línea del catálogo y no se olvida en la próxima.
     */
    public static function necesita(string $permiso): array
    {
        $ficha = self::ficha($permiso);
        $directos = $ficha['necesita'] ?? [];

        [$modulo] = explode('.', $permiso);
        $ver = "{$modulo}.ver";

        if ($permiso !== $ver && self::existe($ver)) {
            array_unshift($directos, $ver);
        }

        return array_values(array_unique($directos));
    }

    /** Lo que necesita cada permiso, para que la pantalla haga lo mismo. */
    public static function mapaDeDependencias(): array
    {
        $mapa = [];

        foreach (self::todos() as $permiso) {
            $mapa[$permiso] = self::necesita($permiso);
        }

        return $mapa;
    }

    /**
     * Lo marcado más todo lo que eso necesita, en el orden del catálogo.
     *
     * Encender «Corregir sus cobros de hoy» enciende «Cobrar» y «Ver pagos».
     * Al revés no: apagar un «ver» y dejar encendido lo de más arriba es una
     * contradicción que resuelve la pantalla apagando lo de arriba, a la vista;
     * aquí lo seguro es completar, nunca quitar en silencio lo que se pidió.
     */
    public static function conLoQueNecesitan(array $permisos): array
    {
        $pendientes = array_values($permisos);
        $resultado = [];

        while ($pendientes !== []) {
            $permiso = array_shift($pendientes);

            if (isset($resultado[$permiso])) {
                continue;
            }

            $resultado[$permiso] = true;

            foreach (self::necesita($permiso) as $previo) {
                $pendientes[] = $previo;
            }
        }

        return array_values(array_filter(self::todos(), fn ($p) => isset($resultado[$p])));
    }

    /**
     * La lista guardada de un rol, escrita con los permisos del catálogo.
     *
     * Un rol puede traer «clientes.*» o nombres viejos de antes del
     * vocabulario «modulo.accion»: el comodín de módulo se abre en sus
     * permisos y lo que no está en el catálogo no se pinta, porque no hay
     * casilla que lo represente.
     */
    public static function expandir(array $guardados): array
    {
        if (in_array('*', $guardados, true)) {
            return self::todos();
        }

        return array_values(array_filter(self::todos(), function (string $permiso) use ($guardados) {
            [$modulo] = explode('.', $permiso);

            return in_array($permiso, $guardados, true) || in_array("{$modulo}.*", $guardados, true);
        }));
    }

    /** Las áreas tal como las pinta la pantalla. */
    public static function paraLaPantalla(): array
    {
        return array_map(fn (string $clave, array $area) => [
            'clave' => $clave,
            'titulo' => $area['titulo'],
            'descripcion' => $area['descripcion'],
            'cuidado' => $area['cuidado'] ?? null,
            'permisos' => array_map(fn (string $permiso, array $ficha) => [
                'permiso' => $permiso,
                'etiqueta' => $ficha['etiqueta'],
                'explicacion' => $ficha['explicacion'],
                'cuidado' => (bool) ($ficha['cuidado'] ?? false),
            ], array_keys($area['permisos']), array_values($area['permisos'])),
        ], array_keys(self::AREAS), array_values(self::AREAS));
    }

    /** El nombre de persona de un permiso, para el registro de cambios. */
    public static function etiqueta(string $permiso): string
    {
        return self::ficha($permiso)['etiqueta'] ?? $permiso;
    }

    private static function ficha(string $permiso): array
    {
        foreach (self::AREAS as $area) {
            if (isset($area['permisos'][$permiso])) {
                return $area['permisos'][$permiso];
            }
        }

        return [];
    }
}
