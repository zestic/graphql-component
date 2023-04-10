<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Handler;

use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\Messenger\Envelope;
use Zestic\GraphQL\GraphQLMessage;

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

        $command = $this->buses[$bus]->dispatch($message);
        if (isset($this->buses['event'])) {
            $this->buses['event']->dispatch($command);
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
Add the message and handlers to graphql.global.php
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

        return new $messageClass($info, $context);
    }
}
