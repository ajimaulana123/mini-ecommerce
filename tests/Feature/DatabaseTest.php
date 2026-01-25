<?php
// tests/Feature/DatabaseConfigTest.php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Medoo\Medoo;

class DatabaseConfigTest extends TestCase
{
    /**
     * @test
     * Test bahwa config database dari .env.testing valid
     */
    public function testDatabaseConfigFromEnvIsValid()
    {
        $config = [
            'type' => getenv('DB_TYPE'),
            'name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => getenv('DB_CHARSET')
        ];
        
        // Validasi config
        $this->assertEquals('mysql', $config['type']);
        $this->assertNotEmpty($config['name']);
        $this->assertNotEmpty($config['server']);
        $this->assertNotEmpty($config['username']);
        // password bisa empty
        $this->assertNotEmpty($config['charset']);
        
        // Database name harus untuk testing
        $this->assertStringContainsString('ecommerce', strtolower($config['name']));
    }
    
    /**
     * @test
     * Test REAL database connection dengan config dari .env.testing
     */
    public function testRealDatabaseConnectionWithEnvConfig()
    {   
        try {
            // Ini menggunakan config REAL dari .env
            $db = new Medoo([
                'database_type' => getenv('DB_TYPE'),
                'database_name' => getenv('DB_NAME'),
                'server' => getenv('DB_HOST'),
                'username' => getenv('DB_USER'),
                'password' => getenv('DB_PASS'),
                'charset' => getenv('DB_CHARSET'),
                'port' => 3306,
                'option' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 5
                ]
            ]);
            
            // Test REAL query
            $result = $db->query("SELECT DATABASE() as db_name")->fetch();
            
            $this->assertEquals(getenv('DB_NAME'), $result['db_name']);
            
        } catch (\PDOException $e) {
            // Database tidak ada? Buat otomatis
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $this->fail(
                    "Database '" . getenv('DB_NAME') . "' tidak ada. " .
                    "Buat dengan: CREATE DATABASE " . getenv('DB_NAME') . ";"
                );
            }
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }
}
