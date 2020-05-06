<?php
declare(strict_types=1);

namespace Tests\Fixture;

use IamPersistent\GraphQL\Traits\VariableInjectionTrait;

final class VariableInjectionTestClass
{
    use VariableInjectionTrait;

    /** @var string */
    private $alpha;
    /** @var int */
    private $beta;

    public function getAlpha(): string
    {
        return $this->alpha;
    }

    public function setAlpha(string $alpha): VariableInjectionTestClass
    {
        $this->alpha = $alpha;

        return $this;
    }

    public function getBeta(): int
    {
        return $this->beta;
    }

    public function setBeta(int $beta): VariableInjectionTestClass
    {
        $this->beta = $beta;

        return $this;
    }
}
