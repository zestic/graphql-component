<?php
declare(strict_types=1);

namespace Zestic\GraphQL;

abstract class GraphQLEvent
{
    public function __construct(
        protected ?array $context = null,
        protected ?string $errorResponse = null,
        protected array $incomingData = [],
        protected mixed $response = null,
    ) {
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getErrorResponse(): ?string
    {
        return $this->errorResponse;
    }

    public function getIncomingData(): array
    {
        return $this->incomingData;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }

    public function getStamps(): array
    {
        return [];
    }
}
