<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use IamPersistent\GraphQL\Handler\RequestDispatcher;
use Psr\Container\ContainerInterface;
use IamPersistent\GraphQL\Resolver\MasterResolver;

final class MasterResolverFactory
{
    public function __invoke(ContainerInterface $container): MasterResolver
    {
        $requestDispatcher = $container->get(RequestDispatcher::class);

        return new MasterResolver($requestDispatcher);
    }
}
