<?php
namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use App\Auth\Auth;
use App\Repositories\UserRepository;

class AuthTest extends TestCase
{
    private $auth;
    private $mockUserRepo;
    private $mockContainer;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock UserRepository
        $this->mockUserRepo = $this->createMock(UserRepository::class);
        
        // Mock Container
        $this->mockContainer = $this->createMock(\Slim\Container::class);
        $this->mockContainer->method('__get')
            ->with('userRepo')
            ->willReturn($this->mockUserRepo);
        
        // Create Auth instance
        $this->auth = new Auth($this->mockContainer);
        
        // Clear session sebelum setiap test
        $_SESSION = [];
    }
    
    protected function tearDown(): void
    {
        // Clear session setelah test
        $_SESSION = [];
        parent::tearDown();
    }
    
    /**
     * @test
     * Test check() mengembalikan false ketika tidak ada user di session
     */
    public function testCheckReturnsFalseWhenNoUserInSession()
    {
        $this->assertFalse($this->auth->check());
    }
    
    /**
     * @test
     * Test check() mengembalikan true ketika ada user di session
     */
    public function testCheckReturnsTrueWhenUserInSession()
    {
        $_SESSION['user'] = 123;
        $this->assertTrue($this->auth->check());
    }
    
    /**
     * @test
     * Test user() mengembalikan null ketika tidak login
     */
    public function testUserReturnsNullWhenNotLoggedIn()
    {
        $this->mockUserRepo->expects($this->never())
            ->method('where');
            
        $this->assertNull($this->auth->user());
    }
    
    /**
     * @test
     * Test user() mengembalikan user data ketika login
     */
    public function testUserReturnsUserDataWhenLoggedIn()
    {
        // Setup session
        $_SESSION['user'] = 123;
        
        // Setup mock repository response
        $expectedUser = [
            'id' => 123,
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ];
        
        $this->mockUserRepo->expects($this->once())
            ->method('where')
            ->with('id', 123)
            ->willReturn($expectedUser);
            
        $result = $this->auth->user();
        
        $this->assertEquals($expectedUser, $result);
    }
    
    /**
     * @test
     * Test attempt() sukses dengan credentials valid
     */
    public function testAttemptSuccessWithValidCredentials()
    {
        $email = 'test@example.com';
        $password = 'secret123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $mockUser = [
            'id' => 456,
            'email' => $email,
            'password' => $hashedPassword,
            'name' => 'Test User'
        ];
        
        // Setup mock: findByEmail mengembalikan user
        $this->mockUserRepo->expects($this->once())
            ->method('where')
            ->with('email', $email)
            ->willReturn($mockUser);
            
        $result = $this->auth->attempt($email, $password);
        
        $this->assertTrue($result);
        $this->assertEquals(456, $_SESSION['user']);
    }
    
    /**
     * @test
     * Test attempt() gagal ketika user tidak ditemukan
     */
    public function testAttemptFailsWhenUserNotFound()
    {
        $this->mockUserRepo->expects($this->once())
            ->method('where')
            ->with('email', 'nonexistent@example.com')
            ->willReturn(null);
            
        $result = $this->auth->attempt('nonexistent@example.com', 'password');
        
        $this->assertFalse($result);
        $this->assertArrayNotHasKey('user', $_SESSION);
    }
    
    /**
     * @test
     * Test attempt() gagal ketika password salah
     */
    public function testAttemptFailsWithWrongPassword()
    {
        $email = 'test@example.com';
        $hashedPassword = password_hash('correctpassword', PASSWORD_DEFAULT);
        
        $mockUser = [
            'id' => 789,
            'email' => $email,
            'password' => $hashedPassword
        ];
        
        $this->mockUserRepo->expects($this->once())
            ->method('where')
            ->with('email', $email)
            ->willReturn($mockUser);
            
        $result = $this->auth->attempt($email, 'wrongpassword');
        
        $this->assertFalse($result);
        $this->assertArrayNotHasKey('user', $_SESSION);
    }
    
    /**
     * @test
     * Test logout() menghapus user dari session
     */
    public function testLogoutRemovesUserFromSession()
    {
        // Setup logged in user
        $_SESSION['user'] = 999;
        $_SESSION['other_data'] = 'should remain';
        
        $this->auth->logout();
        
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertArrayHasKey('other_data', $_SESSION); // Data lain tetap ada
        $this->assertFalse($this->auth->check());
    }
    
    /**
     * @test
     * Test attempt() dengan password yang belum di-hash (edge case)
     */
    public function testAttemptWithUnhashedPassword()
    {
        $email = 'test@example.com';
        
        // User dengan password plain text (tidak seharusnya terjadi)
        $mockUser = [
            'id' => 111,
            'email' => $email,
            'password' => 'plaintextpassword' // Tidak di-hash!
        ];
        
        $this->mockUserRepo->expects($this->once())
            ->method('where')
            ->with('email', $email)
            ->willReturn($mockUser);
            
        $result = $this->auth->attempt($email, 'plaintextpassword');
        
        // password_verify dengan plain text akan return false
        $this->assertFalse($result);
    }
}