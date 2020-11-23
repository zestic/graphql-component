<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use IamPersistent\GraphQL\Context\JwtDecoderContext;
use Psr\Container\ContainerInterface;

final class JwtDecoderContextFactory
{
    public function __invoke(ContainerInterface $container): JwtDecoderContext
    {
        return new JwtDecoderContext();
    }
}
