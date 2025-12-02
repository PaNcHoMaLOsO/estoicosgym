<?php

namespace App\Enums;

/**
 * Constantes de Estados del Sistema
 * 
 * Este archivo centraliza todos los códigos de estado para evitar hardcoding
 * y facilitar el mantenimiento. Cada grupo de estados tiene un rango numérico:
 * 
 * - 100-106: Estados de Inscripción/Membresía
 * - 200-204: Estados de Pago
 * - 300-302: Estados de Convenio
 * - 400-402: Estados de Cliente
 * - 500-504: Estados de Recurso
 * - 600-603: Estados de Notificación
 */
class EstadosCodigo
{
    // ========================================
    // ESTADOS DE INSCRIPCIÓN/MEMBRESÍA (100-106)
    // ========================================
    
    /** Membresía vigente y activa */
    public const INSCRIPCION_ACTIVA = 100;
    
    /** Membresía pausada temporalmente */
    public const INSCRIPCION_PAUSADA = 101;
    
    /** Membresía expirada */
    public const INSCRIPCION_VENCIDA = 102;
    
    /** Membresía cancelada */
    public const INSCRIPCION_CANCELADA = 103;
    
    /** Membresía suspendida por deuda */
    public const INSCRIPCION_SUSPENDIDA = 104;
    
    /** Membresía cambiada a otro plan (upgrade/downgrade) */
    public const INSCRIPCION_CAMBIADA = 105;
    
    /** Membresía traspasada a otro cliente */
    public const INSCRIPCION_TRASPASADA = 106;

    // ========================================
    // ESTADOS DE PAGO (200-205)
    // ========================================
    
    /** Pago pendiente de realizar */
    public const PAGO_PENDIENTE = 200;
    
    /** Pago completado */
    public const PAGO_PAGADO = 201;
    
    /** Pago parcial, saldo pendiente */
    public const PAGO_PARCIAL = 202;
    
    /** Pago vencido sin realizar */
    public const PAGO_VENCIDO = 203;
    
    /** Pago cancelado */
    public const PAGO_CANCELADO = 204;
    
    /** Pago traspasado a otra inscripción */
    public const PAGO_TRASPASADO = 205;

    // ========================================
    // ESTADOS DE CONVENIO (300-302)
    // ========================================
    
    /** Convenio activo y vigente */
    public const CONVENIO_ACTIVO = 300;
    
    /** Convenio temporalmente suspendido */
    public const CONVENIO_SUSPENDIDO = 301;
    
    /** Convenio cancelado */
    public const CONVENIO_CANCELADO = 302;

    // ========================================
    // ESTADOS DE CLIENTE (400-402)
    // ========================================
    
    /** Cliente activo */
    public const CLIENTE_ACTIVO = 400;
    
    /** Cliente suspendido */
    public const CLIENTE_SUSPENDIDO = 401;
    
    /** Cliente cancelado */
    public const CLIENTE_CANCELADO = 402;

    // ========================================
    // ESTADOS DE RECURSO (500-504)
    // ========================================
    
    /** Recurso activo */
    public const RECURSO_ACTIVO = 500;
    
    /** Recurso suspendido */
    public const RECURSO_SUSPENDIDO = 501;
    
    /** Recurso cancelado */
    public const RECURSO_CANCELADO = 502;
    
    /** Recurso inactivo */
    public const RECURSO_INACTIVO = 503;
    
    /** Recurso vencido */
    public const RECURSO_VENCIDO = 504;

    // ========================================
    // ESTADOS DE NOTIFICACIÓN (600-603)
    // ========================================
    
    /** Notificación programada pendiente de envío */
    public const NOTIFICACION_PENDIENTE = 600;
    
    /** Notificación enviada exitosamente */
    public const NOTIFICACION_ENVIADA = 601;
    
    /** Error al enviar la notificación */
    public const NOTIFICACION_FALLIDA = 602;
    
    /** Notificación cancelada manualmente */
    public const NOTIFICACION_CANCELADA = 603;

    // ========================================
    // GRUPOS DE ESTADOS (para validaciones)
    // ========================================
    
    /** Estados que permiten acceso al gimnasio */
    public const INSCRIPCION_ACCESO_PERMITIDO = [
        self::INSCRIPCION_ACTIVA,
    ];
    
    /** Estados de inscripción que se consideran "finalizados" (no editables) */
    public const INSCRIPCION_FINALIZADOS = [
        self::INSCRIPCION_CANCELADA,
        self::INSCRIPCION_CAMBIADA,
        self::INSCRIPCION_TRASPASADA,
    ];
    
    /** Estados de inscripción que requieren cliente activo */
    public const INSCRIPCION_REQUIERE_CLIENTE_ACTIVO = [
        self::INSCRIPCION_ACTIVA,
        self::INSCRIPCION_PAUSADA,
        self::INSCRIPCION_SUSPENDIDA,
    ];
    
    /** Estados de pago que indican que se ha abonado algo */
    public const PAGO_CON_ABONO = [
        self::PAGO_PAGADO,
        self::PAGO_PARCIAL,
    ];
    
    /** Estados de pago pendientes de cobro */
    public const PAGO_PENDIENTES_COBRO = [
        self::PAGO_PENDIENTE,
        self::PAGO_PARCIAL,
        self::PAGO_VENCIDO,
    ];

    // ========================================
    // HELPERS ESTÁTICOS
    // ========================================
    
    /**
     * Obtener nombre del estado por código
     */
    public static function getNombre(int $codigo): string
    {
        return match($codigo) {
            self::INSCRIPCION_ACTIVA => 'Activa',
            self::INSCRIPCION_PAUSADA => 'Pausada',
            self::INSCRIPCION_VENCIDA => 'Vencida',
            self::INSCRIPCION_CANCELADA => 'Cancelada',
            self::INSCRIPCION_SUSPENDIDA => 'Suspendida',
            self::INSCRIPCION_CAMBIADA => 'Cambiada',
            self::INSCRIPCION_TRASPASADA => 'Traspasada',
            self::PAGO_PENDIENTE => 'Pendiente',
            self::PAGO_PAGADO => 'Pagado',
            self::PAGO_PARCIAL => 'Parcial',
            self::PAGO_VENCIDO => 'Vencido',
            self::PAGO_CANCELADO => 'Cancelado',
            self::PAGO_TRASPASADO => 'Traspasado',
            self::CONVENIO_ACTIVO => 'Activo',
            self::CONVENIO_SUSPENDIDO => 'Suspendido',
            self::CONVENIO_CANCELADO => 'Cancelado',
            self::CLIENTE_ACTIVO => 'Activo',
            self::CLIENTE_SUSPENDIDO => 'Suspendido',
            self::CLIENTE_CANCELADO => 'Cancelado',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener clase CSS para badge según estado
     */
    public static function getBadgeClass(int $codigo): string
    {
        return match($codigo) {
            self::INSCRIPCION_ACTIVA, self::PAGO_PAGADO, 
            self::CONVENIO_ACTIVO, self::CLIENTE_ACTIVO => 'success',
            
            self::INSCRIPCION_PAUSADA, self::PAGO_PARCIAL,
            self::CONVENIO_SUSPENDIDO, self::CLIENTE_SUSPENDIDO => 'warning',
            
            self::INSCRIPCION_VENCIDA, self::PAGO_VENCIDO => 'danger',
            
            self::INSCRIPCION_CANCELADA, self::PAGO_CANCELADO,
            self::CONVENIO_CANCELADO, self::CLIENTE_CANCELADO => 'secondary',
            
            self::INSCRIPCION_SUSPENDIDA => 'danger',
            self::INSCRIPCION_CAMBIADA => 'info',
            self::INSCRIPCION_TRASPASADA => 'purple',
            
            self::PAGO_PENDIENTE => 'warning',
            self::PAGO_TRASPASADO => 'purple',
            
            default => 'secondary',
        };
    }

    /**
     * Verificar si una inscripción está en estado activo
     */
    public static function inscripcionEstaActiva(int $codigo): bool
    {
        return in_array($codigo, self::INSCRIPCION_ACCESO_PERMITIDO);
    }

    /**
     * Verificar si un pago tiene saldo pendiente
     */
    public static function pagoTieneSaldoPendiente(int $codigo): bool
    {
        return in_array($codigo, self::PAGO_PENDIENTES_COBRO);
    }
}
