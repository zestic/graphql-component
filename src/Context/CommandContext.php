<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Context;

use GraphQL\Type\Definition\ResolveInfo;

final class CommandContext
{
    /** @var array */
    private $commands;

    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    public function getCommand(ResolveInfo $info)
    {
        $operation = $info->fieldName;
        if (!isset($this->commands[$operation]['command'])) {
            return null;
        }
        $commandClass = $this->commands[$operation]['command'];

        return new $commandClass($info->variableValues);
    }

    public function getQuery(ResolveInfo $info)
    {
        $operation = $info->fieldName;
        if (!isset($this->commands[$operation]['query'])) {
            return null;
        }
        $queryClass = $this->commands[$operation]['query'];

        return new $queryClass($info->variableValues);
    }
}