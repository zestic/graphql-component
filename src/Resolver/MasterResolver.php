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
    /** @var mixed */
    private $response;

    public function __construct(MessageBusInterface $commandBus, MessageBusInterface $queryBus, CommandContext $commands)
    {
        $this->commandBus = $commandBus;
        $this->commands = $commands;
        $this->queryBus = $queryBus;
    }

    public function __invoke($val, $args, $context, ResolveInfo $info)
    {
        if (isset($this->response[$info->fieldName])) {
            return $this->response[$info->fieldName];
        }

        if ($command = $this->commands->getCommand($info)) {
            $envelope = $this->commandBus->dispatch($command);

            $this->response = $envelope->getMessage()->getResponse();

            return $this->response;
        }

        if ($query = $this->commands->getQuery($info)) {
            $envelope = $this->queryBus->dispatch($query);

            $this->response = $envelope->getMessage()->getResponse();

            return $this->response;
        }

        return null;
    }
}
