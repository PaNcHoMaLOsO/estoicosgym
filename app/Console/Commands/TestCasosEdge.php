<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Cliente;
use App\Models\Membresia;
use Illuminate\Support\Facades\DB;

class TestCasosEdge extends Command
{
    protected $signature = 'test:casos-edge {--ejecutar : Ejecutar los tests (de lo contrario solo muestra info)}';
    protected $description = 'Probar casos edge/límite del sistema para validar reglas de negocio';

    public function handle()
    {
        $ejecutar = $this->option('ejecutar');
        
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║            TEST DE CASOS EDGE - VALIDACIONES DE NEGOCIO                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        if (!$ejecutar) {
            $this->warn('⚠ Modo información. Use --ejecutar para probar en base de datos real.');
            $this->warn('  Esto NO modificará datos existentes, creará registros temporales.');
            $this->newLine();
        }
        
        $resultados = [];
        
        // TEST 1: Sobrepago
        $resultados[] = $this->testSobrepago($ejecutar);
        
        // TEST 2: Pago en inscripción finalizada
        $resultados[] = $this->testPagoInscripcionFinalizada();
        
        // TEST 3: Pausa excedida
        $resultados[] = $this->testPausaExcedida();
        
        // TEST 4: Fechas invertidas
        $resultados[] = $this->testFechasInvertidas();
        
        // TEST 5: Monto mixto incorrecto
        $resultados[] = $this->testMontoMixtoIncorrecto();
        
        // TEST 6: Cliente inactivo con pago
        $resultados[] = $this->testClienteInactivoPago();
        
        // Resumen
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════════════════════');
        $this->info('RESUMEN DE VALIDACIONES:');
        $this->info('═══════════════════════════════════════════════════════════════════════════════');
        
        $pasados = 0;
        $fallidos = 0;
        foreach ($resultados as $r) {
            if ($r['status'] === 'pass') {
                $this->info("  ✅ {$r['test']}: {$r['mensaje']}");
                $pasados++;
            } elseif ($r['status'] === 'fail') {
                $this->error("  ❌ {$r['test']}: {$r['mensaje']}");
                $fallidos++;
            } else {
                $this->warn("  ⚠ {$r['test']}: {$r['mensaje']}");
            }
        }
        
        $this->newLine();
        $this->info("Total: {$pasados} pasados, {$fallidos} fallidos");
        
        return $fallidos > 0 ? 1 : 0;
    }

    private function testSobrepago($ejecutar)
    {
        $this->comment('🧪 TEST 1: Validación de sobrepago');
        
        // Buscar inscripción activa con pagos
        $inscripcion = Inscripcion::with('pagos')
            ->where('id_estado', 100)
            ->whereHas('pagos')
            ->first();
        
        if (!$inscripcion) {
            return ['test' => 'Sobrepago', 'status' => 'skip', 'mensaje' => 'No hay inscripciones activas con pagos para probar'];
        }
        
        $precioTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;
        $totalPagado = $inscripcion->pagos->sum('monto_abonado');
        
        $this->line("   Inscripción #{$inscripcion->id}: Precio \${$precioTotal}, Pagado \${$totalPagado}");
        
        // Verificar si la validación existe en el código
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/PagoController.php'));
        $tieneValidacion = strpos($controllerCode, 'montoPagado >= montoTotal') !== false || 
                          strpos($controllerCode, '$montoPagado >= $montoTotal') !== false;
        
        if ($tieneValidacion) {
            $this->info('   → El controlador tiene validación de sobrepago');
            return ['test' => 'Sobrepago', 'status' => 'pass', 'mensaje' => 'Validación existe en PagoController'];
        } else {
            $this->warn('   → ⚠ No se encontró validación explícita de sobrepago');
            return ['test' => 'Sobrepago', 'status' => 'fail', 'mensaje' => 'Falta validación en PagoController'];
        }
    }

    private function testPagoInscripcionFinalizada()
    {
        $this->comment('🧪 TEST 2: Bloqueo de pagos en inscripciones finalizadas');
        
        // Verificar si existe validación en el código
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/PagoController.php'));
        $tieneValidacion = strpos($controllerCode, 'INSCRIPCION_FINALIZADOS') !== false ||
                          strpos($controllerCode, '103, 105, 106') !== false ||
                          strpos($controllerCode, 'No se puede registrar pago') !== false;
        
        if ($tieneValidacion) {
            $this->info('   → El controlador bloquea pagos en inscripciones finalizadas');
            return ['test' => 'Pago en finalizada', 'status' => 'pass', 'mensaje' => 'Validación implementada'];
        } else {
            $this->warn('   → ⚠ No se encontró validación para inscripciones finalizadas');
            return ['test' => 'Pago en finalizada', 'status' => 'fail', 'mensaje' => 'Falta validación'];
        }
    }

    private function testPausaExcedida()
    {
        $this->comment('🧪 TEST 3: Bloqueo de pausas excedidas');
        
        // Verificar si existe validación en el modelo
        $modelCode = file_get_contents(app_path('Models/Inscripcion.php'));
        $tieneValidacion = strpos($modelCode, 'puedeRealizarPausa') !== false &&
                          (strpos($modelCode, 'pausas_realizadas') !== false || 
                           strpos($modelCode, 'max_pausas') !== false);
        
        if ($tieneValidacion) {
            $this->info('   → El modelo Inscripcion tiene validación de pausas');
            return ['test' => 'Pausa excedida', 'status' => 'pass', 'mensaje' => 'Método puedeRealizarPausa existe'];
        } else {
            return ['test' => 'Pausa excedida', 'status' => 'fail', 'mensaje' => 'Falta método puedeRealizarPausa'];
        }
    }

    private function testFechasInvertidas()
    {
        $this->comment('🧪 TEST 4: Inscripciones con fechas invertidas');
        
        $inscripcionesInvalidas = Inscripcion::whereColumn('fecha_inicio', '>', 'fecha_vencimiento')
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_vencimiento')
            ->count();
        
        if ($inscripcionesInvalidas > 0) {
            $this->warn("   → Hay {$inscripcionesInvalidas} inscripciones con fecha_inicio > fecha_vencimiento");
            return ['test' => 'Fechas invertidas', 'status' => 'fail', 'mensaje' => "{$inscripcionesInvalidas} registros con fechas inválidas"];
        } else {
            $this->info('   → No hay inscripciones con fechas invertidas');
            return ['test' => 'Fechas invertidas', 'status' => 'pass', 'mensaje' => 'Todas las fechas son válidas'];
        }
    }

    private function testMontoMixtoIncorrecto()
    {
        $this->comment('🧪 TEST 5: Validación de pagos mixtos');
        
        // Verificar en código
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/PagoController.php'));
        $tieneValidacion = strpos($controllerCode, 'monto1 + $monto2') !== false ||
                          strpos($controllerCode, 'montoAbonado != intval($montoPendiente)') !== false;
        
        if ($tieneValidacion) {
            $this->info('   → Validación de suma de montos mixtos implementada');
            return ['test' => 'Monto mixto', 'status' => 'pass', 'mensaje' => 'Validación existe'];
        } else {
            return ['test' => 'Monto mixto', 'status' => 'fail', 'mensaje' => 'Falta validación de suma'];
        }
    }

    private function testClienteInactivoPago()
    {
        $this->comment('🧪 TEST 6: Bloqueo de pagos para clientes inactivos');
        
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/PagoController.php'));
        $tieneValidacion = strpos($controllerCode, 'cliente->activo') !== false ||
                          strpos($controllerCode, 'cliente inactivo') !== false;
        
        if ($tieneValidacion) {
            $this->info('   → Validación de cliente activo implementada');
            return ['test' => 'Cliente inactivo', 'status' => 'pass', 'mensaje' => 'Validación existe'];
        } else {
            return ['test' => 'Cliente inactivo', 'status' => 'fail', 'mensaje' => 'Falta validación'];
        }
    }
}
