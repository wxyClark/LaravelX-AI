# Feature/Auth 分支测试代码开发说明

本文档详细说明了 `feature/auth` 分支的测试代码开发过程，包括各个模块的单元测试实现，遵循小粒度提交原则，方便后续开发者理解和维护。

## 测试策略

采用分层测试策略，为每个核心组件编写独立的单元测试：
1. JWT Service 测试
2. SSO Controller 测试
3. Passport OAuth2 测试
4. Sanctum JWT 测试
5. 认证路由测试

所有测试均位于 `tests/Feature` 目录下，继承 `Tests\TestCase` 并使用 `RefreshDatabase` trait 确保测试环境隔离。

## 核心测试组件

### 1. JWT Service 测试 (JwtServiceTest)

文件路径: `tests/Feature/JwtServiceTest.php`

测试 JWT 服务类的核心功能：

```php
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
```

### 2. SSO Controller 测试 (SsoControllerTest)

文件路径: `tests/Feature/SsoControllerTest.php`

测试 SSO 控制器的登录接口：

```php
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
```

### 3. Passport OAuth2 测试 (PassportOAuthTest)

文件路径: `tests/Feature/PassportOAuthTest.php`

测试 Passport OAuth2 功能：

```php
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
```

### 4. Sanctum JWT 测试 (SanctumJwtTest)

文件路径: `tests/Feature/SanctumJwtTest.php`

测试 Sanctum JWT 认证功能：

```php
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
```

### 5. 认证路由测试 (AuthRouteTest)

文件路径: `tests/Feature/AuthRouteTest.php`

测试认证路由的存在性和基本功能：

```php
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
```

## 测试执行

可以通过以下命令执行所有认证相关的测试：

```bash
php artisan test tests/Feature/JwtServiceTest.php
php artisan test tests/Feature/SsoControllerTest.php
php artisan test tests/Feature/PassportOAuthTest.php
php artisan test tests/Feature/SanctumJwtTest.php
php artisan test tests/Feature/AuthRouteTest.php
```

或者执行所有功能测试：

```bash
php artisan test tests/Feature
```

## 提交历史

测试代码开发按照测试模块进行了原子化提交：

1. 创建 JwtServiceTest 单元测试文件
2. 为 JwtService 添加单元测试
3. 创建 SsoControllerTest 单元测试文件
4. 为 SsoController 添加登录接口单元测试
5. 创建 PassportOAuthTest 单元测试文件
6. 为 Passport OAuth2 添加单元测试
7. 创建 SanctumJwtTest 单元测试文件
8. 为 Sanctum JWT 添加单元测试
9. 创建 AuthRouteTest 单元测试文件
10. 为认证路由添加单元测试

这种小粒度的提交方式有助于后续开发者理解每个测试模块的目的和实现方式，也便于定位和修复问题。