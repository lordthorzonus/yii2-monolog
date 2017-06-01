<?php

declare(strict_types=1);

namespace leinonen\Yii2Monolog;

use yii\log\Logger;
use Psr\Log\LogLevel;

class Yii2LogMessage
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var array
     */
    private $trace;

    /**
     * @var int
     */
    private $memory;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var int|mixed
     */
    private $yiiLogLevel;

    /**
     * Initializes a new Yii2LogMessage.
     *
     * @param array $message
     */
    public function __construct(array $message)
    {
        $this->message = $message[0];
        $this->yiiLogLevel = $message[1];

        if (isset($message[2])) {
            $this->category = $message[2];
        }

        $this->timestamp = $message[3];

        if (isset($message[4])) {
            $this->trace = $message[4];
        }

        if (isset($message[5])) {
            $this->memory = $message[5];
        }
    }

    /**
     * Returns the message string.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the timestamp for the Yii2LogMessage.
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Returns the context for the Yii2LogMessage.
     *
     * @return array
     */
    public function getContext(): array
    {
        $context = [];

        if ($this->category !== null) {
            $context['category'] = $this->category;
        }

        if ($this->trace !== null) {
            $context['trace'] = $this->trace;
        }

        if ($this->memory !== null) {
            $context['memory'] = $this->memory;
        }

        return $context;
    }

    /**
     * Returns the PSR-3 compliant log level.
     *
     * @return string
     */
    public function getPsr3LogLevel(): string
    {
        $psrLevels = [
            Logger::LEVEL_ERROR => LogLevel::ERROR,
            Logger::LEVEL_WARNING => LogLevel::WARNING,
            Logger::LEVEL_INFO => LogLevel::INFO,
            Logger::LEVEL_TRACE => LogLevel::DEBUG,
            Logger::LEVEL_PROFILE => LogLevel::DEBUG,
            Logger::LEVEL_PROFILE_BEGIN => LogLevel::DEBUG,
            Logger::LEVEL_PROFILE_END => LogLevel::DEBUG,
        ];

        return $psrLevels[$this->yiiLogLevel];
    }
}
