<?php


namespace leinonen\Yii2Monolog;


use leinonen\Yii2Monolog\Factories\MonologFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;

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
     * Initiates a new Yii2Monolog.
     *
     * @param MonologFactory $monologFactory
     * @param array $config
     */
    public function __construct(MonologFactory $monologFactory, array $config = [])
    {
        $this->monologFactory = $monologFactory;
        parent::__construct($config);
    }

    /**
     * Returns the given logger channel.
     *
     * @param string $channel
     *
     * @return Logger
     */
    public function getLogger(string $channel = null): Logger
    {
        if ($channel === null) {
            $channel = $this->getMainChannel();
        }

        return Yii::$container->get($this->getLoggerAlias($channel));
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

            $this->registerLogChannel($channelName, $handlers, $processors);
        }
    }

    /**
     * Registers a new log channel into Yii's DI container.
     * The channel will be registered with an alias yii2-monolog.ChannelName.
     *
     * @param string $channelName
     * @param array $handlers
     * @param array $processors
     */
    private function registerLogChannel(string $channelName, array $handlers, array $processors)
    {
        $serviceName = $this->getLoggerAlias($channelName);

        Yii::$container->setSingleton($serviceName, function () use ($channelName, $handlers, $processors) {
            return $this->monologFactory->make($channelName, $handlers, $processors);
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

    /**
     * Registers the main channel to be used for Psr LoggerInterface.
     */
    private function registerPsrLogger()
    {
        Yii::$container->setSingleton(LoggerInterface::class, function () {
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
