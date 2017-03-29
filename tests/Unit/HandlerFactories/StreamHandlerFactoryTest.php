<?php

namespace leinonen\Yii2MonologTargets\Tests\Unit\HandlerFactories;

use leinonen\Yii2MonologTargets\HandlerFactories\StreamHandlerFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class StreamHandlerFactoryTest extends TestCase
{
    /** @test */
    public function it_can_make_a_StreamHandler()
    {
        $factory = new StreamHandlerFactory();

        $handler = $factory->make(
            [
                'path' => 'app.log',
                'level' => Logger::WARNING,
                'bubble' => false,
                'filePermissions' => 'something',
                'useLocking' => true
            ]
        );

        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertSame('app.log', $handler->getUrl());
        $this->assertSame(Logger::WARNING, $handler->getLevel());
        $this->assertFalse($handler->getBubble());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You need to define a stream for \Monolog\Handler\StreamHandler
     */
    public function it_should_throw_an_exception_if_stream_is_not_configured()
    {
        $factory = new StreamHandlerFactory();

        $factory->make(
            [
                'level' => Logger::WARNING,
                'bubble' => false,
                'filePermissions' => 'something',
                'useLocking' => true
            ]
        );
    }

    /** @test */
    public function it_defaults_to_StreamHandler_default_constructor_values_if_none_given_in_config()
    {
        $factory = new StreamHandlerFactory();

        $handler = $factory->make(
            [
                'path' => 'app.log',
            ]
        );

        $this->assertSame('app.log', $handler->getUrl());
        $this->assertSame(Logger::DEBUG, $handler->getLevel());
        $this->assertTrue($handler->getBubble());
    }
}
