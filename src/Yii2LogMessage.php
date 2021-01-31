<?php

declare(strict_types=1);

namespace bessonov87\Yii2Monolog;

use yii\log\Logger;
use Psr\Log\LogLevel;
use yii\helpers\VarDumper;

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
     * @var \Throwable|null
     */
    private $exception;

    /**
     * Initializes a new Yii2LogMessage.
     *
     * @param array $message
     */
    public function __construct(array $message)
    {
        $this->setMessage($message[0]);

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
     * @return null|\Throwable
     */
    public function getException()
    {
        return $this->exception;
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

        if ($this->exception !== null) {
            $context['exception'] = $this->exception;
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

    /**
     * @param array|\Throwable|string $message
     */
    private function setMessage($message)
    {
        $this->message = ! \is_string($message)
            ? $this->convertYiisMessageToString($message)
            : $message;
    }

    /**
     * Converts Yii's message to string format.
     *
     * @param array|\Throwable $message
     *
     * @return string
     */
    private function convertYiisMessageToString($message): string
    {
        if ($message instanceof \Throwable) {
            $this->exception = $message;

            return \get_class($message) . ': ' . $message->getMessage();
        }

        return VarDumper::export($message);
    }
}
