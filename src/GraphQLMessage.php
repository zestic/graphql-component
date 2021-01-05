<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL;

use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\ResolveInfo;
use IamPersistent\GraphQL\ExpectedReturn\Field;
use ReflectionProperty;

abstract class GraphQLMessage
{
    /** @var array|null */
    protected $context;
    /** @var \IamPersistent\GraphQL\ExpectedReturn[] */
    protected $expectedReturns = [];
    /** @var mixed */
    protected $response;
    /** @var string */
    private $operation;
    /** @var \GraphQL\Language\AST\NodeList[] */
    private $returnNodes;

    public function __construct(ResolveInfo $info, $context)
    {
        $this->context = $context;
        foreach ($info->variableValues as $property => $value) {
            $reflectionProperty = new ReflectionProperty($this, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($this, $value);
            $reflectionProperty->setAccessible(false);
        }
        $this->operation = $info->fieldName;
        $this->returnNodes = $info->fieldNodes->getArrayCopy();
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getContextValue(string $key)
    {
        return $this->context[$key] ?? null;
    }

    public function setErrorResponse(string $message)
    {

    }

    public function getExpectedReturn(string $name): ?ExpectedReturn
    {
        if (empty($this->expectedReturns)) {
            $this->buildExpectedReturn();
        }

        return $this->expectedReturns[$name];
    }

    public function getExpectedReturns(): array
    {
        if (empty($this->expectedReturns)) {
            $this->buildExpectedReturn();
        }

        return $this->expectedReturns;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response): void
    {
        $this->response = $response;
    }

    private function buildExpectedReturn()
    {
        foreach ($this->returnNodes as $node) {
            $name = $node->name->value;
            if ($this->operation === $name) {
                $fields = $this->extractFromSelections($node->selectionSet->selections);
                $this->expectedReturns[$name] = new ExpectedReturn($name, $fields);
            }
        }
    }

    private function extractFromSelections(NodeList $selections): array
    {
        $fields = [];
        foreach ($selections as $node) {
            $name = $node->name->value;
            if ('__typename' === $name) {
                continue;
            }
            if ($node->selectionSet) {
                $children = $this->extractFromSelections($node->selectionSet->selections);
            } else {
                $children = [];
            }

            $fields[$name] = new Field($name, $children);
        }

        return $fields;
    }
}
