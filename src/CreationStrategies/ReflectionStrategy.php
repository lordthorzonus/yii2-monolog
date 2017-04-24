<?php

declare(strict_types=1);

namespace leinonen\Yii2Monolog\CreationStrategies;

use ReflectionParameter;
use InvalidArgumentException;

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

        $requiredParameters = \array_values(
            \array_filter(
                $this->handlerReflectionClass->getConstructor()->getParameters(),
                function (ReflectionParameter $constructorParameter) {
                    return ! $constructorParameter->isOptional();
                }
            )
        );

        return \array_map(
            function (ReflectionParameter $parameter) {
                return $parameter->name;
            },
            $requiredParameters
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConstructorParameters(array $config): array
    {
        if ($this->handlerReflectionClass->getConstructor() === null) {
            return [];
        }

        return array_map(
            function (ReflectionParameter $constructorParameter) use ($config) {
                return $this->resolveConstructorParameterValue($constructorParameter, $config);
            },
            $this->handlerReflectionClass->getConstructor()->getParameters()
        );
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
}
