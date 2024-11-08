<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Handler\Query;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Zestic\GraphQL\Message\Query\PingMessage;

#[AsMessageHandler]
final class PingHandler
{
    public function __invoke(PingMessage $message)
    {
        $message->setResponse('pong');
    }
}
