<?php

namespace leinonen\Yii2MonogTargets\Tests\Unit\CreationStrategies;

use Yii;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use leinonen\Yii2Monolog\CreationStrategies\ReflectionStrategy;

class ReflectionStrategyTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_return_required_parameters_based_on_the_given_class_constructor()
    {
        $strategy = new ReflectionStrategy(TwoRequiredParameters::class);
        $this->assertSame(['required1', 'required2'], $strategy->getRequiredParameters());

        $strategy = new ReflectionStrategy(OneRequiredParameter::class);
        $this->assertSame(['required1'], $strategy->getRequiredParameters());
    }

    /** @test */
    public function it_can_return_the_given_config_as_constructor_parameters_in_right_order()
    {
        $config = [
            'required2' => 2,
            'required1' => 1,
        ];

        $strategy = new ReflectionStrategy(NoOptionalParameters::class);
        $this->assertSame([1, 2], $strategy->getConstructorParameters($config));
    }

    /** @test */
    public function it_uses_the_default_value_if_no_such_key_in_the_given_config_array()
    {
        $config = [
            'required1' => true,
        ];

        $strategy = new ReflectionStrategy(OneRequiredParameter::class);
        $this->assertSame([true, null, true, 1], $strategy->getConstructorParameters($config));

        $config = [
            'required1' => 1,
            'optional2' => 'something',
        ];

        $this->assertSame([1, null, 'something', 1], $strategy->getConstructorParameters($config));
    }

    /** @test */
    public function it_does_not_care_if_there_is_extra_configuration_keys_in_the_config_array()
    {
        $config = [
            'useless' => true,
            'im_not_needed' => 'hahaha',
            'required1' => true,
        ];

        $strategy = new ReflectionStrategy(OneRequiredParameter::class);
        $this->assertSame([true, null, true, 1], $strategy->getConstructorParameters($config));
    }

    /** @test */
    public function it_should_resolve_typehinted_classes_from_yiis_di_container_if_possible()
    {
        $config = [
            'required' => 1,
        ];

        $strategy = new ReflectionStrategy(TypeHintedHandler::class);
        $parameters = $strategy->getConstructorParameters($config);

        $this->assertSame(1, $parameters[0]);
        $this->assertInstanceOf(DummyClass::class, $parameters[1]);
        $this->assertTrue($parameters[2]);
        $this->assertSame(1, $parameters[1]->getParam());
    }

    /** @test */
    public function configuring_a_typehinted_parameter_takes_precedence_over_dependency_injection()
    {
        $config = [
            'required' => 1,
            'typehinted' => new DummyClass(2),
        ];

        $strategy = new ReflectionStrategy(TypeHintedHandler::class);
        $parameters = $strategy->getConstructorParameters($config);

        $this->assertSame(1, $parameters[0]);
        $this->assertInstanceOf(DummyClass::class, $parameters[1]);
        $this->assertTrue($parameters[2]);
        $this->assertSame(2, $parameters[1]->getParam());
    }

    /** @test */
    public function it_should_resolve_typehinted_interface_from_yiis_di_container_if_possible()
    {
        Yii::$container->set(DummyInterface::class, function () {
            return new DummyClass(3);
        });

        $strategy = new ReflectionStrategy(TypeHintedInterfaceHandler::class);
        $parameters = $strategy->getConstructorParameters([]);

        $this->assertInstanceOf(DummyClass::class, $parameters[0]);
        $this->assertSame(3, $parameters[0]->getParam());
    }

    /** @test */
    public function it_should_return_empty_array_for_classes_that_dont_have_constructor_parameters()
    {
        $config = [];
        $strategy = new ReflectionStrategy(NoConstructor::class);

        $this->assertSame([], $strategy->getRequiredParameters());
        $this->assertSame([], $strategy->getConstructorParameters($config));
    }

    /** @test */
    public function it_should_use_the_given_configure_callable_from_config()
    {
        $testArgument = new \StdClass;
        $callbackAssessor = m::mock(\StdClass::class);
        $callbackAssessor->shouldReceive('doSomething')->once()->with($testArgument);

        $config = [
            'configure' => function ($instance) use ($testArgument, $callbackAssessor) {
                $this->assertSame($testArgument, $instance);
                $callbackAssessor->doSomething($testArgument);
            },
        ];

        $strategy = new ReflectionStrategy(NoConstructor::class);
        $configure = $strategy->getConfigurationCallable($config);
        $configure($testArgument);
    }

    /** @test */
    public function if_no_configure_key_given_the_default_configuration_callable_should_just_return_the_given_instance()
    {
        $testArgument = new \StdClass;
        $strategy = new ReflectionStrategy(NoConstructor::class);
        $configure = $strategy->getConfigurationCallable([]);

        $this->assertSame($testArgument, $configure($testArgument));
    }

    /**
     * @test
     *
     *
     */
    public function it_should_throw_an_exception_if_the_required_parameters_are_not_given_in_the_config_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Expected to find key: 'required1' in the given config array but none found.");
        $config = [];

        $strategy = new ReflectionStrategy(OneRequiredParameter::class);
        $strategy->getConstructorParameters($config);
    }
}

class TwoRequiredParameters
{
    public function __construct($required1, $required2, $optional = '1')
    {
    }
}

class OneRequiredParameter
{
    public function __construct($required1, $optional1 = null, $optional2 = true, $optional3 = 1)
    {
    }
}

class NoOptionalParameters
{
    public function __construct($required1, $required2)
    {
    }
}

class TypeHintedHandler
{
    public function __construct($required, DummyClass $typehinted, $optional = true)
    {
    }
}

class TypeHintedInterfaceHandler
{
    public function __construct(DummyInterface $typehinted)
    {
    }
}

class DummyClass implements DummyInterface
{
    /**
     * @var int
     */
    private $param;

    public function __construct(int $param = 1)
    {
        $this->param = $param;
    }

    /**
     * @return int
     */
    public function getParam(): int
    {
        return $this->param;
    }
}

class NoConstructor
{
}

interface DummyInterface
{
}
