<?php
declare(strict_types=1);

namespace Zestic\GraphQL\ExpectedReturn;

class Field
{
    /** @var Field[] */
    protected $fields;
    /** @var string */
    private $name;

    public function __construct(string $name, array $fields = [])
    {
        $this->fields = $fields;
        $this->name = $name;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getFieldsAsArray(): array
    {
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = $field->getName();
        }

        return $fields;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
