<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\V1\Mahasiswa\AcademicController as MobileAcademicController;
use App\Http\Controllers\Mahasiswa\AkademikController as WebAcademicController;
use App\Services\EdomCompletionService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AcademicPredicateBoundaryTest extends TestCase
{
    public function test_web_predicate_places_exactly_three_point_five_below_cum_laude(): void
    {
        $controller = new WebAcademicController;
        $method = new ReflectionMethod($controller, 'getPredikat');

        $this->assertSame('Sangat Memuaskan', $method->invoke($controller, 3.50));
        $this->assertSame('Pujian', $method->invoke($controller, 3.51));
        $this->assertSame('Memuaskan', $method->invoke($controller, 3.00));
        $this->assertSame('Kurang Memuaskan', $method->invoke($controller, 2.75));
        $this->assertSame('Gagal', $method->invoke($controller, 1.99));
    }

    public function test_mobile_predicate_uses_the_same_boundary(): void
    {
        $controller = new MobileAcademicController(new EdomCompletionService);
        $method = new ReflectionMethod($controller, 'transcriptPredicate');

        $this->assertSame('Sangat Memuaskan', $method->invoke($controller, 3.50));
        $this->assertSame('Pujian', $method->invoke($controller, 3.51));
        $this->assertSame('Memuaskan', $method->invoke($controller, 3.00));
        $this->assertSame('Kurang Memuaskan', $method->invoke($controller, 2.75));
        $this->assertSame('Gagal', $method->invoke($controller, 1.99));
    }
}
