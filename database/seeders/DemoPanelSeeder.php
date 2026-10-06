<?php

namespace Database\Seeders;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\CotizacionTaller;
use App\Models\Falla;
use App\Models\InformeGuardado;
use App\Models\Inscripcion;
use App\Models\LogNotificacion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\Rol;
use App\Models\User;
use App\Support\PrecioAcordado;
use App\Support\RegistroDeFallas;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Lo que la demo enseña solo desde Configuración y Reportes: más cuentas del
 * panel, informes guardados del constructor, el registro de fallas y una
 * papelera con algo más que socios.
 *
 * VA DESPUÉS DE DemoCompletoSeeder, que lo llama al final: trabaja sobre su
 * gimnasio ya armado (sus cuentas, sus socios, sus correos rebotados). Solo
 * corre si existe admin@demo.test; contra otra base no hace nada.
 *
 * Como en DemoCompletoSeeder, nada se inventa al lado del sistema: cada fila
 * se escribe como la escribe la pantalla que la produce —borrar una membresía
 * como InscripcionEditarController::eliminar, anular un pago como
 * PagoEditarController::eliminar, una falla como RegistroDeFallas— y en el
 * momento en que pasó.
 */
class DemoPanelSeeder extends Seeder
{
    private const ADMIN = 'admin@demo.test';

    private const RECEPCION = 'recepcion@demo.test';

    private CarbonImmutable $ahora;

    private CarbonImmutable $hoy;

    private ?CarbonInterface $relojDeAntes = null;

    private User $admin;

    private User $recepcion;

    /** @var array<string,string> correo => clave, solo hasta anotarla en el archivo */
    private array $claves = [];

    public function run(): void
    {
        $admin = User::where('email', self::ADMIN)->first();
        $recepcion = User::where('email', self::RECEPCION)->first();

        if (! $admin || ! $recepcion) {
            $this->command?->warn('No es la base de demostración (falta ' . self::ADMIN . '): no se tocó nada.');

            return;
        }

        if (InformeGuardado::where('id_usuario', $admin->id)->exists()) {
            $this->command?->warn('La demo ya tiene lo del panel: no se repite.');

            return;
        }

        $this->admin = $admin;
        $this->recepcion = $recepcion;
        $this->relojDeAntes = Carbon::getTestNow();
        $this->ahora = CarbonImmutable::now();
        $this->hoy = $this->ahora->startOfDay();

        try {
            $this->cuentas();
            $this->informes();
            $this->fallas();
            $this->membresiaBorrada();
            $this->pagoAnulado();
            $this->cotizacionRepetida();
        } finally {
            Carbon::setTestNow($this->relojDeAntes);
        }

        $this->anotarAccesos();
    }

    // =====================================================================
    // Cuentas del panel
    // =====================================================================

    /**
     * Dos cuentas más, como las crea UsuarioController: la recepcionista de la
     * tarde y una que ya no trabaja aquí, desactivada (no entra, pero sigue en
     * la lista con lo que hizo).
     */
    private function cuentas(): void
    {
        $rolRecepcion = (int) Rol::where('nombre', 'Recepcionista')->value('id');

        $this->enElMomento($this->hoy->subDays(120)->setTime(10, 15), fn () => $this->cuenta('Recepción tarde (demo)', 'tarde@demo.test', $rolRecepcion));

        $antes = $this->enElMomento($this->hoy->subDays(410)->setTime(9, 40), fn () => $this->cuenta('Exrecepcionista (demo)', 'antes@demo.test', $rolRecepcion));

        // Se fue hace tres meses: UsuarioController::update con activo = false.
        $this->enElMomento($this->hoy->subDays(95)->setTime(19, 5), fn () => $antes->update(['activo' => false]));
    }

    private function cuenta(string $nombre, string $correo, int $rol): User
    {
        $clave = Str::random(16);

        $usuario = User::create([
            'name' => $nombre,
            'email' => $correo,
            'password' => $clave,
            'id_rol' => $rol,
            'activo' => true,
            'two_factor_enabled' => false,
        ]);
        $usuario->forceFill(['email_verified_at' => now()])->save();
        $this->claves[$correo] = $clave;

        return $usuario;
    }

    /** Las claves nuevas, en el mismo archivo que las de DemoCompletoSeeder. */
    private function anotarAccesos(): void
    {
        $disco = Storage::disk('local');
        $archivo = DemoCompletoSeeder::ARCHIVO_ACCESOS;

        $bloque = implode("\n", [
            'Recepción de la tarde',
            '  correo: tarde@demo.test',
            '  clave:  ' . $this->claves['tarde@demo.test'],
            '',
            'Cuenta desactivada (no puede entrar: es para ver cómo queda alguien que se fue)',
            '  correo: antes@demo.test',
            '  clave:  ' . $this->claves['antes@demo.test'],
            '',
            '',
        ]);

        $texto = $disco->exists($archivo) ? $disco->get($archivo) : '';
        $nota = 'Las claves se sortean cada vez que se siembra.';

        $texto = str_contains($texto, $nota)
            ? str_replace($nota, $bloque . $nota, $texto)
            : $texto . "\n" . $bloque;

        $disco->put($archivo, $texto);

        $this->claves = [];
    }

    // =====================================================================
    // Informes guardados
    // =====================================================================

    /**
     * Recetas del constructor, como las guarda ConstructorController::guardar:
     * columnas, filtros, orden, dirección y cuántas filas. Los filtros van
     * como los manda la pantalla (los desplegables, en texto).
     */
    private function informes(): void
    {
        $mesPasado = $this->hoy->subMonthNoOverflow()->startOfMonth();
        $esteMes = $this->hoy->startOfMonth();

        $recetas = [
            [$this->admin, 'Membresías activas y lo que deben', 'inscripciones', 34, [
                'columnas' => ['socio', 'celular', 'plan', 'fecha_vencimiento', 'precio_final', 'abonado', 'debe'],
                'filtros' => ['id_estado' => (string) EstadosCodigo::INSCRIPCION_ACTIVA],
                'orden' => 'fecha_vencimiento',
                'direccion' => 'asc',
                'limite' => 250,
            ]],
            [$this->admin, 'Cobros de ' . $mesPasado->locale('es')->translatedFormat('F Y'), 'pagos', 2, [
                'columnas' => ['socio', 'plan', 'fecha_pago', 'monto_abonado', 'metodo', 'tipo_pago'],
                'filtros' => ['fecha_pago' => [
                    'desde' => $mesPasado->format('Y-m-d'),
                    'hasta' => $mesPasado->endOfMonth()->format('Y-m-d'),
                ]],
                'orden' => 'fecha_pago',
                'direccion' => 'asc',
                'limite' => 1000,
            ]],
            [$this->admin, 'Transferencias con número', 'pagos', 21, [
                'columnas' => ['socio', 'fecha_pago', 'monto_abonado', 'metodo', 'referencia_pago'],
                'filtros' => ['fecha_pago' => ['desde' => $this->hoy->subMonths(3)->startOfMonth()->format('Y-m-d'), 'hasta' => '']],
                'orden' => 'fecha_pago',
                'direccion' => 'desc',
                'limite' => 500,
            ]],
            [$this->admin, 'Socios activos con su celular', 'clientes', 55, [
                'columnas' => ['run_pasaporte', 'nombres', 'apellido_paterno', 'email', 'celular', 'ultimo_plan', 'ultimo_vence'],
                'filtros' => ['activo' => '1'],
                'orden' => 'apellido_paterno',
                'direccion' => 'asc',
                'limite' => 500,
            ]],
            [$this->recepcion, 'Vencen este mes', 'inscripciones', 9, [
                'columnas' => ['socio', 'celular', 'plan', 'fecha_vencimiento', 'debe', 'dias'],
                'filtros' => [
                    'id_estado' => (string) EstadosCodigo::INSCRIPCION_ACTIVA,
                    'fecha_vencimiento' => ['desde' => $esteMes->format('Y-m-d'), 'hasta' => $esteMes->endOfMonth()->format('Y-m-d')],
                ],
                'orden' => 'fecha_vencimiento',
                'direccion' => 'asc',
                'limite' => 100,
            ]],
        ];

        foreach ($recetas as [$usuario, $nombre, $modulo, $haceDias, $configuracion]) {
            $this->enElMomento($this->hoy->subDays($haceDias)->setTime(11, 20 + $haceDias % 30), fn () => InformeGuardado::create([
                'id_usuario' => $usuario->id,
                'nombre' => $nombre,
                'modulo' => $modulo,
                'configuracion' => $configuracion,
            ]));
        }
    }

    // =====================================================================
    // Registro de fallas
    // =====================================================================

    /**
     * Fallas del servidor y del navegador, con la forma que les da
     * RegistroDeFallas: una fila por huella con sus veces, la primera y la
     * última, rutas sin dominio y sin claves. Todas de los últimos 90 días (lo
     * más viejo lo borra la revisión del día) y algunas ya resueltas.
     */
    private function fallas(): void
    {
        $navegador = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36';
        $dia = fn (int $hace, int $hora, int $minuto = 0) => $this->hoy->subDays($hace)->setTime($hora, $minuto);

        // Los correos que rebotaron de verdad en la demo: cada intento fallido
        // lo anotó NotificacionService con un Log::error.
        $rebotes = LogNotificacion::where('accion', 'fallida')
            ->where('detalle', 'like', '550 5.1.1%')
            ->orderBy('created_at')
            ->get();

        if ($rebotes->isNotEmpty()) {
            $ultimo = $rebotes->last();

            $this->falla([
                'origen' => 'servidor',
                'tipo' => null,
                'mensaje' => 'Error al enviar notificación',
                'lugar' => 'app/Services/NotificacionService.php:342',
                'url' => 'consola',
                'metodo' => null,
                'contexto' => ['id' => (int) $ultimo->id_notificacion, 'error' => $ultimo->detalle],
            ], $rebotes->count(), CarbonImmutable::parse($rebotes->first()->created_at), CarbonImmutable::parse($ultimo->created_at));
        }

        // La base se reinició una mañana: unos minutos sin conexión.
        $this->falla([
            'origen' => 'servidor',
            'tipo' => 'Illuminate\\Database\\QueryException',
            'mensaje' => 'SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5432 failed: Connection refused'
                . "\n\tIs the server running on that host and accepting TCP/IP connections?"
                . ' (Connection: pgsql, SQL: select * from "sessions" where "id" = ? limit 1)',
            'lugar' => 'vendor/laravel/framework/src/Illuminate/Database/Connection.php:838',
            'traza' => implode("\n", [
                '#0 vendor/laravel/framework/src/Illuminate/Database/Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback()',
                '#1 vendor/laravel/framework/src/Illuminate/Database/Connection.php(411): Illuminate\\Database\\Connection->run()',
                '#2 vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php(3381): Illuminate\\Database\\Connection->select()',
                '#3 vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php(97): Illuminate\\Database\\Query\\Builder->first()',
                '#4 {main}',
            ]),
            'url' => '/panel',
            'metodo' => 'GET',
            'id_usuario' => $this->recepcion->id,
            'contexto' => null,
        ], 9, $dia(41, 8, 2), $dia(41, 8, 6), $dia(41, 8, 40));

        // El respaldo del día no encontró pg_dump después de una actualización.
        $this->falla([
            'origen' => 'servidor',
            'tipo' => null,
            'mensaje' => "El respaldo del día falló: El respaldo de la base falló: 'pg_dump' no se reconoce como un comando interno o externo, programa o archivo por lotes ejecutable.",
            'lugar' => 'app/Http/Middleware/RevisaElDia.php:89',
            'url' => '/panel',
            'metodo' => 'GET',
            'id_usuario' => $this->admin->id,
            'contexto' => null,
        ], 3, $dia(33, 8, 14), $dia(31, 8, 9), $dia(31, 12, 30));

        // El formulario de contacto de la web, con internet caído en el local.
        $this->falla([
            'origen' => 'servidor',
            'tipo' => null,
            'mensaje' => 'No se pudo enviar el contacto de la web: Connection could not be established with host "smtp.gmail.com:587": stream_socket_client(): Unable to connect to smtp.gmail.com:587 (A connection attempt failed because the connected party did not properly respond after a period of time)',
            'lugar' => 'app/Http/Controllers/LandingController.php:1785',
            'url' => '/contacto',
            'metodo' => 'POST',
            'id_usuario' => null,
            'contexto' => ['destino' => 'contacto@example.com', 'de' => 'consulta.web@example.com'],
        ], 2, $dia(18, 16, 47), $dia(18, 17, 3), $dia(17, 10, 10));

        // Una pantalla rota en el navegador, ya arreglada.
        $traspaso = Inscripcion::where('es_traspaso', true)->orderBy('id')->first();
        $pantalla = $traspaso ? '/panel/inscripciones/' . $traspaso->uuid : '/panel/inscripciones';

        $this->falla([
            'origen' => 'navegador',
            'tipo' => 'TypeError',
            'mensaje' => "Cannot read properties of undefined (reading 'nombre')",
            'lugar' => '/build/assets/Show-D4kq81Zb.js:1',
            'traza' => "TypeError: Cannot read properties of undefined (reading 'nombre')\n    at Historial (/build/assets/Show-D4kq81Zb.js:1:18342)\n    at Gi (/build/assets/app-C2xv8Lq0.js:9:61208)",
            'url' => $pantalla,
            'metodo' => 'GET',
            'id_usuario' => $this->recepcion->id,
            'contexto' => ['pantalla' => $pantalla, 'navegador' => $navegador],
        ], 4, $dia(24, 12, 31), $dia(23, 18, 2), $dia(22, 11, 0));

        // Después de una actualización, una pestaña abierta pidió el archivo viejo.
        $this->falla([
            'origen' => 'navegador',
            'tipo' => 'TypeError',
            'mensaje' => 'Failed to fetch dynamically imported module: /build/assets/Constructor-B7xq2LmA.js',
            'lugar' => null,
            'traza' => null,
            'url' => '/panel/reportes/constructor',
            'metodo' => 'GET',
            'id_usuario' => $this->admin->id,
            'contexto' => ['pantalla' => '/panel/reportes/constructor', 'navegador' => $navegador],
        ], 2, $dia(6, 9, 12), $dia(6, 9, 13), $dia(6, 9, 30));

        // Un aviso del navegador que no rompe nada pero se repite: abierta.
        $this->falla([
            'origen' => 'navegador',
            'tipo' => 'Error',
            'mensaje' => 'ResizeObserver loop completed with undelivered notifications.',
            'lugar' => null,
            'traza' => null,
            'url' => '/panel/caja',
            'metodo' => 'GET',
            'id_usuario' => $this->admin->id,
            'contexto' => ['pantalla' => '/panel/caja', 'navegador' => $navegador],
        ], 23, $dia(12, 10, 4), $this->ahora->subHours(3)->max($this->hoy));
    }

    /**
     * Una falla con su huella, como RegistroDeFallas::guardar: la huella sale
     * del origen, el tipo, el lugar y el mensaje sin números (con la MISMA
     * regla del registro, para que si vuelve a pasar caiga en esta fila).
     */
    private function falla(array $datos, int $veces, CarbonImmutable $primera, CarbonImmutable $ultima, ?CarbonImmutable $resuelta = null): void
    {
        $sinNumeros = new \ReflectionMethod(RegistroDeFallas::class, 'sinNumeros');
        $mensaje = $datos['mensaje'];

        $huella = hash('sha256', implode('|', [
            $datos['origen'],
            $datos['tipo'] ?? '',
            $datos['lugar'] ?? '',
            $sinNumeros->invoke(null, $mensaje),
        ]));

        $primera = $primera->min($this->ahora);
        $ultima = $ultima->min($this->ahora)->max($primera);
        $resuelta = $resuelta?->min($this->ahora)->max($ultima);

        $falla = new Falla([
            ...$datos,
            'nivel' => 'error',
            'huella' => $huella,
            'veces' => $veces,
            'primera_vez' => $primera,
            'ultima_vez' => $ultima,
            'resuelta_en' => $resuelta,
        ]);
        $falla->timestamps = false;
        $falla->created_at = $primera;
        $falla->updated_at = $resuelta ?? $ultima;
        $falla->save();
    }

    // =====================================================================
    // La papelera
    // =====================================================================

    /**
     * Una membresía vendida sin pagar y borrada al día siguiente, como
     * InscripcionEditarController::eliminar: su pago pendiente de $0 se va con
     * ella a la papelera, con la misma hora, para que vuelva si se recupera.
     *
     * Es del socio recién registrado más antiguo que sigue sin plan: se le
     * cargó la mensualidad al registrarlo, no volvió a pagarla y se borró para
     * no dejarle una deuda de algo que no usó. Al recuperarla queda como era:
     * activa, debiéndola entera.
     */
    private function membresiaBorrada(): void
    {
        $socio = Cliente::where('activo', true)->doesntHave('inscripciones')->orderBy('created_at')->first();
        $plan = Membresia::where('nombre', 'Mensual')->first();

        if (! $socio || ! $plan) {
            return;
        }

        $vendida = CarbonImmutable::parse($socio->created_at)->addMinutes(8);

        $inscripcion = $this->enElMomento($vendida, function () use ($socio, $plan) {
            $precio = $plan->precioVigente();
            $final = PrecioAcordado::para($precio, null);
            $inicio = today();

            $inscripcion = Inscripcion::create([
                'id_cliente' => $socio->id,
                'id_membresia' => $plan->id,
                'id_precio_acordado' => $precio->id,
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                'fecha_inscripcion' => $inicio->format('Y-m-d'),
                'fecha_inicio' => $inicio->format('Y-m-d'),
                'fecha_vencimiento' => $plan->vencimientoDesde($inicio)->format('Y-m-d'),
                'precio_base' => (int) round($precio->precio_normal),
                'descuento_aplicado' => (int) round($precio->precio_normal) - $final,
                'precio_final' => $final,
                'max_pausas_permitidas' => (int) $plan->max_pausas,
            ]);

            // El alta sin pago: un pendiente de $0 (RegistroClienteService).
            Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $socio->id,
                'monto_total' => $final,
                'monto_abonado' => 0,
                'monto_pendiente' => $final,
                'fecha_pago' => today()->format('Y-m-d'),
                'id_metodo_pago' => null,
                'id_estado' => EstadosCodigo::PAGO_PENDIENTE,
                'tipo_pago' => 'pendiente',
            ]);

            $inscripcion->recalcularSusPagos();

            return $inscripcion;
        });

        $borrada = CarbonImmutable::parse($vendida->addDay()->format('Y-m-d'))->setTime(9, 35)->max($vendida->addMinutes(30));

        $this->enElMomento($borrada, fn () => DB::transaction(function () use ($inscripcion) {
            $inscripcion->delete();

            $inscripcion->pagos()->update([
                'deleted_at' => $inscripcion->deleted_at,
                'updated_at' => now(),
            ]);
        }));
    }

    /**
     * Un abono anotado al socio equivocado y anulado al rato, como
     * PagoEditarController::eliminar: a la papelera y la membresía vuelve a
     * cuadrar sus pagos. Cabe en lo que debe, así que se puede recuperar.
     */
    private function pagoAnulado(): void
    {
        $abono = 10000;
        $ayer = $this->hoy->subDay();

        $inscripcion = Inscripcion::where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
            ->where('pausada', false)
            ->where('es_traspaso', false)
            ->whereDate('created_at', '<', $ayer->format('Y-m-d'))
            ->with('pagos')
            ->orderBy('id')
            ->get()
            ->first(fn (Inscripcion $i) => (int) $i->precio_final - (int) $i->pagos->sum('monto_abonado') >= $abono
                && $i->pagos->every(fn (Pago $p) => $p->fecha_pago->lt($ayer)));

        $efectivo = MetodoPago::where('nombre', 'Efectivo')->value('id');

        if (! $inscripcion || ! $efectivo) {
            return;
        }

        $cobrado = $ayer->setTime(18, 41)->min($this->ahora);

        $pago = $this->enElMomento($cobrado, function () use ($inscripcion, $abono, $efectivo) {
            $antes = (int) $inscripcion->pagos()->sum('monto_abonado');
            $saldo = (int) $inscripcion->precio_final - $antes - $abono;

            $pago = Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
                'monto_total' => (int) $inscripcion->precio_final,
                'monto_abonado' => $abono,
                'monto_pendiente' => $saldo,
                'fecha_pago' => today()->format('Y-m-d'),
                'id_metodo_pago' => $efectivo,
                'id_estado' => $saldo <= 0 ? EstadosCodigo::PAGO_PAGADO : EstadosCodigo::PAGO_PARCIAL,
                'tipo_pago' => 'parcial',
                'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
                'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
                'monto_cuota' => $abono,
            ]);

            $inscripcion->recalcularSusPagos();

            return $pago;
        });

        $this->enElMomento($cobrado->addMinutes(12)->min($this->ahora), fn () => DB::transaction(function () use ($pago, $inscripcion) {
            $pago->delete();

            $inscripcion->recalcularSusPagos();
        }));
    }

    /**
     * La cotización del mes que viene, guardada dos veces por un doble clic:
     * la repetida se borró enseguida (CotizacionTallerController::eliminar).
     * Su número queda usado: la serie no reparte otra vez uno borrado.
     */
    private function cotizacionRepetida(): void
    {
        $original = CotizacionTaller::where('estado', 'enviada')->orderBy('id')->first();

        if (! $original) {
            return;
        }

        $guardada = CarbonImmutable::parse($original->created_at)->addSeconds(40)->min($this->ahora);

        $copia = $this->enElMomento($guardada, function () use ($original) {
            $copia = $original->replicate(['uuid', 'numero']);
            $copia->numero = CotizacionTaller::siguienteNumero();
            $copia->save();

            return $copia;
        });

        $this->enElMomento($guardada->addMinutes(3)->min($this->ahora), fn () => $copia->delete());
    }

    // =====================================================================
    // El reloj
    // =====================================================================

    /** Como en DemoCompletoSeeder: created_at, deleted_at y today() caen en ese momento. */
    private function enElMomento(CarbonInterface $momento, callable $hacer): mixed
    {
        Carbon::setTestNow(Carbon::parse($momento->min($this->ahora)->format('Y-m-d H:i:s')));

        try {
            return $hacer();
        } finally {
            Carbon::setTestNow($this->relojDeAntes);
        }
    }
}
