<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Database;

use Exception;

class DatabaseConnectionException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
