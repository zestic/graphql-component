<?php
declare(strict_types=1);

namespace Tests\Fixture\Handler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tests\Fixture\Message\TestMutationMessage;

#[AsMessageHandler]
class TestMutationHandler
{
    public function __invoke(TestMutationMessage $message): void
    {

    }
}
