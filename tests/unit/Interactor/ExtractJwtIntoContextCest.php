<?php
declare(strict_types=1);

namespace Tests\Unit\Interactor;

use Firebase\JWT\JWT;
use ReflectionClass;
use UnitTester;
use Youshido\GraphQL\Execution\Container\Container;
use Youshido\GraphQL\Execution\Context\ExecutionContext;
use Zend\Diactoros\ServerRequest;
use IamPersistent\GraphQL\Interactor\ExtractJwtIntoContext;

class ExtractJwtIntoContextCest
{
    private $extractJwtIntoContext;

    public function _before(UnitTester $I)
    {
        $options = [
            'algorithm' => ['HS256'],
            'secret' => 'notMuchOfASecret'
        ];
        $this->extractJwtIntoContext = new ExtractJwtIntoContext($options);
    }

    public function testExtractFromHeader(UnitTester $I)
    {
        $payload = [
            'user' => [
                'id' => 'e05c9ad421df45ea95f3b9aa60c4b19d',
            ],
        ];
        $token = JWT::encode($payload, 'notMuchOfASecret', 'HS256');
        $headers = [
            'Authorization' => "Bearer $token"
        ];

        $request = $this->createServerRequest($headers);

        $context = $this->createContext();
        $this->extractJwtIntoContext->extract($request, $context);

        $container = $context->getContainer();
        $I->assertSame($payload, $container->get('decodedToken'));
        $I->assertSame($payload['user'], $container->get('user'));
    }

    public function testExtractWithNoToken(UnitTester $I)
    {
        $request = $this->createServerRequest();

        $context = $this->createContext();
        $this->extractJwtIntoContext->extract($request, $context);

        $container = $context->getContainer();
        $I->assertFalse($container->has('decodedToken'));
        $I->assertFalse($container->has('user'));
    }

    private function createContext(): ExecutionContext
    {
        $rc = new ReflectionClass(ExecutionContext::class);
        /** @var ExecutionContext $context */
        $context = $rc->newInstanceWithoutConstructor();
        $container = new Container();
        $context->setContainer($container);

        return $context;
    }

    private function createServerRequest(array $headers = [], array $cookies = []): ServerRequest
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, '');
        rewind($stream);

        return new ServerRequest([], [], null,  null, $stream, $headers, $cookies);
    }
}