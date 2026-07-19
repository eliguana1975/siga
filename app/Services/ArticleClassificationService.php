<?php

namespace App\Services;

use App\Models\Articulo;
use Illuminate\Support\Str;

class ArticleClassificationService
{
    private const CUBIERTA_KEYWORDS = [
        'cubierta',
        'cuvierta',
        'kubierta',
        'kuvierta',
        'cub',
        'neumatic',
        'neumatico',
    ];

    private const CUBIERTA_FUZZY_TARGETS = [
        'cubierta',
        'cubiertas',
        'neumatico',
        'neumaticos',
    ];

    private const CUBIERTA_SQL_KEYWORDS = [
        'cub',
        'cubierta',
        'cuvierta',
        'kubierta',
        'kuvierta',
        'neumatic',
    ];

    private const MEDIDA_CUBIERTA_PATTERNS = [
        '/\b\d{3}\s*[\/\-\s]\s*\d{2,3}\s*(?:r|[\/\-\s])?\s*\d{2}(?:[.,]\d)?\b/i',
        '/\b\d{1,2}(?:[.,]\d{1,2})?\s*r\s*\d{2}(?:[.,]\d)?\b/i',
        '/\b\d{1,2}[.,]\d{1,2}\s*-\s*\d{2}(?:[.,]\d)?\b/i',
        '/\b\d{4}\s*x\s*\d{2}(?:[.,]\d)?\b/i',
    ];

    private const ROPA_EPP_CATEGORY_KEYWORDS = [
        'ropa',
        'epp',
        'indument',
        'protec',
        'uniform',
    ];

    private const MATAFUEGO_KEYWORDS = [
        'matafuego',
        'matafuegos',
        'mata fuego',
        'mata fuegos',
        'extintor',
        'extintores',
        'extinguidor',
        'extinguidores',
        'extinguisher',
    ];

    public function applyNoCubiertaNoRopaEppFilter($query): void
    {
        $query
            ->where(function ($query) {
                $query->whereNull('es_ropa_epp')
                    ->orWhere('es_ropa_epp', false);
            })
            ->whereDoesntHave('categoria', function ($categoria) {
                $categoria->where(function ($categoria) {
                    foreach (self::ROPA_EPP_CATEGORY_KEYWORDS as $keyword) {
                        $categoria->orWhere('nombre', 'like', '%' . $keyword . '%');
                    }
                });
            })
            ->whereDoesntHave('categoria', function ($categoria) {
                $categoria->where(function ($categoria) {
                    foreach (self::CUBIERTA_SQL_KEYWORDS as $keyword) {
                        $categoria->orWhere('nombre', 'like', '%' . $keyword . '%');
                    }
                });
            })
            ->where(function ($query) {
                foreach (self::CUBIERTA_SQL_KEYWORDS as $keyword) {
                    $query->where('nombre', 'not like', '%' . $keyword . '%')
                        ->where(function ($query) use ($keyword) {
                            $query->whereNull('codigo_producto')
                                ->orWhere('codigo_producto', 'not like', '%' . $keyword . '%');
                        });
                }
            });
    }

    public function applyCubiertaFilter($query): void
    {
        $query->whereHas('categoria', function ($categoria) {
            $categoria->where(function ($categoria) {
                foreach (self::CUBIERTA_SQL_KEYWORDS as $keyword) {
                    $categoria->orWhere('nombre', 'like', '%' . $keyword . '%');
                }
            });
        })->orWhere(function ($query) {
            foreach (self::CUBIERTA_SQL_KEYWORDS as $keyword) {
                $query->orWhere('nombre', 'like', '%' . $keyword . '%')
                    ->orWhere('codigo_producto', 'like', '%' . $keyword . '%');
            }
        });
    }

    public function isCubiertaArticulo(Articulo $articulo): bool
    {
        return $this->isCubiertaText(
            $articulo->nombre,
            $articulo->codigo_producto,
            $articulo->categoria?->nombre
        );
    }

    public function isRopaEppArticulo(?Articulo $articulo): bool
    {
        if (! $articulo) {
            return false;
        }

        $articulo->loadMissing('categoria');

        return (bool) ($articulo->es_ropa_epp ?? false)
            || $this->isRopaEppCategory($articulo->categoria?->nombre);
    }

    public function isMatafuegoArticulo(?Articulo $articulo): bool
    {
        if (! $articulo) {
            return false;
        }

        return $this->isMatafuegoText(
            $articulo->nombre,
            $articulo->codigo_producto,
            $articulo->categoria?->nombre
        );
    }

    public function isMatafuegoText(?string $nombre, ?string $codigo, ?string $categoria): bool
    {
        $texto = $this->normalizarTexto(collect([$nombre, $codigo, $categoria])->filter()->implode(' '));

        if ($texto === '') {
            return false;
        }

        foreach (self::MATAFUEGO_KEYWORDS as $keyword) {
            if (str_contains($texto, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public function isCubiertaText(?string $nombre, ?string $codigo, ?string $categoria): bool
    {
        $texto = $this->normalizarTexto(collect([$nombre, $codigo, $categoria])->filter()->implode(' '));

        if ($texto === '') {
            return false;
        }

        foreach (self::CUBIERTA_KEYWORDS as $keyword) {
            if (preg_match('/(^|[^a-z0-9])' . preg_quote($keyword, '/') . '([^a-z0-9]|$)/i', $texto)) {
                return true;
            }
        }

        if ($this->tienePalabraSimilarACubierta($texto)) {
            return true;
        }

        if ($this->tieneMedidaCubierta($texto)) {
            return true;
        }

        return false;
    }

    private function tienePalabraSimilarACubierta(string $texto): bool
    {
        $palabras = preg_split('/[^a-z0-9]+/i', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($palabras as $palabra) {
            if (strlen($palabra) < 6 || ! preg_match('/^[ckn]/i', $palabra)) {
                continue;
            }

            foreach (self::CUBIERTA_FUZZY_TARGETS as $target) {
                if (levenshtein($palabra, $target) <= 2) {
                    return true;
                }
            }
        }

        return false;
    }

    private function tieneMedidaCubierta(string $texto): bool
    {
        foreach (self::MEDIDA_CUBIERTA_PATTERNS as $pattern) {
            if (preg_match($pattern, $texto, $matches) && $this->medidaTieneRangosValidos($matches[0])) {
                return true;
            }
        }

        return false;
    }

    private function medidaTieneRangosValidos(string $medida): bool
    {
        preg_match_all('/\d+(?:[.,]\d+)?/', $medida, $matches);
        $numeros = array_map(fn (string $numero) => (float) str_replace(',', '.', $numero), $matches[0] ?? []);

        if (count($numeros) >= 3) {
            [$ancho, $perfil, $llanta] = $numeros;

            return $ancho >= 145 && $ancho <= 445
                && $perfil >= 25 && $perfil <= 95
                && $llanta >= 12 && $llanta <= 25;
        }

        if (count($numeros) === 2) {
            [$ancho, $llanta] = $numeros;

            if ($ancho >= 700 && $ancho <= 1400 && $llanta >= 12 && $llanta <= 25) {
                return true;
            }

            return $ancho >= 7 && $ancho <= 14
                && $llanta >= 12 && $llanta <= 25;
        }

        return false;
    }

    public function isRopaEppCategory(?string $categoria): bool
    {
        $texto = $this->normalizarTexto((string) $categoria);

        if ($texto === '') {
            return false;
        }

        foreach (self::ROPA_EPP_CATEGORY_KEYWORDS as $keyword) {
            if (str_contains($texto, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function normalizarTexto(string $texto): string
    {
        return mb_strtolower(trim(Str::ascii($texto)), 'UTF-8');
    }
}
