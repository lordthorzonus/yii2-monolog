<?php

namespace bessonov87\Yii2Monolog\Factories;

use Monolog\Formatter\FormatterInterface;

class FormatterFactory
{
    /**
     * @var GenericStrategyBasedFactory
     */
    private $genericFactory;

    /**
     * Initializes a new FormatterFactory.
     *
     * @param GenericStrategyBasedFactory $genericFactory
     */
    public function __construct(GenericStrategyBasedFactory $genericFactory)
    {
        $this->genericFactory = $genericFactory;
    }

    /**
     * Makes a new Formatter with given formatter class and config.
     *
     * @param string $formatterClass
     * @param array $config
     *
     * @return FormatterInterface
     * @throws \InvalidArgumentException
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function make(string $formatterClass, array $config = []): FormatterInterface
    {
        $this->validateFormatter($formatterClass);

        return $this->genericFactory->makeWithStrategy($formatterClass, $config);
    }

    /**
     * Validates the given handler class.
     *
     * @param string $formatterClass
     *
     * @throws \InvalidArgumentException
     */
    private function validateFormatter(string $formatterClass)
    {
        $formatterInterface = FormatterInterface::class;
        if (! (new \ReflectionClass($formatterClass))->implementsInterface($formatterInterface)) {
            throw new \InvalidArgumentException("{$formatterClass} doesn't implement {$formatterInterface}");
        }
    }
}
