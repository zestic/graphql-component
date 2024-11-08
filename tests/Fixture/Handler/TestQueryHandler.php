<?php
declare(strict_types=1);

namespace Tests\Fixture\Handler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tests\Fixture\Message\TestQueryMessage;

#[AsMessageHandler]
class TestQueryHandler
{
    public function __invoke(TestQueryMessage $message): void
    {

    }
}
