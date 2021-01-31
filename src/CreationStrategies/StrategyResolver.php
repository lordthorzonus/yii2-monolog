<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog\CreationStrategies;

class StrategyResolver
{
    const CREATION_STRATEGY_NAMESPACE = 'bessonov87\Yii2Monolog\CreationStrategies';

    /**
     * Returns creation strategy to be used for given class name.
     *
     * @param string $class
     *
     * @return CreationStrategyInterface
     */
    public function resolve(string $class): CreationStrategyInterface
    {
        $shortClassName = (new \ReflectionClass($class))->getShortName();
        $strategyClassName = self::CREATION_STRATEGY_NAMESPACE . "\\{$shortClassName}Strategy";

        if (\class_exists($strategyClassName)) {
            return new $strategyClassName;
        }

        return new ReflectionStrategy($class);
    }
}
