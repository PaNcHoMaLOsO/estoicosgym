<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\CobroTaller;
use App\Models\Contrato;
use App\Models\Convenio;
use App\Models\CotizacionTaller;
use App\Models\EntradaCanje;
use App\Models\Falla;
use App\Models\Fiado;
use App\Models\FiadoRegistro;
use App\Models\HistorialCambio;
use App\Models\HistorialPrecio;
use App\Models\HistorialTraspaso;
use App\Models\HoraTaller;
use App\Models\InformeGuardado;
use App\Models\Inscripcion;
use App\Models\LogNotificacion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Nota;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Models\User;
use App\Rules\RutValido;
use App\Services\ConstructorInformes;
use App\Support\Ajustes;
use App\Support\PrecioAcordado;
use Database\Seeders\DemoCompletoSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * El gimnasio de demostración no se contradice.
 *
 * DemoCompletoSeeder inventa año y medio de historia para recorrer el panel
 * lleno. Si un dato inventado choca con las reglas del sistema —una «Activa»
 * vencida, un socio con dos membresías, un pago que cobra más que el plan—, el
 * panel enseña un disparate y no se sabe si el fallo es del sistema o del
 * generador. Aquí se mira TODA fila generada contra las reglas que el propio
 * sistema impone, y al final se corre la revisión de cada noche: si encuentra
 * algo que arreglar, la demo estaba mal.
 *
 * Se siembra una vez por clase: son unos cientos de socios y cada prueba solo
 * lee.
 */
class DemoCompletoCoherenteTest extends CasoConCatalogos
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;
    private const CAMBIADA = 105;

    protected function setUp(): void
    {
        parent::setUp();

        // El archivo con las claves va al disco falso: la prueba no puede
        // pisar el de una demo de verdad.
        Storage::fake('local');

        // Y las fotos a un disco falso y vacío: DemoFotosSeeder copia las de
        // la web real, y una prueba no puede escribir en la carpeta de verdad.
        Storage::fake('public');

        $this->seed(DemoCompletoSeeder::class);
    }

    /** Una sola prueba con muchas comprobaciones: sembrar cuesta, mirar no. */
    public function test_la_demo_es_coherente_y_todas_las_pantallas_abren(): void
    {
        $this->revisarCantidades();
        $this->revisarSocios();
        $this->revisarMembresias();
        $this->revisarPrecios();
        $this->revisarPagos();
        $this->revisarTraspasosYCambios();
        $this->revisarMeson();
        $this->revisarCorreos();
        $this->revisarContratos();
        $this->revisarTalleres();
        $this->revisarAjustesYCuentas();
        $this->revisarHistorialDePrecios();
        $this->revisarInformesGuardados();
        $this->revisarFallas();
        $this->revisarPapelera();
        $this->laRevisionDeLaNocheNoEncuentraNada();
        $this->lasPantallasAbren();

        // Lo último: recuperar de la papelera cambia la base.
        $this->laPapeleraDevuelveLoBorrado();
    }

    public function test_correrlo_otra_vez_no_toca_una_base_con_socios(): void
    {
        $antes = Cliente::withTrashed()->count();

        $this->seed(DemoCompletoSeeder::class);

        $this->assertSame($antes, Cliente::withTrashed()->count(), 'Sembró encima de una base que ya tenía socios.');
    }

    // ------------------------------------------------------------------

    private function revisarCantidades(): void
    {
        $socios = Cliente::withTrashed()->count();
        $this->assertGreaterThanOrEqual(170, $socios);
        $this->assertLessThanOrEqual(195, $socios);

        // Hay de todo: si un caso deja de generarse, la demo deja de enseñarlo.
        $this->assertGreaterThan(40, Cliente::where('activo', true)->count(), 'Muy pocos socios activos.');
        $this->assertGreaterThan(30, Cliente::where('activo', false)->count(), 'Muy pocos dados de baja.');
        $this->assertGreaterThanOrEqual(2, Cliente::onlyTrashed()->count(), 'Nadie en la papelera.');
        $this->assertGreaterThanOrEqual(1, Cliente::whereNotNull('datos_borrados_en')->count(), 'A nadie se le borraron los datos.');
        $this->assertGreaterThanOrEqual(5, Cliente::where('es_menor_edad', true)->count(), 'Faltan menores.');

        foreach (Membresia::all() as $plan) {
            $this->assertTrue(Inscripcion::where('id_membresia', $plan->id)->exists(), "Nadie tiene el plan {$plan->nombre}.");
        }

        $this->assertGreaterThanOrEqual(1, Inscripcion::where('id_estado', self::PAUSADA)->count(), 'Nadie en pausa.');
        $this->assertGreaterThanOrEqual(1, HistorialCambio::where('tipo_cambio', 'reanudacion')->count(), 'Ninguna pausa terminada.');
        $this->assertGreaterThanOrEqual(20, Inscripcion::where('tipo_cambio', 'renovacion')->count(), 'Pocas renovaciones.');
        $this->assertSame(2, HistorialTraspaso::count());
        $this->assertSame(2, Inscripcion::where('id_estado', self::CAMBIADA)->count());
        $this->assertGreaterThan(0, Inscripcion::whereNotNull('id_convenio')->count(), 'Nadie con convenio.');
        $this->assertGreaterThan(0, Inscripcion::whereNotNull('id_motivo_descuento')->where('id_convenio', null)->count(), 'Ningún descuento con motivo.');
        $this->assertGreaterThan(0, Inscripcion::where('precio_final', 0)->count(), 'Ninguna cortesía.');
        $this->assertGreaterThan(3, Inscripcion::queDeben()->count(), 'Nadie debe nada.');
        $this->assertGreaterThan(2, Pago::where('tipo_pago', 'mixto')->count(), 'Ningún pago mixto.');
        $this->assertGreaterThan(5, Pago::whereNotNull('referencia_pago')->count(), 'Ninguna transferencia con número.');

        foreach (MetodoPago::where('activo', true)->get() as $metodo) {
            $this->assertTrue(Pago::where('id_metodo_pago', $metodo->id)->exists(), "Nadie pagó con {$metodo->nombre}.");
        }

        foreach ([600, 601, 602, 603] as $estado) {
            $this->assertTrue(Notificacion::where('id_estado', $estado)->exists(), "Ningún correo en estado {$estado}.");
        }

        $estadosDeContrato = Contrato::all()->map->estado()->unique()->values()->all();
        foreach (['firmado', 'pendiente', 'vencido', 'anulado', 'fallido', 'borrado'] as $estado) {
            $this->assertContains($estado, $estadosDeContrato, "Ningún contrato {$estado}.");
        }

        $this->assertGreaterThan(0, EntradaCanje::count());
        $this->assertGreaterThan(0, Nota::where('hecha', true)->count());
        $this->assertGreaterThan(0, Nota::where('hecha', false)->count());
        $this->assertGreaterThan(0, Fiado::where('pagado', true)->count());
        $this->assertGreaterThan(0, Fiado::where('pagado', false)->count());
        $this->assertSame(['asignado', 'quitado', 'reabierto'], FiadoRegistro::orderBy('accion')->pluck('accion')->unique()->values()->all());
    }

    private function revisarSocios(): void
    {
        $rut = new RutValido();

        foreach (Cliente::withTrashed()->with('inscripciones')->get() as $socio) {
            $quien = "Socio #{$socio->id}";
            $vigentes = $socio->inscripciones->whereIn('id_estado', [self::ACTIVA, self::PAUSADA]);

            // Nunca dos membresías vigentes a la vez.
            $this->assertLessThanOrEqual(1, $vigentes->count(), "{$quien} tiene dos membresías vigentes.");

            // Dado de baja = sin plan. Activo = con plan, o recién registrado sin ninguno.
            if (! $socio->activo) {
                $this->assertCount(0, $vigentes, "{$quien} está de baja con una membresía vigente.");
            } else {
                $this->assertTrue(
                    $vigentes->isNotEmpty() || $socio->inscripciones->isEmpty(),
                    "{$quien} está activo sin plan vigente: la revisión de la noche lo habría dado de baja."
                );
            }

            $this->assertSame($socio->activo ? 400 : 402, (int) $socio->id_estado, "{$quien}: id_estado no calza con activo.");

            if ($socio->trashed()) {
                $this->assertCount(0, $vigentes, "{$quien} está en la papelera con una membresía vigente.");
                $this->assertFalse($socio->pagos()->whereIn('id_estado', [200, 202, 203])->exists(), "{$quien} está en la papelera debiendo.");
            }

            if ($socio->datos_borrados_en) {
                $this->assertSame('Socio', $socio->nombres);
                $this->assertNull($socio->run_pasaporte);
                $this->assertNull($socio->email);
                $this->assertNull($socio->celular);
                $this->assertFalse((bool) $socio->activo);
                $this->assertSame(0, Notificacion::where('id_cliente', $socio->id)->count(), "{$quien}: datos borrados pero quedan sus correos.");

                continue;
            }

            // Datos personales con forma de datos chilenos de verdad.
            if (preg_match('/^\d{1,2}\.\d{3}\.\d{3}-[\dK]$/', (string) $socio->run_pasaporte)) {
                $this->assertTrue($rut->passes('rut', $socio->run_pasaporte), "{$quien}: RUT {$socio->run_pasaporte} con dígito verificador malo.");
            } else {
                $this->assertMatchesRegularExpression('/^[A-Z]{2}\d+$/', (string) $socio->run_pasaporte, "{$quien}: ni RUT ni pasaporte.");
            }

            $this->assertMatchesRegularExpression('/^(9\d{8}|\+\d{10,15})$/', (string) $socio->celular, "{$quien}: celular raro.");

            foreach (['email', 'apoderado_email'] as $campo) {
                if ($socio->{$campo}) {
                    $this->assertStringEndsWith('@example.com', $socio->{$campo}, "{$quien}: {$campo} de un dominio real.");
                }
            }

            if ($socio->fecha_nacimiento) {
                $this->assertTrue(
                    $socio->fecha_nacimiento->copy()->addYears(14)->lte($socio->created_at),
                    "{$quien} tenía menos de 14 años al inscribirse."
                );
            }

            // Menor de verdad = menor según su fecha, con apoderado completo.
            $this->assertSame((bool) $socio->es_menor_edad, $socio->es_menor, "{$quien}: es_menor_edad no calza con su edad.");

            if ($socio->es_menor_edad) {
                $this->assertTrue($socio->tiene_apoderado_completo, "{$quien}: menor sin apoderado completo.");
                $this->assertTrue($rut->passes('rut', $socio->apoderado_rut), "{$quien}: RUT del apoderado malo.");
                $this->assertNotSame($socio->email, $socio->apoderado_email);
            }
        }
    }

    private function revisarMembresias(): void
    {
        $hoy = today();

        foreach (Inscripcion::withTrashed()->with(['membresia', 'inscripcionesPosteriores'])->get() as $i) {
            $quien = "Membresía #{$i->id} ({$i->membresia->nombre}, estado {$i->id_estado})";
            $estado = (int) $i->id_estado;
            $tieneSucesora = $i->inscripcionesPosteriores->isNotEmpty();

            $this->assertContains($estado, [self::ACTIVA, self::PAUSADA, self::VENCIDA, self::CAMBIADA], "{$quien}: estado que el sistema no produce.");
            $this->assertTrue($i->fecha_inicio->lte($i->fecha_vencimiento), "{$quien} vence antes de empezar.");
            $this->assertSame($i->created_at->toDateString(), $i->fecha_inscripcion->toDateString(), "{$quien}: no se registró el día que dice.");
            $this->assertTrue($i->created_at->lte(now()), "{$quien} creada en el futuro.");

            // El estado sale de las fechas.
            match ($estado) {
                self::ACTIVA => $this->assertTrue(
                    $i->fecha_vencimiento->gte($hoy) && ! $i->pausada,
                    "{$quien}: activa pero vencida o en pausa."
                ),
                self::VENCIDA => $this->assertTrue(
                    $i->fecha_vencimiento->lt($hoy) || $tieneSucesora,
                    "{$quien}: vencida con días por delante y sin haberse renovado."
                ),
                self::PAUSADA => $this->revisarPausada($i, $quien),
                self::CAMBIADA => $this->assertTrue(
                    $tieneSucesora && $i->fecha_vencimiento->isSameDay($i->inscripcionesPosteriores->first()->fecha_inicio),
                    "{$quien}: cambiada sin la nueva que empieza ese día."
                ),
            };

            if ($estado !== self::PAUSADA) {
                $this->assertFalse((bool) $i->pausada, "{$quien}: lleva la marca de pausa sin estar pausada.");
                $this->assertNull($i->fecha_pausa_inicio, "{$quien}: quedó con fecha de pausa.");
                $this->assertNull($i->dias_restantes_al_pausar, "{$quien}: quedó con días guardados.");
            }

            // Las pausas: las del plan, contadas en el historial.
            $this->assertSame((int) $i->membresia->max_pausas, (int) $i->max_pausas_permitidas, "{$quien}: pausas permitidas distintas a las del plan.");
            $this->assertLessThanOrEqual((int) $i->max_pausas_permitidas, (int) $i->pausas_realizadas, "{$quien}: más pausas de las permitidas.");
            $this->assertSame(
                (int) $i->pausas_realizadas,
                HistorialCambio::where('inscripcion_id', $i->id)->where('tipo_cambio', 'pausa')->count(),
                "{$quien}: pausas_realizadas no calza con el historial."
            );

            // El vencimiento es el del plan, más lo que corrieron las pausas ya terminadas.
            if ($estado !== self::CAMBIADA) {
                $corridos = (int) HistorialCambio::where('inscripcion_id', $i->id)->where('tipo_cambio', 'reanudacion')->get()->sum(fn ($h) => $h->detalles['dias_en_pausa']);
                $esperado = $i->membresia->vencimientoDesde($i->fecha_inicio)->addDays($corridos);

                $this->assertSame($esperado->format('Y-m-d'), $i->fecha_vencimiento->format('Y-m-d'), "{$quien}: el vencimiento no sale de su plan y sus pausas.");
            }

            // La renovación queda enlazada y en el historial.
            if ($i->tipo_cambio === 'renovacion' && $i->es_cambio_plan) {
                $anterior = Inscripcion::withTrashed()->find($i->id_inscripcion_anterior);
                $this->assertNotNull($anterior, "{$quien}: renovación sin la anterior.");
                $this->assertSame((int) $anterior->id_cliente, (int) $i->id_cliente, "{$quien}: renueva la membresía de otro.");
                $this->assertSame(self::VENCIDA, (int) $anterior->id_estado, "{$quien}: la renovada no quedó vencida.");
                $this->assertTrue($i->fecha_inicio->gt($anterior->fecha_vencimiento), "{$quien}: se solapa con la anterior.");
                $this->assertTrue(
                    HistorialCambio::where('tipo_cambio', 'renovacion')->where('entidad_id', $i->id)->exists(),
                    "{$quien}: renovación sin su fila en el historial."
                );
            }
        }
    }

    private function revisarPausada(Inscripcion $i, string $quien): void
    {
        $this->assertTrue((bool) $i->pausada, "{$quien}: pausada sin la marca.");
        $this->assertNotNull($i->fecha_pausa_inicio, "{$quien}: sin fecha de inicio de pausa.");
        $this->assertTrue($i->fecha_pausa_inicio->lte(today()), "{$quien}: la pausa empieza en el futuro.");
        $this->assertNotEmpty($i->razon_pausa, "{$quien}: pausada sin motivo.");
        $this->assertSame(
            \App\Models\Inscripcion::diasEntre($i->fecha_pausa_inicio, $i->fecha_vencimiento),
            (int) $i->dias_restantes_al_pausar,
            "{$quien}: los días guardados no son los que le quedaban al pausar."
        );

        if ($i->pausa_indefinida) {
            $this->assertNull($i->fecha_pausa_fin);
            $this->assertNull($i->dias_pausa);
        } else {
            // Si ya hubiera terminado, la revisión de la noche la habría reanudado.
            $this->assertTrue($i->fecha_pausa_fin->gt(today()), "{$quien}: la pausa ya terminó y sigue pausada.");
            $this->assertSame((int) $i->dias_pausa, \App\Models\Inscripcion::diasEntre($i->fecha_pausa_inicio, $i->fecha_pausa_fin));
        }
    }

    private function revisarPrecios(): void
    {
        foreach (Inscripcion::withTrashed()->with('precioAcordado')->get() as $i) {
            $quien = "Membresía #{$i->id}";
            $base = (int) $i->precio_base;
            $descuento = (int) $i->descuento_aplicado;
            $final = (int) $i->precio_final;

            $this->assertSame($base - $descuento, $final, "{$quien}: base menos descuento no da el final.");
            $this->assertGreaterThanOrEqual(0, $final);
            $this->assertSame((int) $i->id_membresia, (int) $i->precioAcordado->id_membresia, "{$quien}: el precio acordado es de otro plan.");
            $this->assertSame((int) round($i->precioAcordado->precio_normal), $base, "{$quien}: la base no es el precio del plan.");

            if ($i->es_cambio_plan && $i->tipo_cambio === 'upgrade') {
                $this->assertSame((int) $i->credito_plan_anterior, $descuento, "{$quien}: el crédito del cambio no es el descuento.");
                $this->assertSame((int) $i->precio_nuevo_plan, $base);

                continue;
            }

            // Lo que rebaja el convenio, y si hay más, un motivo que lo explique.
            $porConvenio = $base - PrecioAcordado::para($i->precioAcordado, $i->id_convenio);
            $this->assertGreaterThanOrEqual($porConvenio, $descuento, "{$quien}: cobra más que el precio de su convenio.");

            if ($descuento > $porConvenio) {
                $this->assertNotNull($i->id_motivo_descuento, "{$quien}: descuento a mano sin motivo.");
            }

            // El convenio de la membresía es el del socio (salvo que se le
            // hayan borrado los datos, que también le borra el convenio).
            $socio = Cliente::withTrashed()->find($i->id_cliente);
            if ($i->id_convenio && ! $socio->datos_borrados_en) {
                $this->assertSame((int) $socio->id_convenio, (int) $i->id_convenio, "{$quien}: con el convenio de otro.");
            }
        }
    }

    private function revisarPagos(): void
    {
        $metodosActivos = MetodoPago::where('activo', true)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $transferencia = (int) MetodoPago::where('nombre', 'Transferencia')->value('id');

        foreach (Inscripcion::withTrashed()->with('pagos')->get() as $i) {
            $quien = "Membresía #{$i->id}";
            $abonado = (int) $i->pagos->sum('monto_abonado');

            $this->assertLessThanOrEqual((int) $i->precio_final, $abonado, "{$quien}: se cobró más que su precio.");

            // Lo que haría la revisión de la noche: no tiene que cambiar nada.
            if ($i->id_estado !== self::CAMBIADA) {
                $this->assertSame(0, $i->recalcularSusPagos(false), "{$quien}: sus pagos no cuadran entre sí.");
            }

            foreach ($i->pagos as $pago) {
                $p = "Pago #{$pago->id} de la membresía #{$i->id}";

                $this->assertSame((int) $i->id_cliente, (int) $pago->id_cliente, "{$p}: es de otro socio que su membresía.");
                $this->assertTrue($pago->fecha_pago->lte(today()), "{$p}: fechado en el futuro.");
                $this->assertTrue($pago->fecha_pago->gte($i->fecha_inscripcion->copy()->startOfDay()), "{$p}: pagado antes de inscribirse.");
                $this->assertContains($pago->tipo_pago, ['completo', 'parcial', 'pendiente', 'mixto'], "{$p}: tipo raro.");

                if ((int) $pago->monto_abonado > 0) {
                    $this->assertContains((int) $pago->id_metodo_pago, $metodosActivos, "{$p}: cobrado sin un medio de pago válido.");
                }

                if ($pago->tipo_pago === 'pendiente') {
                    $this->assertSame(0, (int) $pago->monto_abonado);
                    $this->assertNull($pago->id_metodo_pago);
                }

                if ($pago->tipo_pago === 'mixto') {
                    $this->assertNotNull($pago->id_metodo_pago2, "{$p}: mixto sin segundo medio.");
                    $this->assertNotSame((int) $pago->id_metodo_pago, (int) $pago->id_metodo_pago2, "{$p}: mixto con el mismo medio dos veces.");
                    $this->assertSame((int) $pago->monto_abonado, (int) $pago->monto_metodo1 + (int) $pago->monto_metodo2, "{$p}: el reparto no suma lo abonado.");
                }

                if ($pago->referencia_pago) {
                    $this->assertContains($transferencia, [(int) $pago->id_metodo_pago, (int) $pago->id_metodo_pago2], "{$p}: número de transferencia en un pago sin transferencia.");
                }
            }
        }
    }

    private function revisarTraspasosYCambios(): void
    {
        foreach (HistorialTraspaso::all() as $t) {
            $inscripcion = Inscripcion::find($t->inscripcion_destino_id);

            $this->assertSame($t->inscripcion_origen_id, $t->inscripcion_destino_id, 'El traspaso mueve la misma membresía, no crea otra.');
            $this->assertSame((int) $t->cliente_destino_id, (int) $inscripcion->id_cliente);
            $this->assertTrue((bool) $inscripcion->es_traspaso);
            $this->assertSame((int) $t->cliente_origen_id, (int) $inscripcion->id_cliente_original);
            $this->assertSame(\App\Models\Inscripcion::diasEntre($t->fecha_traspaso, $t->fecha_vencimiento_original), (int) $t->dias_restantes_traspasados);
            $this->assertSame((int) $t->deuda_transferida > 0, (bool) $t->se_transfirio_deuda);
            $this->assertFalse(Cliente::find($t->cliente_origen_id)->activo, 'Quien cedió su membresía sigue activo sin plan.');
            $this->assertTrue($inscripcion->pagos->every(fn ($p) => (int) $p->id_cliente === (int) $t->cliente_destino_id), 'Los pagos no se fueron con la membresía.');
        }

        foreach (Inscripcion::where('id_estado', self::CAMBIADA)->get() as $vieja) {
            $nueva = Inscripcion::where('id_inscripcion_anterior', $vieja->id)->sole();

            $this->assertTrue((bool) $nueva->es_cambio_plan);
            $this->assertSame('upgrade', $nueva->tipo_cambio);
            $this->assertGreaterThan((int) $vieja->precio_final, (int) $nueva->precio_base, 'Un cambio de plan que no es mejora.');
            $this->assertSame(min((int) $vieja->pagos()->sum('monto_abonado'), (int) $nueva->precio_base), (int) $nueva->credito_plan_anterior);
            $this->assertSame((int) $nueva->precio_final, (int) $nueva->diferencia_a_pagar);
        }
    }

    private function revisarMeson(): void
    {
        $activos = MetodoPago::where('activo', true)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $tope = Ajustes::numero('meson.tope_fiado');

        foreach (Fiado::withTrashed()->get() as $f) {
            $quien = "Fiado #{$f->id} ({$f->concepto})";

            $this->assertGreaterThan(0, (int) $f->monto, $quien);
            $this->assertTrue((bool) $f->id_cliente xor filled($f->nombre), "{$quien}: tiene que ser de un socio o de un nombre, no de los dos ni de nadie.");

            if ($f->id_cliente) {
                $this->assertNull($f->celular, "{$quien}: el celular del socio sale de su ficha.");
            }

            if ($f->pagado) {
                $this->assertNotNull($f->pagado_en, "{$quien}: pagado sin fecha.");
                $this->assertContains((int) $f->id_metodo_pago, $activos, "{$quien}: pagado sin medio.");
                $this->assertNotNull($f->id_usuario_cobro, "{$quien}: pagado sin quién cobró.");
                $this->assertTrue($f->pagado_en->gte($f->created_at), "{$quien}: pagado antes de anotarse.");
            } else {
                $this->assertNull($f->pagado_en);
                $this->assertNull($f->id_metodo_pago);
                $this->assertNull($f->id_usuario_cobro);
            }

            if ($f->trashed()) {
                $this->assertNotNull($f->id_usuario_quito, "{$quien}: quitada sin quién la quitó.");
                $this->assertTrue(FiadoRegistro::where('accion', 'quitado')->where('monto', $f->monto)->exists());
            }
        }

        // El abono que partió una línea: la parte pagada y lo que queda, misma fecha.
        $parte = Fiado::where('concepto', 'like', '% (abono)')->sole();
        $this->assertTrue((bool) $parte->pagado);
        $this->assertTrue(Fiado::where('pagado', false)->where('id_cliente', $parte->id_cliente)
            ->where('concepto', str_replace(' (abono)', '', $parte->concepto))
            ->where('created_at', $parte->created_at)->exists(), 'El abono no dejó la otra mitad de la línea.');

        // Nadie pasa el tope del mesón.
        Fiado::debiendo()->get()->groupBy(fn (Fiado $f) => $f->claveDeCuenta())
            ->each(fn ($lineas, $cuenta) => $this->assertLessThanOrEqual($tope, $lineas->sum('monto'), "La cuenta {$cuenta} pasa el tope."));

        // A quien se le borraron los datos no le queda deuda en el mesón.
        foreach (Cliente::whereNotNull('datos_borrados_en')->pluck('id') as $id) {
            $this->assertFalse(Fiado::debiendo()->where('id_cliente', $id)->exists());
        }

        foreach (Nota::all() as $nota) {
            $this->assertSame((bool) $nota->hecha, $nota->hecha_en !== null, "Nota #{$nota->id}: hecha sin fecha o al revés.");
            $this->assertSame((bool) $nota->hecha, $nota->id_usuario_hecha !== null, "Nota #{$nota->id}: hecha sin quién.");

            if ($nota->hecha) {
                $this->assertTrue($nota->hecha_en->gte($nota->created_at), "Nota #{$nota->id}: hecha antes de escribirse.");
            }
        }

        $this->assertTrue(Nota::all()->contains(fn (Nota $n) => $n->estaVieja()), 'Ninguna nota pendiente vieja.');
    }

    private function revisarCorreos(): void
    {
        foreach (Notificacion::with(['logs', 'cliente' => fn ($q) => $q->withTrashed()])->get() as $n) {
            $quien = "Correo #{$n->id} (estado {$n->id_estado})";

            $this->assertStringEndsWith('@example.com', $n->email_destino, "{$quien}: a un dominio real.");
            $this->assertContains($n->email_destino, array_filter([$n->cliente->email, $n->cliente->apoderado_email]), "{$quien}: no va ni al socio ni a su apoderado.");
            $this->assertTrue($n->logs->contains('accion', 'programada'), "{$quien}: sin su registro de programado.");

            match ((int) $n->id_estado) {
                601 => $this->assertTrue($n->fecha_envio !== null && $n->logs->contains('accion', 'enviada'), "{$quien}: enviado sin fecha o sin registro."),
                602 => $this->assertTrue($n->error_mensaje !== null && $n->intentos >= 1 && $n->logs->contains('accion', 'fallida'), "{$quien}: fallido sin error."),
                600 => $this->assertNull($n->fecha_envio, "{$quien}: pendiente con fecha de envío."),
                603 => $this->assertTrue($n->logs->contains('accion', 'cancelada'), "{$quien}: cancelado sin registro."),
            };
        }

        // Nada que pueda salir con los automáticos apagados: los manuales
        // pendientes o con reintentos salen igual.
        $this->assertSame(0, Notificacion::pendientes()->manuales()->count(), 'Hay correos manuales esperando a salir.');
        $this->assertSame(0, Notificacion::fallidas()->whereColumn('intentos', '<', 'max_intentos')->count(), 'Hay correos fallidos que se reintentarían.');
    }

    private function revisarContratos(): void
    {
        $this->assertSame(Contrato::count(), Contrato::distinct()->count('token_hash'));

        foreach (Contrato::with(['cliente' => fn ($q) => $q->withTrashed()])->get() as $c) {
            $quien = "Contrato #{$c->id} ({$c->estado()})";

            if ($c->email_destino) {
                $this->assertStringEndsWith('@example.com', $c->email_destino, $quien);
            }

            if ($c->firmado_en) {
                $this->assertNotNull($c->huella, "{$quien}: firmado sin huella.");
                $this->assertSame(1, $c->version_contrato);
                $this->assertNull($c->anulado_en, "{$quien}: firmado y anulado.");

                if (! $c->datos_borrados_en) {
                    $this->assertTrue($c->integro(), "{$quien}: el texto no calza con su huella.");
                    $this->assertNotEmpty($c->firmante_rut);
                    $this->assertSame($c->firmado_en->toDateString(), $c->cliente->contrato_firmado_en?->toDateString(), "{$quien}: la ficha no anota la firma.");
                    $this->assertSame($c->firmante_tipo === 'apoderado', (bool) $c->cliente->es_menor_edad);
                }
            }

            if ($c->estado() === 'pendiente') {
                $this->assertNotNull($c->enviado_en);
                $this->assertTrue($c->vence_en->isFuture());
            }

            if ($c->estado() === 'fallido') {
                $this->assertNull($c->enviado_en, "{$quien}: no salió pero dice enviado.");
            }
        }

        // Un socio tiene a lo más un enlace vivo: mandar otro anula el anterior.
        Contrato::whereNull('firmado_en')->whereNull('anulado_en')->get()->groupBy('id_cliente')
            ->each(fn ($c, $socio) => $this->assertCount(1, $c, "El socio #{$socio} tiene dos enlaces vivos."));
    }

    private function revisarTalleres(): void
    {
        foreach (CobroTaller::with('horas', 'taller')->get() as $cobro) {
            $quien = "Cobro {$cobro->periodo} de {$cobro->taller->nombre}";

            $this->assertEqualsWithDelta($cobro->horas()->sum('horas'), $cobro->getAttribute('horas'), 0.001, "{$quien}: las horas no son las de sus clases.");
            $this->assertSame((int) round($cobro->getAttribute('horas') * $cobro->precio_hora), $cobro->total, "{$quien}: horas por precio no da el total.");
            $this->assertSame(CobroTaller::desglosar($cobro->total), ['neto' => $cobro->neto, 'iva' => $cobro->iva], "{$quien}: el IVA no cuadra.");

            // Cerrar el mes engancha TODAS sus clases.
            $mes = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $cobro->periodo . '-01');
            $this->assertFalse(HoraTaller::where('id_taller', $cobro->id_taller)->whereNull('id_cobro')
                ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])->exists(), "{$quien}: quedó una clase suelta en un mes cerrado.");

            if ($cobro->pagado_en) {
                $this->assertNotNull($cobro->folio, "{$quien}: pagado sin factura.");
                $this->assertTrue($cobro->pagado_en->gte($cobro->emitido_en));
                $this->assertTrue($cobro->pagado_en->lte(today()));
            }
        }

        $this->assertSame(CobroTaller::count(), CobroTaller::query()->distinct()->count(DB::raw("id_taller || '-' || periodo")), 'Un mes cobrado dos veces.');

        foreach (CotizacionTaller::all() as $cotizacion) {
            $horas = round(collect($cotizacion->lineasIncluidas())->sum('horas'), 2);

            $this->assertEqualsWithDelta($horas, $cotizacion->horas, 0.001, "Cotización N° {$cotizacion->numero}: horas mal sumadas.");
            $this->assertSame((int) round($cotizacion->horas * $cotizacion->precio_hora), $cotizacion->total);
            $this->assertSame($cotizacion->total, $cotizacion->neto + $cotizacion->iva);
            $this->assertNotEmpty($cotizacion->lineasQuitadas(), 'La cotización no enseña ninguna clase quitada.');
        }

        $this->assertSame(CotizacionTaller::count(), CotizacionTaller::distinct()->count('numero'));
    }

    private function revisarAjustesYCuentas(): void
    {
        $this->assertFalse(Ajustes::activo('tareas.correos_automaticos'), 'Los correos automáticos quedaron encendidos.');
        $this->assertSame('PRO GYM', Ajustes::obtener('gimnasio.nombre'));
        $this->assertStringContainsString('Rengo 386', Ajustes::obtener('gimnasio.direccion'));
        $this->assertNotEmpty(\App\Http\Controllers\Panel\FiadoController::listaDePrecios());

        foreach (Convenio::whereNotNull('contacto_email')->pluck('contacto_email') as $correo) {
            $this->assertStringEndsWith('@example.com', $correo);
        }

        $accesos = Storage::disk('local')->get(DemoCompletoSeeder::ARCHIVO_ACCESOS);

        // Las cuatro cuentas, y nada más: ni las de DatabaseSeeder ni otras.
        $this->assertSame(4, User::count());

        foreach ([
            'admin@demo.test' => ['Administrador', true],
            'recepcion@demo.test' => ['Recepcionista', true],
            'tarde@demo.test' => ['Recepcionista', true],
            // La que se fue: desactivada, no entra aunque tenga clave.
            'antes@demo.test' => ['Recepcionista', false],
        ] as $correo => [$rol, $activa]) {
            $usuario = User::where('email', $correo)->sole();

            $this->assertSame($rol, $usuario->rol->nombre);
            $this->assertSame($activa, (bool) $usuario->activo, "{$correo}: activa o no, al revés.");
            $this->assertFalse((bool) $usuario->two_factor_enabled);
            $this->assertNotNull($usuario->email_verified_at);

            // La clave del archivo es la de la cuenta, y no es una de manual.
            $this->assertMatchesRegularExpression('/' . preg_quote($correo, '/') . '\s+clave:\s+(\S{16})/', $accesos);
            preg_match('/' . preg_quote($correo, '/') . '\s+clave:\s+(\S{16})/', $accesos, $m);
            $this->assertTrue(Hash::check($m[1], $usuario->password), "La clave anotada de {$correo} no entra.");
            $this->assertFalse(Hash::check('password', $usuario->password));
        }
    }

    /**
     * Los precios no se pisan: cada subida cierra un tramo y abre otro, con su
     * fila en el historial, y cada membresía se vendió al precio que regía el
     * día en que se vendió.
     */
    private function revisarHistorialDePrecios(): void
    {
        $this->assertGreaterThanOrEqual(2, HistorialPrecio::count(), 'Ningún cambio de precio en la demo.');

        foreach (HistorialPrecio::with('precioMembresia')->get() as $h) {
            $nuevo = $h->precioMembresia;
            $quien = "Cambio de precio #{$h->id}";

            $this->assertSame((int) $h->precio_nuevo, (int) $nuevo->precio_normal, "{$quien}: el precio nuevo no es el del tramo que abrió.");
            $this->assertSame($h->created_at->toDateString(), $nuevo->fecha_vigencia_desde->toDateString(), "{$quien}: el tramo no empieza el día del cambio.");

            // El tramo de antes se cerró ese mismo día, con el precio anterior.
            $anterior = PrecioMembresia::where('id_membresia', $nuevo->id_membresia)
                ->whereDate('fecha_vigencia_hasta', $nuevo->fecha_vigencia_desde)
                ->sole();
            $this->assertFalse((bool) $anterior->activo, "{$quien}: el precio de antes sigue activo.");
            $this->assertSame((int) $h->precio_anterior, (int) $anterior->precio_normal, "{$quien}: el precio anterior no es el del tramo cerrado.");
            $this->assertNotSame((int) $h->precio_anterior, (int) $h->precio_nuevo);
            $this->assertNotEmpty($h->razon_cambio);
        }

        // Un solo precio vigente por plan, y es el que ofrece el sistema hoy.
        foreach (Membresia::all() as $plan) {
            $vigentes = PrecioMembresia::where('id_membresia', $plan->id)->where('activo', true)->get();
            $this->assertCount(1, $vigentes, "{$plan->nombre}: más de un precio vigente.");
            $this->assertSame($vigentes->first()->id, $plan->precioVigente()?->id);
        }

        // Cada venta, al precio que regía ese día.
        $viejas = 0;

        foreach (Inscripcion::withTrashed()->with('precioAcordado')->get() as $i) {
            $tramo = $i->precioAcordado;
            $dia = $i->created_at->toDateString();

            $this->assertTrue($tramo->fecha_vigencia_desde->toDateString() <= $dia, "Membresía #{$i->id}: vendida con un precio que aún no regía.");

            if ($tramo->fecha_vigencia_hasta) {
                $this->assertTrue($dia <= $tramo->fecha_vigencia_hasta->toDateString(), "Membresía #{$i->id}: vendida con un precio que ya no regía.");
                $viejas++;
            }
        }

        $this->assertGreaterThan(10, $viejas, 'Casi nada se vendió al precio de antes: la subida no se nota.');
    }

    /** Los informes del constructor: recetas válidas que se pueden volver a abrir. */
    private function revisarInformesGuardados(): void
    {
        $admin = User::where('email', 'admin@demo.test')->sole();
        $this->assertGreaterThanOrEqual(3, InformeGuardado::where('id_usuario', $admin->id)->count(), 'El administrador no tiene informes guardados.');

        $constructor = app(ConstructorInformes::class);
        $catalogo = $constructor->catalogo();

        foreach (InformeGuardado::all() as $informe) {
            $quien = "Informe «{$informe->nombre}»";
            $receta = $informe->configuracion;

            $this->assertTrue($constructor->existe($informe->modulo), "{$quien}: módulo que no existe.");
            $columnas = $catalogo[$informe->modulo]['columnas'];

            $this->assertSame([], array_diff($receta['columnas'], array_keys($columnas)), "{$quien}: pide columnas que no existen.");
            $this->assertSame([], array_diff(array_keys($receta['filtros']), array_keys($columnas)), "{$quien}: filtra por algo que no existe.");
            $this->assertArrayHasKey($receta['orden'], $columnas);
            $this->assertArrayNotHasKey('derivada', $columnas[$receta['orden']], "{$quien}: ordena por una columna calculada.");
            $this->assertContains($receta['limite'], ConstructorInformes::LIMITES);

            $resultado = $constructor->ejecutar($informe->modulo, $receta);

            $this->assertEqualsCanonicalizing($receta['columnas'], array_column($resultado['columnas'], 'clave'), "{$quien}: no salen las columnas guardadas.");
            $this->assertGreaterThan(0, $resultado['cuantas'], "{$quien}: sale vacío con la demo.");
        }

        // Cada uno ve los suyos en la pantalla del constructor.
        $this->actingAs($admin)->get('/panel/reportes/constructor')->assertOk()
            ->assertInertia(fn ($p) => $p->has('guardados', InformeGuardado::where('id_usuario', $admin->id)->count()));
    }

    /** El registro de fallas, con la forma que les da RegistroDeFallas. */
    private function revisarFallas(): void
    {
        $fallas = Falla::all();

        $this->assertGreaterThanOrEqual(5, $fallas->count());
        $this->assertLessThanOrEqual(8, $fallas->count());
        $this->assertEqualsCanonicalizing(['navegador', 'servidor'], $fallas->pluck('origen')->unique()->values()->all());
        $this->assertTrue($fallas->contains(fn ($f) => $f->resuelta_en !== null), 'Ninguna falla resuelta.');
        $this->assertTrue($fallas->contains(fn ($f) => $f->resuelta_en === null), 'Ninguna falla abierta.');

        foreach ($fallas as $f) {
            $quien = "Falla #{$f->id} ({$f->mensaje})";

            $this->assertSame(64, strlen($f->huella));
            $this->assertGreaterThanOrEqual(1, $f->veces);
            $this->assertTrue($f->primera_vez->lte($f->ultima_vez), "{$quien}: la última vez antes que la primera.");
            $this->assertTrue($f->ultima_vez->lte(now()), "{$quien}: en el futuro.");
            // Lo de más de 90 días lo borra la revisión del día: no estaría.
            $this->assertTrue($f->ultima_vez->gte(now()->subDays(90)), "{$quien}: tan vieja que ya se habría borrado.");

            if ($f->resuelta_en) {
                $this->assertTrue($f->resuelta_en->gte($f->ultima_vez), "{$quien}: resuelta antes de su última vez.");
            }

            if ($f->id_usuario) {
                $this->assertNotNull(User::find($f->id_usuario), "{$quien}: de un usuario que no existe.");
            }

            // Sin claves ni dominios: rutas relativas y correos que no llegan a nadie.
            foreach (array_keys($f->contexto ?? []) as $clave) {
                $this->assertDoesNotMatchRegularExpression('/pass|clave|contrase|token|secret|api[_-]?key|authorization|cookie|smtp/i', (string) $clave, $quien);
            }

            if (preg_match_all('/[\w.+-]+@[\w-]+\.[\w.]+/', $f->mensaje . json_encode($f->contexto), $correos)) {
                foreach ($correos[0] as $correo) {
                    $this->assertStringEndsWith('@example.com', $correo, $quien);
                }
            }

            $this->assertStringNotContainsString('://', (string) $f->url, "{$quien}: dirección con dominio.");
            $this->assertStringNotContainsString(base_path(), (string) $f->traza . $f->lugar, "{$quien}: ruta del disco.");
        }

        // Los correos que rebotaron, contados igual que en su registro.
        $rebote = $fallas->firstWhere('mensaje', 'Error al enviar notificación');
        if ($rebote) {
            $this->assertSame(
                LogNotificacion::where('accion', 'fallida')->where('detalle', 'like', '550 5.1.1%')->count(),
                $rebote->veces,
                'La falla de los correos no cuenta los rebotes que hubo.'
            );
            $this->assertSame(602, (int) Notificacion::find($rebote->contexto['id'])->id_estado);
        }
    }

    /**
     * La papelera tiene de todo lo que se puede borrar desde el panel, y cada
     * cosa quedó como la deja su pantalla al borrarla.
     */
    private function revisarPapelera(): void
    {
        // La membresía borrada, con su pago de $0 a la misma hora.
        $borrada = Inscripcion::onlyTrashed()->sole();
        $this->assertSame(self::ACTIVA, (int) $borrada->id_estado);
        $this->assertSame(0, (int) Pago::withTrashed()->where('id_inscripcion', $borrada->id)->sum('monto_abonado'), 'Se borró una membresía con plata cobrada.');
        $this->assertSame(0, Pago::where('id_inscripcion', $borrada->id)->count(), 'Su pago en cero no se fue con ella.');
        $this->assertTrue(Pago::onlyTrashed()->where('id_inscripcion', $borrada->id)->get()->every(
            fn (Pago $p) => $p->deleted_at->equalTo($borrada->deleted_at)
        ), 'El pago en cero no lleva la hora de la membresía: la papelera no lo devolvería.');
        $this->assertTrue($borrada->deleted_at->gt($borrada->created_at));
        $this->assertFalse(
            Inscripcion::where('id_cliente', $borrada->id_cliente)->conMembresiaVigente()->exists(),
            'El socio tiene otra vigente: la membresía borrada no podría volver.'
        );

        // El abono anulado: cabe en lo que debe su membresía.
        $anulado = Pago::onlyTrashed()->where('monto_abonado', '>', 0)->sole();
        $suya = $anulado->inscripcion()->sole();
        $this->assertLessThanOrEqual((int) $suya->precio_final, (int) $suya->pagos()->sum('monto_abonado') + (int) $anulado->monto_abonado);
        $this->assertTrue($anulado->deleted_at->gt($anulado->created_at));

        // La cotización repetida: el número sigue usado.
        $repetida = CotizacionTaller::onlyTrashed()->sole();
        $this->assertSame((int) CotizacionTaller::withTrashed()->max('numero'), (int) $repetida->numero);

        $admin = User::where('email', 'admin@demo.test')->sole();
        $this->actingAs($admin)->get('/panel/papelera')->assertOk()->assertInertia(
            fn ($p) => $p->where('grupos', fn ($grupos) => collect($grupos)->pluck('clave')->sort()->values()->all()
                === ['clientes', 'cotizaciones', 'inscripciones', 'pagos'])
        );
    }

    /** Recuperar desde la papelera funciona con lo que la demo dejó ahí. */
    private function laPapeleraDevuelveLoBorrado(): void
    {
        $admin = User::where('email', 'admin@demo.test')->sole();

        $borrada = Inscripcion::onlyTrashed()->sole();
        $this->actingAs($admin)->patch("/panel/papelera/inscripciones/{$borrada->id}/restaurar")->assertSessionHas('success');
        $borrada = Inscripcion::find($borrada->id);
        $this->assertNotNull($borrada, 'La membresía no volvió.');
        $this->assertSame(1, $borrada->pagos()->count(), 'Volvió sin su pago en cero.');
        $this->assertSame((int) $borrada->precio_final, (int) $borrada->monto_pendiente, 'Volvió sin deber lo que debía.');

        $anulado = Pago::onlyTrashed()->sole();
        $this->actingAs($admin)->patch("/panel/papelera/pagos/{$anulado->id}/restaurar")->assertSessionHas('success');
        $this->assertNotNull(Pago::find($anulado->id), 'El abono anulado no volvió.');
        $this->assertSame(0, $anulado->inscripcion->recalcularSusPagos(false), 'Al volver, los pagos de su membresía no cuadran.');

        $repetida = CotizacionTaller::onlyTrashed()->sole();
        $this->actingAs($admin)->patch("/panel/papelera/cotizaciones/{$repetida->id}/restaurar")->assertSessionHas('success');
        $this->assertNotNull(CotizacionTaller::find($repetida->id));
    }

    /**
     * Las tareas de cada noche, de verdad: marcar vencidas, reanudar pausas,
     * cuadrar pagos y dar de baja a quien se quedó sin plan. Sobre datos
     * coherentes no tienen nada que hacer.
     */
    private function laRevisionDeLaNocheNoEncuentraNada(): void
    {
        $foto = fn () => [
            Inscripcion::withTrashed()->orderBy('id')->get(['id', 'id_estado', 'fecha_vencimiento', 'pausada'])->toArray(),
            Cliente::withTrashed()->orderBy('id')->get(['id', 'activo', 'id_estado'])->toArray(),
            Pago::withTrashed()->orderBy('id')->get(['id', 'id_estado', 'monto_pendiente', 'monto_total'])->toArray(),
        ];

        $antes = $foto();

        Artisan::call('inscripciones:actualizar-estados');
        Artisan::call('pagos:sincronizar-estados');
        Artisan::call('clientes:desactivar-vencidos');

        $despues = $foto();

        foreach (['membresías', 'socios', 'pagos'] as $n => $que) {
            $this->assertSame($antes[$n], $despues[$n], "La revisión de la noche cambió {$que}: la demo no era coherente.");
        }
    }

    /**
     * Todas las pantallas GET del panel, con fichas de verdad detrás (como
     * TodasLasPantallasAbrenTest), y además una vuelta por las fichas de los
     * casos raros: el pausado, el menor, el de los datos borrados, el traspaso,
     * el cambio de plan.
     */
    private function lasPantallasAbren(): void
    {
        $admin = User::where('email', 'admin@demo.test')->sole();
        $recepcion = User::where('email', 'recepcion@demo.test')->sole();

        $socio = Cliente::where('activo', true)->whereHas('contratos', fn ($q) => $q->whereNotNull('firmado_en'))->firstOrFail();
        $valores = [
            'cliente' => $socio->uuid,
            'inscripcion' => Inscripcion::where('id_estado', self::PAUSADA)->firstOrFail()->uuid,
            'pago' => Pago::where('tipo_pago', 'mixto')->firstOrFail()->uuid,
            'membresia' => Membresia::where('nombre', 'Mensual')->firstOrFail()->uuid,
            'convenio' => Convenio::where('nombre', 'INACAP')->firstOrFail()->uuid,
            'tipo' => 'servicio',
            'grupo' => 'gimnasio',
            'notificacion' => Notificacion::where('id_estado', 601)->firstOrFail()->uuid,
            'tipoNotificacion' => (string) \App\Models\TipoNotificacion::where('codigo', 'bienvenida')->value('id'),
            'modulo' => 'pagos',
            'texto' => 'terminos',
            'contrato' => Contrato::whereNotNull('firmado_en')->whereNull('datos_borrados_en')->firstOrFail()->uuid,
            'taller' => \App\Models\Taller::firstOrFail()->uuid,
            'cotizacion' => CotizacionTaller::firstOrFail()->uuid,
            'rutina' => \App\Models\Rutina::firstOrFail()->uuid,
            'ejercicio' => \App\Models\Ejercicio::firstOrFail()->uuid,
        ];

        $rotas = [];
        $miradas = 0;

        foreach (Route::getRoutes() as $ruta) {
            $nombre = (string) $ruta->getName();

            if (! str_starts_with($nombre, 'panel.') || ! in_array('GET', $ruta->methods(), true)) {
                continue;
            }

            $url = $this->rellenar($ruta->uri(), $valores);

            if ($url === null) {
                $rotas[] = "{$nombre}: no sé con qué rellenar «{$ruta->uri()}»";

                continue;
            }

            $miradas++;
            $codigo = $this->actingAs($admin)->get($url)->baseResponse->getStatusCode();

            // La demo no trae fotos: el retrato de un socio sin foto es un 404 legítimo.
            $sinFoto = $nombre === 'panel.clientes.retrato' && $codigo === 404;

            if (! in_array($codigo, [200, 302], true) && ! $sinFoto) {
                $rotas[] = "{$nombre} ({$url}) devolvió {$codigo}";
            }

            // Recepción puede no tener permiso, pero nunca un error.
            $codigo = $this->actingAs($recepcion)->get($url)->baseResponse->getStatusCode();

            if ($codigo >= 500) {
                $rotas[] = "{$nombre} ({$url}) como recepción devolvió {$codigo}";
            }
        }

        // Las fichas de los casos raros.
        $fichas = [];
        foreach ([
            Cliente::where('es_menor_edad', true)->first(),
            Cliente::whereNotNull('datos_borrados_en')->first(),
            // (Los de la papelera no tienen ficha: se ven en Papelera, que ya pasó arriba.)
            Cliente::find(HistorialTraspaso::first()->cliente_origen_id),
            Cliente::find(HistorialTraspaso::first()->cliente_destino_id),
            Inscripcion::where('id_estado', self::CAMBIADA)->first()?->cliente,
            Cliente::doesntHave('inscripciones')->where('activo', true)->first(),
            Cliente::whereHas('inscripciones', fn ($q) => $q->where('precio_final', 0))->first(),
        ] as $c) {
            if ($c) {
                $fichas[] = '/panel/clientes/' . $c->uuid;
            }
        }

        foreach ([
            Inscripcion::where('id_estado', self::CAMBIADA)->first(),
            Inscripcion::where('es_traspaso', true)->first(),
            Inscripcion::where('precio_final', 0)->first(),
            Inscripcion::where('tipo_cambio', 'renovacion')->first(),
            Inscripcion::queDeben()->first(),
            Inscripcion::where('pausa_indefinida', true)->first(),
        ] as $i) {
            if ($i) {
                $fichas[] = '/panel/inscripciones/' . $i->uuid;
            }
        }

        foreach ([Pago::where('tipo_pago', 'pendiente')->first(), Pago::whereNotNull('referencia_pago')->first(), Pago::where('id_estado', 202)->first()] as $p) {
            if ($p) {
                $fichas[] = '/panel/pagos/' . $p->uuid;
            }
        }

        // La copia impresa existe solo de los firmados (con y sin datos borrados).
        foreach (Contrato::whereNotNull('firmado_en')->get()->unique(fn ($c) => $c->estado()) as $c) {
            $fichas[] = '/panel/contratos/' . $c->uuid;
        }

        foreach (['/panel', '/panel/clientes', '/panel/inscripciones', '/panel/pagos', '/panel/caja', '/panel/fiados', '/panel/reportes', '/panel/talleres', '/panel/notificaciones', ...$fichas] as $url) {
            $codigo = $this->actingAs($admin)->get($url)->baseResponse->getStatusCode();

            if (! in_array($codigo, [200, 302], true)) {
                $rotas[] = "{$url} devolvió {$codigo}";
            }
        }

        $this->assertSame([], $rotas, "Pantallas que no abren con la demo:\n" . implode("\n", $rotas));
        $this->assertGreaterThan(20, $miradas, 'Se miraron muy pocas pantallas: algo falla en la prueba.');
    }

    private function rellenar(string $uri, array $valores): ?string
    {
        preg_match_all('/\{(\w+)\??\}/', $uri, $encontrados);

        foreach ($encontrados[1] as $parametro) {
            if (! isset($valores[$parametro])) {
                return null;
            }

            $uri = preg_replace('/\{' . $parametro . '\??\}/', $valores[$parametro], $uri);
        }

        return '/' . ltrim($uri, '/');
    }
}
