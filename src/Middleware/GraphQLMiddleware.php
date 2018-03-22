<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware;

use Exception;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Zestic\GraphQL\Interactor\Processor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Youshido\GraphQL\Schema\AbstractSchema;
use Zend\Diactoros\Response\JsonResponse;

class GraphQLMiddleware implements MiddlewareInterface
{
    protected $debug;
    protected $contextServices;
    /** @var LoggerInterface */
    protected $logger;
    /** @var AbstractSchema */
    protected $schema;

    public function __construct(AbstractSchema $schema, array $contextServices = [], LoggerInterface $logger = null, bool $debug = false)
    {
        $this->debug = $debug;
        $this->contextServices = $contextServices;
        $this->logger = $logger;
        $this->schema = $schema;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $processor = new Processor($this->schema, $this->contextServices, $this->logger);

            return $processor->processPayload($request);
        } catch (Exception $e) {
            $result = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
            if ($this->logger) {
                $this->logger->error($e->getMessage());
            }

            return new JsonResponse($result);
        }
    }
}
