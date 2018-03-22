<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Type;

use Zestic\GraphQL\AbstractConfigurableFieldType;

class MutationType extends AbstractConfigurableFieldType
{
    public function getName()
    {
        return 'MutationType';
    }
}
