<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use IamPersistent\GraphQL\Context\CommandContext;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\QueryBus;

final class MasterResolver
{
    /** @var array */
    private $commands = [];
    /** @var CommandBus */
    private $commandBus;
    /** @var QueryBus */
    private $queryBus;

    public function __construct(CommandBus $commandBus, QueryBus $queryBus, CommandContext $commands)
    {
        $this->commandBus = $commandBus;
        $this->commands = $commands;
        $this->queryBus = $queryBus;
    }

    public function __invoke($val, $args, $context, ResolveInfo $info)
    {
        if ($command = $this->commands->getCommand($info)) {
            $this->commandBus->dispatch($command);
        }

        if ($query = $this->commands->getQuery($info)) {
            return $this->queryBus->dispatch($query);
        }

        return $info->variableValues[$fieldName] ?? null;
    }
}