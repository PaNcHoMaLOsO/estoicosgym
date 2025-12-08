<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Resend;

class TestEnviarPlantillas extends Command
{
    protected $signature = 'test:enviar-plantillas {email=estoicosgymlosangeles@gmail.com}';
    protected $description = 'Enviar todas las plantillas de email a un correo de prueba';

    public function handle()
    {
        $emailDestino = $this->argument('email');
        
        // Obtener un cliente real de ejemplo
        $cliente = DB::table('clientes')
            ->join('inscripciones', 'clientes.id', '=', 'inscripciones.id_cliente')
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->select(
                'clientes.*',
                'inscripciones.id as inscripcion_id',
                'inscripciones.fecha_inicio',
                'inscripciones.fecha_vencimiento',
                'membresias.nombre as membresia_nombre'
            )
            ->where('clientes.email', '!=', '')
            ->whereNotNull('clientes.email')
            ->first();

        if (!$cliente) {
            $this->error('No hay clientes con email registrados');
            return 1;
        }

        $this->info("Cliente de prueba: {$cliente->nombres} {$cliente->apellido_paterno}");
        $this->info("Email destino: {$emailDestino}");
        $this->newLine();

        $apiKey = env('RESEND_API_KEY');
        if (!$apiKey) {
            $this->error('RESEND_API_KEY no está configurada en .env');
            return 1;
        }
        
        $resend = Resend::client($apiKey);

        $plantillas = [
            '01_bienvenida.html',
            '02_pago_completado.html',
            '03_membresia_por_vencer.html',
            '04_membresia_vencida.html',
            '05_pausa_inscripcion.html',
            '06_activacion_inscripcion.html',
            '07_pago_pendiente.html',
            '08_renovacion.html',
            '09_confirmacion_tutor_legal.html',
            '10_horario_especial.html',
            '11_promocion.html',
            '12_anuncio.html',
            '13_evento.html',
        ];

        $enviados = 0;
        $errores = 0;

        foreach ($plantillas as $plantilla) {
            $ruta = storage_path('app/test_emails/preview/' . $plantilla);
            
            if (!file_exists($ruta)) {
                $this->warn("No se encontró: {$plantilla}");
                continue;
            }
            
            // Leer contenido
            $contenido = file_get_contents($ruta);
            
            // Extraer solo el contenido del body
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
                $contenido = $matches[1];
            }
            
            // Si hay div.container, extraer su contenido
            if (preg_match('/<div\s+class="container"[^>]*>(.*?)<\/div>\s*$/is', $contenido, $matches)) {
                $contenido = $matches[1];
            }
            
            // Reemplazar variables con datos reales del cliente
            $contenido = str_replace('{nombre}', $cliente->nombres, $contenido);
            $contenido = str_replace('{apellido}', $cliente->apellido_paterno, $contenido);
            $contenido = str_replace('{nombre_completo}', $cliente->nombres . ' ' . $cliente->apellido_paterno, $contenido);
            $contenido = str_replace('{email}', $cliente->email, $contenido);
            $contenido = str_replace('{telefono}', $cliente->telefono ?? 'No registrado', $contenido);
            $contenido = str_replace('{membresia}', $cliente->membresia_nombre, $contenido);
            $contenido = str_replace('{precio}', '30000', $contenido);
            $contenido = str_replace('{fecha_inicio}', date('d/m/Y', strtotime($cliente->fecha_inicio)), $contenido);
            $contenido = str_replace('{fecha_vencimiento}', date('d/m/Y', strtotime($cliente->fecha_vencimiento)), $contenido);
            
            // Calcular días restantes
            $diasRestantes = max(0, floor((strtotime($cliente->fecha_vencimiento) - time()) / 86400));
            $contenido = str_replace('{dias_restantes}', $diasRestantes, $contenido);
            
            // Nombre de la plantilla para el asunto
            $nombrePlantilla = str_replace(['_', '.html'], [' ', ''], $plantilla);
            $nombrePlantilla = ucwords($nombrePlantilla);
            
            try {
                // Enviar email
                $resultado = $resend->emails->send([
                    'from' => 'PROGYM Los Angeles <onboarding@resend.dev>',
                    'to' => [$emailDestino],
                    'subject' => "TEST - {$nombrePlantilla}",
                    'html' => $contenido,
                ]);
                
                $this->info("✅ Enviado: {$plantilla} (ID: {$resultado->id})");
                $enviados++;
                
                // Esperar 2 segundos entre envíos
                sleep(2);
                
            } catch (\Exception $e) {
                $this->error("❌ Error en {$plantilla}: {$e->getMessage()}");
                $errores++;
            }
        }

        $this->newLine();
        $this->info(str_repeat('=', 50));
        $this->info("RESUMEN:");
        $this->info("   ✅ Enviados: {$enviados}");
        $this->info("   ❌ Errores: {$errores}");
        $this->info("   📧 Total: " . count($plantillas));
        $this->info("   📬 Destino: {$emailDestino}");
        $this->info(str_repeat('=', 50));

        return 0;
    }
}
