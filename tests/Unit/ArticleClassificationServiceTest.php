<?php

namespace Tests\Unit;

use App\Services\ArticleClassificationService;
use Tests\TestCase;

class ArticleClassificationServiceTest extends TestCase
{
    public function test_detects_ropa_epp_category_keywords(): void
    {
        $service = new ArticleClassificationService();

        $this->assertTrue($service->isRopaEppCategory('Ropa y EPP'));
        $this->assertTrue($service->isRopaEppCategory('Indumentaria tecnica'));
        $this->assertFalse($service->isRopaEppCategory('Repuestos motor'));
    }

    public function test_detects_cubierta_text_keywords(): void
    {
        $service = new ArticleClassificationService();

        $this->assertTrue($service->isCubiertaText('Cubierta radial', null, null));
        $this->assertTrue($service->isCubiertaText('Neumatico', null, null));
        $this->assertFalse($service->isCubiertaText('Filtro aceite', null, null));
    }
}
