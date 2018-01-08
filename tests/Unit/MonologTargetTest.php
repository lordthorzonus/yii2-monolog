<?php

namespace leinonen\Yii2Monolog\Tests\Unit;

use Mockery as m;
use Monolog\Logger;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use leinonen\Yii2Monolog\MonologTarget;
use leinonen\Yii2Monolog\LoggerRegistry;

class MonologTargetTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function it_exports_messages_correctly_for_the_monolog_logger()
    {
        $mockMonolog = m::mock(Logger::class);
        $mockMonolog->shouldReceive('log')
            ->once()
            ->withArgs([LogLevel::ERROR, 'message1', ['category' => 'application', 'memory' => 123]]);

        $mockMonolog->shouldReceive('log')
            ->once()
            ->withArgs([LogLevel::INFO, 'message2', ['category' => 'application', 'memory' => 123]]);

        $target = $this->getMonologTarget($mockMonolog);

        $target->messages = [
            [
                'message1',
                \yii\log\Logger::LEVEL_ERROR,
                'application',
                10,
                null,
                123,
            ],
            [
                'message2',
                \yii\log\Logger::LEVEL_INFO,
                'application',
                10,
                null,
                123,
            ],
        ];

        $this->assertNull($target->export());
    }

    /**
     * Returns a new MonlogTarget instantiated with the given Monolog logger.
     *
     * @param Logger
     *
     * @return MonologTarget
     */
    private function getMonologTarget($monologLogger): MonologTarget
    {
        $mockLoggerRegistry = m::mock(LoggerRegistry::class);
        $mockLoggerRegistry->shouldReceive('getLogger')->with('test')->andReturn($monologLogger);

        $target = new MonologTarget(
            $mockLoggerRegistry,
            [
                'channel' => 'test',
            ]
        );

        return $target;
    }
}
