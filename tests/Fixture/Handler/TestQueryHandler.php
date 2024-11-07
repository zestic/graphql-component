<?php
declare(strict_types=1);

namespace Tests\Fixture\Handler;

use Tests\Fixture\Message\TestQueryMessage;

class TestQueryHandler
{
    public function __invoke(TestQueryMessage $message): void
    {

    }
}
