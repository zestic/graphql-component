<?php
declare(strict_types=1);

namespace Tests\Fixture;

use Youshido\GraphQL\Field\AbstractField;

class TestMutation extends AbstractField
{

    public function getType()
    {
        return new TestType();
    }
}