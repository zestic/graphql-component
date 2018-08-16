<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Type;

use Carbon\Carbon;
use DateTime;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class HumanDateDiffType extends AbstractScalarType
{
    public function getName()
    {
        return 'HumanDateDiff';
    }

    public function isValidValue($value)
    {
        return $value instanceof Carbon || $value instanceof DateTime;
    }

    public function serialize($due)
    {
        $days = Carbon::now()->diffInDays($due);

        return Carbon::now()->subDays($days)->diffForHumans();
    }

    public function getDescription()
    {
        return 'Human readable difference in time';
    }
}
