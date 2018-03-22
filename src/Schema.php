<?php
declare(strict_types=1);

namespace Zestic\GraphQL;

use Zestic\GraphQL\Type\MutationType;
use Zestic\GraphQL\Type\QueryType;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;

class Schema extends AbstractSchema
{
    public function __construct(QueryType $query, MutationType $mutation)
    {
        $config = [
            'query' => $query,
            'mutation' => $mutation,
            'types' => [],
        ];

        parent::__construct($config);
    }

    public function build(SchemaConfig $config)
    {
        // right now, nothing to do
    }
}
