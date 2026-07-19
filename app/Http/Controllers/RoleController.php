<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Dashboard;
use App\Support\SystemPermissions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-roles');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->syncPermissionCatalog();

        $search = trim((string) $request->input('search', ''));
        $query = Role::with('permissions')->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $roles = $query->paginate(5)->withQueryString();
        $dashboards = Dashboard::active()->ordered()->get();
        $roleDashboardIds = DB::table('dashboard_role')
            ->whereIn('role_id', $roles->getCollection()->pluck('id'))
            ->get()
            ->groupBy('role_id')
            ->map(fn ($rows) => $rows->pluck('dashboard_id')->map(fn ($id) => (int) $id)->all());
        $permissions = Permission::query()
            ->whereIn('name', array_keys(SystemPermissions::permissions()))
            ->orderBy('name')
            ->get();
        $permissionLabels = SystemPermissions::permissions();
        $permissionGroups = SystemPermissions::groups();
        $groupedPermissions = collect($permissionGroups)
            ->mapWithKeys(function (array $groupPermissions, string $groupName) use ($permissions, $permissionLabels) {
                $sortedPermissions = $permissions
                    ->whereIn('name', $groupPermissions)
                    ->sortBy(fn (Permission $permission): string => $permissionLabels[$permission->name] ?? $permission->name)
                    ->values();

                return [$groupName => $sortedPermissions];
            });

        return view('admin.roles.index', compact('roles', 'permissions', 'permissionLabels', 'permissionGroups', 'groupedPermissions', 'dashboards', 'roleDashboardIds', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')), 'UTF-8')]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'dashboard_ids' => ['nullable', 'array'],
            'dashboard_ids.*' => ['integer', Rule::exists('dashboards', 'id')->where('is_active', true)],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.roles.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createRoleModal');
        }

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($this->permissionNamesFromIds($request->input('permissions', [])));
        $this->syncRoleDashboards($role, $request->input('dashboard_ids', []));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')), 'UTF-8')]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'dashboard_ids' => ['nullable', 'array'],
            'dashboard_ids.*' => ['integer', Rule::exists('dashboards', 'id')->where('is_active', true)],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.roles.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editRoleModal-' . $role->id);
        }

        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($this->permissionNamesFromIds($request->input('permissions', [])));
        $this->syncRoleDashboards($role, $request->input('dashboard_ids', []));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if (strtoupper($role->name) === 'ADMIN') {
            return redirect()->route('admin.roles.index')->with('error', 'No se puede eliminar el rol administrador.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * @param array<int, int|string> $permissionIds
     * @return array<int, string>
     */
    private function permissionNamesFromIds(array $permissionIds): array
    {
        if (empty($permissionIds)) {
            return [];
        }

        return Permission::query()
            ->whereIn('id', $permissionIds)
            ->pluck('name')
            ->all();
    }

    private function syncPermissionCatalog(): void
    {
        foreach (array_keys(SystemPermissions::permissions()) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }

    private function syncRoleDashboards(Role $role, array $dashboardIds): void
    {
        $ids = collect($dashboardIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        DB::table('dashboard_role')->where('role_id', $role->id)->delete();

        if ($ids->isEmpty()) {
            return;
        }

        $now = now();
        DB::table('dashboard_role')->insert($ids->map(fn (int $dashboardId) => [
            'dashboard_id' => $dashboardId,
            'role_id' => $role->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }
}
