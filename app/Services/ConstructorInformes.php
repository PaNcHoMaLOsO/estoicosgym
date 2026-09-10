<?php

namespace App\Services;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Informes a medida: se elige de qué, qué columnas y con qué filtros.
 *
 * LA LISTA DE COLUMNAS ES UNA LISTA BLANCA, no una sugerencia. Lo que llega del
 * formulario —columnas, filtros, orden— se compara contra el catálogo de aquí
 * abajo y lo que no aparece se descarta. La versión anterior no lo hacía: la
 * clave del filtro entraba directa en el WHERE, así que el propio formulario,
 * que ofrecía un filtro «Género» sobre una columna que no existe en la tabla,
 * devolvía un 500 con el SQL en pantalla.
 *
 * Y se piden SOLO las columnas elegidas. Antes hacía `select *`: un listado de
 * nombres se llevaba al navegador el RUT del apoderado, el teléfono de
 * emergencia y todo lo demás de cada socio.
 */
class ConstructorInformes
{
    /**
     * Tope duro de filas.
     *
     * «Sin límite» en un desplegable es una promesa que no se puede cumplir: la
     * consulta se trae todo a memoria antes de contar. Con el gimnasio en marcha
     * eso tumba el servidor, y quien lo pidió solo quería ver la tabla.
     */
    public const TOPE = 5000;

    public const LIMITES = [50, 100, 250, 500, 1000, self::TOPE];

    /** Los estados que puede tener una membresia vendida, con su nombre. */
    public const ESTADOS_INSCRIPCION = [
        EstadosCodigo::INSCRIPCION_ACTIVA => 'Activa',
        EstadosCodigo::INSCRIPCION_PAUSADA => 'Pausada',
        EstadosCodigo::INSCRIPCION_VENCIDA => 'Vencida',
        EstadosCodigo::INSCRIPCION_CANCELADA => 'Cancelada',
        EstadosCodigo::INSCRIPCION_SUSPENDIDA => 'Suspendida',
        EstadosCodigo::INSCRIPCION_CAMBIADA => 'Cambiada de plan',
        EstadosCodigo::INSCRIPCION_TRASPASADA => 'Traspasada',
    ];

    /** Los estados de un pago. */
    public const ESTADOS_PAGO = [
        EstadosCodigo::PAGO_PENDIENTE => 'Pendiente',
        EstadosCodigo::PAGO_PAGADO => 'Pagado',
        EstadosCodigo::PAGO_PARCIAL => 'Abono',
        EstadosCodigo::PAGO_VENCIDO => 'Vencido',
        EstadosCodigo::PAGO_CANCELADO => 'Cancelado',
        EstadosCodigo::PAGO_TRASPASADO => 'Traspasado',
    ];

    /**
     * Qué se puede pedir, de dónde sale y cómo se enseña.
     *
     * Va en un método y no en una propiedad porque las columnas derivadas —las
     * que resuelven una relación, como el nombre del socio de un pago— se
     * calculan con una función, y una propiedad no admite funciones.
     *
     * @return array<string,array<string,mixed>>
     */
    public function catalogo(): array
    {
        return [
            'clientes' => [
                'titulo' => 'Socios',
                'modelo' => Cliente::class,
                'relaciones' => ['convenio'],
                'columnas' => [
                    'run_pasaporte' => ['titulo' => 'RUT', 'tipo' => 'texto'],
                    'nombres' => ['titulo' => 'Nombres', 'tipo' => 'texto'],
                    'apellido_paterno' => ['titulo' => 'Apellido paterno', 'tipo' => 'texto'],
                    'apellido_materno' => ['titulo' => 'Apellido materno', 'tipo' => 'texto'],
                    'email' => ['titulo' => 'Correo', 'tipo' => 'texto'],
                    'celular' => ['titulo' => 'Celular', 'tipo' => 'texto'],
                    'fecha_nacimiento' => ['titulo' => 'Nacimiento', 'tipo' => 'fecha'],
                    'direccion' => ['titulo' => 'Dirección', 'tipo' => 'texto'],
                    'contacto_emergencia' => ['titulo' => 'Contacto de emergencia', 'tipo' => 'texto'],
                    'telefono_emergencia' => ['titulo' => 'Tel. emergencia', 'tipo' => 'texto'],
                    'es_menor_edad' => ['titulo' => 'Menor de edad', 'tipo' => 'booleano'],
                    'activo' => ['titulo' => 'Activo', 'tipo' => 'booleano'],
                    'created_at' => ['titulo' => 'Se registró', 'tipo' => 'fecha'],
                    'convenio' => [
                        'titulo' => 'Convenio',
                        'tipo' => 'texto',
                        'derivada' => fn (Cliente $c) => $c->convenio?->nombre,
                    ],
                ],
            ],

            'inscripciones' => [
                'titulo' => 'Membresías vendidas',
                'modelo' => Inscripcion::class,
                'relaciones' => ['cliente', 'membresia'],
                'columnas' => [
                    'socio' => [
                        'titulo' => 'Socio',
                        'tipo' => 'texto',
                        'derivada' => fn (Inscripcion $i) => $i->cliente
                            ? trim("{$i->cliente->nombres} {$i->cliente->apellido_paterno} {$i->cliente->apellido_materno}")
                            : null,
                    ],
                    'plan' => [
                        'titulo' => 'Plan',
                        'tipo' => 'texto',
                        'derivada' => fn (Inscripcion $i) => $i->membresia?->nombre,
                    ],
                    'fecha_inicio' => ['titulo' => 'Empieza', 'tipo' => 'fecha'],
                    'fecha_vencimiento' => ['titulo' => 'Vence', 'tipo' => 'fecha'],
                    'precio_base' => ['titulo' => 'Precio del plan', 'tipo' => 'moneda'],
                    'descuento_aplicado' => ['titulo' => 'Descuento', 'tipo' => 'moneda'],
                    'precio_final' => ['titulo' => 'Precio cobrado', 'tipo' => 'moneda'],
                    'id_estado' => [
                        'titulo' => 'Estado',
                        'tipo' => 'estado',
                        // Con nombre, no con el codigo: nadie se sabe de
                        // memoria que 106 es «Traspasada».
                        'opciones' => self::ESTADOS_INSCRIPCION,
                    ],
                    'pausada' => ['titulo' => 'Pausada', 'tipo' => 'booleano'],
                    'created_at' => ['titulo' => 'Se creó', 'tipo' => 'fecha'],
                ],
            ],

            'pagos' => [
                'titulo' => 'Pagos',
                'modelo' => Pago::class,
                'relaciones' => ['cliente', 'metodoPago'],
                'columnas' => [
                    'socio' => [
                        'titulo' => 'Socio',
                        'tipo' => 'texto',
                        'derivada' => fn (Pago $p) => $p->cliente
                            ? trim("{$p->cliente->nombres} {$p->cliente->apellido_paterno}")
                            : null,
                    ],
                    'fecha_pago' => ['titulo' => 'Fecha', 'tipo' => 'fecha'],
                    'monto_abonado' => ['titulo' => 'Abonado', 'tipo' => 'moneda'],
                    'monto_pendiente' => ['titulo' => 'Pendiente', 'tipo' => 'moneda'],
                    'monto_total' => ['titulo' => 'Total', 'tipo' => 'moneda'],
                    'metodo' => [
                        'titulo' => 'Método',
                        'tipo' => 'texto',
                        // Un pago pendiente NO tiene metodo: todavia no se ha
                        // pagado con nada.
                        'derivada' => fn (Pago $p) => $p->metodoPago?->nombre,
                    ],
                    'tipo_pago' => [
                        'titulo' => 'Tipo',
                        'tipo' => 'opciones',
                        'opciones' => [
                            'completo' => 'Completo',
                            'parcial' => 'Abono',
                            'mixto' => 'Mixto',
                            'pendiente' => 'Pendiente',
                        ],
                    ],
                    'id_estado' => [
                        'titulo' => 'Estado',
                        'tipo' => 'estado',
                        'opciones' => self::ESTADOS_PAGO,
                    ],
                    'referencia_pago' => ['titulo' => 'Referencia', 'tipo' => 'texto'],
                    'observaciones' => ['titulo' => 'Observaciones', 'tipo' => 'texto'],
                ],
            ],

            'membresias' => [
                'titulo' => 'Planes',
                'modelo' => Membresia::class,
                'relaciones' => [],
                'columnas' => [
                    'nombre' => ['titulo' => 'Plan', 'tipo' => 'texto'],
                    'descripcion' => ['titulo' => 'Descripción', 'tipo' => 'texto'],
                    'duracion_meses' => ['titulo' => 'Meses', 'tipo' => 'numero'],
                    'duracion_dias' => ['titulo' => 'Días', 'tipo' => 'numero'],
                    'max_pausas' => ['titulo' => 'Pausas permitidas', 'tipo' => 'numero'],
                    'activo' => ['titulo' => 'Activo', 'tipo' => 'booleano'],
                ],
            ],

            'convenios' => [
                'titulo' => 'Convenios',
                'modelo' => Convenio::class,
                'relaciones' => [],
                'columnas' => [
                    'nombre' => ['titulo' => 'Convenio', 'tipo' => 'texto'],
                    'tipo' => ['titulo' => 'Tipo', 'tipo' => 'texto'],
                    'descripcion' => ['titulo' => 'Descripción', 'tipo' => 'texto'],
                    // La columna se llama descuento_porcentaje. El catalogo
                    // viejo la pedia como porcentaje_descuento, al reves, asi
                    // que esta columna salia SIEMPRE vacia.
                    'descuento_porcentaje' => ['titulo' => '% de descuento', 'tipo' => 'numero'],
                    'descuento_monto' => ['titulo' => 'Descuento fijo', 'tipo' => 'moneda'],
                    'contacto_nombre' => ['titulo' => 'Contacto', 'tipo' => 'texto'],
                    'contacto_email' => ['titulo' => 'Correo de contacto', 'tipo' => 'texto'],
                    'activo' => ['titulo' => 'Activo', 'tipo' => 'booleano'],
                ],
            ],

            'metodos_pago' => [
                'titulo' => 'Métodos de pago',
                'modelo' => MetodoPago::class,
                'relaciones' => [],
                'columnas' => [
                    'nombre' => ['titulo' => 'Método', 'tipo' => 'texto'],
                    'descripcion' => ['titulo' => 'Descripción', 'tipo' => 'texto'],
                    'requiere_comprobante' => ['titulo' => 'Pide comprobante', 'tipo' => 'booleano'],
                    'activo' => ['titulo' => 'Activo', 'tipo' => 'booleano'],
                ],
            ],
        ];
    }

    /**
     * El catálogo en la forma que entiende el navegador.
     *
     * Sin las clases de modelo ni las funciones de las columnas derivadas: son
     * cosas del servidor y no pintan nada en una respuesta JSON.
     *
     * @return array<string,mixed>
     */
    public function catalogoParaPantalla(): array
    {
        $salida = [];

        foreach ($this->catalogo() as $clave => $modulo) {
            $columnas = [];

            foreach ($modulo['columnas'] as $nombre => $columna) {
                $columnas[$nombre] = [
                    'titulo' => $columna['titulo'],
                    'tipo' => $columna['tipo'],
                    'opciones' => $columna['opciones'] ?? null,
                    // Por una columna derivada no se puede ordenar ni filtrar:
                    // no existe como columna en la tabla, se calcula despues.
                    'derivada' => isset($columna['derivada']),
                ];
            }

            $salida[$clave] = [
                'titulo' => $modulo['titulo'],
                'columnas' => $columnas,
            ];
        }

        return $salida;
    }

    public function existe(string $modulo): bool
    {
        return isset($this->catalogo()[$modulo]);
    }

    /**
     * Ejecuta el informe.
     *
     * @param array<string,mixed> $peticion columnas, filtros, orden, direccion, limite
     * @return array{columnas:list<array<string,mixed>>,filas:list<array<string,mixed>>,totales:array<string,int>,cuantas:int,recortado:bool}
     */
    public function ejecutar(string $modulo, array $peticion): array
    {
        $config = $this->catalogo()[$modulo];

        $columnas = $this->columnasPedidas($config, $peticion['columnas'] ?? []);
        $limite = $this->limite($peticion['limite'] ?? null);

        $consulta = $config['modelo']::query();

        // Solo se cargan las relaciones que hacen falta para las columnas
        // elegidas: pedirlas todas siempre es una consulta por relación que
        // nadie va a mirar.
        $relaciones = $this->relacionesNecesarias($config, $columnas);

        if ($relaciones !== []) {
            $consulta->with($relaciones);
        }

        $consulta->select($this->seleccion($config, $columnas, $relaciones));

        $this->filtrar($consulta, $config, $peticion['filtros'] ?? []);
        $this->ordenar($consulta, $config, $peticion['orden'] ?? null, $peticion['direccion'] ?? 'desc');

        // Se pide UNA fila de más para saber si el tope dejó algo fuera y poder
        // decirlo, en vez de enseñar una tabla recortada como si fuera todo.
        $filas = $consulta->limit($limite + 1)->get();
        $recortado = $filas->count() > $limite;

        if ($recortado) {
            $filas = $filas->take($limite);
        }

        return [
            'columnas' => array_map(
                fn (string $c) => [
                    'clave' => $c,
                    'titulo' => $config['columnas'][$c]['titulo'],
                    'tipo' => $config['columnas'][$c]['tipo'],
                ],
                $columnas
            ),
            'filas' => $filas->map(fn ($fila) => $this->dibujarFila($fila, $config, $columnas))->values()->all(),
            'totales' => $this->totales($filas, $config, $columnas),
            'cuantas' => $filas->count(),
            'recortado' => $recortado,
        ];
    }

    /**
     * Las columnas del informe, en el orden del catálogo.
     *
     * Lo que no esté en el catálogo se descarta. Sin ninguna válida se enseñan
     * las primeras, que es mejor que una tabla sin columnas.
     *
     * @param array<string,mixed> $config
     * @param list<string> $pedidas
     * @return list<string>
     */
    private function columnasPedidas(array $config, array $pedidas): array
    {
        $validas = array_values(array_filter(
            array_keys($config['columnas']),
            fn (string $c) => in_array($c, $pedidas, true)
        ));

        return $validas !== [] ? $validas : array_slice(array_keys($config['columnas']), 0, 5);
    }

    /**
     * Las columnas que se le piden a la base.
     *
     * SOLO LAS ELEGIDAS. Antes la consulta era `select *`, así que un listado de
     * nombres se traía de cada socio el RUT del apoderado, el teléfono de
     * emergencia y todo lo demás, y eso viajaba entero al navegador.
     *
     * Van también la clave primaria y la foránea de cada relación: sin la
     * foránea el eager loading no tiene con qué emparejar y las columnas que
     * salen de una relación —el socio de un pago, su método— saldrían vacías.
     *
     * @param array<string,mixed> $config
     * @param list<string> $columnas
     * @param list<string> $relaciones
     * @return list<string>
     */
    private function seleccion(array $config, array $columnas, array $relaciones): array
    {
        $modelo = new $config['modelo']();
        $tabla = $modelo->getTable();

        $seleccion = [$tabla . '.' . $modelo->getKeyName()];

        foreach ($columnas as $columna) {
            if (! isset($config['columnas'][$columna]['derivada'])) {
                $seleccion[] = $tabla . '.' . $columna;
            }
        }

        foreach ($relaciones as $relacion) {
            $r = $modelo->{$relacion}();

            if ($r instanceof BelongsTo) {
                $seleccion[] = $tabla . '.' . $r->getForeignKeyName();
            }
        }

        return array_values(array_unique($seleccion));
    }

    /**
     * @param array<string,mixed> $config
     * @param list<string> $columnas
     * @return list<string>
     */
    private function relacionesNecesarias(array $config, array $columnas): array
    {
        if ($config['relaciones'] === []) {
            return [];
        }

        $hayDerivadas = false;

        foreach ($columnas as $columna) {
            if (isset($config['columnas'][$columna]['derivada'])) {
                $hayDerivadas = true;

                break;
            }
        }

        return $hayDerivadas ? $config['relaciones'] : [];
    }

    /**
     * @param array<string,mixed> $config
     * @param array<string,mixed> $filtros
     */
    private function filtrar(Builder $consulta, array $config, array $filtros): void
    {
        foreach ($filtros as $campo => $valor) {
            $columna = $config['columnas'][$campo] ?? null;

            // Lo que no está en el catálogo NO llega a la consulta. Una clave
            // inventada en la URL antes entraba igual y reventaba el informe.
            if (! $columna || isset($columna['derivada'])) {
                continue;
            }

            if ($valor === null || $valor === '' || $valor === []) {
                continue;
            }

            match ($columna['tipo']) {
                'texto' => $consulta->where($campo, 'like', '%' . $valor . '%'),
                'numero', 'moneda' => $consulta->where($campo, $valor),
                'estado', 'opciones' => $consulta->where($campo, $valor),
                'booleano' => $consulta->where($campo, in_array($valor, ['1', 1, true, 'true'], true)),
                'fecha' => $this->filtrarPorFecha($consulta, $campo, $valor),
                default => null,
            };
        }
    }

    private function filtrarPorFecha(Builder $consulta, string $campo, mixed $valor): void
    {
        if (! is_array($valor)) {
            return;
        }

        if (! empty($valor['desde'])) {
            $consulta->whereDate($campo, '>=', $valor['desde']);
        }

        if (! empty($valor['hasta'])) {
            $consulta->whereDate($campo, '<=', $valor['hasta']);
        }
    }

    /**
     * @param array<string,mixed> $config
     */
    private function ordenar(Builder $consulta, array $config, ?string $orden, string $direccion): void
    {
        // Solo asc o desc: cualquier otra cosa hace que Eloquent lance una
        // excepcion, y esto viene de la URL.
        $direccion = strtolower($direccion) === 'asc' ? 'asc' : 'desc';

        $columna = $config['columnas'][$orden] ?? null;

        if ($orden && $columna && ! isset($columna['derivada'])) {
            $consulta->orderBy($orden, $direccion);

            return;
        }

        $consulta->orderBy('id', $direccion);
    }

    /**
     * @param array<string,mixed> $config
     * @param list<string> $columnas
     * @return array<string,mixed>
     */
    private function dibujarFila($fila, array $config, array $columnas): array
    {
        $salida = [];

        foreach ($columnas as $clave) {
            $columna = $config['columnas'][$clave];

            $valor = isset($columna['derivada'])
                ? ($columna['derivada'])($fila)
                : $fila->{$clave};

            $salida[$clave] = $this->formatear($valor, $columna);
        }

        return $salida;
    }

    /**
     * @param array<string,mixed> $columna
     */
    private function formatear(mixed $valor, array $columna): mixed
    {
        if ($valor === null) {
            return null;
        }

        return match ($columna['tipo']) {
            'fecha' => $valor instanceof Carbon
                ? $valor->format('d/m/Y')
                : Carbon::parse($valor)->format('d/m/Y'),
            'moneda', 'numero' => (float) $valor,
            'booleano' => (bool) $valor,
            // El MISMO nombre que ofrece el filtro. Sin esto el desplegable
            // decia «Abono» y la celda de al lado «Parcial» para el mismo
            // valor, que se lee como si fueran dos cosas distintas.
            'estado' => $columna['opciones'][(int) $valor] ?? EstadosCodigo::getNombre((int) $valor),
            'opciones' => $columna['opciones'][$valor] ?? $valor,
            default => (string) $valor,
        };
    }

    /**
     * Suma de las columnas de dinero.
     *
     * Un informe de pagos sin la suma abajo obliga a sacar la calculadora, que
     * es exactamente lo que se venía a evitar.
     *
     * @param array<string,mixed> $config
     * @param list<string> $columnas
     * @return array<string,int>
     */
    private function totales($filas, array $config, array $columnas): array
    {
        $totales = [];

        foreach ($columnas as $clave) {
            if (($config['columnas'][$clave]['tipo'] ?? null) !== 'moneda') {
                continue;
            }

            if (isset($config['columnas'][$clave]['derivada'])) {
                continue;
            }

            $totales[$clave] = (int) round($filas->sum($clave));
        }

        return $totales;
    }

    private function limite(mixed $pedido): int
    {
        $limite = (int) $pedido;

        if ($limite <= 0 || $limite > self::TOPE) {
            return self::TOPE;
        }

        return $limite;
    }
}
