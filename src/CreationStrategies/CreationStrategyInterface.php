<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog\CreationStrategies;

interface CreationStrategyInterface
{
    /**
     * Returns required parameter names as array.
     *
     * @return array|string[]
     */
    public function getRequiredParameters(): array;

    /**
     * Returns the constructor parameters based on the given config.
     *
     * @param array $config
     *
     * @return array
     */
    public function getConstructorParameters(array $config): array;

    /**
     * Returns the callable function which is used to configure the created instance.
     * The function must return the instance it receives.
     *
     * @param array $config
     *
     * @return callable
     */
    public function getConfigurationCallable(array $config): callable;
}
