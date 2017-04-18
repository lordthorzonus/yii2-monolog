<?php

namespace leinonen\Yii2Monolog\Tests\Unit\Factories;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use leinonen\Yii2Monolog\Factories\ProcessorFactory;
use leinonen\Yii2Monolog\Factories\GenericStrategyBasedFactory;

class ProcessorFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_make_a_processor()
    {
        $config = [
            'required' => true,
        ];

        $mockDummyProcessor = m::mock(DummyProcessor::class);
        $mockGenericFactory = m::mock(GenericStrategyBasedFactory::class);
        $mockGenericFactory->shouldReceive('makeWithStrategy')
            ->once()
            ->withArgs([DummyProcessor::class, $config])
            ->andReturn($mockDummyProcessor);

        $factory = new ProcessorFactory($mockGenericFactory);
        $processor = $factory->make(DummyProcessor::class, ['required' => true]);

        $this->assertSame($mockDummyProcessor, $processor);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage leinonen\Yii2Monolog\Tests\Unit\Factories\NonCallable isn't callable. All processor classes must implement the __invoke method.
     */
    public function created_processor_must_be_callable()
    {
        $mockGenericFactory = m::mock(GenericStrategyBasedFactory::class);
        $factory = new ProcessorFactory($mockGenericFactory);

        $factory->make(NonCallable::class);
    }
}

class DummyProcessor
{
    public function __invoke()
    {
    }
}

class NonCallable
{
}
