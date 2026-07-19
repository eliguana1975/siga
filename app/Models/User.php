<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'must_change_password', 'password_changed_at', 'estado', 'base_id', 'puede_ver_todos_inventarios', 'dashboard_preferences', 'dashboard_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    private const SUPER_USER_NAMES = [
        'SUPERUSUARIO',
        'SUPER USUARIO',
        'SUPERUSER',
        'SUPER USER',
    ];

    private const SUPER_USER_ROLE_NAMES = [
        'SUPERUSUARIO',
        'SUPER USUARIO',
        'SUPERUSER',
        'SUPER USER',
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
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'puede_ver_todos_inventarios' => 'boolean',
            'dashboard_preferences' => 'array',
        ];
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function dashboards(): BelongsToMany
    {
        return $this->belongsToMany(Dashboard::class, 'dashboard_user')->withTimestamps();
    }

    public function empleados()
    {
        return $this->HasOne(Empleado::class); // return $this->hasOne(Empleado::class);
    }

    public function comprasTmp()
    {
        return $this->hasMany(CompraTmp::class, 'usuario_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'usuario_id');
    }

    public function isSuperUsuario(): bool
    {
        if ((int) $this->id === 1) {
            return true;
        }

        if (in_array(mb_strtoupper($this->name, 'UTF-8'), self::SUPER_USER_NAMES, true)) {
            return true;
        }

        return $this->roles
            ->pluck('name')
            ->map(fn (string $roleName) => mb_strtoupper($roleName, 'UTF-8'))
            ->intersect(self::SUPER_USER_ROLE_NAMES)
            ->isNotEmpty();
    }

    public function isActive(): bool
    {
        return ($this->estado ?? 'activo') === 'activo';
    }

    public function requiresPasswordChange(): bool
    {
        return (bool) $this->must_change_password;
    }

     
}
