<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Interactor;

use IamPersistent\GraphQL\GraphQLMessage;
use Zestic\Contracts\Person\FindPersonByIdInterface;
use Zestic\Contracts\Person\PersonInterface;

final class GetPersonFromGraphQLMessage
{
    public function __construct(
        private FindPersonByIdInterface $findPersonById
    ) { }

    public function getActor(GraphQLMessage $messaage): ?PersonInterface
    {
        $personId = $messaage->getContextValue('personId');

        $this->findPersonById->find($personId);
    }
}
