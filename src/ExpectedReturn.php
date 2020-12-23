<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL;

use IamPersistent\GraphQL\ExpectedReturn\Field;

final class ExpectedReturn extends Field
{
    public function getFieldFor(string $name): Field
    {
        return $this->fields[$name];
    }
}
