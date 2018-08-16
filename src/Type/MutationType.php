<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Type;

use IamPersistent\GraphQL\AbstractConfigurableFieldType;

class MutationType extends AbstractConfigurableFieldType
{
    public function getName()
    {
        return 'MutationType';
    }
}
