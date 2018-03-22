<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Type;

use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class MoneyType extends AbstractScalarType
{
    public function getName()
    {
        return 'Money';
    }

    public function isValidValue($value)
    {
        return isset($value['amount'], $value['currency']);
    }

    public function serialize($money)
    {
        return $money;
    }

    public function parseValue($argument)
    {
        return $argument;
    }

    public function getDescription()
    {
        return 'JSON object with amount and currency';
    }
}
