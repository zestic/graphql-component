<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Handler;

use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\Messenger\Envelope;
use Zestic\GraphQL\GraphQLMessage;
use Zestic\GraphQL\MessageProcessorInterface;

final class RequestDispatcher
{
    public function __construct(
        private array $buses,
        private array $messages,
        private MessageProcessorInterface $messageProcessor,
    ) {
    }

    public function dispatch(GraphQLMessage $message): Envelope
    {
        $operation = $message->getOperation();
        $bus = $this->messages[$operation]['bus'];

        $command = $this->buses[$bus]->dispatch($message);
        if (isset($this->buses['event'])) {
            $this->launchEvent($command);
        }

        return $command;
    }

    public function handle(ResolveInfo $info, $context)
    {
        $message = $this->getMessage($info, $context);

        $envelope = $this->dispatch($message);

        return $envelope->getMessage()->getResponse();
    }

    public function getMessage(ResolveInfo $info, $context)
    {
        $operation = $info->fieldName;
        if (!isset($this->messages[$operation])) {
            $operationType =
                match ($info->operation->operation)
                    {
                        'mutation'     => 'mutations',
                        'query'        => 'queries',
                        'subscription' => 'subscriptions',
                    };
            $pascalCaseOperation = ucfirst($operation);
            $pascalCaseType = ucfirst($info->operation->operation);
            $message = <<<MESSAGE
Unmapped operation: {$operation}. 
Add the message and handlers to graphql.global.php or implement GraphQL{$pascalCaseType}MessageInterface in 
{$pascalCaseOperation}Message
Example:
return [
    'graphQL' => [
        '{$operationType}'    => [
            '{$operation}'      => [
                'handlers' => \App\Handler\\{$pascalCaseType}\\{$pascalCaseOperation}Handler::class,
                'message'  => \App\Message\\{$pascalCaseType}\\{$pascalCaseOperation}Message::class,
            ],
        ],
    ],
];
MESSAGE;
            throw new \Exception($message);
        }
        $messageClass = $this->messages[$operation]['message'];
        $message = new $messageClass($info, $context);
        $this->messageProcessor->process($message);

        return $message;
    }

    protected function launchEvent(Envelope $envelope): void
    {
        $message = $envelope->getMessage();
        if (!$message->hasEvent()) {
            return;
        }
        $event = $message->getEvent();
        $this->buses['event']->dispatch($event, $event->getStamps());
    }
}
