<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use Psr\Container\ContainerInterface;
use IamPersistent\GraphQL\Context\CommandContext;
use IamPersistent\GraphQL\Resolver\MasterResolver;

final class MasterResolverFactory
{
    public function __invoke(ContainerInterface $container): MasterResolver
    {
        $commandBus = $container->get('messenger.bus.command');
        $queryBus = $container->get('messenger.bus.query');
        $commandContext = new CommandContext($container->get('config')['graphQL']['commands']);

        return new MasterResolver($commandBus, $queryBus, $commandContext);
    }
}
