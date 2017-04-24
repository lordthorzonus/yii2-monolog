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
use Yii;

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

        // The created class in the end should be retrieved from dependency injection container
        $createdHandler = new StreamHandler('app.log', Logger::WARNING, false, 'something', true);
        Yii::$container->set(
            StreamHandler::class,
            function () use ($createdHandler) {
                return $createdHandler;
            }
        );

        // We assert that the configureCallable is called after the class has been resolved from the DI.
        $callbackAssessor = m::mock('StdClass');
        $callbackAssessor->shouldReceive('doSomething')->with($createdHandler)->once();
        $mockConfigureCallable = function ($instance, $receivedConfig) use ($config, $createdHandler, $callbackAssessor) {
            $callbackAssessor->doSomething($instance);
            $this->assertSame($createdHandler, $instance);
            $this->assertSame($config, $receivedConfig);

            return $instance;
        };

        $mockStreamHandlerCreationStrategy = m::mock(StreamHandlerStrategy::class);
        $mockStreamHandlerCreationStrategy->shouldReceive('getRequiredParameters')->once()->andReturn(['path']);
        $mockStreamHandlerCreationStrategy->shouldReceive('getConstructorParameters')
            ->once()
            ->with($config)
            ->andReturn(
                [
                    'app.log',
                    Logger::WARNING,
                    false,
                    'something',
                    true,
                ]
            );
        $mockStreamHandlerCreationStrategy->shouldReceive('getConfigurationCallable')
            ->once()
            ->with($config)
            ->andReturn($mockConfigureCallable);

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
        $mockStreamHandlerCreationStrategy->shouldReceive('getRequiredParameters')->once()->andReturn(
            ['requiredParameter']
        );

        $mockStrategyResolver = m::mock(StrategyResolver::class);
        $mockStrategyResolver->shouldReceive('resolve')
            ->with(StreamHandler::class)
            ->once()
            ->andReturn($mockStreamHandlerCreationStrategy);

        $factory = new GenericStrategyBasedFactory($mockStrategyResolver);
        $handler = $factory->makeWithStrategy(StreamHandler::class, $config);
    }
}
