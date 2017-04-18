<?php

namespace leinonen\Yii2Monolog\Factories;

use Yii;
use yii\base\InvalidConfigException;
use leinonen\Yii2Monolog\CreationStrategies\StrategyResolver;
use leinonen\Yii2Monolog\CreationStrategies\CreationStrategyInterface;

class GenericStrategyBasedFactory
{
    /**
     * @var StrategyResolver
     */
    private $strategyResolver;

    /**
     * Initiates a new AbstractStrategyBasedFactory.
     *
     * @param StrategyResolver $strategyResolver
     */
    public function __construct(StrategyResolver $strategyResolver)
    {
        $this->strategyResolver = $strategyResolver;
    }

    /**
     * Makes the given class with the given strategy and config.
     *
     * @param string $className
     * @param array $config
     * @param CreationStrategyInterface|null $strategy If no strategy given the correct strategy will be resolved using
     * StrategyResolver
     *
     * @see StrategyResolver::resolve()
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function makeWithStrategy(
        string $className,
        array $config = [],
        CreationStrategyInterface $strategy = null
    ) {
        if ($strategy === null) {
            $strategy = $this->strategyResolver->resolve($className);
        }
        $this->validateConfigParameters($strategy, $className, $config);

        return Yii::$container->get($className, $strategy->getConstructorParameters($config));
    }

    /**
     * Validates the given config against the given strategy.
     *
     * @param CreationStrategyInterface $strategy
     * @param string $className
     * @param array $config
     *
     * @throws InvalidConfigException
     */
    private function validateConfigParameters(
        CreationStrategyInterface $strategy,
        string $className,
        array $config
    ): void {
        $requiredParameters = $strategy->getRequiredParameters();
        $givenParameters = \array_keys($config);

        foreach ($requiredParameters as $requiredParameter) {
            if (! \in_array($requiredParameter, $givenParameters, true)) {
                throw new InvalidConfigException(
                    "The parameter '{$requiredParameter}' is required for {$className}"
                );
            }
        }
    }
}
