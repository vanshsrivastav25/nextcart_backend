<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\front\AccountController;

// ADMIN ROUTES
Route::post('/admin/login', [AuthController::class, 'authenticate']);

// USER ROUTES
// Route::post('register', [AccountController::class, 'register']);
// Route::post('login', [AccountController::class, 'authenticate']);

Route::prefix('account')->group(function () {
    Route::post('/register', [AccountController::class, 'register']);
    Route::post('/verify-email', [AccountController::class, 'verifyEmail']);
    Route::post('/resend-otp', [AccountController::class, 'resendOTP']);
    Route::post('/login', [AccountController::class, 'authenticate']);
});

Route::group(['middleware' => 'auth:sanctum'],function(){
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('products', ProductController::class);
    Route::get('sizes', [SizeController::class, 'index']);
    Route::post('temp-images', [TempImageController::class, 'store']);
    Route::post('save-product-images', [ProductController::class, 'saveProductImage']);
    Route::post('change-product-default-images', [ProductController::class, 'updateDefaultImage']);
    Route::delete('product-images/{id}', [ProductController::class, 'deleteImage']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/profile', [AccountController::class, 'profile']);
});
