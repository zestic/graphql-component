<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Exception;

class NoDirectoriesSetException extends \LogicException
{
    const DEFAULT_MESSAGE = <<<MESSAGE
You must set directories with self::setDirectories() before calling this method.
MESSAGE;

    public function __construct($message = self::DEFAULT_MESSAGE, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
