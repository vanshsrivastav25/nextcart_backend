<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\front\AccountController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/admin/login', [AuthController::class, 'authenticate']);

Route::post('register', [AccountController::class, 'register']);
Route::post('login', [AccountController::class, 'authenticate']);

Route::group(['middleware' => 'auth:sanctum'],function(){
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
});
