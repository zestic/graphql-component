<?php
declare(strict_types=1);

namespace Zestic\GraphQL;

use Zestic\GraphQL\ExpectedReturn\Field;

final class ExpectedReturn extends Field
{
    public function getFieldFor(string $name): Field
    {
        return $this->fields[$name];
    }
}
