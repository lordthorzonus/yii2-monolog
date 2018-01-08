<?php

namespace leinonen\Yii2Monolog\Tests\Unit\HandlerCreationStrategies;

use Monolog\Logger;
use ReflectionClass;
use ReflectionParameter;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\StreamHandler;
use leinonen\Yii2Monolog\CreationStrategies\StreamHandlerStrategy;

class StreamHandlerStrategyTest extends TestCase
{
    /** @test */
    public function it_returns_correct_required_parameters_for_a_stream_handler()
    {
        $constructorParameters = collect(
            (new ReflectionClass(StreamHandler::class))
                ->getConstructor()
                ->getParameters()
        );

        $requiredParameters = $constructorParameters->reject(
            function (ReflectionParameter $constructorParameter) {
                return $constructorParameter->isOptional();
            }
        )->map(
            function (ReflectionParameter $constructorParameter) {
                // We want to call the stream parameter path as Symfony does the same in it's Monolog config.
                if ($constructorParameter->name === 'stream') {
                    return 'path';
                }

                return $constructorParameter->name;
            }
        )->all();

        $strategy = new StreamHandlerStrategy();
        $this->assertSame($requiredParameters, $strategy->getRequiredParameters());
    }

    /** @test */
    public function it_returns_the_right_constructor_parameter_values_from_given_config()
    {
        $config = [
            'path' => 'a stream',
            'level' => Logger::WARNING,
            'bubble' => false,
            'filePermission' => 'some',
            'useLocking' => true,
        ];

        $strategy = new StreamHandlerStrategy();
        $this->assertSame(
            [
                'a stream',
                Logger::WARNING,
                false,
                'some',
                true,
            ],
            $strategy->getConstructorParameters($config)
        );
    }

    /** @test */
    public function it_should_fallback_to_correct_defaults_if_no_config_given()
    {
        $expectedValues = [];
        // First constructor parameter is stream which is required
        $expectedValues[] = 'a stream';
        $constructorParameters = (new ReflectionClass(StreamHandler::class))->getConstructor()->getParameters();

        foreach ($constructorParameters as $constructorParameter) {
            if ($constructorParameter->isOptional()) {
                $expectedValues[] = $constructorParameter->getDefaultValue();
            }
        }

        $config = [
            'path' => 'a stream',
        ];

        $strategy = new StreamHandlerStrategy();
        $this->assertSame($expectedValues, $strategy->getConstructorParameters($config));
    }

    /** @test */
    public function it_uses_yiis_get_alias_to_resolve_the_path_value()
    {
        \Yii::setAlias('@myAlias', '/awesome');

        $config = [
            'path' => '@myAlias/test',
        ];
        $strategy = new StreamHandlerStrategy();

        $this->assertSame('/awesome/test', $strategy->getConstructorParameters($config)[0]);
    }

    /** @test */
    public function it_should_return_a_callable_that_just_returns_the_given_instance_from_configuration_callable()
    {
        $streamHandler = new StreamHandler('test');
        $strategy = new StreamHandlerStrategy();

        $configure = $strategy->getConfigurationCallable([]);

        $this->assertSame($streamHandler, $configure($streamHandler));
    }
}
