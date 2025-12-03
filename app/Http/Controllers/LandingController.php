<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
}
