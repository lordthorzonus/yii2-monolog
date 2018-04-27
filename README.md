[![Latest Stable Version](https://poser.pugx.org/leinonen/yii2-monolog/v/stable)](https://packagist.org/packages/leinonen/yii2-monolog)
[![Total Downloads](https://poser.pugx.org/leinonen/yii2-monolog/downloads)](https://packagist.org/packages/leinonen/yii2-monolog)
[![Latest Unstable Version](https://poser.pugx.org/leinonen/yii2-monolog/v/unstable)](https://packagist.org/packages/leinonen/yii2-monolog)
[![License](https://poser.pugx.org/leinonen/yii2-monolog/license)](https://packagist.org/packages/leinonen/yii2-monolog)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lordthorzonus/yii2-monolog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lordthorzonus/yii2-monolog/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/lordthorzonus/yii2-monolog/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lordthorzonus/yii2-monolog/?branch=master)
[![Build Status](https://travis-ci.org/lordthorzonus/yii2-monolog.svg?branch=master)](https://travis-ci.org/lordthorzonus/yii2-monolog)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/44a2e0f3-cde6-48b9-b484-8243a64145de/mini.png)](https://insight.sensiolabs.com/projects/44a2e0f3-cde6-48b9-b484-8243a64145de)

## Yii2 Monolog

Yii2 Monolog allows one to use [Monolog](https://github.com/Seldaek/monolog) easily with Yii2. For instructions regarding Monolog itself please refer to the [documentation](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md).

Table of contents
=================
* [Monolog Usage](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md)
* [Installation](#installation)
* [Configuration](#configuration)
    * [Channels](#channels)
    * [Handlers](#handlers)
    * [Formatters](#formatters)
    * [Processors](#processors)
    * [Configuring Handlers/Formatters/Processors after creation](#configuring-handlersformattersprocessors-after-creation)
* [Usage](#usage)
    * [Using the component as a Yii's log target](#using-the-component-as-a-yiis-log-target)
    * [Using the component standalone](#using-the-component-standalone)

## Installation
Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

```bash
composer require leinonen/yii2-monolog
```

## Configuration
Configure the `leinonen\Yii2Monolog\Yii2Monolog` as a bootstrapped component in your application config.

An example configuration of one log channel called `myLoggerChannel` with a basic StreamHandler and a UidProcessor:
```php
use leinonen\Yii2Monolog\MonologTarget;
use leinonen\Yii2Monolog\Yii2Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

...
[
    'bootstrap' => ['monolog'],
    'components' => [
        ...
        'monolog' => [
            'class' => Yii2Monolog::class,
            'channels' => [
                'myLoggerChannel' => [
                    'handlers' => [
                        StreamHandler::class => [
                            'path' => '@app/runtime/logs/someLog.log',
                        ],
                    ],
                    'processors' => [
                        UidProcessor::class,
                    ],
                ],
            ],
        ],
    ],
    ...
]
```

### Channels
To see the core concepts about Monolog channels check the [Offical Documentation for Monolog](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#core-concepts).

This component allows registering multiple channels with `channel name` => ` configuration array`  key value pairs with the `channels`  configuration key.

#### Main channel
The component automatically registers a main channel which is used when requesting `Psr\Log\LoggerInterface` from the DI container or when fetching a Logger from the component without specifying a channel name.

The main channel is configurable with the configuration key `mainChannel`
```php

[
    'components' => [
        ...
        'monolog' => [
            'class' => Yii2Monolog::class,
            'channels' => [
                'myFirstChannel' => [
                    ...
                ],
                'someOtherAwesomeChannel' => [
                    ...
                ],
            ],
            'mainChannel' => 'someOtherAwesomeChannel'
        ]
    ]
]
```

If the main channel is null or not specified at all, the first channel from the channels list will be used as the main channel. With the example config above it would be `myFirstChannel`.

### Handlers
The package supports all official and 3rd party handlers for Monolog. It uses `leinonen\Yii2Monolog\CreationStrategies\ReflectionStrategy` by default in background to figure out the config values which the handler is to be constructed with. The handlers are defined with a config key `handlers` in the Monolog configuration. All the handlers are resolved through Yii's DI container making it easier to implement your own custom handlers.

Example handler configuration with a stack of two handlers:

```php
[
    ...
        'monolog' => [
            'channels' => [
                'myLoggerChannel' => [
                    'handlers' => [
                        SlackbotHandler::class => [
                            'slackTeam' => 'myTeam',
                            'token' => 'mySecretSlackToken',
                            'channel' => 'myChannel',
                        ],
                        RotatingFileHandler::class => [
                            'path' => '@app/runtime/logs/myRotatinglog.log',
                            'maxFiles' => 10,
                        ],
                    ],
                ],
            ],
        ],
    ...
]
```

You can find the available handlers from the [Monolog\Handler namespace](https://github.com/Seldaek/monolog/tree/master/src/Monolog/Handler)

#### Yii2 specific handlers
The package also provides a specific creation strategies for couple of handlers to help integrating with Yii2.

##### StreamHandler
The `path` config value is resolved through Yii's `getAlias()` method making it possible to use aliases such as `@app` in the config. Use this instead of `stream`

##### RotatingFileHandler
The `path` config value is resolved through Yii's `getAlias()` method making it possible to use aliases such as `@app` in the config. Use this instead of `filename`

### Formatters
The package supports all official and 3rd party formatters for Monolog. It uses `leinonen\Yii2Monolog\CreationStrategies\ReflectionStrategy` by default in background to figure out the config values which the formatter is to be constructed with. All the formatters are resolved through Yii's DI container making it easier to implement your own custom formatters.

It's possible to configure a custom formatter for each handler. The formatter is configured with a `formatter` key in the handler's config array:

```php
'handlers' => [
    StreamHandler::class => [
        'path' => '@app/runtime/logs/myLog.log',
        'formatter' => [
            LineFormatter::class => [
                'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'dateFormat' => "Y-m-d\TH:i:sP"
            ]
        ]
    ]
]
```

You can find available formatters from the [Monolog/Formatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter) namespace.

### Processors
The package supports all official and 3rd party processors for Monolog. It uses `leinonen\Yii2Monolog\CreationStrategies\ReflectionStrategy` by default in background to figure out the config values which the processor is to be constructed with. All the processors are resolved through Yii's DI container making it easier to implement your own custom processors.

The processors can be defined globally for one target or specifically for a handler. Processors are configured with a `processors` key in the config array with an array of callables:

```php
[
    ...
        'monolog' => [
            'channels' => [
                'myLoggerChannel' => [
                    'processors' => [
                        GitProcessor::class,
                        function ($record) {
                            $record['myCustomData'] = 'test';

                            return $record;
                        },
                    ],
                ],
            ],
        ],
    ...
]
```

Or config to a specific handler:

```php
...
'handlers' => [
    StreamHandler::class => [
    'path' => '@app/runtime/logs/myLog.log',
    'processors' => [
        GitProcessor::class,
        function ($record) {
            $record['myCustomData'] = 'test';

            return $record;
        }
    ]
]
```

You can find available processors from the [Monolog/Processor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor) namespace.

### Configuring Handlers/Formatters/Processors after creation
For further customisation it's possible to specify a `configure` key for all classes that are created with `leinonen\Yii2Monolog\CreationStrategies\ReflectionStrategy` . The configure key must be a callable which receives the created class instance and config. It also has to return the instance. It is called just after the class has been resolved from Yii's DI container.
For example it's possible to customize `Monolog\Handler\RotatingFileHandler`'s filename format:

```php
'handlers' => [
    RotatingFileHandler::class => [
        'path' => 'something',
        'maxFiles' => 2,
        'configure' => function (RotatingFileHandler $handler, $config) {
            $handler->setFilenameFormat('myprefix-{filename}-{date}', 'Y-m-d');

            return $handler;
        }
    ],
]
```
## Usage

### Using the component as a Yii's log target

If you want to integrate this component into an existing project which utilizes Yii's own logger, you can configure channels as log targets easily. [See Yii's documentation about log targets here](http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html#log-targets).

Example configuration:

```php
use leinonen\Yii2Monolog\MonologTarget;
use leinonen\Yii2Monolog\Yii2Monolog;

[
    'bootstrap' => ['monolog', 'log'],
    'components' => [
        ...
        'monolog' => [
            'class' => Yii2Monolog::class,
            'channels' => [
                'myFirstChannel' => [
                    ...
                ],
                'someOtherAwesomeChannel' => [
                    ...
                ],
            ],
            'mainChannel' => 'someOtherAwesomeChannel'
        ],
        'log' => [
            'targets' => [
                [
                    'class' => MonologTarget::class,
                    'channel' => 'myFirstChannel',
                    'levels' => ['error', 'warning']
                ],
            ]
        ]
    ]
]
```

In this case all the Yii's loggers messages will go through the handler / processor stack of `myFirstChannel` logger without touching any of your existing code.

```php
\Yii::warning('hello');
\Yii::error('world!');
```

If you leave the channel configuration out the target will use the main channel configured in the component.

### Using the component standalone

If you want to not use Yii's logger at all it's possible to use this component as a completely standalone logger.

Fetching a specific logger from the component:

```php
 $myChannelLogger = Yii::$app->monolog->getLogger('myChannel');
 $myChannelLogger->critical('help me!');

 $mainChannelLogger = Yii::$app->monolog->getLogger();
 $mainChannelLogger->notice('This was a log message through the main channel');
```

As the main channel is registered as the implementation of the `Psr\Log\LoggerInterface`  you can also use constructor injection in your controllers:

```php
class SuperController
{
    private $logger;

    public function __construct($id, $module, LoggerInterface $logger, $config = [])
    {
        $this->logger = $logger;
        parent::__construct($id, $module, $config);
    }

    public function actionExample()
    {
        $this->logger->notice('Action Example was called');
    }
}
```
