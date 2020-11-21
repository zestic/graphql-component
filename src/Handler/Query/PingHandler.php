<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Handler\Query;

use IamPersistent\GraphQL\Message\Query\PingMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PingHandler implements MessageHandlerInterface
{
    public function __invoke(PingMessage $message)
    {
        $message->setResponse('pong');
    }
}
