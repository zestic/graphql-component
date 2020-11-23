<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL;

use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionProperty;

abstract class GraphQLMessage
{
    /** @var array|null */
    protected $context;
    /** @var array */
    protected $expectedReturn;
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

    public function getExpectedReturn(): array
    {
        if (empty($this->expectedReturn)) {
            $this->buildExpectedReturn();
        }

        return $this->expectedReturn;
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
            if ($this->operation === $node->name->value) {
                $this->expectedReturn = $this->extractFromSelections($node->selectionSet->selections);

                return;
            }
        }
    }

    private function extractFromSelections(NodeList $selections): array
    {
        $data = [];
        foreach ($selections as $node) {
            $name = $node->name->value;
            if ($node->selectionSet) {
                $data[$name] = $this->extractFromSelections($node->selectionSet->selections);
            } else {
                $data[$name] = null;
            }
        }

        return $data;
    }
}
