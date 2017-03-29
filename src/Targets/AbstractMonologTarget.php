<?php

namespace leinonen\Yii2MonologTargets\Targets;

use leinonen\Yii2MonologTargets\Yii2LogMessage;
use Monolog\Logger;
use yii\log\Target;

abstract class AbstractMonologTarget extends Target
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Returns the messages from the Target wrapped in Yii2LogMessage.
     *
     * @return Yii2LogMessage[]|array
     */
    public function getMessages()
    {
        return \array_map(
            function ($message) {
                return new Yii2LogMessage($message);
            },
            $this->messages
        );
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        foreach ($this->getMessages() as $message) {
            $this->logger->log($message->getPsr3LogLevel(), $message->getMessage(), $message->getContext());
        }
    }

    /**
     * Sets the logger for the target.
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

}
