<?php
// app/Helpers/Logger.php
namespace App\Helpers;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private static $loggers = [];
    
    /**
     * Get logger instance for context
     */
    private static function getLogger(string $context = 'app'): MonologLogger
    {
        if (!isset(self::$loggers[$context])) {
            $logger = new MonologLogger($context);
            
            // Create logs directory
            $logDir = __DIR__ . '/../../logs/';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            // Daily rotating files (30 days retention)
            $handler = new RotatingFileHandler(
                $logDir . $context . '.log',
                30, // Keep 30 days
                MonologLogger::DEBUG
            );
            
            // Custom format
            $formatter = new LineFormatter(
                "[%datetime%] [%level_name%] %message% %context% %extra%\n",
                "Y-m-d H:i:s",
                true,
                true
            );
            
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            
            self::$loggers[$context] = $logger;
        }
        
        return self::$loggers[$context];
    }
    
    /**
     * Write log with Monolog
     */
    public static function log(string $message, string $level = 'info', array $context = [], string $loggerName = 'app'): void
    {
        $logger = self::getLogger($loggerName);
        
        // Map string level to Monolog constant
        $levelConstant = constant('Monolog\Logger::' . strtoupper($level));
        
        $logger->log($levelConstant, $message, $context);
    }
    
    // Convenience methods
    public static function info(string $message, array $context = [], string $loggerName = 'app'): void
    {
        self::log($message, 'info', $context, $loggerName);
    }
    
    public static function error(string $message, array $context = [], string $loggerName = 'app'): void
    {
        self::log($message, 'error', $context, $loggerName);
    }
    
    public static function warning(string $message, array $context = [], string $loggerName = 'app'): void
    {
        self::log($message, 'warning', $context, $loggerName);
    }
    
    public static function debug(string $message, array $context = [], string $loggerName = 'app'): void
    {
        if (getenv('APP_ENV') === 'development') {
            self::log($message, 'debug', $context, $loggerName);
        }
    }
    
    // Domain-specific methods
    public static function order(string $message, ?int $orderId = null, ?int $userId = null): void
    {
        $context = [];
        if ($orderId) $context['order_id'] = $orderId;
        if ($userId) $context['user_id'] = $userId;
        
        self::info($message, $context, 'order');
    }
    
    public static function payment(string $message, ?int $paymentId = null, ?int $orderId = null): void
    {
        $context = [];
        if ($paymentId) $context['payment_id'] = $paymentId;
        if ($orderId) $context['order_id'] = $orderId;
        
        self::info($message, $context, 'payment');
    }
}