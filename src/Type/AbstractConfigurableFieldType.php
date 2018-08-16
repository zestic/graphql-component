<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL;

use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

abstract class AbstractConfigurableFieldType extends AbstractObjectType
{
    private $configuredFields;

    public function addReferences($fields)
    {
        $this->configuredFields = $fields;
    }

    /**
     * @param ObjectTypeConfig $config
     */
    public function build($config)
    {
        $config->addFields($this->configuredFields);
    }
}
