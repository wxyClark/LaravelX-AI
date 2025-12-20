<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SsoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

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
    
    // 其他业务 API 路由...
});