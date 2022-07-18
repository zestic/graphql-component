<?php
declare(strict_types=1);

namespace Zestic\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionProperty;
use Zestic\GraphQL\ExpectedReturn\Field;

abstract class GraphQLMessage
{
    /** @var array|null */
    protected $context;
    protected string|null $errorResponse = null;
    /** @var \Zestic\GraphQL\ExpectedReturn[] */
    protected array $expectedReturns = [];
    /** @var mixed */
    protected $response;
    private array $data = [];
    /** @var string */
    private $operation;
    /** @var \GraphQL\Language\AST\NodeList[] */
    private $returnNodes;

    public function __construct(ResolveInfo $info, $context)
    {
        $this->context = $context;
        $this->setDataValues($info->variableValues);
        $this->setPropertyValues($this, $info->variableValues);
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

    public function getData(): array
    {
        return $this->data;
    }

    public function setErrorResponse(string $message)
    {
        $this->errorResponse = $message;
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
        if ($this->errorResponse) {
            throw new Error($this->errorResponse);
        }

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

    private function setDataValues($values)
    {
        foreach ($values as $property => $value) {
            $this->data[$property] = $value;
        }
    }

    private function setPropertyValues($object, $values)
    {
        foreach ($values as $property => $rawValue) {
            $reflectionProperty = new ReflectionProperty($object, $property);
            $reflectionProperty->setAccessible(true);
            $value = $this->getValue($reflectionProperty, $rawValue);
            $reflectionProperty->setValue($object, $value);
            $reflectionProperty->setAccessible(false);
        }
    }

    private function getValue(ReflectionProperty $rp, $rawValue)
    {
        $type = $rp->getType()?->getName();
        if (!$type || !class_exists($type)) {
            return $rawValue;
        }

        $object = new $type();
        $this->setPropertyValues($object, $rawValue);

        return $object;
    }
}
