<?php

namespace leinonen\Yii2Monolog\Tests\Unit;

use Mockery as m;
use Monolog\Logger;
use yii\di\Container;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use leinonen\Yii2Monolog\LoggerRegistry;

class LoggerRegistryTest extends TestCase
{
    /**
     * @var Container|m\Mock
     */
    private $mockedContainer;

    public function setUp()
    {
        $this->mockedContainer = m::mock(Container::class);
        \Yii::$container = $this->mockedContainer;
        parent::setUp();
    }

    public function tearDown()
    {
        m::close();
        \Yii::$container = new Container();
        parent::tearDown();
    }

    /** @test */
    public function it_can_register_a_logger_to_di_container_with_a_factory_callable()
    {
        $registry = new LoggerRegistry();
        $factoryCallable = function ($channelName) {
            return $channelName . '_tested';
        };
        $channelName = 'test';

        $this->mockedContainer->shouldReceive('setSingleton')
            ->once()
            ->withArgs([
               'yii2-monolog.test',
                m::on(function (callable $closure) use ($channelName, $factoryCallable) {
                    // The closure should receive the channel name as argument.
                    $this->assertSame($factoryCallable($channelName), $closure());
                    $this->assertEquals($closure, $factoryCallable);

                    return \is_callable($closure);
                }),
            ]);
        $registry->registerLogChannel($channelName, $factoryCallable);
    }

    /** @test */
    public function it_can_retrieve_a_logger_from_the_registry()
    {
        $registry = new LoggerRegistry();
        $logger = new Logger('myChannel');

        $this->mockedContainer->shouldReceive('get')->once()->with('yii2-monolog.myChannel')->andReturn($logger);

        $this->assertSame($logger, $registry->getLogger('myChannel'));
    }

    /** @test */
    public function it_can_register_the_psr_logger_to_the_di_container_with_callable()
    {
        $registry = new LoggerRegistry();
        $factoryCallable = function () {
            return 'tested';
        };

        $this->mockedContainer->shouldReceive('setSingleton')
            ->once()
            ->withArgs([
                LoggerInterface::class,
                m::on(function (callable $closure) use ($factoryCallable) {
                    $this->assertSame($factoryCallable(), $closure());
                    $this->assertEquals($closure, $factoryCallable);

                    return \is_callable($closure);
                }),
            ]);
        $registry->registerPsrLogger($factoryCallable);
    }
}
