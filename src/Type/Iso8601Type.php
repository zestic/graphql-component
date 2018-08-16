<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Type;

use Carbon\Carbon;
use DateTime;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class Iso8601Type extends AbstractScalarType
{
    public function getName()
    {
        return 'Iso8601';
    }

    public function isValidValue($value)
    {
        if ((is_object($value) && $value instanceof \DateTime) || empty($value)) {
            return true;
        } else if (is_string($value)) {
            $date = $this->createFromFormat($value);
        } else {
            $date = null;
        }

        return $date ? true : false;
    }

    public function serialize($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($carbon = self::parseCarbon($value)) {
            return $carbon->toIso8601String();
        }

        return null;
    }

    public function parseValue($value)
    {
        if (is_string($value) || $value instanceof \DateTime) {
            $date = $this->createFromFormat($value);
        } else {
            $date = false;
        }

        return $date->toDateTimeString() ?: null;
    }

    public static function parseCarbon($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }
        if (is_string($value)) {
            return new Carbon($value);
        }
    }

    private function createFromFormat($value)
    {
        return new Carbon($value);
    }

    public function getDescription()
    {
        return 'Representation of date and time in Iso8601 format';
    }

}
