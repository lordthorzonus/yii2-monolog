<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog\CreationStrategies;

use Monolog\Logger;

class RotatingFileHandlerStrategy implements CreationStrategyInterface
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
        $filename = \Yii::getAlias($config['path']);
        $maxFiles = $config['maxFiles'] ?? 0;
        $level = $config['level'] ?? Logger::DEBUG;
        $bubble = $config['bubble'] ?? true;
        $filePermission = $config['filePermission'] ?? null;
        $useLocking = $config['useLocking'] ?? false;

        return [$filename, $maxFiles, $level, $bubble, $filePermission, $useLocking];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationCallable(array $config): callable
    {
        return $config['configure'] ?? function ($instance) {
            return $instance;
        };
    }
}
