<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ReceiptController extends Controller
{

    public function index(Request $request)
    {
        $receipts = QueryBuilder::for(Receipt::class)
            ->with(["store"])
            ->allowedFilters(['store_id'])
            ->defaultSort('created_at')
            ->allowedSorts(['created_at'])
            ->paginate($request->limit)
            ->appends(request()->query());
        return $receipts;
    }

    public function store(Request $request)
    {
        $request->validate([
            "store_id" => "required",
            "products" => "required|json",
            "total" => "required|numeric|min:0",
            "payment" => "required|numeric|min:0",
            "change" => "required|numeric|min:0",
            "discount" => "required|numeric|min:0",
        ]);

        $receipt = Receipt::create([
            "store_id" => $request->store_id,
            "products" => $request->products,
            "total" => $request->total,
            "payment" => $request->payment,
            "change" => $request->change,
            "discount" => $request->discount,
        ]);

        return response()->json(["message" => "Receipt was created!"]);
    }

    public function show(Receipt $receipt)
    {
        return $receipt->load(['store', 'store.media']);
    }

    // public function update(Request $request, Receipt $receipt)
    // {
    //     //
    // }

    // public function destroy(Receipt $receipt)
    // {
    //     //
    // }
}
