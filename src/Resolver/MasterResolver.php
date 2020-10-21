<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use IamPersistent\GraphQL\Handler\RequestDispatcher;

final class MasterResolver
{
    /** @var \IamPersistent\GraphQL\Handler\RequestDispatcher */
    private $dispatcher;

    public function __construct(RequestDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function __invoke($val, $args, $context, ResolveInfo $info)
    {
        if ($val && array_key_exists($info->fieldName, $val)) {
            return $val[$info->fieldName];
        }

        return $this->dispatcher->handle($info);
    }
}
