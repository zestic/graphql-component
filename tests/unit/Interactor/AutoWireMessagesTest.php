<?php
declare(strict_types=1);

namespace Tests\Unit\Interactor;

use PHPUnit\Framework\TestCase;
use Tests\Fixture\Handler\TestMutationHandler;
use Tests\Fixture\Message\TestMutationMessage;
use Tests\Fixture\Handler\TestQueryHandler;
use Tests\Fixture\Message\TestQueryMessage;
use Zestic\GraphQL\Exception\NoDirectoriesSetException;
use Zestic\GraphQL\GraphQLMutationMessageInterface;
use Zestic\GraphQL\Interactor\AutoWireMessages;

class AutoWireMessagesTest extends TestCase
{
    private array $directories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directories = [__DIR__ . '/../../Fixture'];
    }

    /**
     * @test
     */
    public function forceOrder(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @depends forceOrder
     */
    public function testFindHandlersForInterfaceWithoutSetDirectories(): void
    {
        $this->expectException(NoDirectoriesSetException::class);

        AutoWireMessages::setDirectories([]);
        AutoWireMessages::getQueryHandlers();
    }

    /**
     * @depends forceOrder
     */
    public function testFindHandlersForInterfaceWithEmptySetDirectories(): void
    {
        $this->expectException(NoDirectoriesSetException::class);

        AutoWireMessages::setDirectories([]);
        $handlers = AutoWireMessages::findHandlersForInterface(
            GraphQLMutationMessageInterface::class,
        );
    }

    /**
     * @depends forceOrder
     */
    public function testFindHandlersForInterface(): void
    {
        AutoWireMessages::setDirectories($this->directories);
        $handlers = AutoWireMessages::findHandlersForInterface(
            GraphQLMutationMessageInterface::class,
        );

        $this->assertEquals($this->expectedMutationConfig(), $handlers);
    }

    /**
     * @depends forceOrder
     */
    public function testGetMutationHandlers(): void
    {
        AutoWireMessages::setDirectories($this->directories);
        $mutationHandlers = AutoWireMessages::getMutationHandlers();
        $this->assertEquals($this->expectedMutationConfig(), $mutationHandlers);
    }

    /**
     * @depends forceOrder
     */
    public function testGetQueryHandlers(): void
    {
        AutoWireMessages::setDirectories($this->directories);
        $queryHandlers = AutoWireMessages::getQueryHandlers();
        $this->assertEquals($this->expectedQueryConfig(), $queryHandlers);
    }

    private function expectedMutationConfig(): array
    {
        return [
            'testMutation' => [
                'message' => TestMutationMessage::class,
                'handlers' => [TestMutationHandler::class],
            ],
        ];
    }
    private function expectedQueryConfig(): array
    {
        return [
            'testQuery' => [
                'message' => TestQueryMessage::class,
                'handlers' => [TestQueryHandler::class],
            ],
        ];
    }
}
