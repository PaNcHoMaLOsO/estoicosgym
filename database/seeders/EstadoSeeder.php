<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        // Usar insertOrIgnore para evitar errores si los estados ya existen
        DB::table('estados')->insertOrIgnore([
            // ===== RANGO 100-199: ESTADOS DE MEMBRESÍAS =====
            // Códigos: 100=Activa, 101=Pausada, 102=Vencida, 103=Cancelada, 104=Suspendida, 105=Cambiada, 106=Traspasada
            
            [
                'codigo' => 100,
                'nombre' => 'Activa',
                'descripcion' => 'Membresía vigente y activa',
                'categoria' => 'membresia',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 101,
                'nombre' => 'Pausada',
                'descripcion' => 'Membresía pausada temporalmente',
                'categoria' => 'membresia',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 102,
                'nombre' => 'Vencida',
                'descripcion' => 'Membresía expirada',
                'categoria' => 'membresia',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 103,
                'nombre' => 'Cancelada',
                'descripcion' => 'Membresía cancelada',
                'categoria' => 'membresia',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 104,
                'nombre' => 'Suspendida',
                'descripcion' => 'Membresía suspendida por deuda',
                'categoria' => 'membresia',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 105,
                'nombre' => 'Cambiada',
                'descripcion' => 'Membresía cambiada a otro plan (upgrade/downgrade)',
                'categoria' => 'membresia',
                'color' => 'info',
                'activo' => true,
            ],
            [
                'codigo' => 106,
                'nombre' => 'Traspasada',
                'descripcion' => 'Membresía traspasada a otro cliente',
                'categoria' => 'membresia',
                'color' => 'purple',
                'activo' => true,
            ],
            
            // ===== RANGO 200-299: ESTADOS DE PAGOS =====
            // Códigos: 200=Pendiente, 201=Pagado, 202=Parcial, 203=Vencido, 204=Cancelado, 205=Traspasado
            
            [
                'codigo' => 200,
                'nombre' => 'Pendiente',
                'descripcion' => 'Pago pendiente de realizar',
                'categoria' => 'pago',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 201,
                'nombre' => 'Pagado',
                'descripcion' => 'Pago completado',
                'categoria' => 'pago',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 202,
                'nombre' => 'Parcial',
                'descripcion' => 'Pago parcial, saldo pendiente',
                'categoria' => 'pago',
                'color' => 'info',
                'activo' => true,
            ],
            [
                'codigo' => 203,
                'nombre' => 'Vencido',
                'descripcion' => 'Pago vencido sin realizar',
                'categoria' => 'pago',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 204,
                'nombre' => 'Cancelado',
                'descripcion' => 'Pago cancelado',
                'categoria' => 'pago',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 205,
                'nombre' => 'Traspasado',
                'descripcion' => 'Pago traspasado a otra inscripción',
                'categoria' => 'pago',
                'color' => 'purple',
                'activo' => true,
            ],
            
            // ===== RANGO 300-302: ESTADOS DE CONVENIOS (3 ESPECÍFICOS) =====
            // Códigos: 300=Activo, 301=Suspendido, 302=Cancelado
            
            [
                'codigo' => 300,
                'nombre' => 'Activo',
                'descripcion' => 'Convenio activo y vigente',
                'categoria' => 'convenio',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 301,
                'nombre' => 'Suspendido',
                'descripcion' => 'Convenio temporalmente suspendido',
                'categoria' => 'convenio',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 302,
                'nombre' => 'Cancelado',
                'descripcion' => 'Convenio cancelado',
                'categoria' => 'convenio',
                'color' => 'secondary',
                'activo' => true,
            ],
            
            // ===== RANGO 400-402: ESTADOS DE CLIENTES (3 ESPECÍFICOS) =====
            // Códigos: 400=Activo, 401=Suspendido, 402=Cancelado
            
            [
                'codigo' => 400,
                'nombre' => 'Activo',
                'descripcion' => 'Cliente activo',
                'categoria' => 'cliente',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 401,
                'nombre' => 'Suspendido',
                'descripcion' => 'Cliente suspendido',
                'categoria' => 'cliente',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 402,
                'nombre' => 'Cancelado',
                'descripcion' => 'Cliente cancelado',
                'categoria' => 'cliente',
                'color' => 'secondary',
                'activo' => true,
            ],
            
            // ===== RANGO 500-504: ESTADOS GENÉRICOS REUTILIZABLES =====
            // Para cualquier módulo futuro que necesite estados básicos
            // Códigos: 500=Activo, 501=Suspendido, 502=Cancelado, 503=Inactivo, 504=Vencido
            
            [
                'codigo' => 500,
                'nombre' => 'Activo',
                'descripcion' => 'Recurso activo',
                'categoria' => 'generico',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 501,
                'nombre' => 'Suspendido',
                'descripcion' => 'Recurso suspendido',
                'categoria' => 'generico',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 502,
                'nombre' => 'Cancelado',
                'descripcion' => 'Recurso cancelado',
                'categoria' => 'generico',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 503,
                'nombre' => 'Inactivo',
                'descripcion' => 'Recurso inactivo',
                'categoria' => 'generico',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 504,
                'nombre' => 'Vencido',
                'descripcion' => 'Recurso vencido',
                'categoria' => 'generico',
                'color' => 'danger',
                'activo' => true,
            ],
            
            // ===== RANGO 600-603: ESTADOS DE NOTIFICACIONES =====
            // Códigos: 600=Pendiente, 601=Enviada, 602=Fallida, 603=Cancelada
            
            [
                'codigo' => 600,
                'nombre' => 'Pendiente',
                'descripcion' => 'Notificación programada pendiente de envío',
                'categoria' => 'notificacion',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 601,
                'nombre' => 'Enviada',
                'descripcion' => 'Notificación enviada exitosamente',
                'categoria' => 'notificacion',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 602,
                'nombre' => 'Fallida',
                'descripcion' => 'Error al enviar la notificación',
                'categoria' => 'notificacion',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 603,
                'nombre' => 'Cancelada',
                'descripcion' => 'Notificación cancelada manualmente',
                'categoria' => 'notificacion',
                'color' => 'secondary',
                'activo' => true,
            ],
        ]);
    }
}
