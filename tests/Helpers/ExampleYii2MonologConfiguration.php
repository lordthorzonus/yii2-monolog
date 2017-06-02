<?php


namespace leinonen\Yii2Monolog\Tests\Helpers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;

/**
 * An Example Configuration for Yii2Monolog Component which is used in integration tests.
 */
class ExampleYii2MonologConfiguration
{
    public static function getConfiguration(): array
    {
        return [
            'handlers' => [
                TestHandler::class => [
                    'formatter' => [
                        LineFormatter::class => [
                            'format' => "myPrefix %channel%.%level_name%: %message% %context% %extra%\n",
                        ],
                    ],
                    'processors' => [
                        function ($record) {
                            $record['context']['specialValue'] = 'special';

                            return $record;
                        },
                    ],
                ],
            ],
            'processors' => [
                function ($record) {
                    $record['extra']['test'] = 'testvalue';

                    return $record;
                },
                ConfigurableProcessor::class => [
                    'value' => 'changed value',
                ],
            ],
        ];
    }
}
