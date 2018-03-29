<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Youshido\GraphQL\Execution\Processor;
use Zend\Diactoros\Response\JsonResponse;

class GraphQLMiddleware implements MiddlewareInterface
{
    /**
     * @var string The graphql uri path to match against
     */
    private $graphql_uri;

    /**
     * @var array The graphql headers
     */
    private $graphql_headers = [
        "application/graphql"
    ];

    /**
     * @var array Allowed method for a graphql request, default GET, POST
     */
    private $allowed_methods = [
        "GET", "POST"
    ];

    /**
     * @var Processor
     */
    private $processor;

    /**
     * GraphQLMiddleware constructor.
     *
     * @param Processor $processor
     * @param string    $graphql_uri
     */
    public function __construct(Processor $processor, $graphql_uri = '/graphql')
    {
        $this->processor = $processor;
        $this->graphql_uri = $graphql_uri;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isGraphQLRequest($request)) {
            return $handler->handle($request);
        }

        if (!in_array($request->getMethod(), $this->allowed_methods)){
            return new JsonResponse([
                "Method not allowed. Allowed methods are " . implode(", ", $this->allowed_methods)
            ], 405);
        }

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
