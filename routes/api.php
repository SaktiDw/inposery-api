<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleSocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', LoginController::class);
Route::post('/register', RegisterController::class);
Route::post('/logout', LogoutController::class);

Route::group(['middleware' => ['web']], function () {
    Route::get('auth/google', [GoogleSocialiteController::class, 'redirectToGoogle']);
    Route::get('callback/google', [GoogleSocialiteController::class, 'handleCallback']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function () {
        return auth()->user();
    });
    Route::apiResource('/stores', StoreController::class);
    Route::apiResource('/products', ProductController::class);
    Route::apiResource('/transactions', TransactionController::class);
    Route::apiResource('/receipts', ReceiptController::class);
    Route::get('getAllStoresTransaction', [DashboardController::class, 'getAllStoresTransaction']);
    Route::get('getAllStoreTransaction', [DashboardController::class, 'getAllStoreTransaction']);
});

// require __DIR__ . '/auth.php';
