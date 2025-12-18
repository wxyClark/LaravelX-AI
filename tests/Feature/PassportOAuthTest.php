<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Hash;

class PassportOAuthTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * 测试创建 OAuth2 客户端
     */
    public function test_create_oauth_client(): void
    {
        // 创建测试用户
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // 作为用户创建 OAuth 客户端
        Passport::actingAs($user);
        
        $response = $this->postJson('/oauth/clients', [
            'name' => 'Test Client',
            'redirect' => 'http://localhost/auth/callback',
            'confidential' => true,
        ]);
        
        // 断言响应
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'secret',
                'redirect',
                'confidential',
                'created_at',
                'updated_at',
            ]);
    }
    
    /**
     * 测试获取 OAuth2 客户端列表
     */
    public function test_get_oauth_clients(): void
    {
        // 创建测试用户
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // 作为用户获取 OAuth 客户端
        Passport::actingAs($user);
        
        $response = $this->getJson('/oauth/clients');
        
        // 断言响应
        $response->assertStatus(200);
    }
}
