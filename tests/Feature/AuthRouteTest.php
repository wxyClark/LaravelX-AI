<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthRouteTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * 测试认证路由结构
     */
    public function test_auth_routes_structure(): void
    {
        // 测试登录路由存在
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        // 不关心登录结果，只关心路由是否存在
        $response->assertStatus(401); // 或 400，取决于验证规则
        
        // 测试验证路由存在
        $response = $this->getJson('/api/auth/verify');
        $response->assertStatus(401); // 未认证访问应返回 401
        
        // 测试刷新令牌路由存在
        $response = $this->postJson('/api/auth/refresh');
        $response->assertStatus(401); // 未认证访问应返回 401
        
        // 测试登出路由存在
        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(401); // 未认证访问应返回 401
    }
    
    /**
     * 测试受保护的用户路由
     */
    public function test_protected_user_route(): void
    {
        // 创建测试用户
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // 使用 Sanctum 认证用户
        Sanctum::actingAs($user);
        
        // 访问受保护的用户路由
        $response = $this->getJson('/api/user');
        
        // 断言响应
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ]);
    }
}
