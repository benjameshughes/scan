<?php

namespace App\Exceptions;

use App\Models\Scan;
use Exception;

class NoSkuFoundException extends Exception
{
    protected Scan $scan;
    /**
     * Pass in the scan data
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, Scan $scan = null)
    {
        $this->scan = $scan;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the exceptions context information
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return ['scan_id' => $this->scan->id];
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        //
    }
}
