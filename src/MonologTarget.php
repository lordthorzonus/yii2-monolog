<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog;

use Monolog\Logger;
use yii\log\Target;
use Illuminate\Support\Collection;

class MonologTarget extends Target
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $channel = null;

    /**
     * @var LoggerRegistry
     */
    private $loggerRegistry;

    /**
     * Initializes a new MonologTarget.
     *
     * @param LoggerRegistry $loggerRegistry
     * @param array $config
     */
    public function __construct(LoggerRegistry $loggerRegistry, $config = [])
    {
        $this->loggerRegistry = $loggerRegistry;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->logger = $this->loggerRegistry->getLogger($this->channel);

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $this->getMessages()->each(function (Yii2LogMessage $message) {
            $this->logger->log($message->getPsr3LogLevel(), $message->getMessage(), $message->getContext());
        });
    }

    public function setChannel(string $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Returns the messages from the Target wrapped in Yii2LogMessage.
     *
     * @return Yii2LogMessage[]|Collection
     */
    private function getMessages(): Collection
    {
        return collect($this->messages)->map(function ($message) {
            return new Yii2LogMessage($message);
        });
    }
}
