<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Type;

use IamPersistent\GraphQL\AbstractConfigurableFieldType;

class QueryType extends AbstractConfigurableFieldType
{
    public function getName()
    {
        return 'Query';
    }
}
