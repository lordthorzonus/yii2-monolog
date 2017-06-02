<?php


namespace leinonen\Yii2Monolog\Tests\Integration;


use leinonen\Yii2Monolog\Tests\Helpers\ExampleYii2MonologConfiguration;
use leinonen\Yii2Monolog\Yii2Monolog;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Yii;

class Yii2MonologTest extends TestCase
{
    /**
     * @var TestHandler
     */
    private $testHandler;

    /**
     * @var string
     */
    private $channelName;

    public function setUp()
    {
        // Configure a test handler which can be accessed in tests.
        // It is used in the example configuration and the component should resolve it through DI.
        $this->testHandler = new TestHandler();
        \Yii::$container->set(TestHandler::class, function ()  {
            return $this->testHandler;
        });

        $this->channelName = 'myChannel';
        $this->mockApplication([
            'bootstrap' => ['monolog'],
            'components' => [
                'monolog' => [
                    'class' => Yii2Monolog::class,
                    'channels' => [
                        $this->channelName => ExampleYii2MonologConfiguration::getConfiguration(),
                    ],
                ],
            ],
        ]);

        parent::setUp();
    }

    /** @test */
    public function it_configures_monolog_loggers_to_be_fetched_from_service_locator()
    {
        /** @var Yii2Monolog $component */
        $component = \Yii::$app->monolog;
        $this->assertInstanceOf(Yii2Monolog::class, $component);

        $logger = $component->getLogger($this->channelName);
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($this->channelName, $logger->getName());
        $this->assertSame([$this->testHandler], $logger->getHandlers());
    }

    /** @test */
    public function loggers_are_registered_with_an_alias_to_the_di_container()
    {
        $logger = Yii::$container->get("yii2-monolog.{$this->channelName}");

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($this->channelName, $logger->getName());
        $this->assertSame([$this->testHandler], $logger->getHandlers());

        $this->destroyApplication();

        $otherChannel = 'otherChannel';
        $this->mockApplication([
            'bootstrap' => ['monolog'],
            'components' => [
                'monolog' => [
                    'class' => Yii2Monolog::class,
                    'channels' => [
                        $otherChannel => ExampleYii2MonologConfiguration::getConfiguration(),
                    ],
                ],
            ],
        ]);

        $logger = Yii::$container->get("yii2-monolog.{$otherChannel}");
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($otherChannel, $logger->getName());
    }

    /** @test */
    public function it_can_register_multiple_channels()
    {
        $this->destroyApplication();

        $firstChannel = 'firstChannel';
        $secondChannel = 'secondChannel';
        $this->mockApplication([
            'bootstrap' => ['monolog'],
            'components' => [
                'monolog' => [
                    'class' => Yii2Monolog::class,
                    'channels' => [
                        $firstChannel => [],
                        $secondChannel => [],
                    ],
                ],
            ],
        ]);

        /** @var Yii2Monolog $component */
        $component = \Yii::$app->monolog;

        $logger1 = $component->getLogger($firstChannel);
        $this->assertInstanceOf(Logger::class, $logger1);
        $this->assertSame($firstChannel, $logger1->getName());

        $logger2 = $component->getLogger($secondChannel);
        $this->assertInstanceOf(Logger::class, $logger2);
        $this->assertSame($secondChannel, $logger2->getName());
    }
    
    /** @test */
    public function it_can_register_a_main_channel_to_be_used_for_psr_logger_interface()
    {
        $this->destroyApplication();

        $firstChannel = 'firstChannel';
        $secondChannel = 'secondChannel';
        $this->mockApplication([
            'bootstrap' => ['monolog'],
            'components' => [
                'monolog' => [
                    'class' => Yii2Monolog::class,
                    'channels' => [
                        $firstChannel => [],
                        $secondChannel => [],
                    ],
                    'mainChannel' => $secondChannel
                ],
            ],
        ]);

        $logger = Yii::$container->get(LoggerInterface::class);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($secondChannel, $logger->getName());
    }
    
    /** @test */
    public function it_registers_the_first_channel_implicitly_to_be_used_for_the_psr_logger_interface_if_no_main_channel_is_defined()
    {
        $logger = Yii::$container->get(LoggerInterface::class);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($this->channelName, $logger->getName());
    }

    /** @test */
    public function it_returns_the_main_logger_if_no_parameters_given_to_get_logger_method()
    {
        $this->destroyApplication();

        $firstChannel = 'firstChannel';
        $secondChannel = 'secondChannel';
        $this->mockApplication([
            'bootstrap' => ['monolog'],
            'components' => [
                'monolog' => [
                    'class' => Yii2Monolog::class,
                    'channels' => [
                        $firstChannel => [],
                        $secondChannel => [],
                    ],
                    'mainChannel' => $secondChannel
                ],
            ],
        ]);

        $logger = Yii::$app->monolog->getLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($secondChannel, $logger->getName());
    }
    
    /** @test */
    public function it_configures_the_registered_loggers_correctly()
    {
        /** @var Logger $logger */
        $logger = Yii::$container->get(LoggerInterface::class);

        $logger->warning('my message');
        $logger->error('second message');

        $testMessage1 = $this->testHandler->getRecords()[0];
        $this->assertSame('my message', $testMessage1['message']);
        $this->assertSame($this->channelName, $testMessage1['channel']);
        $this->assertSame('special', $testMessage1['context']['specialValue']);
        $this->assertSame('changed value', $testMessage1['context']['configuredValue']);
        $this->assertContains('myPrefix', $testMessage1['formatted']);
        $this->assertContains("{$this->channelName}.WARNING: my message", $testMessage1['formatted']);
        $this->assertContains('{"test":"testvalue"}', $testMessage1['formatted']);

        $testMessage2 = $this->testHandler->getRecords()[1];
        $this->assertSame('second message', $testMessage2['message']);
        $this->assertSame($this->channelName, $testMessage2['channel']);
        $this->assertSame('special', $testMessage2['context']['specialValue']);
        $this->assertSame('changed value', $testMessage2['context']['configuredValue']);
        $this->assertContains('myPrefix', $testMessage2['formatted']);
        $this->assertContains("{$this->channelName}.ERROR: second message", $testMessage2['formatted']);
        $this->assertContains('{"test":"testvalue"}', $testMessage2['formatted']);
    }
}
