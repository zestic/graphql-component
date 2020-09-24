<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Handler;

use GraphQL\Type\Definition\ResolveInfo;
use IamPersistent\GraphQL\GraphQLMessage;
use Symfony\Component\Messenger\Envelope;

final class RequestDispatcher
{
    /** @var array */
    private $buses;
    /** @var array */
    private $messages;

    public function __construct(array $buses, array $messages)
    {
        $this->buses = $buses;
        $this->messages = $messages;
    }

    public function dispatch(GraphQLMessage $message): Envelope
    {
        $operation = $message->getOperation();
        $bus = $this->messages[$operation]['bus'];

        return $this->buses[$bus]->dispatch($message);
    }

    public function handle(ResolveInfo $info)
    {
        $message = $this->getMessage($info);

        $envelope = $this->dispatch($message);

        return $envelope->getMessage()->getResponse();
    }

    public function getMessage(ResolveInfo $info)
    {
        $operation = $info->fieldName;
        if (!isset($this->messages[$operation])) {
            return null;
        }
        $messageClass = $this->messages[$operation]['message'];

        return new $messageClass($info);
    }
}
