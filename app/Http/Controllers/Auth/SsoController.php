<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SsoController extends Controller
{
    protected $jwtService;
    
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }
    
    /**
     * 统一登录接口
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        // 验证用户凭据
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }
        
        // 生成 JWT Token
        $token = $this->jwtService->generateToken($user);
        
        // 返回 Token 和用户信息
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 604800, // 7天
            'user' => $user
        ]);
    }
    
    /**
     * 验证 Token
     */
    public function verifyToken(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        
        $payload = $this->jwtService->validateToken($token);
        
        if (!$payload) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        return response()->json([
            'valid' => true,
            'user' => $payload['user']
        ]);
    }
    
    /**
     * 刷新 Token
     */
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        
        $newToken = $this->jwtService->refreshToken($token);
        
        if (!$newToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 604800, // 7天
        ]);
    }
    
    /**
     * 登出
     */
    public function logout(Request $request)
    {
        // 对于 JWT，登出主要是客户端清除 token
        // 服务端可以将 token 加入黑名单（可选）
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}