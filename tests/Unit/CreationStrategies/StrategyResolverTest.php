<?php

namespace leinonen\Yii2Monolog\Tests\Unit\HandlerCreationStrategies;

use leinonen\Yii2Monolog\CreationStrategies\ReflectionStrategy;
use leinonen\Yii2Monolog\CreationStrategies\StrategyResolver;
use leinonen\Yii2Monolog\CreationStrategies\StreamHandlerStrategy;
use leinonen\Yii2Monolog\Yii2LogMessage;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;

class StrategyResolverTest extends TestCase
{
    /** @test */
    public function it_can_resolve_a_creation_strategy_for_a_given_handler()
    {
        $resolver = new StrategyResolver();

        $this->assertInstanceOf(StreamHandlerStrategy::class, $resolver->resolve(StreamHandler::class));
    }

    /** @test */
    public function it_resolves_to_reflection_handler_creation_strategy_for_handler_classes_that_do_not_have_corresponding_creation_strategy()
    {
        $resolver = new StrategyResolver();

        $this->assertInstanceOf(ReflectionStrategy::class, $resolver->resolve(Yii2LogMessage::class));
    }
}
