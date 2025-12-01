<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwoFactorService
{
    /**
     * Enviar código de verificación
     */
    public function sendVerificationCode(User $user, string $type = 'login'): array
    {
        if (!$user->phone) {
            return [
                'success' => false,
                'message' => 'El usuario no tiene un número de teléfono configurado.',
            ];
        }

        // Crear código de verificación
        $verification = VerificationCode::createFor($user, $type);

        // Enviar según el canal configurado
        $channel = $user->two_factor_channel ?? 'whatsapp';
        
        $sent = match($channel) {
            'whatsapp' => $this->sendWhatsApp($user->phone, $verification->code),
            'sms' => $this->sendSms($user->phone, $verification->code),
            default => $this->sendWhatsApp($user->phone, $verification->code),
        };

        if ($sent) {
            // En desarrollo, guardar código en sesión para mostrar en pantalla
            if (app()->environment('local', 'development')) {
                session(['dev_2fa_code' => $verification->code]);
            }
            
            return [
                'success' => true,
                'message' => 'Código enviado a ' . $this->maskPhone($user->phone),
                'channel' => $channel,
                'expires_in' => 10, // minutos
                'dev_code' => app()->environment('local', 'development') ? $verification->code : null,
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al enviar el código. Intenta nuevamente.',
        ];
    }

    /**
     * Verificar código ingresado
     */
    public function verifyCode(User $user, string $code, string $type = 'login'): array
    {
        $verification = VerificationCode::verify($user, $code, $type);

        if ($verification) {
            return [
                'success' => true,
                'message' => 'Código verificado correctamente.',
            ];
        }

        // Verificar si hay intentos recientes
        $recentAttempts = VerificationCode::where('user_id', $user->id)
            ->where('type', $type)
            ->where('created_at', '>', now()->subMinutes(30))
            ->count();

        if ($recentAttempts >= 5) {
            return [
                'success' => false,
                'message' => 'Demasiados intentos. Espera 30 minutos.',
                'blocked' => true,
            ];
        }

        return [
            'success' => false,
            'message' => 'Código inválido o expirado.',
        ];
    }

    /**
     * Enviar mensaje por WhatsApp usando API
     * Configura tu proveedor preferido (Twilio, MessageBird, Meta, etc.)
     */
    protected function sendWhatsApp(string $phone, string $code): bool
    {
        $message = "🔐 *Estoicos Gym*\n\nTu código de verificación es:\n\n*{$code}*\n\nExpira en 10 minutos.\n\n_Si no solicitaste este código, ignora este mensaje._";

        // Opción 1: Twilio WhatsApp API
        if (config('services.twilio.whatsapp_enabled')) {
            return $this->sendViaTwilio($phone, $message, 'whatsapp');
        }

        // Opción 2: Meta WhatsApp Business API
        if (config('services.meta.whatsapp_enabled')) {
            return $this->sendViaMeta($phone, $code);
        }

        // Opción 3: CallMeBot (Gratis para pruebas)
        if (config('services.callmebot.enabled', true)) {
            return $this->sendViaCallMeBot($phone, $message);
        }

        // Desarrollo: Log del código
        Log::info("2FA WhatsApp Code", [
            'phone' => $phone,
            'code' => $code,
            'message' => $message
        ]);

        // En desarrollo siempre retorna true
        return app()->environment('local', 'development');
    }

    /**
     * Enviar SMS
     */
    protected function sendSms(string $phone, string $code): bool
    {
        $message = "Estoicos Gym - Tu código de verificación es: {$code}. Expira en 10 minutos.";

        // Opción 1: Twilio SMS
        if (config('services.twilio.sms_enabled')) {
            return $this->sendViaTwilio($phone, $message, 'sms');
        }

        // Desarrollo: Log del código
        Log::info("2FA SMS Code", [
            'phone' => $phone,
            'code' => $code,
        ]);

        return app()->environment('local', 'development');
    }

    /**
     * Enviar via Twilio (WhatsApp o SMS)
     */
    protected function sendViaTwilio(string $phone, string $message, string $channel = 'sms'): bool
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = $channel === 'whatsapp' 
                ? 'whatsapp:' . config('services.twilio.whatsapp_from')
                : config('services.twilio.sms_from');

            $to = $channel === 'whatsapp' ? "whatsapp:{$phone}" : $phone;

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("Twilio {$channel} sent", ['phone' => $phone, 'sid' => $response->json('sid')]);
                return true;
            }

            Log::error("Twilio {$channel} failed", ['phone' => $phone, 'error' => $response->json()]);
            return false;

        } catch (\Exception $e) {
            Log::error("Twilio exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Enviar via Meta WhatsApp Business API
     */
    protected function sendViaMeta(string $phone, string $code): bool
    {
        try {
            $token = config('services.meta.whatsapp_token');
            $phoneId = config('services.meta.whatsapp_phone_id');

            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'template',
                    'template' => [
                        'name' => 'verification_code', // Template aprobado por Meta
                        'language' => ['code' => 'es'],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $code]
                                ]
                            ]
                        ]
                    ]
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error("Meta WhatsApp exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Enviar via CallMeBot (Gratis para pruebas personales)
     * Requiere registrar el número: https://www.callmebot.com/blog/free-api-whatsapp-messages/
     */
    protected function sendViaCallMeBot(string $phone, string $message): bool
    {
        try {
            $apiKey = config('services.callmebot.api_key');
            
            if (!$apiKey) {
                Log::warning("CallMeBot API key not configured");
                return false;
            }

            $response = Http::get("https://api.callmebot.com/whatsapp.php", [
                'phone' => $phone,
                'text' => $message,
                'apikey' => $apiKey,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error("CallMeBot exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Ocultar parte del teléfono por privacidad
     */
    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }
        
        return substr($phone, 0, 3) . str_repeat('*', $length - 6) . substr($phone, -3);
    }

    /**
     * Verificar si el usuario tiene 2FA habilitado
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled && !empty($user->phone);
    }

    /**
     * Habilitar 2FA para un usuario
     */
    public function enable(User $user, string $phone, string $channel = 'whatsapp'): array
    {
        // Formatear teléfono (Chile)
        $phone = $this->formatPhone($phone);

        $user->update([
            'phone' => $phone,
            'two_factor_enabled' => true,
            'two_factor_channel' => $channel,
        ]);

        // Enviar código de prueba
        return $this->sendVerificationCode($user, 'phone_verify');
    }

    /**
     * Deshabilitar 2FA
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
        ]);
    }

    /**
     * Formatear número de teléfono (Chile)
     */
    protected function formatPhone(string $phone): string
    {
        // Eliminar espacios y caracteres especiales
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si empieza con 9 y tiene 9 dígitos, agregar +56
        if (preg_match('/^9\d{8}$/', $phone)) {
            return '+56' . $phone;
        }

        // Si empieza con 56, agregar +
        if (preg_match('/^56\d{9}$/', $phone)) {
            return '+' . $phone;
        }

        // Si ya tiene formato internacional
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        return $phone;
    }
}
