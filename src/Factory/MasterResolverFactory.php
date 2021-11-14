<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Factory;

use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Handler\RequestDispatcher;
use Zestic\GraphQL\Resolver\MasterResolver;

final class MasterResolverFactory
{
    public function __invoke(ContainerInterface $container): MasterResolver
    {
        $requestDispatcher = $container->get(RequestDispatcher::class);

        return new MasterResolver($requestDispatcher);
    }
}
