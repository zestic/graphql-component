<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Interactor;

use IamPersistent\GraphQL\GraphQLMessage;
use Zestic\Contracts\User\FindUserByIdInterface;
use Zestic\Contracts\User\UserInterface;

final class GetUserFromGraphQLMessage
{
    public function __construct(
        private FindUserByIdInterface $findUserById
    ) { }

    public function getActor(GraphQLMessage $messaage): ?UserInterface
    {
        $userId = $messaage->getContextValue('userId');

        return $this->findUserById->find($userId);
    }
}
