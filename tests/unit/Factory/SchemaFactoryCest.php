<?php
declare(strict_types=1);

namespace Tests\Unit\Factory;

use ArrayObject;
use AspectMock\Test as Mock;
use Tests\Fixture\TestContainer;
use Tests\Fixture\TestMutation;
use UnitTester;
use Zestic\Factory\SchemaFactory;
use Zestic\GraphQL\Query\Ping;

class SchemaFactoryCest
{
    public function testInvoke(UnitTester $I)
    {
        $graphQLConfig = [
            'mutations' => [
                \Tests\Fixture\TestMutation::class
            ],
            'queries' => [
                \Zestic\GraphQL\Query\Ping::class,
            ],
            'schema' => \Zestic\GraphQL\Schema::class,
        ];
        $getReturn = function ($id) use ($graphQLConfig) {
            $config = new ArrayObject([
                'graphql' => $graphQLConfig,
            ]);
            switch ($id) {
                case 'config':
                    return $config;
                case \Tests\Fixture\TestMutation::class:
                    return new TestMutation();
                case \Zestic\GraphQL\Query\Ping::class:
                    return new Ping();
            }
        };
        $containerMock = Mock::double(TestContainer::class, ['get' => $getReturn]);
        $container = $containerMock->make();
        $factory = (new SchemaFactory())->__invoke($container);


    }
//$container
//    ->register('graphql.mutation_type', $config['mutation']['class'])
//    ->addMethodCall('addReferences', [$fieldReferences]);
//
//$fieldReferences = [];
//foreach($config['query']['fields'] as $fieldId) {
//    $fieldReferences[] = new Reference(implode('', explode('@', $fieldId, 2)));
//}
//$container
//    ->register('graphql.query_type', $config['query']['class'])
//    ->addMethodCall('addReferences', [$fieldReferences]);
//
//$container
//    ->register('graphql.app_schema', $config['schema']['class'])
//    ->addArgument(new Reference('graphql.query_type'))
//    ->addArgument(new Reference('graphql.mutation_type'));
}