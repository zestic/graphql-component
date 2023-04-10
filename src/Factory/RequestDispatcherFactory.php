<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Factory;

use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Handler\RequestDispatcher;

final class RequestDispatcherFactory
{
    public function __invoke(ContainerInterface $container): RequestDispatcher
    {
        $config = $container->get('config')['graphQL'];
        [$mutations, $mutationBuses] = $this->normalizeConfig($config['mutations'], $config['buses']['mutation']);
        [$queries, $queryBuses] = $this->normalizeConfig($config['queries'], $config['buses']['query']);
        $messages = array_merge($mutations, $queries);
        $busList = array_merge($mutationBuses, $queryBuses);
        $buses = [];
        foreach ($busList as $bus) {
            $buses[$bus] = $container->get($bus);
        }
        if (isset($config['eventBus'])) {
            $buses['event'] = $container->get($config['eventBus']);
        }

        return new RequestDispatcher($buses, $messages);
    }

    private function normalizeConfig(array $configs, string $defaultBus): array
    {
        $buses = [];
        $messages = [];
        foreach ($configs as $request => $config) {
            $bus = $config['bus'] ?? $defaultBus;
            $buses[$bus] = $bus;

            $messages[$request] = [
                'bus'     => $bus,
                'message' => $config['message'],
            ];
        }

        return [$messages, $buses];
    }
}
