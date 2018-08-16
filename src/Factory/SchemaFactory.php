<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use GraphQL\Type\Schema as GraphQLSchema;
use Psr\Container\ContainerInterface;

final class SchemaFactory
{
    public function __invoke(ContainerInterface $container): GraphQLSchema
    {
        $schemaClass = $container->get('config')['graphQL']['schema'];

        return new $schemaClass;
    }
}