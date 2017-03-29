<?php

namespace leinonen\Yii2MonologTargets\Targets;

use leinonen\Yii2MonologTargets\HandlerFactories\StreamHandlerFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class StreamTarget extends AbstractMonologTarget
{
    /**
     * Initiates a new StreamTarget.
     *
     * @param Logger $logger
     * @param StreamHandlerFactory $handlerFactory
     * @param array $config
     */
    public function __construct(Logger $logger, StreamHandlerFactory $handlerFactory, array $config = [])
    {
        $handler = $handlerFactory->make($config);
        $logger->pushHandler($handler);
        $this->setLogger($logger);

        parent::__construct($config);
    }

}
