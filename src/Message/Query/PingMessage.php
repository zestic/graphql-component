<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Message\Query;

use Zestic\GraphQL\Event\WasPinged;
use Zestic\GraphQL\GraphQLMessage;
use Zestic\GraphQL\GraphQLQueryMessage;

final class PingMessage extends GraphQLQueryMessage
{
    protected ?string $eventClass = WasPinged::class;
}
