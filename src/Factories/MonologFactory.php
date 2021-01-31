<?php

namespace bessonov87\Yii2Monolog\Factories;

use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;

class MonologFactory
{
    /**
     * @var HandlerFactory
     */
    private $handlerFactory;

    /**
     * @var ProcessorFactory
     */
    private $processorFactory;
    /**
     * @var FormatterFactory
     */
    private $formatterFactory;

    /**
     * Initializes a new MonologFactory.
     *
     * @param HandlerFactory $handlerFactory
     * @param ProcessorFactory $processorFactory
     * @param FormatterFactory $formatterFactory
     */
    public function __construct(
        HandlerFactory $handlerFactory,
        ProcessorFactory $processorFactory,
        FormatterFactory $formatterFactory
    ) {
        $this->handlerFactory = $handlerFactory;
        $this->processorFactory = $processorFactory;
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @param string $name
     * @param array $handlers
     * An array of handlers in format of:
     *  [
     *      HandlerClass::class => [
     *          'configKey' => 'value',
     *      ],
     *  ]
     *
     * @param array $processors
     * An array of processors in format of:
     *  [
     *      function ($record) {
     *          return $record;
     *      },
     *      MyProcessor::class => [
     *          'configKey' => 'value',
     *      ],
     * ]
     *
     * You can supply both callables and invokable classes.
     *
     * @return Logger
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    public function make(string $name, array $handlers = [], array $processors = []): Logger
    {
        $handlers = $this->makeHandlers($handlers);
        $processors = $this->makeProcessors($processors);

        return new Logger($name, $handlers, $processors);
    }

    /**
     * Returns an array of handlers based on the given configuration.
     *
     * @param array $handlerConfigs
     *
     * @see MonologFactory::make() for the array format.
     *
     * @return HandlerInterface[]
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    private function makeHandlers(array $handlerConfigs)
    {
        if (empty($handlerConfigs)) {
            return [];
        }

        return $this->mapConfigurations(
            $handlerConfigs,
            function ($handlerClass, $handlerConfig) {
                $formatter = $this->getFormatterFromHandlerConfig($handlerConfig);
                $processors = $this->getProcessorsFromHandlerConfig($handlerConfig);

                unset($handlerConfig['formatter'], $handlerConfig['processors']);

                return $this->handlerFactory->make($handlerClass, $handlerConfig, $formatter, $processors);
            }
        );
    }

    /**
     * Returns an array of processors based on the given configuration.
     *
     * @param array $processorConfigs
     *
     * @see MonologFactory::make() for the array format.
     *
     * @return callable[]
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    private function makeProcessors(array $processorConfigs): array
    {
        if (empty($processorConfigs)) {
            return [];
        }

        return $this->mapConfigurations(
            $processorConfigs,
            function ($processor, $config) {
                if (\is_callable($processor)) {
                    return $processor;
                }

                return $this->processorFactory->make($processor, $config);
            }
        );
    }

    /**
     * Maps the given configurations with the given callable.
     *
     * @param array $config
     * @param callable $mapFunction
     *
     * @return array
     */
    private function mapConfigurations(array $config, callable $mapFunction): array
    {
        return collect($config)->map(function ($configValue, $configKey) use ($mapFunction) {
            // In case the key is int assume that just the configurable class name has been given
            // and supply empty configurations.
            if (\is_int($configKey)) {
                return $mapFunction($configValue, []);
            }

            return $mapFunction($configKey, $configValue);
        })->values()->all();
    }

    /**
     * Returns the formatter from given handler config array.
     *
     * @param array $handlerConfig
     *
     * @return FormatterInterface|null
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    private function getFormatterFromHandlerConfig(array $handlerConfig)
    {
        if (isset($handlerConfig['formatter'])) {
            return $this->mapConfigurations(
                $handlerConfig['formatter'],
                function ($formatterClass, $formatterConfig) {
                    return $this->formatterFactory->make($formatterClass, $formatterConfig);
                }
            )[0];
        }
    }

    /**
     * Returns processors from the given handler config array.
     *
     * @param array $handlerConfig
     *
     * @return callable[]|array
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    private function getProcessorsFromHandlerConfig(array $handlerConfig): array
    {
        if (isset($handlerConfig['processors'])) {
            return $this->makeProcessors($handlerConfig['processors']);
        }

        return [];
    }
}
