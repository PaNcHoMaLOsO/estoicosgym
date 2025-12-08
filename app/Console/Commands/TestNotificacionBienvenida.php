<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Models\TipoNotificacion;
use App\Models\Notificacion;
use App\Services\NotificacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Resend\Laravel\Facades\Resend;

class TestNotificacionBienvenida extends Command
{
    protected $signature = 'test:notificacion-bienvenida {email}';
    
    protected $description = 'Test completo: Crea cliente, inscripción, pago y verifica si se envía email de bienvenida automáticamente';

    public function handle()
    {
        $this->info('');
        $this->info('🧪 TEST: Notificación de Bienvenida Automática');
        $this->info('═══════════════════════════════════════════════');
        $this->info('');

        $email = $this->argument('email');

        try {
            DB::beginTransaction();

            // PASO 1: Crear cliente de prueba
            $this->info('📝 PASO 1: Creando cliente de prueba...');
            $rut = rand(10000000, 20000000) . '-' . rand(0, 9);
            $cliente = Cliente::create([
                'nombres' => 'TEST',
                'apellido_paterno' => 'CLIENTE',
                'apellido_materno' => 'BIENVENIDA',
                'run_pasaporte' => $rut,
                'email' => $email,
                'celular' => '+56912345678',
                'fecha_nacimiento' => '1990-01-01',
                'activo' => true,
            ]);
            $this->line("   ✅ Cliente creado: ID {$cliente->id} - {$cliente->nombre_completo}");
            $this->info('');

            // PASO 2: Crear inscripción
            $this->info('📋 PASO 2: Creando inscripción...');
            $membresia = Membresia::where('activo', true)->first();
            
            if (!$membresia) {
                throw new \Exception('No hay membresías activas en el sistema');
            }

            $fechaInicio = Carbon::today();
            $fechaVencimiento = $fechaInicio->copy()->addDays($membresia->duracion_dias);

            $precioMembresia = $membresia->precios()->orderBy('id', 'desc')->first();
            
            if (!$precioMembresia) {
                throw new \Exception("No hay precios definidos para la membresía {$membresia->nombre}");
            }
            
            $precioBase = $precioMembresia->precio_normal;
            
            $inscripcion = Inscripcion::create([
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresia->id,
                'id_precio_acordado' => $precioMembresia ? $precioMembresia->id : null,
                'fecha_inscripcion' => Carbon::now(),
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $precioBase,
                'precio_final' => $precioBase,
                'monto_total' => $precioBase,
                'saldo_pendiente' => 0,
                'id_estado' => 100, // Activa
            ]);
            $this->line("   ✅ Inscripción creada: ID {$inscripcion->id}");
            $this->line("   📅 Fecha inicio: {$fechaInicio->format('d/m/Y')}");
            $this->line("   📅 Fecha vencimiento: {$fechaVencimiento->format('d/m/Y')}");
            $this->line("   💰 Monto: \${$membresia->precio_actual}");
            $this->info('');

            // PASO 3: Crear pago
            $this->info('💳 PASO 3: Creando pago completo...');
            $metodoPago = MetodoPago::where('activo', true)->first();
            
            $pago = Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precioBase,
                'monto_abonado' => $precioBase,
                'monto_pendiente' => 0,
                'fecha_pago' => Carbon::now(),
                'id_estado' => 200, // Completo
            ]);
            $this->line("   ✅ Pago creado: ID {$pago->id} - Estado: Completo");
            $this->info('');

            DB::commit();

            // PASO 4: Verificar si existe tipo de notificación de bienvenida
            $this->info('🔍 PASO 4: Verificando configuración de notificación de bienvenida...');
            $tipoBienvenida = TipoNotificacion::where('codigo', TipoNotificacion::BIENVENIDA)
                ->first();

            if (!$tipoBienvenida) {
                $this->error('   ❌ No existe el tipo de notificación de BIENVENIDA');
                $this->warn('   ⚠️  El sistema NO enviará emails de bienvenida automáticamente');
                $this->info('');
                $this->info('📝 Para activar notificaciones de bienvenida:');
                $this->line('   1. Crear tipo de notificación con código "bienvenida"');
                $this->line('   2. Implementar trigger en InscripcionController después de crear inscripción');
                $this->info('');
            } else {
                $this->line("   ✅ Tipo encontrado: {$tipoBienvenida->nombre}");
                $this->line("   📧 Activo: " . ($tipoBienvenida->activo ? 'SÍ' : 'NO'));
                $this->line("   🎯 Automático: " . ($tipoBienvenida->automatico ? 'SÍ' : 'NO'));
                $this->info('');

                // PASO 5: Buscar si se creó notificación automáticamente
                $this->info('🔍 PASO 5: Buscando notificación automática creada...');
                $notificacion = Notificacion::where('id_inscripcion', $inscripcion->id)
                    ->where('id_tipo_notificacion', $tipoBienvenida->id)
                    ->first();

                if ($notificacion) {
                    $this->line("   ✅ Notificación encontrada: ID {$notificacion->id}");
                    $this->line("   📊 Estado: {$notificacion->estado->nombre}");
                    $this->warn('   ⚠️  PERO la notificación fue creada manualmente, NO automáticamente');
                } else {
                    $this->error('   ❌ NO se creó notificación automáticamente');
                    $this->warn('   ⚠️  El sistema NO está enviando emails de bienvenida al crear clientes');
                }
                $this->info('');
            }

            // PASO 6: Usar el servicio de notificaciones REAL
            $this->info('📧 PASO 6: Enviando email de bienvenida usando NotificacionService...');
            
            try {
                $notificacionService = app(NotificacionService::class);
                $resultado = $notificacionService->enviarNotificacionBienvenida($inscripcion);
                
                if ($resultado['enviada']) {
                    $this->line("   ✅ Email enviado exitosamente");
                    $this->line("   📧 Destino: {$email}");
                    $this->line("   💾 Notificación ID: {$resultado['notificacion_id']}");
                    $this->line("   📊 Datos del email:");
                    $this->line("      • Cliente: {$cliente->nombre_completo}");
                    $this->line("      • Membresía: {$membresia->nombre}");
                    $this->line("      • Precio: \$" . number_format($inscripcion->precio_final, 0, ',', '.'));
                    $this->line("      • Fecha inicio: {$fechaInicio->format('d/m/Y')}");
                    $this->line("      • Fecha vencimiento: {$fechaVencimiento->format('d/m/Y')}");
                    $this->line("      • Tipo pago: " . ($pago->monto_pendiente > 0 ? 'Parcial' : 'Completo'));
                    $this->line("      • Monto pagado: \$" . number_format($pago->monto_abonado, 0, ',', '.'));
                    $this->line("      • Saldo: \$" . number_format($pago->monto_pendiente, 0, ',', '.'));
                } else {
                    $this->error("   ❌ Error: {$resultado['mensaje']}");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error al enviar: {$e->getMessage()}");
            }
            
            $this->info('');

            // RESUMEN FINAL
            $this->info('');
            $this->info('═══════════════════════════════════════════════');
            $this->info('📊 RESUMEN DEL TEST:');
            $this->info('═══════════════════════════════════════════════');
            $this->line('');
            $this->line("✅ Cliente creado: {$cliente->nombre_completo}");
            $this->line("✅ Inscripción creada: {$membresia->nombre}");
            $this->line("✅ Pago registrado: \${$membresia->precio_actual}");
            $this->line("✅ Email enviado MANUALMENTE a: {$email}");
            $this->line('');
            
            if (!$tipoBienvenida || !$tipoBienvenida->automatico) {
                $this->error('❌ PROBLEMA DETECTADO:');
                $this->warn('   El sistema NO envía emails de bienvenida automáticamente');
                $this->warn('   cuando se crea un cliente nuevo.');
                $this->line('');
                $this->info('💡 SOLUCIÓN RECOMENDADA:');
                $this->line('   1. Activar tipo de notificación "bienvenida"');
                $this->line('   2. Implementar trigger en InscripcionController->store()');
                $this->line('   3. Llamar a NotificacionService->enviarNotificacionBienvenida($inscripcion)');
            } else {
                $notificacion = Notificacion::where('id_inscripcion', $inscripcion->id)
                    ->where('id_tipo_notificacion', $tipoBienvenida->id)
                    ->first();
                    
                if (!$notificacion) {
                    $this->error('❌ PROBLEMA DETECTADO:');
                    $this->warn('   Existe tipo de notificación pero NO se creó automáticamente');
                    $this->line('');
                    $this->info('💡 SOLUCIÓN:');
                    $this->line('   Agregar código en InscripcionController después de crear inscripción:');
                    $this->line('   $notificacionService->enviarNotificacionBienvenida($inscripcion);');
                }
            }
            $this->line('');
            $this->info('🧹 Limpieza: Eliminar datos de prueba con:');
            $this->line("   Cliente ID: {$cliente->id}");
            $this->line("   php artisan tinker");
            $this->line("   Cliente::find({$cliente->id})->delete();");
            $this->info('');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('');
            $this->error('❌ ERROR: ' . $e->getMessage());
            $this->error('');
            return 1;
        }

        return 0;
    }
}
