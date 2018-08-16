<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use App\Service\OperationMapping;
use Common\Communique\Factory\CommuniqueFactory;
use Psr\Container\ContainerInterface;
use Youshido\GraphQL\Schema\AbstractSchema;
use IamPersistent\GraphQL\Execution\Processor;

class ProcessorFactory
{
    public function __invoke(ContainerInterface $container, $requestedName): Processor
    {
        $communiqueFactory = $container->get(CommuniqueFactory::class);
        $operationMapping = $container->get(OperationMapping::class);
        /** @var AbstractSchema $schema */
        $schema = $container->get("graphql.schema");

        $processor = new Processor($communiqueFactory, $operationMapping, $schema);

        return $processor;
    }
}
