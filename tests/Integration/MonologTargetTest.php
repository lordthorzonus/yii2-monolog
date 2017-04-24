<?php


namespace leinonen\Yii2Monolog\Tests\Integration;


use leinonen\Yii2Monolog\MonologTarget;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use yii\log\Dispatcher;
use yii\log\Logger;

class MonologTargetTest extends TestCase
{
    /** @test */
    public function it_should_be_a_functioning_yii_log_target()
    {
        $logger = new Logger();

        // We want to access the Test Handler to assert everything works
        // So let's configure it into DI as it's resolved from there
        $handler = new TestHandler();
        \Yii::$container->set(TestHandler::class, function () use ($handler) {
            return $handler;
        });

        $dispatcher = new Dispatcher([
            'logger' => $logger,
            'targets' => [
                'monolog' => [
                    'class' => MonologTarget::class,
                    'channel' => 'someChannel',
                    'handlers' => [
                        TestHandler::class => [
                            'formatter' => [
                                LineFormatter::class => [
                                    'format' => "myPrefix %channel%.%level_name%: %message% %context% %extra%\n"
                                ]
                            ],
                            'processors' => [
                                function ($record) {
                                    $record['context']['specialValue'] = 'special';

                                    return $record;
                                }
                            ]
                        ]
                    ],
                    'processors' => [
                        function ($record) {
                            $record['extra']['test'] = 'testvalue';

                            return $record;
                        }
                    ]
                ]
            ]
        ]);

        $logger->log('my message', Logger::LEVEL_WARNING);
        $logger->log('second message', Logger::LEVEL_ERROR, 'custom category');
        $logger->flush(true);

        $testMessage1 = $handler->getRecords()[0];

        $this->assertSame('my message', $testMessage1['message']);
        $this->assertSame('someChannel', $testMessage1['channel']);
        $this->assertSame('application', $testMessage1['context']['category']);
        $this->assertSame('special', $testMessage1['context']['specialValue']);
        $this->assertSame(['test' => 'testvalue'], $testMessage1['extra']);
        $this->assertTrue(is_int($testMessage1['context']['memory']));
        $this->assertTrue(is_array($testMessage1['context']['trace']));
        $this->assertSame(\Monolog\Logger::WARNING, $testMessage1['level']);
        $this->assertContains('myPrefix', $testMessage1['formatted']);
        $this->assertContains('someChannel.WARNING: my message', $testMessage1['formatted']);
        $this->assertContains('{"test":"testvalue"}', $testMessage1['formatted']);

        $testMessage2 = $handler->getRecords()[1];

        $this->assertSame('second message', $testMessage2['message']);
        $this->assertSame('someChannel', $testMessage2['channel']);
        $this->assertSame('custom category', $testMessage2['context']['category']);
        $this->assertSame('special', $testMessage2['context']['specialValue']);
        $this->assertSame(['test' => 'testvalue'], $testMessage2['extra']);
        $this->assertTrue(is_int($testMessage2['context']['memory']));
        $this->assertTrue(is_array($testMessage2['context']['trace']));
        $this->assertSame(\Monolog\Logger::ERROR, $testMessage2['level']);
        $this->assertContains('myPrefix', $testMessage2['formatted']);
        $this->assertContains('someChannel.ERROR: second message', $testMessage2['formatted']);
        $this->assertContains('{"test":"testvalue"}', $testMessage2['formatted']);
    }
}
