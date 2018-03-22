<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Type;

use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;

class PingType extends AbstractObjectType
{
    /**
     * @param ObjectTypeConfig $config
     */
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
        return 'pong';
    }

    public function getName()
    {
        return 'Ping';
    }
}
