<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Type;

use Zestic\GraphQL\AbstractConfigurableFieldType;

class QueryType extends AbstractConfigurableFieldType
{
    public function getName()
    {
        return 'Query';
    }
}
