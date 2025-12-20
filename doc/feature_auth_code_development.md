# Feature/Auth 分支代码开发说明

本文档详细说明了 `feature/auth` 分支的代码开发过程，包括认证系统的架构设计、核心组件实现以及相关配置。

## 项目背景

为 LaravelX-AI 项目实现统一认证鉴权方案，支持：
- OAuth2 对外授权（使用 Laravel Passport）
- 内部 SSO 验证（使用 JWT）
- Vue3 前端统一入口认证

## 技术选型

- **Laravel Passport**: OAuth2 服务器实现
- **Laravel Sanctum**: API 认证
- **Firebase JWT**: JWT Token 生成与验证
- **RESTful API**: 认证相关接口设计

## 核心组件实现

### 1. 依赖包引入

在 `composer.json` 中添加了以下认证相关的依赖包：

```json
"require": {
    "laravel/passport": "^12.0",
    "laravel/sanctum": "^4.0",
    "firebase/php-jwt": "^6.0"
}
```

通过命令安装依赖：
```bash
composer require laravel/passport laravel/sanctum firebase/php-jwt
```

### 2. JWT 服务类 (JwtService)

文件路径: `app/Services/JwtService.php`

提供了 JWT Token 的生成、验证和刷新功能：

```php
class JwtService
{
    protected $key;
    
    public function __construct()
    {
        $this->key = env('JWT_SECRET', 'your-secret-key');
    }
    
    // 生成 JWT Token
    public function generateToken($user) { ... }
    
    // 验证 JWT Token
    public function validateToken($token) { ... }
    
    // 刷新 JWT Token
    public function refreshToken($token) { ... }
}
```

### 3. SSO 控制器 (SsoController)

文件路径: `app/Http/Controllers/Auth/SsoController.php`

实现了统一登录、Token 验证、Token 刷新和登出接口：

```php
class SsoController extends Controller
{
    protected $jwtService;
    
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
        // JWT 认证不需要 CSRF 保护
        $this->middleware('csrf', ['except' => ['login', 'verifyToken', 'refreshToken', 'logout']]);
    }
    
    // 统一登录接口
    public function login(Request $request) { ... }
    
    // 验证 Token
    public function verifyToken(Request $request) { ... }
    
    // 刷新 Token
    public function refreshToken(Request $request) { ... }
    
    // 登出
    public function logout(Request $request) { ... }
}
```

### 4. User 模型扩展

文件路径: `app/Models/User.php`

为 User 模型添加了 Passport API Tokens 支持：

```php
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    // ...
}
```

### 5. 认证配置

#### Auth 配置文件

文件路径: `config/auth.php`

配置了 Passport 认证守卫：

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

#### Passport 配置

文件路径: `app/Providers/AppServiceProvider.php`

配置了 Passport 运行时设置：

```php
public function boot(): void
{
    // 设置 Passport 运行时配置
    Passport::tokensExpireIn(now()->addDays(15));
    Passport::refreshTokensExpireIn(now()->addDays(30));
    Passport::personalAccessTokensExpireIn(now()->addMonths(6));
}
```

### 6. API 路由配置

文件路径: `routes/api.php`

配置了 SSO 认证相关路由：

```php
// SSO 认证相关路由
Route::prefix('auth')->group(function () {
    Route::post('/login', [SsoController::class, 'login']);
    Route::get('/verify', [SsoController::class, 'verifyToken'])->middleware('auth:sanctum');
    Route::post('/refresh', [SsoController::class, 'refreshToken']);
    Route::post('/logout', [SsoController::class, 'logout'])->middleware('auth:sanctum');
});

// 受保护的 API 路由
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

## 数据库迁移

通过以下命令发布并执行了 Passport 和 Sanctum 的迁移文件：

```bash
php artisan passport:install
php artisan vendor:publish --provider="Laravel\Passport\PassportServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

生成了相关的数据表：
- oauth_auth_codes
- oauth_access_tokens
- oauth_refresh_tokens
- oauth_clients
- oauth_device_codes
- personal_access_tokens

## 环境变量配置

在 `.env` 文件中添加了 JWT 密钥：

```env
JWT_SECRET=your-jwt-secret-key
```

## 提交历史

代码开发按照功能模块进行了原子化提交：

1. 引入认证相关插件
2. 发布 Passport 配置文件和迁移文件
3. 发布 Sanctum 配置文件和迁移文件
4. 配置 Passport 认证守卫
5. 为 User 模型添加 Passport API Tokens 支持
6. 创建 JWT 服务类
7. 创建 SSO 控制器
8. 创建 API 路由文件
9. 配置 Passport 运行时设置
10. 修复不存在的 hashClientSecrets 方法调用

这样小粒度的提交便于后续开发者理解每个功能模块的实现过程和目的。