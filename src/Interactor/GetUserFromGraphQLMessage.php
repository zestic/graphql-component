<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Interactor;

use Zestic\Contracts\User\FindUserByIdInterface;
use Zestic\Contracts\User\UserInterface;
use Zestic\GraphQL\GraphQLMessage;

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
