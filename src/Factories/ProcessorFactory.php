<?php

namespace bessonov87\Yii2Monolog\Factories;

class ProcessorFactory
{
    /**
     * @var GenericStrategyBasedFactory
     */
    private $genericFactory;

    /**
     * Initializes a new ProcessorFactory.
     *
     * @param GenericStrategyBasedFactory $genericFactory
     */
    public function __construct(GenericStrategyBasedFactory $genericFactory)
    {
        $this->genericFactory = $genericFactory;
    }

    /**
     * Makes a new Processor from the given processor class and config.
     *
     * @param string $processorClass
     * @param array $config
     *
     * @return callable
     *
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function make(string $processorClass, array $config = []): callable
    {
        $this->validateProcessorClass($processorClass);

        return $this->genericFactory->makeWithStrategy($processorClass, $config);
    }

    /**
     * Validates the given processor class.
     *
     * @param $processorClass
     *
     * @throws \InvalidArgumentException
     */
    private function validateProcessorClass(string $processorClass)
    {
        if (! (new \ReflectionClass($processorClass))->hasMethod('__invoke')) {
            throw new \InvalidArgumentException("{$processorClass} isn't callable. All processor classes must implement the __invoke method.");
        }
    }
}
