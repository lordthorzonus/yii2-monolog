<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog;

use Yii;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerRegistry
{
    /**
     * Returns the corresponding logger for the given channelName.
     *
     * @param string $channelName
     *
     * @return Logger
     */
    public function getLogger(string $channelName): Logger
    {
        return Yii::$container->get($this->getLoggerAlias($channelName));
    }

    /**
     * Registers a new log channel into Yii's DI container.
     * The channel will be registered with an alias yii2-monolog.ChannelName.
     *
     * @param string $channelName
     * @param callable $loggerCreationCallable The callable which should return the logger.
     */
    public function registerLogChannel(string $channelName, callable $loggerCreationCallable)
    {
        $serviceName = $this->getLoggerAlias($channelName);

        Yii::$container->setSingleton($serviceName, function () use ($channelName, $loggerCreationCallable) {
            return $loggerCreationCallable($channelName);
        });
    }

    /**
     * Registers the logger to be used with the Psr LoggerInterface.
     *
     * @param callable $loggerCreationCallable
     */
    public function registerPsrLogger(callable $loggerCreationCallable)
    {
        Yii::$container->setSingleton(LoggerInterface::class, function () use ($loggerCreationCallable) {
            return $loggerCreationCallable();
        });
    }

    /**
     * @param string $channelName
     *
     * @return string
     */
    private function getLoggerAlias(string $channelName): string
    {
        return "yii2-monolog.{$channelName}";
    }
}
