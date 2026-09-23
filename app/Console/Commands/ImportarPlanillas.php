<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mete en el sistema lo que hay en las planillas de Excel del gimnasio.
 *
 * DE DÓNDE SALE. Durante años el gimnasio se llevó en planillas: una maestra
 * con todas las ventas y una por convenio. Cada fila es UNA VENTA —«Fulano,
 * mensual, $40.000, del 3 de marzo al 3 de abril»—, no una persona: quien
 * renovó doce veces aparece doce veces. Aquí se convierten en socios con su
 * historial.
 *
 * LO QUE SE ARREGLA POR EL CAMINO, porque una planilla escrita a mano durante
 * cuatro años trae de todo:
 *  · RUT sin puntos, con puntos, sin dígito verificador o inventados. El que no
 *    cuadra no se guarda como RUT; la persona entra igual, por su nombre.
 *  · Fechas imposibles —31 de noviembre, 30 de febrero— que Excel guardó como
 *    texto. Se corrigen al último día de ese mes.
 *  · «VENCIDO» escrito donde iba la fecha de término.
 *  · Montos como «150.000(DEBE 100.000)», «100000/2» o «25000/VIP».
 *  · La misma venta repetida en la planilla maestra y en la del convenio.
 *
 * LO QUE NO HACE: inventar. Si una fila no dice cuándo empieza, no se crea
 * ninguna membresía por ella —a lo más, la persona—; y si el plan no se
 * reconoce, la fila se cuenta aparte y se informa, en vez de meterla en
 * «mensual» y descuadrar el historial.
 *
 * SE PUEDE CORRER DOS VECES. Busca a cada socio por su RUT y cada membresía
 * por su fecha: lo que ya está no se duplica.
 */
class ImportarPlanillas extends Command
{
    protected $signature = 'datos:importar-planillas
        {archivo=storage/app/private/importacion/planillas.csv : El CSV que sale de las planillas}
        {--confirmar : Guardar de verdad; sin esto solo cuenta lo que haría}
        {--con-pagos : Traer también los montos de la planilla como pagos}
                            {--activos-desde=12 : Meses hacia atrás: a quien no renueva desde entonces se le da de baja}';

    protected $description = 'Carga los socios y sus membresías desde las planillas del gimnasio';

    /** Estados de membresía y de pago, por su código. */
    private const ACTIVA = 100;
    private const VENCIDA = 102;
    private const PAGADO = 201;
    private const CLIENTE_ACTIVO = 400;

    /**
     * Cómo se escribió cada plan en la planilla, y qué plan es.
     *
     * Se mira en este orden y gana el primero que aparezca dentro del texto:
     * «mens» tiene que ir después de «semestral» o «Mens/Sem» caería en mensual.
     */
    private const PLANES = [
        'anual' => 'Anual',
        'semestral' => 'Semestral',
        'semestre' => 'Semestral',
        'trimestral' => 'Trimestral',
        'trimestre' => 'Trimestral',
        'trimestr' => 'Trimestral',
        'trimistral' => 'Trimestral',
        'tres meses' => 'Trimestral',
        '3 meses' => 'Trimestral',
        // «Bimestral» y «2 meses» son lo mismo, y el gimnasio los cobró a
        // $50.000: por eso Dos meses es un plan y no un trimestral barato.
        'bimestral' => 'Dos meses',
        '2 meses' => 'Dos meses',
        '2meses' => 'Dos meses',
        'dos meses' => 'Dos meses',
        // Medio mes, escrito de las cinco maneras en que aparece.
        '1/2 mes' => 'Quincena',
        '!/2 mes' => 'Quincena',
        '15 dias' => 'Quincena',
        '15dias' => 'Quincena',
        'quincena' => 'Quincena',
        'semanal' => 'Semana',
        'semana' => 'Semana',
        'mensual' => 'Mensual',
        'mensal' => 'Mensual',
        // Tecleados de corrido en la planilla: «mesnsual», «menasual».
        'mesnsual' => 'Mensual',
        'menasual' => 'Mensual',
        '1 mes' => 'Mensual',
        '1mes' => 'Mensual',
        'mens' => 'Mensual',
        // La tarifa de adulto mayor es una mensualidad más barata, no otra
        // duración: el plan es mensual y el precio lo pone su convenio.
        'adulto mayor' => 'Mensual',
        'diario' => 'Pase Diario',
        'pase' => 'Pase Diario',
        // «Convenio» a secas, sin decir cuánto dura, es la mensualidad con
        // precio de convenio: así se cobró siempre.
        'convenio' => 'Mensual',
        'conv' => 'Mensual',
    ];

    /** @var array<string,int> */
    private array $planes = [];

    /** @var array<string,int> */
    private array $convenios = [];

    private int $metodoPago = 0;

    public function handle(): int
    {
        $ruta = base_path($this->argument('archivo'));

        if (! is_file($ruta)) {
            $this->error("No encuentro el archivo: {$ruta}");

            return self::FAILURE;
        }

        $filas = $this->leer($ruta);
        $this->info(count($filas).' filas en la planilla.');

        $this->planes = Membresia::pluck('id', 'nombre')->all();
        $this->convenios = Convenio::pluck('id', 'nombre')->all();
        // «Sin registrar» y NO «Efectivo»: la planilla nunca dijo cómo pagó
        // cada socio, y darlo por efectivo hacía que el informe de ingresos
        // por medio de pago dijera una cifra falsa con toda seguridad.
        $this->metodoPago = (int) (MetodoPago::withoutGlobalScopes()->where('nombre', 'Sin registrar')->value('id')
            ?? MetodoPago::value('id'));

        $gente = $this->agrupar($filas);
        $this->info(count($gente).' personas distintas.');

        $ventas = collect($gente)->sum(fn (array $p) => count($p['ventas']));
        $sinPlan = collect($gente)->sum(fn (array $p) => $p['sin_plan']);
        $sinFecha = collect($gente)->sum(fn (array $p) => $p['sin_fecha']);

        $corte = Carbon::today()->subMonths((int) $this->option('activos-desde'));
        $activos = collect($gente)->filter(fn (array $p) => $p['ultimo_fin'] && $p['ultimo_fin']->gte($corte))->count();
        $vigentes = collect($gente)->filter(fn (array $p) => $p['ultimo_fin'] && $p['ultimo_fin']->gte(Carbon::today()))->count();

        $this->table(['Qué', 'Cuánto'], [
            ['personas', count($gente)],
            ['membresías que se crearían', $ventas],
            ['filas sin plan reconocible', $sinPlan],
            ['filas sin fecha de inicio', $sinFecha],
            ["siguen activos (renovaron desde {$corte->format('m/Y')})", $activos],
            ['con membresía vigente hoy', $vigentes],
            ['convenios nombrados', count($this->conveniosDe($gente))],
        ]);

        if (! $this->option('confirmar')) {
            $this->warn('No se guardó nada. Para hacerlo de verdad: php artisan datos:importar-planillas --confirmar');

            return self::SUCCESS;
        }

        $this->crearConvenios($this->conveniosDe($gente));
        $this->guardar($gente, $corte);

        $this->newLine();
        $this->info('Listo.');
        $this->line('  socios: '.Cliente::count());
        $this->line('  membresías: '.Inscripcion::count());
        $this->line('  pagos: '.Pago::count());

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- leer

    /** @return list<array<string,string>> */
    private function leer(string $ruta): array
    {
        $f = fopen($ruta, 'r');
        $cabecera = fgetcsv($f);
        $filas = [];

        while (($fila = fgetcsv($f)) !== false) {
            if (count($fila) !== count($cabecera)) {
                continue;
            }

            $filas[] = array_combine($cabecera, $fila);
        }

        fclose($f);

        return $filas;
    }

    // ------------------------------------------------------------ agrupar

    /**
     * Junta las filas por persona.
     *
     * LA LLAVE ES EL RUT cuando sirve, y el nombre cuando no. Al revés —por
     * nombre siempre— «Juan Pérez» y «juan perez» serían dos socios, y dos
     * personas distintas con el mismo nombre serían una sola.
     *
     * @param list<array<string,string>> $filas
     * @return array<string,array<string,mixed>>
     */
    private function agrupar(array $filas): array
    {
        $gente = [];

        foreach ($filas as $fila) {
            $nombre = trim($fila['nombre']);

            if ($nombre === '' || is_numeric($nombre)) {
                continue;
            }

            $rut = $this->rut($fila['rut']);
            $llave = $rut ?: 'nombre:'.Str::of($nombre)->lower()->ascii()->replaceMatches('/[^a-z]+/', ' ')->trim();

            $gente[$llave] ??= [
                'rut' => $rut,
                'nombre' => $nombre,
                'celular' => '',
                'convenio' => '',
                'ventas' => [],
                'sin_plan' => 0,
                'sin_fecha' => 0,
                'ultimo_fin' => null,
            ];

            // El nombre más largo gana: «Carlos Torres ponce» dice más que «Carlos».
            if (mb_strlen($nombre) > mb_strlen($gente[$llave]['nombre'])) {
                $gente[$llave]['nombre'] = $nombre;
            }

            if ($fila['celular'] !== '' && $gente[$llave]['celular'] === '') {
                $gente[$llave]['celular'] = $this->celular($fila['celular']);
            }

            $inicio = $this->fecha($fila['inicio']);
            $plan = $this->plan($fila['plan']);
            $convenio = $this->convenio($fila['convenio'], $fila['plan']);

            if ($convenio) {
                $gente[$llave]['convenio'] = $convenio;
            }

            if (! $plan) {
                $gente[$llave]['sin_plan']++;

                continue;
            }

            if (! $inicio) {
                $gente[$llave]['sin_fecha']++;

                continue;
            }

            $fin = $this->fecha($fila['termino']) ?? $this->vence($inicio, $plan);

            // La misma venta apuntada en la maestra y en la del convenio: es
            // una, no dos. Se reconoce por la persona, el plan y el día.
            $huella = $plan.'|'.$inicio->toDateString();

            if (isset($gente[$llave]['ventas'][$huella])) {
                continue;
            }

            $gente[$llave]['ventas'][$huella] = [
                'plan' => $plan,
                'inicio' => $inicio,
                'fin' => $fin,
                'pagado' => $this->monto($fila['monto']),
                'convenio' => $convenio,
                'planilla' => $fila['planilla'],
            ];

            if (! $gente[$llave]['ultimo_fin'] || $fin->gt($gente[$llave]['ultimo_fin'])) {
                $gente[$llave]['ultimo_fin'] = $fin;
            }
        }

        return $gente;
    }

    // -------------------------------------------------------- limpieza

    /** El RUT sin puntos ni guion, y solo si el dígito verificador cuadra. */
    private function rut(string $bruto): string
    {
        $limpio = strtoupper(preg_replace('/[^0-9kK]/', '', $bruto));

        if (! preg_match('/^\d{7,8}[0-9K]$/', $limpio)) {
            return '';
        }

        $cuerpo = substr($limpio, 0, -1);
        $dv = substr($limpio, -1);
        $suma = 0;
        $factor = 2;

        foreach (array_reverse(str_split($cuerpo)) as $digito) {
            $suma += ((int) $digito) * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $resto = 11 - ($suma % 11);
        $esperado = $resto === 11 ? '0' : ($resto === 10 ? 'K' : (string) $resto);

        if ($dv !== $esperado) {
            return '';
        }

        // Con puntos, que es como los escribe el resto del sistema: guardado a
        // secas, el mismo socio entraba dos veces —una por cada forma— en
        // cuanto se corría `clientes:normalizar`.
        return number_format((int) $cuerpo, 0, '', '.').'-'.$dv;
    }

    /**
     * La fecha, aunque venga escrita de seis maneras.
     *
     * LAS IMPOSIBLES SE ARREGLAN, NO SE TIRAN: «31-11-2022» es el 30 de
     * noviembre mal tecleado, y esa membresía existió igual.
     */
    private function fecha(string $bruto): ?Carbon
    {
        $texto = trim($bruto);

        if ($texto === '' || preg_match('/^(vencido|sf|no|nc)$/i', $texto)) {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $texto, $p)) {
            [$a, $m, $d] = [(int) $p[1], (int) $p[2], (int) $p[3]];
        } elseif (preg_match('/^(\d{1,2})-+(\d{1,2})-+(\d{4})$/', $texto, $p)) {
            [$a, $m, $d] = [(int) $p[3], (int) $p[2], (int) $p[1]];
        } elseif (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $texto, $p)) {
            [$a, $m, $d] = [(int) $p[3], (int) $p[2], (int) $p[1]];
        } else {
            return null;
        }

        if ($a < 2015 || $a > 2035 || $m < 1 || $m > 12) {
            return null;
        }

        $ultimo = (int) Carbon::create($a, $m, 1)->endOfMonth()->day;

        return Carbon::create($a, $m, min(max($d, 1), $ultimo))->startOfDay();
    }

    /** Qué plan es, por cómo lo escribieron. */
    private function plan(string $bruto): string
    {
        $texto = Str::of($bruto)->lower()->ascii()->toString();

        foreach (self::PLANES as $pista => $plan) {
            if (str_contains($texto, $pista)) {
                return $plan;
            }
        }

        // «1/2 mes», «15 dias», «semana»: son medias mensualidades y pases, y
        // el gimnasio no tiene plan para eso. Se cuentan aparte.
        return '';
    }

    /** El convenio: el de la planilla, o el que venga escrito en el plan. */
    private function convenio(string $dePlanilla, string $plan): string
    {
        if (trim($dePlanilla) !== '') {
            return trim($dePlanilla);
        }

        $texto = Str::of($plan)->lower()->ascii()->toString();

        foreach ($this->pistasDeConvenio() as $pista => $nombre) {
            if (str_contains($texto, $pista)) {
                return $nombre;
            }
        }

        return '';
    }

    /**
     * Lo que aparece escrito al lado del plan y es un convenio.
     *
     * Sale de leer la planilla: son los que de verdad usó el gimnasio. Lo que
     * no esté aquí —«vip», «pendiente», «cuotas»— no es un convenio.
     *
     * @return array<string,string>
     */
    private function pistasDeConvenio(): array
    {
        return [
            'carabinero' => 'Carabineros',
            'conv car' => 'Carabineros',
            'conv/car' => 'Carabineros',
            'militar' => 'Fuerzas Armadas',
            'ejercito' => 'Fuerzas Armadas',
            'movistar' => 'Movistar',
            'entel' => 'Entel',
            'jormat' => 'Jormat',
            'inacap' => 'INACAP',
            'sto tomas' => 'Santo Tomás',
            'santo tomas' => 'Santo Tomás',
            'aiep' => 'AIEP',
            'ucsc' => 'UCSC',
            'udec' => 'Universidad de Concepción',
            'virginio' => 'IP Virginio Gómez',
            'hispanoamericano' => 'Colegio Hispanoamericano',
            'hites' => 'Hites',
            'ripley' => 'Ripley',
            'cmpc' => 'CMPC',
            'afusam' => 'AFUSAM',
            'promasa' => 'Promasa',
            'adulto mayor' => 'Adulto mayor',
            'estudiante' => 'Estudiantes',
            'alumno' => 'Estudiantes',
            'liceo' => 'Estudiantes',
        ];
    }

    /** Lo que pagó: el primer número que aparezca. */
    private function monto(string $bruto): int
    {
        $texto = str_replace(['.', ' '], '', $bruto);

        if (! preg_match('/\d+/', $texto, $p)) {
            return 0;
        }

        $monto = (int) $p[0];

        // «100000/2» son dos cuotas de un semestral, «2/100000» lo mismo al
        // revés: el número chico es la cuota, no el precio.
        return $monto < 1000 ? 0 : $monto;
    }

    private function celular(string $bruto): string
    {
        $digitos = preg_replace('/\D/', '', $bruto);

        if (str_starts_with($digitos, '56') && strlen($digitos) === 11) {
            $digitos = substr($digitos, 2);
        }

        return strlen($digitos) === 9 ? $digitos : '';
    }

    /** Cuándo vence un plan que empezó tal día, si la planilla no lo dice. */
    private function vence(Carbon $inicio, string $plan): Carbon
    {
        return match ($plan) {
            'Anual' => $inicio->copy()->addYear(),
            'Semestral' => $inicio->copy()->addMonths(6),
            'Trimestral' => $inicio->copy()->addMonths(3),
            'Dos meses' => $inicio->copy()->addMonths(2),
            'Quincena' => $inicio->copy()->addDays(15),
            'Semana' => $inicio->copy()->addDays(7),
            'Pase Diario' => $inicio->copy()->addDay(),
            default => $inicio->copy()->addMonth(),
        };
    }

    // -------------------------------------------------------- guardar

    /**
     * @param array<string,array<string,mixed>> $gente
     * @return list<string>
     */
    private function conveniosDe(array $gente): array
    {
        return collect($gente)
            ->pluck('convenio')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** @param list<string> $nombres */
    private function crearConvenios(array $nombres): void
    {
        foreach ($nombres as $nombre) {
            if (isset($this->convenios[$nombre])) {
                continue;
            }

            $convenio = Convenio::create([
                'uuid' => (string) Str::uuid(),
                'nombre' => $nombre,
                'tipo' => $this->tipoDeConvenio($nombre),
                'descripcion' => 'Venía en las planillas del gimnasio.',
                'id_estado' => 300,
                'activo' => true,
            ]);

            $this->convenios[$nombre] = $convenio->id;
        }
    }

    private function tipoDeConvenio(string $nombre): string
    {
        $texto = Str::of($nombre)->lower()->ascii()->toString();

        return match (true) {
            str_contains($texto, 'universidad') || str_contains($texto, 'inacap')
                || str_contains($texto, 'aiep') || str_contains($texto, 'ucsc')
                || str_contains($texto, 'tomas') || str_contains($texto, 'virginio')
                || str_contains($texto, 'colegio') || str_contains($texto, 'liceo')
                || str_contains($texto, 'estudiante') => 'institucion_educativa',
            str_contains($texto, 'carabineros') || str_contains($texto, 'fuerzas')
                || str_contains($texto, 'afusam') => 'organizacion',
            default => 'empresa',
        };
    }

    /** @param array<string,array<string,mixed>> $gente */
    private function guardar(array $gente, Carbon $corte): void
    {
        $barra = $this->output->createProgressBar(count($gente));
        $barra->start();

        foreach ($gente as $persona) {
            DB::transaction(function () use ($persona, $corte) {
                $cliente = $this->socio($persona, $corte);

                foreach ($persona['ventas'] as $venta) {
                    $this->membresia($cliente, $venta);
                }
            });

            $barra->advance();
        }

        $barra->finish();
        $this->newLine();
    }

    /** @param array<string,mixed> $persona */
    private function socio(array $persona, Carbon $corte): Cliente
    {
        [$nombres, $paterno, $materno] = $this->partirNombre($persona['nombre']);

        // SE BUSCA SIN PUNTOS NI GUION: en la base hay RUT escritos de las dos
        // formas, y comparando el texto tal cual el mismo socio se duplicaba.
        $existente = $persona['rut']
            ? Cliente::whereRaw("REPLACE(REPLACE(run_pasaporte, '.', ''), '-', '') = ?", [
                str_replace(['.', '-'], '', $persona['rut']),
            ])->first()
            // Sin RUT solo queda el nombre, y en minúsculas: la ficha lo
            // guarda capitalizado a su manera y comparar tal cual fallaba.
            : Cliente::whereRaw('LOWER(nombres) = ? AND LOWER(apellido_paterno) = ?', [
                mb_strtolower($nombres),
                mb_strtolower($paterno),
            ])->first();

        /*
         * DADO DE BAJA EL QUE NO RENUEVA HACE UN AÑO. Son cuatro años de
         * planillas: dejarlos a todos activos llenaría el panel de gente que
         * no viene desde 2022, y el mesón buscaría entre novecientas fichas
         * para encontrar a las que vienen hoy. Siguen todos ahí —salen en
         * «dados de baja» y por el buscador— y se reactivan de un clic.
         */
        $activo = $persona['ultimo_fin'] && $persona['ultimo_fin']->gte($corte);

        $datos = [
            'nombres' => $nombres,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'run_pasaporte' => $persona['rut'] ?: null,
            'celular' => $persona['celular'] ?: null,
            'id_convenio' => $persona['convenio'] ? ($this->convenios[$persona['convenio']] ?? null) : null,
            'id_estado' => self::CLIENTE_ACTIVO,
            'activo' => $activo,
            'observaciones' => 'Importado de las planillas del gimnasio.',
        ];

        if ($existente) {
            $existente->update($datos);

            return $existente;
        }

        return Cliente::create($datos + ['uuid' => (string) Str::uuid()]);
    }

    /**
     * El nombre partido en nombres y apellidos.
     *
     * EN LA PLANILLA ES UN SOLO CAMPO y no siempre trae los cuatro pedazos:
     * hay «Carlos», «Carlos Kiss» y «agustina ignacia araneda lil». Con cuatro
     * palabras o más, las dos últimas son los apellidos; con tres, la última es
     * el apellido y las otras el nombre. Es la convención chilena y acierta
     * casi siempre; lo que no, se corrige en la ficha.
     *
     * @return array{0:string,1:string,2:string}
     */
    private function partirNombre(string $completo): array
    {
        $partes = preg_split('/\s+/', trim($completo));
        $partes = array_values(array_filter($partes, fn ($p) => $p !== ''));
        $partes = array_map(fn ($p) => Str::title($p), $partes);

        // «N.N.» cuando la planilla solo anotó un nombre de pila: la columna
        // del apellido no admite vacío, y es la abreviatura que se usa para
        // eso. Se ve tal cual en la ficha, así que quien lo sepa lo corrige.
        return match (count($partes)) {
            0 => ['Sin nombre', 'N.N.', ''],
            1 => [$partes[0], 'N.N.', ''],
            2 => [$partes[0], $partes[1], ''],
            3 => [$partes[0], $partes[1], $partes[2]],
            default => [
                implode(' ', array_slice($partes, 0, count($partes) - 2)),
                $partes[count($partes) - 2],
                $partes[count($partes) - 1],
            ],
        };
    }

    /** @var array<int,array{id:int|null,precio:int}> */
    private array $listaDePrecios = [];

    /**
     * El precio de lista de un plan y la fila de precios que lo dice.
     *
     * La membresía guarda a qué precio se vendió —`id_precio_acordado`—, no
     * solo cuánto: así, cuando los precios suban, las membresías viejas siguen
     * sabiendo con qué lista se vendieron.
     *
     * @return array{id:int|null,precio:int}
     */
    private function precioDeLista(int $idPlan): array
    {
        return $this->listaDePrecios[$idPlan] ??= (function () use ($idPlan) {
            $precio = Membresia::find($idPlan)?->precios()->where('activo', true)->first();

            return ['id' => $precio?->id, 'precio' => (int) round($precio?->precio_normal ?? 0)];
        })();
    }

    /** @param array<string,mixed> $venta */
    private function membresia(Cliente $cliente, array $venta): void
    {
        $idPlan = $this->planes[$venta['plan']] ?? null;

        if (! $idPlan) {
            return;
        }

        $yaEsta = Inscripcion::where('id_cliente', $cliente->id)
            ->where('id_membresia', $idPlan)
            ->whereDate('fecha_inicio', $venta['inicio'])
            ->exists();

        if ($yaEsta) {
            return;
        }

        // Lo que dice la planilla; y si no dijo nada, el precio de lista de
        // ese plan, que es lo más cerca de la verdad que se puede estar.
        $lista = $this->precioDeLista($idPlan);

        /*
         * SIN PRECIO, salvo que se pida.
         *
         * La planilla trae el monto, pero NO con qué se pagó, y una membresía
         * con precio y sin pago sale debiendo su precio entero: mil setecientos
         * morosos falsos. Lo que la planilla dice de verdad y sirve es quién,
         * qué plan y entre qué fechas; la plata de esos años se deja fuera.
         * Con --con-pagos se traen los montos igual, que es como se recupera
         * lo que hubiera quitado `datos:quitar-pagos-importados`.
         */
        $precio = $this->option('con-pagos')
            ? ($venta['pagado'] > 0 ? $venta['pagado'] : $lista['precio'])
            : 0;
        $vigente = $venta['fin']->gte(Carbon::today());

        $inscripcion = Inscripcion::create([
            'uuid' => (string) Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $idPlan,
            'id_convenio' => $venta['convenio'] ? ($this->convenios[$venta['convenio']] ?? null) : null,
            'id_precio_acordado' => $lista['id'],
            'fecha_inscripcion' => $venta['inicio'],
            'fecha_inicio' => $venta['inicio'],
            'fecha_vencimiento' => $venta['fin'],
            'precio_base' => $precio,
            'descuento_aplicado' => 0,
            'precio_final' => $precio,
            'id_estado' => $vigente ? self::ACTIVA : self::VENCIDA,
            'observaciones' => 'Importado de '.$venta['planilla'],
        ]);

        /*
         * EL PAGO, aunque solo se sepa cuánto y cuándo.
         *
         * La columna de la planilla se llama «Cancelado»: lo que hay ahí ya se
         * pagó. Sin apuntarlo, cada una de estas membresías saldría debiendo su
         * precio entero y el panel abriría con novecientos morosos que no lo
         * son. El medio de pago no se sabe y no se inventa: se deja el de
         * siempre y queda dicho en la observación de dónde salió.
         */
        if ($precio > 0 && $this->option('con-pagos')) {
            Pago::create([
                'uuid' => (string) Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $precio,
                'monto_abonado' => $precio,
                'monto_pendiente' => 0,
                'fecha_pago' => $venta['inicio'],
                'id_metodo_pago' => $this->metodoPago ?: null,
                'id_estado' => self::PAGADO,
                'tipo_pago' => 'completo',
                'observaciones' => 'Importado de '.$venta['planilla'].'. El medio de pago no venía en la planilla.',
            ]);
        }
    }
}
