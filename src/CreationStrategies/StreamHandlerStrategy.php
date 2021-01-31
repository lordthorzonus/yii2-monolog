<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog\CreationStrategies;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class StreamHandlerStrategy implements CreationStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRequiredParameters(): array
    {
        return [
            'path',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstructorParameters(array $config): array
    {
        $stream = \Yii::getAlias($config['path']);
        $level = $config['level'] ?? Logger::DEBUG;
        $bubble = $config['bubble'] ?? true;
        $filePermission = $config['filePermission'] ?? null;
        $useLocking = $config['useLocking'] ?? false;

        return [$stream, $level, $bubble, $filePermission, $useLocking];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationCallable(array $config): callable
    {
        return function (StreamHandler $instance) {
            return $instance;
        };
    }
}
