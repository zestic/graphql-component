<?php

declare(strict_types=1);

namespace Zestic\GraphQL;

interface MessageProcessorInterface
{
    public function process(GraphQLMessage $message): void;
}
