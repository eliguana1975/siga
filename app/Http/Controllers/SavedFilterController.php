<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SavedFilterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:80'],
            'route' => ['required', 'string', 'max:120'],
            'params' => ['nullable', 'array'],
            'back' => ['nullable', 'string', 'max:2048'],
        ]);

        abort_unless($this->isAllowedRoute($validated['route']), 422);

        $user = $request->user();
        $preferences = $user->dashboard_preferences ?? [];
        $savedFilters = $preferences['saved_filters'] ?? [];
        $key = Str::slug($validated['key'], '_');
        $params = $this->cleanParams($validated['params'] ?? []);

        $savedFilters[$key] ??= [];
        $savedFilters[$key][] = [
            'id' => (string) Str::uuid(),
            'name' => trim($validated['name']),
            'route' => $validated['route'],
            'params' => $params,
            'created_at' => now()->toDateTimeString(),
        ];

        $preferences['saved_filters'] = $savedFilters;
        $user->forceFill(['dashboard_preferences' => $preferences])->save();

        return redirect($this->safeBack($validated['back'] ?? null, $validated['route'], $params))
            ->with('success', 'Filtro guardado correctamente.');
    }

    public function destroy(Request $request, string $key, string $id)
    {
        $user = $request->user();
        $preferences = $user->dashboard_preferences ?? [];
        $savedFilters = $preferences['saved_filters'] ?? [];
        $key = Str::slug($key, '_');

        $savedFilters[$key] = collect($savedFilters[$key] ?? [])
            ->reject(fn (array $filter) => ($filter['id'] ?? null) === $id)
            ->values()
            ->all();

        $preferences['saved_filters'] = $savedFilters;
        $user->forceFill(['dashboard_preferences' => $preferences])->save();

        return back()->with('success', 'Filtro eliminado correctamente.');
    }

    private function cleanParams(array $params): array
    {
        return collect($params)
            ->reject(fn ($value, $key) => in_array((string) $key, ['page', '_token'], true))
            ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
            ->map(fn ($value) => trim((string) $value))
            ->all();
    }

    private function isAllowedRoute(string $routeName): bool
    {
        return Str::startsWith($routeName, 'admin.')
            && Route::has($routeName)
            && Str::endsWith($routeName, '.index');
    }

    private function safeBack(?string $back, string $routeName, array $params): string
    {
        if ($back && Str::startsWith($back, url('/'))) {
            return $back;
        }

        return route($routeName, $params);
    }
}
