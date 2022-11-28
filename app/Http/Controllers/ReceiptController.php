<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Store;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show(Receipt $receipt, Request $request)
    {
        // $receipts = QueryBuilder::for(Receipt::where("store_id", $store))
        //     ->allowedIncludes(['store'])
        //     ->with(["store"])
        //     // ->allowedFilters(['total', 'store_id', 'created_at'])
        //     // ->defaultSort('created_at')
        //     // ->allowedSorts(['total', 'created_at'])
        //     ->paginate($request->limit)
        //     ->appends(request()->query());

        return $receipt;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receipt $receipt)
    {
        //
    }
}
