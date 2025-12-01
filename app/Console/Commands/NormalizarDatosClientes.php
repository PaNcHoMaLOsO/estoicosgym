<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;

class NormalizarDatosClientes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clientes:normalizar 
                            {--rut : Solo normalizar RUTs}
                            {--telefono : Solo normalizar teléfonos}
                            {--dry-run : Mostrar cambios sin aplicarlos}';

    /**
     * The console command description.
     */
    protected $description = 'Normaliza RUTs y teléfonos de clientes al formato estándar chileno';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $soloRut = $this->option('rut');
        $soloTelefono = $this->option('telefono');
        
        // Si no se especifica ninguno, hacer ambos
        $hacerRut = !$soloTelefono || $soloRut;
        $hacerTelefono = !$soloRut || $soloTelefono;

        if ($dryRun) {
            $this->warn('🔍 Modo DRY-RUN: Solo se mostrarán los cambios, no se aplicarán.');
            $this->newLine();
        }

        $clientes = Cliente::all();
        $this->info("📊 Procesando {$clientes->count()} clientes...");
        $this->newLine();

        $rutsActualizados = 0;
        $telefonosActualizados = 0;
        $errores = [];

        $bar = $this->output->createProgressBar($clientes->count());
        $bar->start();

        foreach ($clientes as $cliente) {
            $cambios = [];

            // Normalizar RUT
            if ($hacerRut && $cliente->run_pasaporte) {
                $rutOriginal = $cliente->run_pasaporte;
                $rutNormalizado = $this->formatearRut($rutOriginal);
                
                if ($rutNormalizado !== $rutOriginal) {
                    $cambios['rut'] = [
                        'original' => $rutOriginal,
                        'nuevo' => $rutNormalizado
                    ];
                    
                    if (!$dryRun) {
                        $cliente->run_pasaporte = $rutNormalizado;
                    }
                    $rutsActualizados++;
                }
            }

            // Normalizar Teléfono
            if ($hacerTelefono && $cliente->celular) {
                $telefonoOriginal = $cliente->celular;
                $telefonoNormalizado = $this->formatearTelefono($telefonoOriginal);
                
                if ($telefonoNormalizado !== $telefonoOriginal) {
                    $cambios['telefono'] = [
                        'original' => $telefonoOriginal,
                        'nuevo' => $telefonoNormalizado
                    ];
                    
                    if (!$dryRun) {
                        $cliente->celular = $telefonoNormalizado;
                    }
                    $telefonosActualizados++;
                }
            }

            // Normalizar Teléfono de Emergencia
            if ($hacerTelefono && $cliente->telefono_emergencia) {
                $telefonoEmergOriginal = $cliente->telefono_emergencia;
                $telefonoEmergNormalizado = $this->formatearTelefono($telefonoEmergOriginal);
                
                if ($telefonoEmergNormalizado !== $telefonoEmergOriginal) {
                    $cambios['telefono_emergencia'] = [
                        'original' => $telefonoEmergOriginal,
                        'nuevo' => $telefonoEmergNormalizado
                    ];
                    
                    if (!$dryRun) {
                        $cliente->telefono_emergencia = $telefonoEmergNormalizado;
                    }
                    $telefonosActualizados++;
                }
            }

            // Mostrar cambios para este cliente
            if (!empty($cambios)) {
                $bar->clear();
                $this->newLine();
                $this->line("👤 <fg=cyan>{$cliente->nombres} {$cliente->apellido_paterno}</> (ID: {$cliente->id})");
                
                if (isset($cambios['rut'])) {
                    $this->line("   RUT: <fg=red>{$cambios['rut']['original']}</> → <fg=green>{$cambios['rut']['nuevo']}</>");
                }
                if (isset($cambios['telefono'])) {
                    $this->line("   CEL: <fg=red>{$cambios['telefono']['original']}</> → <fg=green>{$cambios['telefono']['nuevo']}</>");
                }
                if (isset($cambios['telefono_emergencia'])) {
                    $this->line("   EMG: <fg=red>{$cambios['telefono_emergencia']['original']}</> → <fg=green>{$cambios['telefono_emergencia']['nuevo']}</>");
                }
                $bar->display();
            }

            // Guardar si no es dry-run
            if (!$dryRun && !empty($cambios)) {
                try {
                    $cliente->save();
                } catch (\Exception $e) {
                    $errores[] = "Error en cliente {$cliente->id}: " . $e->getMessage();
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resumen
        $this->info('═══════════════════════════════════════');
        $this->info('           📋 RESUMEN                  ');
        $this->info('═══════════════════════════════════════');
        
        if ($hacerRut) {
            $this->line("   RUTs normalizados:     <fg=yellow>{$rutsActualizados}</>");
        }
        if ($hacerTelefono) {
            $this->line("   Teléfonos normalizados: <fg=yellow>{$telefonosActualizados}</>");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  Modo DRY-RUN: Ningún cambio fue aplicado.');
            $this->info('   Ejecuta sin --dry-run para aplicar los cambios.');
        } else {
            $this->newLine();
            $this->info('✅ Cambios aplicados correctamente.');
        }

        if (!empty($errores)) {
            $this->newLine();
            $this->error('❌ Errores encontrados:');
            foreach ($errores as $error) {
                $this->line("   - {$error}");
            }
        }

        return 0;
    }

    /**
     * Formatea un RUT al formato estándar: XX.XXX.XXX-X
     */
    private function formatearRut(string $rut): string
    {
        // Eliminar todo excepto números y K/k
        $rut = preg_replace('/[^0-9kK]/', '', strtoupper($rut));
        
        if (strlen($rut) < 2) {
            return $rut;
        }
        
        // Separar cuerpo y dígito verificador
        $dv = substr($rut, -1);
        $cuerpo = substr($rut, 0, -1);
        
        if (strlen($cuerpo) === 0) {
            return $rut;
        }
        
        // Formatear con puntos (de derecha a izquierda)
        $cuerpoFormateado = '';
        $contador = 0;
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $cuerpoFormateado = $cuerpo[$i] . $cuerpoFormateado;
            $contador++;
            if ($contador === 3 && $i > 0) {
                $cuerpoFormateado = '.' . $cuerpoFormateado;
                $contador = 0;
            }
        }
        
        return $cuerpoFormateado . '-' . $dv;
    }

    /**
     * Formatea un teléfono al formato estándar: +56 9 XXXX XXXX
     */
    private function formatearTelefono(string $telefono): string
    {
        // Eliminar todo excepto números
        $numeros = preg_replace('/\D/', '', $telefono);
        
        // Si empieza con 56, quitarlo
        if (str_starts_with($numeros, '56')) {
            $numeros = substr($numeros, 2);
        }
        
        // Asegurar que empiece con 9 (celular chileno)
        if (!str_starts_with($numeros, '9') && strlen($numeros) > 0) {
            $numeros = '9' . $numeros;
        }
        
        // Limitar a 9 dígitos
        $numeros = substr($numeros, 0, 9);
        
        if (strlen($numeros) < 9) {
            // Si no tiene 9 dígitos, devolver con formato básico
            return '+56 9 ' . $numeros;
        }
        
        // Formato: +56 9 XXXX XXXX
        return '+56 ' . $numeros[0] . ' ' . substr($numeros, 1, 4) . ' ' . substr($numeros, 5, 4);
    }
}
