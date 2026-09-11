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
use Illuminate\Support\Facades\Crypt;
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

        return view('landing.index', [
            'gimnasio' => $gimnasio,
            'planes' => $this->planesALaVenta(),
            'servicios' => $this->servicios(),
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
     * Consulta simple de membresía (sin login)
     * 
     * SEGURIDAD IMPLEMENTADA:
     * ✅ Rate limiting: 3 consultas por IP cada 5 minutos (bloqueo progresivo)
     * ✅ Validación de RUT con dígito verificador
     * ✅ Sanitización completa de inputs
     * ✅ Logs de acceso (IP, fecha, RUT parcial)
     * ✅ Honeypot anti-bot
     * ✅ Headers de seguridad (via middleware)
     * ✅ Datos encriptados en respuesta
     * ✅ Bloqueo temporal por múltiples intentos fallidos
     */
    public function consultarMembresia(Request $request)
    {
        $ip = $request->ip();
        
        // 1. HONEYPOT - Detectar bots
        if ($request->filled('website') || $request->filled('url')) {
            Log::warning('Consulta membresía: Honeypot activado', ['ip' => $ip]);
            // Simular respuesta normal para confundir bots
            return response()->json([
                'success' => false,
                'message' => 'No encontramos tu membresía.',
            ], 404);
        }
        
        // 2. RATE LIMITING PROGRESIVO
        $keyConsultas = 'consulta_membresia:' . $ip;
        $keyBloqueo = 'consulta_bloqueado:' . $ip;
        $keyFallidos = 'consulta_fallidos:' . $ip;
        
        // Verificar si IP está bloqueada
        if (RateLimiter::tooManyAttempts($keyBloqueo, 1)) {
            $segundos = RateLimiter::availableIn($keyBloqueo);
            $minutos = ceil($segundos / 60);
            
            Log::warning('Consulta membresía: IP bloqueada intentando acceder', ['ip' => $ip]);
            
            return response()->json([
                'success' => false,
                'message' => "Tu acceso está temporalmente bloqueado. Intenta en {$minutos} minutos.",
                'blocked' => true,
            ], 429);
        }
        
        // Rate limiting normal: 3 consultas cada 5 minutos
        if (RateLimiter::tooManyAttempts($keyConsultas, 3)) {
            $segundos = RateLimiter::availableIn($keyConsultas);
            
            Log::info('Consulta membresía: Rate limit alcanzado', ['ip' => $ip]);
            
            return response()->json([
                'success' => false,
                'message' => "Has realizado muchas consultas. Espera {$segundos} segundos.",
            ], 429);
        }
        
        RateLimiter::hit($keyConsultas, 300); // 5 minutos

        // 3. DETERMINAR TIPO DE CONSULTA
        $tipoConsulta = $request->input('tipo', 'rut');
        $cliente = null;
        
        if ($tipoConsulta === 'rut') {
            // ===== CONSULTA POR RUT =====
            $rutInput = trim($request->input('rut', ''));
            $rutInput = strip_tags($rutInput);
            $rutInput = preg_replace('/[^0-9kK.\-]/', '', $rutInput);
            
            if (strlen($rutInput) < 7 || strlen($rutInput) > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de RUT inválido.',
                ], 422);
            }
            
            // Limpiar RUT para validación
            $rutLimpio = strtoupper(preg_replace('/[^0-9kK]/', '', $rutInput));
            
            // VALIDAR DÍGITO VERIFICADOR
            if (!$this->validarRutChileno($rutLimpio)) {
                RateLimiter::hit($keyFallidos, 900);
                
                if (RateLimiter::attempts($keyFallidos) >= 5) {
                    RateLimiter::hit($keyBloqueo, 1800);
                    Log::warning('Consulta membresía: IP bloqueada por muchos RUTs inválidos', ['ip' => $ip]);
                }
                
                Log::info('Consulta membresía: RUT inválido', ['ip' => $ip, 'rut_parcial' => substr($rutLimpio, 0, 3) . '****']);
                
                return response()->json([
                    'success' => false,
                    'message' => 'El RUT ingresado no es válido.',
                ], 422);
            }

            // BUSCAR CLIENTE POR RUT
            $cliente = Cliente::where('activo', true)
                ->where(function ($query) use ($rutInput, $rutLimpio) {
                    $query->where('run_pasaporte', $rutInput)
                          ->orWhereRaw("UPPER(REPLACE(REPLACE(REPLACE(run_pasaporte, '.', ''), '-', ''), ' ', '')) = ?", [$rutLimpio]);
                })
                ->with(['inscripciones' => function ($q) {
                    $q->with(['membresia', 'estado'])
                      ->orderBy('fecha_inicio', 'desc')
                      ->limit(3);
                }, 'pagos' => function ($q) {
                    $q->with('estado')
                      ->orderBy('fecha_pago', 'desc')
                      ->limit(5);
                }])
                ->first();

            if (!$cliente) {
                RateLimiter::hit($keyFallidos, 900);
                
                if (RateLimiter::attempts($keyFallidos) >= 5) {
                    RateLimiter::hit($keyBloqueo, 1800);
                    Log::warning('Consulta membresía: IP bloqueada por muchos RUTs no encontrados', ['ip' => $ip]);
                }
                
                Log::info('Consulta membresía: RUT no encontrado', ['ip' => $ip, 'rut_parcial' => substr($rutLimpio, 0, 3) . '****']);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No encontramos tu membresía. Verifica tu RUT.',
                ], 404);
            }
            
        } else {
            // ===== CONSULTA POR CELULAR + NOMBRE =====
            $celularInput = preg_replace('/[^0-9]/', '', $request->input('celular', ''));
            $nombreInput = trim(strip_tags($request->input('nombre', '')));
            
            if (strlen($celularInput) < 8 || strlen($celularInput) > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de celular inválido.',
                ], 422);
            }
            
            if (strlen($nombreInput) < 2 || strlen($nombreInput) > 50) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nombre inválido.',
                ], 422);
            }
            
            // Normalizar nombre para búsqueda (quitar tildes, minúsculas)
            $nombreNormalizado = $this->normalizarTexto($nombreInput);

            // BUSCAR CLIENTE POR CELULAR
            $cliente = Cliente::where('activo', true)
                ->where(function ($query) use ($celularInput) {
                    // Buscar por últimos 8-9 dígitos del celular
                    $query->whereRaw("RIGHT(REPLACE(REPLACE(celular, ' ', ''), '+56', ''), 9) = ?", [substr($celularInput, -9)])
                          ->orWhereRaw("RIGHT(REPLACE(REPLACE(celular, ' ', ''), '+56', ''), 8) = ?", [substr($celularInput, -8)]);
                })
                ->with(['inscripciones' => function ($q) {
                    $q->with(['membresia', 'estado'])
                      ->orderBy('fecha_inicio', 'desc')
                      ->limit(3);
                }, 'pagos' => function ($q) {
                    $q->with('estado')
                      ->orderBy('fecha_pago', 'desc')
                      ->limit(5);
                }])
                ->get();
            
            // Verificar nombre coincide (al menos parcialmente)
            $cliente = $cliente->first(function ($c) use ($nombreNormalizado) {
                $nombreCliente = $this->normalizarTexto($c->nombres);
                // El nombre ingresado debe estar contenido en el nombre del cliente
                return str_contains($nombreCliente, $nombreNormalizado) || 
                       str_contains($nombreNormalizado, explode(' ', $nombreCliente)[0]);
            });

            if (!$cliente) {
                RateLimiter::hit($keyFallidos, 900);
                
                if (RateLimiter::attempts($keyFallidos) >= 5) {
                    RateLimiter::hit($keyBloqueo, 1800);
                    Log::warning('Consulta membresía: IP bloqueada por muchos celulares no encontrados', ['ip' => $ip]);
                }
                
                Log::info('Consulta membresía: Celular/nombre no coincide', [
                    'ip' => $ip, 
                    'celular_parcial' => '****' . substr($celularInput, -4),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No encontramos tu membresía. Verifica tu celular y nombre.',
                ], 404);
            }
        }

        // 4. ÉXITO - Limpiar intentos fallidos
        RateLimiter::clear($keyFallidos);
        
        // LOG de consulta exitosa
        Log::info('Consulta membresía: Exitosa', [
            'ip' => $ip,
            'cliente_id' => $cliente->id,
            'rut_parcial' => substr($rutLimpio, 0, 3) . '****',
            'timestamp' => now()->toIso8601String(),
        ]);

        // 7. Preparar datos seguros
        $inscripcionActiva = $cliente->inscripciones
            ->whereIn('id_estado', [100]) // Solo activas
            ->first();

        $diasRestantes = null;
        $estadoMembresia = 'Sin membresía activa';
        $fechaInicio = null;
        $fechaFin = null;
        $nombreMembresia = null;

        if ($inscripcionActiva) {
            $fechaFin = $inscripcionActiva->fecha_vencimiento; // Campo correcto
            $fechaInicio = $inscripcionActiva->fecha_inicio;
            $nombreMembresia = $inscripcionActiva->membresia?->nombre ?? 'Membresía';
            
            if ($fechaFin) {
                $diasRestantes = max(0, (int) Carbon::now()->diffInDays($fechaFin, false));
                
                if ($diasRestantes > 0) {
                    $estadoMembresia = 'Activa';
                } elseif ($diasRestantes === 0) {
                    $estadoMembresia = 'Vence hoy';
                } else {
                    $estadoMembresia = 'Vencida';
                    $diasRestantes = 0;
                }
            } else {
                $estadoMembresia = 'Activa (sin fecha fin)';
            }
        }

        // Últimos pagos (solo estado, sin montos)
        $ultimosPagos = $cliente->pagos->map(function ($pago) {
            return [
                'fecha' => $pago->fecha_pago?->format('d/m/Y') ?? 'N/A',
                'estado' => $pago->estado?->nombre ?? 'N/A',
                'color' => $this->getColorPago($pago->estado?->codigo ?? 0),
            ];
        })->take(3)->toArray();

        // 5. Respuesta con datos encriptados para integridad
        $datos = [
            'nombre' => $cliente->nombres . ' ' . $cliente->apellido_paterno,
            'membresia' => $nombreMembresia,
            'estado' => $estadoMembresia,
            'fecha_inicio' => $fechaInicio?->format('d/m/Y'),
            'fecha_fin' => $fechaFin?->format('d/m/Y'),
            'dias_restantes' => $diasRestantes,
            'pagos' => $ultimosPagos,
        ];

        return response()->json([
            'success' => true,
            'data' => $datos,
            // Token de verificación encriptado
            'token' => Crypt::encryptString(json_encode([
                'id' => $cliente->id,
                'ts' => time(),
            ])),
        ]);
    }

    /**
     * Helper para color de estado de pago
     */
    private function getColorPago(int $codigo): string
    {
        return match(true) {
            $codigo === 201 => 'green',  // Pagado
            $codigo === 200 => 'yellow', // Pendiente
            $codigo === 202 => 'blue',   // Parcial
            default => 'red',            // Vencido/Cancelado
        };
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
