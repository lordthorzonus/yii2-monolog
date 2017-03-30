<?php


namespace leinonen\Yii2Monolog\Tests\Unit\Factories;


use leinonen\Yii2Monolog\Factories\FormatterFactory;
use leinonen\Yii2Monolog\Factories\GenericStrategyBasedFactory;
use leinonen\Yii2Monolog\Yii2LogMessage;
use Monolog\Formatter\LineFormatter;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class FormatterFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_make_a_formatter()
    {
        $config = [
            'key' => 'value'
        ];

        $mockLineFormatter = m::mock(LineFormatter::class);

        $mockGenericFactory = m::mock(GenericStrategyBasedFactory::class);
        $mockGenericFactory->shouldReceive('makeWithStrategy')->once()
            ->withArgs([LineFormatter::class, $config])
            ->andReturn($mockLineFormatter);

        $factory = new FormatterFactory($mockGenericFactory);

        $formatter = $factory->make(LineFormatter::class, $config);
        $this->assertSame($mockLineFormatter, $formatter);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage leinonen\Yii2Monolog\Yii2LogMessage doesn't implement Monolog\Formatter\FormatterInterface
     */
    public function it_should_throw_an_exception_if_the_given_class_name_doesnt_implement_formatter_interface()
    {
        $mockGenericFactory = m::mock(GenericStrategyBasedFactory::class);
        $factory = new FormatterFactory($mockGenericFactory);
        $factory->make(Yii2LogMessage::class);
    }
}
