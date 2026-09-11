<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property int $id_rol
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Rol $rol
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdRol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol',
        'phone',
        'two_factor_enabled',
        'two_factor_channel',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /**
     * ¿Este usuario puede hacer esto?
     *
     * Los permisos viven en roles.permisos, un JSON con nombres del estilo
     * «clientes.crear». El comodin «*» lo puede todo y es lo que tiene el
     * administrador: asi, cada permiso nuevo que se invente no hay que
     * acordarse de sumarselo.
     *
     * Sin rol NO se puede nada. Es a proposito: un usuario sin rol es un
     * registro a medio hacer, y ante la duda el sistema no abre la puerta.
     */
    public function puede(string $permiso): bool
    {
        $permisos = $this->rol?->permisos;

        if (! is_array($permisos)) {
            return false;
        }

        if (in_array('*', $permisos, true)) {
            return true;
        }

        if (in_array($permiso, $permisos, true)) {
            return true;
        }

        // «clientes.*» concede todo lo de clientes sin listarlo pieza a pieza.
        $modulo = explode('.', $permiso)[0];

        return in_array("{$modulo}.*", $permisos, true);
    }

    /** Todos los permisos efectivos, para mandarlos al panel. */
    public function permisos(): array
    {
        return is_array($this->rol?->permisos) ? $this->rol->permisos : [];
    }

    /**
     * Métodos requeridos por AdminLTE para el menú de usuario
     */
    
    /**
     * Obtener la descripción del usuario para AdminLTE
     */
    public function adminlte_desc(): string
    {
        return $this->rol ? $this->rol->nombre : 'Usuario';
    }

    /**
     * Obtener la imagen de perfil del usuario para AdminLTE
     */
    public function adminlte_image(): string
    {
        // Puedes cambiar esto por una imagen real del usuario
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=e94560&color=fff&size=128';
    }

    /**
     * Obtener la URL del perfil del usuario para AdminLTE
     */
    public function adminlte_profile_url(): string
    {
        return '#'; // Cambiar a route('profile') si tienes una página de perfil
    }
}
