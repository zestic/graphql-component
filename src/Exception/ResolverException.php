<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException as SplRuntimeException;

class ResolverException extends SplRuntimeException implements ContainerExceptionInterface
{
    public static function invalidResolver($field, $resolver)
    {
        return new static (
            "The provided resolver {$resolver} for field {$field} is not valid. Resolver must implement the `resolve` method"
        );
    }
}
