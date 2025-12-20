<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class JwtService
{
    protected $key;
    
    public function __construct()
    {
        $this->key = env('JWT_SECRET', 'your-secret-key');
    }
    
    /**
     * 生成 JWT Token
     */
    public function generateToken($user)
    {
        $payload = [
            'iss' => env('APP_URL'),
            'sub' => $user->id,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addDays(7)->timestamp,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];
        
        return JWT::encode($payload, $this->key, 'HS256');
    }
    
    /**
     * 验证 JWT Token
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 刷新 JWT Token
     */
    public function refreshToken($token)
    {
        $payload = $this->validateToken($token);
        
        if (!$payload) {
            return false;
        }
        
        // 创建新的 payload，更新时间戳
        $newPayload = $payload;
        $newPayload['iat'] = Carbon::now()->timestamp;
        $newPayload['exp'] = Carbon::now()->addDays(7)->timestamp;
        
        return JWT::encode($newPayload, $this->key, 'HS256');
    }
}