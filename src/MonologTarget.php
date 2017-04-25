<?php

declare(strict_types=1);

namespace leinonen\Yii2Monolog;

use Illuminate\Support\Collection;
use Monolog\Logger;
use yii\log\Target;
use leinonen\Yii2Monolog\Factories\MonologFactory;

class MonologTarget extends Target
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $handlers = [];

    /**
     * @var array
     */
    private $processors = [];

    /**
     * @var string
     */
    private $channel = 'main';

    /**
     * @var MonologFactory
     */
    private $monologFactory;

    /**
     * Initializes a new MonologTarget.
     *
     * @param MonologFactory $monologFactory
     * @param array $config
     */
    public function __construct(MonologFactory $monologFactory, $config = [])
    {
        $this->monologFactory = $monologFactory;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->logger = $this->monologFactory->make(
            $this->channel,
            $this->handlers,
            $this->processors
        );

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

    /**
     * @param array $handlers
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param array $processors
     */
    public function setProcessors(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @param string $channel
     */
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
