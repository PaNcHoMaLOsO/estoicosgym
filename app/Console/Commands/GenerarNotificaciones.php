<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerarNotificaciones extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notificaciones:generar 
                            {--force : Forzar generación ignorando duplicados}
                            {--tipo= : Generar solo un tipo específico (membresia_por_vencer, membresia_vencida, pago_pendiente)}';

    /**
     * The console command description.
     */
    protected $description = 'Genera notificaciones automáticas para membresías por vencer, vencidas y pagos pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Iniciando generación de notificaciones automáticas...');
        $this->newLine();

        $tipoSeleccionado = $this->option('tipo');
        $force = $this->option('force');

        $estadisticas = [
            'membresias_por_vencer' => 0,
            'membresias_vencidas' => 0,
            'pagos_pendientes' => 0,
            'total' => 0,
            'duplicados_evitados' => 0,
        ];

        DB::beginTransaction();

        try {
            // 1. Membresías por vencer (3 días antes)
            if (!$tipoSeleccionado || $tipoSeleccionado === 'membresia_por_vencer') {
                $this->info('📅 Procesando membresías próximas a vencer...');
                $estadisticas['membresias_por_vencer'] = $this->generarNotificacionesMembresiasPorVencer($force);
            }

            // 2. Membresías vencidas
            if (!$tipoSeleccionado || $tipoSeleccionado === 'membresia_vencida') {
                $this->info('⚠️  Procesando membresías vencidas...');
                $estadisticas['membresias_vencidas'] = $this->generarNotificacionesMembresiasVencidas($force);
            }

            // 3. Pagos pendientes (más de 3 días sin pagar)
            if (!$tipoSeleccionado || $tipoSeleccionado === 'pago_pendiente') {
                $this->info('💰 Procesando pagos pendientes...');
                $estadisticas['pagos_pendientes'] = $this->generarNotificacionesPagosPendientes($force);
            }

            $estadisticas['total'] = $estadisticas['membresias_por_vencer'] 
                                   + $estadisticas['membresias_vencidas'] 
                                   + $estadisticas['pagos_pendientes'];

            DB::commit();

            $this->newLine();
            $this->info('✅ Generación completada');
            $this->table(
                ['Tipo', 'Cantidad'],
                [
                    ['Membresías por vencer', $estadisticas['membresias_por_vencer']],
                    ['Membresías vencidas', $estadisticas['membresias_vencidas']],
                    ['Pagos pendientes', $estadisticas['pagos_pendientes']],
                    ['─────────────────', '────────'],
                    ['TOTAL GENERADAS', $estadisticas['total']],
                ]
            );

            if ($estadisticas['duplicados_evitados'] > 0) {
                $this->warn("⚠️  Se evitaron {$estadisticas['duplicados_evitados']} notificaciones duplicadas");
            }

            Log::info('Notificaciones generadas automáticamente', $estadisticas);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error al generar notificaciones: ' . $e->getMessage());
            Log::error('Error en generación de notificaciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Genera notificaciones para membresías próximas a vencer (3 días antes)
     */
    private function generarNotificacionesMembresiasPorVencer(bool $force = false): int
    {
        $fechaLimite = Carbon::now()->addDays(3)->format('Y-m-d');
        $contador = 0;

        // Buscar inscripciones activas que vencen en 3 días
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100) // Activa
            ->whereDate('fecha_vencimiento', $fechaLimite)
            ->whereHas('cliente', function($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        $tipoNotificacion = TipoNotificacion::where('codigo', 'membresia_por_vencer')->first();

        if (!$tipoNotificacion) {
            $this->warn('⚠️  Tipo de notificación "membresia_por_vencer" no encontrado');
            return 0;
        }

        foreach ($inscripciones as $inscripcion) {
            $cliente = $inscripcion->cliente;

            // Evitar duplicados (si ya existe notificación pendiente en los últimos 7 días)
            if (!$force && $this->existeNotificacionReciente($cliente->id, $tipoNotificacion->id, $inscripcion->id, 7)) {
                continue;
            }

            $email = $cliente->es_menor_edad ? $cliente->apoderado_email : $cliente->email;

            if (!$email) {
                $this->warn("⚠️  Cliente {$cliente->nombre_completo} sin email válido");
                continue;
            }

            Notificacion::create([
                'id_tipo_notificacion' => $tipoNotificacion->id,
                'id_cliente' => $cliente->id,
                'id_inscripcion' => $inscripcion->id,
                'email_destino' => $email,
                'asunto' => "⏰ Tu membresía {$inscripcion->membresia->nombre} vence pronto",
                'contenido' => $this->generarContenidoMembresiaProxima($cliente, $inscripcion),
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => Carbon::now()->format('Y-m-d'),
                'intentos' => 0,
                'max_intentos' => 3,
                'tipo_envio' => 'automatica',
            ]);

            $contador++;
        }

        return $contador;
    }

    /**
     * Genera notificaciones para membresías vencidas
     */
    private function generarNotificacionesMembresiasVencidas(bool $force = false): int
    {
        $hoy = Carbon::now()->format('Y-m-d');
        $contador = 0;

        // Buscar inscripciones vencidas (fecha_vencimiento < hoy)
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->whereIn('id_estado', [100, 102]) // Activa o Vencida
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->whereHas('cliente', function($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        $tipoNotificacion = TipoNotificacion::where('codigo', 'membresia_vencida')->first();

        if (!$tipoNotificacion) {
            $this->warn('⚠️  Tipo de notificación "membresia_vencida" no encontrado');
            return 0;
        }

        foreach ($inscripciones as $inscripcion) {
            $cliente = $inscripcion->cliente;

            // Evitar duplicados
            if (!$force && $this->existeNotificacionReciente($cliente->id, $tipoNotificacion->id, $inscripcion->id, 7)) {
                continue;
            }

            $email = $cliente->es_menor_edad ? $cliente->apoderado_email : $cliente->email;

            if (!$email) {
                continue;
            }

            $diasVencida = Carbon::parse($inscripcion->fecha_vencimiento)->diffInDays(Carbon::now());

            Notificacion::create([
                'id_tipo_notificacion' => $tipoNotificacion->id,
                'id_cliente' => $cliente->id,
                'id_inscripcion' => $inscripcion->id,
                'email_destino' => $email,
                'asunto' => "❌ Tu membresía {$inscripcion->membresia->nombre} ha vencido",
                'contenido' => $this->generarContenidoMembresiaVencida($cliente, $inscripcion, $diasVencida),
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => Carbon::now()->format('Y-m-d'),
                'intentos' => 0,
                'max_intentos' => 3,
                'tipo_envio' => 'automatica',
            ]);

            $contador++;
        }

        return $contador;
    }

    /**
     * Genera notificaciones para pagos pendientes (más de 3 días)
     */
    private function generarNotificacionesPagosPendientes(bool $force = false): int
    {
        $fechaLimite = Carbon::now()->subDays(3)->format('Y-m-d');
        $contador = 0;

        // Buscar pagos pendientes o parciales con más de 3 días
        $pagos = Pago::with(['cliente', 'inscripcion'])
            ->whereIn('id_estado', [400, 401]) // Pendiente o Parcial
            ->whereDate('created_at', '<=', $fechaLimite)
            ->whereHas('cliente', function($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        $tipoNotificacion = TipoNotificacion::where('codigo', 'pago_pendiente')->first();

        if (!$tipoNotificacion) {
            $this->warn('⚠️  Tipo de notificación "pago_pendiente" no encontrado');
            return 0;
        }

        foreach ($pagos as $pago) {
            $cliente = $pago->cliente;

            // Evitar duplicados
            if (!$force && $this->existeNotificacionReciente($cliente->id, $tipoNotificacion->id, null, 7, $pago->id)) {
                continue;
            }

            $email = $cliente->es_menor_edad ? $cliente->apoderado_email : $cliente->email;

            if (!$email) {
                continue;
            }

            $montoPendiente = $pago->monto_total - $pago->monto_pagado;

            Notificacion::create([
                'id_tipo_notificacion' => $tipoNotificacion->id,
                'id_cliente' => $cliente->id,
                'id_inscripcion' => $pago->id_inscripcion,
                'id_pago' => $pago->id,
                'email_destino' => $email,
                'asunto' => "💰 Recordatorio: Pago pendiente por ${$montoPendiente}",
                'contenido' => $this->generarContenidoPagoPendiente($cliente, $pago, $montoPendiente),
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => Carbon::now()->format('Y-m-d'),
                'intentos' => 0,
                'max_intentos' => 3,
                'tipo_envio' => 'automatica',
            ]);

            $contador++;
        }

        return $contador;
    }

    /**
     * Verifica si ya existe una notificación reciente del mismo tipo
     */
    private function existeNotificacionReciente(
        int $idCliente, 
        int $idTipo, 
        ?int $idInscripcion = null, 
        int $dias = 7,
        ?int $idPago = null
    ): bool {
        $query = Notificacion::where('id_cliente', $idCliente)
            ->where('id_tipo_notificacion', $idTipo)
            ->where('fecha_programada', '>=', Carbon::now()->subDays($dias)->format('Y-m-d'));

        if ($idInscripcion) {
            $query->where('id_inscripcion', $idInscripcion);
        }

        if ($idPago) {
            $query->where('id_pago', $idPago);
        }

        return $query->exists();
    }

    /**
     * Genera contenido HTML para membresía próxima a vencer
     */
    private function generarContenidoMembresiaProxima(Cliente $cliente, Inscripcion $inscripcion): string
    {
        $nombre = $cliente->es_menor_edad ? $cliente->apoderado_nombre : $cliente->nombres;
        
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #e94560;">⏰ Tu membresía está por vencer</h2>
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Te recordamos que tu membresía <strong>{$inscripcion->membresia->nombre}</strong> vence el <strong>{$inscripcion->fecha_vencimiento->format('d/m/Y')}</strong>.</p>
            <p>Para continuar disfrutando de nuestras instalaciones, te invitamos a renovar tu membresía.</p>
            <p style="margin-top: 20px;">
                <strong>Detalles:</strong><br>
                - Membresía: {$inscripcion->membresia->nombre}<br>
                - Fecha de inicio: {$inscripcion->fecha_inicio->format('d/m/Y')}<br>
                - Fecha de vencimiento: {$inscripcion->fecha_vencimiento->format('d/m/Y')}
            </p>
            <p>¡Visítanos o contáctanos para renovar!</p>
            <hr>
            <small style="color: #666;">Estoicos Gym Los Ángeles - Tu templo del fitness</small>
        </div>
        HTML;
    }

    /**
     * Genera contenido HTML para membresía vencida
     */
    private function generarContenidoMembresiaVencida(Cliente $cliente, Inscripcion $inscripcion, int $diasVencida): string
    {
        $nombre = $cliente->es_menor_edad ? $cliente->apoderado_nombre : $cliente->nombres;
        
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #dc3545;">❌ Tu membresía ha vencido</h2>
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Tu membresía <strong>{$inscripcion->membresia->nombre}</strong> venció hace <strong>{$diasVencida} días</strong> ({$inscripcion->fecha_vencimiento->format('d/m/Y')}).</p>
            <p>Para poder seguir entrenando, necesitas renovar tu membresía lo antes posible.</p>
            <p>¡Te esperamos!</p>
            <hr>
            <small style="color: #666;">Estoicos Gym Los Ángeles - Tu templo del fitness</small>
        </div>
        HTML;
    }

    /**
     * Genera contenido HTML para pago pendiente
     */
    private function generarContenidoPagoPendiente(Cliente $cliente, Pago $pago, float $montoPendiente): string
    {
        $nombre = $cliente->es_menor_edad ? $cliente->apoderado_nombre : $cliente->nombres;
        
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #ffc107;">💰 Recordatorio de Pago Pendiente</h2>
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Te recordamos que tienes un pago pendiente:</p>
            <p style="margin-top: 20px;">
                <strong>Detalles del pago:</strong><br>
                - Monto total: \${$pago->monto_total}<br>
                - Monto pagado: \${$pago->monto_pagado}<br>
                - <strong>Monto pendiente: \${$montoPendiente}</strong>
            </p>
            <p>Por favor, acércate al gimnasio para regularizar tu pago.</p>
            <hr>
            <small style="color: #666;">Estoicos Gym Los Ángeles - Tu templo del fitness</small>
        </div>
        HTML;
    }
}
