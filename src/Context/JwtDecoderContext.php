<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Context;

use App\Jwt\JwtConfiguration;
use Firebase\JWT\JWT;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use IamPersistent\GraphQL\Middleware\RequestContextInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class JwtDecoderContext implements RequestContextInterface
{
    const OPTIONS = [
        "algorithm" => ["HS256", "HS512", "HS384"],
        "header"    => "Authorization",
        "regexp"    => "/Bearer\s+(.*)$/i",
        "cookie"    => "token",
    ];

    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    /** @var \App\Jwt\JwtConfiguration */
    private $jwtConfig;
    /** @var array */
    private $options;
    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $request;

    public function __construct(JwtConfiguration $jwtConfig, LoggerInterface $logger = null)
    {
        $this->jwtConfig = $jwtConfig;
        $options = [
            'algorithm' => $jwtConfig->getAlgorithm(),
            'secret' => $jwtConfig->getPublicKey(),
        ];
        $this->options = array_merge(self::OPTIONS, $options);
        $this->logger = $logger;
    }

    public function __invoke(OperationParams $params, DocumentNode $doc, $operationType)
    {
        if (!$token = $this->fetchToken($this->request)) {
            return null;
        }

        return $this->decodeToken($token);
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
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
        } catch (\Exception $exception) {
            $this->log(LogLevel::WARNING, $exception->getMessage(), [$token]);

            throw $exception;
        }
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
