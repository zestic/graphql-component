<?php
declare(strict_types=1);

namespace Zestic\Factory;

use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Interactor\ExtractJwtIntoContext;

class ExtractJwtIntoContextFactory
{
    public function __invoke(ContainerInterface $container): ExtractJwtIntoContext
    {
        $config = $container->get('config');
        $jwtConfig = $config['jwt'];
        $publicKey = file_get_contents($jwtConfig['publicKeyPath']);
        $options = [
            'secret' => $publicKey,
        ];

        return new ExtractJwtIntoContext($options);
    }
}
