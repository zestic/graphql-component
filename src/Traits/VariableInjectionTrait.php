<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Traits;

use ReflectionProperty;

trait VariableInjectionTrait
{
    public function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            $reflectionProperty = new ReflectionProperty($this, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($this, $value);
            $reflectionProperty->setAccessible(false);
        }
    }
}
