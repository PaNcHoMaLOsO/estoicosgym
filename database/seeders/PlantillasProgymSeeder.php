<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER MAESTRO: Plantillas de notificaciones PROGYM
 * 
 * Plantillas HTML probadas y validadas de storage/app/test_emails/
 * - Diseño: PRO (blanco) + GYM (rojo) en fondo negro #101010
 * - Sin imágenes, solo texto HTML
 * - Soporte para apoderados en plantillas aplicables
 */
class PlantillasProgymSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📧 Cargando plantillas PROGYM...');

        // PLANTILLA 1: BIENVENIDA
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'bienvenida'],
            [
                'nombre' => 'Bienvenida',
                'descripcion' => 'Email de bienvenida al inscribirse (incluye detalles de pago)',
                'asunto_email' => '🎉 Bienvenido/a {nombre} a PROGYM - ¡Comienza tu transformación!',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/01_bienvenida.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 2: PAGO COMPLETADO
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'pago_completado'],
            [
                'nombre' => 'Pago Completado',
                'descripcion' => 'Confirmación cuando se completa el pago de la membresía',
                'asunto_email' => '✅ {nombre}, tu pago ha sido registrado - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/02_pago_completado.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 3: MEMBRESÍA POR VENCER
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'membresia_por_vencer'],
            [
                'nombre' => 'Membresía por Vencer',
                'descripcion' => 'Recordatorio X días antes del vencimiento (soporte apoderados)',
                'asunto_email' => '⏰ {nombre}, la membresía de {nombre_cliente} vence en {dias_restantes} días',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/03_membresia_por_vencer.html')),
                'dias_anticipacion' => 5,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 4: MEMBRESÍA VENCIDA
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'membresia_vencida'],
            [
                'nombre' => 'Membresía Vencida',
                'descripcion' => 'Notificación cuando la membresía ha vencido (soporte apoderados)',
                'asunto_email' => '❗ {nombre}, la membresía de {nombre_cliente} en PROGYM ha vencido',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/04_membresia_vencida.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 5: PAUSA INSCRIPCIÓN
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'pausa_inscripcion'],
            [
                'nombre' => 'Pausa de Inscripción',
                'descripcion' => 'Confirmación cuando el cliente pausa su membresía',
                'asunto_email' => '⏸️ {nombre}, tu membresía en PROGYM ha sido pausada',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/05_pausa_inscripcion.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 6: ACTIVACIÓN INSCRIPCIÓN
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'activacion_inscripcion'],
            [
                'nombre' => 'Activación de Inscripción',
                'descripcion' => 'Confirmación cuando se reactiva la membresía pausada',
                'asunto_email' => '▶️ {nombre}, ¡Bienvenido de vuelta a PROGYM!',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/06_activacion_inscripcion.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 7: PAGO PENDIENTE
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'pago_pendiente'],
            [
                'nombre' => 'Pago Pendiente',
                'descripcion' => 'Recordatorio de saldo pendiente',
                'asunto_email' => '💳 {nombre}, tienes un saldo pendiente en PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/07_pago_pendiente.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 8: RENOVACIÓN
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'renovacion'],
            [
                'nombre' => 'Renovación Exitosa',
                'descripcion' => 'Confirmación de renovación de membresía',
                'asunto_email' => '🎊 {nombre}, tu membresía en PROGYM ha sido renovada',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/08_renovacion.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 9: CONFIRMACIÓN TUTOR LEGAL (CRÍTICA)
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'confirmacion_tutor_legal'],
            [
                'nombre' => 'Confirmación de Tutor Legal',
                'descripcion' => 'Constancia legal enviada al apoderado cuando inscribe a un menor',
                'asunto_email' => '📋 {nombre_apoderado}, confirmación de registro como Tutor Legal - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/09_confirmacion_tutor_legal.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ 9 plantillas automáticas cargadas');
        $this->command->newLine();
        $this->command->info('📝 Cargando plantillas manuales...');

        // PLANTILLA 10: HORARIO ESPECIAL (MANUAL)
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'horario_especial'],
            [
                'nombre' => 'Horario Especial',
                'descripcion' => 'Plantilla manual para anunciar cambios en horarios',
                'asunto_email' => '📅 Horario Especial - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/10_horario_especial.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 11: PROMOCIÓN (MANUAL)
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'promocion'],
            [
                'nombre' => 'Promoción Especial',
                'descripcion' => 'Plantilla manual para enviar promociones y ofertas',
                'asunto_email' => '🎁 Promoción Especial - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/11_promocion.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 12: ANUNCIO (MANUAL)
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'anuncio'],
            [
                'nombre' => 'Anuncio Importante',
                'descripcion' => 'Plantilla manual para comunicados importantes',
                'asunto_email' => '📢 Anuncio Importante - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/12_anuncio.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 13: EVENTO (MANUAL)
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'evento'],
            [
                'nombre' => 'Evento Especial',
                'descripcion' => 'Plantilla manual para invitaciones a eventos',
                'asunto_email' => '🎉 No te pierdas nuestro evento - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/preview/13_evento.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ 4 plantillas manuales cargadas');
        $this->command->newLine();
        $this->command->info('🎉 Total: 13 plantillas de notificación PROGYM listas');
    }
}
