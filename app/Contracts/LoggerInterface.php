<?php

namespace App\Contracts;

interface LoggerInterface
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function debug(string $message, array $context = []): void;
    
    public function order(string $message, array $context = []): void;
    public function payment(string $message, array $context = []): void;
    public function user(string $message, array $context = []): void;
}