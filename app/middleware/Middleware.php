<?php

namespace Jeffrey\Educore\Middleware;

abstract class Middleware
{
    /**
     * Handles the request and returns a boolean indicating whether to proceed.
     *
     * @return bool True to proceed, false to stop the request.
     */
    abstract public function handle(): bool;
}