<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:dashboards.administrar');
    }

    public function index()
    {
        $dashboards = Dashboard::with('roles')->ordered()->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.dashboards.index', compact('dashboards', 'roles'));
    }

    public function update(Request $request, Dashboard $dashboard)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $dashboard->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: 'bi bi-speedometer2',
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'],
        ]);

        $dashboard->roles()->sync($validated['roles'] ?? []);

        return redirect()
            ->route('admin.dashboards.index')
            ->with('success', 'Dashboard actualizado correctamente.');
    }
}
