<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SocialiteController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\TransactionController;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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



Route::group(['middleware' => ['web']], function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'reset_password']);
    Route::post('/forgot-password', [AuthController::class, 'reset_password_link']);
    Route::post('/resend-email-verification-link', [AuthController::class, 'resend_email_verification_link']);
    Route::post('/verify-email', [AuthController::class, 'verify_email']);

    /**
     * socialite auth
     */
    Route::get('/auth/{provider}', [SocialiteController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProvideCallback']);

    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipt.show');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function () {
        return auth()->user();
    });
    Route::apiResource('/stores', StoreController::class);
    Route::get('/stores/{id}/restore', [StoreController::class, 'restore']);
    Route::delete('/stores/{id}/delete-permanent', [StoreController::class, 'destroy_permanent']);
    Route::apiResource('/products', ProductController::class);
    Route::get('/products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('/products/{id}/delete-permanent', [ProductController::class, 'destroy_permanent']);
    Route::apiResource('/transactions', TransactionController::class);
    Route::get('/transactions/{id}/restore', [TransactionController::class, 'restore']);
    Route::delete('/transactions/{id}/delete-permanent', [TransactionController::class, 'destroy_permanent']);
    Route::apiResource('/receipts', ReceiptController::class)->except(['show']);
    Route::get('/dashboard', [DashboardController::class, 'Dashboard']);
    Route::get('/getAllStoresTransaction', [DashboardController::class, 'getAllStoresTransaction']);
    Route::get('/getTopProduct', [DashboardController::class, 'getTopProduct']);
    Route::get('/getAllStoreTransaction/{store}', [DashboardController::class, 'getAllStoreTransaction']);
    Route::get('/getModalSales', [DashboardController::class, 'getModalSales']);
    Route::apiResource('/categories', CategoryController::class);
});



Route::get('/test', function (Request $request) {
    $transaction = QueryBuilder::for(Transaction::class)
        ->with(["product"])
        ->allowedFilters([
            AllowedFilter::partial('product.name', null), 'store_id'
        ])
        ->defaultSort('created_at')
        ->allowedSorts(['name', 'created_at'])
        ->paginate($request->limit)
        ->appends(request()->query());
    return $transaction;
});

Route::get('/ko', function () {
    $store = Transaction::whereIn('store_id', [3, 9])->groupBy('year', 'month', 'day')->get();
    return $store;
});

// require __DIR__ . '/auth.php';
// ->groupBy(DB::raw('Date(created_at)'))
// where('created_at', '>=', \Carbon\Carbon::now()->subMonth())