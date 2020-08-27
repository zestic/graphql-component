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
        $commandBus = $container->get('messenger.command.bus');
        $queryBus = $container->get('messenger.query.bus');
        $commandContext = new CommandContext($container->get('config')['graphQL']['commands']);

        return new MasterResolver($commandBus, $queryBus, $commandContext);
    }
}
