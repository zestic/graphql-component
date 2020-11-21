<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Query;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

final class PingQuery extends FieldDefinition
{
    public function __construct()
    {
        $config = [
            'name'    => 'ping',
            'type'    => Type::string(),
        ];
        parent::__construct($config);
    }
}
