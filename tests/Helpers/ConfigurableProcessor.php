<?php

namespace leinonen\Yii2Monolog\Tests\Helpers;

class ConfigurableProcessor
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke(array $record)
    {
        $record['context']['configuredValue'] = $this->value;

        return $record;
    }
}
