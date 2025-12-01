<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Convenio;
use App\Models\Estado;
use App\Models\HistorialTraspaso;
use App\Models\HistorialCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Módulos disponibles para reportes
     */
    private $modulos = [
        'clientes' => [
            'nombre' => 'Clientes',
            'icono' => 'fas fa-users',
            'color' => '#4361ee',
            'modelo' => Cliente::class,
            'campos' => [
                'id' => ['label' => 'ID', 'tipo' => 'numero'],
                'run_pasaporte' => ['label' => 'RUT/Pasaporte', 'tipo' => 'texto'],
                'nombres' => ['label' => 'Nombres', 'tipo' => 'texto'],
                'apellido_paterno' => ['label' => 'Apellido Paterno', 'tipo' => 'texto'],
                'apellido_materno' => ['label' => 'Apellido Materno', 'tipo' => 'texto'],
                'email' => ['label' => 'Email', 'tipo' => 'texto'],
                'celular' => ['label' => 'Celular', 'tipo' => 'texto'],
                'fecha_nacimiento' => ['label' => 'Fecha Nacimiento', 'tipo' => 'fecha'],
                'direccion' => ['label' => 'Dirección', 'tipo' => 'texto'],
                'contacto_emergencia' => ['label' => 'Contacto Emergencia', 'tipo' => 'texto'],
                'telefono_emergencia' => ['label' => 'Tel. Emergencia', 'tipo' => 'texto'],
                'observaciones' => ['label' => 'Observaciones', 'tipo' => 'texto'],
                'activo' => ['label' => 'Activo', 'tipo' => 'booleano'],
                'created_at' => ['label' => 'Fecha Registro', 'tipo' => 'fecha'],
            ],
            'relaciones' => ['inscripciones', 'convenio'],
        ],
        'inscripciones' => [
            'nombre' => 'Inscripciones',
            'icono' => 'fas fa-file-signature',
            'color' => '#7c3aed',
            'modelo' => Inscripcion::class,
            'campos' => [
                'id' => ['label' => 'ID', 'tipo' => 'numero'],
                'fecha_inicio' => ['label' => 'Fecha Inicio', 'tipo' => 'fecha'],
                'fecha_vencimiento' => ['label' => 'Fecha Vencimiento', 'tipo' => 'fecha'],
                'precio_base' => ['label' => 'Precio Base', 'tipo' => 'moneda'],
                'precio_final' => ['label' => 'Precio Final', 'tipo' => 'moneda'],
                'descuento_aplicado' => ['label' => 'Descuento', 'tipo' => 'moneda'],
                'id_estado' => ['label' => 'Estado', 'tipo' => 'estado'],
                'pausada' => ['label' => 'Pausada', 'tipo' => 'booleano'],
                'created_at' => ['label' => 'Fecha Creación', 'tipo' => 'fecha'],
            ],
            'relaciones' => ['cliente', 'membresia', 'estado'],
        ],
        'pagos' => [
            'nombre' => 'Pagos',
            'icono' => 'fas fa-dollar-sign',
            'color' => '#10b981',
            'modelo' => Pago::class,
            'campos' => [
                'id' => ['label' => 'ID', 'tipo' => 'numero'],
                'fecha_pago' => ['label' => 'Fecha Pago', 'tipo' => 'fecha'],
                'monto_total' => ['label' => 'Monto Total', 'tipo' => 'moneda'],
                'monto_abonado' => ['label' => 'Monto Abonado', 'tipo' => 'moneda'],
                'monto_pendiente' => ['label' => 'Monto Pendiente', 'tipo' => 'moneda'],
                'tipo_pago' => ['label' => 'Tipo Pago', 'tipo' => 'select', 'opciones' => ['completo' => 'Completo', 'abono' => 'Abono']],
                'id_estado' => ['label' => 'Estado', 'tipo' => 'estado'],
                'referencia_pago' => ['label' => 'Referencia', 'tipo' => 'texto'],
                'observaciones' => ['label' => 'Observaciones', 'tipo' => 'texto'],
                'created_at' => ['label' => 'Fecha Registro', 'tipo' => 'fecha'],
            ],
            'relaciones' => ['cliente', 'inscripcion', 'metodoPago', 'estado'],
        ],
        'membresias' => [
            'nombre' => 'Membresías',
            'icono' => 'fas fa-id-card',
            'color' => '#f59e0b',
            'modelo' => Membresia::class,
            'campos' => [
                'id' => ['label' => 'ID', 'tipo' => 'numero'],
                'nombre' => ['label' => 'Nombre', 'tipo' => 'texto'],
                'descripcion' => ['label' => 'Descripción', 'tipo' => 'texto'],
                'duracion_meses' => ['label' => 'Duración (meses)', 'tipo' => 'numero'],
                'duracion_dias' => ['label' => 'Duración (días)', 'tipo' => 'numero'],
                'max_pausas' => ['label' => 'Max Pausas', 'tipo' => 'numero'],
                'activo' => ['label' => 'Activo', 'tipo' => 'booleano'],
                'created_at' => ['label' => 'Fecha Creación', 'tipo' => 'fecha'],
            ],
            'relaciones' => ['inscripciones', 'precios'],
        ],
        'metodos_pago' => [
            'nombre' => 'Métodos de Pago',
            'icono' => 'fas fa-credit-card',
            'color' => '#3b82f6',
            'modelo' => MetodoPago::class,
            'campos' => [
                'id' => ['label' => 'ID', 'tipo' => 'numero'],
                'nombre' => ['label' => 'Nombre', 'tipo' => 'texto'],
                'descripcion' => ['label' => 'Descripción', 'tipo' => 'texto'],
                'activo' => ['label' => 'Activo', 'tipo' => 'booleano'],
            ],
            'relaciones' => [],
        ],
        'convenios' => [
            'nombre' => 'Convenios',
            'icono' => 'fas fa-handshake',
            'color' => '#ec4899',
            'modelo' => Convenio::class,
            'campos' => [
                'id' => ['label' => 'ID', 'tipo' => 'numero'],
                'nombre' => ['label' => 'Nombre', 'tipo' => 'texto'],
                'descripcion' => ['label' => 'Descripción', 'tipo' => 'texto'],
                'porcentaje_descuento' => ['label' => '% Descuento', 'tipo' => 'numero'],
                'activo' => ['label' => 'Activo', 'tipo' => 'booleano'],
            ],
            'relaciones' => ['clientes'],
        ],
    ];

    /**
     * Página principal del módulo de reportes
     */
    public function index()
    {
        $modulos = $this->modulos;
        
        // Estadísticas rápidas
        $stats = [
            'clientes' => Cliente::count(),
            'inscripciones_activas' => Inscripcion::where('id_estado', 100)->count(),
            'pagos_mes' => Pago::whereMonth('fecha_pago', now()->month)
                              ->whereYear('fecha_pago', now()->year)
                              ->count(),
            'ingresos_mes' => Pago::whereMonth('fecha_pago', now()->month)
                                 ->whereYear('fecha_pago', now()->year)
                                 ->where('id_estado', 201)
                                 ->sum('monto_abonado'),
        ];

        return view('admin.reportes.index', compact('modulos', 'stats'));
    }

    /**
     * Constructor de reportes dinámico
     */
    public function builder(Request $request)
    {
        $modulos = $this->modulos;
        $moduloSeleccionado = $request->get('modulo', 'clientes');
        $moduloConfig = $this->modulos[$moduloSeleccionado] ?? $this->modulos['clientes'];
        
        // Obtener estados para filtros
        $estados = Estado::orderBy('codigo')->get()->groupBy('tipo');
        
        // Obtener opciones para filtros de relaciones
        $membresias = Membresia::where('activo', true)->orderBy('nombre')->get();
        $metodosPago = MetodoPago::where('activo', true)->orderBy('nombre')->get();
        $convenios = Convenio::where('activo', true)->orderBy('nombre')->get();

        return view('admin.reportes.builder', compact(
            'modulos',
            'moduloSeleccionado',
            'moduloConfig',
            'estados',
            'membresias',
            'metodosPago',
            'convenios'
        ));
    }

    /**
     * Generar reporte según configuración
     */
    public function generar(Request $request)
    {
        $modulosSeleccionados = $request->input('modulos', [$request->input('modulo', 'clientes')]);
        if (!is_array($modulosSeleccionados)) {
            $modulosSeleccionados = [$modulosSeleccionados];
        }
        
        // Eliminar módulos vacíos o inválidos
        $modulosSeleccionados = array_filter($modulosSeleccionados, function($m) {
            return !empty($m) && isset($this->modulos[$m]);
        });
        
        if (empty($modulosSeleccionados)) {
            $modulosSeleccionados = ['clientes'];
        }
        
        $campos = $request->input('campos', []);
        $filtros = $request->input('filtros', []);
        $ordenar = $request->input('ordenar', 'created_at');
        $direccion = $request->input('direccion', 'desc');
        $limite = (int) $request->input('limite', 0);
        $formato = $request->input('formato', 'tabla');

        // Procesar campos - pueden venir como "modulo.campo" o solo "campo"
        $camposPorModulo = [];
        foreach ($campos as $campo) {
            if (strpos($campo, '.') !== false) {
                [$mod, $campoNombre] = explode('.', $campo, 2);
                if (isset($this->modulos[$mod])) {
                    $camposPorModulo[$mod][] = $campoNombre;
                }
            } else {
                // Si no tiene módulo, asignarlo al primer módulo seleccionado
                $camposPorModulo[$modulosSeleccionados[0]][] = $campo;
            }
        }

        // ========================================
        // CASO 1: UN SOLO MÓDULO - Lógica simple
        // ========================================
        if (count($modulosSeleccionados) === 1) {
            return $this->generarReporteSimple(
                $modulosSeleccionados[0],
                $camposPorModulo[$modulosSeleccionados[0]] ?? [],
                $filtros,
                $ordenar,
                $direccion,
                $limite,
                $formato,
                $request
            );
        }

        // ========================================
        // CASO 2: MÚLTIPLES MÓDULOS - Usar JOIN inteligente
        // ========================================
        return $this->generarReporteCombinado(
            $modulosSeleccionados,
            $camposPorModulo,
            $filtros,
            $ordenar,
            $direccion,
            $limite,
            $formato
        );
    }

    /**
     * Generar reporte de un solo módulo
     */
    private function generarReporteSimple($modulo, $camposSeleccionados, $filtros, $ordenar, $direccion, $limite, $formato, $request)
    {
        $config = $this->modulos[$modulo];
        $modelo = $config['modelo'];
        
        // Construir query
        $query = $modelo::query();

        // Agregar relaciones necesarias
        if (!empty($config['relaciones'])) {
            $query->with($config['relaciones']);
        }

        // Aplicar filtros
        $this->aplicarFiltros($query, $filtros, $config);

        // Filtros especiales por relación
        if ($request->filled('filtro_membresia') && in_array($modulo, ['inscripciones', 'pagos'])) {
            if ($modulo === 'inscripciones') {
                $query->where('id_membresia', $request->filtro_membresia);
            } elseif ($modulo === 'pagos') {
                $query->whereHas('inscripcion', function($q) use ($request) {
                    $q->where('id_membresia', $request->filtro_membresia);
                });
            }
        }

        if ($request->filled('filtro_metodo_pago') && $modulo === 'pagos') {
            $query->where('id_metodo_pago', $request->filtro_metodo_pago);
        }

        if ($request->filled('filtro_convenio') && $modulo === 'clientes') {
            $query->where('id_convenio', $request->filtro_convenio);
        }

        // Extraer campo de ordenamiento
        $ordenarCampo = $ordenar;
        if (strpos($ordenar, '.') !== false) {
            [, $ordenarCampo] = explode('.', $ordenar, 2);
        }

        // Ordenar
        if (isset($config['campos'][$ordenarCampo])) {
            $query->orderBy($ordenarCampo, $direccion);
        } else {
            $query->orderBy('id', $direccion);
        }

        // Limitar
        if ($limite > 0) {
            $query->limit($limite);
        }

        // Ejecutar query
        $datos = $query->get();
        
        // Si no hay campos seleccionados, usar campos por defecto
        if (empty($camposSeleccionados)) {
            $camposSeleccionados = array_slice(array_keys($config['campos']), 0, 5);
        }

        // Calcular totales
        $totales = [];
        foreach ($camposSeleccionados as $campo) {
            if (isset($config['campos'][$campo]) && $config['campos'][$campo]['tipo'] === 'moneda') {
                $totales[$campo] = $datos->sum($campo);
            }
        }

        // Exportar
        if ($formato === 'excel') {
            return $this->exportarExcel($datos, $camposSeleccionados, $config, $modulo);
        }

        if ($formato === 'pdf') {
            return $this->exportarPdf($datos, $camposSeleccionados, $config, $modulo);
        }

        return response()->json([
            'datos' => $datos,
            'campos' => $camposSeleccionados,
            'config' => $config,
            'totales' => $totales,
            'total_registros' => $datos->count(),
        ]);
    }

    /**
     * Generar reporte combinando múltiples módulos
     */
    private function generarReporteCombinado($modulosSeleccionados, $camposPorModulo, $filtros, $ordenar, $direccion, $limite, $formato)
    {
        // Determinar qué módulos están seleccionados
        $tieneClientes = in_array('clientes', $modulosSeleccionados);
        $tieneInscripciones = in_array('inscripciones', $modulosSeleccionados);
        $tienePagos = in_array('pagos', $modulosSeleccionados);
        $tieneMembresias = in_array('membresias', $modulosSeleccionados);
        $tieneConvenios = in_array('convenios', $modulosSeleccionados);
        $tieneMetodosPago = in_array('metodos_pago', $modulosSeleccionados);

        // Construir configuración de campos
        $configCombinada = [
            'nombre' => 'Reporte Combinado',
            'campos' => [],
        ];

        foreach ($modulosSeleccionados as $modulo) {
            if (!isset($this->modulos[$modulo])) continue;
            $config = $this->modulos[$modulo];
            $camposModulo = $camposPorModulo[$modulo] ?? [];
            
            foreach ($camposModulo as $campo) {
                if (isset($config['campos'][$campo])) {
                    $configCombinada['campos']["{$modulo}.{$campo}"] = [
                        'label' => $config['campos'][$campo]['label'],
                        'tipo' => $config['campos'][$campo]['tipo'],
                        'modulo' => $config['nombre'],
                    ];
                }
            }
        }

        // Si no hay campos configurados, retornar error
        if (empty($configCombinada['campos'])) {
            return response()->json([
                'error' => 'No hay campos seleccionados para los módulos activos',
                'datos' => [],
                'campos' => [],
                'config' => $configCombinada,
                'totales' => [],
                'total_registros' => 0,
                'modulos_combinados' => true,
            ]);
        }

        $resultados = collect();

        // =============================================
        // ESTRATEGIA MEJORADA: 
        // - Si tiene PAGOS: cada pago es una fila (con su inscripción, cliente, membresía)
        // - Si tiene INSCRIPCIONES pero no pagos: cada inscripción es una fila
        // - Si tiene solo CLIENTES: cada cliente con sus inscripciones
        // =============================================
        
        if ($tienePagos) {
            // CADA PAGO ES UNA FILA
            $query = Pago::query()
                ->with(['cliente', 'cliente.convenio', 'inscripcion', 'inscripcion.membresia', 'inscripcion.estado', 'metodoPago']);
            
            // Aplicar filtros de fecha
            if (isset($filtros['created_at'])) {
                $valor = $filtros['created_at'];
                if (!empty($valor['desde'])) {
                    $query->whereDate('pagos.created_at', '>=', $valor['desde']);
                }
                if (!empty($valor['hasta'])) {
                    $query->whereDate('pagos.created_at', '<=', $valor['hasta']);
                }
            }
            
            $query->orderBy('pagos.created_at', $direccion);
            
            if ($limite > 0) {
                $query->limit($limite);
            }
            
            $pagos = $query->get();
            
            foreach ($pagos as $pago) {
                $fila = [];
                
                // Campos de CLIENTES
                if ($tieneClientes && $pago->cliente) {
                    foreach ($camposPorModulo['clientes'] ?? [] as $campo) {
                        $fila["clientes.{$campo}"] = $pago->cliente->$campo ?? null;
                    }
                }
                
                // Campos de INSCRIPCIONES
                if ($tieneInscripciones && $pago->inscripcion) {
                    foreach ($camposPorModulo['inscripciones'] ?? [] as $campo) {
                        $fila["inscripciones.{$campo}"] = $pago->inscripcion->$campo ?? null;
                    }
                }
                
                // Campos de MEMBRESÍAS
                if ($tieneMembresias && $pago->inscripcion && $pago->inscripcion->membresia) {
                    foreach ($camposPorModulo['membresias'] ?? [] as $campo) {
                        $fila["membresias.{$campo}"] = $pago->inscripcion->membresia->$campo ?? null;
                    }
                }
                
                // Campos de PAGOS
                foreach ($camposPorModulo['pagos'] ?? [] as $campo) {
                    $fila["pagos.{$campo}"] = $pago->$campo ?? null;
                }
                
                // Campos de MÉTODOS DE PAGO
                if ($tieneMetodosPago && $pago->metodoPago) {
                    foreach ($camposPorModulo['metodos_pago'] ?? [] as $campo) {
                        $fila["metodos_pago.{$campo}"] = $pago->metodoPago->$campo ?? null;
                    }
                }
                
                // Campos de CONVENIOS
                if ($tieneConvenios && $pago->cliente && $pago->cliente->convenio) {
                    foreach ($camposPorModulo['convenios'] ?? [] as $campo) {
                        $fila["convenios.{$campo}"] = $pago->cliente->convenio->$campo ?? null;
                    }
                }
                
                $resultados->push($fila);
            }
            
        } elseif ($tieneInscripciones || $tieneMembresias || $tieneClientes) {
            // CADA INSCRIPCIÓN ES UNA FILA
            $query = Inscripcion::query()
                ->with(['cliente', 'cliente.convenio', 'membresia', 'estado']);
            
            // Aplicar filtros de fecha
            if (isset($filtros['created_at'])) {
                $valor = $filtros['created_at'];
                if (!empty($valor['desde'])) {
                    $query->whereDate('inscripciones.created_at', '>=', $valor['desde']);
                }
                if (!empty($valor['hasta'])) {
                    $query->whereDate('inscripciones.created_at', '<=', $valor['hasta']);
                }
            }
            
            $query->orderBy('inscripciones.created_at', $direccion);
            
            if ($limite > 0) {
                $query->limit($limite);
            }
            
            $inscripciones = $query->get();
            
            foreach ($inscripciones as $inscripcion) {
                $fila = [];
                
                // Campos de CLIENTES
                if ($tieneClientes && $inscripcion->cliente) {
                    foreach ($camposPorModulo['clientes'] ?? [] as $campo) {
                        $fila["clientes.{$campo}"] = $inscripcion->cliente->$campo ?? null;
                    }
                }
                
                // Campos de INSCRIPCIONES
                if ($tieneInscripciones) {
                    foreach ($camposPorModulo['inscripciones'] ?? [] as $campo) {
                        $fila["inscripciones.{$campo}"] = $inscripcion->$campo ?? null;
                    }
                }
                
                // Campos de MEMBRESÍAS
                if ($tieneMembresias && $inscripcion->membresia) {
                    foreach ($camposPorModulo['membresias'] ?? [] as $campo) {
                        $fila["membresias.{$campo}"] = $inscripcion->membresia->$campo ?? null;
                    }
                }
                
                // Campos de CONVENIOS
                if ($tieneConvenios && $inscripcion->cliente && $inscripcion->cliente->convenio) {
                    foreach ($camposPorModulo['convenios'] ?? [] as $campo) {
                        $fila["convenios.{$campo}"] = $inscripcion->cliente->convenio->$campo ?? null;
                    }
                }
                
                $resultados->push($fila);
            }
            
        } else {
            // Para módulos no relacionados (convenios solos, métodos pago solos, etc.)
            foreach ($modulosSeleccionados as $modulo) {
                if (!isset($this->modulos[$modulo])) continue;
                $config = $this->modulos[$modulo];
                $modelo = $config['modelo'];
                
                $query = $modelo::query();
                
                if (isset($filtros['created_at'])) {
                    $valor = $filtros['created_at'];
                    if (!empty($valor['desde'])) {
                        $query->whereDate('created_at', '>=', $valor['desde']);
                    }
                    if (!empty($valor['hasta'])) {
                        $query->whereDate('created_at', '<=', $valor['hasta']);
                    }
                }
                
                if ($limite > 0) {
                    $query->limit($limite);
                }
                
                $query->orderBy('created_at', $direccion);
                $datos = $query->get();
                
                foreach ($datos as $row) {
                    $fila = [];
                    foreach ($camposPorModulo[$modulo] ?? [] as $campo) {
                        $fila["{$modulo}.{$campo}"] = $row->$campo ?? null;
                    }
                    // Rellenar campos de otros módulos con null
                    foreach ($modulosSeleccionados as $otroModulo) {
                        if ($otroModulo !== $modulo && isset($camposPorModulo[$otroModulo])) {
                            foreach ($camposPorModulo[$otroModulo] as $campo) {
                                $fila["{$otroModulo}.{$campo}"] = null;
                            }
                        }
                    }
                    $resultados->push($fila);
                }
            }
        }

        // Calcular totales
        $totales = [];
        foreach ($configCombinada['campos'] as $campo => $campoConfig) {
            if ($campoConfig['tipo'] === 'moneda') {
                $totales[$campo] = $resultados->sum(function($row) use ($campo) {
                    return $row[$campo] ?? 0;
                });
            }
        }

        return response()->json([
            'datos' => $resultados->values(),
            'campos' => array_keys($configCombinada['campos']),
            'config' => $configCombinada,
            'totales' => $totales,
            'total_registros' => $resultados->count(),
            'modulos_combinados' => true,
        ]);
    }

    /**
     * Aplicar filtros a una query
     */
    private function aplicarFiltros($query, $filtros, $config)
    {
        foreach ($filtros as $campo => $valor) {
            if (empty($valor) && $valor !== '0') continue;
            
            $tipoCampo = $config['campos'][$campo]['tipo'] ?? 'texto';
            
            switch ($tipoCampo) {
                case 'texto':
                    $query->where($campo, 'like', "%{$valor}%");
                    break;
                case 'numero':
                case 'moneda':
                case 'estado':
                    $query->where($campo, $valor);
                    break;
                case 'fecha':
                    if (is_array($valor)) {
                        if (!empty($valor['desde'])) {
                            $query->whereDate($campo, '>=', $valor['desde']);
                        }
                        if (!empty($valor['hasta'])) {
                            $query->whereDate($campo, '<=', $valor['hasta']);
                        }
                    }
                    break;
                case 'booleano':
                    $query->where($campo, $valor === 'true' || $valor === '1');
                    break;
                case 'select':
                    $query->where($campo, $valor);
                    break;
            }
        }
    }

    /**
     * Reportes predefinidos
     */
    public function predefinido($tipo)
    {
        switch ($tipo) {
            case 'ingresos-mensuales':
                return $this->reporteIngresosMensuales();
            case 'membresias-activas':
                return $this->reporteMembresiasActivas();
            case 'clientes-por-vencer':
                return $this->reporteClientesPorVencer();
            case 'pagos-pendientes':
                return $this->reportePagosPendientes();
            case 'resumen-general':
                return $this->reporteResumenGeneral();
            default:
                return redirect()->route('admin.reportes.index')
                    ->with('error', 'Reporte no encontrado');
        }
    }

    /**
     * Reporte de ingresos mensuales
     */
    private function reporteIngresosMensuales()
    {
        $year = request('year', now()->year);
        
        $ingresosPorMes = Pago::select(
                DB::raw('MONTH(fecha_pago) as mes'),
                DB::raw('SUM(monto_abonado) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->whereYear('fecha_pago', $year)
            ->where('id_estado', 201) // Solo pagados
            ->groupBy(DB::raw('MONTH(fecha_pago)'))
            ->orderBy('mes')
            ->get();

        $ingresosPorMetodo = Pago::select(
                'metodos_pago.nombre',
                DB::raw('SUM(pagos.monto_abonado) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->join('metodos_pago', 'pagos.id_metodo_pago', '=', 'metodos_pago.id')
            ->whereYear('fecha_pago', $year)
            ->where('id_estado', 201)
            ->groupBy('metodos_pago.id', 'metodos_pago.nombre')
            ->orderByDesc('total')
            ->get();

        $ingresosPorMembresia = Pago::select(
                'membresias.nombre',
                DB::raw('SUM(pagos.monto_abonado) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->join('inscripciones', 'pagos.id_inscripcion', '=', 'inscripciones.id')
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->whereYear('fecha_pago', $year)
            ->where('pagos.id_estado', 201)
            ->groupBy('membresias.id', 'membresias.nombre')
            ->orderByDesc('total')
            ->get();

        $totalAnual = Pago::whereYear('fecha_pago', $year)
            ->where('id_estado', 201)
            ->sum('monto_abonado');

        return view('admin.reportes.ingresos-mensuales', compact(
            'ingresosPorMes',
            'ingresosPorMetodo',
            'ingresosPorMembresia',
            'totalAnual',
            'year'
        ));
    }

    /**
     * Reporte de membresías activas
     */
    private function reporteMembresiasActivas()
    {
        $inscripcionesPorMembresia = Inscripcion::select(
                'membresias.nombre',
                'membresias.duracion_dias',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(inscripciones.precio_final) as valor_total')
            )
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->where('inscripciones.id_estado', 100) // Activas
            ->groupBy('membresias.id', 'membresias.nombre', 'membresias.duracion_dias')
            ->orderByDesc('total')
            ->get();

        $inscripcionesPorEstado = Inscripcion::select(
                'estados.nombre',
                'estados.codigo',
                DB::raw('COUNT(*) as total')
            )
            ->join('estados', 'inscripciones.id_estado', '=', 'estados.codigo')
            ->groupBy('estados.codigo', 'estados.nombre')
            ->orderByDesc('total')
            ->get();

        $porVencer = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->orderBy('fecha_vencimiento')
            ->get();

        $stats = [
            'total_activas' => Inscripcion::where('id_estado', 100)->count(),
            'total_pausadas' => Inscripcion::where('id_estado', 101)->count(),
            'total_vencidas' => Inscripcion::where('id_estado', 102)->count(),
            'por_vencer_7dias' => $porVencer->count(),
        ];

        return view('admin.reportes.membresias-activas', compact(
            'inscripcionesPorMembresia',
            'inscripcionesPorEstado',
            'porVencer',
            'stats'
        ));
    }

    /**
     * Reporte de clientes por vencer
     */
    private function reporteClientesPorVencer()
    {
        $dias = request('dias', 7);
        
        $clientesPorVencer = Inscripcion::with(['cliente', 'membresia', 'estado'])
            ->where('id_estado', 100)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)])
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('admin.reportes.clientes-por-vencer', compact('clientesPorVencer', 'dias'));
    }

    /**
     * Reporte de pagos pendientes
     */
    private function reportePagosPendientes()
    {
        $pagosPendientes = Pago::with(['cliente', 'inscripcion.membresia', 'metodoPago'])
            ->whereIn('id_estado', [200, 202]) // Pendiente o Parcial
            ->orderBy('fecha_pago', 'desc')
            ->get();

        $totalPendiente = $pagosPendientes->sum('monto_pendiente');
        $totalAbonado = $pagosPendientes->sum('monto_abonado');

        return view('admin.reportes.pagos-pendientes', compact(
            'pagosPendientes',
            'totalPendiente',
            'totalAbonado'
        ));
    }

    /**
     * Reporte resumen general
     */
    private function reporteResumenGeneral()
    {
        $mesActual = now()->month;
        $yearActual = now()->year;

        $stats = [
            // Clientes
            'total_clientes' => Cliente::count(),
            'clientes_mes' => Cliente::whereMonth('created_at', $mesActual)
                                    ->whereYear('created_at', $yearActual)
                                    ->count(),
            
            // Inscripciones
            'inscripciones_activas' => Inscripcion::where('id_estado', 100)->count(),
            'inscripciones_pausadas' => Inscripcion::where('id_estado', 101)->count(),
            'inscripciones_vencidas' => Inscripcion::where('id_estado', 102)->count(),
            'inscripciones_mes' => Inscripcion::whereMonth('created_at', $mesActual)
                                             ->whereYear('created_at', $yearActual)
                                             ->count(),
            
            // Pagos
            'pagos_mes' => Pago::whereMonth('fecha_pago', $mesActual)
                              ->whereYear('fecha_pago', $yearActual)
                              ->where('id_estado', 201)
                              ->count(),
            'ingresos_mes' => Pago::whereMonth('fecha_pago', $mesActual)
                                 ->whereYear('fecha_pago', $yearActual)
                                 ->where('id_estado', 201)
                                 ->sum('monto_abonado'),
            'pagos_pendientes' => Pago::whereIn('id_estado', [200, 202])->count(),
            'monto_pendiente' => Pago::whereIn('id_estado', [200, 202])->sum('monto_pendiente'),
            
            // Membresías populares
            'membresia_mas_vendida' => Inscripcion::select('id_membresia', DB::raw('COUNT(*) as total'))
                ->groupBy('id_membresia')
                ->orderByDesc('total')
                ->with('membresia')
                ->first(),
        ];

        // Tendencia últimos 6 meses
        $tendencia = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $tendencia[] = [
                'mes' => $fecha->translatedFormat('M Y'),
                'ingresos' => Pago::whereMonth('fecha_pago', $fecha->month)
                                 ->whereYear('fecha_pago', $fecha->year)
                                 ->where('id_estado', 201)
                                 ->sum('monto_abonado'),
                'inscripciones' => Inscripcion::whereMonth('created_at', $fecha->month)
                                             ->whereYear('created_at', $fecha->year)
                                             ->count(),
            ];
        }

        return view('admin.reportes.resumen-general', compact('stats', 'tendencia'));
    }

    /**
     * Exportar a Excel (CSV)
     */
    private function exportarExcel($datos, $campos, $config, $modulo)
    {
        $filename = "reporte_{$modulo}_" . now()->format('Y-m-d_His') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($datos, $campos, $config) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            $headerRow = [];
            foreach ($campos as $campo) {
                $headerRow[] = $config['campos'][$campo]['label'] ?? $campo;
            }
            fputcsv($file, $headerRow, ';');
            
            // Data
            foreach ($datos as $row) {
                $dataRow = [];
                foreach ($campos as $campo) {
                    $valor = $row->{$campo} ?? '';
                    
                    // Formatear según tipo
                    $tipo = $config['campos'][$campo]['tipo'] ?? 'texto';
                    switch ($tipo) {
                        case 'moneda':
                            $valor = number_format((float)$valor, 0, ',', '.');
                            break;
                        case 'fecha':
                            if ($valor instanceof Carbon) {
                                $valor = $valor->format('d/m/Y');
                            }
                            break;
                        case 'booleano':
                            $valor = $valor ? 'Sí' : 'No';
                            break;
                    }
                    
                    $dataRow[] = $valor;
                }
                fputcsv($file, $dataRow, ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar a PDF (genera HTML para imprimir)
     */
    private function exportarPdf($datos, $campos, $config, $modulo)
    {
        return view('admin.reportes.pdf', compact('datos', 'campos', 'config', 'modulo'));
    }

    /**
     * API: Obtener campos de un módulo
     */
    public function getCamposModulo($modulo)
    {
        if (!isset($this->modulos[$modulo])) {
            return response()->json(['error' => 'Módulo no válido'], 400);
        }

        return response()->json([
            'campos' => $this->modulos[$modulo]['campos'],
            'relaciones' => $this->modulos[$modulo]['relaciones'],
        ]);
    }
}
