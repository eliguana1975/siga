<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SystemPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function webLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $this->validateCredentials($validated['email'], $validated['password']);

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        $deviceName = trim((string) ($validated['device_name'] ?? 'siga-pwa'));
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'csrf_token' => csrf_token(),
            'redirect_url' => route('admin.index'),
            'user' => $this->userPayload($user->fresh('roles')),
        ]);
    }

    public function webLogout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Sesion web cerrada correctamente.',
            'csrf_token' => csrf_token(),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $this->validateCredentials($validated['email'], $validated['password']);

        $deviceName = trim((string) ($validated['device_name'] ?? 'android-app'));
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user->fresh('roles')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesion cerrada correctamente.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->userPayload($user->fresh('roles')),
        ]);
    }

    public function permissions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $permissions = $user->getAllPermissions()->pluck('name')->sort()->values();

        return response()->json([
            'roles' => $user->roles->pluck('name')->sort()->values(),
            'permissions' => $permissions,
            'permissions_labels' => $permissions
                ->mapWithKeys(fn (string $permission) => [
                    $permission => SystemPermissions::PERMISSIONS[$permission] ?? $permission,
                ]),
            'mobile_menu' => SystemPermissions::mobileMenu($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'base_id' => $user->base_id,
            'is_super_usuario' => $user->isSuperUsuario(),
            'roles' => $user->roles->pluck('name')->sort()->values(),
        ];
    }

    private function validateCredentials(string $email, string $password): User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales invalidas.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Usuario inactivo. Contacte al administrador.'],
            ]);
        }

        return $user;
    }
}
