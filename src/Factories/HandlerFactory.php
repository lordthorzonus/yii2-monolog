<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog\Factories;

use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;

class HandlerFactory
{
    /**
     * @var GenericStrategyBasedFactory
     */
    private $genericFactory;

    /**
     * Initializes a new HandlerFactory.
     *
     * @param GenericStrategyBasedFactory $genericFactory
     */
    public function __construct(GenericStrategyBasedFactory $genericFactory)
    {
        $this->genericFactory = $genericFactory;
    }

    /**
     * Returns an instance of the given handler class with the given configuration.
     *
     * @param string $handlerClass
     * @param array $config
     * @param FormatterInterface|null $formatter
     * @param array|callable[] $processors
     *
     * @return HandlerInterface
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    public function make(
        string $handlerClass,
        array $config = [],
        FormatterInterface $formatter = null,
        array $processors = []
    ): HandlerInterface {
        $this->validateHandler($handlerClass);

        /** @var HandlerInterface $handler */
        $handler = $this->genericFactory->makeWithStrategy($handlerClass, $config);

        if ($formatter !== null) {
            $handler->setFormatter($formatter);
        }

        foreach ($processors as $processor) {
            $handler->pushProcessor($processor);
        }

        return $handler;
    }

    /**
     * Validates the given handler class.
     *
     * @param string $handlerClass
     *
     * @throws \InvalidArgumentException
     */
    private function validateHandler(string $handlerClass)
    {
        $handlerInterface = HandlerInterface::class;
        if (! (new \ReflectionClass($handlerClass))->implementsInterface($handlerInterface)) {
            throw new \InvalidArgumentException("{$handlerClass} doesn't implement {$handlerInterface}");
        }
    }
}
