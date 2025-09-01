<?php

namespace Jeffrey\Educore\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class AppLogger
{
    private static ?AppLogger $instance = null;
    private Logger $logger;

    private function __construct()
    {
        $this->logger = new Logger('educore');

        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $logPath = $logDir . '/app.log';
        $this->logger->pushHandler(new StreamHandler($logPath, Level::Debug));
    }

    public static function getInstance(): AppLogger
    {
        if (self::$instance === null) {
            self::$instance = new AppLogger();
        }
        return self::$instance;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}