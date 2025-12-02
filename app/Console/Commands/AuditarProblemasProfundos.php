<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\PrecioMembresia;
use Illuminate\Support\Facades\DB;

class AuditarProblemasProfundos extends Command
{
    protected $signature = 'audit:profundo';
    protected $description = 'Auditoría profunda buscando problemas ocultos en el sistema';

    private $problemas = [];

    public function handle()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║           AUDITORÍA PROFUNDA - BÚSQUEDA DE ERRORES OCULTOS                  ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // PARTE 1: CLIENTES
        $this->info('📋 PARTE 1: PROBLEMAS EN CLIENTES');
        $this->line('──────────────────────────────────────────────────────────────────────');
        $this->auditarClientesDuplicados();
        $this->auditarClientesEmailInvalido();
        $this->auditarClientesTelefonoInvalido();

        // PARTE 2: CONVENIOS Y MEMBRESÍAS
        $this->newLine();
        $this->info('📋 PARTE 2: PROBLEMAS EN CONVENIOS Y MEMBRESÍAS');
        $this->line('──────────────────────────────────────────────────────────────────────');
        $this->auditarConveniosInactivosConClientesActivos();
        $this->auditarMembresiasInactivasConInscripcionesActivas();
        $this->auditarMembresiasSinPrecio();

        // PARTE 3: PRECIOS Y DESCUENTOS
        $this->newLine();
        $this->info('📋 PARTE 3: PROBLEMAS EN PRECIOS Y DESCUENTOS');
        $this->line('──────────────────────────────────────────────────────────────────────');
        $this->auditarDescuentosMayoresAlPrecio();
        $this->auditarPreciosNegativos();
        $this->auditarInscripcionesConPrecioIncorrecto();

        // PARTE 4: FECHAS Y PERÍODOS
        $this->newLine();
        $this->info('📋 PARTE 4: PROBLEMAS EN FECHAS Y PERÍODOS');
        $this->line('──────────────────────────────────────────────────────────────────────');
        $this->auditarPagosConFechaFutura();
        $this->auditarInscripcionesConDuracionAnormal();
        $this->auditarPausasAnormales();

        // PARTE 5: SEGURIDAD Y USUARIOS
        $this->newLine();
        $this->info('📋 PARTE 5: SEGURIDAD Y USUARIOS');
        $this->line('──────────────────────────────────────────────────────────────────────');
        $this->auditarUsuariosSinRol();
        $this->auditarClientesInactivosConInscripcionesActivas();

        // PARTE 6: NOTIFICACIONES
        $this->newLine();
        $this->info('📋 PARTE 6: NOTIFICACIONES');
        $this->line('──────────────────────────────────────────────────────────────────────');
        $this->auditarNotificacionesFallidas();

        // RESUMEN
        $this->mostrarResumen();

        return count($this->problemas) > 0 ? 1 : 0;
    }

    // ==================== CLIENTES ====================

    private function auditarClientesDuplicados()
    {
        $this->line('');
        $this->comment('🔍 1.1 Clientes con nombres duplicados...');

        $duplicados = Cliente::selectRaw('CONCAT(nombres, " ", apellido_paterno) as nombre_completo, COUNT(*) as cantidad')
            ->groupBy('nombre_completo')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicados->count() > 0) {
            $this->warn("   ⚠ Hay {$duplicados->count()} nombres duplicados:");
            foreach ($duplicados->take(5) as $dup) {
                $this->line("     - \"{$dup->nombre_completo}\" aparece {$dup->cantidad} veces");
            }
            $this->problemas[] = "{$duplicados->count()} clientes con nombres duplicados";
        } else {
            $this->info('   ✓ OK - No hay nombres duplicados');
        }
    }

    private function auditarClientesEmailInvalido()
    {
        $this->line('');
        $this->comment('🔍 1.2 Clientes con emails inválidos...');

        $emailsInvalidos = Cliente::where('activo', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereRaw("email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'")
            ->get();

        if ($emailsInvalidos->count() > 0) {
            $this->warn("   ⚠ Hay {$emailsInvalidos->count()} clientes con email inválido:");
            foreach ($emailsInvalidos->take(5) as $c) {
                $this->line("     - #{$c->id} {$c->nombres}: '{$c->email}'");
            }
            $this->problemas[] = "{$emailsInvalidos->count()} emails inválidos";
        } else {
            $this->info('   ✓ OK - Todos los emails tienen formato válido');
        }
    }

    private function auditarClientesTelefonoInvalido()
    {
        $this->line('');
        $this->comment('🔍 1.3 Clientes con teléfonos sospechosos...');

        // Teléfonos muy cortos o muy largos
        $telefonosRaros = Cliente::where('activo', true)
            ->whereNotNull('celular')
            ->where(function($q) {
                $q->whereRaw('LENGTH(REPLACE(REPLACE(celular, " ", ""), "+", "")) < 8')
                  ->orWhereRaw('LENGTH(REPLACE(REPLACE(celular, " ", ""), "+", "")) > 15');
            })
            ->get();

        if ($telefonosRaros->count() > 0) {
            $this->warn("   ⚠ Hay {$telefonosRaros->count()} clientes con teléfono sospechoso:");
            foreach ($telefonosRaros->take(5) as $c) {
                $this->line("     - #{$c->id} {$c->nombres}: '{$c->celular}'");
            }
            $this->problemas[] = "{$telefonosRaros->count()} teléfonos sospechosos";
        } else {
            $this->info('   ✓ OK - Teléfonos tienen longitud normal');
        }
    }

    // ==================== CONVENIOS Y MEMBRESÍAS ====================

    private function auditarConveniosInactivosConClientesActivos()
    {
        $this->line('');
        $this->comment('🔍 2.1 Convenios inactivos con clientes activos...');

        $conveniosProblema = Convenio::where('activo', false)
            ->whereHas('clientes', function($q) {
                $q->where('activo', true);
            })
            ->withCount(['clientes' => function($q) {
                $q->where('activo', true);
            }])
            ->get();

        if ($conveniosProblema->count() > 0) {
            $this->warn("   ⚠ Hay {$conveniosProblema->count()} convenios inactivos con clientes activos:");
            foreach ($conveniosProblema as $conv) {
                $this->line("     - \"{$conv->nombre}\" (inactivo) tiene {$conv->clientes_count} cliente(s) activo(s)");
            }
            $this->problemas[] = "{$conveniosProblema->count()} convenios inactivos con clientes";
        } else {
            $this->info('   ✓ OK - Convenios inactivos no tienen clientes activos');
        }
    }

    private function auditarMembresiasInactivasConInscripcionesActivas()
    {
        $this->line('');
        $this->comment('🔍 2.2 Membresías inactivas con inscripciones activas...');

        $membresiasProblema = Membresia::where('activo', false)
            ->whereHas('inscripciones', function($q) {
                $q->where('id_estado', 100); // Activa
            })
            ->withCount(['inscripciones' => function($q) {
                $q->where('id_estado', 100);
            }])
            ->get();

        if ($membresiasProblema->count() > 0) {
            $this->warn("   ⚠ Hay {$membresiasProblema->count()} membresías inactivas con inscripciones activas:");
            foreach ($membresiasProblema as $mem) {
                $this->line("     - \"{$mem->nombre}\" (inactiva) tiene {$mem->inscripciones_count} inscripción(es) activa(s)");
            }
            $this->problemas[] = "{$membresiasProblema->count()} membresías inactivas con inscripciones";
        } else {
            $this->info('   ✓ OK - Membresías inactivas no tienen inscripciones activas');
        }
    }

    private function auditarMembresiasSinPrecio()
    {
        $this->line('');
        $this->comment('🔍 2.3 Membresías activas sin precio vigente...');

        $membresiasSinPrecio = Membresia::where('activo', true)
            ->whereDoesntHave('precios', function($q) {
                $q->where('activo', true)
                  ->where('fecha_vigencia_desde', '<=', now());
            })
            ->get();

        if ($membresiasSinPrecio->count() > 0) {
            $this->warn("   ⚠ Hay {$membresiasSinPrecio->count()} membresías sin precio vigente:");
            foreach ($membresiasSinPrecio as $mem) {
                $this->line("     - \"{$mem->nombre}\" no tiene precio activo");
            }
            $this->problemas[] = "{$membresiasSinPrecio->count()} membresías sin precio";
        } else {
            $this->info('   ✓ OK - Todas las membresías activas tienen precio');
        }
    }

    // ==================== PRECIOS Y DESCUENTOS ====================

    private function auditarDescuentosMayoresAlPrecio()
    {
        $this->line('');
        $this->comment('🔍 3.1 Inscripciones con descuento > precio base...');

        $descuentosExcesivos = Inscripcion::whereColumn('descuento_aplicado', '>', 'precio_base')
            ->whereNotNull('descuento_aplicado')
            ->where('descuento_aplicado', '>', 0)
            ->get();

        if ($descuentosExcesivos->count() > 0) {
            $this->warn("   ⚠ Hay {$descuentosExcesivos->count()} inscripciones con descuento excesivo:");
            foreach ($descuentosExcesivos->take(5) as $insc) {
                $this->line("     - #{$insc->id}: Base \${$insc->precio_base}, Descuento \${$insc->descuento_aplicado}");
            }
            $this->problemas[] = "{$descuentosExcesivos->count()} descuentos excesivos";
        } else {
            $this->info('   ✓ OK - Ningún descuento excede el precio base');
        }
    }

    private function auditarPreciosNegativos()
    {
        $this->line('');
        $this->comment('🔍 3.2 Inscripciones con precio final negativo o cero...');

        $preciosNegativos = Inscripcion::where(function($q) {
                $q->where('precio_final', '<', 0)
                  ->orWhere('precio_final', 0);
            })
            ->whereNotIn('id_estado', [103, 105, 106]) // Excluir canceladas/traspasadas
            ->get();

        if ($preciosNegativos->count() > 0) {
            $this->warn("   ⚠ Hay {$preciosNegativos->count()} inscripciones con precio ≤ 0:");
            foreach ($preciosNegativos->take(5) as $insc) {
                $this->line("     - #{$insc->id}: Precio final \${$insc->precio_final}");
            }
            $this->problemas[] = "{$preciosNegativos->count()} precios inválidos";
        } else {
            $this->info('   ✓ OK - Todos los precios son positivos');
        }
    }

    private function auditarInscripcionesConPrecioIncorrecto()
    {
        $this->line('');
        $this->comment('🔍 3.3 Inscripciones donde precio_final ≠ precio_base - descuento...');

        $preciosIncorrectos = Inscripcion::whereRaw('precio_final != (precio_base - COALESCE(descuento_aplicado, 0))')
            ->whereNotNull('precio_final')
            ->whereNotNull('precio_base')
            ->get();

        if ($preciosIncorrectos->count() > 0) {
            $this->warn("   ⚠ Hay {$preciosIncorrectos->count()} inscripciones con cálculo incorrecto:");
            foreach ($preciosIncorrectos->take(5) as $insc) {
                $esperado = $insc->precio_base - ($insc->descuento_aplicado ?? 0);
                $this->line("     - #{$insc->id}: Final \${$insc->precio_final}, Esperado \${$esperado}");
            }
            $this->problemas[] = "{$preciosIncorrectos->count()} precios mal calculados";
        } else {
            $this->info('   ✓ OK - Todos los precios están bien calculados');
        }
    }

    // ==================== FECHAS Y PERÍODOS ====================

    private function auditarPagosConFechaFutura()
    {
        $this->line('');
        $this->comment('🔍 4.1 Pagos con fecha futura...');

        $pagosFuturos = Pago::where('fecha_pago', '>', now())
            ->with(['cliente', 'inscripcion'])
            ->get();

        if ($pagosFuturos->count() > 0) {
            $this->warn("   ⚠ Hay {$pagosFuturos->count()} pagos con fecha futura:");
            foreach ($pagosFuturos->take(5) as $pago) {
                $this->line("     - Pago #{$pago->id}: {$pago->fecha_pago->format('d/m/Y')} - \${$pago->monto_abonado}");
            }
            $this->problemas[] = "{$pagosFuturos->count()} pagos con fecha futura";
        } else {
            $this->info('   ✓ OK - No hay pagos con fecha futura');
        }
    }

    private function auditarInscripcionesConDuracionAnormal()
    {
        $this->line('');
        $this->comment('🔍 4.2 Inscripciones con duración anormal (>400 días o <0)...');

        $duracionAnormal = Inscripcion::whereRaw('DATEDIFF(fecha_vencimiento, fecha_inicio) > 400')
            ->orWhereRaw('DATEDIFF(fecha_vencimiento, fecha_inicio) < 0')
            ->get();

        if ($duracionAnormal->count() > 0) {
            $this->warn("   ⚠ Hay {$duracionAnormal->count()} inscripciones con duración anormal:");
            foreach ($duracionAnormal->take(5) as $insc) {
                $dias = $insc->fecha_inicio->diffInDays($insc->fecha_vencimiento);
                $this->line("     - #{$insc->id}: {$dias} días (inicio: {$insc->fecha_inicio->format('d/m/Y')}, fin: {$insc->fecha_vencimiento->format('d/m/Y')})");
            }
            $this->problemas[] = "{$duracionAnormal->count()} duraciones anormales";
        } else {
            $this->info('   ✓ OK - Todas las duraciones son razonables');
        }
    }

    private function auditarPausasAnormales()
    {
        $this->line('');
        $this->comment('🔍 4.3 Pausas con fechas inconsistentes...');

        $pausasInconsistentes = Inscripcion::where('pausada', true)
            ->where(function($q) {
                $q->whereNull('fecha_pausa_inicio')
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('fecha_pausa_fin')
                         ->whereColumn('fecha_pausa_fin', '<', 'fecha_pausa_inicio');
                  });
            })
            ->get();

        if ($pausasInconsistentes->count() > 0) {
            $this->warn("   ⚠ Hay {$pausasInconsistentes->count()} pausas con fechas inconsistentes:");
            foreach ($pausasInconsistentes->take(5) as $insc) {
                $this->line("     - #{$insc->id}: inicio={$insc->fecha_pausa_inicio}, fin={$insc->fecha_pausa_fin}");
            }
            $this->problemas[] = "{$pausasInconsistentes->count()} pausas inconsistentes";
        } else {
            $this->info('   ✓ OK - Todas las pausas tienen fechas correctas');
        }
    }

    // ==================== SEGURIDAD Y USUARIOS ====================

    private function auditarUsuariosSinRol()
    {
        $this->line('');
        $this->comment('🔍 5.1 Usuarios sin rol asignado...');

        $usuariosSinRol = \App\Models\User::whereNull('id_rol')->get();

        if ($usuariosSinRol->count() > 0) {
            $this->warn("   ⚠ Hay {$usuariosSinRol->count()} usuarios sin rol:");
            foreach ($usuariosSinRol as $user) {
                $this->line("     - {$user->name} ({$user->email})");
            }
            $this->problemas[] = "{$usuariosSinRol->count()} usuarios sin rol";
        } else {
            $this->info('   ✓ OK - Todos los usuarios tienen rol asignado');
        }
    }

    private function auditarClientesInactivosConInscripcionesActivas()
    {
        $this->line('');
        $this->comment('🔍 5.2 Clientes inactivos con inscripciones activas...');

        $clientesProblema = \App\Models\Cliente::where('activo', false)
            ->whereHas('inscripciones', function($q) {
                $q->where('id_estado', 100); // INSCRIPCION_ACTIVA
            })
            ->with(['inscripciones' => function($q) {
                $q->where('id_estado', 100);
            }])
            ->get();

        if ($clientesProblema->count() > 0) {
            $this->warn("   ⚠ Hay {$clientesProblema->count()} clientes inactivos con inscripciones activas:");
            foreach ($clientesProblema as $cliente) {
                $this->line("     - {$cliente->nombre} {$cliente->apellido}: {$cliente->inscripciones->count()} inscripciones activas");
            }
            $this->problemas[] = "{$clientesProblema->count()} clientes inactivos con inscripciones activas";
        } else {
            $this->info('   ✓ OK - Clientes inactivos no tienen inscripciones activas');
        }
    }

    // ==================== NOTIFICACIONES ====================

    private function auditarNotificacionesFallidas()
    {
        $this->line('');
        $this->comment('🔍 6.1 Notificaciones fallidas en últimos 7 días...');

        try {
            $notificacionesFallidas = \App\Models\LogNotificacion::where('estado', 'error')
                ->where('created_at', '>', now()->subDays(7))
                ->count();

            if ($notificacionesFallidas > 0) {
                $this->warn("   ⚠ Hay {$notificacionesFallidas} notificaciones fallidas en los últimos 7 días");
                $this->problemas[] = "{$notificacionesFallidas} notificaciones fallidas";
            } else {
                $this->info('   ✓ OK - No hay notificaciones fallidas recientes');
            }
        } catch (\Exception $e) {
            $this->info('   ℹ Tabla de logs de notificaciones no disponible');
        }
    }

    // ==================== RESUMEN ====================

    private function mostrarResumen()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');

        if (count($this->problemas) > 0) {
            $this->warn('║  ⚠ RESULTADO: Se encontraron ' . str_pad(count($this->problemas), 2) . ' problemas                                  ║');
            $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->warn('Resumen de problemas encontrados:');
            foreach ($this->problemas as $i => $problema) {
                $this->line("  " . ($i + 1) . ". {$problema}");
            }
        } else {
            $this->info('║  ✅ RESULTADO: No se encontraron problemas ocultos                          ║');
            $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        }
        $this->newLine();
    }
}
