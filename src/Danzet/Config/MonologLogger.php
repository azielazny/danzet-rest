<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 13.02.2018
 * Time: 11:18
 */

namespace Danzet\Config;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class MonologLogger implements LoggerInterface
{
    private $activity_handler = 'activity.log';
    private $activity_handler_level = Logger::DEBUG;
    private $error_handler = 'error.log';
    private $error_handler_level = Logger::WARNING;

    function log($name)
    {
        $logger = new Logger($name);
        $activity_handler = new StreamHandler(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Logs'
            . DIRECTORY_SEPARATOR . date("Y-m-d", time()) . $this->activity_handler, $this->activity_handler_level);
        $error_handler = new StreamHandler(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Logs'
            . DIRECTORY_SEPARATOR . date("Y-m-d", time()) . $this->error_handler, $this->error_handler_level);
        $logger->pushHandler($activity_handler);
        $logger->pushHandler($error_handler);
        return $logger;
    }

    /**
     * @return string
     */
    public function getActivityHandler(): string
    {
        return $this->activity_handler;
    }

    /**
     * @param string $activity_handler
     */
    public function setActivityHandler(string $activity_handler)
    {
        $this->activity_handler = $activity_handler;
    }

    /**
     * @return int
     */
    public function getActivityHandlerLevel(): int
    {
        return $this->activity_handler_level;
    }

    /**
     * @param int $activity_handler_level
     */
    public function setActivityHandlerLevel(int $activity_handler_level)
    {
        $this->activity_handler_level = $activity_handler_level;
    }

    /**
     * @return string
     */
    public function getErrorHandler(): string
    {
        return $this->error_handler;
    }

    /**
     * @param string $error_handler
     */
    public function setErrorHandler(string $error_handler)
    {
        $this->error_handler = $error_handler;
    }

    /**
     * @return int
     */
    public function getErrorHandlerLevel(): int
    {
        return $this->error_handler_level;
    }

    /**
     * @param int $error_handler_level
     */
    public function setErrorHandlerLevel(int $error_handler_level)
    {
        $this->error_handler_level = $error_handler_level;
    }

}
