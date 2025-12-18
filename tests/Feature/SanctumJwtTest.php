<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class SanctumJwtTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * 测试 Sanctum JWT 认证
     */
    public function test_sanctum_jwt_authentication(): void
    {
        // 创建测试用户
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // 使用 Sanctum 认证用户
        Sanctum::actingAs($user);
        
        // 访问受保护的路由
        $response = $this->getJson('/api/user');
        
        // 断言响应
        $response->assertStatus(200)
            ->assertJson([
                'email' => 'test@example.com'
            ]);
    }
    
    /**
     * 测试未认证用户访问受保护路由
     */
    public function test_unauthenticated_user_access(): void
    {
        // 访问受保护的路由
        $response = $this->getJson('/api/user');
        
        // 断言响应
        $response->assertStatus(401);
    }
}
