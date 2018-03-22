<?php
declare(strict_types=1);

namespace Test\Unit\Middleware;

use Http\Factory\Diactoros\StreamFactory;
use Zestic\GraphQL\Middleware\GraphQLMiddleware;
use ReflectionClass;
use UnitTester;

class GraphQLMiddlewareCest
{
    public function testExtractContentFromJsonRequest(UnitTester $I)
    {
        $expected = [
            'query'     => 'query getMatter($id: String!) {\n matter(id: $id) {\nid\n}\n}',
            'variables' => [
                'id' => '4d967a0f65224f1685a602cbe4eef667',
            ],
        ];
        $jsonContent = json_encode($expected);
        $stream = (new StreamFactory)->createStream($jsonContent);
        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            $stream
        );

        $rc = new ReflectionClass(GraphQLMiddleware::class);
        $middleware = $rc->newInstanceWithoutConstructor();
        $rm = new \ReflectionMethod(GraphQLMiddleware::class, 'extractContentFromRequest');
        $rm->setAccessible(true);
        $content = $rm->invoke($middleware, $request);

        $I->assertSame($expected, $content);
    }
}
