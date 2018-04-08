<?php
declare(strict_types=1);

/**
 * This was built using code from
 *
 * Copyright (c) 2015-2018 Mika Tuupola
 *   https://github.com/tuupola/slim-jwt-auth
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

namespace Zestic\GraphQL\Interactor;

use DomainException;
use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Youshido\GraphQL\Execution\Context\ExecutionContext;

class ExtractJwtIntoContext
{
    CONST OPTIONS = [
        "algorithm" => ["HS256", "HS512", "HS384"],
        "header" => "Authorization",
        "regexp" => "/Bearer\s+(.*)$/i",
        "cookie" => "token",
    ];

    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $options;

    public function __construct(array $options = [])
    {
        $this->hydrate($options);
    }

    public function extract(ServerRequestInterface $request, ExecutionContext $context)
    {
        if (!$token = $this->fetchToken($request)) {
            return;
        }

        $decoded = $this->decodeToken($token);

        $container = $context->getContainer();
        $container->set('decodedToken', $decoded);
        $container->set('user', $decoded['user']);
    }

    private function fetchToken(ServerRequestInterface $request): ?string
    {
        $message = "Using token from request header";
        /* Check for token in header. */
        $headers = $request->getHeader($this->options["header"]);
        $header = isset($headers[0]) ? $headers[0] : "";
        if (preg_match($this->options["regexp"], $header, $matches)) {
            $this->log(LogLevel::DEBUG, $message);

            return $matches[1];
        }
        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->options["cookie"]])) {
            $this->log(LogLevel::DEBUG, "Using token from cookie");
            $this->log(LogLevel::DEBUG, $cookieParams[$this->options["cookie"]]);

            return $cookieParams[$this->options["cookie"]];
        };

        return null;
    }

    private function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                $this->options["secret"],
                (array)$this->options["algorithm"]
            );

            return $this->recursivelyConvertObjectToArray($decoded);
        } catch (Exception $exception) {
            $this->log(LogLevel::WARNING, $exception->getMessage(), [$token]);

            throw $exception;
        }
    }

    private function hydrate($data = []): void
    {
        if (isset($data['logger'])) {
            $this->logger = $data['logger'];
        }
        $this->options = array_merge(self::OPTIONS, $data);
    }

    private function log($level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    private function recursivelyConvertObjectToArray($decodedObject)
    {
        return json_decode(json_encode($decodedObject), true);
    }
}