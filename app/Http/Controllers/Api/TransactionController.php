<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
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
        $store = Store::find($request->filter['store_id']) ?? abort(404, "Store not found!");
        if ($store->user_id != auth()->user()->id) return abort(403, "Unauthorized!");
        $transaction = QueryBuilder::for(Transaction::class)
            ->with(["product"])
            ->allowedFilters([
                AllowedFilter::partial('product.name', null), 'store_id',
                AllowedFilter::trashed()
            ])
            ->defaultSort('created_at')
            ->allowedSorts(['price', 'type', 'created_at'])
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
            "store_id" => "required",
            "transaction.*" => 'array|min:1',
            "transaction.*.qty" => "required|numeric|min:0",
            "transaction.*.price" => "required|numeric|min:0",
            "transaction.*.discount" => "numeric|min:0",
            "transaction.*.description" => "max:250",
            "transaction.*.customer" => "max:250",
            "transaction.*.product_id" => "required",
        ]);
        foreach ($request->transaction as $key => $value) {
            if ($request->type == "IN") {
                $product = Product::where('store_id', $request->store_id)->where('qty', '>=', 0)->find($request->transaction[$key]['product_id']);
            } else {
                $product = Product::where('store_id', $request->store_id)->where('qty', '>=', $request->transaction[$key]['qty'])->find($request->transaction[$key]['product_id']);
            }
            abort_if(!$product, 404, 'Product ' . $key + 1 . ' not found or Exceeding max order limit!');
        }

        foreach ($request->transaction as $key => $value) {
            if ($request->type == "IN") {
                $product = Product::where('store_id', $request->store_id)->whereRelation('store', 'user_id', auth()->user()->id)->where('qty', '>=', 0)->find($request->transaction[$key]['product_id']);
                $product->qty = $product->qty + $request->transaction[$key]['qty'];
            } else {
                $product = Product::where('store_id', $request->store_id)->whereRelation('store', 'user_id', auth()->user()->id)->where('qty', '>=', $request->transaction[$key]['qty'])->find($request->transaction[$key]['product_id']);
                $product->qty = $product->qty - $request->transaction[$key]['qty'];
            }
            $product->save();
            if ($product->qty >= 0) {
                if (isset($request->transaction[$key]['customer'])) {
                    if ($request->type == "IN") {
                        $customer = auth()->user()->name;
                    } else {
                        $customer = $request->transaction[$key]['customer'];
                    }
                } else {
                    $customer = "random";
                }

                $transaction = Transaction::create([
                    "type" => $request->type,
                    "store_id" => $request->store_id,
                    "qty" => $request->transaction[$key]['qty'],
                    "price" => $request->transaction[$key]['price'],
                    "discount" => $request->transaction[$key]['discount'],
                    "total" => $request->transaction[$key]['qty'] * $request->transaction[$key]['price'],
                    "description" => $request->transaction[$key]['description'],
                    "customer" => $customer,
                    "product_id" => $request->transaction[$key]['product_id'],
                ]);
                // if ($transaction) {
                //     return response(["message" => "Transaction was created!"]);
                // } else {
                //     return abort(400, ["message" => "Transaction failed!"]);
                // }
            } else {
                return abort(400, 'Product out of stock!!');
            }
        }

        return response(["message" => "Transaction was created!"]);


        // $product = Product::whereIn('id', $merged)->get();
        // $product->each(function ($request, $item, $index) {
        //     $item->qty - $request->transaction[$index]->qty < 0 ?? abort(400, 'Out of stock!');
        // });
        // foreach ($product as $key => $value) {
        // }
        // return $product;

        // $request->validate([
        //     "type" => "required",
        //     "qty" => "required|numeric",
        //     "price" => "required|numeric",
        //     "discount" => "required|numeric",
        //     "description" => "max:250",
        //     "product_id" => "required",
        //     "store_id" => "required",
        // ]);

        // $product = Product::findOrFail($request->product_id);
        // if ($product) {
        //     if ($request->type == "IN") {
        //         $product->qty = $product->qty + $request->qty;
        //     } else {
        //         $product->qty = $product->qty - $request->qty;
        //     }
        //     if ($product->qty >= 0 and $request->type == "OUT" or $request->type == "IN") {

        //         $transaction = Transaction::create([
        //             "type" => $request->type,
        //             "qty" => $request->qty,
        //             "price" => $request->price,
        //             "discount" => $request->discount,
        //             "total" => $request->qty * $request->price,
        //             "description" => $request->description,
        //             "product_id" => $request->product_id,
        //             "store_id" => $request->store_id,
        //         ]);

        //         $product->save();
        //         return response(["message" => "Transaction was created!"]);
        //     } else {
        //         return abort(400, 'Product out of stock!!');
        //     }
        // } else {
        //     return abort(404, 'Product not found!');
        // }
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
        $request->validate([
            "type" => "required",
            "store_id" => "required",
            "qty" => "required|numeric",
            "price" => "required|numeric",
            "discount" => "required|numeric",
            "description" => "max:250",
            "product_id" => "required",
        ]);
        if ($request->type == "IN") {
            $product = Product::where('store_id', $request->store_id)->whereRelation('store', 'user_id', auth()->user()->id)->where('qty', '>=', 0)->find($request->product_id);
            abort_if(!$product, 404, 'Product not found or Exceeding max order limit!');
            $product->qty = $product->qty + $request->qty;
        } else {
            $product = Product::where('store_id', $request->store_id)->whereRelation('store', 'user_id', auth()->user()->id)->where('qty', '>=', $request->qty)->find($request->product_id);
            abort_if(!$product, 404, 'Product not found or Exceeding max order limit!');
            $product->qty = $product->qty - $request->qty;
        }


        $product->save();

        $transaction->update([
            "type" => $request->type,
            "qty" => $request->qty,
            "price" => $request->price,
            "discount" => $request->discount,
            "total" => $request->total,
            "description" => $request->description,
            'product_id' => $request->product_id,
            'store_id' => $request->store_id,
            'created_at' => $request->created_at,
        ]);
        abort_if(!$product and !$transaction, 400, 'Transaction failed!');
        return response(["message" => "Transaction was updated!"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy($request)
    {
        $ids = explode(',', $request);
        $transaction = Transaction::whereIn('id', $ids)->whereRelation('store', 'user_id', auth()->user()->id)->get();
        if ($transaction->count() == 0) abort(404, 'Transaction not found!');
        $transaction->each(function ($item) {
            $item->delete();
        });

        return response(['message' => "Transaction was deleted!"], 200);
    }
    public function restore($id)
    {
        $transaction = Transaction::onlyTrashed()->whereRelation('store', 'user_id', auth()->user()->id)->findOrFail($id);
        if ($transaction->count() == 0) return abort(404, "Transaction not found!");
        // $transaction->transaction()->where('deleted_at', '>=', $transaction->deleted_at)->restore();
        $transaction->restore();
        return response(['message' => 'Transaction was restored!']);
    }
    public function destroy_permanent($id)
    {
        $transaction = Transaction::withTrashed()->whereRelation('store', 'user_id', auth()->user()->id)->findOrFail($id);
        if ($transaction->count() == 0) return abort(404, 'Transaction not found!');
        $transaction->forceDelete();
        return response(['message' => 'Transaction was pemanently deleted!']);
    }
}
