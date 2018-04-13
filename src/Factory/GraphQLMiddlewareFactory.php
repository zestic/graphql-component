<?php

namespace Zestic\GraphQL\Factory;

use GraphQLMiddleware\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Interactor\ExtractJwtIntoContext;
use Zestic\GraphQL\Middleware\GraphQLMiddleware;

class GraphQLMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $processor = $container->get("graphql.processor");

        $config = $container->get("config");
        if (! isset($config['graphql']['uri'])) {
            throw ServiceNotCreatedException::invalidMiddlewareConfigurationProvided();
        }

        $extractJwtIntoContext = $container->get(ExtractJwtIntoContext::class);

        return new GraphQLMiddleware($processor, $config['graphql']['uri'], $extractJwtIntoContext);
    }
}
