<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;

class SsoControllerTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * 测试用户登录接口
     */
    public function test_user_login(): void
    {
        // 创建测试用户
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // 发送登录请求
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        // 断言响应
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user'
            ])
            ->assertJson([
                'token_type' => 'Bearer',
                'user' => [
                    'email' => 'test@example.com'
                ]
            ]);
    }
    
    /**
     * 测试无效凭据登录
     */
    public function test_login_with_invalid_credentials(): void
    {
        // 发送登录请求
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);
        
        // 断言响应
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials'
            ]);
    }
}
