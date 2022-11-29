<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;

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
