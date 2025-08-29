<?php

namespace Jeffrey\Educore\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class AppLogger
{
    private static $instance;
    private $logger;

    private function __construct()
    {
        // Create a logger instance with a unique name for our application
        $this->logger = new Logger('AppCore');
        
        // Use the Monolog\Level enum for the log level
        $logPath = __DIR__ . '/../../storage/logs/app.log';
        $this->logger->pushHandler(new StreamHandler($logPath, Level::Debug));
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}