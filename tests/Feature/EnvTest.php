<?php
// tests/Feature/DotEnvTest.php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class EnvTest extends TestCase
{
    /**
     * @test
     * Test bahwa .env file punya semua required variables
     */
    public function testEnvHasAllRequiredVariables()
    {   
        $requiredVars = [
            'DB_TYPE',
            'DB_NAME',
            'DB_HOST',
            'DB_USER',
            'DB_CHARSET'
        ];
        
        foreach ($requiredVars as $var) {
            $value = getenv($var);
            $this->assertNotEmpty(
                $value,
                ".env file harus memiliki variable: {$var}"
            );
        }
    }
}