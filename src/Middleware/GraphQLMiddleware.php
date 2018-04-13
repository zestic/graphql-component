<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Youshido\GraphQL\Execution\Processor;
use Zend\Diactoros\Response\JsonResponse;
use Zestic\GraphQL\Interactor\ExtractJwtIntoContext;

class GraphQLMiddleware implements MiddlewareInterface
{
    /** @var array */
    private $allowedMethods = [ "GET", "POST"];
    /** @var ExtractJwtIntoContext */
    private $extractJwtIntoContext;
    /** @var array */
    private $graphql_headers = ['application/graphql'];
    /** @var string */
    private $graphql_uri;
    /** @var Processor */
    private $processor;

    public function __construct(Processor $processor, $graphql_uri = '/graphql', ExtractJwtIntoContext $extractJwtIntoContext)
    {
        $this->extractJwtIntoContext = $extractJwtIntoContext;
        $this->processor = $processor;
        $this->graphql_uri = $graphql_uri;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isGraphQLRequest($request)) {
            return $handler->handle($request);
        }

        if (!in_array($request->getMethod(), $this->allowedMethods)){
            return new JsonResponse([
                "Method not allowed. Allowed methods are " . implode(", ", $this->allowedMethods)
            ], 405);
        }

        $this->extractJwtIntoContext->extract($request, $this->processor->getExecutionContext());

        list($query, $variables) = $this->getPayload($request);

        $this->processor->processPayload($query, $variables);

        $response = $this->processor->getResponseData();

        return new JsonResponse($response);
    }

    private function isGraphQLRequest(ServerRequestInterface $request)
    {
        return $this->hasUri($request) || $this->hasGraphQLHeader($request);
    }

    private function hasUri(ServerRequestInterface $request)
    {
        return  $this->graphql_uri === $request->getUri()->getPath();
    }

    private function hasGraphQLHeader(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('content-type')) {
            return false;
        }

        $request_headers = array_map(function($header){
            return trim($header);
        }, explode(",", $request->getHeaderLine("content-type")));

        foreach ($this->graphql_headers as $allowed_header) {
            if (in_array($allowed_header, $request_headers)){
                return true;
            }
        }

        return  false;
    }

    private function getPayload(ServerRequestInterface $request)
    {
        $method = $request->getMethod();

        switch ($method) {
            case "GET":
                return $this->fromGet($request);
            case "POST":
                return $this->fromPost($request);
            default:
                return $this->createEmptyResponse();

        }
    }

    private function fromGet(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();

        $query = isset($params['query']) ? $params['query'] : null;
        $variables = isset($params['variables']) ? $params['variables'] : [];

        $variables = is_string($variables) ? json_decode($variables, true) ?: [] : [];

        return [$query, $variables];

    }

    private function fromPost(ServerRequestInterface $request)
    {
        $content = $request->getBody()->getContents();

        $query = $variables = null;

        if (!empty($content)) {
            if ($this->hasGraphQLHeader($request)) {
                $query = $content;
            } else {
                $params = json_decode($content, true);
                if ($params) {
                    $query = isset($params['query']) ? $params['query'] : $query;
                    if (isset($params['variables'])) {
                        if (is_string($params['variables'])) {
                            $variables = json_decode($params['variables'], true) ?: $variables;
                        } else {
                            $variables = $params['variables'];
                        }
                        $variables = is_array($variables) ? $variables : [];
                    }
                }
            }
        }
        return [$query, $variables];

    }

    private function createEmptyResponse()
    {
        return new JsonResponse([], 200);
    }
}
