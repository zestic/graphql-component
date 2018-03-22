<?php
declare(strict_types=1);

namespace Pac\GraphQL\Interactor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Execution\Processor as GraphQLProcessor;
use Zend\Diactoros\Response\JsonResponse;

class Processor
{
    private $graphQLProcessor;
    private $logger;

    public function __construct(AbstractSchema $schema, array $contextServices, LoggerInterface $logger = null)
    {
        $this->graphQLProcessor = new GraphQLProcessor($schema);
        $this->logger = $logger;

        $container = $this->graphQLProcessor->getExecutionContext()->getContainer();
        foreach ($contextServices as $id => $service) {
            $container->set($id, $service);
        }
    }

    public function processPayload(ServerRequestInterface $request): ResponseInterface
    {
        $content = $this->extractContentFromRequest($request);

        if ($this->logger) {
            $this->logger->info('GraphQL');
            $this->logger->info('=======');
            $this->logger->info('Query');
            $this->logger->info(json_encode($content['query']));
            $this->logger->info('Variables');
            $this->logger->info(json_encode($content['variables']));
            $this->logger->info('=======');
        }
        $response = $this->graphQLProcessor
            ->processPayload($content['query'], $content['variables'])
            ->getResponseData();

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return new JsonResponse($response);
    }

    protected function extractContentFromRequest(ServerRequestInterface $request): array
    {
        if (!$content = json_decode($request->getBody()->getContents(), true)) {
            $content = $request->getParsedBody();
            foreach ($content as $key => $json) {
                $content[$key] = json_decode($json, true);
            }
            $content['variables'] += $request->getUploadedFiles();
        }

        $content += [
            'query'     => null,
            'variables' => null,
        ];

        return $content;
    }
}
