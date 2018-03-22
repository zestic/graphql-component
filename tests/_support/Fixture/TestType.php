<?php
declare(strict_types=1);

namespace Tests\Fixture;

use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;

class TestType extends AbstractObjectType
{
    public function build($config)
    {
        $config
            ->addFields(
                [
                    'response' => new StringType(),
                ]
            );
    }

    public function getResponse()
    {
        return 'testing';
    }

    public function getName()
    {
        return 'Test';
    }
}