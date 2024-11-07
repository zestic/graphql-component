<?php

declare(strict_types=1);

namespace Zestic\GraphQL\Interactor;

use Zestic\GraphQL\GraphQLMessageInterface;
use Zestic\GraphQL\MessageProcessorInterface;

class NullMessageProcessor implements MessageProcessorInterface
{
    public function process(GraphQLMessageInterface $message): void
    {
        return;
    }
}
