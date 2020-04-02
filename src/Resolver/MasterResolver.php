<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use IamPersistent\GraphQL\Context\CommandContext;
use Symfony\Component\Messenger\MessageBusInterface;

final class MasterResolver
{
    /** @var array */
    private $commands = [];
    /** @var \Symfony\Component\Messenger\MessageBusInterface */
    private $commandBus;
    /** @var \Symfony\Component\Messenger\MessageBusInterface */
    private $queryBus;

    public function __construct(MessageBusInterface $commandBus, MessageBusInterface $queryBus, CommandContext $commands)
    {
        $this->commandBus = $commandBus;
        $this->commands = $commands;
        $this->queryBus = $queryBus;
    }

    public function __invoke($val, $args, $context, ResolveInfo $info)
    {
        if ($command = $this->commands->getCommand($info)) {
            $envelope = $this->commandBus->dispatch($command);

            return $envelope->getMessage()->getResponse();
        }

        if ($query = $this->commands->getQuery($info)) {
            $envelope = $this->queryBus->dispatch($query);

            return $envelope->getMessage()->getResponse();
        }

        return $info->variableValues[$info->fieldName] ?? null;
    }
}
