<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\QueryBus;
use Psr\Container\ContainerInterface;
use IamPersistent\GraphQL\Context\CommandContext;
use IamPersistent\GraphQL\Resolver\MasterResolver;

final class MasterResolverFactory
{
    public function __invoke(ContainerInterface $container): MasterResolver
    {
        $commandBus = $container->get(CommandBus::class);
        $queryBus = $container->get(QueryBus::class);
        $commandContext = new CommandContext($container->get('config')['graphQL']['commands']);

        return new MasterResolver($commandBus, $queryBus, $commandContext);
    }
}