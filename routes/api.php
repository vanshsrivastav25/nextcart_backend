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
Route::post('register', [AccountController::class, 'register']);
Route::post('login', [AccountController::class, 'authenticate']);


Route::group(['middleware' => 'auth:sanctum'],function(){
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('products', ProductController::class);
    Route::get('sizes', [SizeController::class, 'index']);
    Route::post('temp-images', [TempImageController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/profile', [AccountController::class, 'profile']);
});
