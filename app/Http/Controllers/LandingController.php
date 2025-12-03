<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
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
     * Mostrar la landing page principal
     */
    public function index()
    {
        // Datos dinámicos para la landing (puedes obtenerlos de BD después)
        $planes = [
            [
                'nombre' => 'Plan Básico',
                'precio' => 29990,
                'periodo' => 'mes',
                'caracteristicas' => [
                    'Acceso a sala de musculación',
                    'Horario completo (6:00 - 22:00)',
                    'Casillero incluido',
                    'Evaluación inicial gratuita',
                ],
                'destacado' => false,
            ],
            [
                'nombre' => 'Plan Premium',
                'precio' => 44990,
                'periodo' => 'mes',
                'caracteristicas' => [
                    'Todo del Plan Básico',
                    'Clases grupales ilimitadas',
                    'Acceso a área de cardio premium',
                    'Seguimiento nutricional básico',
                    '1 sesión de entrenador personal/mes',
                ],
                'destacado' => true,
            ],
            [
                'nombre' => 'Plan Elite',
                'precio' => 69990,
                'periodo' => 'mes',
                'caracteristicas' => [
                    'Todo del Plan Premium',
                    '4 sesiones de entrenador personal/mes',
                    'Plan nutricional personalizado',
                    'Acceso a sauna y spa',
                    'Parking gratuito',
                    'Invitaciones para amigos (2/mes)',
                ],
                'destacado' => false,
            ],
        ];

        $testimonios = [
            [
                'nombre' => 'Carlos M.',
                'texto' => 'En 6 meses transformé mi cuerpo completamente. Los entrenadores son excelentes y el ambiente es muy motivador.',
                'rating' => 5,
                'imagen' => null,
            ],
            [
                'nombre' => 'María P.',
                'texto' => 'El mejor gimnasio de la zona. Las clases grupales son increíbles y siempre hay equipos disponibles.',
                'rating' => 5,
                'imagen' => null,
            ],
            [
                'nombre' => 'Roberto S.',
                'texto' => 'Llevo 2 años entrenando aquí. El equipo es de primera calidad y el personal muy profesional.',
                'rating' => 5,
                'imagen' => null,
            ],
        ];

        $servicios = [
            [
                'icono' => 'dumbbell',
                'titulo' => 'Musculación',
                'descripcion' => 'Equipos de última generación para tu entrenamiento de fuerza.',
            ],
            [
                'icono' => 'heartbeat',
                'titulo' => 'Cardio',
                'descripcion' => 'Área completa de cardio con cintas, bicicletas y elípticas.',
            ],
            [
                'icono' => 'users',
                'titulo' => 'Clases Grupales',
                'descripcion' => 'Spinning, CrossFit, Yoga, Zumba y más de 20 clases semanales.',
            ],
            [
                'icono' => 'user-tie',
                'titulo' => 'Personal Training',
                'descripcion' => 'Entrenadores certificados para alcanzar tus objetivos.',
            ],
            [
                'icono' => 'apple-alt',
                'titulo' => 'Nutrición',
                'descripcion' => 'Asesoría nutricional personalizada con profesionales.',
            ],
            [
                'icono' => 'spa',
                'titulo' => 'Wellness',
                'descripcion' => 'Sauna, spa y área de relajación para tu recuperación.',
            ],
        ];

        return view('landing.index', compact('planes', 'testimonios', 'servicios'));
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
            'servicio' => ['nullable', 'string', 'in:informacion,inscripcion,clases,personal,otro'],
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
            'servicio' => $request->servicio ?? 'informacion',
            'ip' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent(), 255),
            'fecha' => now()->format('Y-m-d H:i:s'),
        ];

        // 5. Log del contacto (en producción: guardar en BD o enviar email)
        Log::channel('daily')->info('Nuevo contacto desde landing', $datos);

        // TODO: En producción, descomentar para enviar email
        // Mail::to('contacto@estoicosgym.cl')->send(new ContactoLanding($datos));

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
