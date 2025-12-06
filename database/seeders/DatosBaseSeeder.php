<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Membresia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER MAESTRO: Carga TODOS los datos base del sistema
 * 
 * Incluye:
 * 1. Roles (Administrador, Recepcionista)
 * 2. Estados (Membresías, Pagos, Convenios, Clientes, Notificaciones)
 * 3. Métodos de Pago
 * 4. Motivos de Descuento
 * 5. Membresías
 * 6. Precios de Membresías
 * 7. Convenios base
 * 8. Plantillas de Notificaciones (PROGYM con soporte apoderados)
 * 9. Usuarios del sistema (Admin, Recepcionista)
 */
class DatosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Cargando datos base del sistema PROGYM...');
        $this->command->newLine();

        // 1. ROLES
        $this->cargarRoles();
        
        // 2. ESTADOS
        $this->cargarEstados();
        
        // 3. MÉTODOS DE PAGO
        $this->cargarMetodosPago();
        
        // 4. MOTIVOS DE DESCUENTO
        $this->cargarMotivosDescuento();
        
        // 5. MEMBRESÍAS
        $this->cargarMembresias();
        
        // 6. PRECIOS DE MEMBRESÍAS
        $this->cargarPreciosMembresias();
        
        // 7. CONVENIOS
        $this->cargarConvenios();
        
        // 8. PLANTILLAS DE NOTIFICACIONES
        $this->cargarPlantillasNotificaciones();
        
        // 9. USUARIOS DEL SISTEMA
        $this->cargarUsuarios();

        $this->command->newLine();
        $this->command->info('🎉 ¡Datos base cargados correctamente!');
    }

    private function cargarRoles(): void
    {
        $this->command->info('👥 Cargando roles...');
        
        DB::table('roles')->insertOrIgnore([
            [
                'id' => 1,
                'nombre' => 'Administrador',
                'descripcion' => 'Control total del sistema',
                'permisos' => json_encode(['*']),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Recepcionista',
                'descripcion' => 'Registro de clientes y pagos',
                'permisos' => json_encode([
                    'ver_clientes',
                    'crear_cliente',
                    'editar_cliente',
                    'ver_pagos',
                    'registrar_pago',
                ]),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function cargarEstados(): void
    {
        $this->command->info('📊 Cargando estados...');
        
        $estados = [
            // MEMBRESÍAS (100-199)
            ['codigo' => 100, 'nombre' => 'Activa', 'descripcion' => 'Membresía activa', 'categoria' => 'membresia', 'color' => 'success'],
            ['codigo' => 101, 'nombre' => 'Pausada', 'descripcion' => 'Membresía pausada temporalmente', 'categoria' => 'membresia', 'color' => 'warning'],
            ['codigo' => 102, 'nombre' => 'Vencida', 'descripcion' => 'Membresía vencida', 'categoria' => 'membresia', 'color' => 'danger'],
            ['codigo' => 103, 'nombre' => 'Cancelada', 'descripcion' => 'Membresía cancelada', 'categoria' => 'membresia', 'color' => 'secondary'],
            ['codigo' => 105, 'nombre' => 'Cambiada', 'descripcion' => 'Membresía cambiada por upgrade', 'categoria' => 'membresia', 'color' => 'info'],

            // PAGOS (200-299)
            ['codigo' => 200, 'nombre' => 'Pendiente', 'descripcion' => 'Pago pendiente', 'categoria' => 'pago', 'color' => 'warning'],
            ['codigo' => 201, 'nombre' => 'Pagado', 'descripcion' => 'Pago completado', 'categoria' => 'pago', 'color' => 'success'],
            ['codigo' => 202, 'nombre' => 'Parcial', 'descripcion' => 'Pago parcial', 'categoria' => 'pago', 'color' => 'info'],
            ['codigo' => 205, 'nombre' => 'Traspasado', 'descripcion' => 'Pago traspasado a otra inscripción', 'categoria' => 'pago', 'color' => 'purple'],

            // CONVENIOS (300-399)
            ['codigo' => 300, 'nombre' => 'Activo', 'descripcion' => 'Convenio activo', 'categoria' => 'convenio', 'color' => 'success'],
            ['codigo' => 301, 'nombre' => 'Inactivo', 'descripcion' => 'Convenio inactivo', 'categoria' => 'convenio', 'color' => 'secondary'],
            ['codigo' => 302, 'nombre' => 'Vencido', 'descripcion' => 'Convenio vencido', 'categoria' => 'convenio', 'color' => 'danger'],

            // CLIENTES (400-499)
            ['codigo' => 400, 'nombre' => 'Activo', 'descripcion' => 'Cliente activo', 'categoria' => 'cliente', 'color' => 'success'],
            ['codigo' => 401, 'nombre' => 'Inactivo', 'descripcion' => 'Cliente inactivo', 'categoria' => 'cliente', 'color' => 'secondary'],
            ['codigo' => 402, 'nombre' => 'Suspendido', 'descripcion' => 'Cliente suspendido temporalmente', 'categoria' => 'cliente', 'color' => 'warning'],

            // GENÉRICOS (500-599)
            ['codigo' => 500, 'nombre' => 'Activo', 'descripcion' => 'Registro activo', 'categoria' => 'generico', 'color' => 'success'],
            ['codigo' => 501, 'nombre' => 'Inactivo', 'descripcion' => 'Registro inactivo', 'categoria' => 'generico', 'color' => 'secondary'],

            // NOTIFICACIONES (600-699)
            ['codigo' => 600, 'nombre' => 'Pendiente', 'descripcion' => 'Notificación pendiente de envío', 'categoria' => 'notificacion', 'color' => 'warning'],
            ['codigo' => 601, 'nombre' => 'Enviada', 'descripcion' => 'Notificación enviada exitosamente', 'categoria' => 'notificacion', 'color' => 'success'],
            ['codigo' => 602, 'nombre' => 'Fallida', 'descripcion' => 'Error al enviar notificación', 'categoria' => 'notificacion', 'color' => 'danger'],
            ['codigo' => 603, 'nombre' => 'Cancelada', 'descripcion' => 'Notificación cancelada', 'categoria' => 'notificacion', 'color' => 'secondary'],
        ];

        foreach ($estados as $estado) {
            DB::table('estados')->updateOrInsert(
                ['codigo' => $estado['codigo']],
                array_merge($estado, [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function cargarMetodosPago(): void
    {
        $this->command->info('💳 Cargando métodos de pago...');
        
        DB::table('metodos_pago')->insertOrIgnore([
            [
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo en el gimnasio',
                'requiere_comprobante' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Transferencia',
                'descripcion' => 'Transferencia bancaria',
                'requiere_comprobante' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tarjeta',
                'descripcion' => 'Tarjeta de débito o crédito',
                'requiere_comprobante' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function cargarMotivosDescuento(): void
    {
        $this->command->info('🏷️  Cargando motivos de descuento...');
        
        DB::table('motivos_descuento')->insertOrIgnore([
            ['nombre' => 'Convenio Estudiante', 'descripcion' => 'Descuento por convenio con institución educativa', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Promoción Mensual', 'descripcion' => 'Oferta promocional del mes', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cliente Frecuente', 'descripcion' => 'Descuento por fidelidad', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Acuerdo Especial', 'descripcion' => 'Negociación directa', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otro', 'descripcion' => 'Motivo no especificado', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function cargarMembresias(): void
    {
        $this->command->info('🏋️  Cargando membresías...');
        
        $membresias = [
            ['nombre' => 'Anual', 'duracion_meses' => 12, 'duracion_dias' => 365, 'max_pausas' => 3, 'descripcion' => 'Membresía válida por 12 meses'],
            ['nombre' => 'Semestral', 'duracion_meses' => 6, 'duracion_dias' => 180, 'max_pausas' => 2, 'descripcion' => 'Membresía válida por 6 meses'],
            ['nombre' => 'Trimestral', 'duracion_meses' => 3, 'duracion_dias' => 90, 'max_pausas' => 1, 'descripcion' => 'Membresía válida por 3 meses'],
            ['nombre' => 'Mensual', 'duracion_meses' => 1, 'duracion_dias' => 30, 'max_pausas' => 1, 'descripcion' => 'Membresía válida por 1 mes'],
            ['nombre' => 'Pase Diario', 'duracion_meses' => 0, 'duracion_dias' => 1, 'max_pausas' => 0, 'descripcion' => 'Acceso por un solo día'],
        ];

        foreach ($membresias as $membresia) {
            Membresia::firstOrCreate(
                ['nombre' => $membresia['nombre']],
                array_merge($membresia, ['activo' => true])
            );
        }
    }

    private function cargarPreciosMembresias(): void
    {
        $this->command->info('💰 Cargando precios de membresías...');
        
        $membresias = Membresia::all();
        $precios = [
            'Anual' => 180000,
            'Semestral' => 100000,
            'Trimestral' => 55000,
            'Mensual' => 20000,
            'Pase Diario' => 3000,
        ];

        foreach ($membresias as $membresia) {
            DB::table('precios_membresias')->updateOrInsert(
                [
                    'id_membresia' => $membresia->id,
                    'activo' => true,
                ],
                [
                    'precio' => $precios[$membresia->nombre] ?? 0,
                    'fecha_vigencia_desde' => now(),
                    'fecha_vigencia_hasta' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function cargarConvenios(): void
    {
        $this->command->info('🤝 Cargando convenios base...');
        
        DB::table('convenios')->insertOrIgnore([
            [
                'nombre' => 'Sin Convenio',
                'descripcion' => 'Cliente sin convenio especial',
                'porcentaje_descuento' => 0,
                'id_estado' => 300, // Activo
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function cargarPlantillasNotificaciones(): void
    {
        $this->command->info('📧 Cargando plantillas de notificaciones...');
        
        // Plantilla base PROGYM
        $plantillaBase = function($titulo, $contenido, $footer = '') {
            return '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2>
        ' . $contenido . '
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">📞 Llámanos: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">
            También en recepción: progymlosangeles@gmail.com
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.5;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/data=!4m2!3m1!1s0x0:0xcd2de1ceea2bbcf1?sa=X&ved=1t:2428&ictx=111" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
            📧 progymlosangeles@gmail.com | 📞 +56 9 5096 3143
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a>
        </p>
        <p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">
            Este es un correo automático, por favor no responder directamente.
        </p>
    </div>
</div>';
        };

        $tipos = [
            [
                'codigo' => 'membresia_por_vencer',
                'nombre' => 'Membresía por Vencer',
                'descripcion' => 'Se envía X días antes del vencimiento (soporte apoderados)',
                'asunto_email' => '⏰ {nombre}, la membresía de {nombre_cliente} en PROGYM vence en {dias_restantes} días',
                'plantilla_email' => $plantillaBase('', '
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            La membresía <strong style="color: #101010;">{membresia}</strong> vence en <strong style="color: #FFC107;">{dias_restantes} días</strong>.
        </p>
        
        <div style="background: #fffbf0; border: 2px solid #FFC107; padding: 18px; margin: 20px 0; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0 0 8px 0; color: #101010; font-size: 20px; font-weight: bold;">⏳ Vence: {fecha_vencimiento}</h3>
            <p style="margin: 0; color: #505050; font-size: 14px;">Renueva para seguir entrenando sin interrupciones 💪</p>
        </div>'),
                'dias_anticipacion' => 5,
            ],
            [
                'codigo' => 'membresia_vencida',
                'nombre' => 'Membresía Vencida',
                'descripcion' => 'Se envía cuando la membresía ha vencido (soporte apoderados)',
                'asunto_email' => '❗ {nombre}, la membresía de {nombre_cliente} en PROGYM ha vencido',
                'plantilla_email' => $plantillaBase('', '
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            La membresía <strong style="color: #101010;">{membresia}</strong> venció el <strong style="color: #E0001A;">{fecha_vencimiento}</strong>. ¡Los extrañamos!
        </p>
        
        <div style="background: #fff5f5; border-left: 4px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 8px 0; color: #E0001A; font-size: 18px; font-weight: bold;">⚠️ Membresía Vencida</h3>
            <p style="margin: 0; color: #505050; font-size: 14px;">Para reactivar, comunícate con nosotros</p>
        </div>'),
                'dias_anticipacion' => 0,
            ],
            [
                'codigo' => 'pago_pendiente',
                'nombre' => 'Pago Pendiente',
                'descripcion' => 'Recordatorio de pago pendiente o parcial',
                'asunto_email' => '💳 {nombre}, tienes un pago pendiente en PROGYM',
                'plantilla_email' => $plantillaBase('', '
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            Tienes un pago pendiente por tu membresía <strong style="color: #101010;">{membresia}</strong>.
        </p>
        
        <div style="background: #FFFFFF; border-left: 6px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 6px; border: 1px solid #C7C7C7;">
            <h3 style="margin: 0 0 8px 0; color: #101010; font-size: 18px; font-weight: bold;">💰 Saldo Pendiente</h3>
            <p style="margin: 0; color: #E0001A; font-size: 26px; font-weight: bold;">${monto_pendiente}</p>
            <p style="margin: 8px 0 0 0; color: #505050; font-size: 13px;">Total: ${monto_total} • Vence: {fecha_vencimiento}</p>
        </div>'),
                'dias_anticipacion' => 0,
            ],
            [
                'codigo' => 'bienvenida',
                'nombre' => 'Bienvenida',
                'descripcion' => 'Email de bienvenida al inscribirse (soporte apoderados)',
                'asunto_email' => '🎉 Bienvenido/a {nombre} a PROGYM - ¡Comienza tu transformación!',
                'plantilla_email' => $plantillaBase('', '
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 26px; font-weight: bold; text-align: center;">¡Bienvenido/a a la familia PROGYM! 🎉</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0; text-align: center;">
            Estamos felices de tenerte con nosotros 💪
        </p>
        
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 20px; margin: 20px 0; border-radius: 10px; border: 2px solid #101010;">
            <h3 style="margin: 0 0 12px 0; color: #101010; font-size: 18px; font-weight: bold; text-align: center;">📋 Detalles de tu Membresía</h3>
            <p style="margin: 5px 0; color: #505050; font-size: 14px;"><strong>Membresía:</strong> {membresia}</p>
            <p style="margin: 5px 0; color: #505050; font-size: 14px;"><strong>Inicio:</strong> {fecha_inicio}</p>
            <p style="margin: 5px 0; color: #E0001A; font-size: 14px; font-weight: bold;"><strong>Vencimiento:</strong> {fecha_vencimiento}</p>
        </div>'),
                'dias_anticipacion' => 0,
            ],
            [
                'codigo' => 'renovacion',
                'nombre' => 'Renovación Exitosa',
                'descripcion' => 'Confirmación de renovación de membresía',
                'asunto_email' => '🎊 {nombre}, tu membresía en PROGYM ha sido renovada',
                'plantilla_email' => $plantillaBase('', '
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            Tu membresía <strong style="color: #101010;">{membresia}</strong> ha sido renovada exitosamente.
        </p>
        
        <div style="background: #d4edda; border: 2px solid #2EB872; padding: 18px; margin: 20px 0; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0 0 8px 0; color: #2EB872; font-size: 20px; font-weight: bold;">✅ Renovación Exitosa</h3>
            <p style="margin: 0; color: #505050; font-size: 14px;">Nueva fecha de vencimiento: <strong>{fecha_vencimiento}</strong></p>
        </div>'),
                'dias_anticipacion' => 0,
            ],
            [
                'codigo' => 'manual',
                'nombre' => 'Notificación Manual',
                'descripcion' => 'Para envíos personalizados desde el sistema',
                'asunto_email' => '{asunto}',
                'plantilla_email' => $plantillaBase('', '
        <div style="color: #505050; font-size: 15px; line-height: 1.6;">
            {mensaje}
        </div>'),
                'dias_anticipacion' => 0,
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_notificaciones')->updateOrInsert(
                ['codigo' => $tipo['codigo']],
                array_merge($tipo, [
                    'activo' => true,
                    'enviar_email' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function cargarUsuarios(): void
    {
        $this->command->info('👤 Cargando usuarios del sistema...');
        
        User::firstOrCreate(
            ['email' => 'admin@progym.cl'],
            [
                'name' => 'Administrador',
                'id_rol' => 1,
                'password' => bcrypt('admin123'), // Cambiar en producción
            ]
        );

        User::firstOrCreate(
            ['email' => 'recepcion@progym.cl'],
            [
                'name' => 'Recepcionista',
                'id_rol' => 2,
                'password' => bcrypt('recepcion123'), // Cambiar en producción
            ]
        );
    }
}
