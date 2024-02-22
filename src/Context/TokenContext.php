<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Context;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use Zestic\GraphQL\Middleware\RequestContextInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TokenContext implements RequestContextInterface
{
    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $request;

    public function __invoke(OperationParams $params, DocumentNode $doc, $operationType)
    {
        return $this->request->getAttribute('token');
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}
