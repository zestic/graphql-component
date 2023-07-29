<?php
declare(strict_types=1);

namespace Tests\Fixture\Handler;

use Tests\Fixture\Message\TestMutationMessage;

class TestMutationHandler
{
    public function __invoke(TestMutationMessage $message): void
    {

    }
}
