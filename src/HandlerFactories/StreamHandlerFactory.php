<?php


namespace leinonen\Yii2MonologTargets\HandlerFactories;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class StreamHandlerFactory
{
    public function make(array $config): StreamHandler
    {
        if (!isset($config['path'])) {
            throw new \InvalidArgumentException('You need to define a stream for \Monolog\Handler\StreamHandler');
        }

        $stream = \Yii::getAlias($config['path']);
        $level = $config['level'] ?? Logger::DEBUG;
        $bubble = $config['bubble'] ?? true;
        $filePermission = $config['filePermission'] ?? null;
        $useLocking = $config['useLocking'] ?? false;

        return new StreamHandler($stream, $level, $bubble, $filePermission, $useLocking);
    }
}
