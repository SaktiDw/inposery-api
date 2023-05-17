<?php

use App\Http\Controllers\Api\SocialiteController;
use App\Http\Controllers\ProfileController;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', config('app.frontend_url'));

Route::get('/dashboard', function () {
    $store = Store::all();
    return view('dashboard', ['store' => $store]);
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get("/store/transactions", function (Request $request) {
    $transaction = QueryBuilder::for(Transaction::class)
        ->with(["product"])
        ->allowedFilters([
            AllowedFilter::partial('product.name', null), 'store_id'
        ])
        ->defaultSort('created_at')
        ->allowedSorts(['name', 'created_at'])
        ->paginate($request->limit)
        ->appends(request()->query());
    return view('store.transactions', ['data' => $transaction]);
})->name('store.transactions');


/**
 * socialite auth
 */
// Route::get('/auth/{provider}', [SocialiteController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProvideCallback']);

require __DIR__ . '/auth.php';
