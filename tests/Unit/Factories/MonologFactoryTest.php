<?php

namespace leinonen\Yii2Monolog\Tests\Unit\Factories;

use Mockery as m;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\TagProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use leinonen\Yii2Monolog\Factories\HandlerFactory;
use leinonen\Yii2Monolog\Factories\MonologFactory;
use leinonen\Yii2Monolog\Factories\FormatterFactory;
use leinonen\Yii2Monolog\Factories\ProcessorFactory;

class MonologFactoryTest extends TestCase
{
    /**
     * @var m\Mock|HandlerFactory
     */
    private $mockHandlerFactory;

    /**
     * @var m\Mock|ProcessorFactory
     */
    private $mockProcessorFactory;

    /**
     * @var m\Mock|FormatterFactory
     */
    private $mockFormatterFactory;

    protected function setUp()
    {
        $this->mockHandlerFactory = m::mock(HandlerFactory::class);
        $this->mockProcessorFactory = m::mock(ProcessorFactory::class);
        $this->mockFormatterFactory = m::mock(FormatterFactory::class);

        parent::setUp();
    }

    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_make_a_monolog_logger()
    {
        $factory = new MonologFactory($this->mockHandlerFactory, $this->mockProcessorFactory, $this->mockFormatterFactory);

        $monologLogger = $factory->make('test');

        $this->assertInstanceOf(Logger::class, $monologLogger);
        $this->assertSame('test', $monologLogger->getName());
    }

    /** @test */
    public function it_assigns_the_handlers_array_correctly_to_the_logger_under_making()
    {
        $mockGitProcessor = m::mock(GitProcessor::class);
        $this->mockProcessorFactory->shouldReceive('make')
            ->once()
            ->withArgs([GitProcessor::class, []])
            ->andReturn($mockGitProcessor);

        $mockTagProcessor = $this->getMockTagProcessor();

        $mockLineFormatter = m::mock(LineFormatter::class);
        $this->mockFormatterFactory->shouldReceive('make')
            ->withArgs([LineFormatter::class, ['key' => 'value']])
            ->once()
            ->andReturn($mockLineFormatter);

        $mockStreamHandler = m::mock(StreamHandler::class);
        $this->mockHandlerFactory->shouldReceive('make')
            ->once()
            ->withArgs(
                [
                    StreamHandler::class,
                    ['path' => 'app.log'],
                    $mockLineFormatter,
                    [$mockGitProcessor, $mockTagProcessor],
                ]
            )
            ->andReturn($mockStreamHandler);

        $mockBrowserConsoleHandler = m::mock(BrowserConsoleHandler::class);
        $this->mockHandlerFactory->shouldReceive('make')
            ->once()
            ->withArgs([BrowserConsoleHandler::class, [], null, []])
            ->andReturn($mockBrowserConsoleHandler);

        $factory = new MonologFactory($this->mockHandlerFactory, $this->mockProcessorFactory, $this->mockFormatterFactory);

        $monologLogger = $factory->make(
            'test',
            [
                StreamHandler::class => [
                    'path' => 'app.log',
                    'formatter' => [
                        LineFormatter::class => [
                            'key' => 'value',
                        ],
                    ],
                    'processors' => [
                        GitProcessor::class,
                        TagProcessor::class => [
                            'tags' => [1, 2],
                        ],
                    ],
                ],
                BrowserConsoleHandler::class,
            ]
        );

        $this->assertSame([$mockStreamHandler, $mockBrowserConsoleHandler], $monologLogger->getHandlers());
    }

    /** @test */
    public function it_assigns_processors_array_correctly_to_the_logger_under_making()
    {
        $mockGitProcessor = m::mock(GitProcessor::class);
        $this->mockProcessorFactory->shouldReceive('make')
            ->once()
            ->withArgs([GitProcessor::class, []])
            ->andReturn($mockGitProcessor);

        $mockTagProcessor = $this->getMockTagProcessor();

        $dummyCallable = function ($record) {
            return $record;
        };

        $factory = new MonologFactory($this->mockHandlerFactory, $this->mockProcessorFactory, $this->mockFormatterFactory);

        $monologLogger = $factory->make(
            'test',
            [],
            [
                GitProcessor::class,
                TagProcessor::class => [
                    'tags' => [1, 2],
                ],
                $dummyCallable,
            ]
        );

        $this->assertSame([$mockGitProcessor, $mockTagProcessor, $dummyCallable], $monologLogger->getProcessors());
    }

    /**
     * @return TagProcessor|m\MockInterface
     */
    private function getMockTagProcessor(): m\MockInterface
    {
        $mockTagProcessor = m::mock(TagProcessor::class);

        $this->mockProcessorFactory->shouldReceive('make')
            ->once()
            ->withArgs([TagProcessor::class, ['tags' => [1, 2]]])
            ->andReturn($mockTagProcessor);

        return $mockTagProcessor;
    }
}
