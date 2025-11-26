<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string|null $run_pasaporte NULL para indocumentados
 * @property string $nombres
 * @property string $apellido_paterno
 * @property string|null $apellido_materno
 * @property string $celular
 * @property string|null $email
 * @property string|null $direccion
 * @property \Illuminate\Support\Carbon|null $fecha_nacimiento
 * @property string|null $contacto_emergencia
 * @property string|null $telefono_emergencia
 * @property int|null $id_convenio Convenio asociado al cliente
 * @property string|null $observaciones
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Convenio|null $convenio
 * @property-read mixed $nombre_completo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereApellidoMaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereApellidoPaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereContactoEmergencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereFechaNacimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereIdConvenio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereNombres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereRunPasaporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefonoEmergencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $uuid
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $notificaciones
 * @property-read int|null $notificaciones_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUuid($value)
 */
	class Cliente extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre Ej: INACAP, Cruz Verde, Falabella
 * @property string $tipo
 * @property string $descuento_porcentaje Porcentaje de descuento (0-100%)
 * @property string $descuento_monto Descuento en pesos fijos
 * @property string|null $descripcion
 * @property string|null $contacto_nombre
 * @property string|null $contacto_telefono
 * @property string|null $contacto_email
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cliente> $clientes
 * @property-read int|null $clientes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereContactoEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereContactoNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereContactoTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereDescuentoMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereDescuentoPorcentaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $uuid
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Convenio whereUuid($value)
 */
	class Convenio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $codigo Rango: 01-99 membresias, 101-108 pagos, 200-299 convenios, 300-399 clientes
 * @property string $nombre
 * @property string|null $descripcion
 * @property string $categoria
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $color Color Bootstrap: primary, success, danger, warning, info, secondary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereCategoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estado whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Estado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_precio_membresia
 * @property \Illuminate\Support\Carbon $created_at
 * @property string $precio_anterior Precio anterior
 * @property string $precio_nuevo Precio nuevo
 * @property string|null $razon_cambio Razón del cambio
 * @property string|null $usuario_cambio Usuario que realizó el cambio
 * @property-read \App\Models\PrecioMembresia $precioMembresia
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereIdPrecioMembresia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio wherePrecioAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio wherePrecioNuevo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereRazonCambio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereUsuarioCambio($value)
 * @mixin \Eloquent
 */
	class HistorialPrecio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_cliente
 * @property int $id_membresia
 * @property int|null $id_convenio Convenio aplicado al momento de la inscripción
 * @property int $id_precio_acordado Precio vigente al momento de la inscripción
 * @property \Illuminate\Support\Carbon $fecha_inscripcion Fecha en que se registra
 * @property \Illuminate\Support\Carbon $fecha_inicio Fecha en que inicia la membresía (puede ser futura)
 * @property \Illuminate\Support\Carbon $fecha_vencimiento Fecha de expiración
 * @property int|null $dia_pago 1-31: Día del mes elegido para pagar
 * @property string $precio_base Precio oficial de la membresía
 * @property string $descuento_aplicado Descuento en pesos
 * @property int|null $id_motivo_descuento Justificación del descuento
 * @property int $id_estado Activa, Vencida, Pausada, Cancelada, Pendiente
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $pausada Si está en pausa
 * @property int $dias_pausa Días que durará la pausa
 * @property \Illuminate\Support\Carbon|null $fecha_pausa_inicio Cuándo inicia la pausa
 * @property \Illuminate\Support\Carbon|null $fecha_pausa_fin Cuándo termina la pausa
 * @property string|null $razon_pausa Motivo de la pausa
 * @property int $pausas_realizadas Cantidad de pausas hechas
 * @property int $max_pausas_permitidas Máximo de pausas permitidas por año
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Convenio|null $convenio
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Membresia $membresia
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @property-read \App\Models\PrecioMembresia $precioAcordado
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereDescuentoAplicado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereDiaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereDiasPausa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaPausaFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaPausaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdConvenio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdMembresia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdMotivoDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdPrecioAcordado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereMaxPausasPermitidas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePausada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePausasRealizadas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePrecioBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereRazonPausa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $uuid
 * @property string $precio_final precio_base - descuento_aplicado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $notificaciones
 * @property-read int|null $notificaciones_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePrecioFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereUuid($value)
 */
	class Inscripcion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property int $duracion_meses Meses de duración (0 para pase diario)
 * @property int $duracion_dias 0 para mensuales, 1 para pase diario, 365 para anual
 * @property string|null $descripcion
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PrecioMembresia> $precios
 * @property-read int|null $precios_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereDuracionDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereDuracionMeses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $uuid
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereUuid($value)
 */
	class Membresia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $requiere_comprobante Para futuro: pago online
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereRequiereComprobante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class MetodoPago extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class MotivoDescuento extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_inscripcion
 * @property int $id_cliente Redundante pero útil para queries
 * @property string $monto_total Total a pagar
 * @property string $monto_abonado Lo que se pagó en este registro
 * @property string $monto_pendiente Saldo restante
 * @property string $descuento_aplicado
 * @property int|null $id_motivo_descuento
 * @property \Illuminate\Support\Carbon $fecha_pago
 * @property \Illuminate\Support\Carbon $periodo_inicio Inicio del período cubierto
 * @property \Illuminate\Support\Carbon $periodo_fin Fin del período cubierto
 * @property int $id_metodo_pago
 * @property string|null $referencia_pago Futuro: N° de transferencia, comprobante
 * @property int $id_estado Pendiente, Pagado, Parcial, Vencido
 * @property int $cantidad_cuotas Total de cuotas (default: 1)
 * @property int $numero_cuota Número de cuota actual (ej: 1 de 3)
 * @property string|null $monto_cuota Monto de cada cuota individual
 * @property \Illuminate\Support\Carbon|null $fecha_vencimiento_cuota Fecha de vencimiento de esta cuota
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Inscripcion $inscripcion
 * @property-read \App\Models\MetodoPago $metodoPago
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereDescuentoAplicado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereFechaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdMetodoPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdMotivoDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoAbonado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoPendiente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago wherePeriodoFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago wherePeriodoInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereReferenciaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $uuid
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereCantidadCuotas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereFechaVencimientoCuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoCuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereNumeroCuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereUuid($value)
 */
	class Pago extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_membresia
 * @property string $precio_normal
 * @property string|null $precio_convenio NULL si no aplica convenio
 * @property \Illuminate\Support\Carbon $fecha_vigencia_desde
 * @property \Illuminate\Support\Carbon|null $fecha_vigencia_hasta NULL = vigente actualmente
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HistorialPrecio> $historialPrecios
 * @property-read int|null $historial_precios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \App\Models\Membresia $membresia
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereFechaVigenciaDesde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereFechaVigenciaHasta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereIdMembresia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia wherePrecioConvenio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia wherePrecioNormal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PrecioMembresia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property array<array-key, mixed>|null $permisos Array de permisos: ["crear_cliente", "editar_precio", etc.]
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $usuarios
 * @property-read int|null $usuarios_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol wherePermisos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Rol extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property int $id_rol
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Rol $rol
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdRol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

