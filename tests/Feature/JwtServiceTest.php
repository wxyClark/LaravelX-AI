<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\JwtService;
use App\Models\User;

class JwtServiceTest extends TestCase
{
    /**
     * 测试 JWT Token 生成
     */
    public function test_jwt_token_generation(): void
    {
        // 创建 JwtService 实例
        $jwtService = new JwtService();
        
        // 创建测试用户
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        // 生成 Token
        $token = $jwtService->generateToken($user);
        
        // 断言 Token 不为空
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }
    
    /**
     * 测试 JWT Token 验证
     */
    public function test_jwt_token_validation(): void
    {
        // 创建 JwtService 实例
        $jwtService = new JwtService();
        
        // 创建测试用户
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        // 生成 Token
        $token = $jwtService->generateToken($user);
        
        // 验证 Token
        $payload = $jwtService->validateToken($token);
        
        // 断言验证结果
        $this->assertNotFalse($payload);
        $this->assertEquals(1, $payload['sub']);
        $this->assertEquals('test@example.com', $payload['user']['email']);
    }
    
    /**
     * 测试无效 JWT Token 验证
     */
    public function test_invalid_jwt_token_validation(): void
    {
        // 创建 JwtService 实例
        $jwtService = new JwtService();
        
        // 验证无效 Token
        $payload = $jwtService->validateToken('invalid.token.string');
        
        // 断言验证结果为 false
        $this->assertFalse($payload);
    }
}
