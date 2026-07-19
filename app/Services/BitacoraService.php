<?php

namespace App\Services;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BitacoraService
{
    private const CAMPOS_SENSIBLES = [
        'password',
        'remember_token',
        'token',
        'api_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $atributosOriginales = [];

    /**
     * @var array<class-string<Model>, bool>
     */
    private static array $modelosIgnorados = [
        Bitacora::class => true,
    ];

    public static function recordarOriginal(Model $model): void
    {
        if (self::debeIgnorarse($model)) {
            return;
        }

        self::$atributosOriginales[spl_object_id($model)] = self::limpiarAtributos($model->getOriginal());
    }

    public static function registrarModelo(string $accion, Model $model): void
    {
        if (self::debeIgnorarse($model)) {
            return;
        }

        $anteriores = null;
        $nuevos = null;

        if ($accion === 'crear') {
            $nuevos = self::limpiarAtributos($model->getAttributes());
        }

        if ($accion === 'editar') {
            $cambios = Arr::except($model->getChanges(), ['updated_at']);

            if (empty($cambios)) {
                self::olvidarOriginal($model);
                return;
            }

            $originales = self::$atributosOriginales[spl_object_id($model)] ?? [];
            $anteriores = self::soloCampos(array_keys($cambios), $originales);
            $nuevos = self::limpiarAtributos($cambios);
        }

        if ($accion === 'eliminar') {
            $anteriores = self::limpiarAtributos($model->getOriginal());
        }

        self::registrar(
            accion: $accion,
            descripcion: self::descripcionModelo($accion, $model),
            modelo: $model,
            datosAnteriores: $anteriores,
            datosNuevos: $nuevos,
        );

        self::olvidarOriginal($model);
    }

    public static function registrar(
        string $accion,
        string $descripcion,
        ?Model $modelo = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?array $metadata = null,
        ?string $modulo = null,
    ): void {
        try {
            $user = Auth::user();
            $request = request();

            Bitacora::query()->create([
                'user_id' => $user?->id,
                'usuario_nombre' => $user?->name,
                'accion' => $accion,
                'modulo' => $modulo ?? ($modelo ? self::moduloModelo($modelo) : self::moduloRequest()),
                'entidad_type' => $modelo ? $modelo::class : null,
                'entidad_id' => $modelo?->getKey(),
                'descripcion' => $descripcion,
                'datos_anteriores' => self::limpiarAtributos($datosAnteriores ?? []),
                'datos_nuevos' => self::limpiarAtributos($datosNuevos ?? []),
                'metadata' => self::limpiarAtributos($metadata ?? []),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'route_name' => $request?->route()?->getName(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('No se pudo registrar la bitacora.', [
                'accion' => $accion,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private static function debeIgnorarse(Model $model): bool
    {
        return isset(self::$modelosIgnorados[$model::class]);
    }

    private static function olvidarOriginal(Model $model): void
    {
        unset(self::$atributosOriginales[spl_object_id($model)]);
    }

    /**
     * @param array<string, mixed> $atributos
     * @return array<string, mixed>
     */
    private static function limpiarAtributos(array $atributos): array
    {
        return collect($atributos)
            ->reject(fn ($value, $key) => in_array(Str::lower((string) $key), self::CAMPOS_SENSIBLES, true))
            ->map(fn ($value) => self::normalizarValor($value))
            ->all();
    }

    /**
     * @param array<int, string> $campos
     * @param array<string, mixed> $atributos
     * @return array<string, mixed>
     */
    private static function soloCampos(array $campos, array $atributos): array
    {
        return self::limpiarAtributos(Arr::only($atributos, $campos));
    }

    private static function normalizarValor(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : get_class($value);
        }

        return $value;
    }

    private static function descripcionModelo(string $accion, Model $model): string
    {
        $usuario = Auth::user()?->name ?? 'Sistema';
        $entidad = self::nombreEntidad($model);
        $id = $model->getKey();

        return trim("{$usuario} {$accion} {$entidad}" . ($id ? " #{$id}" : ''));
    }

    private static function nombreEntidad(Model $model): string
    {
        return Str::of(class_basename($model))
            ->snake(' ')
            ->replace('_', ' ')
            ->lower()
            ->toString();
    }

    private static function moduloModelo(Model $model): string
    {
        return Str::of($model->getTable())->replace('_', ' ')->lower()->toString();
    }

    private static function moduloRequest(): ?string
    {
        $routeName = request()?->route()?->getName();

        if (! $routeName) {
            return null;
        }

        return Str::of($routeName)
            ->replace('admin.', '')
            ->beforeLast('.')
            ->replace('.', ' ')
            ->replace('-', ' ')
            ->lower()
            ->toString();
    }
}
