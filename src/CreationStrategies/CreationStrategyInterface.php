<?php

declare(strict_types=1);

namespace leinonen\Yii2Monolog\CreationStrategies;

interface CreationStrategyInterface
{
    /**
     * Returns required parameter names as array.
     *
     * @return array
     */
    public function getRequiredParameters(): array;

    /**
     * @param array $config
     *
     * @return array
     */
    public function getConstructorParameters(array $config): array;
}
