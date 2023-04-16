<?php
declare(strict_types=1);

namespace Zestic\GraphQL;

use Netglue\PsrContainer\Messenger\Container\MessageBusStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageHandlerMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageSenderMiddlewareStaticFactory;
use Zestic\GraphQL\Locator\MessengerBusLocatorFactory;
use Zestic\GraphQL\Locator\MutationBusLocator;
use Zestic\GraphQL\Locator\QueryBusLocator;

final class ConfigProcessor
{
    public function __invoke(array $config): array
    {
        $config = $this->addFactories($config);
        $config = $this->addGraphQL($config);
        $config = $this->addMessenger($config);

        return $config;
    }

    private function addFactories(array $config): array
    {
        $newConfigs = [
            'messenger.graphql.mutation.bus'                    => [
                MessageBusStaticFactory::class,
                'messenger.graphql.mutation.bus',
            ],
            'messenger.graphql.mutation.bus.sender-middleware'  => [
                MessageSenderMiddlewareStaticFactory::class,
                'messenger.graphql.mutation.bus',
            ],
            'messenger.graphql.mutation.bus.handler-middleware' => [
                MessageHandlerMiddlewareStaticFactory::class,
                'messenger.graphql.mutation.bus',
            ],
            MutationBusLocator::class                           =>
                new MessengerBusLocatorFactory(
                    'messenger.graphql.mutation.bus'
                ),
            'messenger.graphql.query.bus'                    => [
                MessageBusStaticFactory::class,
                'messenger.graphql.query.bus',
            ],
            'messenger.graphql.query.bus.sender-middleware'  => [
                MessageSenderMiddlewareStaticFactory::class,
                'messenger.graphql.query.bus',
            ],
            'messenger.graphql.query.bus.handler-middleware' => [
                MessageHandlerMiddlewareStaticFactory::class,
                'messenger.graphql.query.bus',
            ],
            QueryBusLocator::class                           =>
                new MessengerBusLocatorFactory(
                    'messenger.graphql.query.bus'
                ),
            \Zestic\GraphQL\Handler\RequestDispatcher::class =>
                \Zestic\GraphQL\Factory\RequestDispatcherFactory::class,
            \Zestic\GraphQL\Resolver\MasterResolver::class   =>
                \Zestic\GraphQL\Factory\MasterResolverFactory::class,
        ];
        $config['dependencies']['factories'] =
            array_merge($config['dependencies']['factories'], $newConfigs);

        return $config;
    }

    private function addGraphQL(array $config): array
    {
        if (!isset($config['graphQL']['buses']['mutation'])) {
            $config['graphQL']['buses']['mutation'] = 'messenger.graphql.mutation.bus';
        }
        if (!isset($config['graphQL']['buses']['query'])) {
            $config['graphQL']['buses']['query'] = 'messenger.graphql.query.bus';
        }

        if (!isset($config['graphQL']['serverConfig']['fieldResolver'])) {
            $config['graphQL']['serverConfig']['fieldResolver'] = \Zestic\GraphQL\Resolver\MasterResolver::class;
        }

        return $config;
    }

    private function addBuses(array $config): array
    {
        $buses = [
            'messenger.graphql.mutation.bus' => $this->mutationConfig($config),
            'messenger.graphql.query.bus'    => $this->queryConfig($config),
        ];
        if ($config['symfony']['messenger']['buses']) {
            $buses = array_merge($config['symfony']['messenger']['buses'], $buses);
        }
        $config['symfony']['messenger']['buses'] = $buses;

        return $config;
    }

    private function addMessenger(array $config): array
    {
        $config = $this->addBuses($config);

        return $config;
    }

    private function getHandlers(array $operations): array
    {
        $handlers = [];
        foreach ($operations as $key => $operation) {
            $operationHandlers = $operation['handlers'];
            if (!is_array($operationHandlers)) {
                $operationHandlers = [$operationHandlers];
            }
            $handlers[$operation['message']] = $operationHandlers;
        }

        return $handlers;
    }

    private function mutationConfig(array $config): array
    {
        return [
            'allows_zero_handlers' => false,
            'handler_locator'      => MutationBusLocator::class,
            'handlers'             => $this->mutationHandlersConfig($config),
            'middleware'           => [
                'messenger.graphql.mutation.bus.sender-middleware',
                'messenger.graphql.mutation.bus.handler-middleware',
            ],
            'routes'               => [
            ],
        ];
    }

    private function mutationHandlersConfig(array $config): array
    {
        return $this->getHandlers($config['graphQL']['mutations']);
    }

    private function queryConfig(array $config): array
    {
        return [
            'allows_zero_handlers' => false,
            'handler_locator'      => QueryBusLocator::class,
            'handlers'             => $this->queryHandlersConfig($config),
            'middleware'           => [
                'messenger.graphql.query.bus.sender-middleware',
                'messenger.graphql.query.bus.handler-middleware',
            ],
            'routes'               => [
            ],
        ];
    }

    private function queryHandlersConfig(array $config): array
    {
        return $this->getHandlers($config['graphQL']['queries']);
    }
}
