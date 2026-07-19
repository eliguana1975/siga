<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MobileAuthApiTest extends TestCase
{
    private const API_BASE = 'http://localhost/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            config([
                'database.connections.mysql.database' => env('SIGA_TEST_DB', 'sigas'),
            ]);
            config(['database.default' => 'mysql']);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        // Evita interferencias entre ejecuciones al reutilizar la base local.
        DB::table('personal_access_tokens')->delete();
        User::query()->where('email', 'like', 'mobile-test-%@example.com')->delete();
    }

    public function test_login_returns_token_and_user_payload(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile-test-' . Str::uuid() . '@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->postJson(self::API_BASE . '/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
            'device_name' => 'android-test',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email', 'base_id', 'is_super_usuario', 'roles'],
            ]);

        $this->assertSame(1, $user->fresh()->tokens()->count());
        $this->assertEquals($user->id, (int) $response->json('user.id'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile-test-' . Str::uuid() . '@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $this->postJson(self::API_BASE . '/auth/login', [
            'email' => $user->email,
            'password' => 'bad-pass',
        ])->assertStatus(422);
    }

    public function test_me_and_permissions_require_valid_token_and_return_data(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile-test-' . Str::uuid() . '@example.com',
            'password' => bcrypt('12345678'),
        ]);

        Permission::findOrCreate('articulos.ver', 'web');
        $user->givePermissionTo('articulos.ver');

        $token = $user->createToken('android-test')->plainTextToken;

        $this->getJson(self::API_BASE . '/me')
            ->assertUnauthorized();

        $this->getJson(self::API_BASE . '/me/permisos')
            ->assertUnauthorized();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/me')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);

        $this->withToken($token)
            ->getJson(self::API_BASE . '/me/permisos')
            ->assertOk()
            ->assertJsonFragment(['articulos.ver']);
    }

    public function test_logout_revokes_current_access_token(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile-test-' . Str::uuid() . '@example.com',
        ]);
        $token = $user->createToken('android-test')->plainTextToken;

        $this->withToken($token)
            ->postJson(self::API_BASE . '/auth/logout')
            ->assertOk()
            ->assertJsonFragment(['message' => 'Sesion cerrada correctamente.']);

        $this->assertSame(0, $user->fresh()->tokens()->count());
    }
}
