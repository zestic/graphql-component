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
        if (!$val) {
            return $this->dispatcher->handle($info, $context);
        }
        if ($val && array_key_exists($info->fieldName, $val)) {
            return $val[$info->fieldName];
        }

        return $val;
    }
}
