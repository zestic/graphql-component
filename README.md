GraphQL Component
=================

Bridge between Webonxy and Symfony Messenger

All messages must extend GraphQLMessage

Config
```
'graphQL'      => [
    'mutations'    => [
        'addEmailToList'   => App\Domain\Message\Mutation\AddEmailToListMessage::class,
        'addEmailToList'                    =>  [
            'bus' => 'messenger.not-default-bus', \\ optional
            'handler' => App\Domain\Handler\Mutation\AddEmailToListHandler::class,
            'message' => App\Domain\Message\Mutation\AddEmailToListMessage::class,
        ],
    ],
    'queries'      => [
        'ping'                    =>  [
            'bus' => 'messenger.not-default-bus', \\ optional
            'handler' => App\Domain\Handler\Query\PingHandler::class,
            'message' => App\Domain\Message\Query\PingMessage::class,
        ],
    ],
    'middleware'   => [
        'allowedHeaders' => [
            'application/graphql',
            'application/json',
        ],
    ],
    'schema'       => App\GraphQL\Schema::class,
];
```

In `config.php` add the `ConfigProcess.php` class to the post processors

```php 
$postProcessors = [
    \IamPersistent\GraphQL\ConfigProcessor::class,
];
$aggregator = new ConfigAggregator([
        ...
    ], $cacheConfig['config_cache_path'], $postProcessors); 

return $aggregator->getMergedConfig();
```

This will wire up the connections between the handlers and messages in Symfony Messenger
and it also builds the config for `the RequestDispatcher`
