<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog\CreationStrategies;

use ReflectionParameter;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class ReflectionStrategy implements CreationStrategyInterface
{
    /**
     * @var \ReflectionClass
     */
    private $handlerReflectionClass;

    /**
     * Initializes a new ReflectionStrategy.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->handlerReflectionClass = new \ReflectionClass($class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredParameters(): array
    {
        if ($this->handlerReflectionClass->getConstructor() === null) {
            return [];
        }

        return $this->getReflectionParametersFromReflectionClass()->reject(
            function (ReflectionParameter $constructorParameter) {
                return $constructorParameter->isOptional();
            }
        )->map(
            function (ReflectionParameter $constructorParameter) {
                return $constructorParameter->name;
            }
        )->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getConstructorParameters(array $config): array
    {
        if ($this->handlerReflectionClass->getConstructor() === null) {
            return [];
        }

        return $this->getReflectionParametersFromReflectionClass()->map(
            function (ReflectionParameter $constructorParameter) use ($config) {
                return $this->resolveConstructorParameterValue($constructorParameter, $config);
            }
        )->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationCallable(array $config): callable
    {
        return $config['configure'] ?? function ($instance) {
            return $instance;
        };
    }

    /**
     * Returns the value for the constructorParameter from given configuration array.
     *
     * @param ReflectionParameter $constructorParameter
     * @param array $config
     *
     * @return mixed
     *
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\base\InvalidConfigException
     * @throws \InvalidArgumentException
     */
    private function resolveConstructorParameterValue(ReflectionParameter $constructorParameter, array $config)
    {
        foreach ($config as $parameterName => $configuredParameterValue) {
            if ($constructorParameter->name === $parameterName) {
                return $configuredParameterValue;
            }
        }

        if ($constructorParameter->isDefaultValueAvailable()) {
            return $constructorParameter->getDefaultValue();
        }

        if ($constructorParameter->hasType() && ! $constructorParameter->getType()->isBuiltin()) {
            return \Yii::$container->get($constructorParameter->getClass()->name);
        }

        throw new InvalidArgumentException(
            "Expected to find key: '{$constructorParameter->name}' in the given config array but none found."
        );
    }

    /**
     * Returns the constructor parameters from the reflection class the strategy was initiated with.
     *
     * @return ReflectionParameter[]|Collection
     */
    private function getReflectionParametersFromReflectionClass()
    {
        return collect($this->handlerReflectionClass->getConstructor()->getParameters());
    }
}
