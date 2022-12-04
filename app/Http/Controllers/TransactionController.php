<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
            "type" => "required",
            "qty" => "required|numeric",
            "price" => "required|numeric",
            "discount" => "required|numeric",
            "description" => "required|max:250",
            "product_id" => "required",
            "store_id" => "required",
        ]);

        $transaction = Transaction::create([
            "type" => $request->type,
            "qty" => $request->qty,
            "price" => $request->price,
            "discount" => $request->discount,
            "total" => $request->qty * $request->price,
            "description" => $request->description,
            "product_id" => $request->product_id,
            "store_id" => $request->store_id,
        ]);
        if ($transaction) {
            $product = Product::findOrFail($request->product_id);
            if ($transaction->type == "IN") {
                $product->qty = $product->qty + $request->qty;
            } else {
                $product->qty = $product->qty - $request->qty;
            }
            $product->save();
        }

        return response()->json(["message" => "Transaction was created!"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = explode(',', $request);
        $transaction = Transaction::whereIn('id', $ids)->delete();
        return response()->json(['message' => "Transaction was deleted!"]);
    }
}
