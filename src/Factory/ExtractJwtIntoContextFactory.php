<?php
declare(strict_types=1);

namespace Zestic\Factory;

use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Interactor\ExtractJwtIntoContext;

class ExtractJwtIntoContextFactory
{
    public function __invoke(ContainerInterface $container): ExtractJwtIntoContext
    {

    }
}