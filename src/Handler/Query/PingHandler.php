<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Handler\Query;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Zestic\GraphQL\Message\Query\PingMessage;

final class PingHandler implements MessageHandlerInterface
{
    public function __invoke(PingMessage $message)
    {
        $message->setResponse('pong');
    }
}
