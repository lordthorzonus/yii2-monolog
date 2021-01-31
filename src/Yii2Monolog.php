<?php

namespace bessonov87\Yii2Monolog;

use Yii;
use Monolog\Logger;
use yii\base\Component;
use yii\base\BootstrapInterface;
use bessonov87\Yii2Monolog\Factories\MonologFactory;

class Yii2Monolog extends Component implements BootstrapInterface
{
    /**
     * @var array
     */
    private $channels;

    /**
     * @var MonologFactory
     */
    private $monologFactory;

    /**
     * @var string
     */
    private $mainChannel;

    /**
     * @var LoggerRegistry
     */
    private $loggerRegistry;

    /**
     * Initiates a new Yii2Monolog.
     *
     * @param MonologFactory $monologFactory
     * @param LoggerRegistry $loggerRegistry
     * @param array $config
     */
    public function __construct(MonologFactory $monologFactory, LoggerRegistry $loggerRegistry, array $config = [])
    {
        $this->monologFactory = $monologFactory;
        $this->loggerRegistry = $loggerRegistry;
        parent::__construct($config);
    }

    /**
     * Returns the given logger channel.
     *
     * @param null|string $channel
     *
     * @return Logger
     */
    public function getLogger(string $channel = null): Logger
    {
        if ($channel === null) {
            $channel = $this->getMainChannel();
        }

        return $this->loggerRegistry->getLogger($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        $this->registerLoggers();
        $this->registerPsrLogger();
    }

    /**
     * @param array $channelConfiguration
     */
    public function setChannels(array $channelConfiguration)
    {
        $this->channels = $channelConfiguration;
    }

    /**
     * @param string $channelName
     */
    public function setMainChannel(string $channelName)
    {
        $this->mainChannel = $channelName;
    }

    /**
     * Registers loggers into Yii's DI container.
     */
    private function registerLoggers()
    {
        foreach ($this->channels as $configuredChannelName => $channelConfiguration) {
            $channelName = $configuredChannelName;
            $handlers = $channelConfiguration['handlers'] ?? [];
            $processors = $channelConfiguration['processors'] ?? [];

            $this->loggerRegistry->registerLogChannel($channelName, function () use ($channelName, $handlers, $processors) {
                return $this->monologFactory->make($channelName, $handlers, $processors);
            });
        }
    }

    /**
     * Registers the main channel to be used for Psr LoggerInterface.
     */
    private function registerPsrLogger()
    {
        $this->loggerRegistry->registerPsrLogger(function () {
            return $this->getLogger($this->getMainChannel());
        });
    }

    /**
     * Returns the main channel to be used for Yii2Monolog component.
     *
     * @return string
     */
    private function getMainChannel(): string
    {
        if ($this->mainChannel === null) {
            return array_keys($this->channels)[0];
        }

        return $this->mainChannel;
    }
}
