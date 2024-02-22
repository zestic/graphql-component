<?php

declare(strict_types=1);

namespace Zestic\GraphQL\Interactor;

use Zestic\GraphQL\GraphQLMessage;
use Zestic\GraphQL\MessageProcessorInterface;

class NullMessageProcessor implements MessageProcessorInterface
{
    public function process(GraphQLMessage $message): void
    {
        return;
    }
}
