<?php
declare(strict_types=1);

namespace Tests\Unit\Interactor;

use Tests\Fixture\Handler\TestMutationHandler;
use Tests\Fixture\Message\TestMutationMessage;
use Tests\Fixture\Handler\TestQueryHandler;
use Tests\Fixture\Message\TestQueryMessage;
use Zestic\GraphQL\GraphQLMutationMessageInterface;
use Zestic\GraphQL\Interactor\AutoWireMessages;
use PHPUnit\Framework\TestCase;

class AutoWireMessagesTest extends TestCase
{
    private string $fixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDirectory = __DIR__ . '/../../Fixture';
    }

    public function testFindHandlersForInterface(): true
    {
        $handlers = AutoWireMessages::findHandlersForInterface(GraphQLMutationMessageInterface::class);

        $this->assertEquals($this->expectedMutationConfig(), $handlers);

        return true;
    }

    /**
     * @depends testFindHandlersForInterface
     */
    public function testFindHandlersForInterfaceWithDirectoriesSet(): void
    {
        $directories = [$this->fixtureDirectory];
        $handlers = AutoWireMessages::findHandlersForInterface(GraphQLMutationMessageInterface::class, $directories);

        $this->assertEquals($this->expectedMutationConfig(), $handlers);
    }

    public function testSetUpFilesAndFindHandlersForInterface(): void
    {
        AutoWireMessages::setDirectories([$this->fixtureDirectory]);
        $handlers = AutoWireMessages::findHandlersForInterface(GraphQLMutationMessageInterface::class);

        $this->assertEquals($this->expectedMutationConfig(), $handlers);
    }

    public function testGetMutationHandlers(): void
    {
        AutoWireMessages::setDirectories([$this->fixtureDirectory]);
        $mutationHandlers = AutoWireMessages::getMutationHandlers();
        $this->assertEquals($this->expectedMutationConfig(), $mutationHandlers);
    }

    public function testGetQueryHandlers(): void
    {
        AutoWireMessages::setDirectories([$this->fixtureDirectory]);
        $queryHandlers = AutoWireMessages::getQueryHandlers();
        $this->assertEquals($this->expectedQueryConfig(), $queryHandlers);
    }

    public function testGetQueryHandlersWithEmptySetDirectories(): void
    {
        AutoWireMessages::setDirectories([]);
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
