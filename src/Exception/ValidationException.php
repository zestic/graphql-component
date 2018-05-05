<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Exception;

use Exception as BaseException;

class ValidationException extends BaseException
{
    public static function create($message, $code = 500)
    {
        return new static (
            $message, $code
        );
    }
}
