<?php

namespace Gravitycar\exceptions;

use Exception;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Throwable;

class GCException extends Exception implements Throwable
{
    private Logger $logger;

    private string $convertedFrom;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        bool $enableLogging = true,
        string $convertedFrom = ''
    ) {
        parent::__construct($message, $code, $previous);
        $this->convertedFrom = $convertedFrom;
        $this->logger = new Logger('exceptions');
        $this->logger->pushHandler(new StreamHandler('exceptions.log', Level::Error));

        if ($enableLogging) {
            $this->logException();
        }
    }

    private function logException(): void
    {
        try {
            if (!empty($this->convertedFrom)) {
                $trace = $this->getPrevious()->getTraceAsString();
            } else {
                $trace = $this->getTraceAsString();
            }

            $this->logger->error('Exception occurred', [
                'class' => get_class($this),
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
                'trace' => $trace
            ]);

        } catch (Exception $e) {
            $this->logError($e);
        }
    }

    private function logError(Exception $e): void
    {
        error_log(sprintf(
            "Failed to log exception: %s in %s:%d\nOriginal exception: %s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $this->getMessage()
        ));
    }

    public static function convert(Exception $e, string $exceptionClassName = ''): GCException
    {
        if (empty($exceptionClassName)) {
            $exceptionClassName = 'GCException';
        } else {
            if (!class_exists($exceptionClassName) || !is_subclass_of($exceptionClassName, GCException::class)) {
                $exceptionClassName = 'GCException';
            }
        }

        if ($exceptionClassName === 'GCException') {
            $exceptionClassName = self::class;
        }

        return new $exceptionClassName(
            $e->getMessage(),
            $e->getCode(),
            $e, // pass the original exception as the previous one - this way we can keep the stack trace
            true, // enable logging by default
            get_class($e) // store the class of the original exception
        );
    }
}