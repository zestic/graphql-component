GraphQL Component
=================

Bridge between Webonxy and Symfony Messenger

All messages must extend GraphQLMessage

Config
```
 'graphQL'      => [
    'buses'       => [
        'mutations' => 'messenger.command.bus',
        'queries'   => 'messenger.query.bus',
    ],
    'mutations'    => [
        'addEmailToList'   => App\Domain\Message\Mutation\AddEmailToList::class,
    ],
    'queries'      => [
       'ping'                    =>  [
            'config' => 'messenger.not-default-bus',
            'message' => App\Domain\Message\Query\PingMessage::class,
    ],
    'middleware'   => [
        'allowedHeaders' => [
            'application/graphql',
            'application/json',
        ],
    ],
    'schema'       => App\GraphQL\Schema::class,
    'serverConfig' => [
        'fieldResolver' => IamPersistent\GraphQL\Resolver\MasterResolver::class,
    ],
 ]
```
