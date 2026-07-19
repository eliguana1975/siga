<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role;

class Dashboard extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'dashboard_role')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dashboard_user')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function availableFor(User $user)
    {
        $query = static::query()->active()->with('roles')->ordered();

        if (! $user->isSuperUsuario()) {
            $roleIds = $user->roles->pluck('id')->all();
            $defaultDashboardId = $user->dashboard_id;
            $userDashboardIds = $user->dashboards()->pluck('dashboards.id')->all();

            $query->where(function (Builder $dashboards) use ($roleIds, $defaultDashboardId, $userDashboardIds) {
                $dashboards->whereHas('roles', fn (Builder $roles) => $roles->whereIn('roles.id', $roleIds));

                if ($defaultDashboardId) {
                    $dashboards->orWhere($dashboards->getModel()->getQualifiedKeyName(), $defaultDashboardId);
                }

                if (! empty($userDashboardIds)) {
                    $dashboards->orWhereIn($dashboards->getModel()->getQualifiedKeyName(), $userDashboardIds);
                }
            });
        }

        return $query->get();
    }
}
