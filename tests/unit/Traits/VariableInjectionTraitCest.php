<?php
declare(strict_types=1);

namespace Tests\Unit\Traits;

use Tests\Fixture\VariableInjectionTestClass;

use UnitTester;

class VariableInjectionTraitCest
{
    public function testConstruct(UnitTester $I)
    {
        $data = [
            'alpha' => 'some information',
            'beta'  => 42,
        ];

        $testClass = new VariableInjectionTestClass($data);

        $I->assertEquals('some information', $testClass->getAlpha());
        $I->assertEquals(42, $testClass->getBeta());
    }
}
