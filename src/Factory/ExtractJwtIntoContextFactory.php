<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Factory;

use App\Jwt\JwtConfiguration;
use Psr\Container\ContainerInterface;
use IamPersistent\GraphQL\Interactor\ExtractJwtIntoContext;

class ExtractJwtIntoContextFactory
{
    public function __invoke(ContainerInterface $container): ExtractJwtIntoContext
    {
        /** @var JwtConfiguration $config */
        $config = $container->get(JwtConfiguration::class);
        $options = [
            'algorithm' => $config->getAlgorithm(),
            'secret' => $config->getPublicKey(),
        ];

        return new ExtractJwtIntoContext($options);
    }
}
