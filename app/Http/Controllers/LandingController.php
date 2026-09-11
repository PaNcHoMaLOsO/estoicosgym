<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Support\Ajustes;
use App\Services\CorreoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LandingController extends Controller
{
    /**
     * Lo que puede escribir el cliente en «Interes», y como llega al correo.
     */
    private const INTERESES = [
        'informacion' => 'Información general',
        'inscripcion' => 'Quiero inscribirme',
        'convenio' => 'Convenio de empresa',
        'otro' => 'Otro',
    ];

    /**
     * La portada del gimnasio: la que ven los clientes.
     *
     * TODO LO QUE MUESTRA SALE DEL SISTEMA. Los planes y sus precios son los
     * del catalogo —los mismos que se cobran en el meson— y los datos de
     * contacto son los de Configuracion -> El gimnasio. Antes era todo
     * inventado: tres planes que no existian («Plan Elite» con sauna, spa y
     * estacionamiento), testimonios escritos a mano, una direccion y un
     * telefono de ejemplo y una «garantia de 7 dias» que ningun gimnasio
     * deberia prometer sin haberla decidido. Un cliente que llegaba con el
     * precio de la web se encontraba con otro en el meson.
     */
    public function index()
    {
        $gimnasio = [
            'nombre' => Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM',
            'direccion' => Ajustes::obtener('gimnasio.direccion'),
            'telefono' => Ajustes::obtener('gimnasio.telefono'),
            'email' => Ajustes::obtener('gimnasio.email'),
            'horario' => Ajustes::obtener('gimnasio.horario'),
        ];

        $planes = $this->planesALaVenta();

        return view('landing.index', [
            'gimnasio' => $gimnasio,
            'planes' => $planes,
            'servicios' => $this->servicios(),
            'web' => $this->datosParaGoogle($gimnasio, $planes),
        ]);
    }

    /**
     * Los planes que se pueden comprar hoy, del mas barato al mas caro.
     *
     * Solo los activos y con precio vigente: un plan sin precio no se puede
     * vender, y ensenarlo seria prometer algo que el meson no puede cobrar.
     */
    private function planesALaVenta(): array
    {
        // «El mas elegido» se CUENTA, no se decide: el plan con mas membresias
        // activas. Sin datos, ninguno lleva la marca.
        $masElegido = Inscripcion::where('id_estado', 100)
            ->selectRaw('id_membresia, count(*) as total')
            ->groupBy('id_membresia')
            ->orderByDesc('total')
            ->value('id_membresia');

        return Membresia::where('activo', true)
            ->with(['precios' => fn ($q) => $q->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderByDesc('fecha_vigencia_desde')])
            ->get()
            ->filter(fn (Membresia $m) => $m->precios->isNotEmpty())
            ->map(function (Membresia $m) use ($masElegido) {
                $precio = $m->precios->first();

                return [
                    'nombre' => $m->nombre,
                    'descripcion' => $m->descripcion,
                    'duracion' => $this->duracion($m),
                    'precio' => (int) $precio->precio_normal,
                    'precio_convenio' => $precio->precio_convenio ? (int) $precio->precio_convenio : null,
                    'destacado' => $masElegido !== null && $m->id === (int) $masElegido,
                ];
            })
            ->sortBy('precio')
            ->values()
            ->all();
    }

    /** «1 mes», «3 meses», «1 año», «1 día». */
    private function duracion(Membresia $m): string
    {
        $meses = (int) $m->duracion_meses;
        $dias = (int) $m->duracion_dias;

        return match (true) {
            $meses === 12 => '1 año',
            $meses === 1 => '1 mes',
            $meses > 1 => "{$meses} meses",
            $dias === 1 => '1 día',
            $dias > 1 => "{$dias} días",
            default => '',
        };
    }

    /**
     * Lo que se ofrece.
     *
     * GENERICO A PROPOSITO. La lista anterior prometia cosas que nadie
     * comprobo —«mas de 20 clases semanales», «sauna, spa», nutricionistas—.
     * Esto es lo que tiene cualquier sala de musculacion; lo que PRO GYM
     * tiene de propio lo tiene que escribir quien lo conoce.
     */
    private function servicios(): array
    {
        return [
            ['icono' => 'dumbbell', 'titulo' => 'Musculación', 'descripcion' => 'Máquinas y peso libre para entrenar la fuerza a tu ritmo.'],
            ['icono' => 'heartbeat', 'titulo' => 'Cardio', 'descripcion' => 'Equipos de cardio para calentar, quemar y ganar resistencia.'],
            ['icono' => 'user-check', 'titulo' => 'Orientación en sala', 'descripcion' => 'Te enseñamos a usar los equipos para que entrenes seguro.'],
        ];
    }

    /**
     * Lo que lee Google: el titulo, la descripcion y la ficha estructurada.
     *
     * PARA SALIR EN «GIMNASIO EN LOS ANGELES» lo que mas pesa no esta aqui:
     * es la ficha del gimnasio en Google Maps (Perfil de Empresa) y que la
     * direccion y el telefono sean los mismos en todas partes. Esto hace que
     * la pagina diga lo mismo, con las palabras que la gente escribe, y en el
     * formato que Google entiende —schema.org ExerciseGym—, con los planes y
     * los precios de verdad.
     *
     * @param array<string,mixed> $gimnasio
     * @param list<array<string,mixed>> $planes
     * @return array<string,mixed>
     */
    private function datosParaGoogle(array $gimnasio, array $planes): array
    {
        $ciudad = trim((string) Ajustes::obtener('web.ciudad'));
        $region = trim((string) Ajustes::obtener('web.region'));
        $maps = Ajustes::obtener('web.google_maps') ?: null;
        $instagram = Ajustes::obtener('web.instagram') ?: null;
        $facebook = Ajustes::obtener('web.facebook') ?: null;

        $precios = array_column($planes, 'precio');
        $pesos = fn (int $monto) => '$' . number_format($monto, 0, ',', '.');
        $inicio = url('/');

        $vacio = fn ($valor) => $valor !== null && $valor !== '' && $valor !== [];

        $ficha = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'ExerciseGym',
            'name' => $gimnasio['nombre'],
            'slogan' => 'Profesionales del deporte',
            'url' => $inicio,
            'logo' => asset('images/progym-logo.png'),
            'image' => asset('images/progym-logo.png'),
            'telephone' => $gimnasio['telefono'] ?: null,
            'email' => $gimnasio['email'] ?: null,
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $gimnasio['direccion'] ?: null,
                'addressLocality' => $ciudad ?: null,
                'addressRegion' => $region ?: null,
                'addressCountry' => 'CL',
            ], $vacio),
            'areaServed' => $ciudad ?: null,
            'hasMap' => $maps,
            'sameAs' => array_values(array_filter([$instagram, $facebook])),
            'priceRange' => $precios ? $pesos(min($precios)) . ' - ' . $pesos(max($precios)) : null,
            'makesOffer' => array_map(fn (array $plan) => [
                '@type' => 'Offer',
                'name' => 'Plan ' . $plan['nombre'],
                'price' => $plan['precio'],
                'priceCurrency' => 'CLP',
            ], $planes),
        ], $vacio);

        $donde = $ciudad ? "Gimnasio en {$ciudad}" : 'Gimnasio';

        return [
            'ciudad' => $ciudad,
            'region' => $region,
            'titulo' => $gimnasio['nombre'] . ($ciudad ? " | Gimnasio en {$ciudad}" . ($region ? ", {$region}" : '') : ''),
            'descripcion' => "{$donde}: musculación, cardio y orientación en sala."
                . ($precios ? ' Planes desde ' . $pesos(min($precios)) . '.' : '')
                . ' Revisa los precios y consulta tu membresía en línea.',
            'canonical' => $inicio,
            'json_ld' => $ficha,
            'google_analytics' => Ajustes::obtener('web.google_analytics') ?: null,
            'search_console' => Ajustes::obtener('web.search_console') ?: null,
            'google_maps' => $maps,
            'instagram' => $instagram,
            'facebook' => $facebook,
        ];
    }

    /**
     * Para los buscadores: todo se puede leer, y aqui esta el mapa del sitio.
     *
     * EL PANEL NO SE NOMBRA a proposito. robots.txt lo puede abrir cualquiera,
     * y escribir «Disallow: /login» seria senalarle a todo el mundo donde esta
     * la puerta. El panel ya queda fuera de Google por su cuenta: pide sesion,
     * y la pantalla de acceso lleva «noindex».
     */
    public function robots()
    {
        $texto = "User-agent: *\nAllow: /\n\nSitemap: " . route('landing.sitemap') . "\n";

        return response($texto, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * El mapa del sitio. Hoy es una pagina, pero Search Console lo pide.
     *
     * La fecha es la del ultimo cambio de precios: es lo que cambia la pagina.
     */
    public function sitemap()
    {
        $cambio = \App\Models\PrecioMembresia::max('updated_at');
        $fecha = $cambio ? Carbon::parse($cambio)->toDateString() : now()->toDateString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . '  <url><loc>' . e(url('/')) . '</loc><lastmod>' . $fecha . '</lastmod>'
            . '<changefreq>weekly</changefreq><priority>1.0</priority></url>' . "\n"
            . '</urlset>' . "\n";

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Procesar formulario de contacto con seguridad
     */
    public function contacto(Request $request)
    {
        // 1. Rate Limiting - Máximo 5 envíos por IP cada 10 minutos
        $key = 'contacto:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withInput()
                ->with('error', "Demasiados intentos. Por favor espera {$seconds} segundos.");
        }
        
        RateLimiter::hit($key, 600); // 10 minutos

        // 2. Honeypot - Campo oculto anti-bot
        if ($request->filled('website')) {
            // Bot detectado - simular éxito pero no procesar
            Log::warning('Honeypot triggered', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->with('success', '¡Mensaje enviado correctamente!');
        }

        // 3. Validación estricta con sanitización
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\-\']+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'mensaje' => ['required', 'string', 'min:10', 'max:1000'],
            'servicio' => ['nullable', 'string', 'in:' . implode(',', array_keys(self::INTERESES))],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'telefono.regex' => 'El teléfono tiene un formato inválido.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // 4. Sanitizar datos
        $datos = [
            'nombre' => strip_tags(trim($request->nombre)),
            'email' => filter_var(trim($request->email), FILTER_SANITIZE_EMAIL),
            'telefono' => $request->telefono ? preg_replace('/[^\d\+\-\s]/', '', $request->telefono) : null,
            'mensaje' => strip_tags(trim($request->mensaje)),
            'servicio' => self::INTERESES[$request->servicio ?? 'informacion'] ?? self::INTERESES['informacion'],
            'ip' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent(), 255),
            'fecha' => now()->format('Y-m-d H:i:s'),
        ];

        // 5. Log del contacto (en producción: guardar en BD o enviar email)
        Log::channel('daily')->info('Nuevo contacto desde landing', $datos);

        /*
         * EL MENSAJE SE ENVIA DE VERDAD.
         *
         * Aqui habia un TODO con el envio comentado, asi que al visitante se le
         * decia «te responderemos pronto» y el mensaje no llegaba a nadie: moria
         * en el fichero de registro. Cada persona que escribia desde el sitio
         * publico se perdia.
         *
         * El registro de arriba se queda igual: es lo unico que guarda el
         * mensaje si el envio falla, porque no hay tabla de contactos.
         */
        // Primero el correo de Configuracion: es el que el gimnasio dice que lee.
        $destino = Ajustes::obtener('gimnasio.email') ?: config('correo.contacto') ?: config('mail.from.address');

        try {
            app(CorreoService::class)->enviar(
                $destino,
                "Contacto web · {$datos['nombre']}",
                view('emails.contacto', ['datos' => $datos])->render(),
            );
        } catch (\Throwable $e) {
            // Al visitante no se le dice que fallo: el hizo su parte y el
            // mensaje sigue en el registro para recuperarlo a mano.
            Log::error('No se pudo enviar el contacto de la web: ' . $e->getMessage(), [
                'destino' => $destino,
                'de' => $datos['email'],
            ]);
        }

        // 6. Respuesta exitosa
        return back()->with('success', '¡Gracias por contactarnos! Te responderemos pronto.');
    }

    /**
     * «Mi membresía»: el socio mira cómo está su membresía desde su casa.
     *
     * PIDE DOS DATOS, NO UNO. Con el RUT solo, cualquiera que lo supiera —y
     * un RUT sale en cualquier boleta— veía el nombre completo de la persona,
     * si era socia, su plan y cuándo pagó. Ahora se pide además lo que sabe el
     * socio y no un desconocido: los últimos 4 dígitos de su celular.
     *
     * RESPONDE LO JUSTO: el nombre de pila, el plan, el vencimiento y si debe
     * algo. Ni apellido ni historial de pagos: eso se ve en el mesón.
     *
     * «RUT que no es socio» y «RUT con los dígitos equivocados» responden
     * EXACTAMENTE lo mismo. Si respondieran distinto, la pregunta «¿esta
     * persona es socia?» se contestaría sin saber los dígitos.
     *
     * Los frenos van en capas: por IP —3 consultas cada 5 minutos y bloqueo
     * tras 5 fallos— y por RUT o celular —bloqueo tras 5 fallos, venga de
     * donde venga—, para que cambiar de conexión no sirva para probar dígitos.
     */
    public function consultarMembresia(Request $request)
    {
        $ip = (string) $request->ip();

        // 1. Trampa para bots: se les responde como a alguien que no existe.
        if ($request->filled('website') || $request->filled('url')) {
            Log::warning('Consulta membresía: Honeypot activado', ['ip' => $ip]);

            return $this->noEncontrada();
        }

        // 2. Frenos por IP.
        $keyBloqueo = 'consulta_bloqueado:' . $ip;
        $keyConsultas = 'consulta_membresia:' . $ip;

        if (RateLimiter::tooManyAttempts($keyBloqueo, 1)) {
            $minutos = (int) ceil(RateLimiter::availableIn($keyBloqueo) / 60);
            Log::warning('Consulta membresía: IP bloqueada intentando acceder', ['ip' => $ip]);

            return response()->json([
                'success' => false,
                'message' => "Tu acceso está temporalmente bloqueado. Intenta en {$minutos} minutos.",
                'blocked' => true,
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($keyConsultas, 3)) {
            $segundos = RateLimiter::availableIn($keyConsultas);
            Log::info('Consulta membresía: Rate limit alcanzado', ['ip' => $ip]);

            return response()->json([
                'success' => false,
                'message' => "Has realizado muchas consultas. Espera {$segundos} segundos.",
            ], 429);
        }

        RateLimiter::hit($keyConsultas, 300);

        // 3. Quién es.
        [$cliente, $llave, $respuesta] = $request->input('tipo', 'rut') === 'celular'
            ? $this->buscarPorCelular($request, $ip)
            : $this->buscarPorRut($request, $ip);

        if ($respuesta) {
            return $respuesta;
        }

        if (! $cliente) {
            $this->contarFallo($ip, $llave);

            return $this->noEncontrada();
        }

        // 4. Encontrado: se olvidan los fallos de esta conexión y de esta llave.
        RateLimiter::clear('consulta_fallidos:' . $ip);
        RateLimiter::clear($llave);

        // Antes se registraba aquí «rut_parcial» con una variable que solo
        // existe en la búsqueda por RUT: la búsqueda por celular reventaba
        // con un error 500 justo al encontrar a la persona.
        Log::info('Consulta membresía: Exitosa', ['ip' => $ip, 'cliente_id' => $cliente->id]);

        return response()->json([
            'success' => true,
            'data' => $this->loQueSeMuestra($cliente),
        ]);
    }

    /**
     * Por RUT y los últimos 4 dígitos del celular.
     *
     * @return array{0: ?Cliente, 1: ?string, 2: ?\Illuminate\Http\JsonResponse}
     */
    private function buscarPorRut(Request $request, string $ip): array
    {
        $rutInput = preg_replace('/[^0-9kK.-]/', '', strip_tags(trim((string) $request->input('rut', ''))));
        $digitos = preg_replace('/[^0-9]/', '', (string) $request->input('digitos', ''));

        if (strlen($rutInput) < 7 || strlen($rutInput) > 12) {
            return [null, null, $this->invalido('Formato de RUT inválido.')];
        }

        if (strlen($digitos) !== 4) {
            return [null, null, $this->invalido('Ingresa los últimos 4 dígitos de tu celular.')];
        }

        $rutLimpio = strtoupper(preg_replace('/[^0-9kK]/', '', $rutInput));
        $llave = 'consulta_fallidos_llave:' . hash('sha256', 'rut:' . $rutLimpio);

        if ($bloqueada = $this->llaveBloqueada($llave)) {
            return [null, $llave, $bloqueada];
        }

        // Un RUT mal escrito se dice —el dígito verificador lo calcula
        // cualquiera, no revela nada—, pero cuenta como fallo: probar RUTs al
        // azar es justo lo que hace quien busca a alguien.
        if (! $this->validarRutChileno($rutLimpio)) {
            $this->contarFallo($ip, null);

            return [null, $llave, $this->invalido('El RUT ingresado no es válido.')];
        }

        $cliente = Cliente::where('activo', true)
            ->where(function ($q) use ($rutInput, $rutLimpio) {
                $q->where('run_pasaporte', $rutInput)
                    ->orWhereRaw("UPPER(REPLACE(REPLACE(REPLACE(run_pasaporte, '.', ''), '-', ''), ' ', '')) = ?", [$rutLimpio]);
            })
            ->first();

        // El mismo camino exista o no: así la respuesta no distingue «no es
        // socio» de «es socio pero los dígitos no son».
        $celular = $cliente ? preg_replace('/[^0-9]/', '', (string) $cliente->celular) : '';
        $coincide = $celular !== '' && hash_equals(substr($celular, -4), $digitos);

        return [$coincide ? $cliente : null, $llave, null];
    }

    /**
     * Por celular y primer nombre, para quien no tiene RUT.
     *
     * @return array{0: ?Cliente, 1: ?string, 2: ?\Illuminate\Http\JsonResponse}
     */
    private function buscarPorCelular(Request $request, string $ip): array
    {
        $celularInput = preg_replace('/[^0-9]/', '', (string) $request->input('celular', ''));
        $nombreInput = trim(strip_tags((string) $request->input('nombre', '')));

        if (strlen($celularInput) < 8 || strlen($celularInput) > 12) {
            return [null, null, $this->invalido('Formato de celular inválido.')];
        }

        if (mb_strlen($nombreInput) < 2 || mb_strlen($nombreInput) > 50) {
            return [null, null, $this->invalido('Nombre inválido.')];
        }

        $llave = 'consulta_fallidos_llave:' . hash('sha256', 'cel:' . substr($celularInput, -8));

        if ($bloqueada = $this->llaveBloqueada($llave)) {
            return [null, $llave, $bloqueada];
        }

        /*
         * El celular se guarda normalizado —nueve dígitos, sin +56—, así que se
         * compara tal cual. Antes se usaba RIGHT() de MySQL, que otras bases no
         * tienen.
         *
         * Y el nombre tiene que ser EL PRIMER NOMBRE, entero. Antes bastaba con
         * que las letras escritas estuvieran dentro del nombre: «an» abría la
         * ficha de cualquier Juan, Ana o Daniela con ese celular.
         */
        $primero = fn (string $texto) => explode(' ', trim($this->normalizarTexto($texto)))[0] ?? '';
        $buscado = $primero($nombreInput);

        $cliente = Cliente::where('activo', true)
            ->where(fn ($q) => $q
                ->where('celular', substr($celularInput, -9))
                ->orWhere('celular', 'like', '%' . substr($celularInput, -8)))
            ->get()
            ->first(fn (Cliente $c) => $buscado !== '' && $primero((string) $c->nombres) === $buscado);

        return [$cliente, $llave, null];
    }

    /** Un fallo cuenta para la conexión y, si la hay, para la llave (el RUT o el celular). */
    private function contarFallo(string $ip, ?string $llave): void
    {
        $keyFallidos = 'consulta_fallidos:' . $ip;

        RateLimiter::hit($keyFallidos, 900);

        if (RateLimiter::attempts($keyFallidos) >= 5) {
            RateLimiter::hit('consulta_bloqueado:' . $ip, 1800);
            Log::warning('Consulta membresía: IP bloqueada por muchos fallos', ['ip' => $ip]);
        }

        if ($llave) {
            RateLimiter::hit($llave, 1800);
        }
    }

    /** Cinco fallos para el mismo RUT o celular lo cierran 30 minutos, cambie o no la IP. */
    private function llaveBloqueada(string $llave): ?\Illuminate\Http\JsonResponse
    {
        if (! RateLimiter::tooManyAttempts($llave, 5)) {
            return null;
        }

        $minutos = (int) ceil(RateLimiter::availableIn($llave) / 60);

        return response()->json([
            'success' => false,
            'message' => "Por seguridad, esta consulta quedó bloqueada. Intenta en {$minutos} minutos o pregunta en el mesón.",
            'blocked' => true,
        ], 429);
    }

    private function invalido(string $mensaje): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => false, 'message' => $mensaje], 422);
    }

    /** Siempre la misma respuesta: no existe, no coincide o es un bot. */
    private function noEncontrada(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'No encontramos tu membresía. Revisa los datos e intenta de nuevo.',
        ], 404);
    }

    /**
     * Lo que ve el socio: su nombre de pila, su plan y si debe algo.
     *
     * @return array<string,mixed>
     */
    private function loQueSeMuestra(Cliente $cliente): array
    {
        $activa = $cliente->inscripciones()
            ->with('membresia')
            ->where('id_estado', 100)
            ->orderByDesc('fecha_vencimiento')
            ->first();

        $datos = [
            'nombre' => explode(' ', trim((string) $cliente->nombres))[0] ?: 'Socio',
            'membresia' => null,
            'estado' => 'Sin membresía activa',
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'dias_restantes' => null,
            'saldo' => 0,
        ];

        if (! $activa) {
            return $datos;
        }

        $dias = $activa->fecha_vencimiento
            ? (int) now()->startOfDay()->diffInDays($activa->fecha_vencimiento->copy()->startOfDay(), false)
            : null;

        return [
            ...$datos,
            'membresia' => $activa->membresia?->nombre ?? 'Membresía',
            'estado' => match (true) {
                $dias === null, $dias > 0 => 'Activa',
                $dias === 0 => 'Vence hoy',
                default => 'Vencida',
            },
            'fecha_inicio' => $activa->fecha_inicio?->format('d/m/Y'),
            'fecha_fin' => $activa->fecha_vencimiento?->format('d/m/Y'),
            'dias_restantes' => $dias === null ? null : max(0, $dias),
            'saldo' => (int) $activa->obtenerEstadoPago()['pendiente'],
        ];
    }

    /**
     * Validar RUT chileno con dígito verificador
     * Algoritmo Módulo 11
     * 
     * @param string $rut RUT sin puntos ni guión (ej: 12345678K)
     * @return bool
     */
    private function validarRutChileno(string $rut): bool
    {
        // Debe tener al menos 2 caracteres (1 dígito + DV)
        if (strlen($rut) < 2) {
            return false;
        }
        
        // Separar cuerpo y dígito verificador
        $dv = substr($rut, -1);
        $cuerpo = substr($rut, 0, -1);
        
        // Cuerpo debe ser numérico
        if (!ctype_digit($cuerpo)) {
            return false;
        }
        
        // Calcular dígito verificador esperado
        $suma = 0;
        $multiplicador = 2;
        
        // Recorrer de derecha a izquierda
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int)$cuerpo[$i] * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }
        
        $resto = $suma % 11;
        $dvCalculado = 11 - $resto;
        
        // Convertir a caracter
        if ($dvCalculado === 11) {
            $dvCalculado = '0';
        } elseif ($dvCalculado === 10) {
            $dvCalculado = 'K';
        } else {
            $dvCalculado = (string)$dvCalculado;
        }
        
        // Comparar (case insensitive para K)
        return strtoupper($dv) === $dvCalculado;
    }

    /**
     * Normalizar texto para comparación
     * Quita tildes, convierte a minúsculas
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        
        // Quitar tildes
        $tildes = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 
                   'ü' => 'u', 'ñ' => 'n', 'Á' => 'a', 'É' => 'e', 'Í' => 'i', 
                   'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n'];
        
        return strtr($texto, $tildes);
    }
}
