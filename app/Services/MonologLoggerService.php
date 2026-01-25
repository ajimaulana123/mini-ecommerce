<?php

namespace App\Services;

use App\Contracts\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class MonologLoggerService implements LoggerInterface
{
    private $loggers = [];
    private $logDir;
    
    public function __construct(?string $logDir = null)
    {
        $this->logDir = $logDir ?: __DIR__ . '/../../logs/';
        
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    private function getLogger(string $channel): Logger
    {
        if (!isset($this->loggers[$channel])) {
            $logger = new Logger($channel);
            
            $handler = new RotatingFileHandler(
                $this->logDir . $channel . '.log',
                30, // Keep 30 days
                Logger::DEBUG
            );
            
            $formatter = new LineFormatter(
                "[%datetime%] [%level_name%] %message% %context% %extra%\n",
                "Y-m-d H:i:s",
                true,
                true
            );
            
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            
            $this->loggers[$channel] = $logger;
        }
        
        return $this->loggers[$channel];
    }
    
    private function log(string $level, string $message, array $context, string $channel): void
    {
        $logger = $this->getLogger($channel);
        $logger->log($level, $message, $context);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log(Logger::INFO, $message, $context, 'app');
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log(Logger::ERROR, $message, $context, 'error');
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log(Logger::WARNING, $message, $context, 'app');
    }
    
    public function debug(string $message, array $context = []): void
    {
        if (getenv('APP_ENV') === 'development') {
            $this->log(Logger::DEBUG, $message, $context, 'debug');
        }
    }
    
    public function order(string $message, array $context = []): void
    {
        $this->log(Logger::INFO, $message, $context, 'order');
    }
    
    public function payment(string $message, array $context = []): void
    {
        $this->log(Logger::INFO, $message, $context, 'payment');
    }
    
    public function user(string $message, array $context = []): void
    {
        $this->log(Logger::INFO, $message, $context, 'user');
    }
}