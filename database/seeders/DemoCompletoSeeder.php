<?php

namespace Database\Seeders;

use App\Enums\EstadosCodigo;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\CobroTaller;
use App\Models\ContenidoWeb;
use App\Models\Contrato;
use App\Models\Convenio;
use App\Models\ConvenioPrecio;
use App\Models\CotizacionTaller;
use App\Models\EntradaCanje;
use App\Models\Especialista;
use App\Models\Fiado;
use App\Models\FiadoRegistro;
use App\Models\HistorialCambio;
use App\Models\HistorialPrecio;
use App\Models\HistorialTraspaso;
use App\Models\HoraTaller;
use App\Models\Inscripcion;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Models\Nota;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Models\Rol;
use App\Models\Taller;
use App\Models\TipoNotificacion;
use App\Models\User;
use App\Services\BorradoDeDatosService;
use App\Services\ContratoDigitalService;
use App\Services\NotificacionService;
use App\Services\RegistroClienteService;
use App\Support\Ajustes;
use App\Support\PrecioAcordado;
use App\Support\RutinasDeEjemplo;
use App\Support\TextosLegales;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * UN GIMNASIO ENTERO DE MENTIRA, PARA RECORRER EL PANEL LLENO.
 *
 * Unos 180 socios con año y medio de historia: altas, renovaciones a tiempo y
 * tarde, pausas, traspasos, cambios de plan, deudas, el mesón, los talleres,
 * los contratos y los correos. Todo con fechas relativas a hoy, para que
 * siempre parezca el gimnasio de esta semana.
 *
 * LO QUE IMPORTA ES QUE NO SE CONTRADIGA. Un dato de prueba incoherente no
 * enseña nada: con una membresía «Activa» vencida hace un mes no se distingue
 * un fallo del panel de un disparate del generador. Por eso aquí no se sortea
 * el estado de nada: se SIMULA lo que pasó. Cada alta, cobro, pausa o
 * renovación se escribe como la escribe el sistema, en el momento en que pasó
 * (se viaja en el tiempo con Carbon::setTestNow), y la revisión de cada noche
 * marca las vencidas y da de baja a quien se quedó sin plan, como hace
 * inscripciones:actualizar-estados y clientes:desactivar-vencidos. La prueba
 * DemoCompletoCoherenteTest corre después esas mismas tareas y exige que no
 * encuentren nada que arreglar.
 *
 * SOLO SOBRE UNA BASE VACÍA. Si ya hay socios no hace nada: correrlo por error
 * contra la base del gimnasio mezclaría 180 personas inventadas con las de
 * verdad.
 *
 * NADIE PUEDE RECIBIR UN CORREO DE AQUÍ. Los socios llevan direcciones
 * @example.com, que CorreoService se niega a usar (RFC 2606), y los correos
 * automáticos quedan apagados en Configuración. Los avisos que quedan
 * pendientes son todos automáticos; los manuales —que salen aunque los
 * automáticos estén apagados— están enviados, cancelados o sin reintentos.
 *
 * Las dos cuentas del panel (admin@demo.test y recepcion@demo.test) nacen con
 * una clave al azar que se escribe en storage/app/private/demo-accesos.txt, que
 * no va al repositorio. No se imprime en pantalla.
 *
 *   php artisan migrate:fresh && php artisan db:seed --class=DemoCompletoSeeder
 */
class DemoCompletoSeeder extends Seeder
{
    /** Misma semilla, mismo gimnasio: las fechas se mueven con hoy, la historia no. */
    private const SEMILLA = 386;

    /** Cuánta historia se inventa hacia atrás. */
    private const MESES_DE_HISTORIA = 18;

    public const ARCHIVO_ACCESOS = 'demo-accesos.txt';

    private CarbonImmutable $hoy;

    private CarbonImmutable $ahora;

    private CarbonImmutable $desde;

    /** El reloj que había antes de empezar (una prueba puede tenerlo fijo). */
    private ?CarbonInterface $relojDeAntes = null;

    private User $admin;

    private User $recepcion;

    /** @var array<string,Membresia> por nombre */
    private array $planes = [];

    /** @var array<int,PrecioMembresia> por id de membresía: el que rige HOY */
    private array $precios = [];

    /** @var array<int,list<array{0:CarbonImmutable,1:PrecioMembresia}>> por id de membresía: desde cuándo rigió cada precio */
    private array $tramos = [];

    /** @var array<string,int> efectivo|transferencia|tarjeta => id */
    private array $metodos = [];

    /** @var array<string,int> nombre del motivo => id */
    private array $motivos = [];

    /** @var array<string,Convenio> */
    private array $convenios = [];

    /** @var array<string,array<string,bool>> para no repetir RUT, celular, correo ni nombre */
    private array $usados = ['rut' => [], 'celular' => [], 'correo' => [], 'nombre' => []];

    /** Socios con un papel en la historia, para elegirlos después. */
    private array $papeles = [
        'cadena' => [], 'menores' => [], 'pases' => [], 'solos' => [],
        'traspaso' => [], 'cambio' => [], 'extranjero' => [],
    ];

    private int $operacion = 4518230;

    /** @var array<string,string> correo => clave, solo hasta escribir el archivo */
    private array $claves = [];

    private bool $huboIndefinida = false;

    private bool $huboCortesia = false;

    public function run(): void
    {
        if (Cliente::withTrashed()->exists()) {
            $this->command?->error('La base ya tiene socios: este seeder es solo para una base vacía (migrate:fresh). No se tocó nada.');

            return;
        }

        mt_srand(self::SEMILLA);

        $this->relojDeAntes = Carbon::getTestNow();
        $this->ahora = CarbonImmutable::now();
        $this->hoy = $this->ahora->startOfDay();
        $this->desde = $this->hoy->subMonths(self::MESES_DE_HISTORIA);

        try {
            $this->catalogos();
            $this->ajustes();
            $this->usuarios();
            $this->conveniosYPrecios();
            $this->subidasDePrecio();

            $this->sociosConHistoria();
            $this->traspasos();
            $this->cambiosDePlan();

            [$aLaPapelera, $aBorrar] = $this->elegirBajasDefinitivas();

            $this->contratos($aBorrar);
            $this->notificaciones();
            $this->meson($aBorrar, $aLaPapelera);
            $this->canjes();
            $this->talleres();
            $this->web();

            $this->papeleraYBorrados($aLaPapelera, $aBorrar);
        } finally {
            Carbon::setTestNow($this->relojDeAntes);
        }

        $this->anotarAccesos();

        // Las fotos (si las hay en el disco) y lo que se ve solo desde
        // Configuración: fallas, informes guardados, papelera, más cuentas.
        $this->call([DemoFotosSeeder::class, DemoPanelSeeder::class]);

        $this->resumen();
    }

    // =====================================================================
    // Catálogos, ajustes y cuentas
    // =====================================================================

    /**
     * Los mismos catálogos que DatabaseSeeder, sin llamarlo a él.
     *
     * DatabaseSeeder además crea admin@progym.cl y recepcion@progym.cl y
     * escribe sus claves en pantalla. En una base de demostración sobran dos
     * cuentas de verdad con la clave a la vista: se siembra solo lo que falta,
     * tabla por tabla.
     */
    private function catalogos(): void
    {
        $faltan = array_filter([
            RolesSeeder::class => ! DB::table('roles')->exists(),
            EstadoSeeder::class => true, // insertOrIgnore: no repite nada
            MetodoPagoSeeder::class => ! DB::table('metodos_pago')->exists(),
            MotivoDescuentoSeeder::class => ! DB::table('motivos_descuento')->exists(),
            MembresiasSeeder::class => ! DB::table('membresias')->exists(),
            PreciosMembresiasSeeder::class => ! DB::table('precios_membresias')->exists(),
            ConveniosSeeder::class => ! DB::table('convenios')->exists(),
        ]);

        $this->call(array_keys($faltan));

        // Las plantillas de correo de verdad: sus HTML van en el repositorio
        // (database/seeders/plantillas), así que siempre están.
        $this->call(PlantillasProgymSeeder::class);

        foreach (Membresia::all() as $plan) {
            $this->planes[$plan->nombre] = $plan;
        }

        /*
         * EL PRECIO VALE DESDE ANTES DE LA PRIMERA VENTA. PreciosMembresiasSeeder
         * lo deja vigente desde hoy, y una membresía de hace un año apuntando a
         * un precio que empezó a regir hoy es una fecha imposible.
         */
        PrecioMembresia::query()->update(['fecha_vigencia_desde' => $this->desde->subMonth()->format('Y-m-d')]);

        foreach (PrecioMembresia::where('activo', true)->get() as $precio) {
            $this->precios[(int) $precio->id_membresia] = $precio;
        }

        $this->metodos = [
            'efectivo' => (int) MetodoPago::where('nombre', 'Efectivo')->value('id'),
            'transferencia' => (int) MetodoPago::where('nombre', 'Transferencia')->value('id'),
            'tarjeta' => (int) MetodoPago::where('nombre', 'Tarjeta')->value('id'),
        ];

        $this->motivos = MotivoDescuento::pluck('id', 'nombre')->map(fn ($id) => (int) $id)->all();

        // Los textos legales, versión 1, desde antes del primer socio: los
        // contratos en papel anotan esa versión.
        $this->enElMomento($this->desde->subDays(20)->setTime(10, 0), function () {
            foreach (['contrato', 'terminos', 'privacidad'] as $tipo) {
                TextosLegales::vigente($tipo);
            }
        });
    }

    private function ajustes(): void
    {
        Ajustes::guardar([
            // Lo primero: que el sistema no le escriba solo a nadie.
            'tareas.correos_automaticos' => '0',

            'gimnasio.nombre' => 'PRO GYM',
            'gimnasio.razon_social' => 'Pro Gym Los Ángeles SpA',
            'gimnasio.rut' => $this->rutCon(76482391),
            'gimnasio.direccion' => 'Rengo 386, segundo piso',
            'gimnasio.comuna' => 'Los Ángeles, Biobío',
            'gimnasio.email' => 'contacto@example.com',

            'horario.lunes' => '06:30-22:00',
            'horario.martes' => '06:30-22:00',
            'horario.miercoles' => '06:30-22:00',
            'horario.jueves' => '06:30-22:00',
            'horario.viernes' => '06:30-21:00',
            'horario.sabado' => '09:00-14:00',
            'horario.domingo' => '',
            'horario.nota' => 'Festivos de 9:00 a 14:00',

            'meson.precios' => implode("\n", [
                'Agua mineral 500 cc = 1000',
                'Bebida isotónica = 1800',
                'Barra de proteína = 2500',
                'Batido de proteína = 3500',
                'Creatina (dosis) = 1500',
                'Arriendo de toalla = 1000',
                'Candado para casillero = 4500',
                'Shaker = 6000',
            ]),
            'meson.tope_fiado' => '20000',
        ]);
    }

    /**
     * Las dos cuentas del panel, con clave al azar.
     *
     * Sin segundo factor: el de fábrica está apagado y encenderlo con un
     * teléfono inventado mandaría el código por WhatsApp a quien tenga ese
     * número. El correo queda verificado y la cuenta activa, que es lo que mira
     * el inicio de sesión.
     */
    private function usuarios(): void
    {
        $rolAdmin = (int) Rol::where('nombre', 'Administrador')->value('id');
        $rolRecepcion = (int) Rol::where('nombre', 'Recepcionista')->value('id');

        [$this->admin, $this->recepcion] = $this->enElMomento($this->desde->subDays(30)->setTime(9, 0), function () use ($rolAdmin, $rolRecepcion) {
            $cuentas = [];

            foreach ([
                ['Administración (demo)', 'admin@demo.test', $rolAdmin],
                ['Recepción (demo)', 'recepcion@demo.test', $rolRecepcion],
            ] as [$nombre, $correo, $rol]) {
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
                $cuentas[] = $usuario;
            }

            return $cuentas;
        });
    }

    private function anotarAccesos(): void
    {
        $texto = implode("\n", [
            'PRO GYM · base de demostración (DemoCompletoSeeder)',
            'Generada: ' . $this->ahora->format('d/m/Y H:i'),
            '',
            'Administrador',
            '  correo: ' . $this->admin->email,
            '  clave:  ' . $this->claves[$this->admin->email],
            '',
            'Recepción',
            '  correo: ' . $this->recepcion->email,
            '  clave:  ' . $this->claves[$this->recepcion->email],
            '',
            'Las claves se sortean cada vez que se siembra. Este archivo no va al repositorio.',
            '',
        ]);

        Storage::disk('local')->put(self::ARCHIVO_ACCESOS, $texto);

        $this->claves = [];
    }

    /**
     * Los convenios del catálogo, con correos que no llegan a nadie, precios
     * propios y uno de canje.
     */
    private function conveniosYPrecios(): void
    {
        // El catálogo trae correos de dominios reales (inacap.cl, falabella.cl…).
        foreach (Convenio::all() as $convenio) {
            if ($convenio->contacto_email) {
                $convenio->update(['contacto_email' => Str::slug($convenio->nombre, '.') . '@example.com']);
            }
            $this->convenios[$convenio->nombre] = $convenio;
        }

        $this->convenios['INACAP']->update(['mostrar_en_web' => true, 'requisito_web' => 'Credencial de estudiante vigente']);
        $this->convenios['DUOC UC']->update(['mostrar_en_web' => true, 'requisito_web' => 'Credencial de estudiante vigente']);

        ConvenioPrecio::create(['id_convenio' => $this->convenios['INACAP']->id, 'id_membresia' => $this->planes['Mensual']->id, 'precio' => 25000, 'condicion' => 'Con credencial de estudiante vigente']);
        ConvenioPrecio::create(['id_convenio' => $this->convenios['INACAP']->id, 'id_membresia' => $this->planes['Trimestral']->id, 'precio' => 65000, 'condicion' => 'Con credencial de estudiante vigente']);
        ConvenioPrecio::create(['id_convenio' => $this->convenios['Cruz Verde']->id, 'id_membresia' => $this->planes['Mensual']->id, 'precio' => 30000, 'condicion' => 'Presentando la credencial de la empresa']);

        $this->convenios['Hotel del Centro'] = Convenio::create([
            'nombre' => 'Hotel del Centro',
            'tipo' => 'empresa',
            'descripcion' => 'Canje: sus huéspedes entran con la tarjeta de la habitación y el hotel nos publicita.',
            'contacto_nombre' => 'Recepción del hotel',
            'contacto_email' => 'reservas.hotel@example.com',
            'contacto_telefono' => '+56 9 5550 1386',
            'activo' => true,
            'canje' => true,
        ]);
    }

    /**
     * Dos subidas de precio a mitad de la historia, como las escribe
     * CatalogoController::ponerPrecio(): el precio de antes NO se pisa, se
     * cierra (activo = false, vigente hasta ese día) y se abre otro desde ese
     * día. Más la fila de historial_precios que enseña la ficha del plan.
     *
     * Lo vendido antes de la subida queda con el precio de entonces: una
     * mensualidad de hace un año dice $35.000 y la de este mes $40.000, que es
     * justo el desajuste que la ficha del plan explica con su historial.
     *
     * Las subidas son de hace meses: los traspasos y cambios de plan (de las
     * últimas semanas) ya se venden al precio de hoy.
     */
    private function subidasDePrecio(): void
    {
        $subidas = [
            // [plan, precio de antes, hace cuántos días, por qué]
            ['Mensual', 35000, 300, 'Alza anual: subió el arriendo del local y la luz.'],
            ['Pase Diario', 4000, 150, 'Se iguala al pase de los gimnasios del centro.'],
        ];

        foreach ($subidas as [$nombre, $antes, $haceDias, $razon]) {
            $plan = $this->planes[$nombre];
            $vigente = $this->precios[$plan->id];
            $ahora = (float) $vigente->precio_normal;
            $cuando = $this->momento($this->hoy->subDays($haceDias), 10, 12);

            // El que estaba desde el principio era el de antes: se escribe con
            // ese precio y la subida lo cierra ese día.
            $vigente->update(['precio_normal' => $antes]);

            $nuevo = $this->enElMomento($cuando, function () use ($vigente, $plan, $ahora, $antes, $razon) {
                $vigente->update([
                    'activo' => false,
                    'fecha_vigencia_hasta' => now()->format('Y-m-d'),
                ]);

                $nuevo = PrecioMembresia::create([
                    'id_membresia' => $plan->id,
                    'precio_normal' => $ahora,
                    'precio_convenio' => $vigente->precio_convenio,
                    'fecha_vigencia_desde' => now()->format('Y-m-d'),
                    'activo' => true,
                ]);

                HistorialPrecio::create([
                    'id_precio_membresia' => $nuevo->id,
                    'precio_anterior' => $antes,
                    'precio_nuevo' => $ahora,
                    'razon_cambio' => $razon,
                    'usuario_cambio' => $this->admin->name,
                ]);

                return $nuevo;
            });

            $this->tramos[$plan->id] = [
                [$this->desde->subMonth(), $vigente->fresh()],
                [CarbonImmutable::parse($nuevo->created_at), $nuevo],
            ];
            $this->precios[$plan->id] = $nuevo;
        }
    }

    /** El precio del plan que regía en ese momento: el de la última subida anterior. */
    private function precioEn(Membresia $plan, CarbonInterface $momento): PrecioMembresia
    {
        $precio = $this->precios[$plan->id];

        foreach ($this->tramos[$plan->id] ?? [] as [$desde, $tramo]) {
            if ($desde->lte($momento)) {
                $precio = $tramo;
            }
        }

        return $precio;
    }

    // =====================================================================
    // Socios y su historia
    // =====================================================================

    private function sociosConHistoria(): void
    {
        // Adultos con su ir y venir: el grueso del gimnasio.
        for ($i = 0; $i < 150; $i++) {
            $alta = $this->hoy->subDays((int) floor(540 * (mt_rand() / mt_getrandmax()) ** 1.25) + 1);
            $perfil = ['menor' => false];

            if ($i % 9 === 4) {
                $perfil['convenio'] = $this->elegir(['INACAP', 'INACAP', 'DUOC UC', 'Cruz Verde', 'Banco Santander', 'Clínica Montefiore']);
            }

            if ($i === 17) {
                $perfil['extranjero'] = true;
            }

            $socio = $this->crearSocio($alta, $perfil);
            $this->papeles[isset($perfil['extranjero']) ? 'extranjero' : 'cadena'][] = $socio->id;
            $this->cadena($socio, $alta, $perfil);
        }

        // Menores: de 14 a 17, con apoderado y su autorización. Se inscribieron
        // hace poco para que hoy sigan siendo menores.
        for ($i = 0; $i < 8; $i++) {
            $alta = $this->hoy->subDays($this->azar(8, 170));
            $socio = $this->crearSocio($alta, ['menor' => true]);
            $this->papeles['menores'][] = $socio->id;
            $this->cadena($socio, $alta, ['menor' => true]);
        }

        // Visitas que solo compran pase diario.
        for ($i = 0; $i < 12; $i++) {
            $this->visitaConPases($i);
        }

        // Recién registrados, todavía sin plan («Sin plan» en la lista).
        for ($i = 0; $i < 3; $i++) {
            $alta = $this->hoy->subDays($i);
            $socio = $this->crearSocio($alta, ['menor' => false]);
            $this->papeles['solos'][] = $socio->id;
        }
    }

    /**
     * La historia de un socio, de su alta a hoy.
     *
     * Se inscribe; puede pausar; paga lo que le falte; y al vencer renueva a
     * tiempo, vuelve más tarde o se va. Cada paso se escribe en su fecha.
     */
    private function cadena(Cliente $socio, CarbonImmutable $alta, array $perfil): void
    {
        $registro = $this->momento($alta);
        $inicio = $alta;
        $anterior = null;
        $plan = $this->primerPlan($perfil);
        $esElAlta = true;

        while (true) {
            $inscripcion = $this->inscribir($socio, $plan, $inicio, $registro, $anterior, $esElAlta, $perfil);
            $esElAlta = false;

            $pausa = $this->quizasPausar($inscripcion);
            $vence = CarbonImmutable::parse($inscripcion->fecha_vencimiento->format('Y-m-d'));
            $vigente = $pausa === 'en_curso' || $vence->gte($this->hoy);

            $this->cobrarElSaldo($inscripcion, $registro, $vigente);

            if ($pausa === 'en_curso') {
                return;
            }

            $siguiente = $this->queHaceAlVencer($registro, $vence);

            if ($siguiente === null) {
                $this->revisionDeLaNoche($socio, $inscripcion, $vence);

                return;
            }

            // Si volvió después de vencer, esa noche la revisión ya la había
            // marcado vencida y lo había dado de baja.
            if ($vence->addDay()->lte($siguiente['registro']->startOfDay())) {
                $this->revisionDeLaNoche($socio, $inscripcion, $vence);
            }

            $anterior = $siguiente['enlazada'] ? $inscripcion : null;
            $registro = $siguiente['registro'];
            $inicio = $siguiente['inicio'];
            $plan = $this->siguientePlan($plan, $perfil);
        }
    }

    /** Qué hace cuando se le acaba: null si no vuelve (o todavía no le toca). */
    private function queHaceAlVencer(CarbonImmutable $registro, CarbonImmutable $vence): ?array
    {
        $margen = Ajustes::numero('reglas.dias_para_renovar');

        if ($vence->gte($this->hoy)) {
            // Vigente. Algunos renuevan unos días antes de que se les acabe:
            // la nueva empieza el día siguiente al vencimiento.
            $faltan = Inscripcion::diasEntre($this->hoy, $vence);

            if ($faltan > 5 || $faltan > $margen || ! $this->probabilidad(0.35)) {
                return null;
            }

            $primerDia = $vence->subDays(5)->max($registro->startOfDay()->addDay());

            if ($primerDia->gt($this->hoy)) {
                return null;
            }

            $dia = $primerDia->addDays($this->azar(0, Inscripcion::diasEntre($primerDia, $this->hoy)));

            return ['registro' => $this->momento($dia), 'inicio' => $vence->addDay(), 'enlazada' => true];
        }

        $suerte = mt_rand() / mt_getrandmax();

        if ($suerte < 0.6) {
            // Renueva a tiempo, en la semana en que vence.
            $dia = $vence->subDays($this->azar(0, 5))->max($registro->startOfDay()->addDay());

            return ['registro' => $this->momento($dia), 'inicio' => $vence->addDay(), 'enlazada' => true];
        }

        if ($suerte < 0.74) {
            // Vuelve más tarde. Desde la ficha se renueva la vencida (queda
            // enlazada y empieza ese día); otras veces se le hace una nueva.
            $dia = $vence->addDays($this->azar(4, 80));

            if ($dia->gt($this->hoy)) {
                return null;
            }

            return ['registro' => $this->momento($dia), 'inicio' => $dia, 'enlazada' => $this->probabilidad(0.5)];
        }

        return null;
    }

    /**
     * Una membresía nueva, como la escriben el alta y la renovación.
     *
     * El precio sale del plan (PrecioMembresia) y del convenio
     * (PrecioAcordado), el descuento a mano lleva su motivo, y base menos
     * descuento es el final. La primera de cada socio es la del alta
     * (RegistroClienteService); las demás, RegistroInscripcionService.
     */
    private function inscribir(Cliente $socio, Membresia $plan, CarbonImmutable $inicio, CarbonImmutable $registro, ?Inscripcion $anterior, bool $esElAlta, array $perfil): Inscripcion
    {
        $precio = $this->precioEn($plan, $registro);
        $base = (int) round($precio->precio_normal);

        $idConvenio = $socio->id_convenio && PrecioAcordado::para($precio, $socio->id_convenio) < $base
            ? (int) $socio->id_convenio
            : null;
        $acordado = PrecioAcordado::para($precio, $idConvenio);

        [$manual, $motivo, $nota] = $this->descuentoManual($acordado, $plan);

        if (! $motivo && $idConvenio && $this->convenioEducativo($idConvenio)) {
            $motivo = $this->motivos['Convenio Estudiante'];
        }

        $final = max(0, $acordado - $manual);

        return $this->enElMomento($registro, function () use ($socio, $plan, $precio, $inicio, $anterior, $esElAlta, $idConvenio, $motivo, $nota, $base, $final) {
            $datos = [
                'id_cliente' => $socio->id,
                'id_membresia' => $plan->id,
                'id_convenio' => $idConvenio,
                'id_motivo_descuento' => $motivo,
                'id_precio_acordado' => $precio->id,
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                'fecha_inscripcion' => today()->format('Y-m-d'),
                'fecha_inicio' => $inicio->format('Y-m-d'),
                'fecha_vencimiento' => $plan->vencimientoDesde($inicio)->format('Y-m-d'),
                'precio_base' => $base,
                'descuento_aplicado' => $base - $final,
                'precio_final' => $final,
                'max_pausas_permitidas' => (int) $plan->max_pausas,
                'observaciones' => $nota ?? $this->quizas(0.12, [
                    'Paga los días 5, cuando le llega el sueldo.',
                    'Viene recomendado por un socio.',
                    'Entrena en la mañana, antes del trabajo.',
                    'Pidió que le avisen por WhatsApp cuando esté por vencer.',
                ]),
            ];

            if ($anterior) {
                $datos += [
                    'es_cambio_plan' => true,
                    'tipo_cambio' => 'renovacion',
                    'id_inscripcion_anterior' => $anterior->id,
                ];
            }

            $inscripcion = Inscripcion::create($datos);

            // Inscribir reactiva al socio dado de baja (RegistroInscripcionService::registrar).
            if (! $socio->activo) {
                $socio->update(['activo' => true]);
            }

            $this->primerPago($inscripcion, $plan, $esElAlta);

            if ($anterior) {
                // cerrarLaAnterior(): el historial con el estado que tenía y la vieja, vencida.
                HistorialCambio::registrarRenovacion($anterior->fresh(), $inscripcion, $this->quien()->id);
                $anterior->update([
                    'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
                    'pausada' => false,
                    'dias_pausa' => null,
                    'dias_restantes_al_pausar' => null,
                    'fecha_pausa_inicio' => null,
                    'fecha_pausa_fin' => null,
                    'razon_pausa' => null,
                    'pausa_indefinida' => false,
                ]);
            }

            return $inscripcion;
        });
    }

    /** @return array{0:int,1:?int,2:?string} descuento a mano, su motivo y la nota */
    private function descuentoManual(int $acordado, Membresia $plan): array
    {
        if ($plan->esPase()) {
            return [0, null, null];
        }

        // Una cortesía del 100 %: se registra con un pago de $0 que la deja al día.
        if (! $this->huboCortesia && $plan->nombre === 'Mensual' && $this->probabilidad(0.05)) {
            $this->huboCortesia = true;

            return [$acordado, $this->motivos['Acuerdo Especial'], 'Cortesía: premio del sorteo de Instagram.'];
        }

        if (! $this->probabilidad(0.08)) {
            return [0, null, null];
        }

        $motivo = $this->elegir(['Promoción Mensual', 'Cliente Frecuente', 'Acuerdo Especial']);
        $monto = $plan->nombre === 'Mensual' ? 5000 : (int) (round($acordado * 0.1 / 1000) * 1000);

        $nota = match ($motivo) {
            'Promoción Mensual' => 'Promoción del mes: descuento al inscribirse.',
            'Cliente Frecuente' => 'Descuento por ser socio de hace años.',
            default => 'Acuerdo con el dueño.',
        };

        return [min($monto, $acordado), $this->motivos[$motivo], $nota];
    }

    /**
     * El cobro del día en que se inscribe.
     *
     * Con la forma de cada pantalla: el alta (RegistroClienteService) guarda
     * la referencia de la transferencia y no el período; la inscripción
     * (RegistroInscripcionService) guarda el período y no la referencia.
     */
    private function primerPago(Inscripcion $inscripcion, Membresia $plan, bool $esElAlta): void
    {
        $final = (int) $inscripcion->precio_final;
        $forma = ($plan->esPase() || $final < 4000)
            ? 'completo'
            : $this->elegirConPeso(['completo' => 74, 'parcial' => 15, 'pendiente' => 11]);

        $metodo = $plan->esPase()
            ? $this->elegirConPeso(['efectivo' => 70, 'tarjeta' => 30])
            : $this->metodoAlAzar();

        $periodo = $esElAlta ? [] : [
            'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
            'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
        ];

        $referencia = ($esElAlta && $metodo === 'transferencia' && $this->probabilidad(0.8))
            ? $this->referencia()
            : null;

        match ($forma) {
            'completo' => $this->registrarPago($inscripcion, $periodo + [
                'monto_abonado' => $final,
                'tipo_pago' => 'completo',
                'id_metodo_pago' => $this->metodos[$metodo],
                'referencia_pago' => $referencia,
            ]),
            'parcial' => $this->registrarPago($inscripcion, $periodo + [
                'monto_abonado' => $this->abonoDe($final),
                'tipo_pago' => 'parcial',
                'id_metodo_pago' => $this->metodos[$metodo],
                'referencia_pago' => $referencia,
            ]),
            'pendiente' => $this->registrarPago($inscripcion, $periodo + [
                'monto_abonado' => 0,
                'tipo_pago' => 'pendiente',
                'id_metodo_pago' => null,
                'observaciones' => $esElAlta ? null : 'Inscripción sin pago inicial',
            ]),
        };
    }

    /**
     * Lo que paga después: el resto de una vez, en dos medios o en abonos.
     *
     * Con la forma de RegistroPagoService: un mixto es UNA fila con sus dos
     * medios y el reparto, y los dos montos suman justo el saldo.
     */
    private function cobrarElSaldo(Inscripcion $inscripcion, CarbonImmutable $registro, bool $vigente): void
    {
        $saldo = $this->saldoDe($inscripcion);

        if ($saldo <= 0 || ! $this->probabilidad($vigente ? 0.45 : 0.9)) {
            return;
        }

        $dia = $registro->startOfDay()->addDays($this->azar(1, 20));

        if ($dia->gt($this->hoy)) {
            return;
        }

        $forma = $saldo < 4000 ? 'completo' : $this->elegirConPeso(['completo' => 55, 'mixto' => 25, 'abono' => 20]);

        $this->enElMomento($this->momento($dia), function () use ($inscripcion, $saldo, $forma, $dia) {
            if ($forma === 'mixto') {
                $uno = $this->abonoDe($saldo);
                $primero = $this->elegir(['efectivo', 'tarjeta']);
                $this->registrarCobro($inscripcion, [
                    'monto_abonado' => $saldo,
                    'tipo_pago' => 'mixto',
                    'id_metodo_pago' => $this->metodos[$primero],
                    'id_metodo_pago2' => $this->metodos['transferencia'],
                    'monto_metodo1' => $uno,
                    'monto_metodo2' => $saldo - $uno,
                    'referencia_pago' => $this->referencia(),
                    'observaciones' => ucfirst($primero) . ' y el resto por transferencia.',
                ]);

                return;
            }

            if ($forma === 'abono') {
                $this->registrarCobro($inscripcion, $this->conMetodo([
                    'monto_abonado' => $this->abonoDe($saldo),
                    'tipo_pago' => 'parcial',
                ]));

                // Y a veces vuelve a la semana con lo que faltaba.
                $otroDia = $dia->addDays($this->azar(3, 12));

                if ($otroDia->lte($this->hoy) && $this->probabilidad(0.6)) {
                    $this->enElMomento($this->momento($otroDia), fn () => $this->registrarCobro($inscripcion, $this->conMetodo([
                        'monto_abonado' => $this->saldoDe($inscripcion),
                        'tipo_pago' => 'completo',
                    ])));
                }

                return;
            }

            $this->registrarCobro($inscripcion, $this->conMetodo([
                'monto_abonado' => $saldo,
                'tipo_pago' => 'completo',
            ]));
        });
    }

    /** Un cobro desde Pagos (RegistroPagoService): con período y cuotas. */
    private function registrarCobro(Inscripcion $inscripcion, array $datos): Pago
    {
        return $this->registrarPago($inscripcion, $datos + [
            'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
            'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
            'cantidad_cuotas' => 1,
            'numero_cuota' => 1,
            'monto_cuota' => $datos['monto_abonado'],
        ]);
    }

    /**
     * Escribe un pago y vuelve a cuadrar los de su membresía.
     *
     * recalcularSusPagos() es lo que hace la revisión de cada noche
     * (pagos:sincronizar-estados): el saldo de cada fila es lo que quedaba
     * después de ella y el estado es el de la membresía entera.
     */
    private function registrarPago(Inscripcion $inscripcion, array $datos): Pago
    {
        $final = (int) $inscripcion->precio_final;
        $antes = (int) $inscripcion->pagos()->sum('monto_abonado');
        $monto = (int) $datos['monto_abonado'];

        if ($monto < 0 || $antes + $monto > $final) {
            throw new \LogicException("Se iba a cobrar {$monto} sobre una membresía de {$final} con {$antes} ya pagados.");
        }

        $saldo = $final - $antes - $monto;

        $pago = Pago::create($datos + [
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $final,
            'monto_pendiente' => $saldo,
            'id_estado' => match (true) {
                $saldo <= 0 => EstadosCodigo::PAGO_PAGADO,
                $antes + $monto <= 0 => EstadosCodigo::PAGO_PENDIENTE,
                default => EstadosCodigo::PAGO_PARCIAL,
            },
            'fecha_pago' => today()->format('Y-m-d'),
        ]);

        $inscripcion->recalcularSusPagos();

        return $pago;
    }

    /**
     * Una pausa: ya terminada (corre el vencimiento) o en curso.
     *
     * Como Inscripcion::pausar() y reanudar(): al pausar se guardan los días
     * que quedaban; la revisión del día en que acaba la reanuda y el
     * vencimiento pasa a ser ese día más los días guardados. Las dos quedan en
     * el historial.
     */
    private function quizasPausar(Inscripcion $inscripcion): ?string
    {
        $plan = $this->planes[$inscripcion->membresia->nombre];

        if ((int) $plan->max_pausas < 1 || $plan->esPase()) {
            return null;
        }

        $inicio = CarbonImmutable::parse($inscripcion->fecha_inicio->format('Y-m-d'));
        $vence = CarbonImmutable::parse($inscripcion->fecha_vencimiento->format('Y-m-d'));
        $razones = [
            'Viaje por trabajo a la mina, turno de 14x14.',
            'Esguince de tobillo: reposo indicado por el traumatólogo.',
            'Vacaciones en el sur con la familia.',
            'Operación de menisco, con licencia médica.',
            'Turnos de noche durante el mes.',
        ];

        // En pausa ahora mismo.
        if ($this->probabilidad(0.07)) {
            $desde = $this->hoy->subDays($this->azar(1, 25));
            $indefinida = ! $this->huboIndefinida;
            $dias = $this->azar(Inscripcion::diasEntre($desde, $this->hoy) + 3, 45);

            if ($desde->gte($inicio->addDays(3)) && Inscripcion::diasEntre($desde, $vence) >= 5) {
                $this->huboIndefinida = $this->huboIndefinida || $indefinida;

                $this->enElMomento($this->momento($desde), function () use ($inscripcion, $desde, $vence, $dias, $indefinida, $razones) {
                    $razon = $indefinida ? 'Licencia médica sin fecha de alta.' : $this->elegir($razones);
                    $inscripcion->update([
                        'pausada' => true,
                        'dias_pausa' => $indefinida ? null : $dias,
                        'dias_restantes_al_pausar' => max(0, Inscripcion::diasEntre($desde, $vence)),
                        'fecha_pausa_inicio' => $desde->format('Y-m-d'),
                        'fecha_pausa_fin' => $indefinida ? null : $desde->addDays($dias)->format('Y-m-d'),
                        'razon_pausa' => $razon,
                        'pausa_indefinida' => $indefinida,
                        'pausas_realizadas' => $inscripcion->pausas_realizadas + 1,
                        'id_estado' => EstadosCodigo::INSCRIPCION_PAUSADA,
                    ]);

                    HistorialCambio::registrarPausa($inscripcion, [
                        'dias' => $indefinida ? null : $dias,
                        'razon' => $razon,
                        'indefinida' => $indefinida,
                        'fecha_fin' => $inscripcion->fecha_pausa_fin?->format('Y-m-d'),
                    ], $this->quien()->id);
                });

                return 'en_curso';
            }
        }

        // Una pausa que ya terminó.
        $duracion = Inscripcion::diasEntre($inicio, $vence);

        if ($duracion < 25 || ! $this->probabilidad(0.1)) {
            return null;
        }

        $desde = $inicio->addDays($this->azar(3, $duracion - 12));
        $dias = $this->azar(7, 30);
        $vuelve = $desde->addDays($dias);
        $guardados = Inscripcion::diasEntre($desde, $vence);

        if ($vuelve->gt($this->hoy) || $guardados < 10) {
            return null;
        }

        $razon = $this->elegir($razones);

        $this->enElMomento($this->momento($desde), fn () => HistorialCambio::registrarPausa($inscripcion, [
            'dias' => $dias,
            'razon' => $razon,
            'indefinida' => false,
            'fecha_fin' => $vuelve->format('Y-m-d'),
        ], $this->quien()->id));

        // La reanuda la revisión de esa madrugada (sin usuario): desde el fin
        // de la pausa, con los días que tenía guardados.
        $this->enElMomento($vuelve->setTime(1, 0), function () use ($inscripcion, $vuelve, $guardados, $dias) {
            $inscripcion->update([
                'fecha_vencimiento' => $vuelve->addDays($guardados)->format('Y-m-d'),
                'pausas_realizadas' => $inscripcion->pausas_realizadas + 1,
            ]);

            HistorialCambio::registrarReanudacion($inscripcion, $dias, $guardados);
        });

        return 'terminada';
    }


    /**
     * La revisión de la madrugada siguiente al vencimiento.
     *
     * inscripciones:actualizar-estados la marca vencida con su nota, y
     * clientes:desactivar-vencidos da de baja a quien ya no tiene ninguna
     * vigente ni pausada.
     */
    private function revisionDeLaNoche(Cliente $socio, Inscripcion $inscripcion, CarbonImmutable $vence): void
    {
        $noche = $vence->addDay()->setTime(1, 0);

        if ($noche->gt($this->ahora)) {
            return;
        }

        $this->enElMomento($noche, function () use ($socio, $inscripcion) {
            $inscripcion->refresh();

            if ((int) $inscripcion->id_estado === EstadosCodigo::INSCRIPCION_ACTIVA) {
                $inscripcion->update([
                    'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
                    'observaciones' => ($inscripcion->observaciones ? $inscripcion->observaciones . "\n" : '')
                        . '[' . now()->format('d/m/Y H:i') . '] Marcada como vencida automáticamente (vencimiento: ' . $inscripcion->fecha_vencimiento->format('d/m/Y') . ')',
                ]);
            }

            $sigueConAlgo = Inscripcion::where('id_cliente', $socio->id)
                ->whereIn('id_estado', [EstadosCodigo::INSCRIPCION_ACTIVA, EstadosCodigo::INSCRIPCION_PAUSADA])
                ->exists();

            $socio->refresh();

            if ($socio->activo && ! $sigueConAlgo) {
                $socio->update(['activo' => false]);
            }
        });
    }

    /** Quien viene de paso: uno o varios pases diarios, el último quizás hoy. */
    private function visitaConPases(int $i): void
    {
        $dias = [];
        $cuantos = $this->azar(1, 4);

        for ($n = 0; $n < $cuantos; $n++) {
            $dias[] = $this->hoy->subDays($this->azar(1, 150));
        }

        // Dos de ellos están entrenando hoy.
        if ($i < 2) {
            $dias[] = $this->hoy;
        }

        $dias = collect($dias)->unique(fn ($d) => $d->format('Y-m-d'))->sort()->values();

        $socio = $this->crearSocio($dias->first(), ['menor' => false, 'visita' => true]);
        $this->papeles['pases'][] = $socio->id;
        $pase = $this->planes['Pase Diario'];

        foreach ($dias as $n => $dia) {
            $inscripcion = $this->inscribir($socio, $pase, $dia, $this->momento($dia, 8, 19), null, $n === 0, []);
            $this->revisionDeLaNoche($socio, $inscripcion, $dia);
        }
    }

    // =====================================================================
    // Traspasos y cambios de plan
    // =====================================================================

    /**
     * Dos traspasos, como Admin\InscripcionController::traspasar().
     *
     * La membresía cambia de dueño con sus pagos —no se crea otra—, queda la
     * marca del traspaso y una fila en historial_traspasos. Quien la cede se
     * queda sin plan y la revisión de esa noche lo da de baja.
     */
    private function traspasos(): void
    {
        $casos = [
            ['Semestral', 60, 12, false, 'Se va a trabajar a Concepción: se la cede a su hermana.'],
            ['Trimestral', 50, 5, true, 'Lesión de hombro que lo deja fuera meses: se la pasa a su primo, que se hace cargo del saldo.'],
        ];

        foreach ($casos as [$nombrePlan, $haceCuanto, $traspasoHace, $conDeuda, $motivo]) {
            // Quien cede: socio de antes, con una mensualidad vieja ya vencida.
            $altaVieja = $this->hoy->subDays($this->azar(220, 400));
            $origen = $this->crearSocio($altaVieja, ['menor' => false]);
            $vieja = $this->inscribir($origen, $this->planes['Mensual'], $altaVieja, $this->momento($altaVieja), null, true, []);
            $this->asegurarPagada($vieja, $altaVieja);
            $this->revisionDeLaNoche($origen, $vieja, CarbonImmutable::parse($vieja->fecha_vencimiento->format('Y-m-d')));

            $dia = $this->hoy->subDays($haceCuanto);
            $plan = $this->planes[$nombrePlan];
            $inscripcion = $this->enElMomento($this->momento($dia), function () use ($origen, $plan, $dia, $conDeuda) {
                $precio = $this->precios[$plan->id];
                $final = (int) round($precio->precio_normal);
                $i = Inscripcion::create([
                    'id_cliente' => $origen->id,
                    'id_membresia' => $plan->id,
                    'id_precio_acordado' => $precio->id,
                    'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                    'fecha_inscripcion' => $dia->format('Y-m-d'),
                    'fecha_inicio' => $dia->format('Y-m-d'),
                    'fecha_vencimiento' => $plan->vencimientoDesde($dia)->format('Y-m-d'),
                    'precio_base' => $final,
                    'descuento_aplicado' => 0,
                    'precio_final' => $final,
                    'max_pausas_permitidas' => (int) $plan->max_pausas,
                ]);
                $origen->update(['activo' => true]);
                $this->registrarPago($i, [
                    'monto_abonado' => $conDeuda ? (int) ($final / 2) : $final,
                    'tipo_pago' => $conDeuda ? 'parcial' : 'completo',
                    'id_metodo_pago' => $this->metodos[$conDeuda ? 'efectivo' : 'transferencia'],
                    'periodo_inicio' => $i->fecha_inicio->format('Y-m-d'),
                    'periodo_fin' => $i->fecha_vencimiento->format('Y-m-d'),
                ]);

                return $i;
            });

            // Quien recibe: se registra ese mismo día, sin plan, para recibirla.
            $diaTraspaso = $this->hoy->subDays($traspasoHace);
            $momento = $this->momento($diaTraspaso, 10, 18);
            $destino = $this->crearSocio($diaTraspaso, ['menor' => false], $momento->subHour());

            $this->enElMomento($momento, function () use ($inscripcion, $origen, $destino, $motivo) {
                $inscripcion->load(['cliente', 'membresia', 'pagos']);
                $info = $inscripcion->getInfoTraspaso();
                $nombreOrigen = $origen->nombres . ' ' . $origen->apellido_paterno;
                $nombreDestino = "{$destino->nombres} {$destino->apellido_paterno}";

                HistorialTraspaso::create([
                    'inscripcion_origen_id' => $inscripcion->id,
                    'inscripcion_destino_id' => $inscripcion->id,
                    'cliente_origen_id' => $origen->id,
                    'cliente_destino_id' => $destino->id,
                    'membresia_id' => $inscripcion->id_membresia,
                    'fecha_traspaso' => now(),
                    'motivo' => $motivo,
                    'dias_restantes_traspasados' => $info['dias_restantes'],
                    'fecha_vencimiento_original' => $inscripcion->fecha_vencimiento,
                    'monto_pagado' => $info['monto_pagado'],
                    'deuda_transferida' => $info['monto_pendiente'],
                    'se_transfirio_deuda' => $info['tiene_deuda'],
                    'usuario_id' => $this->admin->id,
                ]);

                $inscripcion->update([
                    'id_cliente' => $destino->id,
                    'es_traspaso' => true,
                    'id_cliente_original' => $origen->id,
                    'fecha_traspaso' => now(),
                    'motivo_traspaso' => $motivo,
                    'observaciones' => ($inscripcion->observaciones ? $inscripcion->observaciones . "\n" : '')
                        . '[' . now()->format('d/m/Y H:i') . "] Traspaso de: {$nombreOrigen} → {$nombreDestino}. Motivo: {$motivo}",
                ]);

                foreach ($inscripcion->pagos as $pago) {
                    $pago->update([
                        'id_cliente' => $destino->id,
                        'observaciones' => ($pago->observaciones ? $pago->observaciones . "\n" : '')
                            . '[' . now()->format('d/m/Y H:i') . "] Transferido de: {$nombreOrigen} → {$nombreDestino}",
                    ]);
                }
            });

            // Quien cedió ya no tiene plan: la revisión de esa noche lo da de baja.
            $this->enElMomento($diaTraspaso->addDay()->setTime(1, 0), function () use ($origen) {
                if (! Inscripcion::where('id_cliente', $origen->id)->whereIn('id_estado', [100, 101])->exists()) {
                    $origen->refresh()->update(['activo' => false]);
                }
            });

            $this->papeles['traspaso'][] = ['origen' => $origen->id, 'destino' => $destino->id, 'inscripcion' => $inscripcion->id];
        }
    }

    /**
     * Dos mejoras de plan, como Admin\InscripcionController::cambiarPlan().
     *
     * La vieja queda «Cambiada» con vencimiento el día del cambio; la nueva
     * empieza ese día, enlazada, y lo ya pagado entra como descuento (crédito)
     * para que base menos descuento siga dando el final. Ese controlador NO
     * escribe historial_cambios, así que aquí tampoco.
     */
    private function cambiosDePlan(): void
    {
        $casos = [
            // [plan nuevo, alta hace, cambio hace, la mensual quedó debiendo, paga ahora]
            ['Trimestral', 20, 8, false, 'todo'],
            ['Semestral', 15, 3, true, 70000],
        ];

        foreach ($casos as [$nombreNuevo, $altaHace, $cambioHace, $debia, $pagaAhora]) {
            $alta = $this->hoy->subDays($altaHace);
            $socio = $this->crearSocio($alta, ['menor' => false]);
            $mensual = $this->planes['Mensual'];
            $precioMensual = $this->precios[$mensual->id];

            $vieja = $this->enElMomento($this->momento($alta), function () use ($socio, $mensual, $precioMensual, $alta, $debia) {
                $i = Inscripcion::create([
                    'id_cliente' => $socio->id,
                    'id_membresia' => $mensual->id,
                    'id_precio_acordado' => $precioMensual->id,
                    'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                    'fecha_inscripcion' => $alta->format('Y-m-d'),
                    'fecha_inicio' => $alta->format('Y-m-d'),
                    'fecha_vencimiento' => $mensual->vencimientoDesde($alta)->format('Y-m-d'),
                    'precio_base' => 40000,
                    'descuento_aplicado' => 0,
                    'precio_final' => 40000,
                    'max_pausas_permitidas' => (int) $mensual->max_pausas,
                ]);
                $this->registrarPago($i, [
                    'monto_abonado' => $debia ? 20000 : 40000,
                    'tipo_pago' => $debia ? 'parcial' : 'completo',
                    'id_metodo_pago' => $this->metodos['efectivo'],
                ]);

                return $i;
            });

            $dia = $this->hoy->subDays($cambioHace);
            $nuevo = $this->planes[$nombreNuevo];
            $precioNuevo = $this->precios[$nuevo->id];

            $this->enElMomento($this->momento($dia), function () use ($vieja, $socio, $nuevo, $precioNuevo, $pagaAhora, $debia) {
                $vieja->load(['membresia', 'pagos']);
                $precioNuevoPlan = (int) round($precioNuevo->precio_normal);
                $deudaAnterior = (int) $vieja->monto_pendiente;
                $credito = min((int) $vieja->monto_pagado, $precioNuevoPlan);
                $diferencia = max(0, $precioNuevoPlan - $credito);
                $inicio = now();
                $vence = $nuevo->vencimientoDesde($inicio);

                $vieja->update([
                    'id_estado' => EstadosCodigo::INSCRIPCION_CAMBIADA,
                    'fecha_vencimiento' => now()->format('Y-m-d'),
                    'observaciones' => ($vieja->observaciones ? $vieja->observaciones . "\n" : '')
                        . '[' . now()->format('d/m/Y H:i') . "] Cambio de plan a: {$nuevo->nombre}",
                ]);

                $observaciones = "Cambio de plan desde: {$vieja->membresia->nombre}. Crédito de lo pagado: $" . number_format($credito, 0, ',', '.');
                if ($debia && $deudaAnterior > 0) {
                    $observaciones .= '. Al cambiar quedaban $' . number_format($deudaAnterior, 0, ',', '.') . ' sin pagar del plan anterior';
                }

                $nueva = Inscripcion::create([
                    'id_cliente' => $socio->id,
                    'id_membresia' => $nuevo->id,
                    'id_precio_acordado' => $precioNuevo->id,
                    'fecha_inscripcion' => now()->format('Y-m-d'),
                    'fecha_inicio' => $inicio->format('Y-m-d'),
                    'fecha_vencimiento' => $vence->format('Y-m-d'),
                    'precio_base' => $precioNuevoPlan,
                    'descuento_aplicado' => $credito,
                    'precio_final' => $precioNuevoPlan - $credito,
                    'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                    'observaciones' => $observaciones,
                    'max_pausas_permitidas' => (int) $nuevo->max_pausas,
                    'id_inscripcion_anterior' => $vieja->id,
                    'es_cambio_plan' => true,
                    'tipo_cambio' => 'upgrade',
                    'credito_plan_anterior' => $credito,
                    'precio_nuevo_plan' => $precioNuevoPlan,
                    'diferencia_a_pagar' => $diferencia,
                    'fecha_cambio_plan' => now(),
                    'motivo_cambio_plan' => $debia ? 'Quiere entrenar todo el semestre con el plan del preuniversitario.' : 'Le gustó y prefiere el trimestral, que sale más barato al mes.',
                ]);

                $monto = $pagaAhora === 'todo' ? $diferencia : min((int) $pagaAhora, $diferencia);

                // El controlador no pone tipo_pago y la columna dice «completo»
                // aunque quede saldo: aquí va el que corresponde.
                $this->registrarPago($nueva, [
                    'monto_abonado' => $monto,
                    'tipo_pago' => $monto >= $diferencia ? 'completo' : 'parcial',
                    'id_metodo_pago' => $this->metodos['tarjeta'],
                    'periodo_inicio' => $inicio->format('Y-m-d'),
                    'periodo_fin' => $vence->format('Y-m-d'),
                ]);
            });

            $this->papeles['cambio'][] = $socio->id;
        }
    }

    /** Para los guiones: la deja saldada en su día, si no lo estaba. */
    private function asegurarPagada(Inscripcion $inscripcion, CarbonImmutable $dia): void
    {
        $saldo = $this->saldoDe($inscripcion);

        if ($saldo > 0) {
            $this->enElMomento($this->momento($dia), fn () => $this->registrarCobro($inscripcion, $this->conMetodo([
                'monto_abonado' => $saldo,
                'tipo_pago' => 'completo',
            ])));
        }
    }

    // =====================================================================
    // Bajas definitivas: papelera y borrado de datos
    // =====================================================================

    /**
     * Los que se pueden mandar a la papelera o borrarles los datos: sin plan,
     * sin deuda, sin pago pendiente y sin un papel en otro guion.
     *
     * @return array{0:list<int>,1:list<int>}
     */
    private function elegirBajasDefinitivas(): array
    {
        $borrado = app(BorradoDeDatosService::class);

        $candidatos = Cliente::where('activo', false)
            ->whereIn('id', $this->papeles['cadena'])
            ->orderBy('id')
            ->get()
            ->filter(fn (Cliente $c) => $borrado->porQueNoSePuede($c) === null)
            ->values();

        return [
            $candidatos->slice(0, 3)->pluck('id')->all(),
            $candidatos->slice(3, 2)->pluck('id')->all(),
        ];
    }

    private function papeleraYBorrados(array $aLaPapelera, array $aBorrar): void
    {
        foreach ($aLaPapelera as $id) {
            $socio = Cliente::find($id);
            $this->enElMomento($this->momento($this->hoy->subDays($this->azar(2, 30))), fn () => $socio->delete());
        }

        $servicio = app(BorradoDeDatosService::class);

        foreach (array_values($aBorrar) as $n => $id) {
            $socio = Cliente::find($id);
            $this->enElMomento($this->momento($this->hoy->subDays($this->azar(1, 6))), fn () => $servicio->borrar(
                $socio,
                $this->admin->id,
                $n === 0 ? 'solicitud' : 'plazo'
            ));
        }
    }

    // =====================================================================
    // Contratos
    // =====================================================================

    /**
     * Contratos por correo en todos sus estados, como ContratoDigitalService.
     *
     * No se llama a enviar() ni a firmar(): los dos mandan correos. Se escribe
     * lo mismo que ellos —el aviso con el enlace tapado, la firma con el texto
     * del día y su huella— sin que salga nada.
     */
    private function contratos(array $aBorrar): void
    {
        $adultos = Cliente::where('activo', true)
            ->where('es_menor_edad', false)
            ->whereNotNull('email')
            ->where('run_pasaporte', 'like', '%-%')
            ->whereIn('id', [...$this->papeles['cadena'], ...$this->papeles['menores']])
            ->orderBy('id')
            ->get()
            ->values();

        $menor = Cliente::where('es_menor_edad', true)->whereNotNull('apoderado_email')->orderBy('id')->first();

        // Firmados: seis socios, un menor (firma su apoderado) y uno al que
        // después se le borran los datos.
        foreach ([...$adultos->slice(0, 6)->all(), $menor, Cliente::find($aBorrar[0] ?? 0)] as $socio) {
            if ($socio) {
                $enviado = $this->momento(CarbonImmutable::parse($socio->created_at->format('Y-m-d')));
                $contrato = $this->enviarContrato($socio, $enviado);
                $this->firmarContrato($contrato, $enviado->addHours($this->azar(2, 30)));
            }
        }

        // Pendientes: mandados hace poco, todavía sin firmar.
        foreach ($adultos->slice(6, 3) as $socio) {
            $this->enviarContrato($socio, $this->momento($this->hoy->subDays($this->azar(0, 3))));
        }

        // Vencido: el enlace caducó y nadie lo volvió a mandar.
        $this->enviarContrato($adultos[9], $this->momento($this->hoy->subDays(25)));

        // Anulado: venció, se le mandó otro y el viejo quedó anulado.
        $this->enviarContrato($adultos[10], $this->momento($this->hoy->subDays(20)));
        $this->enviarContrato($adultos[10], $this->momento($this->hoy->subDays(1)));

        // Fallido: el correo no salió.
        $this->enviarContrato($adultos[11], $this->momento($this->hoy->subDays(4)), 'Connection could not be established with host "smtp.gmail.com:587": stream_socket_client(): unable to connect (Network is unreachable)');
    }

    private function enviarContrato(Cliente $socio, CarbonImmutable $cuando, ?string $falla = null): Contrato
    {
        $servicio = app(ContratoDigitalService::class);

        return $this->enElMomento($cuando, function () use ($socio, $servicio, $falla) {
            $firmante = $servicio->firmante($socio);
            $dias = max(1, Ajustes::numero('reglas.dias_para_firmar'));

            $anteriores = Contrato::where('id_cliente', $socio->id)->whereNull('firmado_en')->whereNull('anulado_en')->pluck('id');

            $vigente = Inscripcion::where('id_cliente', $socio->id)
                ->whereNotIn('id_estado', [103, 105, 106])
                ->where('created_at', '<=', now())
                ->orderByDesc('fecha_vencimiento')
                ->first();

            $contrato = Contrato::create([
                'id_cliente' => $socio->id,
                'id_inscripcion' => $vigente?->id,
                'token_hash' => hash('sha256', Str::random(48)),
                'firmante_tipo' => $firmante['tipo'],
                'email_destino' => $firmante['email'],
                'vence_en' => now()->addDays($dias)->endOfDay(),
                'id_usuario' => $this->quien()->id,
            ]);

            $plantilla = TipoNotificacion::where('codigo', ContratoDigitalService::PLANTILLA_ENVIO)->first();
            $aviso = $this->avisoDeContrato($contrato, $plantilla, $firmante, ['vence_enlace' => $contrato->vence_en->format('d/m/Y'), 'firmado_en' => '']);

            if ($falla) {
                $aviso?->marcarComoFallida($falla);
                $contrato->update(['anulado_en' => now(), 'error_envio' => $falla]);

                return $contrato;
            }

            $aviso?->marcarComoEnviada();
            $contrato->update(['enviado_en' => now()]);

            if ($anteriores->isNotEmpty()) {
                Contrato::whereIn('id', $anteriores)->update(['anulado_en' => now()]);
            }

            return $contrato;
        });
    }

    /** El correo del contrato, como ContratoDigitalService::mandar(): manual, un intento, enlace tapado. */
    private function avisoDeContrato(Contrato $contrato, ?TipoNotificacion $plantilla, array $firmante, array $variables): ?Notificacion
    {
        if (! $plantilla || blank($firmante['email'])) {
            return null;
        }

        $socio = $contrato->cliente;
        $correo = $plantilla->renderizar($variables + [
            'firmante' => $firmante['nombre'],
            'enlace_contrato' => ContratoDigitalService::ENLACE_OCULTO,
            'gimnasio' => 'PRO GYM',
            'nombre' => $firmante['nombre'],
            'nombre_cliente' => $socio->nombre_completo,
        ]);

        $aviso = Notificacion::create([
            'id_tipo_notificacion' => $plantilla->id,
            'id_cliente' => $contrato->id_cliente,
            'id_inscripcion' => $contrato->id_inscripcion,
            'email_destino' => $firmante['email'],
            'asunto' => $correo['asunto'],
            'contenido' => $correo['contenido'],
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
            'enviado_por_user_id' => $contrato->firmado_en ? null : $contrato->id_usuario,
            'max_intentos' => 1,
        ]);

        $aviso->registrarLog('programada', $contrato->firmado_en ? 'Copia del contrato firmado' : 'Contrato para firmar por ' . $this->quien()->name);

        return $aviso;
    }

    /** La firma, como firmar(): el texto del día con la firma dibujada y su huella. */
    private function firmarContrato(Contrato $contrato, CarbonImmutable $cuando): void
    {
        $servicio = app(ContratoDigitalService::class);

        $this->enElMomento($cuando, function () use ($contrato, $servicio) {
            $cliente = $contrato->cliente;
            $firmante = $servicio->firmante($cliente);
            $fecha = now();
            $documento = $servicio->documento($contrato, $fecha);
            $imagen = $this->probabilidad(0.7);
            $difusion = $imagen && $this->probabilidad(0.5);

            $contenido = view('contrato.partes.documento', $documento + [
                'firma' => $this->firmaDibujada(),
                'nombre' => $firmante['nombre'],
                'rut' => $firmante['rut'],
                'fecha' => $fecha,
                'ip' => '203.0.113.' . $this->azar(10, 250),
                'email' => $contrato->email_destino,
                'consentimiento_imagen' => $imagen,
                'consentimiento_difusion' => $difusion,
                'enlace_privacidad' => TextosLegales::direccion(route('landing.privacidad', [], false)),
            ])->render();

            $contrato->update([
                'firmado_en' => $fecha,
                'firmante_nombre' => $firmante['nombre'],
                'firmante_rut' => $firmante['rut'],
                'ip' => '203.0.113.' . $this->azar(10, 250),
                'navegador' => 'Mozilla/5.0 (Linux; Android 14; SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Mobile Safari/537.36',
                'version_contrato' => $documento['textos']['contrato']->version,
                'version_terminos' => $documento['textos']['terminos']->version,
                'version_privacidad' => $documento['textos']['privacidad']->version,
                'consentimiento_imagen' => $imagen,
                'consentimiento_difusion' => $difusion,
                'contenido' => $contenido,
                'huella' => hash('sha256', $contenido),
            ]);

            app(RegistroClienteService::class)->registrarContrato($cliente, [
                'contrato_version' => (string) $documento['textos']['contrato']->version,
                'contrato_firmado_en' => $fecha->toDateString(),
                'consentimiento_imagen' => $imagen,
                'consentimiento_difusion' => $difusion,
            ]);

            if ($contrato->firmante_tipo === 'apoderado') {
                $cliente->update(['consentimiento_apoderado' => true]);
            }

            // La copia firmada, a quien firmó.
            $copia = TipoNotificacion::where('codigo', ContratoDigitalService::PLANTILLA_COPIA)->first();
            $this->avisoDeContrato($contrato->fresh(), $copia, $firmante, [
                'vence_enlace' => '',
                'firmado_en' => $fecha->format('d/m/Y \a \l\a\s H:i'),
            ])?->marcarComoEnviada();
        });
    }

    /** Un PNG de 300×100 con una raya, armado a mano (como tests/FirmaDePrueba). */
    private function firmaDibujada(): string
    {
        $ancho = 300;
        $alto = 100;
        $filas = '';

        for ($y = 0; $y < $alto; $y++) {
            $fila = str_repeat("\xff\xff\xff", $ancho);
            $x = (int) floor($y * ($ancho - 3) / ($alto - 1));
            $filas .= "\0" . substr_replace($fila, str_repeat("\x11\x11\x14", 3), $x * 3, 9);
        }

        $trozo = fn (string $tipo, string $datos) => pack('N', strlen($datos)) . $tipo . $datos . pack('N', crc32($tipo . $datos));

        return 'data:image/png;base64,' . base64_encode("\x89PNG\r\n\x1a\n"
            . $trozo('IHDR', pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0))
            . $trozo('IDAT', gzcompress($filas))
            . $trozo('IEND', ''));
    }

    // =====================================================================
    // Correos
    // =====================================================================

    /**
     * Los avisos de los últimos meses, en cada estado.
     *
     * El contenido lo arma NotificacionService::crearNotificacion() en el día
     * en que se programó; después se marcan enviados o fallidos en su hora,
     * con sus registros.
     *
     * EL INTERRUPTOR SE PRENDE SOLO MIENTRAS SE ARMAN. La demo lo deja
     * apagado para no escribirle a nadie, pero crearNotificacion() ya no anota
     * nada con los automáticos apagados, y la demo se quedaba sin su
     * historial. Aquí no sale ningún correo: solo se escriben filas, y se
     * marcan enviadas a mano.
     */
    private function notificaciones(): void
    {
        $antes = \App\Support\Ajustes::obtener('tareas.correos_automaticos');
        \App\Support\Ajustes::guardar(['tareas.correos_automaticos' => '1']);

        try {
            $this->anotarNotificaciones();
        } finally {
            \App\Support\Ajustes::guardar(['tareas.correos_automaticos' => (string) $antes]);
        }
    }

    private function anotarNotificaciones(): void
    {
        $servicio = app(NotificacionService::class);
        $tipo = fn (string $codigo) => TipoNotificacion::where('codigo', $codigo)->first();
        $porVencer = $tipo(TipoNotificacion::MEMBRESIA_POR_VENCER);
        $anticipacion = max(1, (int) ($porVencer?->dias_anticipacion ?: 5));

        $conCorreo = fn (Inscripcion $i) => $i->cliente
            && ($i->cliente->email || ($i->cliente->es_menor_edad && $i->cliente->apoderado_email));

        // Bienvenida (y al apoderado, la confirmación) de las altas recientes.
        $altas = Inscripcion::with(['cliente', 'membresia'])
            ->whereNull('id_inscripcion_anterior')
            ->where('created_at', '>=', $this->hoy->subDays(90))
            ->get()
            ->filter(fn (Inscripcion $i) => $conCorreo($i) && ! $i->membresia->esPase()
                && $i->created_at->isSameDay($i->cliente->created_at));

        foreach ($altas as $inscripcion) {
            $momento = CarbonImmutable::parse($inscripcion->created_at)->addMinutes(2);
            $this->programarYEnviar($servicio, $tipo(TipoNotificacion::BIENVENIDA), $inscripcion, $momento, $momento->addMinute());

            if ($inscripcion->cliente->es_menor_edad && $inscripcion->cliente->apoderado_email) {
                $this->programarYEnviar($servicio, $tipo('confirmacion_tutor_legal'), $inscripcion, $momento, $momento->addMinutes(2));
            }
        }

        // «Tu membresía vence pronto»: la revisión los programa N días antes.
        $porVencerlas = Inscripcion::with(['cliente', 'membresia', 'inscripcionesPosteriores'])
            ->whereIn('id_estado', [100, 102])
            ->whereBetween('fecha_vencimiento', [$this->hoy->subDays(60), $this->hoy->addDays($anticipacion)])
            ->get()
            ->filter(fn (Inscripcion $i) => $conCorreo($i) && ! $i->membresia->esPase());

        $fallidas = 0;

        foreach ($porVencerlas as $inscripcion) {
            $dia = CarbonImmutable::parse($inscripcion->fecha_vencimiento->format('Y-m-d'))->subDays($anticipacion);
            $programado = $dia->setTime(1, 5);
            $renovada = $inscripcion->inscripcionesPosteriores->first();

            // Si ya había renovado ese día, ya no era «activa»: no hubo aviso.
            if ($dia->gt($this->hoy) || ($renovada && $renovada->created_at->lte($programado))
                || $inscripcion->created_at->gt($programado)) {
                continue;
            }

            if ($dia->equalTo($this->hoy)) {
                // Programado esta madrugada; sale a las 08:00 si están encendidos.
                $this->enElMomento($programado, fn () => $servicio->crearNotificacion($porVencer, $inscripcion));

                continue;
            }

            if ($fallidas < 3 && $this->probabilidad(0.12)) {
                // Rebotó tres veces: se agotaron los intentos y no se reintenta más.
                $fallidas++;
                $aviso = $this->enElMomento($programado, fn () => $servicio->crearNotificacion($porVencer, $inscripcion));
                foreach ([[0, 8], [0, 14], [1, 8]] as [$despues, $hora]) {
                    $this->enElMomento($dia->addDays($despues)->setTime($hora, 1), fn () => $aviso->marcarComoFallida(
                        '550 5.1.1 The email account that you tried to reach does not exist.'
                    ));
                }

                continue;
            }

            $this->programarYEnviar($servicio, $porVencer, $inscripcion, $programado, $dia->setTime(8, $this->azar(0, 9)));
        }

        // «Tu membresía venció»: las que se acabaron este último mes y medio sin renovar.
        $vencidas = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 102)
            ->whereDoesntHave('inscripcionesPosteriores')
            ->whereBetween('fecha_vencimiento', [$this->hoy->subDays(45), $this->hoy->subDay()])
            ->get()
            ->filter(fn (Inscripcion $i) => $conCorreo($i) && ! $i->membresia->esPase());

        foreach ($vencidas as $inscripcion) {
            $dia = CarbonImmutable::parse($inscripcion->fecha_vencimiento->format('Y-m-d'))->addDay();
            $this->programarYEnviar($servicio, $tipo(TipoNotificacion::MEMBRESIA_VENCIDA), $inscripcion, $dia->setTime(1, 10), $dia->setTime(8, $this->azar(10, 20)));
        }

        // Recordatorio de saldo, y el aviso de la pausa a quien está pausado.
        $deudoras = Inscripcion::queDeben()->with(['cliente', 'membresia'])->orderBy('id')->get()->filter($conCorreo)->take(6);
        foreach ($deudoras as $inscripcion) {
            $dia = $this->hoy->subDays($this->azar(1, 10));
            if ($dia->gte(CarbonImmutable::parse($inscripcion->created_at)->startOfDay()->addDay())) {
                $this->programarYEnviar($servicio, $tipo(TipoNotificacion::PAGO_PENDIENTE), $inscripcion, $dia->setTime(1, 15), $dia->setTime(8, 30));
            }
        }

        foreach (Inscripcion::with(['cliente', 'membresia'])->where('id_estado', 101)->get()->filter($conCorreo) as $inscripcion) {
            $momento = CarbonImmutable::parse($inscripcion->fecha_pausa_inicio->format('Y-m-d'))->setTime(19, 30)->min($this->ahora);
            $this->programarYEnviar($servicio, $tipo(TipoNotificacion::PAUSA_INSCRIPCION), $inscripcion, $momento, $momento);
        }

        $this->correosAMano($tipo);
    }

    private function programarYEnviar(NotificacionService $servicio, ?TipoNotificacion $tipo, Inscripcion $inscripcion, CarbonImmutable $programado, CarbonImmutable $enviado): void
    {
        if (! $tipo) {
            return;
        }

        $aviso = $this->enElMomento($programado, fn () => $servicio->crearNotificacion($tipo, $inscripcion));

        $this->enElMomento($enviado->max($programado), function () use ($aviso) {
            $aviso->update(['intentos' => 1]);
            $aviso->marcarComoEnviada();
        });
    }

    /**
     * Lo que se escribe a mano desde el panel: un aviso a un grupo, ya
     * enviado, y una promoción que se canceló antes de salir.
     *
     * NINGUNO queda pendiente ni con reintentos: los manuales salen aunque
     * los automáticos estén apagados.
     */
    private function correosAMano(callable $tipo): void
    {
        $horario = $tipo('horario_especial');
        $promocion = $tipo('promocion');
        $dia = $this->hoy->subDays(9);
        $nota = 'Este sábado cerramos a las 13:00 por mantención de las máquinas de cardio.';

        $grupo = Cliente::where('activo', true)->whereNotNull('email')->where('es_menor_edad', false)
            ->where('created_at', '<', $dia)->orderBy('id')->limit(12)->get();

        foreach ($grupo as $n => $socio) {
            $this->enElMomento($dia->setTime(11, 0)->addSeconds($n * 3), function () use ($horario, $socio, $nota) {
                $correo = $horario->renderizar([
                    'nombre' => trim("{$socio->nombres} {$socio->apellido_paterno}"),
                    'nombre_cliente' => $socio->nombre_completo,
                    'membresia' => '',
                    'fecha_vencimiento' => '',
                    'es_menor_edad' => 'false',
                ]);

                $aviso = Notificacion::create([
                    'id_tipo_notificacion' => $horario->id,
                    'id_cliente' => $socio->id,
                    'email_destino' => $socio->email,
                    'asunto' => $correo['asunto'],
                    'contenido' => $correo['contenido'],
                    'id_estado' => Notificacion::ESTADO_PENDIENTE,
                    'fecha_programada' => today(),
                    'tipo_envio' => 'manual',
                    'enviado_por_user_id' => $this->admin->id,
                    'nota_personalizada' => $nota,
                ]);
                $aviso->registrarLog('programada', 'Envío a un grupo por ' . $this->admin->name);
                $aviso->update(['intentos' => 1]);
                $aviso->marcarComoEnviada();
            });
        }

        $socio = $grupo->first();

        if ($promocion && $socio) {
            $this->enElMomento($this->hoy->subDays(3)->setTime(17, 40), function () use ($promocion, $socio) {
                $correo = $promocion->renderizar(['nombre' => trim("{$socio->nombres} {$socio->apellido_paterno}"), 'membresia' => '', 'fecha_vencimiento' => '']);
                $aviso = Notificacion::create([
                    'id_tipo_notificacion' => $promocion->id,
                    'id_cliente' => $socio->id,
                    'email_destino' => $socio->email,
                    'asunto' => $correo['asunto'],
                    'contenido' => $correo['contenido'],
                    'id_estado' => Notificacion::ESTADO_PENDIENTE,
                    'fecha_programada' => today()->addDays(2),
                    'tipo_envio' => 'manual',
                    'enviado_por_user_id' => $this->recepcion->id,
                    'nota_personalizada' => 'Trae a un amigo en octubre y su primer mes sale a mitad de precio.',
                ]);
                $aviso->registrarLog('programada', 'Programada por ' . $this->recepcion->name);
                $aviso->cancelar('Cancelada manualmente: la promoción se cambió.');
            });
        }
    }

    // =====================================================================
    // El mesón: fiados y libreta
    // =====================================================================

    /**
     * Lo fiado en el mesón y la libreta de notas, como FiadoController y
     * NotaController: deudas abiertas, cobros con su medio, un abono que
     * parte una línea, una línea quitada, un cobro deshecho y una cuenta que
     * pasó de un nombre suelto a un socio.
     */
    private function meson(array $aBorrar, array $aLaPapelera): void
    {
        $fuera = [...$aBorrar, ...$aLaPapelera];
        $activos = Cliente::where('activo', true)->whereNotIn('id', $fuera)->whereIn('id', $this->papeles['cadena'])->orderBy('id')->pluck('id')->all();
        $socio = fn (int $n) => (int) $activos[$n % count($activos)];
        $fiar = fn (?int $idCliente, ?string $nombre, ?string $celular, string $concepto, int $monto, CarbonImmutable $cuando, ?User $quien = null) => $this->enElMomento($cuando, fn () => Fiado::create([
            'id_cliente' => $idCliente,
            'nombre' => $idCliente ? null : $nombre,
            'celular' => $idCliente ? null : $celular,
            'concepto' => $concepto,
            'monto' => $monto,
            'id_usuario' => ($quien ?? $this->quien())->id,
        ]));

        $momento = fn (int $dias, int $hora = 18) => $this->momento($this->hoy->subDays($dias), $hora, $hora + 2);

        // 1. Debe de esta semana.
        $a = $socio(3);
        $fiar($a, null, null, 'Barra de proteína', 2500, $momento(6));
        $fiar($a, null, null, 'Batido de proteína', 3500, $momento(4));
        $fiar($a, null, null, 'Agua mineral 500 cc', 1000, $momento(1));
        // Una línea apuntada por error, quitada (queda quién y qué).
        $error = $fiar($a, null, null, 'Bebida isotónica', 1800, $momento(4));
        $this->enElMomento($momento(4, 19)->addMinutes(5)->min($this->ahora), function () use ($error) {
            $error->update(['id_usuario_quito' => $this->recepcion->id]);
            $error->delete();
            FiadoRegistro::anotar('quitado', $error, $error->concepto . ' (anotado el ' . $error->created_at->format('d/m/Y') . ')', (int) $error->monto, $this->recepcion->id);
        });

        // 2. Una cuenta vieja (se marca para insistir) con un cobro que se deshizo.
        $b = $socio(11);
        $shaker = $fiar($b, null, null, 'Shaker', 6000, $momento(24));
        $batido = $fiar($b, null, null, 'Batido de proteína', 3500, $momento(20));
        $cobro = $momento(17);
        $this->enElMomento($cobro, fn () => Fiado::whereKey([$shaker->id, $batido->id])->update([
            'pagado' => true, 'pagado_en' => now(), 'id_usuario_cobro' => $this->recepcion->id, 'id_metodo_pago' => $this->metodos['efectivo'],
        ]));
        $this->enElMomento($cobro->addMinutes(10), function () use ($shaker, $batido, $cobro) {
            Fiado::whereKey([$shaker->id, $batido->id])->update(['pagado' => false, 'pagado_en' => null, 'id_usuario_cobro' => null, 'id_metodo_pago' => null]);
            FiadoRegistro::anotar('reabierto', $shaker->fresh(), 'Cobro del ' . $cobro->format('d/m/Y H:i') . ': Shaker, Batido de proteína', 9500, $this->admin->id);
        });

        // 3. Visitas sin ficha: nombre suelto, una con celular para recordarle.
        $fiar(null, 'Javiera (amiga de una socia)', '+56987451203', 'Bebida isotónica', 1800, $momento(5));
        $fiar(null, 'Javiera (amiga de una socia)', '+56987451203', 'Arriendo de toalla', 1000, $momento(5));
        $fiar(null, 'Don Hernán (el del agua)', null, 'Barra de proteína', 2500, $momento(2));

        // 4. Un abono: trae $4.000 y debe $7.000. Se paga lo más viejo primero
        // y la línea a la que no le alcanza se parte en lo pagado y lo que queda.
        $c = $socio(19);
        $barra = $fiar($c, null, null, 'Barra de proteína', 2500, $momento(12));
        $batidoC = $fiar($c, null, null, 'Batido de proteína', 3500, $momento(9));
        $fiar($c, null, null, 'Agua mineral 500 cc', 1000, $momento(5));
        $this->enElMomento($momento(3, 19), function () use ($barra, $batidoC) {
            $pagar = fn (Fiado $f) => $f->update(['pagado' => true, 'pagado_en' => now(), 'id_usuario_cobro' => $this->recepcion->id, 'id_metodo_pago' => $this->metodos['efectivo']]);
            $pagar($barra);
            $parte = $batidoC->replicate(['uuid', 'pagado', 'pagado_en', 'id_usuario_cobro', 'id_metodo_pago']);
            $parte->monto = 1500;
            $parte->concepto = mb_substr($batidoC->concepto . ' (abono)', 0, 120);
            $parte->created_at = $batidoC->created_at;
            $parte->save();
            $pagar($parte);
            $batidoC->update(['monto' => $batidoC->monto - 1500]);
        });

        // 5. Cuentas saldadas: una en dos cobros (uno de hoy) y una visita.
        $d = $socio(27);
        $lineas = [
            $fiar($d, null, null, 'Creatina (dosis)', 1500, $momento(15)),
            $fiar($d, null, null, 'Creatina (dosis)', 1500, $momento(13)),
            $fiar($d, null, null, 'Candado para casillero', 4500, $momento(12)),
        ];
        $this->enElMomento($momento(10, 19), fn () => Fiado::whereKey(collect($lineas)->pluck('id'))->update([
            'pagado' => true, 'pagado_en' => now(), 'id_usuario_cobro' => $this->admin->id, 'id_metodo_pago' => $this->metodos['transferencia'],
        ]));
        $hoyMismo = $fiar($d, null, null, 'Batido de proteína', 3500, $this->momento($this->hoy, 7, 9));
        $this->enElMomento($this->momento($this->hoy, 9, 11)->max(CarbonImmutable::parse($hoyMismo->created_at)), fn () => $hoyMismo->update([
            'pagado' => true, 'pagado_en' => now(), 'id_usuario_cobro' => $this->recepcion->id, 'id_metodo_pago' => $this->metodos['tarjeta'],
        ]));

        $visita = $fiar(null, 'Matías (pase diario)', '+56976543210', 'Agua mineral 500 cc', 1000, $momento(8));
        $this->enElMomento($momento(8, 20), fn () => $visita->update([
            'pagado' => true, 'pagado_en' => now(), 'id_usuario_cobro' => $this->recepcion->id, 'id_metodo_pago' => $this->metodos['efectivo'],
        ]));

        // 6. Una visita que después se inscribió: su cuenta pasó a su ficha.
        $e = $socio(35);
        $l1 = $fiar(null, 'Rodrigo (visita)', '+56965432109', 'Bebida isotónica', 1800, $momento(22));
        $l2 = $fiar(null, 'Rodrigo (visita)', '+56965432109', 'Barra de proteína', 2500, $momento(21));
        $this->enElMomento($momento(21, 20), fn () => $l1->update([
            'pagado' => true, 'pagado_en' => now(), 'id_usuario_cobro' => $this->recepcion->id, 'id_metodo_pago' => $this->metodos['efectivo'],
        ]));
        $this->enElMomento($momento(2, 12), function () use ($l1, $l2, $e) {
            $receptor = Cliente::find($e);
            FiadoRegistro::anotar('asignado', $l1->fresh(), 'Pasó a la ficha de ' . trim("{$receptor->nombres} {$receptor->apellido_paterno}") . ' (2 cosas)', 2500, $this->admin->id);
            Fiado::whereKey([$l1->id, $l2->id])->update(['id_cliente' => $e, 'nombre' => null, 'celular' => null]);
        });

        // La libreta del mesón.
        $notas = [
            [0, false, 'Llamar al proveedor del agua: el bidón del segundo piso está vacío.', null],
            [1, false, 'La trotadora 3 suena raro en la correa. Avisar al técnico.', null],
            [5, false, 'Pedir cotización de discos olímpicos de 20 kg (faltan dos).', null],
            [9, false, 'Revisar si la llave del camarín de mujeres quedó con la recepcionista de la tarde.', null],
            [1, true, 'Cambiar la ampolleta del camarín de hombres.', 0],
            [0, true, 'Confirmar con el profe de judo la clase del sábado.', 0],
            [30, true, 'Imprimir el horario nuevo y pegarlo en la entrada.', 28],
            [45, true, 'Comprar alcohol gel para la entrada.', 44],
        ];

        foreach ($notas as [$hace, $hecha, $texto, $hechaHace]) {
            $autor = $this->quien();
            $nota = $this->enElMomento($this->momento($this->hoy->subDays($hace), 8, 12), fn () => Nota::create(['texto' => $texto, 'id_usuario' => $autor->id]));

            if ($hecha) {
                $cuando = $this->momento($this->hoy->subDays($hechaHace), 12, 20)->max(CarbonImmutable::parse($nota->created_at)->addMinutes(5));
                $this->enElMomento($cuando, fn () => $nota->update(['hecha' => true, 'hecha_en' => now(), 'id_usuario_hecha' => $this->recepcion->id]));
            }
        }
    }

    /** Huéspedes del hotel que entran por canje. */
    private function canjes(): void
    {
        $hotel = $this->convenios['Hotel del Centro'];
        $huespedes = ['Andrés Valdivia', 'Carla Montenegro', 'Pedro Aguilera', 'Sofía Lagos', 'Thomas Becker', 'Lucía Fernández', 'Mario Rossi', 'Ana Sepúlveda', 'Jorge Oyarzún', 'Emily Clark', 'Raúl Inostroza', 'Paula Cifuentes'];

        foreach (range(0, 17) as $n) {
            $dia = $n < 2 ? $this->hoy : $this->hoy->subDays($this->azar(1, 45));
            $this->enElMomento($this->momento($dia, 7, 20), fn () => EntradaCanje::create([
                'id_convenio' => $hotel->id,
                'nombre' => $huespedes[$n % count($huespedes)],
                'tarjeta' => 'Hab. ' . $this->azar(1, 4) . '0' . $this->azar(1, 9),
                'id_usuario' => $this->recepcion->id,
            ]));
        }
    }

    // =====================================================================
    // Talleres y arriendos
    // =====================================================================

    /**
     * Dos instituciones que arriendan la sala, como TallerController: las
     * clases del horario se anotan, el mes se cierra con su cuenta (horas por
     * precio, neto e IVA), se le pone folio y se marca pagado. Y sus
     * cotizaciones, con rehacerLaCuenta() como las arma la pantalla.
     */
    private function talleres(): void
    {
        $club = Institucion::create([
            'nombre' => 'Club Deportivo Los Notros',
            'rut' => $this->rutCon(65143872),
            'giro' => 'Club deportivo',
            'direccion' => 'Av. Las Industrias 1250',
            'comuna' => 'Los Ángeles',
            'contacto_nombre' => 'Patricio Mella',
            'contacto_email' => 'patricio.mella@example.com',
            'contacto_telefono' => '+56 9 6123 4587',
            'activo' => true,
        ]);

        $colegio = Institucion::create([
            'nombre' => 'Colegio Alto Biobío',
            'rut' => $this->rutCon(71983402),
            'giro' => 'Educación',
            'direccion' => 'Camino a Antuco km 2',
            'comuna' => 'Los Ángeles',
            'contacto_nombre' => 'Marcela Vidal (UTP)',
            'contacto_email' => 'utp.altobiobio@example.com',
            'contacto_telefono' => '+56 9 7345 1290',
            'observaciones' => 'Facturar a nombre de la fundación sostenedora.',
            'activo' => true,
        ]);

        $judo = Taller::create([
            'id_institucion' => $club->id,
            'nombre' => 'Judo infantil Los Notros',
            'descripcion_factura' => 'Arriendo de sala para clases de judo infantil',
            'precio_hora' => 18000,
            'horario' => ['martes' => [['17:00', '18:30']], 'jueves' => [['17:00', '18:30']]],
            'activo' => true,
        ]);

        $acondicionamiento = Taller::create([
            'id_institucion' => $colegio->id,
            'nombre' => 'Acondicionamiento físico 3° medio',
            'descripcion_factura' => 'Uso de instalaciones deportivas para clase de educación física',
            'precio_hora' => 22000,
            'horario' => ['lunes' => [['15:00', '16:30']], 'miercoles' => [['15:00', '16:30']]],
            'activo' => true,
        ]);

        // [taller, meses atrás, estado del mes]
        $meses = [
            [$judo, 4, 'pagado'], [$judo, 3, 'pagado'], [$judo, 2, 'pagado'], [$judo, 1, 'emitido'], [$judo, 0, 'abierto'],
            [$acondicionamiento, 2, 'pagado'], [$acondicionamiento, 1, 'cerrado'], [$acondicionamiento, 0, 'abierto'],
        ];
        $folio = 1182;

        foreach ($meses as [$taller, $atras, $estado]) {
            $mes = Carbon::parse($this->hoy->startOfMonth()->subMonths($atras)->format('Y-m-d'));
            $clases = collect($taller->clasesDe($mes))->filter(fn ($c) => $c['fecha'] <= $this->hoy->format('Y-m-d'))->values();

            // Una clase de cada mes cerrado no se hizo (feriado o suspensión) y se quitó.
            if ($estado !== 'abierto' && $clases->count() > 2) {
                $clases->forget($this->azar(0, $clases->count() - 1));
            }

            $horas = [];
            foreach ($clases as $clase) {
                $dia = CarbonImmutable::parse($clase['fecha']);
                $horas[] = $this->enElMomento($this->momento($dia, 19, 20), fn () => HoraTaller::create([
                    'id_taller' => $taller->id,
                    'fecha' => $clase['fecha'],
                    'horas' => $clase['horas'],
                    'detalle' => $clase['detalle'],
                    'id_usuario' => $this->quien()->id,
                ]));
            }

            if ($estado === 'abierto' || $horas === []) {
                continue;
            }

            $finDeMes = CarbonImmutable::parse($mes->format('Y-m-d'))->endOfMonth()->startOfDay();
            $cierre = $this->momento($finDeMes->addDays($this->azar(1, 3))->min($this->hoy), 10, 12);

            $this->enElMomento($cierre, function () use ($taller, $mes, $horas, $estado, &$folio, $cierre) {
                $suma = collect($horas)->sum('horas');
                $total = (int) round($suma * $taller->precio_hora);
                $desglose = CobroTaller::desglosar($total);

                $cobro = CobroTaller::create([
                    'id_taller' => $taller->id,
                    'periodo' => $mes->format('Y-m'),
                    'horas' => $suma,
                    'precio_hora' => $taller->precio_hora,
                    'total' => $total,
                    'neto' => $desglose['neto'],
                    'iva' => $desglose['iva'],
                    'id_usuario' => $this->admin->id,
                ]);
                HoraTaller::whereIn('id', collect($horas)->pluck('id'))->update(['id_cobro' => $cobro->id]);

                if ($estado !== 'cerrado') {
                    $pagado = $cierre->addDays($this->azar(6, 15));
                    $cobro->update([
                        'folio' => (string) $folio++,
                        'emitido_en' => $cierre->format('Y-m-d'),
                        'pagado_en' => $estado === 'pagado' && $pagado->lte($this->hoy) ? $pagado->format('Y-m-d') : null,
                        'observaciones' => $estado === 'pagado' ? 'Pagado por transferencia.' : null,
                    ]);
                }
            });
        }

        // Cotizaciones: la del mes que viene, enviada; una aceptada; una vieja rechazada.
        $cotizaciones = [
            [$judo, 1, 3, 'enviada', 'Incluye las clases de la semana de vacaciones de invierno si el club las confirma.'],
            [$acondicionamiento, 0, 12, 'aceptada', null],
            [$acondicionamiento, -2, 70, 'rechazada', 'El colegio prefirió hacer la clase en su gimnasio ese mes.'],
        ];

        foreach ($cotizaciones as [$taller, $adelante, $hace, $estado, $notas]) {
            $mes = Carbon::parse($this->hoy->startOfMonth()->addMonths($adelante)->format('Y-m-d'));
            $dia = $this->hoy->subDays($hace);

            $this->enElMomento($this->momento($dia, 10, 13), function () use ($taller, $mes, $estado, $notas) {
                $cotizacion = new CotizacionTaller([
                    'id_taller' => $taller->id,
                    'numero' => CotizacionTaller::siguienteNumero(),
                    'periodo' => $mes->format('Y-m'),
                    'fecha' => today(),
                    'valido_hasta' => today()->addMonth(),
                    'descripcion' => $taller->descripcion_factura ?: $taller->nombre,
                    'precio_hora' => $taller->precio_hora,
                    'estado' => $estado,
                    'notas' => $notas,
                    'id_usuario' => $this->admin->id,
                ]);

                // Una clase quitada a mano (cae en feriado): se ve tachada y no suma.
                $lineas = CotizacionTaller::clasesParaCotizar($taller, $mes);
                if (count($lineas) > 2) {
                    $lineas[1]['incluida'] = false;
                }

                $cotizacion->rehacerLaCuenta($lineas);
                $cotizacion->save();
            });
        }
    }

    // =====================================================================
    // La web: clases, especialistas, contenidos y rutinas
    // =====================================================================

    private function web(): void
    {
        $clases = [
            ['Judo', 'Sensei Rodrigo Carrasco', 'Desde los 12 años, todos los niveles', 25000, 'azul', [['lunes', '19:00', '20:30'], ['miercoles', '19:00', '20:30']], 'Técnica, caídas y randori. El primer mes se presta judogi.'],
            ['Lucha olímpica', 'Profesora Daniela Riquelme', 'Jóvenes y adultos', 25000, 'rojo', [['martes', '19:00', '20:30'], ['jueves', '19:00', '20:30']], 'Lucha libre olímpica con preparación física específica.'],
            ['Boxeo', 'Coach Felipe Navarrete', 'Adultos, sin experiencia previa', 22000, 'naranjo', [['lunes', '20:30', '21:30'], ['miercoles', '20:30', '21:30'], ['viernes', '19:30', '20:30']], 'Técnica de golpes, sombra y saco. Trae tus vendas.'],
            ['Entrenamiento funcional', 'Equipo PRO GYM', 'Socios de la sala', null, 'verde', [['sabado', '10:00', '11:00']], 'Circuito en grupo. Incluido en la membresía.'],
        ];

        foreach ($clases as $n => [$nombre, $profesor, $paraQuien, $precio, $color, $bloques, $descripcion]) {
            Clase::create([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'profesor' => $profesor,
                'para_quien' => $paraQuien,
                'precio_mensual' => $precio,
                'color' => $color,
                'horario' => array_map(fn ($b) => ['dia' => $b[0], 'desde' => $b[1], 'hasta' => $b[2]], $bloques),
                'activo' => true,
                'orden' => $n + 1,
            ]);
        }

        $especialistas = [
            ['especialista', 'Fernanda Castillo Vera', 'Nutricionista', 'Planes de alimentación para bajar grasa o ganar masa muscular, con control de composición corporal.', ['Pérdida de grasa', 'Nutrición deportiva', 'Planes de alimentación'], 'ambas', 'fernanda.nutri.demo'],
            ['especialista', 'Diego Saavedra Muñoz', 'Kinesiólogo', 'Rehabilitación de lesiones deportivas y prevención para quien vuelve a entrenar.', ['Lesiones de rodilla', 'Dolor lumbar', 'Vuelta al entrenamiento'], 'presencial', 'kine.diego.demo'],
            ['especialista', 'Valentina Orellana Paz', 'Psicóloga deportiva', 'Motivación, ansiedad competitiva y hábitos.', ['Motivación', 'Ansiedad competitiva'], 'online', null],
            ['embajador', 'Martina Fuentes Toro', 'Atleta de powerlifting', 'Campeona regional de powerlifting, entrena en PRO GYM desde 2023.', null, null, 'martina.lifts.demo'],
            ['embajador', 'Benjamín Toro Cárcamo', 'Judoca seleccionado regional', 'Judoca del Biobío; da la clase de judo de los miércoles cuando el sensei viaja.', null, null, null],
        ];

        foreach ($especialistas as $n => [$tipo, $nombre, $especialidad, $descripcion, $temas, $modalidad, $instagram]) {
            Especialista::create([
                'tipo' => $tipo,
                'nombre' => $nombre,
                'especialidad' => $especialidad,
                'descripcion' => $descripcion,
                'temas' => $temas,
                'modalidad' => $modalidad,
                'whatsapp' => $tipo === 'especialista' ? '569' . $this->celularLibre() : null,
                'instagram' => $instagram,
                'email' => $tipo === 'especialista' ? Str::slug($nombre, '.') . '@example.com' : null,
                'orden' => $n + 1,
                'activo' => true,
            ]);
        }

        $orden = (int) ContenidoWeb::max('orden');
        $contenidos = [
            ['testimonio', 'Camila, socia hace 2 años', 'Llegué sin saber usar ninguna máquina y el equipo me armó la rutina desde el primer día.', true],
            ['testimonio', 'Jorge, 52 años', 'Volví a entrenar después de una operación de rodilla. Me acompañaron con paciencia.', true],
            ['testimonio', 'Ignacia, estudiante', 'Con el convenio de mi instituto sale muy a cuenta, y el horario me calza con las clases.', true],
            ['pregunta', '¿Puedo pausar mi membresía?', 'Sí. Según el plan puedes pausarla una o más veces; los días que te quedaban se guardan y se suman al volver.', false],
            ['pregunta', '¿Hay pase diario?', 'Sí, a $5.000. Se paga en el mesón y sirve para todo el día.', false],
            ['pregunta', '¿Tienen estacionamiento?', 'No propio, pero en Rengo y en Colo Colo hay estacionamientos a menos de una cuadra.', false],
            ['servicio', 'Clases de judo y lucha', 'Disciplinas de combate con profesores de trayectoria, para jóvenes y adultos.', false],
        ];

        foreach ($contenidos as [$tipo, $titulo, $texto, $permiso]) {
            ContenidoWeb::create([
                'tipo' => $tipo,
                'titulo' => $titulo,
                'texto' => $texto,
                'icono' => $tipo === 'servicio' ? 'medal' : null,
                'con_permiso' => $permiso,
                'orden' => ++$orden,
                'activo' => true,
            ]);
        }

        RutinasDeEjemplo::cargar();
    }

    // =====================================================================
    // Personas
    // =====================================================================

    private const NOMBRES_MUJER = ['Camila', 'Valentina', 'Javiera', 'Catalina', 'Fernanda', 'Constanza', 'Francisca', 'Daniela', 'Antonia', 'Sofía', 'Isidora', 'Martina', 'Paula', 'Carolina', 'Andrea', 'Macarena', 'Bárbara', 'Josefa', 'Trinidad', 'Florencia', 'María José', 'Ignacia', 'Karina', 'Natalia', 'Pamela', 'Romina', 'Valeria', 'Gabriela', 'Claudia', 'Paz', 'Belén', 'Agustina', 'Rocío', 'Tamara', 'Ximena', 'Lorena', 'Verónica', 'Marcela', 'Daniela Paz', 'Ana María'];

    private const NOMBRES_HOMBRE = ['Matías', 'Benjamín', 'Sebastián', 'Nicolás', 'Diego', 'Felipe', 'Joaquín', 'Vicente', 'Tomás', 'Cristóbal', 'Ignacio', 'Martín', 'Francisco', 'Javier', 'Gonzalo', 'Rodrigo', 'Pablo', 'Maximiliano', 'Bastián', 'Agustín', 'Lucas', 'Juan Pablo', 'José Tomás', 'Esteban', 'Claudio', 'Mauricio', 'Patricio', 'Alejandro', 'Daniel', 'Andrés', 'Camilo', 'Fabián', 'Hernán', 'Marcelo', 'Cristian', 'Renato', 'Álvaro', 'Gaspar', 'Leonardo', 'Luis Alberto'];

    private const APELLIDOS = ['González', 'Muñoz', 'Rojas', 'Díaz', 'Pérez', 'Soto', 'Contreras', 'Silva', 'Martínez', 'Sepúlveda', 'Morales', 'Rodríguez', 'López', 'Fuentes', 'Hernández', 'Torres', 'Araya', 'Flores', 'Espinoza', 'Valenzuela', 'Castillo', 'Tapia', 'Reyes', 'Gutiérrez', 'Castro', 'Pizarro', 'Álvarez', 'Vásquez', 'Sánchez', 'Fernández', 'Ramírez', 'Carrasco', 'Gómez', 'Cortés', 'Herrera', 'Núñez', 'Jara', 'Vergara', 'Rivera', 'Figueroa', 'Riquelme', 'García', 'Miranda', 'Bravo', 'Vera', 'Molina', 'Vega', 'Campos', 'Sandoval', 'Orellana', 'Cárcamo', 'Saavedra', 'Navarro', 'Medina', 'Bustos', 'Parra', 'Salazar', 'Henríquez', 'Mella', 'Arriagada', 'Lagos', 'Toledo', 'Ulloa', 'Burgos', 'Cifuentes', 'Garrido', 'Alarcón', 'Leiva', 'Valdés', 'Ortiz', 'Venegas', 'Poblete', 'Beltrán', 'Monsalve'];

    private const CALLES = ['Colo Colo', 'Caupolicán', 'Lautaro', 'Almagro', 'Valdivia', 'Villagrán', 'Ercilla', 'Av. Alemania', 'Av. Ricardo Vicuña', 'Rengo', 'Tucapel', 'Mendoza', 'Galvarino', 'Orompello', 'Av. Sor Vicenta', 'Lord Cochrane', 'Janequeo', 'Saavedra', 'Bulnes', 'Av. Oriente'];

    private const VILLAS = ['Villa Galilea', 'Villa Los Profesores', 'Población Orompello', 'Villa Las Araucarias', 'Villa Parque Lauquén', 'Villa Los Ríos'];

    /**
     * Un socio como lo deja el alta: RUT con su dígito verificador, celular
     * chileno, correo que no llega a nadie, dirección en Los Ángeles. El menor,
     * con su apoderado completo y la autorización marcada.
     */
    private function crearSocio(CarbonImmutable $alta, array $perfil, ?CarbonImmutable $momento = null): Cliente
    {
        $menor = (bool) ($perfil['menor'] ?? false);
        $mujer = $this->probabilidad($menor ? 0.5 : 0.48);
        [$nombres, $paterno, $materno] = $this->nombreLibre($mujer);

        $tramo = match (true) {
            $menor => '15-17',
            in_array($perfil['convenio'] ?? null, ['INACAP', 'DUOC UC'], true) => '19-26',
            default => $this->elegirConPeso(['19-25' => 30, '26-35' => 34, '36-48' => 24, '49-64' => 12]),
        };
        [$menos, $mas] = array_map('intval', explode('-', $tramo));
        $edadHoy = $this->azar($menos, $mas);

        // Que al inscribirse tuviera la edad que pide el alta (14 o más) y,
        // si es menor, que lo siga siendo hoy.
        $nacimiento = $this->hoy->subYears($edadHoy)->subDays($this->azar(30, 330));
        if ($nacimiento->addYears(14)->gt($alta)) {
            $nacimiento = $alta->subYears(14)->subDays($this->azar(10, 200));
        }

        $extranjero = (bool) ($perfil['extranjero'] ?? false);
        $conCorreo = $menor ? $this->probabilidad(0.4) : $this->probabilidad(0.86);

        $datos = [
            'run_pasaporte' => $extranjero ? 'VE' . $this->azar(1000000, 9999999) : $this->rutPara((int) $nacimiento->format('Y')),
            'nombres' => $nombres,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'celular' => $extranjero ? '+58 412 ' . $this->azar(100, 999) . ' ' . $this->azar(1000, 9999) : $this->celular(),
            'email' => $conCorreo ? $this->correo($nombres, $paterno) : null,
            'direccion' => $this->probabilidad(0.82) ? $this->direccion() : null,
            'fecha_nacimiento' => ($menor || $this->probabilidad(0.9)) ? $nacimiento->format('Y-m-d') : null,
            'observaciones' => $extranjero
                ? 'Venezolano, con pasaporte. Su cédula chilena está en trámite.'
                : $this->quizas(0.18, [
                    'Lesión antigua de rodilla derecha: evitar sentadilla profunda.',
                    'Hipertensión controlada; trae su propio medidor.',
                    'Prefiere que le escriban por WhatsApp.',
                    'Entrena con su pareja en la tarde.',
                    'Viene después del turno en el hospital, casi siempre a las 20:00.',
                    'Asmático: deja su inhalador en el casillero.',
                    'Pide boleta a nombre de su empresa.',
                ]),
            'activo' => true,
            'es_menor_edad' => $menor,
        ];

        if (isset($perfil['convenio'])) {
            $datos['id_convenio'] = $this->convenios[$perfil['convenio']]->id;
        }

        if ($menor) {
            $padreMujer = $this->probabilidad(0.6);
            $nombreApoderado = $this->elegir($padreMujer ? self::NOMBRES_MUJER : self::NOMBRES_HOMBRE);
            $maternoApoderado = $this->elegir(self::APELLIDOS);
            $telefono = '+56 9 ' . substr($this->celularLibre(), 1, 4) . ' ' . substr($this->celularLibre(), 5, 4);
            $datos += [
                'consentimiento_apoderado' => true,
                'apoderado_nombre' => "{$nombreApoderado} {$paterno} {$maternoApoderado}",
                'apoderado_rut' => $this->rutPara((int) $nacimiento->subYears($this->azar(24, 38))->format('Y')),
                'apoderado_email' => $this->correo($nombreApoderado, $paterno),
                'apoderado_telefono' => $telefono,
                'apoderado_parentesco' => $padreMujer ? $this->elegir(['Madre', 'Madre', 'Abuela', 'Tía']) : $this->elegir(['Padre', 'Padre', 'Tío']),
                'apoderado_observaciones' => $this->quizas(0.5, ['Lo retira a las 19:30.', 'Autoriza que entrene solo en horario de tarde.', 'Avisar a la madre ante cualquier lesión.']),
                'contacto_emergencia' => "{$nombreApoderado} {$paterno}",
                'telefono_emergencia' => $telefono,
            ];
        } elseif ($this->probabilidad(0.6)) {
            $datos['contacto_emergencia'] = $this->elegir([...self::NOMBRES_MUJER, ...self::NOMBRES_HOMBRE]) . ' ' . $this->elegir(self::APELLIDOS);
            $datos['telefono_emergencia'] = '+56 9 ' . substr($this->celularLibre(), 1, 4) . ' ' . substr($this->celularLibre(), 5, 4);
        }

        // El contrato en papel, firmado el día del alta (los digitales vienen después).
        if (! $menor && $this->probabilidad(0.35)) {
            $datos += [
                'contrato_version' => (string) TextosLegales::vigente('contrato')->version,
                'contrato_firmado_en' => $alta->format('Y-m-d'),
                'consentimiento_imagen' => $this->probabilidad(0.6),
                'consentimiento_difusion' => $this->probabilidad(0.3),
            ];
        }

        return $this->enElMomento($momento ?? $this->momento($alta)->subMinutes(5), fn () => Cliente::create($datos));
    }

    /** @return array{0:string,1:string,2:string} */
    private function nombreLibre(bool $mujer): array
    {
        do {
            $nombre = [
                $this->elegir($mujer ? self::NOMBRES_MUJER : self::NOMBRES_HOMBRE),
                $this->elegir(self::APELLIDOS),
                $this->elegir(self::APELLIDOS),
            ];
            $clave = implode('|', $nombre);
        } while ($nombre[1] === $nombre[2] || isset($this->usados['nombre'][$clave]));

        $this->usados['nombre'][$clave] = true;

        return $nombre;
    }

    /** El RUT de quien nació ese año: los números van subiendo con los años. */
    private function rutPara(int $anio): string
    {
        $centro = 8_500_000 + ($anio - 1960) * 300_000;

        do {
            $cuerpo = $this->azar(max(5_000_000, $centro - 450_000), $centro + 450_000);
        } while (isset($this->usados['rut'][$cuerpo]));

        $this->usados['rut'][$cuerpo] = true;

        return $this->rutCon($cuerpo);
    }

    private function rutCon(int $cuerpo): string
    {
        return number_format($cuerpo, 0, '', '.') . '-' . self::digitoVerificador($cuerpo);
    }

    /** Módulo 11, el mismo que valida App\Rules\RutValido. */
    public static function digitoVerificador(int $cuerpo): string
    {
        $suma = 0;
        $factor = 2;

        foreach (array_reverse(str_split((string) $cuerpo)) as $digito) {
            $suma += (int) $digito * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        return match ($dv = 11 - ($suma % 11)) {
            11 => '0',
            10 => 'K',
            default => (string) $dv,
        };
    }

    /** Un celular escrito como lo escriben en el mesón; se guarda en 9 dígitos. */
    private function celular(): string
    {
        $numero = $this->celularLibre();

        return '+56 9 ' . substr($numero, 1, 4) . ' ' . substr($numero, 5, 4);
    }

    private function celularLibre(): string
    {
        do {
            $numero = '9' . str_pad((string) $this->azar(0, 99_999_999), 8, '0', STR_PAD_LEFT);
        } while (isset($this->usados['celular'][$numero]) || $numero[1] === '0');

        $this->usados['celular'][$numero] = true;

        return $numero;
    }

    /** Siempre @example.com: CorreoService no le escribe a ese dominio (RFC 2606). */
    private function correo(string $nombres, string $apellido): string
    {
        $base = Str::slug(explode(' ', $nombres)[0] . ' ' . $apellido, '.');
        $correo = "{$base}@example.com";

        for ($n = 2; isset($this->usados['correo'][$correo]); $n++) {
            $correo = "{$base}{$n}@example.com";
        }

        $this->usados['correo'][$correo] = true;

        return $correo;
    }

    private function direccion(): string
    {
        if ($this->probabilidad(0.25)) {
            return 'Pasaje ' . $this->elegir(['Los Aromos', 'Las Azaleas', 'El Roble', 'Los Canelos', 'Las Camelias']) . ' ' . $this->azar(10, 480)
                . ', ' . $this->elegir(self::VILLAS) . ', Los Ángeles';
        }

        return $this->elegir(self::CALLES) . ' ' . $this->azar(100, 2400)
            . ($this->probabilidad(0.15) ? ', depto. ' . $this->azar(101, 804) : '') . ', Los Ángeles';
    }

    // =====================================================================
    // Planes y medios de pago
    // =====================================================================

    private function primerPlan(array $perfil): Membresia
    {
        if (($perfil['menor'] ?? false) || isset($perfil['convenio'])) {
            return $this->planes[$this->elegirConPeso(['Mensual' => 85, 'Trimestral' => 15])];
        }

        return $this->planes[$this->elegirConPeso(['Mensual' => 62, 'Trimestral' => 18, 'Semestral' => 12, 'Anual' => 8])];
    }

    private function siguientePlan(Membresia $actual, array $perfil): Membresia
    {
        return $this->probabilidad(0.7) ? $actual : $this->primerPlan($perfil);
    }

    private function convenioEducativo(int $idConvenio): bool
    {
        return Convenio::whereKey($idConvenio)->value('tipo') === 'institucion_educativa';
    }

    private function metodoAlAzar(): string
    {
        return $this->elegirConPeso(['efectivo' => 45, 'transferencia' => 33, 'tarjeta' => 22]);
    }

    /** Un cobro con un solo medio; la transferencia casi siempre con su número. */
    private function conMetodo(array $datos): array
    {
        $metodo = $this->metodoAlAzar();

        return $datos + [
            'id_metodo_pago' => $this->metodos[$metodo],
            'referencia_pago' => $metodo === 'transferencia' && $this->probabilidad(0.85) ? $this->referencia() : null,
        ];
    }

    private function referencia(): string
    {
        $this->operacion += $this->azar(7, 900);

        return $this->elegir(['Op. ', 'TEF ', 'Comprobante ']) . $this->operacion;
    }

    /** Un abono redondo: entre el 30 y el 70 % del total, y nunca todo. */
    private function abonoDe(int $total): int
    {
        $abono = (int) (round($total * $this->azar(30, 70) / 100 / 1000) * 1000);

        return max(1000, min($total - 1000, $abono));
    }

    private function saldoDe(Inscripcion $inscripcion): int
    {
        return max(0, (int) $inscripcion->precio_final - (int) $inscripcion->pagos()->sum('monto_abonado'));
    }

    // =====================================================================
    // El reloj y el azar
    // =====================================================================

    /**
     * Ejecuta algo como si fuera ese momento: created_at, fecha_cambio, los
     * registros de los correos y cualquier today() del sistema caen ahí.
     */
    private function enElMomento(CarbonInterface $momento, callable $hacer): mixed
    {
        Carbon::setTestNow(Carbon::parse($momento->min($this->ahora)->format('Y-m-d H:i:s')));

        try {
            return $hacer();
        } finally {
            Carbon::setTestNow($this->relojDeAntes);
        }
    }

    /** Una hora del día en que el gimnasio atiende, nunca más tarde que ahora. */
    private function momento(CarbonInterface $dia, int $desde = 9, int $hasta = 20): CarbonImmutable
    {
        $dia = CarbonImmutable::parse($dia->format('Y-m-d'));

        if ($dia->gt($this->hoy)) {
            throw new \LogicException('Un momento en el futuro: ' . $dia->format('Y-m-d'));
        }

        // Siempre los mismos tres tiros, sea la hora que sea: con la misma
        // semilla, la misma historia.
        $momento = $dia->setTime($this->azar($desde, $hasta), $this->azar(0, 59));
        $antes = $this->azar(1, 20);

        if ($momento->gt($this->ahora)) {
            $momento = $this->ahora->subMinutes($antes)->max($dia);
        }

        return $momento;
    }

    /** Quién atiende: casi siempre recepción. */
    private function quien(): User
    {
        return $this->probabilidad(0.75) ? $this->recepcion : $this->admin;
    }

    private function azar(int $min, int $max): int
    {
        return $max <= $min ? $min : mt_rand($min, $max);
    }

    private function probabilidad(float $p): bool
    {
        return mt_rand() / mt_getrandmax() < $p;
    }

    private function elegir(array $opciones): mixed
    {
        return $opciones[array_keys($opciones)[mt_rand(0, count($opciones) - 1)]];
    }

    private function elegirConPeso(array $pesos): mixed
    {
        $tiro = mt_rand(1, array_sum($pesos));

        foreach ($pesos as $valor => $peso) {
            if (($tiro -= $peso) <= 0) {
                return $valor;
            }
        }

        return array_key_last($pesos);
    }

    private function quizas(float $p, array $opciones): ?string
    {
        return $this->probabilidad($p) ? $this->elegir($opciones) : null;
    }

    private function resumen(): void
    {
        $this->command?->info(sprintf(
            'Demo lista: %d socios (%d activos, %d en la papelera, %d con datos borrados), %d membresías, %d pagos, %d fiados, %d correos, %d contratos.',
            Cliente::withTrashed()->count(),
            Cliente::where('activo', true)->count(),
            Cliente::onlyTrashed()->count(),
            Cliente::whereNotNull('datos_borrados_en')->count(),
            Inscripcion::count(),
            Pago::count(),
            Fiado::withTrashed()->count(),
            Notificacion::count(),
            Contrato::count(),
        ));
        $this->command?->info('Accesos al panel en storage/app/private/' . self::ARCHIVO_ACCESOS . ' (no van al repositorio).');
    }
}
