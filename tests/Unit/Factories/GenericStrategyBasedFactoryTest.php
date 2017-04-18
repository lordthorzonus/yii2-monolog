<?php

namespace leinonen\Yii2Monolog\Tests\Unit\Factories;

use Mockery as m;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\StreamHandler;
use leinonen\Yii2Monolog\CreationStrategies\StrategyResolver;
use leinonen\Yii2Monolog\Factories\GenericStrategyBasedFactory;
use leinonen\Yii2Monolog\CreationStrategies\StreamHandlerStrategy;
use leinonen\Yii2Monolog\CreationStrategies\CreationStrategyInterface;

class GenericStrategyBasedFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_make_classes_using_by_utilizing_strategies()
    {
        $config = [
            'path' => 'app.log',
            'level' => Logger::WARNING,
            'bubble' => false,
            'filePermissions' => 'something',
            'useLocking' => true,
        ];

        $mockStreamHandlerCreationStrategy = m::mock(StreamHandlerStrategy::class);
        $mockStreamHandlerCreationStrategy->shouldReceive('getRequiredParameters')->once()->andReturn(['path']);
        $mockStreamHandlerCreationStrategy->shouldReceive('getConstructorParameters')
            ->with($config)
            ->andReturn([
                'app.log',
                Logger::WARNING,
                false,
                'something',
                true,
            ]);

        $mockStrategyResolver = m::mock(StrategyResolver::class);
        $mockStrategyResolver->shouldReceive('resolve')
            ->with(StreamHandler::class)
            ->once()
            ->andReturn($mockStreamHandlerCreationStrategy);

        $factory = new GenericStrategyBasedFactory($mockStrategyResolver);
        $handler = $factory->makeWithStrategy(StreamHandler::class, $config);

        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertSame('app.log', $handler->getUrl());
        $this->assertSame(Logger::WARNING, $handler->getLevel());
        $this->assertFalse($handler->getBubble());
    }

    /**
     * @test
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage The parameter 'requiredParameter' is required for Monolog\Handler\StreamHandler
     */
    public function it_throws_an_exception_if_the_given_config_misses_required_parameters()
    {
        $config = [
            'optionalParameter' => true,
        ];

        $mockStreamHandlerCreationStrategy = m::mock(CreationStrategyInterface::class);
        $mockStreamHandlerCreationStrategy->shouldReceive('getRequiredParameters')->once()->andReturn(['requiredParameter']);

        $mockStrategyResolver = m::mock(StrategyResolver::class);
        $mockStrategyResolver->shouldReceive('resolve')
            ->with(StreamHandler::class)
            ->once()
            ->andReturn($mockStreamHandlerCreationStrategy);

        $factory = new GenericStrategyBasedFactory($mockStrategyResolver);
        $handler = $factory->makeWithStrategy(StreamHandler::class, $config);
    }
}
