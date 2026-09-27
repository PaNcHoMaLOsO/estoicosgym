<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'channel',
        'phone',
        'is_used',
        'intentos',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'intentos' => 'integer',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el código ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verificar si el código es válido
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Marcar código como usado
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Generar un nuevo código de 6 dígitos
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /** Intentos fallidos que aguanta un código antes de anularse. */
    public const INTENTOS = 5;

    /**
     * El código tal cual, solo en memoria: para mandarlo por WhatsApp o SMS.
     * En la base queda su huella, nunca el código.
     */
    public ?string $codigoPlano = null;

    /** La huella que se guarda: HMAC con la APP_KEY, comparable sin descifrar. */
    public static function huella(string $codigo): string
    {
        return hash_hmac('sha256', trim($codigo), (string) config('app.key'));
    }

    /**
     * Crear código de verificación para un usuario
     */
    public static function createFor(User $user, string $type = 'login', ?string $channel = null): self
    {
        // Invalidar códigos anteriores del mismo tipo
        self::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $channel = $channel ?? $user->two_factor_channel ?? 'whatsapp';

        $codigo = self::generateCode();

        $verificacion = self::create([
            'user_id' => $user->id,
            'code' => self::huella($codigo),
            'type' => $type,
            'channel' => $channel,
            'phone' => $user->phone,
            'expires_at' => now()->addMinutes(10), // Expira en 10 minutos
        ]);
        $verificacion->codigoPlano = $codigo;

        return $verificacion;
    }

    /**
     * Verificar código
     */
    public static function verify(User $user, string $code, string $type = 'login'): ?self
    {
        // El vigente: se invalidan los anteriores al crear uno nuevo.
        $verification = self::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $verification) {
            return null;
        }

        if (hash_equals($verification->code, self::huella($code))) {
            $verification->markAsUsed();

            return $verification;
        }

        // Fallido: al quinto, el código ya no sirve y hay que pedir otro.
        $verification->intentos++;
        $verification->is_used = $verification->intentos >= self::INTENTOS;
        $verification->save();

        return null;
    }

    /**
     * Scope para códigos válidos
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
