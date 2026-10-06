<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Services\NotificacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestNotificacionTutorLegal extends Command
{
    protected $signature = 'test:notificacion-tutor {email}';
    protected $description = 'Prueba el envío de notificación de confirmación de tutor legal';

    public function handle()
    {
        $emailTutor = $this->argument('email');
        
        $this->info('');
        $this->info('🧪 TEST: Notificación de Confirmación de Tutor Legal');
        $this->info('═══════════════════════════════════════════════════');
        $this->info('');

        DB::beginTransaction();

        try {
            // PASO 1: Crear cliente menor de edad con tutor
            $this->info('📝 PASO 1: Creando cliente menor de edad...');
            
            $runRandom = rand(20000000, 29999999) . '-' . rand(0, 9);
            $runTutor = rand(10000000, 19999999) . '-' . rand(0, 9);
            
            $cliente = Cliente::create([
                'run_pasaporte' => $runRandom,
                'nombres' => 'Juanito Test',
                'apellido_paterno' => 'Menor',
                'apellido_materno' => 'Legal',
                'email' => 'menor_' . time() . '@test.com',
                'celular' => '+56912345678',
                'telefono' => '+56912345678',
                'fecha_nacimiento' => Carbon::now()->subYears(15)->format('Y-m-d'), // 15 años
                'direccion' => 'Test 123',
                'activo' => true,
                'es_menor_edad' => true,
                'apoderado_nombre' => 'María González Test',
                'apoderado_rut' => $runTutor,
                'apoderado_email' => $emailTutor,
                'apoderado_telefono' => '+56987654321',
            ]);
            
            $this->line("   ✅ Cliente menor creado: ID {$cliente->id} - {$cliente->nombre_completo}");
            $this->line("   👤 Tutor: {$cliente->apoderado_nombre}");
            $this->line("   📧 Email tutor: {$emailTutor}");
            $this->info('');

            // PASO 2: Usar una inscripción existente y actualizar el cliente
            $this->info('📋 PASO 2: Buscando inscripción de ejemplo...');
            $inscripcion = Inscripcion::with(['membresia'])->orderBy('id', 'desc')->first();
            
            if (!$inscripcion) {
                throw new \Exception('No hay inscripciones en la base de datos');
            }
            
            // Actualizar el cliente de la inscripción temporal
            $clienteOriginal = $inscripcion->id_cliente;
            $inscripcion->id_cliente = $cliente->id;
            $inscripcion->save();
            
            $this->line("   ✅ Inscripción asignada: ID {$inscripcion->id}");
            $this->line("   📋 Membresía: {$inscripcion->membresia->nombre}");
            $this->line("   📅 Fecha inicio: " . Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'));
            $this->line("   📅 Fecha vencimiento: " . Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'));
            $this->line("   💰 Precio: \$" . number_format($inscripcion->precio_final, 0, ',', '.'));
            $this->info('');

            DB::commit();

            // PASO 3: Enviar notificaciones
            $this->info('📧 PASO 3: Enviando notificaciones...');
            
            $notificacionService = app(NotificacionService::class);
            
            // 4.1: Notificación de bienvenida al cliente menor
            $this->line('   📨 Enviando bienvenida al cliente menor...');
            $resultadoBienvenida = $notificacionService->enviarNotificacionBienvenida($inscripcion);
            
            if ($resultadoBienvenida['enviada']) {
                $this->line("   ✅ Bienvenida enviada a: {$cliente->email}");
            } else {
                $this->warn("   ⚠️  Bienvenida no enviada: {$resultadoBienvenida['mensaje']}");
            }
            
            // 4.2: Notificación de confirmación al tutor legal
            $this->line('   📨 Enviando confirmación al tutor legal...');
            $resultadoTutor = $notificacionService->enviarNotificacionTutorLegal($inscripcion);
            
            if ($resultadoTutor['enviada']) {
                $this->line("   ✅ Confirmación enviada al tutor: {$emailTutor}");
                $this->line("   🆔 ID de notificación: {$resultadoTutor['notificacion_id']}");
            } else {
                $this->error("   ❌ Error: {$resultadoTutor['mensaje']}");
            }
            
            $this->info('');

            // RESUMEN
            $this->info('═══════════════════════════════════════════════════');
            $this->info('📊 RESUMEN DEL TEST');
            $this->info('═══════════════════════════════════════════════════');
            $this->info('');
            $this->line("✅ Cliente menor creado: {$cliente->nombre_completo}");
            $this->line("✅ Tutor legal: {$cliente->apoderado_nombre}");
            $this->line("✅ Inscripción: {$inscripcion->membresia->nombre}");
            $this->line("✅ Notificaciones enviadas:");
            $this->line("   • Bienvenida → {$cliente->email}");
            $this->line("   • Confirmación tutor → {$emailTutor}");
            $this->info('');
            $this->line("🔍 Verifica ambas bandejas de entrada");
            $this->info('');
            $this->info('🧹 Limpieza: Eliminar datos de prueba con:');
            $this->line("   Cliente ID: {$cliente->id}");
            $this->line("   php artisan tinker");
            $this->line("   Cliente::find({$cliente->id})->delete();");
            $this->info('');
            $this->info('═══════════════════════════════════════════════════');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('');
            $this->error('❌ ERROR: ' . $e->getMessage());
            $this->error('');
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
