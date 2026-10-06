<?php

namespace App\Models;

use App\Enums\EstadosCodigo;
use App\Models\HistorialCambio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $id_cliente
 * @property int $id_membresia
 * @property int|null $id_convenio Convenio aplicado al momento de la inscripción
 * @property int $id_precio_acordado Precio vigente al momento de la inscripción
 * @property \Illuminate\Support\Carbon $fecha_inscripcion Fecha en que se registra
 * @property \Illuminate\Support\Carbon $fecha_inicio Fecha en que inicia la membresía (puede ser futura)
 * @property \Illuminate\Support\Carbon $fecha_vencimiento Fecha de expiración
 * @property string $precio_base Precio oficial de la membresía
 * @property string $descuento_aplicado Descuento en pesos
 * @property string $precio_final precio_base - descuento_aplicado
 * @property int|null $id_motivo_descuento Justificación del descuento
 * @property int $id_estado Activa, Vencida, Pausada, Cancelada, Pendiente (referencia estados.codigo)
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Convenio|null $convenio
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Membresia $membresia
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @property-read \App\Models\PrecioMembresia $precioAcordado
 * @property-read int $dias_restantes
 * @property-read bool $esta_vencida
 * @property-read bool $esta_activa
 * @mixin \Eloquent
 */
class Inscripcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscripciones';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'id_cliente',
        'id_membresia',
        'id_convenio',
        'id_precio_acordado',
        'fecha_inscripcion',
        'fecha_inicio',
        'fecha_vencimiento',
        'precio_base',
        'descuento_aplicado',
        'precio_final',
        'id_motivo_descuento',
        'id_estado',
        'observaciones',
        // Campos de sistema de pausas
        'pausada',
        'dias_pausa',
        'dias_restantes_al_pausar',
        'fecha_pausa_inicio',
        'fecha_pausa_fin',
        'razon_pausa',
        'pausa_indefinida',
        'pausas_realizadas',
        'max_pausas_permitidas',
        'dias_compensacion',
        // Campos de cambio de plan (upgrade/downgrade)
        'id_inscripcion_anterior',
        'es_cambio_plan',
        'tipo_cambio',
        'credito_plan_anterior',
        'precio_nuevo_plan',
        'diferencia_a_pagar',
        'fecha_cambio_plan',
        'motivo_cambio_plan',
        // Campos de traspaso de membresía
        'es_traspaso',
        'id_inscripcion_origen',
        'id_cliente_original',
        'fecha_traspaso',
        'motivo_traspaso',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_pausa_inicio' => 'date',
        'fecha_pausa_fin' => 'date',
        'fecha_cambio_plan' => 'datetime',
        'fecha_traspaso' => 'datetime',
        'precio_base' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'precio_final' => 'decimal:2',
        'credito_plan_anterior' => 'decimal:2',
        'precio_nuevo_plan' => 'decimal:2',
        'diferencia_a_pagar' => 'decimal:2',
        'pausada' => 'boolean',
        'pausa_indefinida' => 'boolean',
        'es_cambio_plan' => 'boolean',
        'es_traspaso' => 'boolean',
        'dias_pausa' => 'integer',
        'dias_restantes_al_pausar' => 'integer',
        'pausas_realizadas' => 'integer',
        'max_pausas_permitidas' => 'integer',
        'dias_compensacion' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /** Solo las mensualidades: sin los pases diarios (ver Membresia::scopePases). */
    public function scopeSinPases($consulta)
    {
        return $consulta->whereHas('membresia', fn ($q) => $q->withTrashed()->where(fn ($q) => $q
            ->where('duracion_meses', '>', 0)
            ->orWhere('duracion_dias', '>', 1)));
    }

    /** Solo los pases diarios. */
    public function scopeSoloPases($consulta)
    {
        return $consulta->whereHas('membresia', fn ($q) => $q->withTrashed()->pases());
    }

    public function membresia()
    {
        // Con los de la papelera: un plan que se deja de vender no deja en
        // blanco el nombre de las membresías que ya se vendieron con él.
        return $this->belongsTo(Membresia::class, 'id_membresia')->withTrashed();
    }

    public function precioAcordado()
    {
        return $this->belongsTo(PrecioMembresia::class, 'id_precio_acordado');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
    }

    public function motivoDescuento()
    {
        return $this->belongsTo(MotivoDescuento::class, 'id_motivo_descuento');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'id_convenio');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_inscripcion');
    }

    /**
     * Vuelve a escribir el saldo y el estado de TODOS sus pagos.
     *
     * El saldo que guarda cada pago es lo que quedaba por cobrar DESPUES de él,
     * así que cualquier cosa que mueva la cuenta —corregir un monto, anular un
     * cobro, cambiarle el precio a la membresía— invalida a todos los que vengan
     * detrás. Actualizar solo el que se tocó deja las demás filas diciendo un
     * saldo que ya no es, y en la ficha del socio se ven pagos que no cuadran
     * entre sí.
     *
     * Vive en el modelo y no en un controlador porque son SUS pagos: lo llaman
     * la corrección de un pago, la de la propia membresía y la revisión de cada
     * noche (pagos:sincronizar-estados).
     *
     * Devuelve cuántos pagos no cuadraban. Con `$guardar` en false solo los
     * cuenta, sin tocar nada.
     */
    public function recalcularSusPagos(bool $guardar = true): int
    {
        $precio = (int) ($this->precio_final ?? $this->precio_base ?? 0);

        $pagos = $this->pagos()->orderBy('fecha_pago')->orderBy('id')->get();
        $cobrado = (int) $pagos->sum('monto_abonado');

        // El estado es de la membresía entera, no de cada pago suelto: o está
        // saldada o no lo está.
        //
        // «Saldada» se mira ANTES que «no se cobró nada». Una cortesía o un
        // descuento del 100% cuesta $0: se registra con un pago de $0 que ya
        // la deja al día. Con el orden al revés, la revisión de la noche veía
        // $0 cobrado y lo marcaba «Pendiente», y ese pendiente fantasma
        // impedía dar de baja al socio o borrar sus datos.
        $estado = match (true) {
            $cobrado >= $precio => EstadosCodigo::PAGO_PAGADO,
            $cobrado <= 0 => EstadosCodigo::PAGO_PENDIENTE,
            default => EstadosCodigo::PAGO_PARCIAL,
        };

        $restante = $precio;
        $descuadrados = 0;

        foreach ($pagos as $pago) {
            $restante -= (int) $pago->monto_abonado;

            $pago->fill([
                'monto_total' => $precio,
                'monto_pendiente' => max(0, $restante),
                'id_estado' => $estado,
            ]);

            // Solo se escribe lo que no cuadraba: la revisión de la noche pasa
            // por todas las membresías y no tiene por qué tocar las que están bien.
            if ($pago->isDirty()) {
                $descuadrados++;

                if ($guardar) {
                    $pago->save();
                }
            }
        }

        return $descuadrados;
    }

    /**
     * Obtener el estado actual de pago de la inscripción
     */
    public function obtenerEstadoPago()
    {
        $montoTotal = $this->precio_final ?? (($this->precio_base ?? 0) - ($this->descuento_aplicado ?? 0));
        $allPagos = $this->pagos()->get();
        $totalAbonado = $allPagos->sum('monto_abonado');
        
        $pendiente = max(0, $montoTotal - $totalAbonado);
        $porcentajePagado = $montoTotal > 0 ? ($totalAbonado / $montoTotal) * 100 : 0;

        return [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'pendiente' => $pendiente,
            'porcentaje_pagado' => min(100, $porcentajePagado),
            'estado' => $pendiente <= 0 ? 'pagado' : ($totalAbonado > 0 ? 'parcial' : 'pendiente'),
        ];
    }

    /**
     * Obtener días restantes de la membresía
     */
    public function getDiasRestantesAttribute()
    {
        // En pausa, lo que le queda es lo que se guardó al pausar. La fecha de
        // vencimiento se queda quieta durante la pausa —se rehace al
        // reanudar— y restar contra ella daba «venció hace 10 días» de una
        // membresía congelada con 20 por delante: no se podía traspasar y la
        // ficha la pintaba en rojo.
        if ($this->estaEnPausa()) {
            return max(0, (int) $this->dias_restantes_al_pausar);
        }

        if (!$this->fecha_vencimiento) {
            return 0;
        }
        // De día a día. Con now() y la hora del momento, una membresía que
        // vence mañana daba 0 —el (int) corta los 0,4 días— y ya no se podía
        // traspasar: a cualquier hora que no fuera medianoche se restaba uno.
        return self::diasEntre(today(), $this->fecha_vencimiento);
    }

    /**
     * Días de calendario entre dos fechas (negativo si $hasta va antes).
     *
     * POR LA FECHA, NO POR LAS HORAS. Chile cambia la hora: el domingo en que
     * se adelanta, ese día empieza a la 01:00 y dura 23 horas, y en abril uno
     * dura 25. diffInDays() cuenta horas: de ese domingo al lunes da 0,96, y el
     * (int) lo deja en 0. El socio que pausaba ese domingo perdía un día, y
     * quien vencía el domingo seguía «venciendo hoy» el lunes. Se comparan las
     * dos fechas como fechas en UTC, donde todos los días duran 24 horas.
     */
    public static function diasEntre($desde, $hasta): int
    {
        $enUtc = fn ($fecha) => Carbon::createFromFormat('Y-m-d', Carbon::parse($fecha)->format('Y-m-d'), 'UTC')->startOfDay();

        return (int) $enUtc($desde)->diffInDays($enUtc($hasta), false);
    }

    /**
     * En pausa de verdad: el estado 101 Y la marca.
     *
     * Las dos: una renovada mientras estaba pausada quedó con la marca y estado
     * Vencida (antes de que cerrarLaAnterior la limpiara), y esa ya no está
     * congelada.
     */
    public function estaEnPausa(): bool
    {
        return (bool) $this->pausada && (int) $this->id_estado === EstadosCodigo::INSCRIPCION_PAUSADA;
    }

    /**
     * Verificar si la inscripción está vencida
     */
    public function getEstaVencidaAttribute()
    {
        return $this->dias_restantes < 0;
    }

    /**
     * Verificar si la inscripción está activa
     */
    public function getEstaActivaAttribute()
    {
        return $this->id_estado == 100 && !$this->esta_vencida;
    }

    /**
     * Verificar si la inscripción está pausada
     */
    public function estaPausada()
    {
        // Estado 101 = Pausada (NO confundir con 102 = Vencida)
        return $this->pausada === true || $this->id_estado == 101;
    }

    /**
     * Verificar si puede realizar más pausas
     */
    public function puedeRealizarPausa()
    {
        return $this->pausas_realizadas < $this->max_pausas_permitidas
            && !$this->pausada
            && $this->id_estado == 100
            // Ya vencida por fecha, aunque la revisión del día todavía no la
            // haya marcado: no queda nada que congelar. Se pausaba con 0 días
            // guardados y al reanudar seguía vencida, con una pausa gastada.
            && $this->dias_restantes >= 0;
    }

    /**
     * Obtener pausas disponibles
     */
    public function getPausasDisponiblesAttribute()
    {
        return max(0, $this->max_pausas_permitidas - $this->pausas_realizadas);
    }

    /**
     * Pausar la membresía
     * 
     * @param int|null $dias Días de pausa (null para indefinida)
     * @param string $razon Razón de la pausa
     * @param bool $indefinida Si es pausa indefinida
     * @return bool
     */
    public function pausar($dias = null, $razon = '', $indefinida = false)
    {
        /*
         * SE DECIDE CON LA FILA TRABADA Y RECIÉN LEÍDA, no con lo que trajo la
         * pantalla. Doble clic o dos pestañas: las dos peticiones cargaban la
         * membresía activa, las dos pasaban el control y las dos pausaban —dos
         * filas en el historial, dos avisos al socio y los días guardados
         * escritos dos veces—. Con la fila trabada la segunda espera, la lee ya
         * pausada y se va sin tocar nada.
         */
        return DB::transaction(function () use ($dias, $razon, $indefinida) {
            if (! $this->releerTrabada()?->puedeRealizarPausa()) {
                return false;
            }

            // Los días que le quedaban, contando HOY: pausar el último día le
            // guarda 0 y al reanudar vence ese mismo día (ver reanudar()).
            $diasRestantes = $this->fecha_vencimiento
                ? max(0, self::diasEntre(today(), $this->fecha_vencimiento))
                : 0;

            $this->pausada = true;
            $this->dias_pausa = $indefinida ? null : $dias; // Solo informativo: cuántos días pidió pausar
            $this->dias_restantes_al_pausar = $diasRestantes; // CRÍTICO: los días que le quedaban
            $this->fecha_pausa_inicio = today();
            // El día en que VUELVE: la revisión de ese día la reanuda.
            $this->fecha_pausa_fin = $indefinida ? null : today()->addDays($dias);
            $this->razon_pausa = $razon;
            $this->pausa_indefinida = $indefinida;
            $this->pausas_realizadas = $this->pausas_realizadas + 1;
            $this->id_estado = EstadosCodigo::INSCRIPCION_PAUSADA;

            if (! $this->save()) {
                return false;
            }

            // Con los nombres que lee registrarPausa(): se mandaban como
            // 'dias_solicitados' y 'fecha_fin_pausa', y el historial guardaba
            // siempre los días y el fin de la pausa vacíos.
            HistorialCambio::registrarPausa($this, [
                'dias' => $indefinida ? null : $dias,
                'razon' => $razon,
                'indefinida' => $indefinida,
                'fecha_fin' => $this->fecha_pausa_fin?->format('Y-m-d'),
            ]);

            $this->cancelarAvisosDeVencimiento();

            return true;
        });
    }

    /**
     * Vuelve a leer la fila, trabada hasta que acabe la transacción, y deja
     * este modelo con lo que dice la base. Null si ya no existe.
     */
    private function releerTrabada(): ?self
    {
        $actual = static::whereKey($this->getKey())->lockForUpdate()->first();

        if ($actual) {
            $this->setRawAttributes($actual->getAttributes(), true);
        }

        return $actual ? $this : null;
    }

    /**
     * Los avisos de «por vencer» y «vencida» que todavía no salieron.
     *
     * Uno que quedó pendiente, o que falló y se reintenta cada noche, salía
     * igual con la membresía ya congelada: «tu membresía vence en 7 días» a
     * quien acaba de pausar. Se cancelan; al reanudar, la fecha nueva tendrá
     * su propio aviso.
     */
    private function cancelarAvisosDeVencimiento(): void
    {
        Notificacion::where('id_inscripcion', $this->id)
            ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_FALLIDO])
            ->whereHas('tipoNotificacion', fn ($q) => $q->whereIn('codigo', [
                TipoNotificacion::MEMBRESIA_POR_VENCER,
                TipoNotificacion::MEMBRESIA_VENCIDA,
            ]))
            ->get()
            ->each(fn (Notificacion $aviso) => $aviso->cancelar('Membresía pausada'));
    }

    /**
     * Por qué no se puede reanudar, o null si se puede.
     *
     * No basta con la marca `pausada`. Renovar una membresía en pausa la
     * cerraba como Vencida pero le dejaba la marca y los días guardados, y la
     * ficha seguía ofreciendo «Reanudar»: al pulsarlo volvía a Activa con sus
     * días, y el socio quedaba con DOS membresías vigentes, la vieja revivida
     * y la nueva que acababa de pagar. Solo se reanuda la que está de verdad en
     * pausa (101) y que nadie ha reemplazado todavía.
     *
     * Lo preguntan reanudar(), el controlador y la ficha: así el botón que se
     * ve es el mismo que el servidor acepta.
     */
    public function porQueNoSePuedeReanudar(): ?string
    {
        if (! $this->pausada || (int) $this->id_estado !== EstadosCodigo::INSCRIPCION_PAUSADA) {
            return 'Esta membresía no está pausada.';
        }

        if ($this->inscripcionesPosteriores()->exists()) {
            return 'Esta membresía ya se renovó: la vigente es la nueva.';
        }

        // Otra mensualidad vigente del mismo socio que no salió de esta: la
        // pausada estuvo en la papelera y entretanto se le vendió otra.
        // Reanudarla —a mano o la revisión del día— lo dejaba con dos. Los
        // pases diarios no cuentan: quien está en pausa puede venir un día.
        $otraVigente = static::where('id_cliente', $this->id_cliente)
            ->whereKeyNot($this->getKey())
            ->whereIn('id_estado', [EstadosCodigo::INSCRIPCION_ACTIVA, EstadosCodigo::INSCRIPCION_PAUSADA])
            ->sinPases()
            ->exists();

        if ($otraVigente) {
            return 'El socio ya tiene otra membresía vigente: cancela una de las dos antes de reanudar.';
        }

        return null;
    }

    /**
     * Reanudar la membresía pausada
     * 
     * LÓGICA CORRECTA:
     * - Al pausar se guardaron los días que le quedaban (dias_restantes_al_pausar)
     * - Al reanudar: nueva_fecha_vencimiento = HOY + dias_restantes_al_pausar
     * - NO importa cuántos días estuvo pausado, solo importa cuántos días le quedaban
     * 
     * @return bool
     */
    public function reanudar()
    {
        // Con la fila trabada y releída, como al pausar: el doble clic o la
        // segunda pestaña reanudaban otra vez la misma pausa y dejaban dos
        // reanudaciones en el historial.
        return DB::transaction(function () {
            if (! $this->releerTrabada() || $this->porQueNoSePuedeReanudar() !== null) {
                return false;
            }

            $reanudada = $this->reanudarYa();

            if ($reanudada) {
                $this->cancelarAvisoDePausaEnCola();
            }

            return $reanudada;
        });
    }

    /**
     * El «tu membresía quedó en pausa» que todavía no salió ya no es verdad.
     *
     * Si el correo de la pausa se atrasó (tope del día, servidor caído) y la
     * membresía se reactiva antes de que salga, el socio recibía «en pausa»
     * DESPUÉS de «reactivada». Vale para el botón y para la tarea nocturna.
     */
    private function cancelarAvisoDePausaEnCola(): void
    {
        Notificacion::where('id_inscripcion', $this->id)
            ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_FALLIDO])
            ->whereHas('tipoNotificacion', fn ($q) => $q->where('codigo', TipoNotificacion::PAUSA_INSCRIPCION))
            ->get()
            ->each->cancelar('No se envió: la membresía se reactivó antes de que saliera el aviso de pausa.');
    }

    /**
     * El día desde el que se cuentan los días guardados al reanudar HOY: el
     * fin de la pausa si ya pasó, y si no, hoy. Lo usa también el aviso del
     * botón, para decir lo mismo que se hace.
     */
    public function desdeCuandoSeReanuda(): Carbon
    {
        $hoy = Carbon::today();

        return ($this->fecha_pausa_fin && self::diasEntre($this->fecha_pausa_fin, $hoy) > 0)
            ? Carbon::parse($this->fecha_pausa_fin->format('Y-m-d'))
            : $hoy;
    }

    /**
     * Hasta cuándo le alcanzará una vez reanudada, si la pausa tiene fin.
     *
     * Mientras dura la pausa, fecha_vencimiento es la vieja y no vale: la
     * ficha la mostraba como si fuera la de verdad. Null en una indefinida,
     * que no se sabe cuándo vuelve.
     */
    public function vencimientoAlReanudar(): ?Carbon
    {
        if (! $this->estaEnPausa() || ! $this->fecha_pausa_fin) {
            return null;
        }

        // Desde el fin de la pausa aunque ya haya pasado: es lo que hace
        // reanudar() con una pausa que nadie reanudó a tiempo.
        return Carbon::parse($this->fecha_pausa_fin->format('Y-m-d'))
            ->addDays(max(0, (int) $this->dias_restantes_al_pausar));
    }

    private function reanudarYa(): bool
    {

        /*
         * DESDE CUANDO SE REANUDA: el dia en que acababa la pausa, si ya paso.
         *
         * Se contaba siempre desde HOY. Pero la pausa tiene fecha de fin, y si
         * se reanuda tarde —la tarea nocturna corre al dia siguiente, o nadie
         * pulso «Reanudar» a tiempo— los dias de retraso se le regalaban al
         * socio encima de su membresia: una pausa de 7 dias reanudada a las
         * tres semanas le daba dos semanas gratis. Pidio 7, y 7 son.
         *
         * Reanudar ANTES de tiempo sigue contando desde hoy: ahi la pausa se
         * corta, que es justo lo que se pidio al pulsar el boton.
         */
        $desde = $this->desdeCuandoSeReanuda();

        // Calcular días que estuvo pausado (solo para información/historial)
        $diasEnPausa = 0;
        if ($this->fecha_pausa_inicio) {
            // Hasta $desde y no hasta hoy: el historial diria «estuvo pausada
            // 21 dias» de una pausa de 7 que se reanudo tarde.
            $diasEnPausa = max(0, self::diasEntre($this->fecha_pausa_inicio, $desde));
        }

        // Los días que tenía guardados al momento de pausar
        $diasRestantesGuardados = $this->dias_restantes_al_pausar ?? 0;

        // NUEVA FECHA DE VENCIMIENTO = desde + días que le quedaban.
        //
        // También con 0: quien pausó el último día tenía ese día por delante,
        // y vence el día que vuelve. Con «solo si es mayor que 0» la fecha se
        // quedaba en la vieja, ya pasada, y la membresía recién reanudada
        // amanecía vencida. Solo se deja quieta si no hay nada guardado.
        if ($this->dias_restantes_al_pausar !== null) {
            $this->fecha_vencimiento = $desde->copy()->addDays($diasRestantesGuardados);
        }

        // Limpiar todos los campos de pausa
        $this->pausada = false;
        $this->dias_pausa = null;
        $this->dias_restantes_al_pausar = null;
        $this->fecha_pausa_inicio = null;
        $this->fecha_pausa_fin = null;
        $this->razon_pausa = null;
        $this->pausa_indefinida = false;
        $this->id_estado = 100; // Estado Activa

        $resultado = $this->save();

        // Registrar en historial
        if ($resultado) {
            HistorialCambio::registrarReanudacion($this, $diasEnPausa, $diasRestantesGuardados);
        }

        return $resultado;
    }

    /**
     * Obtener descripción del estado de pausa
     */
    public function getEstadoPausaDescripcionAttribute()
    {
        if (!$this->pausada) {
            return null;
        }

        if ($this->pausa_indefinida) {
            return 'Pausada hasta nuevo aviso';
        }

        return $this->fecha_pausa_fin 
            ? 'Pausada hasta ' . $this->fecha_pausa_fin->format('d/m/Y')
            : 'Pausada';
    }

    /**
     * Verificar si la pausa ha expirado y reanudar automáticamente
     * 
     * Este método es llamado por el CRON para verificar pausas expiradas.
     * Solo aplica a pausas con fecha de fin definida (NO indefinidas).
     * 
     * @return bool true si se reanudó, false si no
     */
    public function verificarPausaExpirada()
    {
        // Solo verificar si está pausada y NO es indefinida
        if (!$this->pausada || $this->pausa_indefinida) {
            return false;
        }

        // Si no tiene fecha de fin, no hay nada que verificar
        if (!$this->fecha_pausa_fin) {
            return false;
        }

        // Si la fecha de fin ya pasó, reanudar automáticamente
        // El día en que termina la pausa ya es día de vuelta.
        if (self::diasEntre($this->fecha_pausa_fin, today()) >= 0) {
            return (bool) $this->reanudar();
        }

        return false;
    }

    /**
     * Alias de puedeRealizarPausa para compatibilidad
     * @return bool
     */
    public function puedePausarse()
    {
        return $this->puedeRealizarPausa();
    }

    /**
     * Obtener información completa de la pausa actual
     * @return array
     */
    public function obtenerInfoPausa()
    {
        if (!$this->pausada) {
            return [
                'pausada' => false,
                'puede_pausarse' => $this->puedeRealizarPausa(),
                'pausas_realizadas' => $this->pausas_realizadas,
                'pausas_disponibles' => $this->pausas_disponibles,
                'max_pausas' => $this->max_pausas_permitidas,
            ];
        }

        $diasEnPausa = $this->fecha_pausa_inicio
            ? max(0, self::diasEntre($this->fecha_pausa_inicio, today()))
            : 0;

        $diasRestantesPausa = null;
        if ($this->fecha_pausa_fin && !$this->pausa_indefinida) {
            $diasRestantesPausa = max(0, self::diasEntre(today(), $this->fecha_pausa_fin));
        }

        return [
            'pausada' => true,
            'indefinida' => $this->pausa_indefinida,
            'dias_solicitados' => $this->dias_pausa,
            'dias_en_pausa' => $diasEnPausa,
            'dias_restantes_pausa' => $diasRestantesPausa,
            'dias_membresia_guardados' => $this->dias_restantes_al_pausar,
            'fecha_inicio' => $this->fecha_pausa_inicio?->format('Y-m-d'),
            'fecha_fin' => $this->fecha_pausa_fin?->format('Y-m-d'),
            'razon' => $this->razon_pausa,
            'pausas_realizadas' => $this->pausas_realizadas,
            'pausas_disponibles' => $this->pausas_disponibles,
            'max_pausas' => $this->max_pausas_permitidas,
            'descripcion' => $this->estado_pausa_descripcion,
        ];
    }

    // ============================================
    // MÉTODOS DE CAMBIO DE PLAN (UPGRADE/DOWNGRADE)
    // ============================================

    /**
     * Relación con la inscripción anterior (si es upgrade/downgrade)
     */
    public function inscripcionAnterior()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion_anterior');
    }

    /**
     * Relación con inscripciones que la reemplazaron
     */
    public function inscripcionesPosteriores()
    {
        return $this->hasMany(Inscripcion::class, 'id_inscripcion_anterior');
    }

    /**
     * Verificar si esta inscripción puede cambiar de plan
     * Solo inscripciones activas pueden cambiar
     */
    public function puedeCambiarPlan()
    {
        return $this->id_estado == 100 && !$this->pausada;
    }

    /**
     * Obtener el monto total pagado de esta inscripción
     */
    public function getMontoPagadoAttribute()
    {
        return $this->pagos()->sum('monto_abonado');
    }

    /**
     * Obtener el monto pendiente de esta inscripción
     */
    public function getMontoPendienteAttribute()
    {
        return max(0, $this->precio_final - $this->monto_pagado);
    }

    /**
     * Verificar si la inscripción está completamente pagada
     */
    public function getEstaPagadaAttribute()
    {
        return $this->monto_pagado >= $this->precio_final;
    }

    /** Las membresías cuya deuda todavía se sale a cobrar. Las canceladas, no. */
    public const ESTADOS_CON_DEUDA = [100, 101, 102];

    /**
     * Lo que debe esta membresía: su precio menos todo lo abonado.
     *
     * Usa la suma de `withSum('pagos as abonado', ...)` si viene cargada, para
     * no hacer una consulta por membresía al listar.
     */
    public function getDeudaAttribute(): int
    {
        $abonado = array_key_exists('abonado', $this->attributes)
            ? (int) $this->attributes['abonado']
            : (int) $this->monto_pagado;

        return max(0, (int) $this->precio_final - $abonado);
    }

    /**
     * Las membresías que deben algo, con lo abonado ya sumado.
     *
     * LA DEUDA ES DE LA MEMBRESÍA, NO DE CADA PAGO. Cada pago guarda en
     * `monto_pendiente` lo que quedaba DESPUÉS de él, así que sumar esa columna
     * contaba dos veces a quien abonó dos veces, y nada a quien no había pagado
     * ni una cuota. El resumen, Pagos y Reportes daban tres cifras distintas y
     * ninguna era lo que se debía.
     */
    public static function conDeuda(): \Illuminate\Database\Eloquent\Collection
    {
        // El filtro va en la consulta: antes se traían a PHP TODAS las
        // membresías 100/101/102 —la 102 es casi todo el historial— en cada
        // carga de Pagos, Caja y Reportes. El filtro de PHP queda detrás porque
        // `deuda` trunca a entero cada lado y así el resultado es idéntico al
        // de siempre; la consulta ya solo devuelve las que deben.
        return static::queDeben()
            ->withSum('pagos as abonado', 'monto_abonado')
            ->get()
            ->filter(fn (self $inscripcion) => $inscripcion->deuda > 0)
            ->values();
    }

    /**
     * Lo abonado a cada membresía, como subconsulta de SQL.
     *
     * Sale de Pago::query() para que el SoftDeletes ponga solo el «deleted_at
     * IS NULL»: un pago anulado no cuenta, igual que en withSum().
     *
     * @return array{0: string, 1: array}
     */
    private static function abonadoEnSql(): array
    {
        $sub = Pago::query()
            ->selectRaw('COALESCE(SUM(pagos.monto_abonado), 0)')
            ->whereColumn('pagos.id_inscripcion', 'inscripciones.id');

        return ['('.$sub->toSql().')', $sub->getBindings()];
    }

    /** Las membresías que se cobran y cuyo precio supera lo abonado. */
    public function scopeQueDeben($consulta)
    {
        [$abonado, $valores] = self::abonadoEnSql();

        return $consulta
            ->whereIn('inscripciones.id_estado', self::ESTADOS_CON_DEUDA)
            ->whereRaw("COALESCE(inscripciones.precio_final, 0) - {$abonado} > 0", $valores);
    }

    /** El total que se debe, para las cifras de arriba de cada pantalla. */
    public static function porCobrar(): int
    {
        // Sumado en la base: con miles de membresías no hay que traer ninguna.
        [$abonado, $valores] = self::abonadoEnSql();

        return (int) static::queDeben()
            ->selectRaw("COALESCE(SUM(COALESCE(inscripciones.precio_final, 0) - {$abonado}), 0) AS total", $valores)
            ->value('total');
    }

    /**
     * Calcular el crédito disponible para cambio de plan
     * Es el monto que ya pagó el cliente
     */
    public function getCreditoDisponibleAttribute()
    {
        return $this->monto_pagado;
    }

    /**
     * Obtener días restantes de la membresía actual
     */
    public function getDiasConsumidosAttribute()
    {
        if (!$this->fecha_inicio) return 0;
        return max(0, self::diasEntre($this->fecha_inicio, today()));
    }

    /**
     * Verificar si es un upgrade o downgrade
     */
    public function getTipoCambioDescripcionAttribute()
    {
        if (!$this->es_cambio_plan) {
            return null;
        }

        return $this->tipo_cambio === 'upgrade' ? 'Mejora de Plan' : 'Cambio a Plan Menor';
    }

    // ============================================
    // MÉTODOS DE TRASPASO DE MEMBRESÍA
    // ============================================

    /**
     * Relación con la inscripción origen (de donde viene el traspaso)
     */
    public function inscripcionOrigen()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion_origen');
    }

    /**
     * Relación con el cliente original que cedió la membresía
     */
    public function clienteOriginal()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente_original');
    }

    /**
     * Inscripciones que fueron traspasadas desde esta
     */
    public function inscripcionesTraspasadas()
    {
        return $this->hasMany(Inscripcion::class, 'id_inscripcion_origen');
    }

    /**
     * Verificar si esta inscripción puede ser traspasada
     * Solo inscripciones activas o pausadas pueden traspasarse
     * @param bool $ignorarDeuda Si es true, ignora la validación de deuda pendiente
     */
    public function puedeTraspasarse($ignorarDeuda = false)
    {
        // Debe estar activa o pausada
        if (!in_array($this->id_estado, [100, 101])) {
            return false;
        }
        
        // Debe tener días restantes
        if ($this->dias_restantes <= 0) {
            return false;
        }
        
        // Si no se ignora la deuda, verificar que esté completamente pagada
        if (!$ignorarDeuda && $this->monto_pendiente > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener información detallada de traspaso
     * Incluye validaciones y montos de deuda si existen
     */
    public function getInfoTraspaso()
    {
        $estadoPago = $this->obtenerEstadoPago();
        
        return [
            'puede_traspasar' => $this->puedeTraspasarse(false), // Sin ignorar deuda
            'puede_traspasar_con_deuda' => $this->puedeTraspasarse(true), // Ignorando deuda
            'tiene_deuda' => $estadoPago['pendiente'] > 0,
            'monto_total' => $estadoPago['monto_total'],
            'monto_pagado' => $estadoPago['total_abonado'],
            'monto_pendiente' => $estadoPago['pendiente'],
            'porcentaje_pagado' => $estadoPago['porcentaje_pagado'],
            'estado_pago' => $estadoPago['estado'],
            'dias_restantes' => $this->dias_restantes,
            'membresia' => $this->membresia->nombre ?? 'N/A',
            'fecha_vencimiento' => $this->fecha_vencimiento->format('d/m/Y'),
        ];
    }

    /**
     * Verificar si un cliente puede recibir un traspaso
     * No debe tener membresía activa
     */
    public static function clientePuedeRecibirTraspaso($clienteId)
    {
        return !self::where('id_cliente', $clienteId)
            ->conMembresiaVigente()
            ->exists();
    }

    /**
     * Las que cuentan como «ya tiene membresía»: una activa que no venció, o
     * una pausada, SIN MIRAR LA FECHA.
     *
     * La fecha de una pausada es la que tenía al pausar y queda atrás mientras
     * dura la pausa. Con «fecha >= hoy» para las dos, quien tenía una en pausa
     * figuraba libre y podía recibir otra por traspaso: dos membresías.
     */
    public function scopeConMembresiaVigente($consulta)
    {
        return $consulta->where(fn ($q) => $q
            ->where(fn ($q) => $q
                ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
                ->whereDate('fecha_vencimiento', '>=', today()))
            ->orWhere('id_estado', EstadosCodigo::INSCRIPCION_PAUSADA));
    }
}
