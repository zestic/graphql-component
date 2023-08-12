<?php
declare(strict_types=1);

namespace Zestic\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\ResolveInfo;
use Zestic\GraphQL\ExpectedReturn\Field;

abstract class GraphQLMessage
{
    protected ?string $errorResponse = null;
    protected ?string $eventClass = null;
    /** @var \Zestic\GraphQL\ExpectedReturn[] */
    protected array $expectedReturns = [];
    protected mixed $response = null;
    private array $data = [];
    private string $operation;
    /** @var \GraphQL\Language\AST\NodeList[] */
    private $returnNodes;

    public function __construct(
        ResolveInfo $info,
        protected ?array $context = null,
    ) {
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

    public function getEvent(): ?GraphQLEvent
    {
        if ($this->eventClass === null) {
            return null;
        }

        return new $this->eventClass(
            $this->context,
            $this->errorResponse,
            $this->toArray(),
            $this->response,
        );
    }

    public function hasEvent(): bool
    {
        return $this->eventClass !== null;
    }

    public function toArray(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        $data = [];
        foreach ($this->getChildClassProperties() as $property) {
            $reflectionProperty = $reflectionClass->getProperty($property);
            $data[$property] = $reflectionProperty->getValue($this);
        }

        return $data;
    }

    private function buildExpectedReturn(): void
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

    private function getChildClassProperties(): array
    {
        $properties = [];
        $reflectionClass = new \ReflectionClass($this);
        $childClassName = $reflectionClass->getName();
        foreach ($reflectionClass->getProperties() as $property)
        {
            $propertyName = $property->name;
            if ($property->class !== $childClassName || $propertyName === 'eventClass') {
                continue;
            }
            $properties[] = $propertyName;
        }

        return $properties;
    }
    private function setDataValues($values): void
    {
        foreach ($values as $property => $value) {
            $this->data[$property] = $value;
        }
    }

    private function setPropertyValues($object, $values): void
    {
        foreach ($values as $property => $rawValue) {
            $reflectionProperty = new \ReflectionProperty($object, $property);
            $value = $this->getValue($reflectionProperty, $rawValue);
            $reflectionProperty->setValue($object, $value);
        }
    }

    private function getValue(\ReflectionProperty $rp, $rawValue): mixed
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
