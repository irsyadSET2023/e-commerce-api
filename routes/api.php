<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('logout', [AuthController::class, 'logout']);
    });
});


Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('my-account', [UserController::class, 'myAccount']);
});

Route::prefix('product')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
});

Route::prefix('cart')->group(function () {
    Route::post('add_item', [CartController::class, 'addProductToCart']);
    Route::post('checked_out', [CartController::class, 'checkedOutCart']);
});
