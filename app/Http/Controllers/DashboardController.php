<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getAllStoresTransaction()
    {
        $store = Store::where("user_id", auth()->user()->id)->with("transaction")->get();
        return $store;
    }

    public function getAllStoreTransaction(Store $store)
    {
        $store->with("transaction")->get();
        return $store;
    }
}
