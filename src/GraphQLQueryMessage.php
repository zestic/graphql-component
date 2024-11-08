<?php

declare(strict_types=1);

namespace Zestic\GraphQL;

use Zestic\GraphQL\GraphQLMessage;
use Zestic\GraphQL\GraphQLQueryMessageInterface;

abstract class GraphQLQueryMessage extends GraphQLMessage implements GraphQLQueryMessageInterface
{

}
