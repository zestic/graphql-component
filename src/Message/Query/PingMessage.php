<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Message\Query;

use Zestic\GraphQL\Event\WasPinged;
use Zestic\GraphQL\GraphQLMessage;

final class PingMessage extends GraphQLMessage
{
    protected ?string $eventClass = WasPinged::class;
}
