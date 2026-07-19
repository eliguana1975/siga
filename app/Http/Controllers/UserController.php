<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const PROTECTED_USER_NAMES = [
        'SUPERUSUARIO',
        'SUPER USUARIO',
        'SUPERUSER',
        'SUPER USER',
    ];

    private const PROTECTED_ROLE_NAMES = [
        'ADMIN',
        'ADMINISTRADOR',
        'SUPERUSUARIO',
        'SUPER USUARIO',
        'SUPERUSER',
        'SUPER USER',
    ];

    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-usuarios');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = User::with(['roles', 'base.deposito', 'dashboard', 'dashboards'])->orderBy('name');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate(5)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $bases = Base::with('deposito')->where('estado', 'activa')->orderBy('nombre')->get();
        $dashboards = Dashboard::active()->ordered()->get();

        return view('admin.users.index', compact('users', 'roles', 'bases', 'dashboards', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => mb_strtolower(trim((string) $request->input('email')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'base_id' => ['nullable', 'integer', 'exists:bases,id'],
            'dashboard_id' => ['nullable', 'integer', Rule::exists('dashboards', 'id')->where('is_active', true)],
            'dashboard_ids' => ['nullable', 'array'],
            'dashboard_ids.*' => ['integer', Rule::exists('dashboards', 'id')->where('is_active', true)],
            'puede_ver_todos_inventarios' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors($validator)
                ->withInput($request->except('password'))
                ->with('open_modal', 'createUserModal');
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'must_change_password' => true,
            'password_changed_at' => null,
            'estado' => $request->input('estado', 'activo'),
            'base_id' => $request->input('base_id'),
            'dashboard_id' => $request->input('dashboard_id'),
            'puede_ver_todos_inventarios' => $request->boolean('puede_ver_todos_inventarios'),
        ]);

        $this->syncUserRole($user, $request->input('role_id'));
        $this->syncUserDashboards($user, $request->input('dashboard_ids', []), $request->input('dashboard_id'));

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => mb_strtolower(trim((string) $request->input('email')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'base_id' => ['nullable', 'integer', 'exists:bases,id'],
            'dashboard_id' => ['nullable', 'integer', Rule::exists('dashboards', 'id')->where('is_active', true)],
            'dashboard_ids' => ['nullable', 'array'],
            'dashboard_ids.*' => ['integer', Rule::exists('dashboards', 'id')->where('is_active', true)],
            'puede_ver_todos_inventarios' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors($validator)
                ->withInput($request->except('password'))
                ->with('open_modal', 'editUserModal-' . $user->id);
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->estado = $request->input('estado', 'activo');
        $user->base_id = $request->input('base_id');
        $user->dashboard_id = $request->input('dashboard_id');
        $user->puede_ver_todos_inventarios = $request->boolean('puede_ver_todos_inventarios');

        if ($request->filled('password')) {
            $user->password = $request->input('password');
            $user->must_change_password = true;
            $user->password_changed_at = null;
        }

        $user->save();

        $this->syncUserRole($user, $request->input('role_id'));
        $this->syncUserDashboards($user, $request->input('dashboard_ids', []), $request->input('dashboard_id'));

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::with('roles')->findOrFail($id);

        if (auth()->id() === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($this->isProtectedSuperUser($user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'No se puede eliminar el superusuario.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    private function syncUserRole(User $user, mixed $roleId): void
    {
        if (empty($roleId)) {
            $user->syncRoles([]);
            return;
        }

        $role = Role::findOrFail($roleId);
        $user->syncRoles([$role->name]);
    }

    private function syncUserDashboards(User $user, mixed $dashboardIds, mixed $defaultDashboardId): void
    {
        $ids = collect((array) $dashboardIds)
            ->filter()
            ->map(fn ($id) => (int) $id);

        if (! empty($defaultDashboardId)) {
            $ids->push((int) $defaultDashboardId);
        }

        $user->dashboards()->sync($ids->unique()->values()->all());
    }

    private function isProtectedSuperUser(User $user): bool
    {
        if ((int) $user->id === 1) {
            return true;
        }

        if (in_array(mb_strtoupper($user->name, 'UTF-8'), self::PROTECTED_USER_NAMES, true)) {
            return true;
        }

        return $user->roles
            ->pluck('name')
            ->map(fn (string $roleName) => mb_strtoupper($roleName, 'UTF-8'))
            ->intersect(self::PROTECTED_ROLE_NAMES)
            ->isNotEmpty();
    }
}
