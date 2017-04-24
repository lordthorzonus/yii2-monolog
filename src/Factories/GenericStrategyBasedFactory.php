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
     * Makes the given class with config utilizing the resolved strategy based on class.
     *
     * @param string $className
     * @param array $config
     *
     * @see StrategyResolver::resolve()
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function makeWithStrategy(
        string $className,
        array $config = []
    ) {
        $strategy = $this->strategyResolver->resolve($className);
        $this->validateConfigParameters($strategy, $className, $config);

        $instance = Yii::$container->get($className, $strategy->getConstructorParameters($config));
        $configure = $strategy->getConfigurationCallable($config);

        return $configure($instance, $config);
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
    ) {
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
