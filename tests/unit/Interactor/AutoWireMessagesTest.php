<?php
declare(strict_types=1);

namespace Tests\Unit\Interactor;

use Tests\Fixture\Handler\TestMutationHandler;
use Tests\Fixture\Message\TestMutationMessage;
use Zestic\GraphQL\GraphQLMutationMessageInterface;
use Zestic\GraphQL\Interactor\AutoWireMessages;
use PHPUnit\Framework\TestCase;

class AutoWireMessagesTest extends TestCase
{
    public function testFindHandlersForInterface(): void
    {
        $handlers = AutoWireMessages::findHandlersForInterface(GraphQLMutationMessageInterface::class);

        $this->assertEquals($this->expected(), $handlers);
    }

    private function expected(): array
    {
        return [
            TestMutationMessage::class => [
                TestMutationHandler::class,
            ],
        ];
    }
}
