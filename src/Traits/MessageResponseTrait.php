<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Traits;

trait MessageResponseTrait
{
    /** @var mixed */
    protected $response;

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response): void
    {
        $this->response = $response;
    }
}
