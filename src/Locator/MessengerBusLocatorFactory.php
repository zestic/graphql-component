<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Locator;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Netglue\PsrContainer\Messenger\Container\Util;
use Netglue\PsrContainer\Messenger\HandlerLocator\OneToManyFqcnContainerHandlerLocator;
use Psr\Container\ContainerInterface;

final class MessengerBusLocatorFactory implements FactoryInterface
{
    public function __construct(
        private readonly string $busIdentifier,
    ) { }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = Util::messageBusOptions($container, $this->busIdentifier);

        return new OneToManyFqcnContainerHandlerLocator($options->handlers(), $container);
    }
}
